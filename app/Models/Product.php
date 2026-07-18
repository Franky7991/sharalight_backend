<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_category_id',
        'finished_product',
    ];

    protected function casts(): array
    {
        return [
            'finished_product' => 'boolean',
        ];
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
