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
                'success' => true,
                'data' => $resultado,
            ]);
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
