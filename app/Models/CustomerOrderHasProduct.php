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
        'unit_of_measure_id',
    ];

    protected function casts(): array
    {
        return [
            'qnt' => 'decimal:2',
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
}
