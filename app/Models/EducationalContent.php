<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'body',
        'content_type',
        'profile_type_required',
        'category',
        'tags',
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
     * Get the videos for this content
     */
    public function videos(): HasMany
    {
        return $this->hasMany(EducationalVideo::class);
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
     * Scope for active content
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
}
