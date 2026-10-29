<?php

namespace App\Services;

use App\Helpers\ActividadesEconomicas;
use App\Helpers\SifenCatalogo;
use App\Helpers\RucParaguayo;
use App\Models\ActividadEconomica;
use App\Models\Departamento;
use App\Models\Distrito;
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
use IonysDev\Pkuatia\Core\Constants\OpeDETipEmi;
use IonysDev\Pkuatia\Core\Constants\TimbTiDE;
use IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura;
use IonysDev\Pkuatia\Core\Fields\Request\Event\GDE\GGroupGesEve;
use IonysDev\Pkuatia\Core\Fields\Request\Event\GDE\RGesEve;

class SifenPkuatiaService
{
    /** @var SifenConfiguracion Configuración del emisor (RUC, certificado, CSC, etc.) */
    protected SifenConfiguracion $config;

    /** @var bool Indica si Sifen::Init() ya fue llamado */
    protected bool $initialized = false;

    /** @var string|null Ruta temporal del archivo .p12 extraído para la biblioteca pkuatia */
    protected ?string $certTempPath = null;

    /** @var string|null Ruta temporal del archivo dId.json para la biblioteca pkuatia */
    protected ?string $dIdFilePath = null;

    /**
     * Constructor: carga la única fila de sifen_configuraciones
     * o crea una por defecto (ambiente pruebas, establecimiento 001, deshabilitado).
     */
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

    /** ¿Está todo listo para emitir? (habilitado + RUC + DV + certificado) */
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

