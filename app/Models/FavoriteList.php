<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavoriteList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'transaction_type',
    ];

    /**
     * Get the user that owns the favorite list.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quotas in this favorite list.
     */
    public function quotas()
    {
        return $this->belongsToMany(Quota::class, 'favorite_list_items')
                    ->withTimestamps();
    }
}









