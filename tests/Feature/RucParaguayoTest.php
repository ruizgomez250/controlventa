<?php

namespace Tests\Feature;

use App\Helpers\RucParaguayo;
use App\Http\Controllers\Concerns\NormalizaRuc;
use App\Models\Cliente;
use App\Models\SifenConfiguracion;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\SifenPkuatiaService;
use Illuminate\Http\Request;
use IonysDev\Pkuatia\Core\Config;
use IonysDev\Pkuatia\Sifen;
use Tests\TestCase;

class RucParaguayoTest extends TestCase
{
    public function test_separa_por_guion_y_elimina_espacios_sin_perder_digitos(): void
    {
        $esperado = ['ruc' => '2535470', 'dv' => '1'];
        $this->assertSame($esperado, RucParaguayo::splitRuc('2535470-1'));
        $this->assertSame($esperado, RucParaguayo::splitRuc(" 2 535 470\t-\n1\u{00A0}"));
        $this->assertSame($esperado, RucParaguayo::splitRuc('2535470', '1'));
        $this->assertSame('2535470', RucParaguayo::numeroConsulta(' 2535470 - 1 '));
        $this->assertSame('2535470', RucParaguayo::numeroConsulta('2535470'));
    }

    public function test_rechaza_letras_guiones_extra_dv_multiple_y_longitudes_invalidas(): void
    {
        foreach (['ABC2535470-1', '253A470-1', '2535470-a', '2535470-', '2535470--1', '253-5470-1', '2535470-11', '1234-0', '123456789-1', '25354701'] as $entrada) {
            try {
                RucParaguayo::splitRuc($entrada);
                $this->fail('Se aceptó un formato inválido: ' . $entrada);
            } catch (\InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
        $this->expectException(\InvalidArgumentException::class);
        RucParaguayo::splitRuc('2535470-1', '2');
    }

    public function test_formato_correcto_no_significa_dv_correcto_ni_existencia(): void
    {
        $this->assertSame('9', RucParaguayo::splitRuc('2535470-9')['dv']);
        $this->assertSame('1', RucParaguayo::calcularDv('2535470'));
        $this->assertSame('0', RucParaguayo::validar('123456-0')['dv']);
        $this->expectExceptionMessage('no coincide');
        RucParaguayo::validar('2535470-9');
    }

    public function test_controlador_normaliza_entrada_visible_y_emisor_con_la_misma_funcion(): void
    {
        $normalizador = new class {
            use NormalizaRuc;
            public function ejecutar(Request $request, string $ruc = 'ruc', string $dv = 'dv'): void
            {
                $this->normalizarRuc($request, $ruc, $dv);
            }
        };
        foreach ([['ruc', 'dv'], ['sifen_ruc_emisor', 'sifen_dv']] as [$campo, $campoDv]) {
            $request = Request::create('/', 'POST', [$campo => ' 2535470 - 1 ']);
            $normalizador->ejecutar($request, $campo, $campoDv);
            $this->assertSame('2535470', $request->input($campo));
            $this->assertSame('1', $request->input($campoDv));
        }
    }

    public static function facturaDePrueba(): \IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct()
            {
                $this->config = new SifenConfiguracion([
                    'ruc_emisor' => ' 2535470 - 1 ', 'razon_social' => 'Emisor de prueba',
                    'establecimiento' => '001', 'punto_expedicion' => '001',
                    'calle_principal' => 'Calle de prueba', 'numero_casa' => '1',
                    'departamento_codigo' => 10, 'distrito_codigo' => 125, 'ciudad_codigo' => 3889,
                    'telefono' => '000000000', 'email' => 'pruebas@example.invalid',
                    'tipo_contribuyente' => 2, 'actividad_economica_codigo' => 47190,
                    'actividad_economica_descripcion' => 'COMERCIO AL POR MENOR',
                ]);
            }
            public function construir(Venta $venta) { return $this->buildFactura($venta); }
        };
        $venta = new Venta(['fecha_emision' => '2026-10-29 10:00:00', 'numero_factura' => '0000001',
            'timbrado_factura' => '2535470', 'tipo_comprobante' => 'CONTADO', 'total' => 11000]);
        $venta->sifen_fecha_inicio_timbrado = '2026-10-15';
        $venta->setRelation('cliente', new Cliente(['id' => 1, 'razonsocial' => 'Receptor de prueba', 'ruc' => ' 2535470 - 1 ']));
        $detalle = new VentaDetalle(['id_producto' => 1, 'descripcion' => 'Artículo de prueba', 'cantidad' => 1,
            'precio_u' => 10000, 'monto' => 11000, 'tipo_impuesto' => '10']);
        $detalle->setRelation('producto', null);
        $venta->setRelation('detalles', collect([$detalle]));
        return $service->construir($venta);
    }

