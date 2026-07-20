<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Causal extends Model
{
    use HasFactory;

    const TYPE_LOAD   = 'load';
    const TYPE_UNLOAD = 'unload';

    const TYPES = [
        self::TYPE_LOAD   => 'Carico',
        self::TYPE_UNLOAD => 'Scarico',
    ];

    protected $fillable = [
        'name',
        'type',
    ];

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
