<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    // ---- Chiavi disponibili ----
    const KEY_WAREHOUSE_LOAD_CAUSAL = 'warehouse_load_causal_id';

    const KEYS = [
        self::KEY_WAREHOUSE_LOAD_CAUSAL => 'Causale Carico in Magazzino',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    // ---- Helper statici ----

    /**
     * Legge il valore di una chiave. Restituisce $default se non esiste.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Salva (inserisce o aggiorna) il valore di una chiave.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrInsert(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
