<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoView extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_video_id',
        'user_id',
        'viewed_at',
        'duration_watched',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'completed' => 'boolean',
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
}
