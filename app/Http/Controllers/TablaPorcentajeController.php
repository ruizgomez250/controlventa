<?php

namespace App\Http\Controllers;

use App\Models\TablaPorcentaje;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TablaPorcentajeController extends Controller
{
    public function index(): View
    {
        if (!auth()->user()->can('tabla_porcentaje leer')) {
            return view('sinpermiso.index');
        }
        $tablaporc = TablaPorcentaje::All();
        $heads = [
            'ID', 'Cant. Cuotas', 'Porcentaje %', 'Estado', 'Acción'
        ];
        return view('tablaporc.index', ['tablaporc' => $tablaporc, 'heads' => $heads]);
    }

    public function create(): View
    {
        if (!auth()->user()->can('tabla_porcentaje modificar')) {
            return view('sinpermiso.index');
        }
        return view('tablaporc.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('tabla_porcentaje modificar')) {
            return view('sinpermiso.index');
        }
        try {
            $request->validate([
                'cuota' => 'required|numeric|min:2',
                'porcentaje' => 'required|numeric|min:0',
            ]);
            $estado = 1;
            $request->merge(['estado' => $estado]);
            TablaPorcentaje::create($request->all());
            return redirect()->route('tablaporc.index')->with('success', 'Registro creado exitosamente');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el registro: ' . $e->getMessage());
        }
    }

    public function update(Request $request): RedirectResponse
    {
        if (!auth()->user()->can('tabla_porcentaje modificar')) {
            return redirect()->route('sinpermiso');
        }
        $id = $request->input('id');
        $estado = $request->has('estado') ? 1 : 0;
        $cuota = $request->input('cuota');
        $porcentaje = $request->input('porcentaje');
        $porcentajeEntry = TablaPorcentaje::find($id);
        if ($porcentajeEntry) {
            $porcentajeEntry->estado = $estado;
            $porcentajeEntry->cuota = $cuota ?? $porcentajeEntry->cuota;
            $porcentajeEntry->porcentaje = $porcentaje ?? $porcentajeEntry->porcentaje;
            $porcentajeEntry->save();
            return redirect()->back()->with('success', '¡La entrada se ha actualizado correctamente!');
        } else {
            return redirect()->back()->with('error', '¡No se encontró la entrada correspondiente en la tabla!');
        }
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->can('tabla_porcentaje modificar')) {
            return redirect()->route('sinpermiso');
        }
        $tabla = TablaPorcentaje::find($id);
        if (!$tabla) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }
        try {
            $tabla->estado = 0;
            $tabla->save();
            return redirect()->route('tablaporc.index')->with('success', 'Registro desactivado con éxito.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'No se puede desactivar el registro debido a restricciones de clave foránea.');
        }
    }
}
