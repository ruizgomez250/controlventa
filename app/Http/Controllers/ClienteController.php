<?php

namespace App\Http\Controllers;

use App\Http\Resources\clienteresource;
use App\Models\Cliente;
use App\Models\Opcion;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        if (! auth()->user()->can('cliente leer')) {
            return view('sinpermiso.index');
        }
        try {
            $cliente = Cliente::all();
            if ($cliente->isEmpty()) {
                $cliente = collect();
            } else {
                $cliente = $cliente->sortBy('Razón Social');
            }
            $heads = [
                'ID',
                'Razón Social',
                'RUC',
                'Correo',
                'Teléfono',
                'Estado',
                'Acción',
            ];

            return view('clientes.index', ['clientes' => $cliente, 'heads' => $heads]);
        } catch (Exception $e) {
            return view('clientes.index')->with('message', 'No se pudo completar la operación.');
        }
    }

    public function indexA()
    {
        // Verificar permisos (opcional)

        try {
            // Obtener todos los clientes
            $clientes = Cliente::all();

            // Si no hay registros
            if ($clientes->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No se encontraron clientes registrados.',
                    'data' => [],
                ], 200);
            }

            // Ordenar por nombre o razón social
            $clientes = $clientes->sortBy('razonsocial')->values();

            // Devolver respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Lista de clientes obtenida correctamente.',
                'total' => $clientes->count(),
                'data' => $clientes,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los clientes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getClientes(string $id)
    {
        if (! auth()->user()->can('cliente editar')) {
            return view('sinpermiso.index');
        }
        try {
            $clientes = Cliente::findOrFail($id);
            // cuando es una lista: $clienteFormat = clienteresource::collection($clientes);
            /*cuando es un solo registro*/
            $response = [
                'status' => 200, // 200 para éxito
                'data' => $clientes,
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => 500, // 500 para error
                'error' => $e->getMessage(),
            ];
        }

        return response()->json($response);
    }

    public function create(): View
    {
        if (! auth()->user()->can('cliente crear')) {
            return view('sinpermiso.index');
        }
        try {
            $opcion = Opcion::where('id_dominio', 1)->get();

            return view('clientes.create', ['opcion' => $opcion]);
        } catch (Exception $e) {
            return view('clientes.index')->with('message', 'No se pudo completar la operación.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        if (! auth()->user()->can('cliente crear')) {
            return redirect()->route('sinpermiso');
        }
        try {
            $validated = $request->validate($this->rules());

            Cliente::create($validated);

            return redirect()->route('cliente.create')->with('success', 'Operación exitosa');
        } catch (ValidationException $e) {
            return redirect()->route('cliente.create')->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            return redirect()->route('cliente.create')->with('error', 'No se pudo completar la operación.');
        }
    }

    public function apistore(Request $request)
    {
        try {
            Cliente::create($request->all());

            return redirect()->route('cliente.create')->with('success', 'Operación exitosa');
        } catch (ValidationException $e) {
            return redirect()->route('cliente.create')->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            return redirect()->route('cliente.create')->with('error', 'No se pudo completar la operación.');
        }
    }

    public function storeA(Request $request)
    {

        try {
            // Validación: solo 'nombre' es obligatorio
            $validatedData = $request->validate([
                'razonsocial' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:30',
                'correo' => 'nullable|email|max:255',
                'direccion' => 'nullable|string|max:255',
                'observacion' => 'nullable|string',
                'estado' => 'required|integer',
            ]);

            // Crear el registro
            $cliente = Cliente::create($validatedData);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente.',
                'data' => $cliente,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear el cliente.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Cliente $cliente): View
    {
        if (! auth()->user()->can('cliente editar')) {
            return view('sinpermiso.index');
        }
        try {
            $opcion = Opcion::where('id_dominio', 1)->get();

            return view('clientes.edit', ['opcion' => $opcion, 'cli' => $cliente]);
        } catch (Exception $e) {
            return view('clientes.index')->with('message', 'No se pudo completar la operación.');
        }
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        if (! auth()->user()->can('cliente editar')) {
            return redirect()->route('sinpermiso');
        }
        try {
            $cliente->update($request->validate($this->rules()));

            return redirect()->route('cliente.index')->with('success', 'Operación exitosa');
        } catch (Exception $e) {
            return redirect()->route('cliente.index')->with('error', 'no se pudo completar la operacion!!');
        }
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if (! auth()->user()->can('cliente borrar')) {
            return redirect()->route('sinpermiso');
        }
        try {
            $cliente->delete();

            return redirect()->route('cliente.index')->with('success', 'Operación exitosa');
        } catch (Exception $e) {
            return redirect()->route('cliente.index')->with('error', 'No se pudo completar la operación.');
        }
    }

    public function guardar(Request $request)
    {
        if (! auth()->user()->can('cliente crear')) {
            return view('sinpermiso.index');
        }
        Cliente::create($request->validate($this->rules()));
        // Retorna una respuesta JSON con código de respuesta HTTP 201
        return response()->json([
            'message' => 'Cliente creado correctamente',
            'success' => 'success',
        ], 201);
    }

    private function rules(): array
    {
        return [
            'razonsocial' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email:rfc', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'celular' => ['nullable', 'string', 'max:30'],
            'estado' => ['nullable', 'integer'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
