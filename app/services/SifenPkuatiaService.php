<?php

namespace App\Services;

use App\Models\SifenConfiguracion;
use App\Models\Venta;
use App\Models\Cliente;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use IonysDev\Pkuatia\Core\Config;
use IonysDev\Pkuatia\Sifen;
use IonysDev\Pkuatia\Core\Constants\CamCondOpe;
use IonysDev\Pkuatia\Core\Constants\CamFEIndPres;
use IonysDev\Pkuatia\Core\Constants\CamIVAAfecIVA;
use IonysDev\Pkuatia\Core\Constants\CamIVATasaIVA;
use IonysDev\Pkuatia\Core\Constants\EmisRecTipCont;
use IonysDev\Pkuatia\Core\Constants\OpeComTipTrans;
use IonysDev\Pkuatia\Core\Constants\PaConEIniTiPago;
use IonysDev\Pkuatia\Core\Constants\RecTiOpe;
use IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura;

class SifenPkuatiaService
{
    protected SifenConfiguracion $config;

    protected bool $initialized = false;

    protected ?string $certTempPath = null;

    protected ?string $dIdFilePath = null;

    public function __construct()
    {
        $this->config = SifenConfiguracion::firstOrCreate([], [
            'ambiente' => 1,
            'establecimiento' => '001',
            'punto_expedicion' => '001',
            'habilitado' => false,
        ]);
    }

    public function getConfig(): SifenConfiguracion
    {
        return $this->config;
    }

    public function isHabilitado(): bool
    {
        return $this->config->isHabilitado();
    }

