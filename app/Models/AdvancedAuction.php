<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AdvancedAuction extends Model
{
    protected $fillable = [
        'rental_offer_id',
        'user_id',
        'start_time',
        'end_time',
        'minimum_price',
        'duration_minutes',
        'bid_extension_minutes',
        'status',
        'current_bid',
        'current_winner_id',
        'total_bids',
        'auction_rules',
        'auto_extend',
        'max_extensions',
        'extensions_used',
        'last_bid_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'last_bid_at' => 'datetime',
        'minimum_price' => 'decimal:2',
        'current_bid' => 'decimal:2',
        'auction_rules' => 'array',
        'auto_extend' => 'boolean',
    ];

    /**
     * Get the rental offer that owns the auction.
     */
    public function rentalOffer(): BelongsTo
    {
        return $this->belongsTo(RentalOffer::class);
    }

    /**
     * Get the user who created the auction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current winner of the auction.
     */
    public function currentWinner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_winner_id');
    }

    /**
     * Check if the auction is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_time <= now() && 
               $this->end_time > now();
    }

    /**
     * Check if the auction has ended.
     */
    public function hasEnded(): bool
    {
        return $this->status === 'ended' || $this->end_time <= now();
    }

    /**
     * Check if the auction is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->start_time > now();
    }

    /**
     * Get time remaining in the auction.
     */
    public function getTimeRemaining(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }

        return max(0, $this->end_time->diffInMinutes(now()));
    }

    /**
     * Check if the auction can be extended.
     */
    public function canBeExtended(): bool
    {
        return $this->auto_extend && 
               $this->extensions_used < $this->max_extensions &&
               $this->getTimeRemaining() <= $this->bid_extension_minutes;
    }

    /**
     * Extend the auction by the specified minutes.
     */
    public function extend(int $minutes = null): bool
    {
        if (!$this->canBeExtended()) {
            return false;
        }

        $extensionMinutes = $minutes ?? $this->bid_extension_minutes;
        $this->end_time = $this->end_time->addMinutes((int) $extensionMinutes);
        $this->extensions_used++;
        $this->save();

        return true;
    }

    /**
     * Place a bid on the auction.
     */
    public function placeBid(User $user, float $amount, string $message = null): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($amount <= $this->current_bid) {
            return false;
        }

        $this->current_bid = $amount;
        $this->current_winner_id = $user->id;
        $this->total_bids++;
        $this->last_bid_at = now();
        $this->save();

        // Auto-extend if needed
        if ($this->canBeExtended()) {
            $this->extend();
        }

        return true;
    }

    /**
     * End the auction and determine the winner.
     */
    public function endAuction(): bool
    {
        if ($this->hasEnded()) {
            return false;
        }

        $this->status = 'ended';
        $this->end_time = now();
        $this->save();

        // Update the rental offer with the winning bid
        if ($this->current_winner_id && $this->current_bid) {
            $this->rentalOffer->update([
                'price' => $this->current_bid,
                'status' => 'negotiated'
            ]);
        }

        return true;
    }

    /**
     * Get auction status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'scheduled' => 'Agendado',
            'active' => 'Ativo',
            'ended' => 'Finalizado',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido'
        };
    }

    /**
     * Scope for active auctions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>', now());
    }

    /**
     * Scope for scheduled auctions.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('start_time', '>', now());
    }

    /**
     * Scope for ended auctions.
     */
    public function scopeEnded($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'ended')
              ->orWhere('end_time', '<=', now());
        });
    }
}
