<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quota_id',
        'hotel_id',
        'weeks',
        'number_of_rooms',
        'city',
        'company',
        'minimum_price',
        'acceptable_price',
        'desired_price',
        'observations_by_price',
        'status',
        'negotiation_status',
        'admin_id',
        'auction_id',
        'app_commission',
    ];

    protected function casts(): array
    {
        return [
            'minimum_price' => 'decimal:2',
            'acceptable_price' => 'decimal:2',
            'desired_price' => 'decimal:2',
            'observations_by_price' => 'array',
            'app_commission' => 'decimal:2',
        ];
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quota
     */
    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }

    /**
     * Get the hotel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the admin handling negotiation
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the auction
     */
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * Check if user can see prices based on profile type
     */
    public function canUserSeePrices(User $user): bool
    {
        if (!$user->profile) {
            return false;
        }

        $profileType = $user->profile->profile_type;
        
        // Tipo 1 (curioso): não vê preços nem nomes
        if ($profileType === 'curioso') {
            return false;
        }

        // Tipo 2 (inteligente): vê preços
        // Tipo 3 (sábio): negociação direta
        return true;
    }

    /**
     * Check if user can negotiate directly
     */
    public function canUserNegotiateDirectly(User $user): bool
    {
        if (!$user->profile) {
            return false;
        }

        return $user->profile->profile_type === 'sabio';
    }

    /**
     * Calculate app commission (10% if via auction)
     */
    public function calculateCommission(): float
    {
        if ($this->negotiation_status === 'auction') {
            return (float) ($this->desired_price ?? 0) * 0.10;
        }
        return 0;
    }
}
