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
        $cliente->update($this->clientData($request, $cliente->id));
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

    private function clientData(Request $request, ?int $id = null): array
    {
        $this->normalizarRuc($request);
        return $request->validate([
            'razonsocial' => ['required', 'string', 'max:70'],
            'ruc' => ['nullable', 'string', 'regex:/^[0-9]{5,8}$/', Rule::unique('clientes', 'ruc')->ignore($id)],
            'dv' => ['nullable', 'string', 'regex:/^\d$/'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
