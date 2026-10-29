@php($esContribuyente = (string) old('es_contribuyente', isset($clienteRuc) ? (int) $clienteRuc->es_contribuyente : 1))
<x-adminlte-select name="es_contribuyente" label="Tipo de receptor SIFEN" fgroup-class="col-md-3"
    onchange="alternarIdentidadSifen(this.form)">
    <option value="1" {{ $esContribuyente === '1' ? 'selected' : '' }}>Contribuyente (RUC)</option>
    <option value="0" {{ $esContribuyente === '0' ? 'selected' : '' }}>No contribuyente (documento)</option>
</x-adminlte-select>
@php($tipoContribuyente = (string) old('tipo_contribuyente', $clienteRuc->tipo_contribuyente ?? ''))
<div class="col-md-7 row mx-0 px-0 identidad-ruc">
    <x-adminlte-input name="ruc" label="Número de RUC (sin DV)" placeholder="2535470 o 2535470-1"
        fgroup-class="col-md-5" :value="old('ruc', $clienteRuc->ruc ?? '')" inputmode="numeric"
        maxlength="24" pattern="[0-9]{5,8}(-[0-9])?" oninput="separarRucFormulario(this)" />
    <x-adminlte-input name="dv" label="DV" placeholder="Ej.: 1"
        fgroup-class="col-md-2" :value="old('dv', $clienteRuc->dv ?? '')" inputmode="numeric"
        maxlength="1" pattern="[0-9]" />
    <x-adminlte-select name="tipo_contribuyente" label="Tipo de contribuyente" fgroup-class="col-md-5">
        <option value="" {{ $tipoContribuyente === '' ? 'selected' : '' }}>Según el RUC</option>
        <option value="1" {{ $tipoContribuyente === '1' ? 'selected' : '' }}>Persona física</option>
        <option value="2" {{ $tipoContribuyente === '2' ? 'selected' : '' }}>Persona jurídica</option>
    </x-adminlte-select>
</div>
<div class="col-md-5 row mx-0 px-0 identidad-documento">
    <x-adminlte-select name="tipo_identificacion" label="Tipo de documento" fgroup-class="col-md-6">
        @foreach ([1 => 'Cédula paraguaya', 2 => 'Pasaporte', 3 => 'Cédula extranjera', 4 => 'Carnet de residencia', 6 => 'Tarjeta diplomática'] as $valor => $texto)
            <option value="{{ $valor }}" {{ (string) old('tipo_identificacion', $clienteRuc->tipo_identificacion ?? 1) === (string) $valor ? 'selected' : '' }}>{{ $texto }}</option>
        @endforeach
    </x-adminlte-select>
    <x-adminlte-input name="numero_identificacion" label="Número de documento" placeholder="Cédula sin DV, ej.: 1807552"
        fgroup-class="col-md-6" :value="old('numero_identificacion', $clienteRuc->numero_identificacion ?? '')" maxlength="50" />
</div>
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

    function alternarIdentidadSifen(form) {
        if (!form) return;
        const contribuyente = form.elements.namedItem('es_contribuyente').value === '1';
        form.querySelectorAll('.identidad-ruc').forEach(el => el.style.display = contribuyente ? '' : 'none');
        form.querySelectorAll('.identidad-documento').forEach(el => el.style.display = contribuyente ? 'none' : '');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select[name="es_contribuyente"]').forEach(el => alternarIdentidadSifen(el.form));
    });
</script>
@endpush
@endonce
