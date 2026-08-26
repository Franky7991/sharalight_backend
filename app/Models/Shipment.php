<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    const STATE_CREATED = 'created';
    const STATE_SHIPPED = 'shipped';

    const STATES = [
        self::STATE_CREATED => 'Creato',
        self::STATE_SHIPPED => 'Spedito',
    ];

    protected $fillable = [
        'progressive',
        'date',
        'state',
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
            'date' => 'date',
        ];
    }

    public function stateLabel(): string
    {
        return self::STATES[$this->state] ?? $this->state;
    }

    public function isCreated(): bool
    {
        return $this->state === self::STATE_CREATED;
    }

    public function isShipped(): bool
    {
        return $this->state === self::STATE_SHIPPED;
    }

    public function details()
    {
        return $this->hasMany(ShipmentDetail::class);
    }

    public function customerOrders()
    {
        return $this->belongsToMany(CustomerOrder::class, 'shipment_details');
    }
}