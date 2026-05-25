<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAuthorizationDocument extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'file_path',
        'sort_order',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function publicAssetUrl(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function suggestedDownloadFilename(): string
    {
        $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $this->slug) ?: 'documento';
        $ext = $this->file_path ? strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION) ?: '') : '';

        return $base . ($ext !== '' ? '.' . $ext : '');
    }
}
