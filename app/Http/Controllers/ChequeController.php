<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use Illuminate\Http\Request;

class ChequeController extends Controller
{
    // LISTAR CHEQUES
    public function index()
    {
        abort_unless(auth()->user()->can('cheque leer'), 403);

        $cheques = Cheque::orderBy('fecha_cobro', 'asc')->get();

        $proximos = Cheque::proximos()->count();
        $vencidos = Cheque::vencidos()->count();

        $heads = [
            'N°',
            'Tipo',
            'N° Cheque',
            'Banco',
            'Titular',
            'Monto',
            'Fecha Emision',
            'Fecha Cobro',
            'Estado',
            ['label' => 'Acciones', 'no-export' => true, 'width' => 10],
        ];

        return view('cheques.index', compact(
            'cheques',
            'proximos',
            'vencidos',
            'heads'
        ));
    }

    // FORMULARIO CREAR
    public function create()
    {
        abort_unless(auth()->user()->can('cheque crear'), 403);

        return view('cheques.create');
    }

    // GUARDAR CHEQUE
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('cheque crear'), 403);

        $validated = $request->validate([
            'tipo' => 'required|string|max:30',
            'numero_cheque' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'titular' => 'nullable|string|max:150',
            'monto' => 'required|numeric|gt:0',
            'fecha_emision' => 'nullable|date',
            'fecha_cobro' => 'required|date',
            'estado' => 'nullable|string|in:pendiente,cobrado,rechazado,anulado',
            'observacion' => 'nullable|string|max:1000',
        ]);

        Cheque::create($validated);

        return redirect()
            ->route('cheques.index')
            ->with('success', 'Cheque registrado correctamente');
    }

    // FORMULARIO EDITAR
    public function edit($id)
    {
        abort_unless(auth()->user()->can('cheque editar'), 403);

        $cheque = Cheque::findOrFail($id);

        return view('cheques.edit', compact('cheque'));
    }

    // ACTUALIZAR CHEQUE
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('cheque editar'), 403);

        $cheque = Cheque::findOrFail($id);

        $validated = $request->validate([
            'tipo' => 'required|string|max:30',
            'numero_cheque' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'titular' => 'nullable|string|max:150',
            'monto' => 'required|numeric|gt:0',
            'fecha_emision' => 'nullable|date',
            'fecha_cobro' => 'required|date',
            'estado' => 'nullable|string|in:pendiente,cobrado,rechazado,anulado',
            'observacion' => 'nullable|string|max:1000',
        ]);

        $cheque->update($validated);

        return redirect()
            ->route('cheques.index')
            ->with('success', 'Cheque actualizado correctamente');
    }

    // ELIMINAR CHEQUE
    public function destroy($id)
    {
        abort_unless(auth()->user()->can('cheque borrar'), 403);

        $cheque = Cheque::findOrFail($id);

        $cheque->delete();

        return redirect()
            ->route('cheques.index')
            ->with('success', 'Cheque eliminado correctamente');
    }
}
