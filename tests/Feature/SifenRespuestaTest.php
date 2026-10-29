<?php

namespace Tests\Feature;

use App\Models\SifenConfiguracion;
use App\Models\Venta;
use App\Services\SifenPkuatiaService;
use Illuminate\Support\Facades\Storage;
use IonysDev\Pkuatia\Core\Fields\Response\GResProc;
use IonysDev\Pkuatia\Core\Fields\Response\RProtDe;
use IonysDev\Pkuatia\Core\Responses\RRetEnviDe;
use Tests\TestCase;

class SifenRespuestaTest extends TestCase
{
    private function procesar(RProtDe $protocolo): array
    {
        Storage::fake('local');
        $servicio = new class extends SifenPkuatiaService {
            public function __construct()
            {
                $this->config = new SifenConfiguracion(['ambiente' => 1]);
            }

            public function procesarPrueba($respuesta, Venta $venta): array
            {
                return $this->procesarRespuesta($respuesta, $venta);
            }
        };
        $venta = new class extends Venta {
            public function save(array $options = []) { return true; }
        };
        $venta->id = 1;
        $venta->sifen_cdc = str_repeat('1', 44);
        $respuesta = (new RRetEnviDe())->setRProtDe($protocolo);
        $resultado = $servicio->procesarPrueba($respuesta, $venta);
        $this->assertSame(str_repeat('1', 44), $venta->sifen_cdc);
        $this->assertCount(1, Storage::disk('local')->files('sifen/respuestas'));
        $this->assertSame($resultado['estado'], $venta->sifen_estado);
        $this->assertSame($resultado['mensaje'], $venta->sifen_mensaje);
        return $resultado;
    }

    public function test_rechazo_sin_id_conserva_el_cdc_y_todos_los_mensajes(): void
    {
        $protocolo = new RProtDe(); // id y dProtAut quedan sin inicializar.
        $protocolo->dEstRes = 'Rechazado';
        $protocolo->gResProc = [
            (new GResProc())->setDCodRes('1234')->setDMsgRes('Validación de prueba'),
            (new GResProc())->setDCodRes('1235')->setDMsgRes('Segunda validación de prueba'),
        ];
        $resultado = $this->procesar($protocolo);
        $this->assertSame('rechazado', $resultado['estado']);
        $this->assertSame('1234', $resultado['codigo']);
        $this->assertSame('Validación de prueba', $resultado['mensaje']);
        $this->assertCount(2, $resultado['detalles']);
    }

    public function test_aprobacion_sin_protocolo_opcional_no_produce_error_php(): void
    {
        $protocolo = new RProtDe();
        $protocolo->id = str_repeat('1', 44);
        $protocolo->dEstRes = 'Aprobado';
        $protocolo->gResProc = [(new GResProc())->setDCodRes('0260')->setDMsgRes('Aprobado')];
        $resultado = $this->procesar($protocolo);
        $this->assertSame('autorizado', $resultado['estado']);
        $this->assertNull($resultado['cde']);
    }

    public function test_respuesta_incompleta_se_registra_como_error_sin_borrar_cdc(): void
    {
        $resultado = $this->procesar(new RProtDe());
        $this->assertSame('error', $resultado['estado']);
        $this->assertStringContainsString('no contiene resultados', $resultado['mensaje']);
    }
}
