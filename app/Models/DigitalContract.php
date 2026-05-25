<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalContract extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'transaction_id',
        'contract_type',
        'contract_content',
        'contract_file_path',
        'owner_signature',
        'renter_signature',
        'owner_signed_at',
        'renter_signed_at',
        'is_completed',
    ];

    protected $casts = [
        'owner_signed_at' => 'datetime',
        'renter_signed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Alias para compatibilidade: is_signed reflete is_completed (contrato assinado/concluído).
     */
    public function getIsSignedAttribute(): bool
    {
        return (bool) $this->is_completed;
    }

    // Contract types
    const TYPE_RENTAL_AGREEMENT = 'rental_agreement';
    const TYPE_EXCHANGE_AGREEMENT = 'exchange_agreement';

    /**
     * Get the transaction that owns the contract.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(QuotaTransaction::class, 'transaction_id');
    }

    /**
     * Check if the contract is signed by both parties.
     */
    public function isSigned(): bool
    {
        return $this->is_completed;
    }

    /**
     * Check if the owner has signed.
     */
    public function isOwnerSigned(): bool
    {
        return !is_null($this->owner_signature);
    }

    /**
     * Check if the renter has signed.
     */
    public function isRenterSigned(): bool
    {
        return !is_null($this->renter_signature);
    }

    /**
     * Check if both parties have signed.
     */
    public function isBothPartiesSigned(): bool
    {
        return $this->isOwnerSigned() && $this->isRenterSigned();
    }

    /**
     * Get the contract type in a readable format.
     */
    public function getContractTypeName(): string
    {
        return match($this->contract_type) {
            self::TYPE_RENTAL_AGREEMENT => 'Contrato de Aluguel',
            self::TYPE_EXCHANGE_AGREEMENT => 'Contrato de Troca',
            default => 'Contrato',
        };
    }
}
