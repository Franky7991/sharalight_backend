<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // ---- Costanti tipo prodotto ----
    const TYPE_RAW_MATERIAL    = 'raw_material';
    const TYPE_SEMI_FINISHED   = 'semi_finished';
    const TYPE_FINISHED        = 'finished';

    const TYPES = [
        self::TYPE_RAW_MATERIAL  => 'Materia Prima',
        self::TYPE_SEMI_FINISHED => 'Semi Lavorato',
        self::TYPE_FINISHED      => 'Prodotto Finito',
    ];

    // Tipi che abilitano la tab Ricetta
    const TYPES_WITH_RECIPE = [
        self::TYPE_SEMI_FINISHED,
        self::TYPE_FINISHED,
    ];

    protected $fillable = [
        'name',
        'product_category_id',
        'type',
    ];

    // ---- Helper ----

    public function hasRecipe(): bool
    {
        return in_array($this->type, self::TYPES_WITH_RECIPE);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    // ---- Relazioni ----

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}
