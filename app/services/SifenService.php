<?php

namespace App\Services;

use App\Models\SifenConfiguracion;
use App\Models\Venta;
use App\Models\Cliente;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @deprecated Usar SifenPkuatiaService en su lugar.
 * Esta implementación manual queda solo como respaldo.
 * La migración a PKuatia es la vía recomendada.
 */
class SifenService
{
    protected SifenConfiguracion $config;

    protected int $precision = 6;

    protected const SIFEN_NS = 'http://ekuatia.set.gov.py/sifen/xsd';

    protected const SIFEN_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    protected const PROD_WSDL = 'https://sifen.set.gov.py/de/ws/sync/recibe.wsdl';

    protected const TEST_WSDL = 'https://sifen-test.set.gov.py/de/ws/sync/recibe.wsd';

    protected const PROD_QR_URL = 'https://ekuatia.set.gov.py/consultas/qr?';

    protected const TEST_QR_URL = 'https://www.ekuatia.set.gov.py/consultas-test/qr?';

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

    public function emitir(Venta $venta): array
    {
        if (!$this->isHabilitado()) {
            throw new Exception('SIFEN no está habilitado. Configure los datos del emisor primero.');
        }

        $json = $this->buildJsonData($venta);
        $xmlData = $this->generarXml($json);
        $responseXml = $this->enviarXml($xmlData['xml'], $xmlData['id']);
        $resultado = $this->procesarRespuesta($responseXml);

        $venta->sifen_cdc = $xmlData['id'];
        $venta->sifen_cde = $resultado['cde'] ?? null;
        $venta->sifen_estado = $resultado['estado'] ?? 'pendiente';
        $venta->save();

        return $resultado;
    }

    protected function buildJsonData(Venta $venta): array
    {
        $cliente = $venta->cliente;
        $detalles = $venta->detalles;

        $rucEmisor = preg_replace('/[^0-9]/', '', $this->config->ruc_emisor);
        $dvEmisor = $this->config->dv;

        $rucRec = '';
        $dvRec = '';
        $esContribuyente = false;

        if ($cliente && $cliente->ruc) {
            $rucLimpio = preg_replace('/[^0-9]/', '', $cliente->ruc);
            if (strlen($rucLimpio) > 1) {
                $dvRec = substr($rucLimpio, -1);
                $rucRec = substr($rucLimpio, 0, -1);
                $esContribuyente = true;
            }
        }

        $razonSocialRec = $cliente ? ($cliente->razonsocial ?: 'Consumidor Final') : 'Consumidor Final';

        $items = [];
        foreach ($detalles as $detalle) {
            $items[] = [
                'dCodInt' => $detalle->id_producto,
                'dDesProSer' => $detalle->descripcion,
                'cUniMed' => 77,
                'dDesUniMed' => 'UNI',
                'dCantProSer' => (float)$detalle->cantidad,
                'dPUniProSer' => (float)$detalle->precio_u,
                'dTasaIVA' => $this->getTasaIVA($detalle->tipo_impuesto),
                'dDescItem' => 0,
                'dPorcDesIt' => 0,
                'dDescGloItem' => 0,
                'dAntPreUniIt' => 0,
                'dAntGloPreUniIt' => 0,
                'iAfecIVA' => 1,
                'dDesAfecIVA' => 'Gravado IVA',
                'dPropIVA' => 100,
            ];
        }

        $tipoDoc = 1;
        $condOpe = 1;
        $desCondOpe = 'Contado';
        if ($venta->tipo_comprobante === 'CREDITO') {
            $condOpe = 2;
            $desCondOpe = 'Crédito';
        }

        $fechaEmision = $venta->fecha_emision ? date('Y-m-d\TH:i:s', strtotime($venta->fecha_emision)) : date('Y-m-d\TH:i:s');

        return [
            'DE' => [[
                'dSisFact' => 1,
                'iTipEmi' => 1,
                'dDesTipEmi' => 'Normal',
                'dInfoEmi' => 1,
                'iTiDE' => $tipoDoc,
                'dDesTiDE' => 'Factura electrónica',
                'dNumTim' => $venta->timbrado_factura ? (int)preg_replace('/[^0-9]/', '', $venta->timbrado_factura) : 0,
                'dEst' => $this->config->establecimiento,
                'dPunExp' => $this->config->punto_expedicion,
                'dNumDoc' => str_pad($venta->numero_factura ?: $venta->id, 7, '0', STR_PAD_LEFT),
                'dFeIniT' => $venta->fecha_vencimiento ?? date('Y-m-d', strtotime('+6 months')),
                'dFeEmiDE' => $fechaEmision,
                'iTipTra' => 1,
                'dDesTipTra' => 'Venta de mercadería',
                'iTImp' => 1,
                'dDesTImp' => 'IVA',
                'cMoneOpe' => 'PYG',
                'dDesMoneOpe' => 'Guaraní',
                'dRucEm' => $rucEmisor,
                'dDVEmi' => $dvEmisor,
                'iTipCont' => 2,
                'dNomEmi' => $this->config->razon_social,
                'dDirEmi' => $this->config->direccion ?: 'Sin dirección',
                'dNumCas' => 0,
                'cDepEmi' => 1,
                'dDesDepEmi' => 'CAPITAL',
                'cDisEmi' => 1,
                'dDesDisEmi' => 'ASUNCION (DISTRITO)',
                'cCiuEmi' => 1,
                'dDesCiuEmi' => 'ASUNCION (DISTRITO)',
                'dTelEmi' => $this->config->telefono ?: '000000000',
                'dEmailE' => $this->config->email ?: 'email@empresa.com',
                'cActEco' => 620,
                'dDesActEco' => 'ACTIVIDADES DE PROGRAMACIÓN Y CONSULTORÍA INFORMÁTICAS Y OTRAS ACTIVIDADES CONEXAS',
                'iNatRec' => $esContribuyente ? 1 : 2,
                'iTiOpe' => $esContribuyente ? 2 : 1,
                'cPaisRec' => 'PRY',
                'dDesPaisRe' => 'Paraguay',
                'iTiContRec' => $esContribuyente ? 1 : 2,
                'dRucRec' => $rucRec,
                'dDVRec' => $dvRec,
                'dNomRec' => $razonSocialRec,
                'iIndPres' => 1,
                'dDesIndPres' => 'Operación presencial',
                'iCondOpe' => $condOpe,
                'dDCondOpe' => $desCondOpe,
                'iTiPago' => 1,
                'dDesTiPag' => 'Efectivo',
                'dMonTiPag' => (float)$venta->total,
                'cMoneTiPag' => 'PYG',
                'dDMoneTiPag' => 'Guaraní',
            ]],
            'items' => $items,
        ];
    }

