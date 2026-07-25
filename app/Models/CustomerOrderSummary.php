<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_order_id',
        'product_id',
        'unit_of_measure_id',
        'total_qnt',
    ];

    protected function casts(): array
    {
        return [
            'total_qnt' => 'decimal:4',
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
}
