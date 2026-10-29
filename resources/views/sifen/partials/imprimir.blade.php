<div class="d-inline-flex flex-wrap" style="gap:4px" aria-label="Imprimir factura">
    <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"
        href="{{ route('sifen.factura-test', ['venta' => $venta->id, 'formato' => 'ticket']) }}"
        title="Factura con QR en ticket de 80 mm"><i class="fas fa-receipt" aria-hidden="true"></i> Ticket / QR</a>
    <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"
        href="{{ route('sifen.factura-test', ['venta' => $venta->id, 'formato' => 'a4']) }}"
        title="Factura con QR en A4"><i class="fas fa-print" aria-hidden="true"></i> A4 / QR</a>
</div>
