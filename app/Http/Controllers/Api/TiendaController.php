<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TiendaConfiguracion;
use App\Models\TiendaPedido;
use App\Services\TiendaCatalogo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TiendaController extends Controller
{
    public function __construct(private TiendaCatalogo $catalogo) {}

    private function configuracion(): TiendaConfiguracion
    {
        $c = TiendaConfiguracion::actual();
        abort_unless($c->habilitada, 503, 'La tienda está temporalmente cerrada. Volvé a visitarnos pronto.');
        return $c;
    }

    public function inicio()
    {
        $c = $this->configuracion();
        $categorias = $this->catalogo->query()->select('id_categoria')->distinct()->get()
            ->map(fn ($p) => ['id' => $p->id_categoria, 'nombre' => $p->categoriaproducto?->descripcion])
            ->filter(fn ($p) => $p['id'] && $p['nombre'])->values();
        return response()->json([
            'tienda' => $c->only(['identificador', 'nombre', 'descripcion', 'color', 'moneda', 'aviso']),
            'pedidos_habilitados' => (bool) $c->whatsapp,
            'categorias' => $categorias,
        ])->header('Cache-Control', 'no-store');
    }

    public function productos(Request $request)
    {
        $this->configuracion();
        $v = $request->validate([
            'buscar' => 'nullable|string|max:100', 'categoria' => 'nullable|integer|min:1',
            'seccion' => ['nullable', Rule::in(['todos', 'ofertas', 'premium', 'destacados'])],
            'orden' => ['nullable', Rule::in(['nuevos', 'precio_asc', 'precio_desc'])],
            'ids' => 'nullable|array|max:24', 'ids.*' => 'integer|min:1',
            'page' => 'nullable|integer|min:1',
        ]);
        $q = $this->catalogo->query();
        if (!empty($v['buscar'])) $q->where(fn (Builder $b) => $b->where('descripcion', 'like', '%' . trim($v['buscar']) . '%')->orWhere('codigo', 'like', '%' . trim($v['buscar']) . '%')->orWhere('brand', 'like', '%' . trim($v['buscar']) . '%'));
        if (!empty($v['categoria'])) $q->where('id_categoria', $v['categoria']);
        if (isset($v['ids'])) $q->whereIn('id', $v['ids']);
        if (in_array($v['seccion'] ?? '', ['premium', 'destacados'])) {
            $campo = $v['seccion'] === 'premium' ? 'premium' : 'destacado';
            $q->whereHas('tienda', fn (Builder $t) => $t->where($campo, true));
        }
        if (($v['seccion'] ?? '') === 'ofertas') $q->whereHas('tienda', fn (Builder $t) => $t->where('precio_oferta', '>', 0)->whereColumn('precio_oferta', '<', 'productos.pventa')->where(fn (Builder $b) => $b->whereNull('oferta_hasta')->orWhere('oferta_hasta', '>', now())));
        $precioSql = '(CASE WHEN tienda_productos.precio_oferta > 0 AND tienda_productos.precio_oferta < productos.pventa AND (tienda_productos.oferta_hasta IS NULL OR tienda_productos.oferta_hasta > ?) THEN tienda_productos.precio_oferta ELSE productos.pventa END)';
        if (in_array($v['orden'] ?? '', ['precio_asc', 'precio_desc'])) {
            $q->leftJoin('tienda_productos', 'tienda_productos.producto_id', '=', 'productos.id')->select('productos.*')
                ->orderByRaw($precioSql . (($v['orden'] === 'precio_asc') ? ' ASC' : ' DESC'), [now()]);
        }
        $resultado = $q->orderByDesc('productos.id')->paginate(12);
        return response()->json(['data' => $resultado->getCollection()->map(fn ($p) => $this->catalogo->producto($p)), 'meta' => ['pagina' => $resultado->currentPage(), 'paginas' => $resultado->lastPage(), 'total' => $resultado->total()]]);
    }

    public function producto(int $id)
    {
        $this->configuracion();
        return response()->json(['data' => $this->catalogo->producto($this->catalogo->query()->findOrFail($id))]);
    }

    public function imagen(int $id)
    {
        $this->configuracion();
        $p = $this->catalogo->query()->findOrFail($id);
        abort_unless($p->imagen && str_starts_with($p->imagen, 'productos/') && !str_contains($p->imagen, '..'), 404);
        abort_unless(Storage::disk('public')->exists($p->imagen), 404);
        return Storage::disk('public')->response($p->imagen, null, ['Cache-Control' => 'public, max-age=300', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function pedido(Request $request)
    {
        $c = $this->configuracion();
        abort_unless($c->whatsapp, 422, 'La tienda todavía no habilitó los pedidos por WhatsApp.');
        $v = $request->validate([
            'referencia' => 'required|uuid', 'token' => 'required|string|min:32|max:100',
            'nombre' => 'required|string|max:100', 'telefono' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9 ()-]{6,25}$/'],
            'entrega' => ['required', Rule::in(['retiro', 'envio'])],
            'direccion' => 'nullable|required_if:entrega,envio|string|max:250', 'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1|max:50', 'items.*.id' => 'required|integer|min:1|distinct',
            'items.*.cantidad' => 'required|integer|min:1|max:1000', 'items.*.precio' => 'required|numeric|min:0',
        ]);
        $hash = hash('sha256', json_encode($v));
        $pedido = DB::transaction(function () use ($v, $hash, $c) {
            $anterior = TiendaPedido::where('referencia', $v['referencia'])->first();
            if ($anterior) {
                abort_unless(hash_equals($anterior->solicitud_hash, $hash), 409, 'Esta referencia ya corresponde a otro pedido.');
                return $anterior;
            }
            $productos = $this->catalogo->query()->whereIn('id', array_column($v['items'], 'id'))->lockForUpdate()->get()->keyBy('id');
            // A retry may have committed while this request waited for product locks.
            $anterior = TiendaPedido::where('referencia', $v['referencia'])->lockForUpdate()->first();
            if ($anterior) {
                abort_unless(hash_equals($anterior->solicitud_hash, $hash), 409, 'Esta referencia ya corresponde a otro pedido.');
                return $anterior;
            }
            $items = []; $total = 0;
            foreach ($v['items'] as $item) {
                $p = $productos->get($item['id']);
                abort_unless($p, 422, 'Un producto ya no está disponible. Revisá tu carrito.');
                abort_if((float) $p->stock < $item['cantidad'], 422, 'No hay suficiente disponibilidad de ' . $p->descripcion . '.');
                $publico = $this->catalogo->producto($p);
                abort_if(abs($publico['precio'] - (float) $item['precio']) > 0.009, 409, 'Cambió el precio de ' . $p->descripcion . '. Actualizá el carrito antes de enviar.');
                $subtotal = round($publico['precio'] * $item['cantidad'], 2);
                $items[] = ['id' => $p->id, 'codigo' => $p->codigo, 'nombre' => $p->descripcion, 'cantidad' => $item['cantidad'], 'precio' => $publico['precio'], 'subtotal' => $subtotal];
                $total += $subtotal;
            }
            return TiendaPedido::create([
                'uuid' => (string) Str::uuid(), 'referencia' => $v['referencia'], 'token_hash' => hash('sha256', $v['token']), 'solicitud_hash' => $hash,
                'nombre' => trim($v['nombre']), 'telefono' => $v['telefono'], 'entrega' => $v['entrega'],
                'direccion' => $v['direccion'] ?? null, 'observaciones' => $v['observaciones'] ?? null,
                'total' => round($total, 2), 'moneda' => $c->moneda, 'items' => $items,
            ]);
        });
        $texto = $c->nombre . ' · Pedido #' . $pedido->id . "\n" . $pedido->nombre . ' · ' . $pedido->telefono . "\n\n";
        foreach ($pedido->items as $i) $texto .= $i['cantidad'] . ' × ' . $i['nombre'] . ' (' . $i['codigo'] . ') — ' . number_format($i['subtotal'], 0, ',', '.') . ' ' . $pedido->moneda . "\n";
        $texto .= "\nTotal de productos: " . number_format((float) $pedido->total, 0, ',', '.') . ' ' . $pedido->moneda;
        $texto .= "\nEntrega: " . $pedido->entrega . ($pedido->direccion ? ' · ' . $pedido->direccion : '');
        if ($pedido->observaciones) $texto .= "\nObservaciones: " . $pedido->observaciones;
        $texto .= "\nSujeto a confirmación de disponibilidad y costo de envío.";
        return response()->json(['pedido' => $pedido->only(['uuid', 'id', 'estado', 'total', 'moneda', 'created_at', 'items']), 'whatsapp_url' => 'https://wa.me/' . $c->whatsapp . '?text=' . rawurlencode($texto)], 201)->header('Cache-Control', 'no-store');
    }

    public function estado(Request $request, string $uuid)
    {
        $this->configuracion();
        $p = TiendaPedido::where('uuid', $uuid)->firstOrFail();
        abort_unless(hash_equals($p->token_hash, hash('sha256', (string) $request->header('X-Pedido-Token'))), 404);
        return response()->json(['pedido' => $p->only(['uuid', 'id', 'estado', 'total', 'moneda', 'created_at', 'items'])])->header('Cache-Control', 'no-store');
    }
}
