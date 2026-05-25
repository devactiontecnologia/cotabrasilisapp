<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_content_id',
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'duration',
        'profile_type_required',
        'category',
        'tags',
        'views_count',
        'likes_count',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the educational content
     */
    public function educationalContent(): BelongsTo
    {
        return $this->belongsTo(EducationalContent::class);
    }

    /**
     * Get the comments
     */
    public function comments(): HasMany
    {
        return $this->hasMany(VideoComment::class, 'educational_video_id')
                    ->where('is_approved', true)
                    ->whereNull('parent_id'); // Apenas comentários principais
    }

    /**
     * Get all comments including replies
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(VideoComment::class, 'educational_video_id')
                    ->where('is_approved', true);
    }

    /**
     * Get the views
     */
    public function views(): HasMany
    {
        return $this->hasMany(VideoView::class, 'educational_video_id');
    }

    /**
     * Check if user can access based on profile type
     */
    public function canUserAccess(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->profile_type_required) {
            return true;
        }

        return $user->profile
            && $user->profile->profile_type === $this->profile_type_required;
    }

    /**
     * Record a view
     */
    public function recordView(User $user, int $durationWatched = 0, bool $completed = false): void
    {
        $this->views()->create([
            'user_id' => $user->id,
            'viewed_at' => now(),
            'duration_watched' => $durationWatched,
            'completed' => $completed,
        ]);

        $this->increment('views_count');
    }

    /**
     * Scope for active videos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for profile type
     */
    public function scopeForProfileType($query, ?string $profileType)
    {
        if (!$profileType) {
            return $query->whereNull('profile_type_required');
        }

        return $query->where(function($q) use ($profileType) {
            $q->whereNull('profile_type_required')
              ->orWhere('profile_type_required', $profileType);
        });
    }

    /**
     * Scope for category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
