<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderHasProductWarehouse extends Model
{
    use HasFactory;

    protected $table = 'customer_order_has_product_warehouses';

    protected $fillable = [
        'customer_order_has_product_id',
        'warehouse_id',
        'qnt',
    ];

    protected function casts(): array
    {
        return [
            'qnt' => 'decimal:2',
        ];
    }

    public function customerOrderHasProduct()
    {
        return $this->belongsTo(CustomerOrderHasProduct::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}