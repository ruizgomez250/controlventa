@php
    $estadoFiscal = $venta->sifen_estado ?: 'sin_enviar';
    $estadosFiscales = [
        'sin_enviar' => ['secondary', 'Sin envío a SIFEN'],
        'por_cobrar' => ['info', 'Pendiente de cobro para enviar'],
        'enviando' => ['info', 'Enviando a SIFEN'],
        'pendiente' => ['warning', 'Pendiente de aprobación SIFEN'],
        'sin_respuesta' => ['warning', 'Sin respuesta de SIFEN'],
        'autorizado' => ['success', 'Aprobado por SIFEN'],
        'rechazado' => ['danger', 'Rechazado por SIFEN'],
        'error' => ['danger', 'Error de envío SIFEN'],
        'cancelado' => ['secondary', 'Cancelado en SIFEN'],
    ];
    [$colorFiscal, $textoFiscal] = $estadosFiscales[$estadoFiscal] ?? ['secondary', $estadoFiscal];
@endphp
<span class="badge badge-{{ $colorFiscal }}" style="white-space:normal">{{ $textoFiscal }}</span>
@if($venta->sifen_actualizado_at)
    <small class="d-block text-muted">{{ $venta->sifen_actualizado_at->format('d/m/Y H:i:s') }}</small>
@endif
@if(in_array($estadoFiscal, ['rechazado', 'error', 'pendiente', 'sin_respuesta'], true))
    <a class="d-block small" href="{{ route('sifen.comprobantes', ['buscar' => $venta->sifen_cdc ?: $venta->numero_factura]) }}">Ver respuesta DNIT</a>
@endif
