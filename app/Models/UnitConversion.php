<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_unit_of_measure_id',
        'from_quantity',
        'to_unit_of_measure_id',
        'to_quantity',
    ];

    public function fromUnitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'from_unit_of_measure_id');
    }

    public function toUnitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'to_unit_of_measure_id');
    }
}
