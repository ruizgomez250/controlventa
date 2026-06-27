<?php

namespace App\Http\Controllers;

use App\Models\EntregaInsumo;
use App\Models\EntregaInsumoDetalle;
use App\Models\Persona;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class EntregaInsumoController extends Controller
{
    public function index(): View
    {
        if (!auth()->user()->can('entrega_insumo leer')) {
            return view('sinpermiso.index');
        }
        $entregas = EntregaInsumo::with('persona', 'usuario')->get();
        $heads = [
            'ID',
            'Fecha',
            'Persona',
            'Observación',
            'Usuario',
            'Estado',
            'Acción'
        ];
        return view('entrega_insumos.index', compact('entregas', 'heads'));
    }

    public function create(): View
    {
        if (!auth()->user()->can('entrega_insumo crear')) {
            return view('sinpermiso.index');
        }
        $personas = Persona::where('estado', 1)->get();
        $productos = Producto::where('estado', 1)->where('stock', '>', 0)->get();
        return view('entrega_insumos.create', compact('personas', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->can('entrega_insumo crear')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();

            $cabecera = new EntregaInsumo();
            $cabecera->fecha = $request->fecha;
            $cabecera->id_persona = $request->id_persona;
            $cabecera->observacion = $request->observacion;
            $cabecera->id_usuario = auth()->id();
            $cabecera->estado = true;
            $cabecera->save();

            $productos = $request->input('id_producto', []);
            $cantidades = $request->input('cantidad', []);

            for ($i = 0; $i < count($productos); $i++) {
                if (empty($productos[$i]) || $cantidades[$i] <= 0) continue;

                $producto = Producto::findOrFail($productos[$i]);

                if ($producto->stock < $cantidades[$i]) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Stock insuficiente para el producto: {$producto->descripcion}. Stock actual: {$producto->stock}");
                }

                $detalle = new EntregaInsumoDetalle();
                $detalle->id_entrega = $cabecera->id;
                $detalle->id_producto = $productos[$i];
                $detalle->cantidad = $cantidades[$i];
                $detalle->save();

                $producto->stock = $producto->stock - $cantidades[$i];
                $producto->save();
            }

            DB::commit();
            return redirect()->route('entrega_insumo.index')
                ->with('success', 'Entrega de insumos registrada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar entrega de insumos: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la entrega de insumos: ' . $e->getMessage());
        }
    }

    public function edit(EntregaInsumo $entregaInsumo): View
    {
        if (!auth()->user()->can('entrega_insumo editar')) {
            return view('sinpermiso.index');
        }
        $entregaInsumo->load('detalles.producto');
        $personas = Persona::where('estado', 1)->get();
        $productos = Producto::where('estado', 1)->get();
        return view('entrega_insumos.edit', compact('entregaInsumo', 'personas', 'productos'));
    }

    public function update(Request $request, EntregaInsumo $entregaInsumo): RedirectResponse
    {
        if (!auth()->user()->can('entrega_insumo editar')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();

            $detallesAnteriores = $entregaInsumo->detalles;

            foreach ($detallesAnteriores as $det) {
                $producto = Producto::findOrFail($det->id_producto);
                $producto->stock = $producto->stock + $det->cantidad;
                $producto->save();
                $det->delete();
            }

            $entregaInsumo->fecha = $request->fecha;
            $entregaInsumo->id_persona = $request->id_persona;
            $entregaInsumo->observacion = $request->observacion;
            $entregaInsumo->save();

            $productos = $request->input('id_producto', []);
            $cantidades = $request->input('cantidad', []);

            for ($i = 0; $i < count($productos); $i++) {
                if (empty($productos[$i]) || $cantidades[$i] <= 0) continue;

                $producto = Producto::findOrFail($productos[$i]);

                if ($producto->stock < $cantidades[$i]) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Stock insuficiente para el producto: {$producto->descripcion}. Stock actual: {$producto->stock}");
                }

                $detalle = new EntregaInsumoDetalle();
                $detalle->id_entrega = $entregaInsumo->id;
                $detalle->id_producto = $productos[$i];
                $detalle->cantidad = $cantidades[$i];
                $detalle->save();

                $producto->stock = $producto->stock - $cantidades[$i];
                $producto->save();
            }

            DB::commit();
            return redirect()->route('entrega_insumo.index')
                ->with('success', 'Entrega de insumos actualizada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar entrega de insumos: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la entrega de insumos: ' . $e->getMessage());
        }
    }

    public function destroy(EntregaInsumo $entregaInsumo): RedirectResponse
    {
        if (!auth()->user()->can('entrega_insumo borrar')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();

            foreach ($entregaInsumo->detalles as $det) {
                $producto = Producto::findOrFail($det->id_producto);
                $producto->stock = $producto->stock + $det->cantidad;
                $producto->save();
            }

            $entregaInsumo->detalles()->delete();
            $entregaInsumo->delete();

            DB::commit();
            return redirect()->route('entrega_insumo.index')
                ->with('success', 'Entrega de insumos eliminada correctamente. Stock restaurado.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar entrega de insumos: ' . $e->getMessage());
            return redirect()->route('entrega_insumo.index')
                ->with('error', 'Error al eliminar la entrega de insumos: ' . $e->getMessage());
        }
    }

    public function getDetalles($id)
    {
        if (!auth()->user()->can('entrega_insumo leer')) {
            return response()->json([]);
        }
        $detalles = EntregaInsumoDetalle::where('id_entrega', $id)
            ->with('producto')
            ->get();
        return response()->json($detalles);
    }
}
