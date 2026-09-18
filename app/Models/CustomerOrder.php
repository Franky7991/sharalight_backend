<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerOrder extends Model
{
    use HasFactory;

    const STATE_CREATED = 'created';
    const STATE_PRODUCTS_DEFINED = 'products_defined';
    const STATE_PRODUCTS_ALLOCATED = 'products_allocated';
    const STATE_IN_SHIPMENT = 'in_shipment';
    const STATE_SHIPPED = 'shipped';

    const STATES = [
        self::STATE_CREATED           => 'Creato',
        self::STATE_PRODUCTS_DEFINED  => 'Prodotti Definiti',
        self::STATE_PRODUCTS_ALLOCATED => 'Prodotti Allocati',
        self::STATE_IN_SHIPMENT       => 'In Spedizione',
        self::STATE_SHIPPED           => 'Spedito',
    ];

    protected $fillable = [
        'progressive',
        'address',
        'lat',
        'lng',
        'user_id',
        'order_date',
        'state',
        'state_before_shipment',
        'qnt',
        'qnt_produced',
        'price',
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

    /**
     * Ricalcola il prezzo totale dell'ordine:
     * somma di (prezzo unitario riga × quantità), dove il prezzo unitario è
     * (prezzo candela + prezzo ingredienti scelti).
     */
    public function recalculatePrice(): void
    {
        $total = $this->products()->sum(DB::raw('qnt * price'));

        $this->update(['price' => (float) $total]);
    }

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'qnt' => 'decimal:2',
            'qnt_produced' => 'decimal:2',
            'price' => 'decimal:2',
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
     * L'ordine è nello stato "In Spedizione".
     */
    public function isInShipment(): bool
    {
        return $this->state === self::STATE_IN_SHIPMENT;
    }

    /**
     * L'ordine è nello stato "Spedito".
     */
    public function isShipped(): bool
    {
        return $this->state === self::STATE_SHIPPED;
    }

    /**
     * Porta l'ordine nello stato "In Spedizione" (aggiunta a una spedizione).
     * Lo stato corrente viene memorizzato in state_before_shipment per poterlo
     * ripristinare in caso di rimozione dalla spedizione.
     * Non comporta movimenti di magazzino.
     */
    public function markAsInShipment(): void
    {
        // Già in spedizione (es. ordine presente in un'altra spedizione)
        // oppure già spedito: lo stato precedente non va sovrascritto.
        if ($this->isInShipment() || $this->isShipped()) {
            return;
        }

        $this->update([
            'state_before_shipment' => $this->state,
            'state'                 => self::STATE_IN_SHIPMENT,
        ]);
    }

    /**
     * Ripristina lo stato precedente all'inserimento in spedizione
     * (rimozione dell'ordine dalla spedizione o eliminazione della spedizione).
     * Se l'ordine è ancora presente in un'altra spedizione resta
     * "In Spedizione". Non comporta movimenti di magazzino.
     */
    public function restoreStateAfterShipmentRemoval(): void
    {
        if (! $this->isInShipment()) {
            return;
        }

        if ($this->shipmentDetails()->exists()) {
            return;
        }

        $this->update([
            'state'                 => $this->state_before_shipment ?: self::STATE_PRODUCTS_ALLOCATED,
            'state_before_shipment' => null,
        ]);
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
