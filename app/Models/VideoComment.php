<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_video_id',
        'user_id',
        'comment',
        'parent_id',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Get the video
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(EducationalVideo::class, 'educational_video_id');
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (if this is a reply)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(VideoComment::class, 'parent_id');
    }

    /**
     * Get the replies
     */
    public function replies(): HasMany
    {
        return $this->hasMany(VideoComment::class, 'parent_id')
                    ->where('is_approved', true);
    }
}
