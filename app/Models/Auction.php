<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_offer_id',
        'user_id',
        'bid_amount',
        'is_winning_bid',
        'bid_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'bid_amount' => 'decimal:2',
            'is_winning_bid' => 'boolean',
            'bid_at' => 'datetime',
        ];
    }

    /**
     * Get the rental offer that owns the auction.
     */
    public function rentalOffer()
    {
        return $this->belongsTo(RentalOffer::class);
    }

    /**
     * Get the user that made the bid.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is the highest bid.
     */
    public function isHighestBid()
    {
        return $this->rentalOffer->getHighestBidAmount() == $this->bid_amount;
    }

    /**
     * Check if bid is valid (higher than minimum and previous bids).
     */
    public function isValidBid()
    {
        $offer = $this->rentalOffer;
        
        // Check if auction is still active
        if (!$offer->isAuctionActive()) {
            return false;
        }
        
        // Check if bid is higher than minimum price
        if ($this->bid_amount < $offer->minimum_price) {
            return false;
        }
        
        // Check if bid is higher than current highest bid
        $highestBid = $offer->getHighestBidAmount();
        if ($highestBid > 0 && $this->bid_amount <= $highestBid) {
            return false;
        }
        
        return true;
    }

    /**
     * Scope for winning bids.
     */
    public function scopeWinning($query)
    {
        return $query->where('is_winning_bid', true);
    }

    /**
     * Scope for bids by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for bids by offer.
     */
    public function scopeByOffer($query, $offerId)
    {
        return $query->where('rental_offer_id', $offerId);
    }

    /**
     * Scope for recent bids.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('bid_at', '>=', now()->subHours($hours));
    }
}