    /**
     * Inicializa la librería ionysdev/pkuatia:
     *  1. Decodifica el certificado P12 (base64 → binario)
     *  2. Lo escribe en storage/app/sifen/pkuatia/cert_<id>.p12
     *  3. Crea un objeto Config con entorno, certificado, CSC
     *  4. Llama a Sifen::Init($config)
     *  Solo se ejecuta una vez por request (flag $initialized).
     */
    protected function ensureInitialized(): void
    {
        // En esta instalación default_socket_timeout=0 hace fallar de inmediato
        // la descarga del WSDL y sus XSD, aunque HTTPS y el certificado funcionen.
        if ((int) ini_get('default_socket_timeout') <= 0) {
            ini_set('default_socket_timeout', '60');
        }

        if ($this->initialized) {
            return;
        }

        if (!$this->config->certificado_p12) {
            throw new Exception('No se ha configurado el certificado digital P12');
        }

        $pkuatiaConfig = new Config();
        // 1 = testing (homologación), 2 = producción
        $pkuatiaConfig->env = $this->config->isProduccion() ? Config::ENV_PROD : Config::ENV_DEV;

        // El certificado se guardó en base64 en la BD, lo decodificamos
        $certP12 = base64_decode($this->config->certificado_p12);
        $password = $this->config->certificado_password ?: '';

        $tempDir = storage_path('app/sifen/pkuatia');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // La librería necesita el .p12 como archivo físico
        $certPath = $tempDir . '/cert_' . $this->config->id . '.p12';
        file_put_contents($certPath, $certP12);
        $this->certTempPath = $certPath;

        $pkuatiaConfig->certificateFormat = 'p12';
        $pkuatiaConfig->privateKeyFilePath = $certPath;
        $pkuatiaConfig->privateKeyPassphrase = $password;

        // CSC = Código de Seguridad del Contribuyente (firma del DE)
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

    /**
     * Valida que la configuración SIFEN esté completa antes de emitir.
     * Retorna un array con strings de error (vacío si todo ok).
     */
    public function validarConfiguracion(): array
    {
        $errores = [];

        if (!$this->config->ruc_emisor) {
            $errores[] = 'RUC del emisor no configurado.';
        }
        if ($this->config->dv === null || $this->config->dv === '') {
            $errores[] = 'DV del emisor no configurado.';
        }
        if (!$this->config->razon_social) {
            $errores[] = 'Razón social del emisor no configurada.';
        }
        if (!$this->config->certificado_p12) {
            $errores[] = 'Certificado digital P12 no cargado.';
        }
        if (!$this->config->calle_principal) {
            $errores[] = 'Calle principal del emisor no configurada.';
        }

        // Validar que el departamento/distrito existan en la BD (catálogo SET)
        $dep = $this->config->departamento_codigo;
        if (!$dep || !Departamento::find((int)$dep)) {
            $errores[] = "Código de departamento inválido: $dep. Debe ser 1-20 según catálogo SET.";
        }

        $distrito = $this->config->distrito_codigo;
        if ($dep && (!$distrito || !Distrito::where('codigo', (int)$distrito)->where('departamento_codigo', (int)$dep)->exists())) {
            $errores[] = "Código de distrito $distrito inválido para el departamento $dep.";
        }

        // Validar que la actividad económica exista en la BD (catálogo CIIU)
        $codActividad = $this->config->actividad_economica_codigo;
        if (!$codActividad) {
            $errores[] = 'Actividad económica principal no configurada.';
        } elseif (!ActividadEconomica::find((int)$codActividad)) {
            if (!isset(ActividadesEconomicas::ACTIVIDADES[(int)$codActividad])) {
                $errores[] = "Código de actividad económica inválido: $codActividad.";
            }
        }

        // Validar RUC con algoritmo módulo 11
        if ($this->config->ruc_emisor) {
            $validacion = $this->validarRUC($this->config->ruc_emisor, $this->config->dv);
            if (!$validacion['valido']) {
                $errores[] = 'RUC/DV del emisor inválido: ' . $validacion['mensaje'];
            }
        }

        return $errores;
    }

    /**
     * Valida RUC paraguayo (5 a 8 dígitos + DV) con módulo 11.
     * Toma los dígitos del RUC en orden inverso, los multiplica por
     * la secuencia de pesos 2 a 11, suma, resto 11.
     * Si el resto > 1, DV = 11 - resto; si no, DV = 0.
     */
    public function validarRUC(string $ruc, ?string $dvEsperado = null): array
    {
        try {
            $sinDv = $dvEsperado === null && !str_contains($ruc, '-');
            $numero = RucParaguayo::numeroConsulta($ruc);
            $calculado = RucParaguayo::calcularDv($numero);
            if (!$sinDv) RucParaguayo::validar($ruc, $dvEsperado);
            return ['valido' => true, 'ruc' => $numero, 'dv' => (int) $calculado,
                'dv_validado' => !$sinDv, 'existencia_verificada' => false,
                'mensaje' => $sinDv ? 'Formato válido. DV calculado; falta un DV ingresado para compararlo.'
                    : 'Formato y DV válidos por módulo 11. La existencia en Marangatu no fue verificada.'];
        } catch (\InvalidArgumentException $e) {
            return ['valido' => false, 'dv_validado' => false, 'existencia_verificada' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /**
     * ============================================================
     *  EMISIÓN DE FACTURA ELECTRÓNICA (DE → Documento Electrónico)
     * ============================================================
     * Flujo completo:
     *  1. Verificar que SIFEN esté habilitado (isHabilitado)
     *  2. Validar configuración (RUC, cert, CSC, datos geográficos)
     *  3. Inicializar la librería pkuatia (ensureInitialized)
     *  4. Construir objeto Factura con datos de venta, cliente, items
     *  5. Convertir Factura → RDE (representación intermedia)
     *  6. Firmar el DE con el CSC (Sifen::FirmarDE)
     *  7. Guardar XML firmado en disco (auditarXML)
     *  8. Enviar a SET (Sifen::EnviarDE)
     *  9. Procesar respuesta: extraer CDC, CDE, estado y guardar en Venta
     * 10. Loguear resultado (auditarRespuesta)
     */
    public function emitir(Venta $venta): array
    {
        if (!$this->isHabilitado()) {
            throw new Exception('SIFEN no está habilitado. Configure los datos del emisor y cargue el certificado.');
        }

        $errores = $this->validarConfiguracion();
        if (!empty($errores)) {
            throw new Exception('Errores de configuración SIFEN: ' . implode(' | ', $errores));
        }

        try {
            $this->ensureInitialized();

            // Construir la Factura según el modelo de datos de pkuatia
            $factura = $this->buildFactura($venta);
            // Convertir a Representación de Documento Electrónico (RDE)
            $rde = $factura->facturaToRDE();
            // Firmar con CSC (Código de Seguridad del Contribuyente)
            $fechaFirma = new DateTime('now', new DateTimeZone('America/Asuncion'));
            $xmlFirmado = Sifen::FirmarDE($rde, $fechaFirma);

            // Guardar copia del XML firmado para auditoría
            $this->auditarXML($venta, $xmlFirmado, 'enviado');

            // Enviar a la SET vía SOAP
            $respuesta = Sifen::EnviarDE($xmlFirmado);

            // Extraer CDC, CDE y estado desde la respuesta
            $resultado = $this->procesarRespuesta($respuesta, $venta);

            $this->auditarRespuesta($venta, $resultado);

            return $resultado;
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::emitir - Error: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->registrarError($venta, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Guarda el XML (firmado o recibido) en storage/app/sifen/auditoria/
     * para trazabilidad y debugging.
     */
    protected function auditarXML(Venta $venta, string $xml, string $tipo): void
    {
        try {
            $dir = storage_path('app/sifen/auditoria/' . date('Y/m/d'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = $dir . '/venta_' . $venta->id . '_' . $tipo . '_' . date('YmdHis') . '.xml';
            file_put_contents($filename, $xml);

            $document = new \DOMDocument();
            if ($document->loadXML($xml, LIBXML_NONET)) {
                $xpath = new \DOMXPath($document);
                $cdc = $xpath->evaluate('string(//*[local-name()="DE"]/@Id)');
                if (preg_match('/^\d{44}$/', $cdc)) {
                    $venta->sifen_cdc = $cdc;
                    $venta->sifen_estado = 'pendiente';
                    $venta->sifen_ambiente = $this->config->ambiente;
                    $venta->sifen_actualizado_at = now();
                    $venta->save();
                }
            }

            Log::info("SIFEN XML $tipo guardado para venta {$venta->id}", ['archivo' => $filename]);
        } catch (Exception $e) {
            Log::warning("No se pudo guardar XML de auditoría: " . $e->getMessage());
        }
    }

    /**
     * Loguea el resultado de la emisión (CDC, CDE, estado).
     */
    protected function auditarRespuesta(Venta $venta, array $resultado): void
    {
        Log::info('SIFEN respuesta procesada para venta ' . $venta->id, [
            'venta_id' => $venta->id,
            'cdc' => $resultado['cdc'] ?? null,
            'cde' => $resultado['cde'] ?? null,
            'estado' => $resultado['estado'],
            'mensaje' => $resultado['mensaje'],
        ]);
    }

    /**
     * Construye el objeto Factura (del SDK pkuatia) con todos los datos:
     *  - Timbre (Nro Timbrado, establecimiento, punto de expedición, número de documento)
     *  - Emisor (RUC, DV, razón social, dirección, actividad económica)
     *  - Receptor (cliente: si tiene RUC válido → B2B, si no → Consumidor Final B2C)
     *  - Items de la venta (con IVA 0%, 5% o 10%)
     *  - Pago (monto total en efectivo, moneda PYG)
     */
    protected function buildFactura(Venta $venta): Factura
    {
        $cliente = $venta->cliente;
        $detalles = $venta->detalles;

        // CRÉDITO o CONTADO según el tipo de comprobante
        $condicion = $venta->tipo_comprobante === 'CREDITO'
            ? CamCondOpe::Credito
            : CamCondOpe::Contado;

        $factura = new Factura($condicion);

        // Datos del timbrado: número, fechas, establecimiento, punto de expedición, documento
        $factura->setTimbrado(
            numTimb: (int)$venta->timbrado_factura,
            fechaInicio: new DateTime($venta->sifen_fecha_inicio_timbrado ?: ($this->config->fecha_inicio_timbrado ?: $venta->fecha_emision)),
            numEst: (int)($venta->sifen_establecimiento ?: $this->config->establecimiento),
            numExp: (int)($venta->sifen_punto_expedicion ?: $this->config->punto_expedicion),
            numDoc: (int)($venta->numero_factura ?: $venta->id),
        );

        $fechaEmision = $venta->fecha_emision
            ? new DateTime($venta->fecha_emision, new DateTimeZone('America/Asuncion'))
            : new DateTime('now', new DateTimeZone('America/Asuncion'));
        $factura->setFechaEmision($fechaEmision);

        $emisor = RucParaguayo::validar((string) $this->config->ruc_emisor, $this->config->dv);
        $rucEmisor = $emisor['ruc'];

        // Datos del emisor (quien emite la factura, configurado en SIFEN)
        $factura->setEmisor(
            rucEmisor: $rucEmisor,
            dv: (int)$emisor['dv'],
            tipoContribuyente: $this->config->tipo_contribuyente ?: EmisRecTipCont::PersonaJuridica,
            tipoRegimen: $this->config->tipo_regimen,
            nombreEmisor: $this->config->razon_social ?: 'Sin Razon Social',
            nombreFantasia: null,
            callePrincipal: $this->config->calle_principal ?: ($this->config->direccion ?: 'Sin direccion'),
            casaNro: $this->config->numero_casa ?: '0',
            calleSecundaria: $this->config->calle_secundaria,
            complementoDir: $this->config->complemento_direccion,
            codDep: $this->config->departamento_codigo ? (int)$this->config->departamento_codigo : 1,
            codDistrito: $this->config->distrito_codigo ? (int)$this->config->distrito_codigo : 1,
            codCiud: $this->config->ciudad_codigo ? (int)$this->config->ciudad_codigo : 1,
            telefono: $this->config->telefono ?: '000000000',
            email: $this->config->email ?: 'email@empresa.com',
            nombreSucursal: $this->config->nombre_sucursal,
        );

        // Actividad económica principal del emisor
        $factura->addEmisorActividadEconomica(
            codigo: (int)$this->config->actividad_economica_codigo,
            descripcion: $this->config->actividad_economica_descripcion ?: 'ACTIVIDADES DE PROGRAMACION Y CONSULTORIA INFORMATICAS',
        );

        // Datos del comprador (receptor)
        $this->setReceptor($factura, $cliente);

        $factura->setTipoDeTransaccion(OpeComTipTrans::VentaMercaderia);
        $factura->setIndicadorPresencia(CamFEIndPres::Presencial);

        // Agregar cada línea de detalle como item de la factura
        foreach ($detalles as $detalle) {
            $this->addItem($factura, $detalle);
        }

        // Agregar pago: monto total en efectivo, moneda PYG
        $montoTotal = (float)$venta->total;
        $factura->addPago(
            tipoDePago: PaConEIniTiPago::Efectivo,
            monto: number_format($montoTotal, 0, '.', ''),
            moneda: 'PYG',
        );

        $factura->calcTotSub();

        return $factura;
    }

    /**
     * Configura el receptor (cliente) de la factura.
     * Si el cliente tiene RUC válido → se emite como B2B (contribuyente).
     * Si no → se emite como B2C (Consumidor Final).
     */
    protected function setReceptor(Factura $factura, ?Cliente $cliente): void
    {
        $rucRec = '';
        $dvRec = null;
        $esContribuyente = false;

        if ($cliente && $cliente->ruc) {
            $receptor = RucParaguayo::validar((string) $cliente->ruc, $cliente->dv);
            $rucRec = $receptor['ruc'];
            $dvRec = (int) $receptor['dv'];
            $esContribuyente = true;
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

    /**
     * Agrega una línea de detalle (item) a la factura.
     * Calcula la tasa de IVA según el tipo_impuesto del detalle:
     *   exenta → 0% (Exento)
     *   5 → IVA 5%
     *   10 → IVA 10%
     * También mapea la unidad de medida al catálogo SET.
     */
    protected function addItem(Factura $factura, $detalle): void
    {
        $tasaIVA = $this->getTasaIVA($detalle->tipo_impuesto);
        $afectIVA = $tasaIVA === 0 ? CamIVAAfecIVA::Exento : CamIVAAfecIVA::Gravado;
        $tasaEnum = match ($tasaIVA) {
            5 => CamIVATasaIVA::IVA5,
            10 => CamIVATasaIVA::IVA10,
            default => CamIVATasaIVA::ExentoExonerado,
        };

        $cantidad = number_format((float)$detalle->cantidad, 4, '.', '');
        $precioUnit = number_format((float)$detalle->cantidad > 0
            ? (float)$detalle->monto / (float)$detalle->cantidad : (float)$detalle->precio_u, 8, '.', '');

        // Unidad de medida: por defecto 77 (UNI), se busca en catálogo SET
        $codUnidadMedida = 77;
        $desUnidadMedida = 'UNI';
        if ($detalle->producto && $detalle->producto->unidaddemedida) {
            $nombreUnidad = strtolower(trim($detalle->producto->unidaddemedida->descripcion));
            $codUnidadMedida = match ($nombreUnidad) {
                'kg', 'kilo', 'kilogramo', 'kilogramos' => 83,
                'g', 'gr', 'gramo', 'gramos' => 86,
                'lt', 'l', 'litro', 'litros' => 89,
                'ml', 'mililitro', 'mililitros' => 88,
                'm', 'mt', 'metro', 'metros' => 87,
                'm2' => 109,
                'm3' => 110,
                'cm', 'centimetro' => 91,
                'ton', 'tonelada' => 99,
                default => 77,
            };
        }

        $factura->addItem(
            codigo: (string)$detalle->id_producto,
            descripcion: $detalle->descripcion ?: 'Producto',
            codUnidadMedida: $codUnidadMedida,
            cantidad: $cantidad,
            precisionMoneda: 0,
            precioUnit: $precioUnit,
            totalBruto: null,
            afectIVA: $afectIVA,
            proporcionGravadaIVA: $tasaIVA === 0 ? '0' : '100',
            tasaDeIVA: $tasaEnum,
        );
    }

    /**
     * Procesa la respuesta de SET después de enviar un DE.
     * Extrae:
     *   - CDC (Código de Control): identificador único del documento en SET
     *   - CDE (Código de Documento Electrónico): número de autorización
     *   - Estado: aprobado, pendiente o rechazado según el protocolo DNIT
     * Guarda estos valores en la venta (columnas sifen_cdc, sifen_cde, sifen_estado).
     */
    protected function procesarRespuesta($respuesta, Venta $venta): array
    {
        $resultado = [
            'estado' => 'rechazado',
            'cde' => null,
            'cdc' => $venta->sifen_cdc,
            'mensaje' => '',
            'codigo' => null,
            'detalles' => [],
        ];

        try {
            // Conserva la respuesta original antes de acceder a propiedades
            // opcionales de la biblioteca (un rechazo puede omitir id/protocolo).
            try {
                Storage::disk('local')->put(
                    'sifen/respuestas/venta_' . $venta->id . '_' . uniqid('', true) . '.json',
                    json_encode($respuesta, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            } catch (\Throwable $auditoriaError) {
                Log::warning('No se pudo guardar la respuesta original SIFEN.', ['venta_id' => $venta->id]);
            }
            // RProtDe = Protocolo de recepción del DE
            $prot = $respuesta->getRProtDe();
            $cdcRespuesta = isset($prot->id) ? $prot->id : null;
            if ($cdcRespuesta && preg_match('/^\d{44}$/', $cdcRespuesta)) {
                if ($venta->sifen_cdc && $venta->sifen_cdc !== $cdcRespuesta) {
                    throw new Exception('El CDC de la respuesta no corresponde a esta venta.');
                }
                $resultado['cdc'] = $cdcRespuesta;
            }
            $estadoDnit = mb_strtolower(trim($prot->dEstRes ?? ''));

            // GResProc = grupo de resultado de procesamiento
            $gResProc = $prot->gResProc ?? [];
            foreach ($gResProc as $detalle) {
                $resultado['detalles'][] = ['codigo' => (string) ($detalle->dCodRes ?? ''), 'mensaje' => (string) ($detalle->dMsgRes ?? '')];
            }
            if (!empty($gResProc)) {
                $proc = $gResProc[0];
                $msgRes = $proc->dMsgRes ?? 'DNIT no devolvió un mensaje de procesamiento.';
                $resultado['mensaje'] = $msgRes;
                $resultado['codigo'] = isset($proc->dCodRes) ? (string) $proc->dCodRes : null;

                // El estado del protocolo determina el resultado del documento.
                if (in_array($estadoDnit, ['aprobado', 'aprobado con obs.', 'aprobado con observaciones'], true)) {
                    $resultado['estado'] = 'autorizado';
                    $resultado['cde'] = isset($prot->dProtAut) ? $prot->getDProtAut() : null;
                } elseif (in_array($estadoDnit, ['pendiente', 'en procesamiento'], true)) {
                    $resultado['estado'] = 'pendiente';
                } else {
                    $resultado['estado'] = 'rechazado';
                }
            } else {
                $resultado['estado'] = 'error';
                $resultado['mensaje'] = 'La respuesta de SIFEN no contiene resultados de procesamiento. Se conservó la respuesta original para revisión.';
            }

            // Persistir en la BD
            $venta->sifen_cdc = $resultado['cdc'];
            $venta->sifen_cde = $resultado['cde'];
            $venta->sifen_estado = $resultado['estado'];
            $venta->sifen_codigo = $resultado['codigo'];
            $venta->sifen_mensaje = $resultado['mensaje'];
            $venta->sifen_respuesta = $resultado['detalles'];
            $venta->sifen_ambiente = $this->config->ambiente;
            $venta->sifen_actualizado_at = now();
            $venta->save();
        } catch (\Throwable $e) {
            $resultado['estado'] = 'error';
            $resultado['mensaje'] = 'Error al procesar respuesta: ' . $e->getMessage();
            $this->registrarError($venta, $resultado['mensaje']);
            Log::error('SifenPkuatiaService::procesarRespuesta: ' . $e->getMessage());
        }

        return $resultado;
    }

    public function registrarError(Venta $venta, string $mensaje): void
    {
        foreach ([$this->config->certificado_password, $this->config->csc_codigo] as $secreto) {
            if (is_string($secreto) && $secreto !== '') {
                $mensaje = str_replace($secreto, '[oculto]', $mensaje);
            }
        }
        $venta->sifen_estado = 'error';
        $venta->sifen_codigo = null;
        $venta->sifen_mensaje = mb_substr($mensaje, 0, 10000);
        $venta->sifen_respuesta = null;
        $venta->sifen_ambiente = $this->config->ambiente;
        $venta->sifen_actualizado_at = now();
        $venta->save();
    }

    /**
     * ============================================================
     *  CANCELACIÓN / ANULACIÓN DE FACTURA ELECTRÓNICA
     * ============================================================
     * Envía un evento de cancelación a SET para un DE ya emitido.
     * Requiere que la venta tenga un CDC (es decir, ya fue autorizada).
     * SET procesa la cancelación y el DE queda como "cancelado".
     */
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
            // Llama al web service de SET para cancelar el DE por su CDC
            $respuesta = Sifen::CancelarDE($venta->sifen_cdc, $motivo);
            $venta->sifen_estado = 'cancelado';
            $venta->save();

            Log::info('SIFEN DE cancelado exitosamente', [
                'venta_id' => $venta->id,
                'cdc' => $venta->sifen_cdc,
            ]);

            return [
                'estado' => 'cancelado',
                'mensaje' => 'Evento de cancelacion enviado correctamente.',
            ];
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::cancelarDE: ' . $e->getMessage());
            throw $e;
        }
    }

    /** Consulta los datos de un RUC en la SET (razón social, dirección, DV) */
    public function consultarRUC(string $ruc): ?array
    {
        $numero = RucParaguayo::numeroConsulta($ruc);
        $this->ensureInitialized();

        try {
            $respuesta = $this->consultarNumeroRuc($numero);
            $rContRuc = $respuesta->getRContRuc();

            if (isset($rContRuc->dRUCCons)) {
                return [
                    'ruc' => $rContRuc->dRUCCons,
                    'dv' => RucParaguayo::calcularDv($rContRuc->dRUCCons),
                    'dv_origen' => 'calculado_modulo11',
                    'razon_social' => $rContRuc->dRazCons,
                    'contribuyente' => true,
                    'encontrado' => true,
                    'existencia_verificada' => true,
                    'codigo_resultado' => $respuesta->getDCodRes(),
                    'mensaje' => $respuesta->getDMsgRes(),
                ];
            }

            return ['ruc' => $numero, 'contribuyente' => false, 'encontrado' => false,
                'existencia_verificada' => (int) $respuesta->getDCodRes() === 500,
                'codigo_resultado' => $respuesta->getDCodRes(), 'mensaje' => $respuesta->getDMsgRes()];
        } catch (\Throwable $e) {
            Log::warning('SifenPkuatiaService::consultarRUC: ' . $e->getMessage());
            return ['ruc' => $numero, 'contribuyente' => false, 'encontrado' => false,
                'existencia_verificada' => false, 'codigo_resultado' => null,
                'mensaje' => 'No se pudo verificar la existencia del RUC en SIFEN: ' . $e->getMessage()];
        }
    }

    protected function consultarNumeroRuc(string $numero)
    {
        return Sifen::ConsultarRUC($numero);
    }

    /** Consulta el estado de un DE en SET por su CDC */
    public function consultarCDC(string $cdc): ?array
    {
        $this->ensureInitialized();

        try {
            $resultado = Sifen::ConsultarDE($cdc);

            return [
                'cdc' => $cdc,
                'codigo_resultado' => $resultado->getDCodRes(),
                'estado' => $resultado->getDMsgRes() ?? 'Consultado',
                'respuesta' => $resultado,
            ];
        } catch (Exception $e) {
            Log::warning('SifenPkuatiaService::consultarCDC: ' . $e->getMessage());
            return null;
        }
    }

    /** Consulta el resultado de procesamiento de un lote de DEs previamente enviado */
    public function consultarLote(int $nroLote): array
    {
        $this->ensureInitialized();

        try {
            $respuesta = Sifen::ConsultaLote($nroLote);

            $resultados = [];
            foreach ($respuesta->getGResProcLote() as $item) {
                $resultados[] = [
                    'cdc' => $item->getId(),
                    'estado' => $item->getDEstRes(),
                ];
            }

            return $resultados;
        } catch (Exception $e) {
            Log::warning('SifenPkuatiaService::consultarLote: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Inutiliza un rango de numeración en SET.
     * Se usa cuando se pierden talonarios o se cambia de numeración.
     */
    public function inutilizarNumeros(
        int $timbrado,
        int $nroEstablecimiento,
        int $nroPuntoEmision,
        int $nroDocumentoInicial,
        int $nroDocumentoFinal,
        int $tipoDE = 1,
        string $motivo = ''
    ): array {
        $this->ensureInitialized();

        $motivo = $motivo ?: 'Cancelacion de rango de numeracion por configuracion';

        try {
            $respuesta = Sifen::InutilizarNumeros(
                timbrado: $timbrado,
                nroEstablecimiento: $nroEstablecimiento,
                nroPuntoEmision: $nroPuntoEmision,
                nroDocumentoInicial: $nroDocumentoInicial,
                nroDocumentoFinal: $nroDocumentoFinal,
                tipoDE: $tipoDE,
                motivo: $motivo
            );

            Log::info('SIFEN rango de numeracion inutilizado exitosamente', [
                'timbrado' => $timbrado,
                'establecimiento' => $nroEstablecimiento,
                'punto_emision' => $nroPuntoEmision,
                'desde' => $nroDocumentoInicial,
                'hasta' => $nroDocumentoFinal,
            ]);

            return [
                'estado' => 'procesado',
                'mensaje' => 'Rango de numeracion inutilizado correctamente.',
            ];
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::inutilizarNumeros: ' . $e->getMessage());
            throw $e;
        }
    }

    public function emitirContingencia(Venta $venta): array
    {
        if (!$this->isHabilitado()) {
            throw new Exception('SIFEN no esta habilitado.');
        }

        $errores = $this->validarConfiguracion();
        if (!empty($errores)) {
            throw new Exception('Errores de configuracion SIFEN: ' . implode(' | ', $errores));
        }

        try {
            $this->ensureInitialized();

            $factura = $this->buildFactura($venta);
            $rde = $factura->facturaToRDE();
            $rde->gOpeDE->iTipEmi = OpeDETipEmi::Contingencia->value;
            $rde->gOpeDE->dDesTipEmi = OpeDETipEmi::Contingencia->getDescription();

            $fechaFirma = new DateTime('now', new DateTimeZone('America/Asuncion'));
            $xmlFirmado = Sifen::FirmarDE($rde, $fechaFirma);

            $this->auditarXML($venta, $xmlFirmado, 'contingencia_enviado');

            $respuesta = Sifen::EnviarDE($xmlFirmado);

            $resultado = $this->procesarRespuesta($respuesta, $venta);

            $this->auditarRespuesta($venta, $resultado);

            return $resultado;
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::emitirContingencia - Error: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function enviarLote(array $lote): array
    {
        $this->ensureInitialized();

        try {
            $respuesta = Sifen::EnviarLoteDE($lote);

            return [
                'estado' => 'enviado',
                'nro_lote' => $respuesta->getdProtConsLote(),
                'mensaje' => $respuesta->getDMsgRes(),
            ];
        } catch (Exception $e) {
            Log::error('SifenPkuatiaService::enviarLote: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getTasaIVA(?string $tipoImpuesto): int
    {
        if ($tipoImpuesto === null || trim($tipoImpuesto) === '') {
            return 10;
        }
        $tipo = strtolower(trim($tipoImpuesto));
        if (in_array($tipo, ['exenta', 'exento', '0', '0%']) || (is_numeric($tipo) && (float) $tipo === 0.0)) {
            return 0;
        }
        if (in_array($tipo, ['5', '5%']) || (is_numeric($tipo) && (float) $tipo === 5.0)) {
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
