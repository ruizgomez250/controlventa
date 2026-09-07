<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\GarmentType;
use App\Models\ClothingSize;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileSyncController extends Controller
{
    public function updatedProducts(Request $request): JsonResponse
    {
        $request->validate(['last_sync' => ['nullable', 'date']]);
        $query = Producto::query()->with(['categoriaproducto', 'unidaddemedida', 'impuesto', 'garmentType', 'clothingSize']);
        if ($request->filled('last_sync')) $query->where('updated_at', '>', $request->date('last_sync'));
        return response()->json(['success' => true, 'server_time' => now()->toIso8601String(), 'data' => $query->orderBy('updated_at')->get()]);
    }

    public function uploadProduct(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_uuid' => ['required','uuid'], 'codigo' => ['required','string','max:14'],
            'descripcion' => ['required','string','max:150'], 'detalle' => ['nullable','string'],
            'stock' => ['required','numeric','min:0'], 'stock_minimo' => ['nullable','numeric','min:0'],
            'pventa' => ['required','numeric','min:0'], 'id_medida' => ['nullable','integer'],
            'garment_type' => ['nullable','string','max:100'], 'clothing_size' => ['nullable','string','max:30'],
            'brand' => ['nullable','string','max:80'], 'color' => ['nullable','string','max:60'],
            'age_group' => ['nullable','string','max:40'], 'collection' => ['nullable','string','max:100'],
            'bale_id' => ['nullable','integer','exists:bales,id'],
        ]);
        $data['garment_type_id'] = filled($data['garment_type'] ?? null) ? GarmentType::firstOrCreate(['name' => trim($data['garment_type'])])->id : null;
        $data['clothing_size_id'] = filled($data['clothing_size'] ?? null) ? ClothingSize::firstOrCreate(['name' => trim($data['clothing_size'])])->id : null;
        unset($data['garment_type'], $data['clothing_size']);
        $data += ['estado' => 1, 'pcosto' => 0];
        $producto = Producto::updateOrCreate(['client_uuid' => $data['client_uuid']], $data);
        if (!empty($data['bale_id'])) DB::table('bale_product_items')->insertOrIgnore(['bale_id' => $data['bale_id'], 'producto_id' => $producto->id, 'quantity' => $data['stock'], 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['success' => true, 'data' => $producto], $producto->wasRecentlyCreated ? 201 : 200);
    }

    public function clothingCatalog(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'garment_types' => GarmentType::orderBy('name')->pluck('name'),
            'clothing_sizes' => ClothingSize::orderBy('sort_order')->orderBy('name')->pluck('name'),
        ]);
    }

    public function uploadPhoto(Request $request, int $id): JsonResponse
    {
        $request->validate(['photo' => ['required','image','max:10240']]);
        $producto = Producto::findOrFail($id);
        if ($producto->imagen) Storage::disk('public')->delete($producto->imagen);
        $producto->update(['imagen' => $request->file('photo')->store('productos', 'public')]);
        return response()->json(['success' => true, 'data' => $producto->fresh()]);
    }

    public function uploadSale(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_uuid' => ['required','uuid'], 'id_cliente' => ['required','integer','exists:clientes,id'],
            'payment_method' => ['required','in:EFECTIVO,TARJETA,TRANSFERENCIA,CREDITO'], 'fecha_emision' => ['required','date'],
            'items' => ['required','array','min:1'], 'items.*.id_producto' => ['required','integer','exists:productos,id'],
            'items.*.cantidad' => ['required','numeric','gt:0'], 'items.*.precio_u' => ['required','numeric','min:0'],
        ]);
        $existing = Venta::where('client_uuid', $data['client_uuid'])->first();
        if ($existing) return response()->json(['success' => true, 'data' => $existing->load('detalles')]);

        $venta = DB::transaction(function () use ($data, $request) {
            $total = collect($data['items'])->sum(fn ($item) => $item['cantidad'] * $item['precio_u']);
            $venta = Venta::create([
                'client_uuid' => $data['client_uuid'], 'id_usuario' => $request->user()->id, 'id_cliente' => $data['id_cliente'],
                'tipo_comprobante' => $data['payment_method'] === 'CREDITO' ? 'CREDITO' : 'CONTADO',
                'payment_method' => $data['payment_method'], 'total' => $total, 'fecha_emision' => $data['fecha_emision'], 'estado' => 1,
            ]);
            foreach ($data['items'] as $item) {
                $producto = Producto::whereKey($item['id_producto'])->lockForUpdate()->firstOrFail();
                if ((float) $producto->stock < (float) $item['cantidad']) {
                    throw ValidationException::withMessages(['stock' => ["Stock insuficiente para {$producto->descripcion}."]]);
                }
                $producto->decrement('stock', $item['cantidad']);
                $venta->detalles()->create(['id_producto' => $producto->id, 'cantidad' => $item['cantidad'], 'descripcion' => $producto->descripcion, 'precio_u' => $item['precio_u'], 'monto' => $item['cantidad'] * $item['precio_u']]);
            }
            return $venta;
        });
        return response()->json(['success' => true, 'data' => $venta->load('detalles')], 201);
    }
}
