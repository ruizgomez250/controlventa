<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobilePeopleController extends Controller
{
    use \App\Http\Controllers\Concerns\NormalizaRuc;
    public function createClient(Request $request): JsonResponse
    {
        $data = $this->clientData($request);
        $data['estado'] = 1;
        return response()->json(['success' => true, 'data' => Cliente::create($data)], 201);
    }

    public function updateClient(Request $request, Cliente $cliente): JsonResponse
    {
        $cliente->update($this->clientData($request, $cliente));
        return response()->json(['success' => true, 'data' => $cliente->fresh()]);
    }

    public function updateSupplier(Request $request, Proveedor $proveedor): JsonResponse
    {
        $data = $request->validate([
            'razonsocial' => ['required', 'string', 'max:70'],
            'ruc' => ['required', 'string', 'max:14', Rule::unique('proveedores', 'ruc')->ignore($proveedor->id)],
        ]);
        $proveedor->update($data);
        return response()->json(['success' => true, 'data' => $proveedor->fresh()]);
    }

    private function clientData(Request $request, ?Cliente $cliente = null): array
    {
        $this->prepararIdentidadSifen($request, $cliente);
        return $request->validate([
            'razonsocial' => ['required', 'string', 'max:70'],
            'es_contribuyente' => ['required', 'boolean'],
            'ruc' => ['nullable', 'required_if:es_contribuyente,1', 'string', 'regex:/^[0-9]{5,8}$/', Rule::unique('clientes', 'ruc')->ignore($cliente?->id)],
            'dv' => ['nullable', 'required_if:es_contribuyente,1', 'string', 'regex:/^\d$/'],
            'tipo_contribuyente' => ['nullable', 'integer', 'in:1,2'],
            'tipo_identificacion' => ['nullable', 'required_if:es_contribuyente,0', 'integer', 'in:1,2,3,4,5,6,9'],
            'numero_identificacion' => ['nullable', 'required_if:es_contribuyente,0', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
