<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GastoController extends Controller
{
    /**
     * Listado de gastos
     */
    public function index()
    {
        $gastos = Gasto::with('usuario')
            ->orderBy('id', 'desc')
            ->get();

        return view('gasto.index', compact('gastos'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('gasto.create');
    }

    /**
     * Guardar nuevo gasto
     */
    public function store(Request $request)
    {
        $request->validate([
            'concepto'     => 'required|string|max:255',
            'monto'        => 'required|numeric|min:0.01',
            'fecha'        => 'required|date',
            'metodo_pago'  => 'required|in:efectivo,transferencia,tarjeta',
            'observacion'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $gasto = new Gasto();
            $gasto->user_id     = auth()->id();
            $gasto->concepto    = $request->concepto;
            $gasto->monto       = $request->monto;
            $gasto->fecha       = Carbon::parse($request->fecha);
            $gasto->metodo_pago = $request->metodo_pago;
            $gasto->observacion = $request->observacion;
            $gasto->estado      = 'pendiente';
            $gasto->save();

            DB::commit();

            return redirect()
                ->route('gasto.index')
                ->with('success', 'Gasto registrado correctamente');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error al registrar gasto: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al registrar el gasto');
        }
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $gasto = Gasto::findOrFail($id);
        return view('gasto.edit', compact('gasto'));
    }

    /**
     * Actualizar gasto
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'concepto'     => 'required|string|max:255',
            'monto'        => 'required|numeric|min:0.01',
            'fecha'        => 'required|date',
            'metodo_pago'  => 'required|in:efectivo,transferencia,tarjeta',
            'estado'       => 'required|in:pendiente,aprobado,rechazado',
            'observacion'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $gasto = Gasto::findOrFail($id);
            $gasto->concepto    = $request->concepto;
            $gasto->monto       = $request->monto;
            $gasto->fecha       = Carbon::parse($request->fecha);
            $gasto->metodo_pago = $request->metodo_pago;
            $gasto->estado      = $request->estado;
            $gasto->observacion = $request->observacion;
            $gasto->save();

            DB::commit();

            return redirect()
                ->route('gasto.index')
                ->with('success', 'Gasto actualizado correctamente');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error al actualizar gasto: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al actualizar el gasto');
        }
    }
}