    public function generarXml(array $jsonData): array
    {
        $json_de = $jsonData;

        $dFecFirma = date('Y-m-d\TH:i:s');

        $dFeEmiDE = str_replace('T', ' ', $json_de['DE'][0]['dFeEmiDE']);
        $fechaEmision = new \DateTime($dFeEmiDE);
        $dFeEmiDEFormatted = $fechaEmision->format('Ymd');

        $dCodSeg = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);

        $codiSeguridad = '0'
            . $json_de['DE'][0]['iTiDE']
            . $json_de['DE'][0]['dRucEm']
            . $json_de['DE'][0]['dDVEmi']
            . $json_de['DE'][0]['dEst']
            . $json_de['DE'][0]['dPunExp']
            . $json_de['DE'][0]['dNumDoc']
            . $json_de['DE'][0]['iTipCont']
            . $dFeEmiDEFormatted
            . $json_de['DE'][0]['iTipEmi']
            . $dCodSeg;

        $cv = $this->mod11($codiSeguridad);
        $Id = $codiSeguridad . $cv;

        $dSubExe = 0;
        $dSub5 = 0;
        $dSub10 = 0;
        $dTotOpe = 0;
        $dTotDesc = 0;
        $dTotDescGlotem = 0;
        $dTotAntItem = 0;
        $dTotAnt = 0;
        $dPorcDescTotal = 0;
        $dDescTotal = 0;
        $dAnticipo = 0;
        $dRedon = 0;
        $dTotGralOpe = 0;
        $dIVA5 = 0;
        $dIVA10 = 0;
        $dLiqTotIVA5 = 0;
        $dLiqTotIVA10 = 0;
        $dIVAComi = 0;
        $dTotIVA = 0;
        $dBaseGrav5 = 0;
        $dBaseGrav10 = 0;
        $dTBasGraIVA = 0;

        $itemsXml = '';
        $cItems = 0;

