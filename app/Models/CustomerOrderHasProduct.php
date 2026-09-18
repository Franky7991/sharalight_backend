<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderHasProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_order_id',
        'product_id',
        'qnt',
        'qnt_produced',
        'price',
        'warehouses_allocated',
        'unit_of_measure_id',
    ];

    protected function casts(): array
    {
        return [
            'qnt' => 'decimal:2',
            'qnt_produced' => 'decimal:2',
            'price' => 'decimal:2',
            'warehouses_allocated' => 'boolean',
        ];
    }

    public function customerOrder()
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function details()
    {
        return $this->hasMany(CustomerOrderHasProductDetail::class);
    }

    public function warehouses()
    {
        return $this->hasMany(CustomerOrderHasProductWarehouse::class, 'customer_order_has_product_id');
    }

    /**
     * Ricalcola il prezzo unitario della riga:
     * (prezzo candela + prezzo ingredienti scelti).
     *
     * Gli ingredienti conteggiati sono solo le materie prime (prodotti senza
     * ricetta): i semi-lavorati e i loro componenti non vanno sommati due volte.
     */
    public function recalculatePrice(): void
    {
        $basePrice = $this->product?->price;

        $ingredientPrice = (float) $this->details()
            ->whereHas('product', fn ($query) => $query->where('type', Product::TYPE_RAW_MATERIAL))
            ->sum('price');

        if ($basePrice === null && $ingredientPrice <= 0) {
            $this->update(['price' => null]);

            return;
        }

        $this->update(['price' => (float) $basePrice + $ingredientPrice]);
    }

    public function productionOrderDetails()
    {
        return $this->hasMany(ProductionOrderDetail::class);
    }
}
