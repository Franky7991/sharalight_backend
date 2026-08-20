<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory;

    const STATE_CREATED = 'created';
    const STATE_IN_PROCESSING = 'in_processing';
    const STATE_COMPLETED = 'completed';

    const STATES = [
        self::STATE_CREATED       => 'Creato',
        self::STATE_IN_PROCESSING => 'In Lavorazione',
        self::STATE_COMPLETED     => 'Completato',
    ];

    protected $fillable = [
        'progressive',
        'production_date',
        'warehouse_id',
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
            'production_date' => 'date',
        ];
    }

    public function stateLabel(): string
    {
        return self::STATES[$this->state] ?? $this->state;
    }

    /**
     * L'ordine di produzione è nello stato "Creato".
     */
    public function isCreated(): bool
    {
        return $this->state === self::STATE_CREATED;
    }

    /**
     * L'ordine di produzione è nello stato "In Lavorazione".
     */
    public function isInProcessing(): bool
    {
        return $this->state === self::STATE_IN_PROCESSING;
    }

    /**
     * L'ordine di produzione è nello stato "Completato".
     */
    public function isCompleted(): bool
    {
        return $this->state === self::STATE_COMPLETED;
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details()
    {
        return $this->hasMany(ProductionOrderDetail::class);
    }

    public function records()
    {
        return $this->hasMany(ProductionRecord::class);
    }
}