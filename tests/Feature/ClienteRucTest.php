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
    public function test_formulario_exige_numero_y_dv_separados(): void
    {
        $method = new \ReflectionMethod(ClienteController::class, 'rules');
        $method->setAccessible(true);
        $rules = $method->invoke(new ClienteController());
        $this->assertTrue(Validator::make(['razonsocial' => 'Prueba', 'ruc' => '80178718', 'dv' => '1'], $rules)->passes());
        foreach ([['ruc' => '80178718-1', 'dv' => '1'], ['ruc' => 'ABC', 'dv' => '1'], ['ruc' => '80178718'], ['ruc' => '80178718', 'dv' => '12']] as $invalid) {
            $this->assertTrue(Validator::make(['razonsocial' => 'Prueba'] + $invalid, $rules)->fails());
        }
    }

    public function test_formulario_completa_tipo_de_contribuyente_y_cedula_sin_dv(): void
    {
        $preparador = new class {
            use \App\Http\Controllers\Concerns\NormalizaRuc;
            public function ejecutar(\Illuminate\Http\Request $request): \Illuminate\Http\Request
            {
                $this->prepararIdentidadSifen($request);
                return $request;
            }
        };
        $empresa = $preparador->ejecutar(\Illuminate\Http\Request::create('/', 'POST', ['es_contribuyente' => '1', 'ruc' => '80178718-1']));
        $this->assertSame(2, $empresa->input('tipo_contribuyente'));
        $persona = $preparador->ejecutar(\Illuminate\Http\Request::create('/', 'POST', ['es_contribuyente' => '1', 'ruc' => '3895241', 'dv' => '6']));
        $this->assertSame(1, $persona->input('tipo_contribuyente'));
        $elegido = $preparador->ejecutar(\Illuminate\Http\Request::create('/', 'POST', ['es_contribuyente' => '1', 'ruc' => '3895241', 'dv' => '6', 'tipo_contribuyente' => '2']));
        $this->assertSame('2', $elegido->input('tipo_contribuyente'));

        $cedula = $preparador->ejecutar(\Illuminate\Http\Request::create('/', 'POST', ['es_contribuyente' => '0', 'ruc' => '1807552', 'dv' => '5']));
        $this->assertSame('1807552', $cedula->input('numero_identificacion'));
        $this->assertNull($cedula->input('tipo_contribuyente'));
        $escrita = $preparador->ejecutar(\Illuminate\Http\Request::create('/', 'POST', ['es_contribuyente' => '0', 'numero_identificacion' => '1.807.552-5']));
        $this->assertSame('1807552', $escrita->input('numero_identificacion'));
    }

    public function test_emision_envia_ruc_completo_sin_recortar_y_dv_aparte(): void
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct() {}
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
        $this->expectExceptionMessage('identificador/DV');
        $service->receptor(new Cliente(['id' => 1, 'razonsocial' => 'Prueba', 'ruc' => '80178718']));
    }
}
