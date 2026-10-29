<?php

namespace Tests\Feature;

use App\Models\SifenConfiguracion;
use App\Models\Venta;
use App\Services\SifenPkuatiaService;
use Illuminate\Support\Facades\Storage;
use IonysDev\Pkuatia\Core\Config;
use IonysDev\Pkuatia\Core\Fields\Response\DE\RContDe;
use IonysDev\Pkuatia\Core\Responses\RResEnviConsDe;
use IonysDev\Pkuatia\Sifen;
use Tests\TestCase;

class SifenReenvioTest extends TestCase
{
    private const CDC = '01801787181001001000000822026102916611293676';

    /** Servicio sin red: la consulta de CDC devuelve lo que indique la prueba. */
    private function servicio(?array $consulta): SifenPkuatiaService
    {
        $servicio = new class extends SifenPkuatiaService {
            public ?array $consulta = null;
            public int $consultas = 0;
            public function __construct()
            {
                $this->config = new SifenConfiguracion(['ambiente' => 1]);
            }
            public function consultarCDC(string $cdc): ?array
            {
                $this->consultas++;
                return $this->consulta;
            }
            public function enviarPrueba(Venta $venta, string $xml): array
            {
                return $this->enviarFirmado($venta, $xml, 'enviado');
            }
            protected function auditarXML(Venta $venta, string $xml, string $tipo): void {}
        };
        $servicio->consulta = $consulta;
        return $servicio;
    }

    private function venta(?string $estado, ?string $cdc = self::CDC): Venta
    {
        $venta = new class extends Venta {
            public function save(array $options = []) { return true; }
        };
        $venta->id = 68;
        $venta->sifen_estado = $estado;
        $venta->sifen_cdc = $cdc;
        return $venta;
    }

    public function test_envio_sin_respuesta_aprobado_en_sifen_no_se_reenvia(): void
    {
        $respuesta = (new RResEnviConsDe())->setDCodRes(422)->setDMsgRes('CDC encontrado')
            ->setRContDe((new RContDe())->setDProtAut('1234567890'));
        $servicio = $this->servicio(['codigo_resultado' => 422, 'estado' => 'CDC encontrado', 'respuesta' => $respuesta]);
        $venta = $this->venta('sin_respuesta');

        $resultado = $servicio->verificarEnvioAnterior($venta);

        $this->assertSame('autorizado', $resultado['estado']);
        $this->assertSame('autorizado', $venta->sifen_estado);
        $this->assertSame('1234567890', $venta->sifen_cde);
        $this->assertSame(self::CDC, $venta->sifen_cdc);
    }

    public function test_cdc_no_registrado_en_sifen_permite_emitir_de_nuevo(): void
    {
        $respuesta = (new RResEnviConsDe())->setDCodRes(420)->setDMsgRes('Documento No Existe en SIFEN o ha sido Rechazado');
        $servicio = $this->servicio(['codigo_resultado' => 420, 'estado' => $respuesta->getDMsgRes(), 'respuesta' => $respuesta]);

        $this->assertNull($servicio->verificarEnvioAnterior($this->venta('error')));
    }

    public function test_si_no_se_puede_consultar_no_se_genera_otro_documento(): void
    {
        $servicio = $this->servicio(null);
        $venta = $this->venta('sin_respuesta');

        $resultado = $servicio->verificarEnvioAnterior($venta);

        $this->assertSame('sin_respuesta', $resultado['estado']);
        $this->assertSame('sin_respuesta', $venta->sifen_estado);
        $this->assertSame(self::CDC, $venta->sifen_cdc);
        $this->assertStringContainsString('No se generó un documento nuevo', $venta->sifen_mensaje);
    }

    public function test_rechazo_definitivo_o_venta_sin_cdc_no_consultan(): void
    {
        $servicio = $this->servicio(null);
        $this->assertNull($servicio->verificarEnvioAnterior($this->venta('rechazado')));
        $this->assertNull($servicio->verificarEnvioAnterior($this->venta('por_cobrar', null)));
        $this->assertSame(0, $servicio->consultas);
    }

    public function test_corte_de_conexion_queda_sin_respuesta_con_el_cdc_nuevo(): void
    {
        Storage::fake('local');
        $config = new Config();
        $archivoDid = tempnam(sys_get_temp_dir(), 'did_test_');
        $config->dIdFilePath = $archivoDid;
        $anteriores = [];
        foreach (['config' => $config, 'options' => []] as $nombre => $valor) {
            $p = new \ReflectionProperty(Sifen::class, $nombre);
            if ($p->isInitialized()) $anteriores[$nombre] = $p->getValue();
            $p->setValue(null, $valor);
        }
        $soap = new class extends \SoapClient {
            public function __construct() {}
            public function rEnviDe($request): never
            {
                throw new \SoapFault('HTTP', 'Error Fetching http headers');
            }
        };
        Sifen::SetSoapClientFactory(fn () => $soap);
        try {
            $venta = $this->venta('error', '01801787181001001000000822026102911904043261');
            $xml = '<rDE xmlns="http://ekuatia.set.gov.py/sifen/xsd"><DE Id="' . self::CDC . '"/></rDE>';

            $resultado = $this->servicio(null)->enviarPrueba($venta, $xml);

            $this->assertSame('sin_respuesta', $resultado['estado']);
            $this->assertSame('sin_respuesta', $venta->sifen_estado);
            $this->assertSame(self::CDC, $venta->sifen_cdc);
            $this->assertStringContainsString('Error Fetching http headers', $venta->sifen_mensaje);
        } finally {
            Sifen::SetSoapClientFactory(null);
            foreach ($anteriores as $nombre => $valor) {
                (new \ReflectionProperty(Sifen::class, $nombre))->setValue(null, $valor);
            }
            if (is_file($archivoDid)) unlink($archivoDid);
        }
    }
}
