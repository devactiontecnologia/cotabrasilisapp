<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'payment_method',
        'amount',
        'fees',
        'total_amount',
        'status',
        'payment_reference',
        'payment_details',
        'asaas_payment_id',
        'asaas_webhook_data',
        'authorization_document_path',
        'video_path',
        'sent_at_hour',
        'payment_due_at',
        'payment_completed_at',
        'blocked_until',
        'block_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fees' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_details' => 'array',
            'asaas_webhook_data' => 'array',
            'sent_at_hour' => 'boolean',
            'payment_due_at' => 'datetime',
            'payment_completed_at' => 'datetime',
            'blocked_until' => 'datetime',
        ];
    }

    /**
     * Get the quota transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(QuotaTransaction::class, 'transaction_id');
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->payment_due_at && $this->payment_due_at < now() && !$this->isCompleted();
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until > now();
    }

    /**
     * Get hours remaining until payment due
     */
    public function getHoursRemaining(): ?int
    {
        if (!$this->payment_due_at) {
            return null;
        }
        
        $hours = now()->diffInHours($this->payment_due_at, false);
        return $hours > 0 ? $hours : 0;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        $this->update([
            'status' => 'completed',
            'payment_completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Apply block for non-compliance
     */
    public function applyBlock(string $reason, int $hours = 24): bool
    {
        $this->update([
            'blocked_until' => now()->addHours($hours),
            'block_reason' => $reason,
        ]);

        return true;
    }
}
