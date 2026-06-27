<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PersonaController extends Controller
{
    public function index(): View
    {
        if (!auth()->user()->can('persona leer')) {
            return view('sinpermiso.index');
        }
        $personas = Persona::all();
        $heads = [
            'ID',
            'Nombre',
            'Apellido',
            'Documento',
            'Teléfono',
            'Estado',
            'Acción'
        ];
        return view('personas.index', compact('personas', 'heads'));
    }

    public function create(): View
    {
        if (!auth()->user()->can('persona crear')) {
            return view('sinpermiso.index');
        }
        return view('personas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->can('persona crear')) {
            return redirect()->route('sinpermiso');
        }
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'documento' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'observacion' => 'nullable|string',
            'estado' => 'nullable|boolean',
        ]);
        $data['estado'] = $request->boolean('estado');
        Persona::create($data);
        return redirect()->route('persona.index')->with('success', 'Persona registrada correctamente.');
    }

    public function edit(Persona $persona): View
    {
        if (!auth()->user()->can('persona editar')) {
            return view('sinpermiso.index');
        }
        return view('personas.edit', compact('persona'));
    }

    public function update(Request $request, Persona $persona): RedirectResponse
    {
        if (!auth()->user()->can('persona editar')) {
            return redirect()->route('sinpermiso');
        }
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'documento' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'observacion' => 'nullable|string',
            'estado' => 'nullable|boolean',
        ]);
        $data['estado'] = $request->boolean('estado');
        $persona->update($data);
        return redirect()->route('persona.index')->with('success', 'Persona actualizada correctamente.');
    }

    public function destroy(Persona $persona): RedirectResponse
    {
        if (!auth()->user()->can('persona borrar')) {
            return redirect()->route('sinpermiso');
        }
        if ($persona->entregas()->exists()) {
            return redirect()->route('persona.index')
                ->with('error', 'No se puede eliminar la persona porque tiene entregas registradas.');
        }
        $persona->delete();
        return redirect()->route('persona.index')->with('success', 'Persona eliminada correctamente.');
    }
}
