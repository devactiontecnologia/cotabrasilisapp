<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuotaTransaction extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'quota_id',
        'exchange_quota_id',
        'renter_id',
        'owner_id',
        'transaction_type',
        'is_fair_exchange',
        'total_amount',
        'owner_amount',
        'platform_fee',
        'status',
        'payment_method',
        'payment_id',
        'payment_status',
        'transaction_date',
        'negotiation_started_at',
        'negotiation_deadline',
        'document_upload_deadline',
        'document_uploaded_at',
        'document_path',
        'owner_pix',
        'workflow_step',
        'renter_signed_document_path',
        'owner_signed_document_path',
        'payment_receipt_path',
        'payment_deadline_hours',
        'document_deadline_hours',
        'guest_names',
    ];

    // Workflow steps (aluguel/compra: documento -> assinatura -> taxa -> comprovante -> concluído)
    const WORKFLOW_AWAITING_OWNER_DOC = 'awaiting_owner_doc';
    const WORKFLOW_DOC_AVAILABLE = 'doc_available';
    const WORKFLOW_RENTER_SIGNED = 'renter_signed_uploaded';
    const WORKFLOW_AWAITING_TAX_PAYMENT = 'awaiting_tax_payment';
    const WORKFLOW_TAX_PAID = 'tax_paid';
    const WORKFLOW_COMPLETED = 'completed';

    protected $casts = [
        'total_amount' => 'decimal:2',
        'owner_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'is_fair_exchange' => 'boolean',
        'transaction_date' => 'datetime',
        'negotiation_started_at' => 'datetime',
        'negotiation_deadline' => 'datetime',
        'document_upload_deadline' => 'datetime',
        'document_uploaded_at' => 'datetime',
        'guest_names' => 'array',
    ];

    // Transaction types
    const TYPE_RENTAL = 'rental';
    const TYPE_EXCHANGE = 'exchange';

    // Transaction statuses
    const STATUS_PENDING = 'pending';
    const STATUS_NEGOTIATING = 'negotiating';
    const STATUS_PAYMENT_PENDING = 'payment_pending';
    const STATUS_DOCUMENT_PENDING = 'document_pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    // Payment statuses
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_COMPLETED = 'completed';
    const PAYMENT_FAILED = 'failed';

    /**
     * Transações em que o usuário é proprietário e o processo ainda não terminou.
     */
    public function scopeForOwnerInProgress($query, int $userId)
    {
        return $query->where('owner_id', $userId)
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_EXPIRED,
            ]);
    }

    /**
     * Transações em que o usuário é o interessado e o processo ainda não terminou.
     */
    public function scopeForRenterInProgress($query, int $userId)
    {
        return $query->where('renter_id', $userId)
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_EXPIRED,
            ]);
    }

    /**
     * Get the quota that belongs to the transaction.
     */
    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }

    /**
     * Get the user who owns the quota (owner).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user who is renting/exchanging (renter).
     */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    /**
     * Get the digital contract for this transaction.
     */
    public function digitalContract(): HasOne
    {
        return $this->hasOne(DigitalContract::class, 'transaction_id');
    }

    /**
     * Check if the transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the transaction is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if payment is completed.
     */
    public function isPaymentCompleted(): bool
    {
        return $this->payment_status === self::PAYMENT_COMPLETED;
    }

    /**
     * Check if payment is pending.
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    /**
     * Check if this is a rental transaction.
     */
    public function isRental(): bool
    {
        return $this->transaction_type === self::TYPE_RENTAL;
    }

    /**
     * Check if this is an exchange transaction.
     */
    public function isExchange(): bool
    {
        return $this->transaction_type === self::TYPE_EXCHANGE;
    }

    /**
     * Check if negotiation deadline has passed.
     */
    public function isNegotiationExpired(): bool
    {
        return $this->negotiation_deadline && 
               $this->negotiation_deadline < now() && 
               $this->payment_status !== self::PAYMENT_COMPLETED;
    }

    /**
     * Check if document upload deadline has passed.
     */
    public function isDocumentDeadlineExpired(): bool
    {
        return $this->document_upload_deadline && 
               $this->document_upload_deadline < now() && 
               !$this->document_uploaded_at;
    }

    /**
     * Check if payment deadline has passed.
     */
    public function isPaymentDeadlineExpired(): bool
    {
        if (!$this->negotiation_deadline) {
            return false;
        }
        
        $paymentDeadline = $this->negotiation_started_at 
            ? $this->negotiation_started_at->addHours((int) ($this->payment_deadline_hours ?? 24))
            : $this->negotiation_deadline;
            
        return $paymentDeadline < now() && 
               $this->payment_status !== self::PAYMENT_COMPLETED;
    }
}
