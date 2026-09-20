<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderHasProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_order_has_product_id',
        'recipe_id',
        'product_id',
        'original_qnt',
        'original_unit_of_measure_id',
        'conversion_qnt',
        'conversion_unit_of_measure_id',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Prezzo dell'ingrediente da MOSTRARE: solo le materie prime (prodotti
     * senza ricetta) hanno un prezzo ingrediente.
     *
     * I semi-lavorati e i prodotti finiti possono comparire come ingredienti
     * (es. la Candela Piccola dentro la Candela Piccola con Busta), ma il loro
     * prezzo di listino non è un costo ingrediente: non entra nel prezzo della
     * riga (vedi CustomerOrderHasProduct::recalculatePrice) e quindi non va
     * nemmeno mostrato.
     *
     * @return float|null  null = nessun prezzo da mostrare
     */
    public function visibleIngredientPrice(): ?float
    {
        if ($this->price === null) {
            return null;
        }

        return $this->product?->type === Product::TYPE_RAW_MATERIAL
            ? (float) $this->price
            : null;
    }

    public function customerOrderHasProduct()
    {
        return $this->belongsTo(CustomerOrderHasProduct::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
