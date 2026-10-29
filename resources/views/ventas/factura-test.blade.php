<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura TEST {{ $venta->id }}</title>
    <style>
        *{box-sizing:border-box}body{font:14px Arial,sans-serif;color:#17202a;background:#eef1f4;margin:0;padding:24px}
        main{max-width:800px;margin:auto;background:white;padding:32px;border:1px solid #ccd2d8}
        h1{font-size:22px;margin:8px 0}p{line-height:1.5}.test{padding:12px;background:#fff1cd;border:1px solid #ad7b00;font-weight:bold;text-align:center}
        table{width:100%;border-collapse:collapse;margin:24px 0}th,td{padding:10px 6px;border-bottom:1px solid #ddd;text-align:left}.num{text-align:right}
        .qr{text-align:center;margin-top:24px}.qr svg{max-width:100%;height:auto}.cdc{overflow-wrap:anywhere;font-family:monospace}
        button{padding:10px 20px;cursor:pointer}.controls{max-width:800px;margin:0 auto 16px}a{color:#154c80;overflow-wrap:anywhere}
        @page{size:{{ ($formato ?? 'a4') === 'ticket' ? '80mm 297mm' : 'A4' }};margin:{{ ($formato ?? 'a4') === 'ticket' ? '4mm' : '12mm' }}}@media print{body{padding:0;background:white}main{border:0;padding:0;max-width:none}.controls{display:none}tr,.qr{break-inside:avoid}}
        body.ticket main{width:80mm;padding:4mm}body.ticket{font-size:11px}body.ticket th,body.ticket td{padding:6px 2px}body.ticket .qr svg{width:56mm}
        @media print{body.ticket main{width:72mm;padding:0}}
    </style>
</head>
<body class="{{ ($formato ?? 'a4') === 'ticket' ? 'ticket' : '' }}">
<div class="controls">
    <button type="button" onclick="window.print()">Imprimir {{ ($formato ?? 'a4') === 'ticket' ? 'ticket 80 mm' : 'A4' }}</button>
    <a href="{{ route('sifen.factura-test', ['venta' => $venta->id, 'formato' => 'a4']) }}">Ver A4</a> ·
    <a href="{{ route('sifen.factura-test', ['venta' => $venta->id, 'formato' => 'ticket']) }}">Ver ticket 80 mm</a>
</div>
<main>
    <div class="test">TEST — SIN VALIDEZ TRIBUTARIA</div>
    <h1>{{ $config->razon_social }}</h1>
    <p>RUC: {{ $config->ruc_emisor }}-{{ $config->dv }}<br>{{ $config->nombre_sucursal }}<br>{{ $config->direccion }}<br>{{ $config->telefono }} · {{ $config->email }}</p>
    <h2>{{ $cdc ? 'Factura electrónica TEST' : 'Vista de prueba de factura' }}</h2>
    <p>Venta: {{ $venta->id }} · Factura: {{ $venta->numero_factura ? ($venta->sifen_establecimiento ?: str_pad($config->establecimiento, 3, '0', STR_PAD_LEFT)) . '-' . ($venta->sifen_punto_expedicion ?: str_pad($config->punto_expedicion, 3, '0', STR_PAD_LEFT)) . '-' . $venta->numero_factura : 'Sin numeración asignada' }}<br>
        Timbrado de la venta: {{ $venta->timbrado_factura ?: 'Sin asignar' }}<br>
        Fecha: {{ $venta->fecha_emision }}<br>Cliente: {{ $venta->cliente->razonsocial ?? 'Consumidor final' }}<br>
        Documento del cliente: {{ $venta->cliente?->ruc_completo ?: 'Sin documento' }}<br>
        Condición: {{ $venta->tipo_comprobante }}<br>
        Estado SIFEN: {{ $venta->sifen_estado ?: 'Sin envío registrado' }}
        @if($venta->sifen_cde)<br>Protocolo: {{ $venta->sifen_cde }}@endif</p>
    <table><thead><tr><th>Descripción</th><th class="num">Cant.</th><th class="num">Precio</th><th class="num">IVA</th><th class="num">Importe</th></tr></thead>
        <tbody>@foreach($venta->detalles as $detalle)<tr><td>{{ $detalle->descripcion }}</td><td class="num">{{ $detalle->cantidad }}</td><td class="num">{{ number_format($detalle->precio_u, 0, ',', '.') }}</td><td class="num">{{ $detalle->tipo_impuesto }}</td><td class="num">{{ number_format($detalle->monto, 0, ',', '.') }}</td></tr>@endforeach</tbody>
    </table>
    <h2 class="num">Total Gs. {{ number_format($venta->total, 0, ',', '.') }}</h2>
    @php
        $cdcVisible = $cdc ?: (preg_match('/^\d{44}$/', (string) $venta->sifen_cdc) ? $venta->sifen_cdc : null);
    @endphp
    <div class="qr">
        @if($cdcVisible)
            <p class="cdc"><strong>CDC:</strong><br>{{ $cdcVisible }}</p>
        @else
            <p><strong>CDC: AÚN NO GENERADO</strong></p>
            <p>{{ $venta->sifen_mensaje ?: 'La factura todavía no tiene un documento electrónico firmado. Revisá la bandeja de comprobantes.' }}</p>
        @endif
        @if($cdc)
            <p>Estado SIFEN: {{ $venta->sifen_estado ?: 'Pendiente de aprobación' }}</p>
            {!! $qrSvg !!}
            <p>QR extraído del XML firmado de esta venta.</p>
        @else
            <p><strong>{{ $cdcVisible ? 'XML FIRMADO NO DISPONIBLE PARA ESTA IMPRESIÓN' : 'DEMOSTRACIÓN — SIN XML FIRMADO' }}</strong></p>
            {!! $qrSvg !!}
            <p>Este QR abre el portal de consultas TEST.<br>No identifica una factura ni demuestra aprobación.</p>
        @endif
        <a href="{{ $qrUrl }}" target="_blank" rel="noopener">Abrir consulta SIFEN TEST</a>
    </div>
</main>
</body>
</html>
