<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bale;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\GarmentType;
use App\Models\ClothingSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BaleApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => ['required', 'in:pending,in_progress,finalized'], 'page' => ['nullable', 'integer', 'min:1']]);
        $page = Bale::with('supplierRelation')->withCount('products')->where('status', $data['status'])->latest()->paginate(30);
        return response()->json([
            'success' => true,
            'data' => $page->items(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
        ]);
    }

    public function show(Bale $bale): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $bale->load('supplierRelation')->loadCount('products')]);
    }

    public function products(Bale $bale): JsonResponse
    {
        $products = Producto::with(['garmentType', 'clothingSize'])
            ->where(function ($query) use ($bale) {
                $query->where('bale_id', $bale->id)->orWhereHas('additionalBales', fn ($q) => $q->where('bales.id', $bale->id));
            })->orderByDesc('productos.id')->get();
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function addExisting(Request $request, Bale $bale, Producto $producto): JsonResponse
    {
        $data = $request->validate([
            'descripcion' => ['required','string','max:150'], 'pventa' => ['required','numeric','min:0'],
            'garment_type' => ['nullable','string','max:100'], 'clothing_size' => ['nullable','string','max:30'],
            'brand' => ['nullable','string','max:80'], 'color' => ['nullable','string','max:60'],
            'age_group' => ['nullable','string','max:40'], 'gender' => ['nullable','string','max:30'], 'collection' => ['nullable','string','max:100'],
        ]);
        $data['garment_type_id'] = filled($data['garment_type'] ?? null) ? GarmentType::firstOrCreate(['name' => trim($data['garment_type'])])->id : null;
        $data['clothing_size_id'] = filled($data['clothing_size'] ?? null) ? ClothingSize::firstOrCreate(['name' => trim($data['clothing_size'])])->id : null;
        unset($data['garment_type'], $data['clothing_size']);
        DB::transaction(function () use ($bale, $producto, $data) {
            $locked = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
            $locked->increment('stock', 1);
            $locked->update($data);
            $current = DB::table('bale_product_items')->where(['bale_id' => $bale->id, 'producto_id' => $producto->id])->value('quantity');
            DB::table('bale_product_items')->updateOrInsert(
                ['bale_id' => $bale->id, 'producto_id' => $producto->id],
                ['quantity' => ((float) $current) + 1, 'updated_at' => now(), 'created_at' => now()]
            );
        });
        return response()->json(['success' => true, 'data' => $producto->fresh()->load(['garmentType','clothingSize'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purchase_date' => ['required','date'], 'supplier_id' => ['nullable','integer','exists:proveedores,id'], 'bale_type' => ['nullable','string','max:100'],
            'purchase_amount' => ['required','numeric','min:0'], 'freight_amount' => ['nullable','numeric','min:0'],
            'other_costs' => ['nullable','numeric','min:0'], 'estimated_quantity' => ['nullable','integer','min:1'], 'notes' => ['nullable','string'],
        ]);
        $data += ['code' => 'FAR-' . now()->format('Ymd-His'), 'created_by' => $request->user()->id, 'status' => 'pending'];
        return response()->json(['success' => true, 'data' => Bale::create($data)->loadCount('products')], 201);
    }

    public function update(Request $request, Bale $bale): JsonResponse
    {
        abort_unless($request->user()->can('fardo editar'), 403);
        $data = $request->validate([
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'bale_type' => ['present', 'nullable', 'string', 'max:100'],
            'purchase_amount' => ['required', 'numeric', 'min:0'],
            'freight_amount' => ['required', 'numeric', 'min:0'],
            'other_costs' => ['required', 'numeric', 'min:0'],
            'estimated_quantity' => ['present', 'nullable', 'integer', 'min:1', 'max:2147483647'],
            'actual_quantity' => ['present', 'nullable', 'integer', 'min:1', 'max:2147483647'],
            'damaged_quantity' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'notes' => ['present', 'nullable', 'string', 'max:5000'],
        ]);
        $updated = DB::transaction(function () use ($bale, $data) {
            $locked = Bale::whereKey($bale->id)->lockForUpdate()->firstOrFail();
            $actual = $data['actual_quantity'];
            $damaged = (int) $data['damaged_quantity'];
            if (($locked->status === 'finalized' && $actual === null)
                || ($actual === null && $damaged > 0)
                || ($actual !== null && $damaged >= (int) $actual)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'actual_quantity' => 'Indica la cantidad recibida y deja al menos una prenda aprovechable.',
                ]);
            }
            // Editar cantidades no crea ni elimina mercaderías y conserva el estado y fecha de cierre.
            $locked->update($data);
            if ($locked->status === 'finalized') {
                $unitCost = $locked->fresh()->unit_cost;
                $locked->products()->update(['bale_unit_cost' => $unitCost, 'pcosto' => $unitCost]);
            }
            return $locked->fresh()->load('supplierRelation')->loadCount('products');
        });
        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function suppliers(): JsonResponse { return response()->json(['success' => true, 'data' => Proveedor::orderBy('razonsocial')->get(['id','razonsocial','ruc'])]); }
    public function storeSupplier(Request $request): JsonResponse
    {
        $data = $request->validate(['razonsocial' => ['required','string','max:70'], 'ruc' => ['required','string','max:14']]);
        $supplier = Proveedor::firstOrCreate(['ruc' => trim($data['ruc'])], ['razonsocial' => trim($data['razonsocial']), 'estado' => 1]);
        return response()->json(['success' => true, 'data' => $supplier], $supplier->wasRecentlyCreated ? 201 : 200);
    }

    public function start(Bale $bale): JsonResponse
    {
        if ($bale->status === 'pending') $bale->update(['status' => 'in_progress']);
        return response()->json(['success' => true, 'data' => $bale->fresh()->loadCount('products')]);
    }

    public function finalize(Request $request, Bale $bale): JsonResponse
    {
        $data = $request->validate(['actual_quantity' => ['required','integer','min:1'], 'damaged_quantity' => ['required','integer','min:0','lt:actual_quantity']]);
        $bale->finalizeCosts($data['actual_quantity'], $data['damaged_quantity']);
        return response()->json(['success' => true, 'data' => $bale->fresh()->loadCount('products')]);
    }

    public function uploadPhoto(Request $request, Bale $bale): JsonResponse
    {
        $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);
        if ($bale->image) Storage::disk('public')->delete($bale->image);
        $bale->update(['image' => $request->file('photo')->store('fardos', 'public')]);
        return response()->json(['success' => true, 'data' => $bale->fresh()->load('supplierRelation')->loadCount('products')]);
    }
}
