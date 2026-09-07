<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bale extends Model
{
    protected $fillable = ['code', 'purchase_date', 'supplier_id', 'supplier', 'bale_type', 'purchase_amount', 'freight_amount', 'other_costs', 'estimated_quantity', 'actual_quantity', 'damaged_quantity', 'status', 'notes', 'image', 'finalized_at', 'created_by'];
    protected $casts = ['purchase_date' => 'date:Y-m-d', 'finalized_at' => 'datetime', 'purchase_amount' => 'float', 'freight_amount' => 'float', 'other_costs' => 'float'];
    protected $appends = ['total_cost', 'sellable_quantity', 'unit_cost', 'supplier_name', 'image_url'];

    public function products(): HasMany { return $this->hasMany(Producto::class); }
    public function addedProducts() { return $this->belongsToMany(Producto::class, 'bale_product_items')->withPivot('quantity')->withTimestamps(); }
    public function supplierRelation() { return $this->belongsTo(Proveedor::class, 'supplier_id'); }
    public function getSupplierNameAttribute(): ?string { return $this->supplierRelation?->razonsocial ?: $this->supplier; }
    public function getImageUrlAttribute(): ?string { return $this->image ? asset('storage/' . $this->image) : null; }
    public function getTotalCostAttribute(): float { return (float) $this->purchase_amount + (float) $this->freight_amount + (float) $this->other_costs; }
    public function getSellableQuantityAttribute(): int
    {
        $actual = $this->actual_quantity ?: ($this->products_count ?? $this->products()->count());
        return max(0, (int) $actual - (int) $this->damaged_quantity);
    }
    public function getUnitCostAttribute(): float { return $this->sellable_quantity > 0 ? round($this->total_cost / $this->sellable_quantity, 2) : 0; }

    public function finalizeCosts(int $actualQuantity, int $damagedQuantity = 0): void
    {
        $this->update(['actual_quantity' => $actualQuantity, 'damaged_quantity' => $damagedQuantity, 'status' => 'finalized', 'finalized_at' => now()]);
        $unitCost = $this->fresh()->unit_cost;
        $this->products()->update(['bale_unit_cost' => $unitCost, 'pcosto' => $unitCost]);
    }
}
