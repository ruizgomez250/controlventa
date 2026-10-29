<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\SifenPkuatiaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SifenController extends Controller
{
    protected SifenPkuatiaService $sifen;

    public function __construct(SifenPkuatiaService $sifen)
    {
        $this->sifen = $sifen;
    }

    public function estado()
    {
        try {
            $config = $this->sifen->getConfig();
            $errores = $this->sifen->validarConfiguracion();

            return response()->json([
                'success' => true,
                'data' => [
                    'habilitado' => $config->isHabilitado(),
                    'ambiente' => $config->isProduccion() ? 'Produccion' : 'Testing',
                    'ruc_emisor' => $config->ruc_emisor,
                    'razon_social' => $config->razon_social,
                    'departamento' => $config->getDepartamentoNombre(),
                    'distrito' => $config->getDistritoNombre(),
                    'configuracion_valida' => empty($errores),
                    'errores' => $errores,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function comprobantes(Request $request)
    {
        abort_unless(auth()->user()->can('venta leer'), 403);
        $filtros = $request->validate([
            'estado' => 'nullable|in:sin_enviar,por_cobrar,enviando,pendiente,sin_respuesta,autorizado,rechazado,error,cancelado',
            'buscar' => 'nullable|string|max:100',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);
        $query = Venta::with('cliente');
        if ($estado = $filtros['estado'] ?? null) {
            $estado === 'sin_enviar' ? $query->whereNull('sifen_estado') : $query->where('sifen_estado', $estado);
        }
        if ($buscar = $filtros['buscar'] ?? null) {
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_factura', 'like', '%' . $buscar . '%')
                    ->orWhere('sifen_cdc', 'like', '%' . $buscar . '%')
                    ->orWhereHas('cliente', fn ($cliente) => $cliente->where('razonsocial', 'like', '%' . $buscar . '%'));
            });
        }
        if (!empty($filtros['desde'])) $query->whereDate('fecha_emision', '>=', $filtros['desde']);
        if (!empty($filtros['hasta'])) $query->whereDate('fecha_emision', '<=', $filtros['hasta']);
        $comprobantes = $query->orderByDesc('id')->paginate(25)->withQueryString();
        $estados = ['sin_enviar' => 'Sin enviar', 'por_cobrar' => 'Pendiente de cobro', 'enviando' => 'Enviando', 'pendiente' => 'Pendiente de aprobación', 'sin_respuesta' => 'Sin respuesta de SIFEN', 'autorizado' => 'Aprobado', 'rechazado' => 'Rechazado', 'error' => 'Error técnico', 'cancelado' => 'Cancelado'];
        return view('sifen.comprobantes', compact('comprobantes', 'estados'));
    }

    public function facturaTest(Request $request, Venta $venta)
    {
        abort_unless(auth()->user()->can('venta leer') || auth()->user()->can('caja leer'), 403);
        $request->validate(['formato' => 'nullable|in:a4,ticket']);
        $formato = $request->input('formato', 'a4');
        $config = $this->sifen->getConfig();
        abort_if($config->isProduccion(), 409, 'Esta vista es exclusiva de TEST.');
        $venta->load(['cliente', 'detalles']);
        $qrUrl = 'https://ekuatia.set.gov.py/consultas-test/';
        $cdc = null;
        $archivos = glob(storage_path('app/sifen/auditoria/*/*/*/venta_' . (int) $venta->id . '_*.xml')) ?: [];
        rsort($archivos);
        foreach ($archivos as $archivo) {
            $xml = new \DOMDocument();
            if (!@$xml->loadXML(file_get_contents($archivo), LIBXML_NONET)) {
                continue;
            }
            $xpath = new \DOMXPath($xml);
            $url = $xpath->evaluate('string(//*[local-name()="dCarQR"])');
            $id = $xpath->evaluate('string(//*[local-name()="DE"]/@Id)');
            $parts = parse_url($url);
            parse_str($parts['query'] ?? '', $query);
            if (($parts['scheme'] ?? '') === 'https'
                && in_array($parts['host'] ?? '', ['ekuatia.set.gov.py', 'www.ekuatia.set.gov.py'], true)
                && ($parts['path'] ?? '') === '/consultas-test/qr'
                && preg_match('/^\d{44}$/', $id)
                && ($query['Id'] ?? null) === $id
                && (!$venta->sifen_cdc || $venta->sifen_cdc === $id)
                && $xpath->query('//*[local-name()="Signature"]')->length > 0) {
                $qrUrl = $url;
                $cdc = $id;
                break;
            }
        }
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(240)->margin(4)->generate($qrUrl);
        return response()->view('ventas.factura-test', compact('venta', 'config', 'qrUrl', 'qrSvg', 'cdc', 'formato'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function consultarCDC(Request $request, $id)
    {
        try {
            $venta = Venta::findOrFail($id);

            if (!$venta->sifen_cdc) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'La venta no tiene un CDC asociado.',
                ], 400);
            }

            // Si el último envío quedó sin resultado, la consulta también actualiza la venta
            if ($this->sifen->requiereVerificacion($venta)) {
                $resultado = $this->sifen->verificarEnvioAnterior($venta) ?? [
                    'estado' => 'no_registrado',
                    'cdc' => $venta->sifen_cdc,
                    'mensaje' => 'SIFEN no registra este CDC. Se puede reenviar sin duplicar la factura.',
                ];
                return response()->json(['success' => $resultado['estado'] !== 'sin_respuesta', 'data' => $resultado]);
            }

            $resultado = $this->sifen->consultarCDC($venta->sifen_cdc);

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ]);
        } catch (\Exception $e) {
            Log::error('SifenController::consultarCDC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function consultarRUC(Request $request)
    {
        $request->validate(['ruc' => 'required|string']);

        try {
            $resultado = $this->sifen->consultarRUC($request->ruc);

            return response()->json([
                'success' => $resultado['encontrado'] ?? false,
                'data' => $resultado,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('SifenController::consultarRUC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function reemitir(Venta $venta)
    {
        try {
            if ($venta->sifen_estado === 'autorizado') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El documento ya fue autorizado.',
                ], 400);
            }

            $resultado = $this->sifen->emitir($venta);

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ]);
        } catch (\Exception $e) {
            Log::error('SifenController::reemitir: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function inutilizar(Request $request)
    {
        $request->validate([
            'timbrado' => 'required|integer',
            'nro_inicial' => 'required|integer|min:1',
            'nro_final' => 'required|integer|min:1',
            'motivo' => 'required|string|min:5',
        ]);

        try {
            $config = $this->sifen->getConfig();

            $resultado = $this->sifen->inutilizarNumeros(
                timbrado: (int)$request->timbrado,
                nroEstablecimiento: (int)$config->establecimiento,
                nroPuntoEmision: (int)$config->punto_expedicion,
                nroDocumentoInicial: (int)$request->nro_inicial,
                nroDocumentoFinal: (int)$request->nro_final,
                tipoDE: 1,
                motivo: $request->motivo
            );

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ]);
        } catch (\Exception $e) {
            Log::error('SifenController::inutilizar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function validarRUC(Request $request)
    {
        $request->validate(['ruc' => 'required|string']);

        $validacion = $this->sifen->validarRUC(
            $request->ruc,
            $request->input('dv')
        );

        return response()->json([
            'success' => $validacion['valido'],
            'data' => $validacion,
        ]);
    }
}
