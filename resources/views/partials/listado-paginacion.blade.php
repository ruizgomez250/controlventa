<div class="mt-3">
    <p class="text-muted small">
        Mostrando {{ $registros->firstItem() ?? 0 }}–{{ $registros->lastItem() ?? 0 }} de {{ $registros->total() }} registros
    </p>
    @if ($registros->isEmpty())
        <p class="text-center text-muted">No se encontraron registros.</p>
    @endif
    <div class="overflow-auto">
        {{ $registros->links('pagination::bootstrap-4') }}
    </div>
</div>
