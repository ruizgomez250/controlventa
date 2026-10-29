<x-adminlte-input name="ruc" label="Número de RUC (sin DV)" placeholder="2535470 o 2535470-1"
    fgroup-class="col-md-3" :value="old('ruc', $clienteRuc->ruc ?? '')" inputmode="numeric"
    maxlength="24" pattern="[0-9]{5,8}(-[0-9])?" oninput="separarRucFormulario(this)" />
<x-adminlte-input name="dv" label="Identificador / DV" placeholder="Ej.: 1"
    fgroup-class="col-md-2" :value="old('dv', $clienteRuc->dv ?? '')" inputmode="numeric"
    maxlength="1" pattern="[0-9]" />
@once
@push('js')
<script>
    function separarRucFormulario(input) {
        input.value = input.value.replace(/\s/g, '');
        const partes = /^([0-9]{5,8})-([0-9])$/.exec(input.value);
        if (partes && input.form) {
            const dv = input.form.elements.namedItem('dv');
            if (dv) {
                input.value = partes[1];
                dv.value = partes[2];
            }
        }
    }
</script>
@endpush
@endonce
