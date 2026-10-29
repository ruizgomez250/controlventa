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
use IonysDev\Pkuatia\Core\Constants\TipIDRec;
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

    /** @var \SoapClient|null Último cliente SOAP creado; conserva la respuesta HTTP cruda (trace) */
    protected static ?\SoapClient $ultimoClienteSoap = null;

    /** Estados en los que SIFEN pudo haber recibido el último CDC sin que se conozca el resultado */
    protected const ESTADOS_SIN_CONFIRMAR = ['sin_respuesta', 'enviando', 'pendiente', 'error'];

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
        $this->configurarTransporteSoap();
        $this->initialized = true;
    }

    /**
     * El balanceador de SIFEN TEST corta algunas conexiones HTTP persistentes
     * sin devolver cabeceras. Pkuatia permite inyectar la creación del cliente;
     * se conserva el contexto TLS con el certificado y se fuerza Connection: close.
     * El WSDL se toma de una copia local (wsdlLocal) para no descargarlo en cada operación.
     */
    protected function configurarTransporteSoap(): void
    {
        Sifen::SetSoapClientFactory(static function (string $wsdl, array $options): \SoapClient {
            $options['keep_alive'] = false;
            $options['connection_timeout'] = 30;

            $context = $options['stream_context'] ?? null;
            if (is_resource($context)) {
                stream_context_set_option($context, 'http', 'protocol_version', 1.0);
                stream_context_set_option($context, 'http', 'timeout', 60);
                stream_context_set_option($context, 'http', 'header', "Connection: close\r\n");
            }

            return self::$ultimoClienteSoap = new \SoapClient(self::wsdlLocal($wsdl, $context), $options);
        });
    }

    /**
     * La caché de WSDL de PHP no se relee en este servidor, así que cada operación
     * descargaba WSDL y XSD de SIFEN: conexiones extra que el balanceador corta.
     * La primera vez se guarda una copia en storage/app/sifen/wsdl/<host>/... y
     * luego solo se conecta para la operación (el endpoint está en el WSDL).
     * Para refrescar las copias basta con borrar esa carpeta.
     *
     * @param resource|null $context Contexto TLS con el certificado del emisor
     */
    protected static function wsdlLocal(string $wsdl, $context = null): string
    {
        $partes = parse_url($wsdl);
        if (($partes['scheme'] ?? '') !== 'https' || empty($partes['host']) || empty($partes['path'])) {
            return $wsdl;
        }
        $carpeta = dirname($partes['path']);
        $dir = storage_path('app/sifen/wsdl/' . $partes['host'] . $carpeta);
        $local = $dir . '/' . basename($partes['path']);
        if (is_file($local)) {
            return $local;
        }

        try {
            $base = 'https://' . $partes['host'] . $carpeta . '/';
            $archivos = [];
            $pendientes = [basename($partes['path']) => $wsdl];
            while ($pendientes) {
                $nombre = array_key_first($pendientes);
                $url = array_shift($pendientes);
                $contenido = @file_get_contents($url, false, is_resource($context) ? $context : null);
                $xml = new \DOMDocument();
                if (!is_string($contenido) || $contenido === '' || !@$xml->loadXML($contenido, LIBXML_NONET)) {
                    throw new Exception('respuesta vacía o inválida para ' . $url);
                }
                $archivos[$nombre] = $contenido;
                // Solo imports relativos a la misma carpeta (p. ej. recibe.wsdl.xsd1.xsd)
                foreach ((new \DOMXPath($xml))->query('//@schemaLocation') as $import) {
                    if (preg_match('/^[A-Za-z0-9_-][A-Za-z0-9._-]*$/D', $import->value) && !isset($archivos[$import->value])) {
                        $pendientes[$import->value] = $base . $import->value;
                    }
                }
            }
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            // El WSDL se escribe al final: su existencia indica que la copia está completa
            foreach (array_reverse($archivos, true) as $nombre => $contenido) {
                file_put_contents($dir . '/' . $nombre, $contenido);
            }
            return $local;
        } catch (\Throwable $e) {
            Log::warning('No se pudo guardar la copia local del WSDL de SIFEN; se usa el remoto.', ['wsdl' => $wsdl, 'error' => $e->getMessage()]);
            return $wsdl;
        }
    }

    /** Cabeceras y cuerpo HTTP de la última respuesta SOAP, o null si no llegó nada. */
    protected function respuestaSoapCruda(): ?string
    {
        $cliente = self::$ultimoClienteSoap;
        if (!$cliente) {
            return null;
        }
        $crudo = trim($cliente->__getLastResponseHeaders() . "\n" . $cliente->__getLastResponse());
        return $crudo === '' ? null : $crudo;
    }

    /**
     * Guarda en storage/app/sifen/respuestas la respuesta HTTP cruda de SIFEN.
     * Si no hay respuesta cruda (p. ej. en pruebas), guarda el objeto procesado.
     */
    protected function guardarRespuesta(Venta $venta, string $tipo, $respuesta = null): void
    {
        try {
            $crudo = $this->respuestaSoapCruda();
            if ($crudo === null && $respuesta === null) {
                return;
            }
            Storage::disk('local')->put(
                'sifen/respuestas/venta_' . $venta->id . '_' . $tipo . '_' . uniqid('', true) . ($crudo !== null ? '.xml' : '.json'),
                $crudo ?? json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)
            );
        } catch (\Throwable $e) {
            Log::warning('No se pudo guardar la respuesta original SIFEN.', ['venta_id' => $venta->id, 'error' => $e->getMessage()]);
        }
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
     *  4. Si un envío anterior quedó sin respuesta, consultar su CDC (verificarEnvioAnterior)
     *  5. Construir objeto Factura con datos de venta, cliente, items
     *  6. Convertir Factura → RDE (representación intermedia)
     *  7. Firmar el DE con el CSC (Sifen::FirmarDE)
     *  8. Guardar XML firmado y CDC, enviar a SET y procesar la respuesta (enviarFirmado)
     */
    public function emitir(Venta $venta): array
    {
        if (!$this->isHabilitado()) {
            throw new Exception('SIFEN no está habilitado. Configure los datos del emisor y cargue el certificado.');
        }
        if ($venta->sifen_estado === 'autorizado') {
            throw new Exception('El documento ya fue autorizado por SIFEN.');
        }

        $errores = $this->validarConfiguracion();
        if (!empty($errores)) {
            throw new Exception('Errores de configuración SIFEN: ' . implode(' | ', $errores));
        }

        try {
            $this->ensureInitialized();

            // Un envío anterior sin respuesta pudo haber sido aprobado: no generar otro CDC sin consultarlo
            $anterior = $this->verificarEnvioAnterior($venta);
            if ($anterior !== null) {
                return $anterior;
            }

            // Construir la Factura según el modelo de datos de pkuatia
            $factura = $this->buildFactura($venta);
            // Convertir a Representación de Documento Electrónico (RDE)
            $rde = $factura->facturaToRDE();
            // Firmar con CSC (Código de Seguridad del Contribuyente)
            $fechaFirma = new DateTime('now', new DateTimeZone('America/Asuncion'));
            $xmlFirmado = Sifen::FirmarDE($rde, $fechaFirma);

            return $this->enviarFirmado($venta, $xmlFirmado, 'enviado');
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
     * Registra el CDC, envía el DE firmado y procesa la respuesta.
     * Si SIFEN corta la conexión, el documento pudo haber llegado: queda
     * 'sin_respuesta' y el próximo reenvío consulta primero ese CDC.
     */
    protected function enviarFirmado(Venta $venta, string $xmlFirmado, string $tipo): array
    {
        // El CDC se guarda antes de enviar: es lo único que permite consultar un envío sin respuesta
        $document = new \DOMDocument();
        $cdc = $document->loadXML($xmlFirmado, LIBXML_NONET)
            ? (new \DOMXPath($document))->evaluate('string(//*[local-name()="DE"]/@Id)')
            : '';
        if (!preg_match('/^\d{44}$/', $cdc)) {
            throw new Exception('El XML firmado no contiene un CDC válido; no se envió a SIFEN.');
        }
        $venta->sifen_cdc = $cdc;
        $venta->sifen_estado = 'pendiente';
        $venta->sifen_ambiente = $this->config->ambiente;
        $venta->sifen_actualizado_at = now();
        $venta->save();

        // Guardar copia del XML firmado para auditoría
        $this->auditarXML($venta, $xmlFirmado, $tipo);

        self::$ultimoClienteSoap = null;
        try {
            // Enviar a la SET vía SOAP
            $respuesta = Sifen::EnviarDE($xmlFirmado);
        } catch (Exception $e) {
            $this->guardarRespuesta($venta, 'sin_respuesta');
            Log::warning('SIFEN no respondió al envío de la venta ' . $venta->id, ['cdc' => $cdc, 'error' => $e->getMessage()]);
            $this->registrarError($venta, $e->getMessage()
                . ' — El documento pudo haber llegado a SIFEN; antes de reenviar se consultará el CDC.', 'sin_respuesta');
            $resultado = ['estado' => 'sin_respuesta', 'cde' => null, 'cdc' => $cdc, 'mensaje' => $venta->sifen_mensaje, 'codigo' => null, 'detalles' => []];
            $this->auditarRespuesta($venta, $resultado);
            return $resultado;
        }

        // Extraer CDC, CDE y estado desde la respuesta
        $resultado = $this->procesarRespuesta($respuesta, $venta);
        $this->auditarRespuesta($venta, $resultado);
        return $resultado;
    }

    /** ¿El último CDC de la venta quedó sin un resultado definitivo de SIFEN? */
    public function requiereVerificacion(Venta $venta): bool
    {
        return (bool) $venta->sifen_cdc && in_array($venta->sifen_estado, self::ESTADOS_SIN_CONFIRMAR, true);
    }

    /**
     * Consulta en SIFEN el último CDC de la venta cuando no se conoce su resultado.
     * Devuelve null si SIFEN no lo tiene registrado (se puede emitir uno nuevo);
     * si lo tiene aprobado o no se pudo consultar, devuelve el resultado a informar
     * y no se debe generar otro documento.
     */
    public function verificarEnvioAnterior(Venta $venta): ?array
    {
        if (!$this->requiereVerificacion($venta)) {
            return null;
        }

        $consulta = $this->consultarCDC($venta->sifen_cdc);
        $respuesta = $consulta['respuesta'] ?? null;
        $codigo = $consulta ? sprintf('%04d', $consulta['codigo_resultado']) : null;

        if ($respuesta && ($respuesta->getXContenDE() !== null || isset($respuesta->rContDe))) {
            $rContDe = $respuesta->getRContDe();
            $venta->sifen_estado = 'autorizado';
            $venta->sifen_cde = $rContDe && isset($rContDe->dProtAut) ? $rContDe->dProtAut : null;
            $venta->sifen_codigo = $codigo;
            $venta->sifen_mensaje = 'SIFEN ya tenía aprobado este CDC (verificado por consulta). No se reenvió.';
            $venta->sifen_respuesta = [['codigo' => $codigo, 'mensaje' => (string) $consulta['estado']]];
            $venta->sifen_ambiente = $this->config->ambiente;
            $venta->sifen_actualizado_at = now();
            $venta->save();
            Log::info('SIFEN: envío anterior sin respuesta estaba aprobado', ['venta_id' => $venta->id, 'cdc' => $venta->sifen_cdc]);
            return ['estado' => 'autorizado', 'cde' => $venta->sifen_cde, 'cdc' => $venta->sifen_cdc,
                'mensaje' => $venta->sifen_mensaje, 'codigo' => $codigo, 'detalles' => $venta->sifen_respuesta];
        }

        // 0420: "Documento No Existe en SIFEN o ha sido Rechazado"
        if ($codigo === '0420') {
            Log::info('SIFEN no registra el CDC anterior; se emite un documento nuevo', ['venta_id' => $venta->id, 'cdc' => $venta->sifen_cdc]);
            return null;
        }

        $mensaje = 'No se pudo confirmar en SIFEN si el envío anterior (CDC ' . $venta->sifen_cdc . ') fue recibido'
            . ($consulta ? ' (' . $codigo . ' ' . $consulta['estado'] . ')' : '')
            . '. No se generó un documento nuevo; reintente más tarde.';
        $this->registrarError($venta, $mensaje, 'sin_respuesta');
        return ['estado' => 'sin_respuesta', 'cde' => null, 'cdc' => $venta->sifen_cdc, 'mensaje' => $mensaje, 'codigo' => null, 'detalles' => []];
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
     * El tipo de receptor se toma explícitamente del cliente. Un número de
     * cédula con DV puede parecer un RUC, pero SIFEN debe recibirlo como B2C.
     */
    protected function setReceptor(Factura $factura, ?Cliente $cliente): void
    {
        $rucRec = null;
        $dvRec = null;
        $tipoContribuyente = null;
        $tipoIdentificacion = null;
        $numeroIdentificacion = null;
        $esContribuyente = $cliente ? (bool) $cliente->es_contribuyente : false;

        if ($esContribuyente) {
            if (!$cliente?->ruc) {
                throw new Exception('El cliente contribuyente no tiene RUC configurado.');
            }
            $receptor = RucParaguayo::validar((string) $cliente->ruc, $cliente->dv);
            $rucRec = $receptor['ruc'];
            $dvRec = (int) $receptor['dv'];
            // iTiContRec: persona física (1) o jurídica (2); si el cliente no lo indica, se deduce del RUC
            $tipoContribuyente = EmisRecTipCont::tryFrom((int) $cliente->tipo_contribuyente)
                ?? EmisRecTipCont::from(RucParaguayo::tipoContribuyente($rucRec));
        } else {
            $tipoIdentificacion = (int) ($cliente?->tipo_identificacion ?: TipIDRec::CedulaParaguaya->value);
            $numeroIdentificacion = trim((string) ($cliente?->numero_identificacion ?: ''));

            // Compatibilidad con clientes antiguos: si estaban guardados en
            // ruc/dv pero se marcan no contribuyentes, la cédula es el número sin DV.
            if ($numeroIdentificacion === '' && $cliente?->ruc) {
                $numeroIdentificacion = trim((string) $cliente->ruc);
            }
            $numeroIdentificacion = RucParaguayo::numeroDocumento($tipoIdentificacion, $numeroIdentificacion);
            if ($numeroIdentificacion === '') {
                throw new Exception('El cliente no contribuyente no tiene número de identificación configurado.');
            }
        }

        $nombreRec = $cliente ? ($cliente->razonsocial ?: 'Consumidor Final') : 'Consumidor Final';

        $factura->setReceptor(
            nombre: $nombreRec,
            esContribuyente: $esContribuyente,
            tipoOperacion: $esContribuyente ? RecTiOpe::B2B : RecTiOpe::B2C,
            codPais: 'PRY',
            tipoContribuyente: $tipoContribuyente,
            ruc: $rucRec,
            dv: $dvRec,
            tipoIdentificacion: $tipoIdentificacion,
            nroIdentificacion: $numeroIdentificacion,
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
            $this->guardarRespuesta($venta, 'respuesta', $respuesta);
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

    /** $estado 'sin_respuesta' indica que el CDC pudo haber llegado a SIFEN y debe consultarse antes de reenviar. */
    public function registrarError(Venta $venta, string $mensaje, string $estado = 'error'): void
    {
        foreach ([$this->config->certificado_password, $this->config->csc_codigo] as $secreto) {
            if (is_string($secreto) && $secreto !== '') {
                $mensaje = str_replace($secreto, '[oculto]', $mensaje);
            }
        }
        $venta->sifen_estado = $estado;
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
        if ($venta->sifen_estado === 'autorizado') {
            throw new Exception('El documento ya fue autorizado por SIFEN.');
        }

        $errores = $this->validarConfiguracion();
        if (!empty($errores)) {
            throw new Exception('Errores de configuracion SIFEN: ' . implode(' | ', $errores));
        }

        try {
            $this->ensureInitialized();

            $anterior = $this->verificarEnvioAnterior($venta);
            if ($anterior !== null) {
                return $anterior;
            }

            $factura = $this->buildFactura($venta);
            // El tipo de emisión forma parte del CDC: debe fijarse antes de facturaToRDE()
            $factura->setTipoEmisionDocumento(OpeDETipEmi::Contingencia);
            $rde = $factura->facturaToRDE();

            $fechaFirma = new DateTime('now', new DateTimeZone('America/Asuncion'));
            $xmlFirmado = Sifen::FirmarDE($rde, $fechaFirma);

            return $this->enviarFirmado($venta, $xmlFirmado, 'contingencia_enviado');
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