        foreach ($json_de['items'] as $item) {
            $dCantProSer = number_format((float)$item['dCantProSer'], 4, '.', '');
            $dPUniProSer = number_format((float)$item['dPUniProSer'], 4, '.', '');

            $cUniMed = $item['cUniMed'] ?? 77;
            $dDesUniMed = $item['dDesUniMed'] ?? 'UNI';
            $dDescItem = $item['dDescItem'] ?? 0;
            $dPorcDesIt = $item['dPorcDesIt'] ?? 0;
            $dDescGloItem = $item['dDescGloItem'] ?? 0;
            $dAntPreUniIt = $item['dAntPreUniIt'] ?? 0;
            $dAntGloPreUniIt = $item['dAntGloPreUniIt'] ?? 0;
            $iAfecIVA = $item['iAfecIVA'] ?? 1;
            $dDesAfecIVA = $item['dDesAfecIVA'] ?? 'Gravado IVA';
            $dPropIVA = $item['dPropIVA'] ?? 100;
            $dTasaIVA = $item['dTasaIVA'] ?? 10;

            $dTotBruOpeItem = $dCantProSer * $dPUniProSer;
            $dTotBruOpeItem = number_format(round($dTotBruOpeItem), $this->precision, '.', '');
            $dTotOpeItem = ($dPUniProSer - $dDescItem - $dPorcDesIt - $dDescGloItem - $dAntPreUniIt - $dAntGloPreUniIt) * $dCantProSer;
            $dTotOpeItem = number_format(round($dTotOpeItem), $this->precision, '.', '');

            switch ($dTasaIVA) {
                case 0:
                    $dBasGravIVA = 0;
                    $dLiqIVAItem = 0;
                    break;
                case 5:
                    $dBasGravIVA = number_format(round(($dTotOpeItem * ($dPropIVA / 100)) / 1.05), $this->precision, '.', '');
                    $dLiqIVAItem = number_format(round($dBasGravIVA * (5 / 100)), $this->precision, '.', '');
                    $dSub5 += $dTotBruOpeItem;
                    $dTotOpe += $dTotBruOpeItem;
                    $dTotGralOpe += $dTotBruOpeItem;
                    $dIVA5 += $dLiqIVAItem;
                    $dTotIVA += $dLiqIVAItem;
                    $dBaseGrav5 += $dBasGravIVA;
                    $dTBasGraIVA += $dBasGravIVA;
                    break;
                case 10:
                    $dBasGravIVA = number_format(round(($dTotOpeItem * ($dPropIVA / 100)) / 1.1), $this->precision, '.', '');
                    $dLiqIVAItem = number_format(round($dBasGravIVA * (10 / 100)), $this->precision, '.', '');
                    $dSub10 += $dTotBruOpeItem;
                    $dTotOpe += $dTotBruOpeItem;
                    $dTotGralOpe += $dTotBruOpeItem;
                    $dIVA10 += $dLiqIVAItem;
                    $dTotIVA += $dLiqIVAItem;
                    $dBaseGrav10 += $dBasGravIVA;
                    $dTBasGraIVA += $dBasGravIVA;
                    break;
                default:
                    $dBasGravIVA = 0;
                    $dLiqIVAItem = 0;
                    break;
            }

            $cItems++;

            $itemsXml .= <<<XML
<gCamItem>
    <dCodInt>{$item['dCodInt']}</dCodInt>
    <dDesProSer><![CDATA[{$item['dDesProSer']}]]></dDesProSer>
    <cUniMed>{$cUniMed}</cUniMed>
    <dDesUniMed>{$dDesUniMed}</dDesUniMed>
    <dCantProSer>{$dCantProSer}</dCantProSer>
    <gValorItem>
        <dPUniProSer>{$dPUniProSer}</dPUniProSer>
        <dTotBruOpeItem>{$dTotBruOpeItem}</dTotBruOpeItem>
        <gValorRestaItem>
            <dDescItem>{$dDescItem}</dDescItem>
            <dPorcDesIt>{$dPorcDesIt}</dPorcDesIt>
            <dDescGloItem>{$dDescGloItem}</dDescGloItem>
            <dAntPreUniIt>{$dAntPreUniIt}</dAntPreUniIt>
            <dAntGloPreUniIt>{$dAntGloPreUniIt}</dAntGloPreUniIt>
            <dTotOpeItem>{$dTotOpeItem}</dTotOpeItem>
        </gValorRestaItem>
    </gValorItem>
    <gCamIVA>
        <iAfecIVA>{$iAfecIVA}</iAfecIVA>
        <dDesAfecIVA>{$dDesAfecIVA}</dDesAfecIVA>
        <dPropIVA>{$dPropIVA}</dPropIVA>
        <dTasaIVA>{$dTasaIVA}</dTasaIVA>
        <dBasGravIVA>{$dBasGravIVA}</dBasGravIVA>
        <dLiqIVAItem>{$dLiqIVAItem}</dLiqIVAItem>
    </gCamIVA>
</gCamItem>
XML;
        }

