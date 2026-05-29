<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistOwnerAlert extends Model
{
    protected $fillable = [
        'owner_user_id',
        'quota_id',
        'transaction_type',
        'interested_user_id',
        'wishlist_search_id',
        'interested_count',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }

    public function interestedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interested_user_id');
    }

    public function wishlistSearch(): BelongsTo
    {
        return $this->belongsTo(WishlistSearch::class);
    }
}
