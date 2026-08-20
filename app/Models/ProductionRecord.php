<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'production_order_detail_id',
        'customer_order_has_product_id',
        'product_id',
        'qnt',
        'unit_of_measure_id',
    ];

    protected function casts(): array
    {
        return [
            'qnt' => 'decimal:2',
        ];
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function productionOrderDetail()
    {
        return $this->belongsTo(ProductionOrderDetail::class);
    }

    public function customerOrderHasProduct()
    {
        return $this->belongsTo(CustomerOrderHasProduct::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }
}