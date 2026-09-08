<?php

namespace App\Http\Controllers;

use App\Models\Bale;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class BaleController extends Controller
{
    public function index() { abort_unless(auth()->user()->can('fardo leer'), 403); $bales = Bale::with('supplierRelation')->withCount('products')->latest()->get(); return view('bales.index', compact('bales')); }
    public function create() { abort_unless(auth()->user()->can('fardo crear'), 403); return view('bales.form', ['bale' => new Bale(), 'suppliers' => Proveedor::orderBy('razonsocial')->get()]); }
    public function edit(Bale $bale) { abort_unless(auth()->user()->can('fardo editar'), 403); $bale->loadCount('products'); return view('bales.form', ['bale' => $bale, 'suppliers' => Proveedor::orderBy('razonsocial')->get()]); }
    public function show(Request $request, Bale $bale)
    {
        abort_unless(auth()->user()->can('fardo leer'), 403);
        $bale->load('supplierRelation')->loadCount('products');
        $productsQuery = \App\Models\Producto::with(['garmentType', 'clothingSize'])
            ->where(function ($query) use ($bale) {
                $query->where('bale_id', $bale->id)->orWhereHas('additionalBales', fn ($q) => $q->where('bales.id', $bale->id));
            });

        $filters = $request->validate([
            'age_group' => ['nullable', 'array'],
            'age_group.*' => ['string', 'max:40'],
            'gender' => ['nullable', 'array'],
            'gender.*' => ['string', 'max:30'],
            'garment_type_id' => ['nullable', 'array'],
            'garment_type_id.*' => ['integer', 'exists:garment_types,id'],
        ]);

        $catalogQuery = \App\Models\Producto::query()->where(function ($query) use ($bale) {
            $query->where('bale_id', $bale->id)->orWhereHas('additionalBales', fn ($q) => $q->where('bales.id', $bale->id));
        });
        $ageGroups = (clone $catalogQuery)->whereNotNull('age_group')->where('age_group', '<>', '')->distinct()->orderBy('age_group')->pluck('age_group');
        $genders = (clone $catalogQuery)->whereNotNull('gender')->where('gender', '<>', '')->distinct()->orderBy('gender')->pluck('gender');
        $garmentTypes = \App\Models\GarmentType::whereHas('products', function ($query) use ($bale) {
            $query->where('bale_id', $bale->id)->orWhereHas('additionalBales', fn ($q) => $q->where('bales.id', $bale->id));
        })->orderBy('name')->get();

        $products = $productsQuery
            ->when(!empty($filters['age_group']), fn ($query) => $query->whereIn('age_group', $filters['age_group']))
            ->when(!empty($filters['gender']), fn ($query) => $query->whereIn('gender', $filters['gender']))
            ->when(!empty($filters['garment_type_id']), fn ($query) => $query->whereIn('garment_type_id', $filters['garment_type_id']))
            ->orderByDesc('productos.id')
            ->paginate(30)->withQueryString();
        return view('bales.show', compact('bale', 'products', 'ageGroups', 'genders', 'garmentTypes'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('fardo crear'), 403);
        $data = $this->validated($request);
        $data['code'] = $data['code'] ?: 'FAR-' . now()->format('Ymd-His');
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';
        Bale::create($data);
        return redirect()->route('bales.index')->with('success', 'Fardo registrado correctamente.');
    }

    public function update(Request $request, Bale $bale)
    {
        abort_unless(auth()->user()->can('fardo editar'), 403);
        $bale->update($this->validated($request, $bale->id));
        return redirect()->route('bales.index')->with('success', 'Fardo actualizado correctamente.');
    }

    public function finalize(Request $request, Bale $bale)
    {
        abort_unless(auth()->user()->can('fardo finalizar'), 403);
        $data = $request->validate(['actual_quantity' => ['required','integer','min:1'], 'damaged_quantity' => ['required','integer','min:0','lt:actual_quantity']]);
        $bale->finalizeCosts($data['actual_quantity'], $data['damaged_quantity']);
        return redirect()->route('bales.index')->with('success', 'Fardo finalizado y costos recalculados.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['nullable','string','max:30','unique:bales,code,' . $id], 'purchase_date' => ['required','date'],
            'supplier' => ['nullable','string','max:120'], 'bale_type' => ['nullable','string','max:100'],
            'supplier_id' => ['nullable','integer','exists:proveedores,id'],
            'purchase_amount' => ['required','numeric','min:0'], 'freight_amount' => ['nullable','numeric','min:0'],
            'other_costs' => ['nullable','numeric','min:0'], 'estimated_quantity' => ['nullable','integer','min:1'],
            'notes' => ['nullable','string'],
        ]);
    }
}
