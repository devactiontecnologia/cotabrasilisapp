<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class HospitalityAuthorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_offer_id',
        'quota_id',
        'guest_user_id',
        'authorization_code',
        'guest_name',
        'guest_document',
        'guest_phone',
        'guest_email',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'special_requests',
        'status',
        'approved_at',
        'used_at',
        'expires_at',
        'rejection_reason',
        'hotel_notes',
        'is_transferable',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'approved_at' => 'datetime',
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_transferable' => 'boolean',
        ];
    }

    /**
     * Get the rental offer that owns the authorization.
     */
    public function rentalOffer()
    {
        return $this->belongsTo(RentalOffer::class);
    }

    /**
     * Get the quota that owns the authorization.
     */
    public function quota()
    {
        return $this->belongsTo(Quota::class);
    }

    /**
     * Get the guest user.
     */
    public function guestUser()
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    /**
     * Check if authorization is valid.
     */
    public function isValid(): bool
    {
        return $this->status === 'approved' && 
               $this->expires_at > now() && 
               $this->used_at === null;
    }

    /**
     * Check if authorization is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now() || $this->status === 'expired';
    }

    /**
     * Check if authorization can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->isValid() && 
               $this->check_in_date <= now()->toDateString() && 
               $this->check_out_date >= now()->toDateString();
    }

    /**
     * Mark authorization as used.
     */
    public function markAsUsed(): bool
    {
        if (!$this->canBeUsed()) {
            return false;
        }

        $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);

        return true;
    }

    /**
     * Approve authorization.
     */
    public function approve(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject authorization.
     */
    public function reject(string $reason): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Generate unique authorization code.
     */
    public static function generateAuthorizationCode(): string
    {
        do {
            $code = 'HA' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('authorization_code', $code)->exists());

        return $code;
    }

    /**
     * Scope for valid authorizations.
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'approved')
                    ->where('expires_at', '>', now())
                    ->whereNull('used_at');
    }

    /**
     * Scope for expired authorizations.
     */
    public function scopeExpired($query)
    {
        return $query->where(function($q) {
            $q->where('expires_at', '<=', now())
              ->orWhere('status', 'expired');
        });
    }

    /**
     * Scope for pending authorizations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovada',
            'rejected' => 'Rejeitada',
            'used' => 'Utilizada',
            'expired' => 'Expirada',
            default => 'Desconhecido'
        };
    }
}
