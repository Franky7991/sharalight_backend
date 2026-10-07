<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'customer_order_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'paypal_order_id',
        'paypal_payment_id',
        'paypal_capture_id',
        'payment_method',
        'raw_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function customerOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(string $captureId, array $rawResponse): void
    {
        $this->update([
            'status' => 'completed',
            'paypal_capture_id' => $captureId,
            'raw_response' => $rawResponse,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(array $rawResponse): void
    {
        $this->update([
            'status' => 'failed',
            'raw_response' => $rawResponse,
        ]);
    }
}