        $dTotGralOpe = number_format(round($dTotGralOpe), $this->precision, '.', '');
        $dTotIVA = number_format(round($dTotIVA), $this->precision, '.', '');

        $xmlCrudo = <<<XML
<rDE xmlns="{$this::SIFEN_NS}"
     xmlns:xsi="{$this::SIFEN_XSI}"
     xsi:schemaLocation="{$this::SIFEN_NS} siRecepDE_v150.xsd">
    <dVerFor>150</dVerFor>
    <DE Id="{$Id}">
        <dDVId>{$cv}</dDVId>
        <dFecFirma>{$dFecFirma}</dFecFirma>
        <dSisFact>{$json_de['DE'][0]['dSisFact']}</dSisFact>
        <gOpeDE>
            <iTipEmi>{$json_de['DE'][0]['iTipEmi']}</iTipEmi>
            <dDesTipEmi>{$json_de['DE'][0]['dDesTipEmi']}</dDesTipEmi>
            <dCodSeg>{$dCodSeg}</dCodSeg>
            <dInfoEmi>{$json_de['DE'][0]['dInfoEmi']}</dInfoEmi>
        </gOpeDE>
        <gTimb>
            <iTiDE>{$json_de['DE'][0]['iTiDE']}</iTiDE>
            <dDesTiDE>{$json_de['DE'][0]['dDesTiDE']}</dDesTiDE>
            <dNumTim>{$json_de['DE'][0]['dNumTim']}</dNumTim>
            <dEst>{$json_de['DE'][0]['dEst']}</dEst>
            <dPunExp>{$json_de['DE'][0]['dPunExp']}</dPunExp>
            <dNumDoc>{$json_de['DE'][0]['dNumDoc']}</dNumDoc>
            <dFeIniT>{$json_de['DE'][0]['dFeIniT']}</dFeIniT>
        </gTimb>
        <gDatGralOpe>
            <dFeEmiDE>{$json_de['DE'][0]['dFeEmiDE']}</dFeEmiDE>
            <gOpeCom>
                <iTipTra>{$json_de['DE'][0]['iTipTra']}</iTipTra>
                <dDesTipTra>{$json_de['DE'][0]['dDesTipTra']}</dDesTipTra>
                <iTImp>{$json_de['DE'][0]['iTImp']}</iTImp>
                <dDesTImp>{$json_de['DE'][0]['dDesTImp']}</dDesTImp>
                <cMoneOpe>{$json_de['DE'][0]['cMoneOpe']}</cMoneOpe>
                <dDesMoneOpe>{$json_de['DE'][0]['dDesMoneOpe']}</dDesMoneOpe>
            </gOpeCom>
            <gEmis>
                <dRucEm>{$json_de['DE'][0]['dRucEm']}</dRucEm>
                <dDVEmi>{$json_de['DE'][0]['dDVEmi']}</dDVEmi>
                <iTipCont>{$json_de['DE'][0]['iTipCont']}</iTipCont>
                <dNomEmi><![CDATA[{$json_de['DE'][0]['dNomEmi']}]]></dNomEmi>
                <dDirEmi><![CDATA[{$json_de['DE'][0]['dDirEmi']}]]></dDirEmi>
                <dNumCas>{$json_de['DE'][0]['dNumCas']}</dNumCas>
                <cDepEmi>{$json_de['DE'][0]['cDepEmi']}</cDepEmi>
                <dDesDepEmi>{$json_de['DE'][0]['dDesDepEmi']}</dDesDepEmi>
                <cDisEmi>{$json_de['DE'][0]['cDisEmi']}</cDisEmi>
                <dDesDisEmi>{$json_de['DE'][0]['dDesDisEmi']}</dDesDisEmi>
                <cCiuEmi>{$json_de['DE'][0]['cCiuEmi']}</cCiuEmi>
                <dDesCiuEmi>{$json_de['DE'][0]['dDesCiuEmi']}</dDesCiuEmi>
                <dTelEmi>{$json_de['DE'][0]['dTelEmi']}</dTelEmi>
                <dEmailE>{$json_de['DE'][0]['dEmailE']}</dEmailE>
                <gActEco>
                    <cActEco>{$json_de['DE'][0]['cActEco']}</cActEco>
                    <dDesActEco>{$json_de['DE'][0]['dDesActEco']}</dDesActEco>
                </gActEco>
            </gEmis>
            <gDatRec>
                <iNatRec>{$json_de['DE'][0]['iNatRec']}</iNatRec>
                <iTiOpe>{$json_de['DE'][0]['iTiOpe']}</iTiOpe>
                <cPaisRec>{$json_de['DE'][0]['cPaisRec']}</cPaisRec>
                <dDesPaisRe>{$json_de['DE'][0]['dDesPaisRe']}</dDesPaisRe>
                <iTiContRec>{$json_de['DE'][0]['iTiContRec']}</iTiContRec>
                <dRucRec>{$json_de['DE'][0]['dRucRec']}</dRucRec>
                <dDVRec>{$json_de['DE'][0]['dDVRec']}</dDVRec>
                <dNomRec><![CDATA[{$json_de['DE'][0]['dNomRec']}]]></dNomRec>
            </gDatRec>
        </gDatGralOpe>
        <gDtipDE>
            <gCamFE>
                <iIndPres>{$json_de['DE'][0]['iIndPres']}</iIndPres>
                <dDesIndPres>{$json_de['DE'][0]['dDesIndPres']}</dDesIndPres>
            </gCamFE>
            <gCamCond>
                <iCondOpe>{$json_de['DE'][0]['iCondOpe']}</iCondOpe>
                <dDCondOpe>{$json_de['DE'][0]['dDCondOpe']}</dDCondOpe>
                <gPaConEIni>
                    <iTiPago>{$json_de['DE'][0]['iTiPago']}</iTiPago>
                    <dDesTiPag>{$json_de['DE'][0]['dDesTiPag']}</dDesTiPag>
                    <dMonTiPag>{$json_de['DE'][0]['dMonTiPag']}</dMonTiPag>
                    <cMoneTiPag>{$json_de['DE'][0]['cMoneTiPag']}</cMoneTiPag>
                    <dDMoneTiPag>{$json_de['DE'][0]['dDMoneTiPag']}</dDMoneTiPag>
                </gPaConEIni>
            </gCamCond>
            {$itemsXml}
        </gDtipDE>
        <gTotSub>
            <dSubExe>{$dSubExe}</dSubExe>
            <dSub5>{$dSub5}</dSub5>
            <dSub10>{$dSub10}</dSub10>
            <dTotOpe>{$dTotOpe}</dTotOpe>
            <dTotDesc>{$dTotDesc}</dTotDesc>
            <dTotDescGlotem>{$dTotDescGlotem}</dTotDescGlotem>
            <dTotAntItem>{$dTotAntItem}</dTotAntItem>
            <dTotAnt>{$dTotAnt}</dTotAnt>
            <dPorcDescTotal>{$dPorcDescTotal}</dPorcDescTotal>
            <dDescTotal>{$dDescTotal}</dDescTotal>
            <dAnticipo>{$dAnticipo}</dAnticipo>
            <dRedon>{$dRedon}</dRedon>
            <dTotGralOpe>{$dTotGralOpe}</dTotGralOpe>
            <dIVA5>{$dIVA5}</dIVA5>
            <dIVA10>{$dIVA10}</dIVA10>
            <dLiqTotIVA5>{$dLiqTotIVA5}</dLiqTotIVA5>
            <dLiqTotIVA10>{$dLiqTotIVA10}</dLiqTotIVA10>
            <dIVAComi>{$dIVAComi}</dIVAComi>
            <dTotIVA>{$dTotIVA}</dTotIVA>
            <dBaseGrav5>{$dBaseGrav5}</dBaseGrav5>
            <dBaseGrav10>{$dBaseGrav10}</dBaseGrav10>
            <dTBasGraIVA>{$dTBasGraIVA}</dTBasGraIVA>
        </gTotSub>
    </DE>
</rDE>
XML;

        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->loadXML($xmlCrudo);

