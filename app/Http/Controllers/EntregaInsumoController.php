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
use TCPDF;
use Carbon\Carbon;

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
        $productos = Producto::where('estado', 1)
            ->where('stock', '>', 0)
            ->whereIn('tipo', ['uso_interno', 'ambos'])
            ->get();
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
        $productos = Producto::where('estado', 1)
            ->whereIn('tipo', ['uso_interno', 'ambos'])
            ->get();
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

    public function generarComprobante($id)
    {
        if (!auth()->user()->can('entrega_insumo leer')) {
            return redirect()->route('sinpermiso');
        }

        $entrega = EntregaInsumo::with('persona', 'usuario', 'detalles.producto')->findOrFail($id);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->SetCreator('easyStock');
        $pdf->SetTitle('Comprobante de Entrega de Insumos #' . $entrega->id);

        // Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, __('COMPROBANTE DE ENTREGA DE INSUMOS'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, __('Cod.') . ' #' . str_pad($entrega->id, 6, '0', STR_PAD_LEFT), 0, 1, 'C');
        $pdf->Ln(5);

        // Separator line
        $y = $pdf->GetY();
        $pdf->Line(15, $y, 195, $y);
        $pdf->Ln(5);

        // Delivery info
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 6, __('Fecha') . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, Carbon::parse($entrega->fecha)->format('d/m/Y'), 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 6, __('Recibido por') . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, $entrega->persona->nombre . ' ' . $entrega->persona->apellido, 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 6, __('Entregado por') . ':', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, $entrega->usuario->name, 0, 1, 'L');

        if ($entrega->observacion) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(50, 6, __('Observación') . ':', 0, 0, 'L');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->MultiCell(0, 6, $entrega->observacion, 0, 'L');
        }

        $pdf->Ln(5);
        $y = $pdf->GetY();
        $pdf->Line(15, $y, 195, $y);
        $pdf->Ln(5);

        // Table header
        $colW = [120, 60];
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($colW[0], 8, __('Producto'), 1, 0, 'C', true);
        $pdf->Cell($colW[1], 8, __('Cantidad'), 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);

        // Table rows
        foreach ($entrega->detalles as $det) {
            $pdf->Cell($colW[0], 7, '  ' . $det->producto->descripcion, 1, 0, 'L');
            $pdf->Cell($colW[1], 7, number_format($det->cantidad, 3, ',', '.'), 1, 1, 'C');
        }

        // Total row
        $totalItems = $entrega->detalles->count();
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($colW[0], 7, '  ' . __('Total de items') . ':', 1, 0, 'L');
        $pdf->Cell($colW[1], 7, $totalItems, 1, 1, 'C');

        $pdf->Ln(15);

        // Signature section
        $pdf->SetFont('helvetica', '', 10);
        $sigY = $pdf->GetY();

        // Signature lines
        $pdf->Cell(80, 5, '____________________________', 0, 0, 'C');
        $pdf->Cell(30, 5, '', 0, 0, 'C');
        $pdf->Cell(80, 5, '____________________________', 0, 1, 'C');

        $pdf->Cell(80, 5, __('Recibí Conforme'), 0, 0, 'C');
        $pdf->Cell(30, 5, '', 0, 0, 'C');
        $pdf->Cell(80, 5, __('Entregué Conforme'), 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(80, 5, $entrega->persona->nombre . ' ' . $entrega->persona->apellido, 0, 0, 'C');
        $pdf->Cell(30, 5, '', 0, 0, 'C');
        $pdf->Cell(80, 5, $entrega->usuario->name, 0, 1, 'C');

        $pdf->Output('comprobante_entrega_' . $entrega->id . '.pdf', 'I');
        exit;
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
