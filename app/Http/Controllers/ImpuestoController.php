<?php

namespace App\Http\Controllers;

use App\Models\Impuesto;
use Illuminate\Http\Request;

class ImpuestoController extends Controller
{
    /**
     * Listar impuestos
     */
    public function index()
    {
        $heads = [
            'ID',
            'Descripción',
            ['label' => 'Valor (%)', 'class' => 'text-right'],
            ['label' => 'Acciones', 'no-export' => true, 'width' => 10],
        ];

        $impuestos = Impuesto::all();

        return view('impuestos.index', compact('impuestos', 'heads'));
    }


    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('impuestos.create');
    }

    /**
     * Guardar nuevo impuesto
     */
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:100',
            'valor' => 'required|numeric|min:0',
        ]);

        Impuesto::create([
            'descripcion' => $request->descripcion,
            'valor' => $request->valor,
        ]);

        return redirect()
            ->route('impuestos.index')
            ->with('success', 'Impuesto creado correctamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Impuesto $impuesto)
    {
        return view('impuestos.edit', compact('impuesto'));
    }

    /**
     * Actualizar impuesto
     */
    public function update(Request $request, Impuesto $impuesto)
    {
        $request->validate([
            'descripcion' => 'required|string|max:100',
            'valor' => 'required|numeric|min:0',
        ]);

        $impuesto->update($request->all());

        return redirect()
            ->route('impuestos.index')
            ->with('success', 'Impuesto actualizado correctamente');
    }

    /**
     * Eliminar impuesto
     */
    public function destroy(Impuesto $impuesto)
    {
        $impuesto->delete();

        return redirect()
            ->route('impuestos.index')
            ->with('success', 'Impuesto eliminado correctamente');
    }
}