    public function test_xml_real_serializa_emisor_y_receptor_separados(): void
    {
        $factura = self::facturaDePrueba();
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $root = $doc->appendChild($doc->createElement('identificacionDePrueba'));
        $root->appendChild($factura->gEmis->toDOMElement($doc));
        $root->appendChild($factura->gDatRec->toDOMElement($doc));
        $xml = $doc->saveXML();
        foreach (['<dRucRec>2535470</dRucRec>', '<dDVRec>1</dDVRec>', '<dRucEm>2535470</dRucEm>', '<dDVEmi>1</dDVEmi>'] as $campo) {
            $this->assertStringContainsString($campo, $xml);
        }
        $this->assertStringNotContainsString('<dRucRec>2535470-1</dRucRec>', $xml);
        $this->assertStringNotContainsString('<dRucRec>25354701</dRucRec>', $xml);
    }

    public function test_xml_serializa_a_jorge_como_no_contribuyente_con_cedula_sin_dv(): void
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct() {}
            public function receptor(\IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura $factura, Cliente $cliente): void
            {
                $this->setReceptor($factura, $cliente);
            }
        };
        $factura = new \IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura(
            \IonysDev\Pkuatia\Core\Constants\CamCondOpe::Contado
        );
        $cliente = new Cliente([
            'id' => 9,
            'razonsocial' => 'Jorge Ariel Ruiz Gomez',
            'ruc' => '1807552',
            'dv' => '5',
            'es_contribuyente' => false,
            'tipo_identificacion' => 1,
            'numero_identificacion' => '1807552-5',
        ]);

        $service->receptor($factura, $cliente);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->appendChild($factura->gDatRec->toDOMElement($doc));
        $xml = $doc->saveXML();

        // El dato guardado trae el DV del RUC; la cédula paraguaya se envía sin él.
        foreach (['<iTiOpe>2</iTiOpe>', '<iNatRec>2</iNatRec>', '<iTipIDRec>1</iTipIDRec>', '<dNumIDRec>1807552</dNumIDRec>'] as $campo) {
            $this->assertStringContainsString($campo, $xml);
        }
        $this->assertStringNotContainsString('<dRucRec>', $xml);
        $this->assertStringNotContainsString('<dDVRec>', $xml);
        $this->assertStringNotContainsString('<iTiContRec>', $xml);
    }

    public function test_documento_de_identidad_solo_quita_dv_a_la_cedula_paraguaya(): void
    {
        $this->assertSame('1807552', RucParaguayo::numeroDocumento(1, ' 1.807.552-5 '));
        $this->assertSame('1807552', RucParaguayo::numeroDocumento(1, '1807552'));
        $this->assertSame('AB-12345-6', RucParaguayo::numeroDocumento(2, 'AB-12345-6'));
    }

    public function test_receptor_contribuyente_informa_persona_fisica_o_juridica(): void
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct() {}
            public function receptor(\IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura $factura, Cliente $cliente): void
            {
                $this->setReceptor($factura, $cliente);
            }
        };
        $casos = [
            // [ruc, dv, tipo guardado, iTiContRec esperado]
            ['3895241', '6', null, 1],   // RUC de persona física (su cédula)
            ['80178718', '1', null, 2],  // RUC de persona jurídica (80…)
            ['3895241', '6', 2, 2],      // el valor elegido en el cliente manda
        ];
        foreach ($casos as [$ruc, $dv, $tipo, $esperado]) {
            $factura = new \IonysDev\Pkuatia\Core\DocumentosElectronicos\Factura(
                \IonysDev\Pkuatia\Core\Constants\CamCondOpe::Contado
            );
            $service->receptor($factura, new Cliente([
                'id' => 22, 'razonsocial' => 'Receptor de prueba', 'ruc' => $ruc, 'dv' => $dv,
                'es_contribuyente' => true, 'tipo_contribuyente' => $tipo,
            ]));
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $doc->appendChild($factura->gDatRec->toDOMElement($doc));
            $this->assertStringContainsString('<iTiContRec>' . $esperado . '</iTiContRec>', $doc->saveXML());
        }
    }

    public function test_ws_consulta_recibe_numero_solo_en_d_r_u_c_cons_sin_red(): void
    {
        $config = new Config();
        $archivoDid = tempnam(sys_get_temp_dir(), 'ruc_test_');
        $config->dIdFilePath = $archivoDid;
        $anteriores = [];
        foreach (['config' => $config, 'options' => []] as $nombre => $valor) {
            $p = new \ReflectionProperty(Sifen::class, $nombre);
            $p->setAccessible(true);
            if ($p->isInitialized()) $anteriores[$nombre] = $p->getValue();
            $p->setValue(null, $valor);
        }
        $soap = new class extends \SoapClient {
            public ?string $rucRecibido = null;
            public function __construct() {}
            public function rEnviConsRUC($request): \stdClass
            {
                $this->rucRecibido = $request->dRUCCons;
                return (object) ['dCodRes' => '0502', 'dMsgRes' => 'Respuesta simulada', 'xContRUC' => (object) [
                    'dRUCCons' => $request->dRUCCons, 'dRazCons' => 'Nombre omitido',
                    'dCodEstCons' => 'ACT', 'dDesEstCons' => 'ACTIVO', 'dRUCFactElec' => 'N',
                ]];
            }
        };
        Sifen::SetSoapClientFactory(fn () => $soap);
        $service = new class extends SifenPkuatiaService {
            public function __construct() {}
            protected function ensureInitialized(): void {}
        };
        try {
            $resultado = $service->consultarRUC(' 2535470 - 1 ');
            $this->assertSame('2535470', $soap->rucRecibido);
            $this->assertSame('2535470', $resultado['ruc']);
            $this->assertTrue($resultado['encontrado']);
            $this->assertSame('calculado_modulo11', $resultado['dv_origen']);
        } finally {
            Sifen::SetSoapClientFactory(null);
            foreach ($anteriores as $nombre => $valor) {
                $p = new \ReflectionProperty(Sifen::class, $nombre);
                $p->setAccessible(true);
                $p->setValue(null, $valor);
            }
            if (is_file($archivoDid)) unlink($archivoDid);
        }
    }

    public function test_fallo_de_consulta_no_se_confunde_con_ruc_inexistente(): void
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct() {}
            protected function ensureInitialized(): void {}
            protected function consultarNumeroRuc(string $numero) { throw new \RuntimeException('WSDL vacío'); }
        };
        $resultado = $service->consultarRUC('2535470-1');
        $this->assertFalse($resultado['existencia_verificada']);
        $this->assertFalse($resultado['encontrado']);
        $this->assertNull($resultado['codigo_resultado']);
        $this->assertStringContainsString('WSDL vacío', $resultado['mensaje']);
    }

    public function test_consulta_rechaza_letras_antes_de_iniciar_transporte(): void
    {
        $service = new class extends SifenPkuatiaService {
            public function __construct() {}
            protected function ensureInitialized(): void { throw new \LogicException('No debe iniciar transporte'); }
        };
        $this->expectException(\InvalidArgumentException::class);
        $service->consultarRUC('ABC2535470-1');
    }
}
