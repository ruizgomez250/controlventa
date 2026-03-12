<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChequeController extends Controller
{

    // LISTAR CHEQUES
    public function index()
    {
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
        return view('cheques.create');
    }


    // GUARDAR CHEQUE
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string',
            'numero_cheque' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'titular' => 'nullable|string|max:150',
            'monto' => 'required|numeric',
            'fecha_cobro' => 'required|date',
            'estado' => 'nullable|string'
        ]);

        Cheque::create($request->all());

        return redirect()
            ->route('cheques.index')
            ->with('success', 'Cheque registrado correctamente');
    }


    // FORMULARIO EDITAR
    public function edit($id)
    {
        $cheque = Cheque::findOrFail($id);

        return view('cheques.edit', compact('cheque'));
    }


    // ACTUALIZAR CHEQUE
    public function update(Request $request, $id)
    {
        $cheque = Cheque::findOrFail($id);

        $request->validate([
            'tipo' => 'required|string',
            'numero_cheque' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'titular' => 'nullable|string|max:150',
            'monto' => 'required|numeric',
            'fecha_cobro' => 'required|date',
            'estado' => 'nullable|string'
        ]);

        $cheque->update($request->all());

        return redirect()
            ->route('cheques.index')
            ->with('success', 'Cheque actualizado correctamente');
    }


    // ELIMINAR CHEQUE
    public function destroy($id)
    {
        $cheque = Cheque::findOrFail($id);

        $cheque->delete();

        return redirect()
            ->route('cheques.index')
            ->with('success', 'Cheque eliminado correctamente');
    }
}
