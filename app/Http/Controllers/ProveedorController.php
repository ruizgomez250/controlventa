<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Proveedor;
use App\Models\Opcion;
use App\Services\PermisoService;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }
    public function index(): view
    {
        
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'leer');
        if ($tienePermiso) {
            //obtenemos los datos
            $proveedor = Proveedor::with('estadoproveedor')->get();
            //asignar cabecera datatable
            $heads = [
                'ID', 'Razón Social', 'RUC', 'Correo', 'Teléfono', 'Estado', 'Acción'
            ];
            return view('proveedores.index', ['proveedores' => $proveedor, 'heads' => $heads]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): view
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'crear');
        if ($tienePermiso) {
            $opcion = Opcion::where('id_dominio', 2)->get();
            return view('proveedores.create', ['opcion' => $opcion]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'crear');
        if ($tienePermiso) {
            $request->validate(['razonsocial' => 'required']);

            Proveedor::create($request->all());
            return redirect()->route('proveedor.index');
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proveedor $proveedor): view
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'editar');
        if ($tienePermiso) {
            // dd($proveedor->razonsocial);
            $opcion = Opcion::where('id_dominio', 2)->get();
            return view('proveedores.edit', ['opcion' => $opcion, 'proveedor' => $proveedor]);
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'editar');
        if ($tienePermiso) {
            $proveedor->update($request->all());
            return redirect()->route('proveedor.index');
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'borrar');
        if ($tienePermiso) {
            $proveedor->delete();
            return redirect()->route('proveedor.index');
        } else {
            return redirect()->route('sinpermiso');
        }
    }
}
