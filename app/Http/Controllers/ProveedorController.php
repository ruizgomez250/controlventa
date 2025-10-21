<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Proveedor;
use App\Models\Opcion;
use App\services\PermisoService;

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
            $proveedor = Proveedor::all();
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
            return view('proveedores.create');
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //dd($request->input());
        $tienePermiso = $this->permisoService->verificarPermiso('Proveedor', 'crear');
        if ($tienePermiso) {
            $estado = $request->input('estado');
            if ($estado === 'true') {
                $estado = 1;
            } elseif ($estado === 'false' || $estado === null) {
                $estado = 0;
            }else{
                $estado = $request->input('estado');
            }

            // Recoger todos los datos del request, incluyendo el campo 'estado' procesado
            $data = $request->all();
            $data['estado'] = $estado;

            // Crear el nuevo proveedor
            Proveedor::create($data);
            return redirect()->route('proveedor.create')->with('success', 'Operación exitosa');
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
            return view('proveedores.edit', ['proveedor' => $proveedor]);
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
        // Recoger todos los datos del request excepto 'estado'
        
        $data = $request->input();

        // Actualizar el proveedor con los datos procesados
        $proveedor->update($data);
        return redirect()->route('proveedor.index')->with('success', 'Proveedor actualizado con éxito');
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
        if (!$tienePermiso) {
            return redirect()->route('sinpermiso');
        }

        // Verificar si el proveedor tiene compras asociadas
        $tieneCompras = DB::table('compras_cab')
            ->where('id_proveedor', $proveedor->id)
            ->exists();

        if ($tieneCompras) {
            return redirect()->route('proveedor.index')
                ->with('error', 'No se puede eliminar el proveedor porque tiene compras registradas.');
        }

        // Opcional: verificar otras relaciones (ej. productos, etc.)

        $proveedor->delete();

        return redirect()->route('proveedor.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }
}