    public function updateConfig(array $data): SifenConfiguracion
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->config->getFillable())) {
                $this->config->$key = $value;
            }
        }
        $this->config->save();
        return $this->config;
    }

    protected function ensureInitialized(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!$this->config->certificado_p12) {
            throw new Exception('No se ha configurado el certificado digital P12');
        }

        $pkuatiaConfig = new Config();
        $pkuatiaConfig->env = $this->config->isProduccion() ? Config::ENV_PROD : Config::ENV_DEV;

        $certP12 = base64_decode($this->config->certificado_p12);
        $password = $this->config->certificado_password ?: '';

        $tempDir = storage_path('app/sifen/pkuatia');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $certPath = $tempDir . '/cert_' . $this->config->id . '.p12';
        file_put_contents($certPath, $certP12);
        $this->certTempPath = $certPath;

        $pkuatiaConfig->certificateFormat = 'p12';
        $pkuatiaConfig->privateKeyFilePath = $certPath;
        $pkuatiaConfig->privateKeyPassphrase = $password;

        $pkuatiaConfig->idCsc = $this->config->csc_id ?: '0001';
        $pkuatiaConfig->csc = $this->config->csc_codigo ?: '';

        $this->dIdFilePath = $tempDir . '/dId_' . $this->config->id . '.json';
        $pkuatiaConfig->dIdFilePath = $this->dIdFilePath;

        $pkuatiaConfig->wsdlCacheEnabled = true;

        if (PHP_OS_FAMILY === 'Windows') {
            ini_set('soap.wsdl_cache_dir', sys_get_temp_dir());
        }

        Sifen::Init($pkuatiaConfig);
        $this->initialized = true;
    }

    public function emitir(Venta $venta): array
    {
        if (!$this->isHabilitado()) {
            throw new Exception('SIFEN no está habilitado. Configure los datos del emisor y cargue el certificado.');
        }

        try {
            $this->ensureInitialized();

            $factura = $this->buildFactura($venta);
            $rde = $factura->facturaToRDE();
            $fechaFirma = new DateTime('now', new DateTimeZone('America/Asuncion'));
            $xmlFirmado = Sifen::FirmarDE($rde, $fechaFirma);

            $tempDir = storage_path('app/sifen/pkuatia');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $xmlPath = $tempDir . '/de_' . $venta->id . '_' . date('YmdHis') . '.xml';
            file_put_contents($xmlPath, $xmlFirmado);

            $respuesta = Sifen::EnviarDE($xmlFirmado);

            $resultado = $this->procesarRespuesta($respuesta, $venta);

            return $resultado;
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::emitir - Error: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function buildFactura(Venta $venta): Factura
    {
        $cliente = $venta->cliente;
        $detalles = $venta->detalles;

        $condicion = $venta->tipo_comprobante === 'CREDITO'
            ? CamCondOpe::Credito
            : CamCondOpe::Contado;

        $factura = new Factura($condicion);

        $factura->setTimbrado(
            numTimb: (int)$venta->timbrado_factura,
            fechaInicio: new DateTime($venta->fecha_emision),
            numEst: (int)$this->config->establecimiento,
            numExp: (int)$this->config->punto_expedicion,
            numDoc: (int)($venta->numero_factura ?: $venta->id),
        );

        $fechaEmision = $venta->fecha_emision
            ? new DateTime($venta->fecha_emision, new DateTimeZone('America/Asuncion'))
            : new DateTime('now', new DateTimeZone('America/Asuncion'));
        $factura->setFechaEmision($fechaEmision);

        $rucEmisor = preg_replace('/[^0-9]/', '', $this->config->ruc_emisor);

        $factura->setEmisor(
            rucEmisor: $rucEmisor,
            dv: (int)$this->config->dv,
            tipoContribuyente: $this->config->tipo_contribuyente ?: EmisRecTipCont::PersonaJuridica,
            tipoRegimen: $this->config->tipo_regimen,
            nombreEmisor: $this->config->razon_social ?: 'Sin Razon Social',
            nombreFantasia: null,
            callePrincipal: $this->config->calle_principal ?: ($this->config->direccion ?: 'Sin direccion'),
            casaNro: $this->config->numero_casa ?: '0',
            calleSecundaria: $this->config->calle_secundaria,
            complementoDir: $this->config->complemento_direccion,
            codDep: $this->config->departamento_codigo ?: 1,
            codDistrito: $this->config->distrito_codigo,
            codCiud: $this->config->ciudad_codigo ?: 1,
            telefono: $this->config->telefono ?: '000000000',
            email: $this->config->email ?: 'email@empresa.com',
            nombreSucursal: $this->config->nombre_sucursal,
        );

        $factura->addEmisorActividadEconomica(
            codigo: $this->config->actividad_economica_codigo ?: 620,
            descripcion: $this->config->actividad_economica_descripcion ?: 'ACTIVIDADES DE PROGRAMACION Y CONSULTORIA INFORMATICAS',
        );

        $this->setReceptor($factura, $cliente);

        $factura->setTipoDeTransaccion(OpeComTipTrans::VentaMercaderia);
        $factura->setIndicadorPresencia(CamFEIndPres::Presencial);

        foreach ($detalles as $detalle) {
            $this->addItem($factura, $detalle);
        }

        $montoTotal = (float)$venta->total;
        $factura->addPago(
            tipoDePago: PaConEIniTiPago::Efectivo,
            monto: number_format($montoTotal, 0, '.', ''),
            moneda: 'PYG',
        );

        return $factura;
    }

    protected function setReceptor(Factura $factura, ?Cliente $cliente): void
    {
        $rucRec = '';
        $dvRec = null;
        $esContribuyente = false;

        if ($cliente && $cliente->ruc) {
            $rucLimpio = preg_replace('/[^0-9]/', '', $cliente->ruc);
            if (strlen($rucLimpio) > 1) {
                $dvRec = (int)substr($rucLimpio, -1);
                $rucRec = substr($rucLimpio, 0, -1);
                $esContribuyente = true;
            }
        }

        $nombreRec = $cliente ? ($cliente->razonsocial ?: 'Consumidor Final') : 'Consumidor Final';

        $factura->setReceptor(
            nombre: $nombreRec,
            esContribuyente: $esContribuyente,
            tipoOperacion: $esContribuyente ? RecTiOpe::B2B : RecTiOpe::B2C,
            codPais: 'PRY',
            tipoContribuyente: $esContribuyente ? EmisRecTipCont::PersonaJuridica : null,
            ruc: $rucRec ?: null,
            dv: $dvRec,
            tipoIdentificacion: null,
            nroIdentificacion: null,
            nombreFantasia: null,
            callePrincipal: $cliente->direccion ?? null,
            numeroCasa: null,
            codigoDepartamento: null,
            codigoDistrito: null,
            codigoCiudad: null,
            telefono: $cliente->telefono ?? null,
            celular: $cliente->celular ?? null,
            email: $cliente->correo ?? null,
            codigoDeCliente: (string)$cliente->id,
        );
    }

    protected function addItem(Factura $factura, $detalle): void
    {
        $tasaIVA = $this->getTasaIVA($detalle->tipo_impuesto);
        $afectIVA = $tasaIVA === 0 ? CamIVAAfecIVA::Exento : CamIVAAfecIVA::Gravado;
        $tasaEnum = match ($tasaIVA) {
            5 => CamIVATasaIVA::IVA5,
            10 => CamIVATasaIVA::IVA10,
            default => CamIVATasaIVA::IVA5,
        };

        $cantidad = number_format((float)$detalle->cantidad, 4, '.', '');
        $precioUnit = number_format((float)$detalle->precio_u, 0, '.', '');

        $factura->addItem(
            codigo: (string)$detalle->id_producto,
            descripcion: $detalle->descripcion ?: 'Producto',
            codUnidadMedida: 77,
            cantidad: $cantidad,
            precisionMoneda: 0,
            precioUnit: $precioUnit,
            totalBruto: null,
            afectIVA: $afectIVA,
            proporcionGravadaIVA: $tasaIVA === 0 ? '0' : '100',
            tasaDeIVA: $tasaEnum,
        );
    }

    protected function procesarRespuesta($respuesta, Venta $venta): array
    {
        $resultado = [
            'estado' => 'rechazado',
            'cde' => null,
            'cdc' => null,
            'mensaje' => '',
        ];

        try {
            $prot = $respuesta->getRProtDe();
            $resultado['cdc'] = $prot->getId();

            $gResProc = $prot->getGResProc();
            if (!empty($gResProc)) {
                $proc = $gResProc[0];
                $codRes = (int)$proc->dCodRes;
                $msgRes = $proc->dMsgRes;
                $resultado['mensaje'] = $msgRes;

                if ($codRes >= 260 && $codRes < 270) {
                    $resultado['estado'] = 'autorizado';
                    $resultado['cde'] = $prot->getDProtAut() ?? $prot->getId();
                } elseif ($codRes >= 270 && $codRes < 280) {
                    $resultado['estado'] = 'pendiente';
                } else {
                    $resultado['estado'] = 'rechazado';
                }
            }

            $venta->sifen_cdc = $resultado['cdc'];
            $venta->sifen_cde = $resultado['cde'];
            $venta->sifen_estado = $resultado['estado'];
            $venta->save();
        } catch (Exception $e) {
            $resultado['mensaje'] = 'Error al procesar respuesta: ' . $e->getMessage();
            Log::error('SifenPkuatiaService::procesarRespuesta: ' . $e->getMessage());
        }

        return $resultado;
    }

    public function cancelarDE(Venta $venta, string $motivo = ''): array
    {
        if (!$this->isHabilitado()) {
            throw new Exception('SIFEN no esta habilitado.');
        }
        if (!$venta->sifen_cdc) {
            throw new Exception('La venta no tiene un CDC asociado.');
        }

        $this->ensureInitialized();

        $motivo = $motivo ?: 'Cancelacion de documento electronico';

        try {
            $respuesta = Sifen::CancelarDE($venta->sifen_cdc, $motivo);
            return [
                'estado' => 'procesado',
                'mensaje' => 'Evento de cancelacion enviado correctamente.',
            ];
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::cancelarDE: ' . $e->getMessage());
            throw $e;
        }
    }

    public function consultarRUC(string $ruc): ?array
    {
        $this->ensureInitialized();

        try {
            $rucLimpio = preg_replace('/[^0-9]/', '', $ruc);
            $respuesta = Sifen::ConsultarRUC($rucLimpio);
            $rContRuc = $respuesta->getRContRuc();

            if ($rContRuc) {
                return [
                    'ruc' => $rContRuc->dRucCont,
                    'dv' => $rContRuc->dDVCont,
                    'razon_social' => $rContRuc->dRazCons,
                    'direccion' => $rContRuc->dDirCont ?? null,
                    'contribuyente' => true,
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::warning('SifenPkuatiaService::consultarRUC: ' . $e->getMessage());
            return null;
        }
    }

    protected function getTasaIVA(?string $tipoImpuesto): int
    {
        if (!$tipoImpuesto) {
            return 10;
        }
        $tipo = strtolower(trim($tipoImpuesto));
        if (in_array($tipo, ['exenta', 'exento', '0', '0%'])) {
            return 0;
        }
        if (in_array($tipo, ['5', '5%'])) {
            return 5;
        }
        return 10;
    }

    public function __destruct()
    {
        if ($this->certTempPath && file_exists($this->certTempPath)) {
            @unlink($this->certTempPath);
        }
    }
}
