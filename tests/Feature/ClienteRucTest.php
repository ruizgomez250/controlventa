<?php

namespace Tests\Feature;

use App\Http\Controllers\ClienteController;
use App\Models\Cliente;
use App\Services\SifenPkuatiaService;
use Illuminate\Support\Facades\Validator;
use IonysDev\Pkuatia\Core\Constants\CamCondOpe;
use IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura;
use Tests\TestCase;

class ClienteRucTest extends TestCase
{
    public function test_formulario_acepta_ruc_sin_dv(): void
    {
        $method = new \ReflectionMethod(ClienteController::class, 'rules');
        $method->setAccessible(true);
        $rules = $method->invoke(new ClienteController());
        $this->assertTrue(Validator::make(['razonsocial' => 'Prueba', 'ruc' => '80178718', 'dv' => '1'], $rules)->passes());
        $this->assertTrue(Validator::make(['razonsocial' => 'Prueba', 'ruc' => '80178718'], $rules)->passes());
        foreach ([['ruc' => '80178718-1', 'dv' => '1'], ['ruc' => 'ABC', 'dv' => '1'], ['ruc' => '80178718', 'dv' => '12']] as $invalid) {
            $this->assertTrue(Validator::make(['razonsocial' => 'Prueba'] + $invalid, $rules)->fails());
        }
    }

    public function test_normalizacion_permite_omitir_dv_del_cliente(): void
    {
        $normalizador = new class {
            use \App\Http\Controllers\Concerns\NormalizaRuc;

            public function normalizar(\Illuminate\Http\Request $request): void
            {
                $this->normalizarRuc($request, dvOpcional: true);
            }
        };
        foreach ([null, '', ' '] as $dv) {
            $request = new \Illuminate\Http\Request(['ruc' => '80178718', 'dv' => $dv]);
            $normalizador->normalizar($request);
            $this->assertSame('80178718', $request->input('ruc'));
            $this->assertNull($request->input('dv'));
        }
        $request = new \Illuminate\Http\Request(['ruc' => '80178718-1']);
        $normalizador->normalizar($request);
        $this->assertSame('1', $request->input('dv'));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $normalizador->normalizar(new \Illuminate\Http\Request(['ruc' => '80178718', 'dv' => '2']));
    }

    public function test_emision_envia_ruc_completo_sin_recortar_y_dv_aparte(): void
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct(int $ambiente = 2)
            {
                $this->config = new \App\Models\SifenConfiguracion(['ambiente' => $ambiente]);
            }
            public function receptor(Cliente $cliente): Factura
            {
                $factura = new Factura(CamCondOpe::Contado);
                $this->setReceptor($factura, $cliente);
                return $factura;
            }
        };
        foreach ([['80178718', '1'], ['123456', '0']] as [$ruc, $dv]) {
            $cliente = new Cliente(['id' => 1, 'razonsocial' => 'Prueba', 'ruc' => $ruc, 'dv' => $dv]);
            $factura = $service->receptor($cliente);
            $this->assertSame($ruc, $factura->gDatRec->getDRucRec());
            $this->assertSame((int) $dv, $factura->gDatRec->getDDVRec());
            $this->assertSame($ruc . '-' . $dv, $cliente->ruc_completo);
        }
        $factura = $service->receptor(new Cliente(['id' => 1, 'razonsocial' => 'Prueba', 'ruc' => '2535470']));
        $this->assertSame('2535470', $factura->gDatRec->getDNumIDRec());
        $this->assertSame(1, $factura->gDatRec->getITipIDRec());
        $this->assertSame(2, $factura->gDatRec->getINatRec());
        $this->assertNull($factura->gDatRec->getDRucRec());
        $this->assertFalse((new \ReflectionProperty($factura->gDatRec, 'dDVRec'))->isInitialized($factura->gDatRec));
        $this->assertSame('Prueba', $factura->gDatRec->getDNomRec());

        $testService = new ($service::class)(1);
        foreach ([['80178718', '1'], ['123456', '0'], ['80178718-1', null]] as [$numero, $dv]) {
            $cliente = new Cliente(['id' => 1, 'razonsocial' => 'Cliente TEST', 'ruc' => $numero, 'dv' => $dv]);
            $facturaTest = $testService->receptor($cliente);
            $this->assertSame(explode('-', $numero)[0], $facturaTest->gDatRec->getDNumIDRec());
            $this->assertSame(2, $facturaTest->gDatRec->getINatRec());
            $this->assertSame(2, $facturaTest->gDatRec->getITiOpe());
            $this->assertSame(1, $facturaTest->gDatRec->getITipIDRec());
            $xml = new \DOMDocument();
            $xml->appendChild($facturaTest->gDatRec->toDOMElement($xml));
            $this->assertStringNotContainsString('dRucRec', $xml->saveXML());
            $this->assertStringNotContainsString('dDVRec', $xml->saveXML());
            $this->assertSame($dv, $cliente->dv);
        }

        $this->expectException(\InvalidArgumentException::class);
        $service->receptor(new Cliente(['id' => 1, 'ruc' => '80178718', 'dv' => '2']));
    }
}
