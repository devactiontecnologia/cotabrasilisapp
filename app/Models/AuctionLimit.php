<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionLimit extends Model
{
    protected $fillable = [
        'user_id',
        'quota_id',
        'auctions_used',
        'auctions_limit',
        'limit_period',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * Get the user that owns the auction limit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quota that owns the auction limit.
     */
    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }

    /**
     * Check if the user has reached the auction limit.
     */
    public function hasReachedLimit(): bool
    {
        return $this->auctions_used >= $this->auctions_limit;
    }

    /**
     * Get remaining auctions for this limit.
     */
    public function getRemainingAuctions(): int
    {
        return max(0, $this->auctions_limit - $this->auctions_used);
    }

    /**
     * Increment the auctions used count.
     */
    public function incrementUsage(): bool
    {
        if ($this->hasReachedLimit()) {
            return false;
        }

        $this->increment('auctions_used');
        return true;
    }

    /**
     * Reset the auctions used count for a new period.
     */
    public function resetForNewPeriod(): void
    {
        $this->update(['auctions_used' => 0]);
    }

    /**
     * Get limit period label.
     */
    public function getLimitPeriodLabel(): string
    {
        return match($this->limit_period) {
            'year' => 'Ano',
            'month' => 'Mês',
            'usage' => 'Uso',
            default => 'Desconhecido'
        };
    }

    /**
     * Scope for current period limits.
     */
    public function scopeCurrentPeriod($query)
    {
        return $query->where('period_start', '<=', now())
                    ->where('period_end', '>=', now());
    }

    /**
     * Scope for specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific quota.
     */
    public function scopeForQuota($query, $quotaId)
    {
        return $query->where('quota_id', $quotaId);
    }

    /**
     * Scope for specific period type.
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('limit_period', $period);
    }
}
