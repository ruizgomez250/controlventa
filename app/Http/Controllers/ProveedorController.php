<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Proveedor;
use App\Models\Opcion;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): view
    {

        if (!auth()->user()->can('proveedor leer')) {
            return view('sinpermiso.index');
        }
        //obtenemos los datos
        $proveedor = Proveedor::all();
        //asignar cabecera datatable
        $heads = [
            'ID',
            'Razón Social',
            'RUC',
            'Correo',
            'Teléfono',
            'Estado',
            'Acción'
        ];
        return view('proveedores.index', ['proveedores' => $proveedor, 'heads' => $heads]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): view
    {
        if (!auth()->user()->can('proveedor crear')) {
            return view('sinpermiso.index');
        }
        return view('proveedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //dd($request->input());
        if (!auth()->user()->can('proveedor crear')) {
            return redirect()->route('sinpermiso');
        }
        $estado = $request->input('estado');
        if ($estado === 'true') {
            $estado = 1;
        } elseif ($estado === 'false' || $estado === null) {
            $estado = 0;
        } else {
            $estado = $request->input('estado');
        }

        // Recoger todos los datos del request, incluyendo el campo 'estado' procesado
        $data = $request->all();
        $data['estado'] = $estado;

        // Crear el nuevo proveedor
        Proveedor::create($data);
        return redirect()->route('proveedor.create')->with('success', 'Operación exitosa');
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
        if (!auth()->user()->can('proveedor editar')) {
            return view('sinpermiso.index');
        }
        // dd($proveedor->razonsocial);
        return view('proveedores.edit', ['proveedor' => $proveedor]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        if (!auth()->user()->can('proveedor editar')) {
            return redirect()->route('sinpermiso');
        }
        // Recoger todos los datos del request excepto 'estado'

        $data = $request->input();

        // Actualizar el proveedor con los datos procesados
        $proveedor->update($data);
        return redirect()->route('proveedor.index')->with('success', 'Proveedor actualizado con éxito');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if (!auth()->user()->can('proveedor borrar')) {
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
