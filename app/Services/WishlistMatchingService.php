<?php

namespace App\Services;

use App\Models\Quota;
use App\Models\User;
use App\Models\WishlistOwnerAlert;
use App\Models\WishlistSearch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
class WishlistMatchingService
{
    public const TRANSACTION_ORDER = ['rental', 'exchange', 'purchase'];

    public const LIST_TYPE_ORDER = ['state', 'city', 'hotel'];

    public function __construct(
        protected NotificationService $notifications
    ) {}

    public function normalizeTransactionType(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return match (true) {
            in_array($type, ['rent', 'rental'], true) => 'rental',
            in_array($type, ['buy', 'purchase', 'sell'], true) => 'purchase',
            $type === 'exchange' => 'exchange',
            default => 'rental',
        };
    }

    public function inferListType(?string $hotel, ?string $city, ?string $state): string
    {
        if ($hotel) {
            return 'hotel';
        }
        if ($city) {
            return 'city';
        }

        return 'state';
    }

    public function extractStateFromQuota(Quota $quota): string
    {
        if ($quota->hotel?->state) {
            return trim((string) $quota->hotel->state);
        }
        if ($quota->location) {
            $parts = preg_split('/,\s*/', $quota->location);
            if (count($parts) >= 2) {
                return trim($parts[1]);
            }
        }

        return 'Sem estado';
    }

    public function extractCityFromQuota(Quota $quota): string
    {
        if ($quota->hotel?->city) {
            return trim((string) $quota->hotel->city);
        }
        if ($quota->location) {
            $parts = preg_split('/,\s*/', $quota->location);

            return trim($parts[0]);
        }

        return $quota->location ?? 'Sem localização';
    }

    /**
     * Processa uma busca salva: alerta donos sem oferta publicada e avisa interessado se já houver oferta.
     */
    public function processSavedSearch(WishlistSearch $search): void
    {
        $search->loadMissing('user');
        $transactionType = $this->normalizeTransactionType($search->transaction_type);

        $this->notifyOwnersWithUnpublishedMatchingQuotas($search, $transactionType);
        $this->notifySearcherIfPublishedOffersExist($search, $transactionType);
    }

