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
