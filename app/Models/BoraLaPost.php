<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoraLaPost extends Model
{
    public const TYPE_OFERTA_UNICA = 'oferta_unica';

    public const TYPE_ATUALIZACAO = 'atualizacao';

    public const TYPE_AVISO = 'aviso';

    public const TYPE_ENQUETE = 'enquete';

    public const TYPE_DICA = 'dica';

    public const TYPES = [
        self::TYPE_OFERTA_UNICA => 'Oferta única',
        self::TYPE_ATUALIZACAO => 'Atualização',
        self::TYPE_AVISO => 'Aviso',
        self::TYPE_ENQUETE => 'Enquete',
        self::TYPE_DICA => 'Dica',
    ];

    protected $fillable = [
        'type',
        'title',
        'body',
        'payload',
        'is_published',
        'published_at',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Ofertas únicas com período definido no payload: oculta após data/hora de término.
     */
    public function isExpiredUniqueOffer(): bool
    {
        if ($this->type !== self::TYPE_OFERTA_UNICA) {
            return false;
        }
        $p = $this->payload ?? [];
        $endDate = $p['end_date'] ?? null;
        if (! $endDate) {
            return false;
        }
        $endTime = $p['end_time'] ?? '23:59';
        try {
            return Carbon::parse($endDate.' '.$endTime)->lt(now());
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function displayBodyHtml(): string
    {
        $raw = (string) ($this->body ?? '');
        if ($raw === '') {
            return '';
        }

        return nl2br(e($raw));
    }

    public function payloadString(string $key, ?string $default = null): ?string
    {
        $v = data_get($this->payload, $key);

        return $v !== null && $v !== '' ? (string) $v : $default;
    }

    /**
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function publishedListingForType(string $type)
    {
        return static::query()
            ->published()
            ->ofType($type)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->reject(fn (self $p) => $p->isExpiredUniqueOffer());
    }

    /**
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function publishedRecentForDashboard(int $limit = 8)
    {
        return static::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reject(fn (self $p) => $p->isExpiredUniqueOffer());
    }
}
