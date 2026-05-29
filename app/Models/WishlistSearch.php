<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishlistSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_type',
        'list_type',
        'hotel_name',
        'city',
        'state',
        'start_date',
        'end_date',
        'number_of_guests',
        'number_of_rooms',
        'nights',
        'seasonality',
        'quota_type',
        'price_min',
        'price_max',
        'apartment_amenities',
        'notified',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'apartment_amenities' => 'array',
        'notified' => 'boolean',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    /**
     * Get the user that owns the wishlist search.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}









