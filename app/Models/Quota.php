<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Quota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hotel_name',
        'location',
        'start_date',
        'end_date',
        'number_of_guests',
        'rental_price',
        'is_exchange',
        'observations',
        'contract_photo_path',
        'status',
        'is_fractioned',
        'fraction_details',
        'quota_type',
        // Novos campos para gestão
        'weeks',
        'number_of_rooms',
        'seasonality',
        'payment_status',
        'is_owner',
        'authorizations',
        'is_published',
        'published_at',
        'quota_status',
        'transferred_at',
        'previous_owner_id',
        'allowed_uses',
        'negotiation_deadline',
        'current_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rental_price' => 'decimal:2',
            'is_exchange' => 'boolean',
            'is_fractioned' => 'boolean',
            'fraction_details' => 'array',
            // Novos campos
            'is_owner' => 'boolean',
            'authorizations' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'transferred_at' => 'datetime',
            'allowed_uses' => 'array',
        ];
    }

    /**
     * Get the user that owns the quota.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuários que marcaram esta cota como desejada (estrela na busca).
     */
    public function wishlistedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_wishlist_quotas')->withTimestamps();
    }

    /**
     * Get the previous owner of the quota.
     */
    public function previousOwner()
    {
        return $this->belongsTo(User::class, 'previous_owner_id');
    }

    /**
     * Get the transactions for the quota.
     */
    public function transactions()
    {
        return $this->hasMany(QuotaTransaction::class);
    }

    /**
     * Get the current transaction (if in negotiation).
     */
    public function currentTransaction()
    {
        return $this->belongsTo(QuotaTransaction::class, 'current_transaction_id');
    }

    /**
     * Get the rental offers for the quota.
     */
    public function rentalOffers()
    {
        return $this->hasMany(RentalOffer::class);
    }

    /**
     * Get the hotel for this quota (by name).
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_name', 'name');
    }

    /**
     * Get the active rental offers for the quota.
     */
    public function activeRentalOffers()
    {
        return $this->rentalOffers()->where('status', 'active');
    }

    /**
     * Ofertas de troca vinculadas à cota.
     */
    public function exchangeOffers()
    {
        return $this->hasMany(ExchangeOffer::class);
    }

    /**
     * Ofertas de venda vinculadas à cota.
     */
    public function saleOffers()
    {
        return $this->hasMany(SaleOffer::class);
    }

    /**
     * Cota aparece no marketplace quando existe oferta pública de aluguel, troca ou venda.
     */
    public function scopeWithMarketplaceListing(Builder $query): Builder
    {
        return $query->where(function (Builder $outer) {
            $outer->whereHas('rentalOffers', function (Builder $q) {
                $q->where('status', 'active')->whereNull('negotiated_at');
            })
                ->orWhereHas('exchangeOffers', function (Builder $q) {
                    $q->where('status', 'active')
                        ->where(function (Builder $v) {
                            $v->whereNull('validity_until')
                                ->orWhere('validity_until', '>', now());
                        });
                })
                ->orWhereHas('saleOffers', function (Builder $q) {
                    $q->whereNotIn('status', ['cancelled', 'sold']);
                });
        });
    }

    public function scopeWhereHasActiveRentalListing(Builder $query): Builder
    {
        return $query->whereHas('rentalOffers', function (Builder $q) {
            $q->where('status', 'active')->whereNull('negotiated_at');
        });
    }

    public function scopeWhereHasActiveExchangeListing(Builder $query): Builder
    {
        return $query->whereHas('exchangeOffers', function (Builder $q) {
            $q->where('status', 'active')
                ->where(function (Builder $v) {
                    $v->whereNull('validity_until')
                        ->orWhere('validity_until', '>', now());
                });
        });
    }

    public function scopeWhereHasActiveSaleListing(Builder $query): Builder
    {
        return $query->whereHas('saleOffers', function (Builder $q) {
            $q->whereNotIn('status', ['cancelled', 'sold']);
        });
    }

    /**
     * Cotas que podem aparecer na busca pública (aluguel/compra/troca).
     * Exclui negociação em andamento mesmo se status da cota ainda estiver "available" por inconsistência.
     */
    public function scopeListedInMarketplaceSearch(Builder $query): Builder
    {
        $terminalTxStatuses = [
            \App\Models\QuotaTransaction::STATUS_COMPLETED,
            \App\Models\QuotaTransaction::STATUS_CANCELLED,
            \App\Models\QuotaTransaction::STATUS_EXPIRED,
        ];

        return $query->where('status', self::STATUS_AVAILABLE)
            ->where(function (Builder $q) use ($terminalTxStatuses) {
                $q->whereNull('current_transaction_id')
                    ->orWhereHas('currentTransaction', function (Builder $t) use ($terminalTxStatuses) {
                        $t->whereIn('status', $terminalTxStatuses);
                    });
            });
    }

    public function scopeWhereFractionPeriodAction(Builder $query, string $action): Builder
    {
        $action = trim($action);

        return $query->whereNotNull('fraction_details')
            ->where(function (Builder $q) use ($action) {
                $q->where('fraction_details', 'like', '%"action":"' . $action . '"%')
                    ->orWhere('fraction_details', 'like', '%"action": "' . $action . '"%')
                    ->orWhere('fraction_details', 'like', "%'action':'" . $action . "'%")
                    ->orWhere('fraction_details', 'like', "%'action': '" . $action . "'%");
            });
    }

    /**
     * Preço exibido em listagens conforme tipo de transação (oferta de aluguel / troca / venda).
     */
    public function getMarketplaceListPrice(?string $transactionType = null): ?float
    {
        $type = $transactionType;
        if ($type === null || $type === '') {
            $type = request()->input('transaction_type', 'rent');
        }
        if ($type === 'rental') {
            $type = 'rent';
        }
        if ($type === 'purchase') {
            $type = 'buy';
        }

        if ($type === 'exchange') {
            return 0.0;
        }

        if (in_array($type, ['sell', 'buy'], true)) {
            $offer = $this->pickActiveSaleOfferForDisplay();
            if (! $offer) {
                return null;
            }
            foreach (['desired_price', 'acceptable_price', 'minimum_price'] as $field) {
                $v = $offer->getAttribute($field);
                if ($v !== null && (float) $v > 0) {
                    return (float) $v;
                }
            }

            return null;
        }

        $rental = $this->pickActiveRentalOfferForDisplay();
        if ($rental && $rental->price !== null) {
            return (float) $rental->price;
        }

        return $this->rental_price !== null ? (float) $this->rental_price : null;
    }

    public function normalizeMarketplaceTransactionType(?string $transactionType): string
    {
        $type = $transactionType ?? request()->input('transaction_type', 'rent');
        if ($type === 'rental') {
            return 'rent';
        }
        if ($type === 'purchase') {
            return 'buy';
        }

        return $type;
    }

    /**
     * Preço principal para cards sem transaction_type (ex.: destaques): aluguel, senão venda, senão troca (0).
     */
    public function getPrimaryMarketplaceDisplayPrice(): ?float
    {
        if ($this->pickActiveRentalOfferForDisplay() !== null) {
            return $this->getMarketplaceListPrice('rent');
        }
        if ($this->pickActiveSaleOfferForDisplay() !== null) {
            return $this->getMarketplaceListPrice('sell');
        }
        if ($this->activeExchangeOfferExists()) {
            return 0.0;
        }

        return null;
    }

    private function activeExchangeOfferExists(): bool
    {
        if ($this->relationLoaded('exchangeOffers')) {
            return $this->exchangeOffers->contains(function (ExchangeOffer $e) {
                return $e->status === 'active'
                    && (! $e->validity_until || $e->validity_until > now());
            });
        }

        return $this->exchangeOffers()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('validity_until')->orWhere('validity_until', '>', now());
            })
            ->exists();
    }

    private function pickActiveRentalOfferForDisplay(): ?RentalOffer
    {
        if ($this->relationLoaded('rentalOffers')) {
            $candidates = $this->rentalOffers
                ->where('status', 'active')
                ->whereNull('negotiated_at')
                ->sortBy(fn (RentalOffer $r) => $r->price === null ? PHP_FLOAT_MAX : (float) $r->price);

            return $candidates->first();
        }

        return $this->activeRentalOffers()
            ->whereNull('negotiated_at')
            ->orderBy('price')
            ->first();
    }

    private function pickActiveSaleOfferForDisplay(): ?SaleOffer
    {
        if ($this->relationLoaded('saleOffers')) {
            return $this->saleOffers
                ->whereNotIn('status', ['cancelled', 'sold'])
                ->sortByDesc('id')
                ->first();
        }

        return $this->saleOffers()
            ->whereNotIn('status', ['cancelled', 'sold'])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Get status constants.
     */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_NEGOTIATING = 'negotiating';
    public const STATUS_RENTED = 'rented';
    public const STATUS_EXCHANGED = 'exchanged';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * No cadastro, o usuário autorizou alugar ou alugar e trocar (uso "rent" em allowed_uses).
     * Cotas só com venda/compra/troca sem alugar não devem aparecer ao publicar oferta de aluguel.
     */
    public function allowsRentalPublicationFromRegistration(): bool
    {
        $uses = $this->allowed_uses;
        if (!is_array($uses)) {
            return false;
        }

        return in_array('rent', $uses, true);
    }

    /**
     * Check if quota is available.
     */
    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if quota is in negotiation.
     */
    public function isNegotiating()
    {
        return $this->status === self::STATUS_NEGOTIATING;
    }

    /**
     * Check if negotiation deadline has passed.
     */
    public function isNegotiationExpired()
    {
        return $this->isNegotiating() && 
               $this->negotiation_deadline && 
               $this->negotiation_deadline < now();
    }

    /**
     * Get the number of days for the quota.
     */
    public function getDaysCount()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
    
    /**
     * Get the number of nights for the quota.
     * For integral quota: 7 days = 7 nights
     * For fractioned quota: number of days in the fraction = number of nights
     */
    public function getNightsCount()
    {
        // Se for fracionada, calcular baseado na fração
        if ($this->is_fractioned && $this->fraction_details) {
            $fractionDetails = is_array($this->fraction_details) ? $this->fraction_details : [];
            
            // Se fraction_details tem start_date e end_date, calcular dias da fração
            if (isset($fractionDetails['start_date']) && isset($fractionDetails['end_date'])) {
                try {
                    $startDate = Carbon::parse($fractionDetails['start_date']);
                    $endDate = Carbon::parse($fractionDetails['end_date']);
                    return $startDate->diffInDays($endDate) + 1;
                } catch (\Exception $e) {
                    // Se houver erro ao parsear datas, continuar para outras opções
                }
            }
            
            // Se fraction_details tem number_of_days, usar esse valor
            if (isset($fractionDetails['number_of_days'])) {
                return (int) $fractionDetails['number_of_days'];
            }
        }
        
        // Para cota inteira, número de pernoites = número de dias
        if ($this->start_date && $this->end_date) {
            try {
                return $this->start_date->diffInDays($this->end_date) + 1;
            } catch (\Exception $e) {
                return 0;
            }
        }
        
        return 0;
    }

    /**
     * Verifica se um período está habilitado e com ação definida (alugar, trocar ou ambos).
     * Usado em publicações e telas públicas: exige ação válida; se existir a chave "enabled", ela precisa
     * estar ativa (0/false/off desligam). Se "enabled" não existir (cadastro legado), confia só na ação.
     */
    public static function isPeriodEnabledWithAction(array $period): bool
    {
        $action = trim((string) ($period['action'] ?? ''));
        $validActions = ['rent', 'exchange', 'rent_exchange', 'sell'];
        if (!\in_array($action, $validActions, true)) {
            return false;
        }

        if (!array_key_exists('enabled', $period)) {
            return true;
        }

        $enabledVal = $period['enabled'];
        if ($enabledVal === false || $enabledVal === 0 || $enabledVal === '0' || $enabledVal === 'false' || $enabledVal === 'off' || $enabledVal === 'no') {
            return false;
        }

        return $enabledVal === true || $enabledVal === 1 || $enabledVal === '1' || $enabledVal === 'on' || $enabledVal === 'true' || $enabledVal === 'yes' || $enabledVal === 'sim';
    }

    /**
     * Indica se, no cadastro (fraction_details), existe um período com as mesmas datas
     * e ação "Alugar e trocar" (rent_exchange), habilitado conforme isPeriodEnabledWithAction.
     */
    public function periodInRegistrationHasRentExchange(?string $rangeStart, ?string $rangeEnd): bool
    {
        if (!$rangeStart || !$rangeEnd) {
            return false;
        }

        try {
            $rs = Carbon::parse($rangeStart)->toDateString();
            $re = Carbon::parse($rangeEnd)->toDateString();
        } catch (\Throwable) {
            return false;
        }

        $details = $this->fraction_details;
        if (empty($details) || !is_array($details)) {
            return false;
        }

        if (isset($details['fraction_weeks']) && is_array($details['fraction_weeks'])) {
            foreach ($details['fraction_weeks'] as $weekData) {
                $periods = static::extractPeriodsFromWeekData($weekData);
                foreach ($periods as $period) {
                    if (!is_array($period) || !static::isPeriodEnabledWithAction($period)) {
                        continue;
                    }
                    if (trim((string) ($period['action'] ?? '')) !== 'rent_exchange') {
                        continue;
                    }
                    $ps = $period['start'] ?? $period['start_date'] ?? null;
                    $pe = $period['end'] ?? $period['end_date'] ?? null;
                    if (!$ps || !$pe) {
                        continue;
                    }
                    try {
                        if (Carbon::parse($ps)->toDateString() === $rs && Carbon::parse($pe)->toDateString() === $re) {
                            return true;
                        }
                    } catch (\Throwable) {
                    }
                }
            }
        } elseif (isset($details[0]) && is_array($details[0])) {
            foreach ($details as $period) {
                if (!is_array($period) || !static::isPeriodEnabledWithAction($period)) {
                    continue;
                }
                if (trim((string) ($period['action'] ?? '')) !== 'rent_exchange') {
                    continue;
                }
                $ps = $period['start_date'] ?? $period['start'] ?? null;
                $pe = $period['end_date'] ?? $period['end'] ?? null;
                if (!$ps || !$pe) {
                    continue;
                }
                try {
                    if (Carbon::parse($ps)->toDateString() === $rs && Carbon::parse($pe)->toDateString() === $re) {
                        return true;
                    }
                } catch (\Throwable) {
                }
            }
        }

        return false;
    }

    /**
     * Retorna linhas de exibição do período conforme cadastro: uma semana = "Período: xxx", duas ou mais = "Período semana 1: xxx", etc.
     * Inclui apenas períodos em que "Desejo alugar ou trocar este período" está habilitado e a ação (alugar/trocar/alugar e trocar) foi escolhida.
     *
     * @return array<int, array{label: string, formatted: string}>
     */
    public function getPeriodDisplayLines(): array
    {
        $lines = [];
        $details = $this->fraction_details;
        if (!empty($details) && is_array($details)) {
            if (isset($details['fraction_weeks']) && is_array($details['fraction_weeks'])) {
                $weeks = $details['fraction_weeks'];
                $enabledWeeks = [];
                $fallbackWeekNumber = 1;
                foreach ($weeks as $weekIndex => $weekData) {
                    $periods = static::extractPeriodsFromWeekData($weekData);
                    $starts = [];
                    $ends = [];
                    foreach ($periods as $period) {
                        if (!is_array($period) || !static::isPeriodEnabled($period)) {
                            continue;
                        }
                        $s = $period['start'] ?? $period['start_date'] ?? null;
                        $e = $period['end'] ?? $period['end_date'] ?? null;
                        if ($s && $e) {
                            $starts[] = $s;
                            $ends[] = $e;
                        }
                    }
                    if (!empty($starts) && !empty($ends)) {
                        $enabledWeeks[] = [
                            'week_number' => static::resolveWeekNumber($weekIndex, $fallbackWeekNumber),
                            'start' => min($starts),
                            'end' => max($ends),
                        ];
                    }
                    $fallbackWeekNumber++;
                }
                $numWeeks = count($enabledWeeks);
                foreach ($enabledWeeks as $range) {
                    $start = Carbon::parse($range['start']);
                    $end = Carbon::parse($range['end']);
                    $label = $numWeeks > 1 ? 'Período semana ' . $range['week_number'] . ':' : 'Período:';
                    $lines[] = [
                        'label' => $label,
                        'formatted' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
                    ];
                }
            } elseif (isset($details[0]) && is_array($details[0])) {
                $enabledList = [];
                foreach ($details as $idx => $fraction) {
                    if (!is_array($fraction) || !static::isPeriodEnabledWithAction($fraction)) {
                        continue;
                    }
                    $s = $fraction['start_date'] ?? $fraction['start'] ?? null;
                    $e = $fraction['end_date'] ?? $fraction['end'] ?? null;
                    if ($s && $e) {
                        $enabledList[] = ['idx' => $idx, 'start' => $s, 'end' => $e];
                    }
                }
                $count = count($enabledList);
                foreach ($enabledList as $item) {
                    $start = Carbon::parse($item['start']);
                    $end = Carbon::parse($item['end']);
                    $label = $count > 1 ? 'Período semana ' . ($item['idx'] + 1) . ':' : 'Período:';
                    $lines[] = [
                        'label' => $label,
                        'formatted' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
                    ];
                }
            }
        }
        if (empty($lines) && $this->start_date && $this->end_date) {
            $lines[] = [
                'label' => 'Período:',
                'formatted' => $this->start_date->format('d/m/Y') . ' - ' . $this->end_date->format('d/m/Y'),
            ];
        }
        return $lines;
    }

    /**
     * Retorna períodos com pernoites por cota/período.
     *
     * @return array<int, array{label: string, formatted: string, nights: int}>
     */
    public function getPeriodNightsBreakdown(): array
    {
        $items = [];
        $details = $this->fraction_details;

        if (!empty($details) && is_array($details)) {
            if (isset($details['fraction_weeks']) && is_array($details['fraction_weeks'])) {
                $fallbackWeekNumber = 1;
                foreach ($details['fraction_weeks'] as $weekIndex => $weekData) {
                    $periods = static::extractPeriodsFromWeekData($weekData);
                    $enabledPeriodsCount = 0;
                    foreach ($periods as $period) {
                        if (is_array($period) && static::isPeriodEnabled($period)) {
                            $enabledPeriodsCount++;
                        }
                    }

                    $position = 0;
                    foreach ($periods as $period) {
                        if (!is_array($period) || !static::isPeriodEnabled($period)) {
                            continue;
                        }
                        $startRaw = $period['start'] ?? $period['start_date'] ?? null;
                        $endRaw = $period['end'] ?? $period['end_date'] ?? null;
                        if (!$startRaw || !$endRaw) {
                            continue;
                        }
                        try {
                            $start = Carbon::parse($startRaw);
                            $end = Carbon::parse($endRaw);
                            $position++;
                            $weekNumber = static::resolveWeekNumber($weekIndex, $fallbackWeekNumber);
                            $label = 'Período semana ' . $weekNumber;
                            if ($enabledPeriodsCount > 1) {
                                $label .= ' - parte ' . $position;
                            }
                            $items[] = [
                                'label' => $label . ':',
                                'formatted' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
                                'nights' => max(0, $start->diffInDays($end)),
                            ];
                        } catch (\Exception $e) {
                        }
                    }
                    $fallbackWeekNumber++;
                }
            } elseif (isset($details[0]) && is_array($details[0])) {
                foreach ($details as $idx => $fraction) {
                    if (!is_array($fraction) || !static::isPeriodEnabled($fraction)) {
                        continue;
                    }
                    $startRaw = $fraction['start_date'] ?? $fraction['start'] ?? null;
                    $endRaw = $fraction['end_date'] ?? $fraction['end'] ?? null;
                    if (!$startRaw || !$endRaw) {
                        continue;
                    }
                    try {
                        $start = Carbon::parse($startRaw);
                        $end = Carbon::parse($endRaw);
                        $items[] = [
                            'label' => 'Período semana ' . ((int) $idx + 1) . ':',
                            'formatted' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
                            'nights' => max(0, $start->diffInDays($end)),
                        ];
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        if (empty($items) && $this->start_date && $this->end_date) {
            try {
                $items[] = [
                    'label' => 'Período:',
                    'formatted' => $this->start_date->format('d/m/Y') . ' - ' . $this->end_date->format('d/m/Y'),
                    'nights' => max(0, $this->start_date->diffInDays($this->end_date)),
                ];
            } catch (\Exception $e) {
            }
        }

        return $items;
    }

    private static function isPeriodEnabled(array $period): bool
    {
        $enabled = $period['enabled'] ?? null;
        if ($enabled === null) {
            return !empty($period['start'] ?? $period['start_date'] ?? null)
                && !empty($period['end'] ?? $period['end_date'] ?? null);
        }

        return in_array($enabled, [true, 1, '1', 'on', 'yes', 'sim'], true);
    }

    private static function resolveWeekNumber(mixed $weekIndex, int $fallback): int
    {
        if (is_numeric($weekIndex)) {
            $numeric = (int) $weekIndex;
            return $numeric >= 1 ? $numeric : $numeric + 1;
        }

        return $fallback;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function extractPeriodsFromWeekData(mixed $weekData): array
    {
        if (!is_array($weekData)) {
            return [];
        }

        if (isset($weekData['periods']) && is_array($weekData['periods'])) {
            return $weekData['periods'];
        }

        if (
            isset($weekData['start'], $weekData['end'])
            || isset($weekData['start_date'], $weekData['end_date'])
        ) {
            return [$weekData];
        }

        if (isset($weekData[0]) && is_array($weekData[0])) {
            return $weekData;
        }

        return [];
    }

    /**
     * Check if quota can be fractioned based on user profile.
     */
    public function canBeFractioned($userProfile)
    {
        $config = $userProfile->getProfileConfig();
        return $config['can_fraction'] ?? false;
    }

    /**
     * Check if quota is published.
     */
    public function isPublished()
    {
        return $this->is_published && $this->quota_status === 'active';
    }

    /**
     * Check if quota is paid.
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if quota is owned by user.
     */
    public function isOwnedByUser()
    {
        return $this->is_owner;
    }

    /**
     * Check if quota can be published.
     */
    public function canBePublished()
    {
        return $this->quota_status === 'active' && 
               $this->isPaid() && 
               $this->isOwnedByUser() && 
               !$this->isPublished();
    }

    /**
     * Publish the quota.
     */
    public function publish()
    {
        if ($this->canBePublished()) {
            $this->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Unpublish the quota.
     */
    public function unpublish()
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        
        // Cancelar ofertas ativas
        $this->activeRentalOffers()->update(['status' => 'cancelled']);
        
        return true;
    }

    /**
     * Transfer ownership of the quota.
     */
    public function transferOwnership($newOwnerId)
    {
        $this->update([
            'user_id' => $newOwnerId,
            'previous_owner_id' => $this->user_id,
            'transferred_at' => now(),
            'quota_status' => 'transferred',
            'is_published' => false,
            'published_at' => null,
        ]);
        
        // Cancelar ofertas ativas
        $this->activeRentalOffers()->update(['status' => 'cancelled']);
        
        return true;
    }

    /**
     * Get seasonality label.
     */
    public function getSeasonalityLabel()
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'peak' => 'Altíssima',
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'pico' => 'Altíssima',
            'altissima' => 'Altíssima',
            'altíssima' => 'Altíssima',
        ];
        
        $key = $this->seasonality ? strtolower((string) $this->seasonality) : '';

        return $labels[$key] ?? ($this->seasonality ? ucfirst((string) $this->seasonality) : 'Média');
    }

    /**
     * Get payment status label.
     */
    public function getPaymentStatusLabel()
    {
        $labels = [
            'paid' => 'Quitada',
            'unpaid' => 'Não Quitada'
        ];
        
        return $labels[$this->payment_status] ?? 'Não Quitada';
    }

    /**
     * Get quota status label.
     */
    public function getQuotaStatusLabel()
    {
        $labels = [
            'active' => 'Ativa',
            'inactive' => 'Inativa',
            'suspended' => 'Suspensa',
            'transferred' => 'Transferida'
        ];
        
        return $labels[$this->quota_status] ?? 'Ativa';
    }

    /**
     * Scope for published quotas.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where('quota_status', 'active');
    }

    /**
     * Scope for paid quotas.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for owned quotas.
     */
    public function scopeOwned($query)
    {
        return $query->where('is_owner', true);
    }

    /**
     * Scope for available quotas.
     */
    public function scopeAvailable($query)
    {
        return $query->where('quota_status', 'active')
                    ->where('status', 'available');
    }

    /**
     * Período de estadia ainda não encerrado (fim do período >= hoje).
     * Usado para listagens públicas: oculta cotas com data de término já vencida
     * enquanto seguem como disponíveis (não concluídas como aluguel/troca).
     */
    public function scopeWhereStayPeriodNotEnded($query)
    {
        return $query->whereNotNull('end_date')
            ->whereDate('end_date', '>=', Carbon::today());
    }

    /**
     * Build normalized registration details for quota detail screens.
     *
     * @return array<int, array{label: string, value: string, icon: string}>
     */
    public function getRegistrationDetailsForDisplay(): array
    {
        $profile = $this->user?->profile;
        $quotaDetails = is_array($profile?->quota_details) ? $profile->quota_details : [];

        // Sazonalidade/tipo/tamanho na cota têm prioridade; no perfil tentamos owner e gestor (cadastro pode ter gravado só um lado).
        $seasonalityRaw = $this->seasonality
            ?? $this->resolveValue($profile, $quotaDetails, [
                'owner_quota_seasonality',
                'gestor_quota_seasonality',
                'seasonality',
            ]);

        $quotaTypeRaw = $this->quota_type
            ?? $this->resolveValue($profile, $quotaDetails, [
                'owner_quota_type',
                'gestor_quota_type',
                'quota_type',
            ]);

        $size = $this->resolveValue($profile, $quotaDetails, [
            'owner_quota_size',
            'gestor_quota_size',
            'size',
        ]);

        $yesNo = fn ($value): string => $value ? 'Sim' : 'Não';

        $rows = [
            ['label' => 'Tamanho (m²)', 'value' => $size ?: 'Não informado', 'icon' => 'fa-ruler-combined'],
            ['label' => 'Varanda', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_balcony', 'gestor_quota_balcony', 'balcony'])), 'icon' => 'fa-house'],
            ['label' => 'Cozinha Completa', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_kitchen', 'gestor_quota_kitchen', 'kitchen'])), 'icon' => 'fa-utensils'],
            ['label' => 'Vista Mar', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_vista_mar', 'gestor_quota_vista_mar', 'vista_mar'])), 'icon' => 'fa-water'],
            ['label' => 'Jacuzzi', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_jacuzzi', 'gestor_quota_jacuzzi', 'owner_quota_hidromassagem', 'gestor_quota_hidromassagem', 'jacuzzi'])), 'icon' => 'fa-hot-tub-person'],
            ['label' => 'Spa', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_spa', 'gestor_quota_spa', 'spa'])), 'icon' => 'fa-spa'],
            ['label' => 'Piscina', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_piscina', 'gestor_quota_piscina', 'piscina'])), 'icon' => 'fa-person-swimming'],
            ['label' => 'Academia', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_academia', 'gestor_quota_academia', 'academia'])), 'icon' => 'fa-dumbbell'],
            ['label' => 'Lareira', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_lareira', 'gestor_quota_lareira', 'lareira'])), 'icon' => 'fa-fire'],
            ['label' => 'Adega', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_adega', 'gestor_quota_adega', 'adega'])), 'icon' => 'fa-wine-bottle'],
            ['label' => 'Área kids', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_area_kids', 'gestor_quota_area_kids', 'area_kids'])), 'icon' => 'fa-child-reaching'],
            ['label' => 'Área de trabalho', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_area_trabalho', 'gestor_quota_area_trabalho', 'area_trabalho'])), 'icon' => 'fa-briefcase'],
            ['label' => 'Wifi', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_wifi', 'gestor_quota_wifi', 'wifi'])), 'icon' => 'fa-wifi'],
            ['label' => 'Estacionamento Gratuito', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_parking', 'gestor_quota_parking', 'parking'])), 'icon' => 'fa-square-parking'],
            ['label' => 'Sazonalidade', 'value' => $this->formatSeasonality($seasonalityRaw), 'icon' => 'fa-sun'],
            ['label' => 'Tipo de Cota', 'value' => $this->formatQuotaType($quotaTypeRaw), 'icon' => 'fa-file-lines'],
            ['label' => 'Café da manhã gratuito', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_breakfast', 'gestor_quota_breakfast', 'breakfast'])), 'icon' => 'fa-mug-hot'],
            ['label' => 'Sofá mais', 'value' => $yesNo($this->resolveBoolean($profile, $quotaDetails, ['owner_quota_sofa_mais', 'gestor_quota_sofa_mais', 'sofa_mais'])), 'icon' => 'fa-couch'],
        ];

        return $this->dedupeRegistrationDetailRowsByLabel($rows);
    }

    /**
     * Remove linhas com o mesmo rótulo (ignorando maiúsculas e espaços extras).
     *
     * @param  array<int, array{label: string, value: string, icon: string}>  $rows
     * @return array<int, array{label: string, value: string, icon: string}>
     */
    private function dedupeRegistrationDetailRowsByLabel(array $rows): array
    {
        $seen = [];
        $out = [];
        foreach ($rows as $row) {
            $label = isset($row['label']) ? trim((string) $row['label']) : '';
            $key = $label === '' ? '_' . count($out) : mb_strtolower(preg_replace('/\s+/u', ' ', $label), 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $row;
        }

        return $out;
    }

    /**
     * Dados do cadastro da cota usam colunas/chaves owner_* ou gestor_* conforme o papel.
     * has_quota no perfil não pode ser inferido só do cast boolean: no banco, 2 = gestor.
     */
    private function profileUsesGestorFields(?UserProfile $profile): bool
    {
        if (!$profile) {
            return false;
        }

        $rawHas = $profile->getRawOriginal('has_quota');
        if ($rawHas === null && array_key_exists('has_quota', $profile->getAttributes())) {
            $rawHas = $profile->getAttributes()['has_quota'];
        }

        // has_quota é a fonte de verdade do cadastro: 1 = proprietário, 2 = gestor.
        // Antes is_authorized_user vinha antes e podia desalinhar dos dados gravados em owner_* / quota_details.
        if ($rawHas !== null && $rawHas !== '') {
            return (int) $rawHas === 2;
        }

        if (!empty($profile->is_authorized_user)) {
            return true;
        }
        if (!empty($profile->is_quota_owner)) {
            return false;
        }

        return false;
    }

    private function resolveValue($profile, array $quotaDetails, array $keys): mixed
    {
        $nested = [];
        foreach (['step5', 'step6'] as $block) {
            if (!empty($quotaDetails[$block]) && is_array($quotaDetails[$block])) {
                $nested = array_merge($nested, $quotaDetails[$block]);
            }
        }
        $flatQuota = array_merge($nested, $quotaDetails);

        foreach ($keys as $key) {
            if (array_key_exists($key, $flatQuota)) {
                return $flatQuota[$key];
            }
            if ($profile && array_key_exists($key, $profile->getAttributes())) {
                return $profile->{$key};
            }
        }
        return null;
    }

    private function resolveBoolean($profile, array $quotaDetails, array $keys): bool
    {
        $value = $this->resolveValue($profile, $quotaDetails, $keys);
        if ($value === null || $value === '') {
            return false;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'sim', 'yes', 'on'], true);
    }

    private function formatSeasonality($value): string
    {
        $map = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'peak' => 'Altíssima',
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'pico' => 'Altíssima',
            'altissima' => 'Altíssima',
            'altíssima' => 'Altíssima',
        ];

        if (!$value) {
            return 'Não informado';
        }

        $key = strtolower((string) $value);
        return $map[$key] ?? ucfirst((string) $value);
    }

    private function formatQuotaType($value): string
    {
        $map = [
            'fixa' => 'Fixa',
            'flexivel' => 'Flexível',
            'fix_flexivel' => 'Fixa + Flexível',
        ];

        if (!$value) {
            return 'Não informado';
        }

        $key = strtolower((string) $value);
        return $map[$key] ?? ucfirst((string) $value);
    }

    /**
     * Return each registered room details from profile/quota_details.
     *
     * @return array<int, array{title: string, description: string}>
     */
    public function getRoomDetailsForDisplay(): array
    {
        $profile = $this->user?->profile;
        if (!$profile) {
            return [];
        }

        $quotaDetails = is_array($profile->quota_details) ? $profile->quota_details : [];
        $roomsKey = $this->profileUsesGestorFields($profile) ? 'gestor_rooms' : 'owner_rooms';
        $rooms = $quotaDetails[$roomsKey] ?? [];

        if (!is_array($rooms) || $rooms === []) {
            return [];
        }

        $items = [];
        foreach ($rooms as $index => $room) {
            if (!is_array($room)) {
                continue;
            }

            $suite = (int) ($room['suite'] ?? 0);
            $doubleBed = (int) ($room['double_bed'] ?? 0);
            $singleBed = (int) ($room['single_bed'] ?? 0);
            $sofaBed = (int) ($room['sofa_bed'] ?? 0);
            $bunkBed = (int) ($room['bunk_bed'] ?? 0);
            $people = (int) ($room['people'] ?? 0);

            $parts = [];
            $parts[] = $suite === 1 ? 'Suíte' : 'Sem suíte';
            if ($doubleBed > 0) {
                $parts[] = $doubleBed . ' cama(s) de casal';
            }
            if ($singleBed > 0) {
                $parts[] = $singleBed . ' cama(s) de solteiro';
            }
            if ($sofaBed > 0) {
                $parts[] = $sofaBed . ' sofá(s)-cama';
            }
            if ($bunkBed > 0) {
                $parts[] = $bunkBed . ' beliche(s)';
            }
            if ($people > 0) {
                $parts[] = $people . ' pessoa(s)';
            }

            $items[] = [
                'title' => 'Quarto ' . (is_numeric($index) ? (int) $index : (count($items) + 1)),
                'description' => implode(' | ', $parts),
            ];
        }

        return $items;
    }
}