        $deContent = $xml->getElementsByTagName('DE')->item(0)->C14N();
        $digestValue = base64_encode(hash('sha256', $deContent, true));

        $this->firmarXml($xml, $Id, $digestValue, $cv);

        $concatenado = "nVersion=150&Id={$Id}&dFeEmiDE=" . bin2hex($json_de['DE'][0]['dFeEmiDE'])
            . "&dRucRec={$json_de['DE'][0]['dRucRec']}&dTotGralOpe={$dTotGralOpe}"
            . "&dTotIVA={$dTotIVA}&cItems={$cItems}&DigestValue=" . bin2hex($digestValue)
            . "&IdCSC=0001";

        $codigoSecreto = 'ABCD0000000000000000000000000000';
        $hashQr = hash('sha256', $concatenado . $codigoSecreto);

        $qrBase = $this->config->ambiente === 2 ? self::PROD_QR_URL : self::TEST_QR_URL;
        $enlaceQR = $qrBase . $concatenado . '&cHashQR=' . $hashQr;
        $enlaceQrXml = str_replace('&', '&amp;', $enlaceQR);

        $root = $xml->documentElement;
        $gCamFuFD = $xml->createElement('gCamFuFD');
        $dCarQR = $xml->createElement('dCarQR', $enlaceQrXml);
        $gCamFuFD->appendChild($dCarQR);
        $root->appendChild($gCamFuFD);

