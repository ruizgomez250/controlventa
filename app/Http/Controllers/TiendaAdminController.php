<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\TiendaConfiguracion;
use App\Models\TiendaPedido;
use App\Models\TiendaProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TiendaAdminController extends Controller
{
    public function configuracion(Request $request)
    {
        $request->validate(['buscar' => 'nullable|string|max:100']);
        $config = TiendaConfiguracion::actual();
        $productos = Producto::with('tienda')->when($request->filled('buscar'), fn ($q) => $q->where('descripcion', 'like', '%' . $request->string('buscar') . '%'))
            ->orderByDesc('id')->paginate(20)->withQueryString();
        return view('tienda.admin.configuracion', compact('config', 'productos'));
    }

    public function guardarConfiguracion(Request $request)
    {
        $v = $request->validate([
            'nombre' => 'required|string|max:100', 'descripcion' => 'required|string|max:250',
            'whatsapp' => ['nullable', 'regex:/^[1-9][0-9]{7,14}$/'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'aviso' => 'required|string|max:200', 'habilitada' => 'required|boolean',
        ]);
        TiendaConfiguracion::actual()->update($v);
        return back()->with('success', 'Configuración de la tienda guardada.');
    }

    public function guardarProducto(Request $request, int $id)
    {
        $producto = Producto::findOrFail($id);
        $v = $request->validate([
            'visible' => 'required|boolean', 'premium' => 'required|boolean', 'destacado' => 'required|boolean',
            'precio_oferta' => ['nullable', 'numeric', 'min:0.01', function ($attribute, $value, $fail) use ($producto) {
                if ((float) $value >= (float) $producto->pventa) $fail('El precio de oferta debe ser menor al precio de venta.');
            }],
            'oferta_hasta' => 'nullable|date',
        ]);
        TiendaProducto::updateOrCreate(['producto_id' => $id], $v);
        return back()->with('success', 'Presentación del producto actualizada.');
    }

    public function pedidos(Request $request)
    {
        $request->validate(['estado' => ['nullable', Rule::in(['pendiente', 'confirmado', 'preparando', 'entregado', 'cancelado'])]]);
        $pedidos = TiendaPedido::when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->latest('id')->paginate(20)->withQueryString();
        return view('tienda.admin.pedidos', compact('pedidos'));
    }

    public function actualizarPedido(Request $request, int $id)
    {
        $v = $request->validate(['estado' => ['required', Rule::in(['pendiente', 'confirmado', 'preparando', 'entregado', 'cancelado'])]]);
        TiendaPedido::findOrFail($id)->update($v);
        return back()->with('success', 'Estado del pedido actualizado.');
    }
}
