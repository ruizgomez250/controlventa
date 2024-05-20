<?php

namespace App\Http\Controllers;

use App\Http\Requests\clienterequest;
use App\Http\Resources\clienteresource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Cliente;
use App\Models\Opcion;
use App\Services\PermisoService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Exception;

class ClienteController extends Controller
{
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }
    public function index(): View
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'leer');
        if ($tienePermiso) {
            try {
                $cliente = Cliente::all();
                if ($cliente->isEmpty()) {
                    $cliente = collect();
                } else {
                    $cliente = $cliente->sortBy("Razón Social");
                }
                $heads = [
                    'ID', 'Razón Social', 'RUC', 'Correo', 'Teléfono', 'Estado', 'Acción'
                ];
                return view('clientes.index', ['clientes' => $cliente, 'heads' => $heads]);
            } catch (Exception $e) {
                return view('clientes.index')->with('message', 'No se pudo completar la operación.');
            }
        } else {
            return view('sinpermiso.index');
        }
    }

    public function getClientes(string $id)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'editar');
        if ($tienePermiso) {
            try {
                $clientes = Cliente::findOrFail($id);
                // cuando es una lista: $clienteFormat = clienteresource::collection($clientes);
                /*cuando es un solo registro*/
                $response = [
                    'status' => 200, // 200 para éxito
                    'data' =>  $clientes,
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 500, // 500 para error
                    'error' => $e->getMessage(),
                ];
            }

            return response()->json($response);
        } else {
            return view('sinpermiso.index');
        }
    }

    public function create(): View
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'crear');
        if ($tienePermiso) {
            try {
                $opcion = Opcion::where('id_dominio', 1)->get();
                return view('clientes.create', ['opcion' => $opcion]);
            } catch (Exception $e) {
                return view('clientes.index')->with('message', 'No se pudo completar la operación.');
            }
        } else {
            return view('sinpermiso.index');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'crear');
        if ($tienePermiso) {
            try {
                $request->validate(['razonsocial' => 'required']);
                Cliente::create($request->all());
                return redirect()->route('cliente.create')->with('success', 'Operación exitosa');
            } catch (ValidationException $e) {
                return redirect()->route('cliente.create')->withErrors($e->validator)->withInput();
            } catch (Exception $e) {
                return redirect()->route('cliente.create')->with('error', 'No se pudo completar la operación.');
            }
        } else {
            return view('sinpermiso.index');
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

    public function edit(Cliente $cliente): View
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'editar');
        if ($tienePermiso) {
            try {
                $opcion = Opcion::where('id_dominio', 1)->get();
                return view('clientes.edit', ['opcion' => $opcion, 'cli' => $cliente]);
            } catch (Exception $e) {
                return view('clientes.index')->with('message', 'No se pudo completar la operación.');
            }
        } else {
            return view('sinpermiso.index');
        }
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'editar');
        if ($tienePermiso) {
            try {
                $cliente->update($request->all());
                return redirect()->route('cliente.index')->with('success', 'Operación exitosa');
            } catch (Exception $e) {
                return view('clientes.index');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'borrar');
        if ($tienePermiso) {
            try {
                $cliente->delete();
                return redirect()->route('cliente.index')->with('success', 'Operación exitosa');
            } catch (Exception $e) {
                return redirect()->route('cliente.index')->with('error', 'No se pudo completar la operación.');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }


    public function guardar(Request $request)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Cliente', 'crear');
        if ($tienePermiso) {
            Cliente::create($request->all());
            // Retorna una respuesta JSON con código de respuesta HTTP 201
            return response()->json([
                'message' => 'Cliente creado correctamente', 'success' => 'success',
            ], 201);
        } else {
            return view('sinpermiso.index');
        }
    }
}
