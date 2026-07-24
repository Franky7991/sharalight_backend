<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    const STATE_CREATED = 'created';

    const STATES = [
        self::STATE_CREATED => 'Creato',
    ];

    protected $fillable = [
        'progressive',
        'address',
        'user_id',
        'order_date',
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
            'order_date' => 'date',
        ];
    }

    public function stateLabel(): string
    {
        return self::STATES[$this->state] ?? $this->state;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
