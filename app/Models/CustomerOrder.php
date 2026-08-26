<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    const STATE_CREATED = 'created';
    const STATE_PRODUCTS_DEFINED = 'products_defined';
    const STATE_PRODUCTS_ALLOCATED = 'products_allocated';

    const STATES = [
        self::STATE_CREATED           => 'Creato',
        self::STATE_PRODUCTS_DEFINED  => 'Prodotti Definiti',
        self::STATE_PRODUCTS_ALLOCATED => 'Prodotti Allocati',
    ];

    protected $fillable = [
        'progressive',
        'address',
        'user_id',
        'order_date',
        'state',
        'qnt',
        'qnt_produced',
    ];

    /**
     * Genera il progressivo nel formato YYYY-NNNNN (es. 2026-00001).
     * Il contatore riparte da 1 ogni anno.
     */
    public static function generateProgressive(): string
    {
        $year = now()->year;

        $last = static::query()
            ->whereRaw('LEFT(progressive, 4) = ?', [(string) $year])
            ->orderByDesc('progressive')
            ->value('progressive');

        $next = $last ? ((int) substr($last, 5)) + 1 : 1;

        return $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'qnt' => 'decimal:2',
            'qnt_produced' => 'decimal:2',
        ];
    }

    public function stateLabel(): string
    {
        return self::STATES[$this->state] ?? $this->state;
    }

    /**
     * L'ordine è nello stato "Prodotti Definiti".
     */
    public function isProductsDefined(): bool
    {
        return $this->state === self::STATE_PRODUCTS_DEFINED;
    }

    /**
     * L'ordine è nello stato "Prodotti Allocati".
     */
    public function isProductsAllocated(): bool
    {
        return $this->state === self::STATE_PRODUCTS_ALLOCATED;
    }

    /**
     * L'ordine può essere modificato (aggiunta/rimozione prodotti,
     * configurazione ingredienti, cancellazione).
     * Solo nello stato "created".
     */
    public function canBeModified(): bool
    {
        return $this->state === self::STATE_CREATED;
    }

    /**
     * Verifica che tutti i prodotti dell'ordine siano allocati ai magazzini.
     */
    public function areAllProductsAllocated(): bool
    {
        return $this->products()->where('warehouses_allocated', false)->doesntExist();
    }

    /**
     * Percentuale di produzione dell'ordine (0-100), basata sulla somma
     * di qnt_produced dei prodotti (richiede il caricamento di
     * products_sum_qnt_produced via withSum('products', 'qnt_produced')).
     */
    public function productionProgress(): float
    {
        $qnt = (float) $this->qnt;

        if ($qnt <= 0) {
            return 0.0;
        }

        $produced = (float) ($this->products_sum_qnt_produced ?? 0);

        return min(100, round($produced / $qnt * 100, 1));
    }

    /**
     * L'ordine è interamente prodotto (progressbar al 100%).
     */
    public function isFullyProduced(): bool
    {
        $qnt = (float) $this->qnt;

        if ($qnt <= 0) {
            return false;
        }

        $produced = (float) ($this->products_sum_qnt_produced ?? 0);

        return $produced >= $qnt;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(CustomerOrderHasProduct::class);
    }

    public function shipmentDetails()
    {
        return $this->hasMany(ShipmentDetail::class);
    }
}