        $xmlString = $xml->saveXML();

        return [
            'xml' => $xmlString,
            'id' => $Id,
        ];
    }

    protected function firmarXml(DOMDocument $xml, string $Id, string $digestValue, string $cv): void
    {
        if (!$this->config->certificado_p12) {
            throw new Exception('No se ha configurado el certificado digital P12');
        }

        $certP12 = base64_decode($this->config->certificado_p12);
        $password = $this->config->certificado_password ?: '';

        if (!openssl_pkcs12_read($certP12, $certs, $password)) {
            throw new Exception('Error al leer el certificado P12: ' . openssl_error_string());
        }

        $privateKey = openssl_pkey_get_private($certs['pkey']);
        if (!$privateKey) {
            throw new Exception('Error al obtener la clave privada: ' . openssl_error_string());
        }

        $deContent = $xml->getElementsByTagName('DE')->item(0)->C14N();

        openssl_sign($deContent, $signatureValue, $privateKey, OPENSSL_ALGO_SHA256);

        $certPem = $certs['cert'];
        $certB64 = str_replace([
            '-----BEGIN CERTIFICATE-----',
            '-----END CERTIFICATE-----',
            "\n",
            "\r",
        ], '', $certPem);

        $root = $xml->documentElement;
        $signature = $xml->createElement('Signature');
        $root->appendChild($signature);

        $signedInfo = $xml->createElement('SignedInfo');
        $signature->appendChild($signedInfo);

        $canonMethod = $xml->createElement('CanonicalizationMethod');
        $canonMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfo->appendChild($canonMethod);

        $sigMethod = $xml->createElement('SignatureMethod');
        $sigMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfo->appendChild($sigMethod);

        $reference = $xml->createElement('Reference');
        $reference->setAttribute('URI', '#' . $Id);
        $signedInfo->appendChild($reference);

        $transforms = $xml->createElement('Transforms');
        $reference->appendChild($transforms);

        $t1 = $xml->createElement('Transform');
        $t1->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transforms->appendChild($t1);

        $t2 = $xml->createElement('Transform');
        $t2->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        $transforms->appendChild($t2);

        $digestMethod = $xml->createElement('DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $reference->appendChild($digestMethod);

        $digestValueEl = $xml->createElement('DigestValue', $digestValue);
        $reference->appendChild($digestValueEl);

        $sigValueEl = $xml->createElement('SignatureValue', base64_encode($signatureValue));
        $signature->appendChild($sigValueEl);

        $keyInfo = $xml->createElement('KeyInfo');
        $signature->appendChild($keyInfo);

        $x509Data = $xml->createElement('X509Data');
        $keyInfo->appendChild($x509Data);

        $x509Cert = $xml->createElement('X509Certificate', $certB64);
        $x509Data->appendChild($x509Cert);
    }

    public function enviarXml(string $xmlString, string $id): string
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlString);
        $contenidoXML = $dom->saveXML($dom->documentElement);

        $urlDestino = $this->config->ambiente === 2 ? self::PROD_WSDL : self::TEST_WSDL;

        $soapEnvelope = '<?xml version="1.0" encoding="UTF-8"?>
        <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
            <env:Header/>
            <env:Body>
                <rEnviDe xmlns="http://ekuatia.set.gov.py/sifen/xsd">
                    <dId>25</dId>
                    <xDE>
                        ' . $contenidoXML . '
                    </xDE>
                </rEnviDe>
            </env:Body>
        </env:Envelope>';

        if (!$this->config->certificado_p12) {
            throw new Exception('No se ha configurado el certificado digital P12');
        }

        $certP12 = base64_decode($this->config->certificado_p12);
        $password = $this->config->certificado_password ?: '';

        $tempDir = storage_path('app/sifen');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $certPath = $tempDir . '/temp_cert.pem';
        $keyPath = $tempDir . '/temp_key.pem';

        if (!openssl_pkcs12_read($certP12, $certs, $password)) {
            throw new Exception('Error al leer el certificado P12 para envío: ' . openssl_error_string());
        }

        file_put_contents($certPath, $certs['cert']);
        file_put_contents($keyPath, $certs['pkey']);

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $urlDestino);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $soapEnvelope);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
            curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
            curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            file_put_contents($tempDir . '/' . $id . '_respuesta.xml', $response);

            @unlink($certPath);
            @unlink($keyPath);

            if ($error) {
                throw new Exception('Error cURL: ' . $error);
            }

            if ($httpCode !== 200) {
                throw new Exception('El servidor SIFEN respondió con código HTTP: ' . $httpCode . '. Respuesta: ' . $response);
            }

            return $response;
        } catch (Exception $e) {
            @unlink($certPath);
            @unlink($keyPath);
            throw $e;
        }
    }

    protected function procesarRespuesta(string $responseXml): array
    {
        $resultado = [
            'estado' => 'rechazado',
            'cde' => null,
            'mensaje' => '',
        ];

        try {
            $dom = new DOMDocument();
            $dom->loadXML($responseXml);

            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $xpath->registerNamespace('sifen', self::SIFEN_NS);

            $xDe = $xpath->query('//soap:Body//sifen:xDE');
            if ($xDe && $xDe->length > 0) {
                $cdeNode = $xpath->query('.//sifen:dCDE', $xDe->item(0));
                if ($cdeNode && $cdeNode->length > 0) {
                    $cde = trim($cdeNode->item(0)->nodeValue);
                    $resultado['cde'] = $cde;
                    $resultado['estado'] = 'autorizado';
                    $resultado['mensaje'] = 'Documento autorizado con CDE: ' . $cde;
                    return $resultado;
                }

                $msgNode = $xpath->query('.//sifen:dMsgRes|.//sifen:dProtAsoc|.//text()', $xDe->item(0));
                if ($msgNode && $msgNode->length > 0) {
                    $resultado['mensaje'] = trim($msgNode->item(0)->nodeValue);
                }
            }

            $nsNode = $xpath->query('//*[local-name()="rResEnviDe"]');
            if ($nsNode && $nsNode->length > 0) {
                $cdeNode = $xpath->query('.//*[local-name()="dCDE"]', $nsNode->item(0));
                if ($cdeNode && $cdeNode->length > 0) {
                    $resultado['cde'] = trim($cdeNode->item(0)->nodeValue);
                    $resultado['estado'] = 'autorizado';
                    $resultado['mensaje'] = 'Documento autorizado';
                    return $resultado;
                }
            }

            $resultado['mensaje'] = 'Respuesta recibida sin CDE. Posible rechazo. XML: ' . substr($responseXml, 0, 500);
        } catch (Exception $e) {
            $resultado['mensaje'] = 'Error al procesar respuesta: ' . $e->getMessage();
        }

        return $resultado;
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

    protected function mod11(string $numero, int $basemax = 11): string
    {
        $numeroAl = '';

        for ($i = 0; $i < strlen($numero); $i++) {
            $c = substr($numero, $i, 1);
            $codigo = ord(strtoupper($c));
            if (!($codigo >= 48 && $codigo <= 57)) {
                $numeroAl .= $codigo;
            } else {
                $numeroAl .= $c;
            }
        }

        $k = 2;
        $total = 0;

        for ($i = strlen($numeroAl); $i >= 1; $i--) {
            if ($k > $basemax) {
                $k = 2;
            }
            $total += (int)substr($numeroAl, $i - 1, 1) * $k;
            $k++;
        }

        $resto = $total % 11;

        return $resto > 1 ? (string)(11 - $resto) : '0';
    }
}
