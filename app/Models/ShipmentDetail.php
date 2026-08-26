<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'customer_order_id',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customerOrder()
    {
        return $this->belongsTo(CustomerOrder::class);
    }
}