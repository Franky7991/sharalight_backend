<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_of_measure_id',
    ];

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }
}