    /**
     * Reprocessa buscas ainda não atendidas (cron).
     */
    public function processPendingSearches(): int
    {
        $count = 0;
        WishlistSearch::query()
            ->where('notified', false)
            ->with('user')
            ->orderBy('id')
            ->chunkById(50, function ($searches) use (&$count) {
                foreach ($searches as $search) {
                    $this->processSavedSearch($search);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Agrupa cotas desejadas: transaction_type → list_type → list_name → [quotas].
     */
    public function groupWishlistQuotas(Collection $quotas): Collection
    {
        $grouped = collect();

        foreach ($quotas as $quota) {
            $tx = $this->normalizeTransactionType($quota->pivot->transaction_type ?? 'rental');
            $listType = $quota->pivot->list_type ?? 'city';
            $listName = match ($listType) {
                'hotel' => $quota->hotel_name ?? 'Sem hotel',
                'state' => $this->extractStateFromQuota($quota),
                default => $this->extractCityFromQuota($quota),
            };

            $grouped->put($tx, $grouped->get($tx, collect()));
            $txGroup = $grouped[$tx];
            $txGroup->put($listType, $txGroup->get($listType, collect()));
            $typeGroup = $txGroup[$listType];
            $typeGroup->put($listName, $typeGroup->get($listName, collect())->push($quota));
        }

        return $grouped;
    }

    public function listBucketForQuota(Quota $quota, ?string $preferredType = null): array
    {
        if ($preferredType && in_array($preferredType, self::LIST_TYPE_ORDER, true)) {
            $listType = $preferredType;
        } elseif ($quota->hotel_name) {
            $listType = 'hotel';
        } else {
            $listType = 'city';
        }

        $listName = match ($listType) {
            'hotel' => $quota->hotel_name ?? 'Sem hotel',
            'state' => $this->extractStateFromQuota($quota),
            default => $this->extractCityFromQuota($quota),
        };

        return [$listType, $listName];
    }

    /**
     * Agrupa buscas salvas por transaction_type e list_type.
     */
    public function groupWishlistSearches(Collection $searches): Collection
    {
        $grouped = collect();

        foreach ($searches as $search) {
            $tx = $this->normalizeTransactionType($search->transaction_type);
            $listType = $search->list_type ?? $this->inferListType($search->hotel_name, $search->city, $search->state);
            $listName = match ($listType) {
                'hotel' => $search->hotel_name ?? 'Sem hotel',
                'city' => $search->city ?? 'Sem cidade',
                default => $search->state ?? 'Sem estado',
            };

            if (! $grouped->has($tx)) {
                $grouped->put($tx, collect());
            }
            if (! $grouped[$tx]->has($listType)) {
                $grouped[$tx]->put($listType, collect());
            }
            if (! $grouped[$tx][$listType]->has($listName)) {
                $grouped[$tx][$listType]->put($listName, collect());
            }
            $grouped[$tx][$listType][$listName]->push($search);
        }

        return $grouped;
    }

    protected function notifyOwnersWithUnpublishedMatchingQuotas(WishlistSearch $search, string $transactionType): void
    {
        $interested = $search->user;
        if (! $interested) {
            return;
        }

        $candidates = $this->matchingQuotasQuery($search, $transactionType, $interested->id)->get();

        foreach ($candidates as $quota) {
            if ($this->quotaHasPublishedListing($quota, $transactionType)) {
                continue;
            }

            if (! $this->quotaAllowsTransactionType($quota, $transactionType)) {
                continue;
            }

            $alert = WishlistOwnerAlert::firstOrCreate(
                [
                    'owner_user_id' => $quota->user_id,
                    'quota_id' => $quota->id,
                    'transaction_type' => $transactionType,
                    'interested_user_id' => $interested->id,
                ],
                [
                    'wishlist_search_id' => $search->id,
                    'interested_count' => 1,
                ]
            );

            if ($alert->notified_at && $alert->notified_at->gt(now()->subDays(7))) {
                continue;
            }

            $totalInterested = $this->countMatchingWishlistDemands($search, $transactionType);

            $alert->update([
                'interested_count' => max($alert->interested_count, $totalInterested),
                'wishlist_search_id' => $search->id,
            ]);

            $owner = $quota->user;
            if ($owner) {
                $publishUrl = route(
                    $this->publishOfferRouteForType($transactionType),
                    ['quota_id' => $quota->id]
                );
                $this->notifications->notifyOwnerToPublishForWishlistMatch(
                    $owner,
                    $quota,
                    $transactionType,
                    $totalInterested,
                    $search,
                    $publishUrl
                );
                $alert->update(['notified_at' => now()]);
            }
        }
    }

    protected function notifySearcherIfPublishedOffersExist(WishlistSearch $search, string $transactionType): void
    {
        $user = $search->user;
        if (! $user) {
            return;
        }

        $hasMatch = $this->matchingQuotasQuery($search, $transactionType, $user->id)
            ->where(function (Builder $q) use ($transactionType) {
                match ($transactionType) {
                    'rental' => $q->whereHasActiveRentalListing(),
                    'exchange' => $q->whereHasActiveExchangeListing(),
                    'purchase' => $q->whereHasActiveSaleListing(),
                };
            })
            ->exists();

        if (! $hasMatch) {
            return;
        }

        $this->notifications->notifyWishlistSearcherOfferAvailable($user, $search, $transactionType);
        $search->update(['notified' => true]);
    }

    protected function matchingQuotasQuery(WishlistSearch $search, string $transactionType, int $excludeUserId): Builder
    {
        $query = Quota::query()
            ->with(['user', 'hotel'])
            ->listedInMarketplaceSearch()
            ->where('user_id', '!=', $excludeUserId);

        if ($search->hotel_name) {
            $hotel = $search->hotel_name;
            $query->where('hotel_name', 'like', '%'.$hotel.'%');
        }

        if ($search->city) {
            $city = $search->city;
            $query->where(function (Builder $q) use ($city) {
                $q->where('location', 'like', '%'.$city.'%')
                    ->orWhereHas('hotel', fn (Builder $h) => $h->where('city', 'like', '%'.$city.'%'));
            });
        }

        if ($search->state) {
            $state = $search->state;
            $query->where(function (Builder $q) use ($state) {
                $q->where('location', 'like', '%'.$state.'%')
                    ->orWhereHas('hotel', fn (Builder $h) => $h->where('state', 'like', '%'.$state.'%'));
            });
        }

        if ($search->start_date && $search->end_date) {
            $start = Carbon::parse($search->start_date)->startOfDay();
            $end = Carbon::parse($search->end_date)->endOfDay();
            $query->where('start_date', '<=', $end)
                ->where('end_date', '>=', $start);
        }

        if ($search->number_of_guests) {
            $query->where('number_of_guests', '>=', (int) $search->number_of_guests);
        }

        return $query;
    }

    public function quotaHasPublishedListing(Quota $quota, string $transactionType): bool
    {
        return match ($transactionType) {
            'rental' => $quota->activeRentalOffers()->exists(),
            'exchange' => $quota->exchangeOffers()
                ->where('status', 'active')
                ->where(function (Builder $q) {
                    $q->whereNull('validity_until')->orWhere('validity_until', '>', now());
                })
                ->exists(),
            'purchase' => $quota->saleOffers()->whereNotIn('status', ['cancelled', 'sold'])->exists(),
            default => false,
        };
    }

    public function quotaAllowsTransactionType(Quota $quota, string $transactionType): bool
    {
        $uses = is_array($quota->allowed_uses) ? $quota->allowed_uses : [];

        return match ($transactionType) {
            'rental' => $quota->allowsRentalPublicationFromRegistration(),
            'exchange' => in_array('exchange', $uses, true)
                || in_array('rent_exchange', $uses, true)
                || $quota->is_exchange,
            'purchase' => in_array('sell', $uses, true),
            default => false,
        };
    }

    public function publishOfferRouteForType(string $transactionType): string
    {
        return match ($transactionType) {
            'exchange' => 'exchanges.create',
            'purchase' => 'sales.create',
            default => 'rental-offers.create',
        };
    }

    /**
     * Quando uma oferta é publicada, avisa buscas salvas compatíveis ainda não notificadas.
     */
    public function processNewPublishedListing(Quota $quota, string $transactionType): void
    {
        $transactionType = $this->normalizeTransactionType($transactionType);

        if (! $this->quotaHasPublishedListing($quota, $transactionType)) {
            return;
        }

        WishlistSearch::query()
            ->where('notified', false)
            ->where('transaction_type', $transactionType)
            ->with('user')
            ->orderBy('id')
            ->chunkById(50, function ($searches) use ($quota, $transactionType) {
                foreach ($searches as $search) {
                    if (! $search->user) {
                        continue;
                    }
                    $matches = $this->matchingQuotasQuery($search, $transactionType, $search->user_id)
                        ->where('quotas.id', $quota->id)
                        ->exists();
                    if ($matches) {
                        $this->notifySearcherIfPublishedOffersExist($search, $transactionType);
                    }
                }
            });
    }

    protected function countMatchingWishlistDemands(WishlistSearch $search, string $transactionType): int
    {
        return WishlistSearch::query()
            ->where('transaction_type', $transactionType)
            ->when($search->hotel_name, fn (Builder $q) => $q->where('hotel_name', $search->hotel_name))
            ->when($search->city, fn (Builder $q) => $q->where('city', $search->city))
            ->when($search->state, fn (Builder $q) => $q->where('state', $search->state))
            ->when($search->start_date && $search->end_date, function (Builder $q) use ($search) {
                $q->where('start_date', '<=', $search->end_date)
                    ->where('end_date', '>=', $search->start_date);
            })
            ->count();
    }
}
