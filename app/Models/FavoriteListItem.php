<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavoriteListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'favorite_list_id',
        'quota_id',
    ];

    public function favoriteList()
    {
        return $this->belongsTo(FavoriteList::class);
    }

    public function quota()
    {
        return $this->belongsTo(Quota::class);
    }
}









