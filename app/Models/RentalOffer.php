<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class RentalOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quota_id',
        'hotel_id',
        'title',
        'description',
        'city',
        'state',
        'start_date',
        'end_date',
        'number_of_days',
        'number_of_people',
        'price',
        'original_price',
        'status',
        'is_fractioned',
        'fraction_details',
        'is_auction',
        'minimum_price',
        'auction_end_time',
        'photos',
        'observations',
        'views_count',
        'favorites_count',
        'negotiated_at',
        'negotiated_with',
        'super_desconto_applied',
        'super_desconto_applied_at',
        'super_desconto_percentage',
        'mega_oferta_applied',
        'mega_oferta_applied_at',
        'mega_oferta_percentage',
        'app_commission',
        'is_penalized',
        'penalty_until',
        'penalty_reason',
        // Novos campos para melhorias
        'period_type',
        'flexible_weeks',
        'price_min',
        'price_max',
        'auction_start_time',
        'auction_duration_minutes',
        'auction_day',
        'auction_start_hour',
        'is_batch_offer',
        'batch_quota_ids',
        'accepts_exchange',
        'accepts_sale',
        'accepts_diaria_exchange',
        'days_until_start',
        'auto_discount_applied',
        'auto_discount_percentage',
        'auto_discount_applied_at',
        'rented_at',
        'moved_to_metrics',
        'metrics_type',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'minimum_price' => 'decimal:2',
            'is_fractioned' => 'boolean',
            'fraction_details' => 'array',
            'is_auction' => 'boolean',
            'auction_end_time' => 'datetime',
            'photos' => 'array',
            'negotiated_at' => 'datetime',
            'super_desconto_applied' => 'boolean',
            'super_desconto_applied_at' => 'datetime',
            'super_desconto_percentage' => 'decimal:2',
            'mega_oferta_applied' => 'boolean',
            'mega_oferta_applied_at' => 'datetime',
            'mega_oferta_percentage' => 'decimal:2',
            'app_commission' => 'decimal:2',
            'is_penalized' => 'boolean',
            'penalty_until' => 'datetime',
            // Novos casts
            'flexible_weeks' => 'array',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'auction_start_time' => 'datetime',
            'auction_day' => 'date',
            'auction_start_hour' => 'datetime',
            'batch_quota_ids' => 'array',
            'accepts_exchange' => 'boolean',
            'accepts_sale' => 'boolean',
            'accepts_diaria_exchange' => 'boolean',
            'auto_discount_applied' => 'boolean',
            'auto_discount_percentage' => 'decimal:2',
            'auto_discount_applied_at' => 'datetime',
            'rented_at' => 'datetime',
            'moved_to_metrics' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the offer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quota associated with the offer.
     */
    public function quota()
    {
        return $this->belongsTo(Quota::class);
    }

    /**
     * Get the hotel associated with the offer.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * URL pública de uma imagem no disco storage (oferta ou hotel).
     */
    public static function resolveStorageImageUrl(mixed $image): ?string
    {
        if ($image === null || $image === '') {
            return null;
        }

        if (is_array($image)) {
            $image = $image['path'] ?? $image['url'] ?? $image['src'] ?? null;
        }

        if (! is_string($image) || trim($image) === '') {
            return null;
        }

        return UserProfile::publicStorageUrl($image);
    }

    /**
     * URLs das fotos para exibição: oferta primeiro, depois imagens do hotel.
     *
     * @return array<int, string>
     */
    public function getDisplayImageUrls(): array
    {
        $urls = [];

        foreach ($this->photos ?? [] as $photo) {
            $url = self::resolveStorageImageUrl($photo);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        if ($urls !== []) {
            return $urls;
        }

        $hotel = $this->relationLoaded('hotel') ? $this->hotel : $this->hotel()->first();
        if ($hotel) {
            foreach ($hotel->images ?? [] as $image) {
                $url = self::resolveStorageImageUrl($image);
                if ($url !== null) {
                    $urls[] = $url;
                }
            }
        }

        return $urls;
    }

    /**
     * Get the user who negotiated the offer.
     */
    public function negotiatedWith()
    {
        return $this->belongsTo(User::class, 'negotiated_with');
    }

    /**
     * Verificar se o título é válido e descritivo
     */
    private function isValidTitle(?string $title): bool
    {
        if (empty($title)) {
            return false;
        }

        // Títulos muito curtos (menos de 4 caracteres) não são válidos
        if (strlen(trim($title)) < 4) {
            return false;
        }

        // Verificar se parece ser um placeholder/teste
        $invalidPatterns = [
            '/^(test|teste|fg|dfg|abc|xyz|123|aaa|bbb|asd|qwe|zxc)/i',
            '/^[a-z]{1,6}$/i', // Apenas letras minúsculas, muito curto
            '/^[0-9]+$/', // Apenas números
            '/^(fgdfg|dfgdfg|fdgdfg|fgdfgdfgdf)/i', // Padrões específicos de teste
        ];

        foreach ($invalidPatterns as $pattern) {
            if (preg_match($pattern, trim($title))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obter título formatado para exibição
     * Se o título não for adequado, gera um título automático baseado nas informações
     */
    public function getDisplayTitleAttribute(): string
    {
        // Se o título é válido, usar ele
        if ($this->isValidTitle($this->title)) {
            return $this->title;
        }

        // Gerar título automático baseado nas informações
        $parts = [];
        
        // Adicionar hotel se disponível
        if ($this->hotel && $this->hotel->name) {
            $hotelName = $this->hotel->name;
            // Limitar tamanho do nome do hotel se muito longo
            if (strlen($hotelName) > 30) {
                $hotelName = substr($hotelName, 0, 27) . '...';
            }
            $parts[] = $hotelName;
        } elseif ($this->city) {
            $parts[] = $this->city;
        }
        
        // Adicionar tipo de oferta
        if ($this->is_auction) {
            $parts[] = 'Leilão';
        } else {
            $parts[] = 'Aluguel';
        }
        
        // Adicionar período se disponível
        if ($this->start_date && $this->end_date) {
            try {
                $startDate = \Carbon\Carbon::parse($this->start_date);
                $endDate = \Carbon\Carbon::parse($this->end_date);
                
                // Se for no mesmo mês, mostrar apenas o mês
                if ($startDate->format('m/Y') === $endDate->format('m/Y')) {
                    $parts[] = $startDate->format('M/Y');
                } else {
                    // Se for em meses diferentes, mostrar o período
                    $parts[] = $startDate->format('M') . '-' . $endDate->format('M/Y');
                }
            } catch (\Exception $e) {
                // Ignorar erro de data
            }
        }
        
        // Se não tiver partes suficientes, usar um título genérico mais descritivo
        if (empty($parts)) {
            return 'Oferta de Aluguel';
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Get the auctions for this offer.
     */
    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }

    /**
     * Get the winning auction.
     */
    public function winningAuction()
    {
        return $this->hasOne(Auction::class)->where('is_winning_bid', true);
    }

    /**
     * Check if offer is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if offer is negotiated.
     */
    public function isNegotiated()
    {
        return $this->status === 'negotiated';
    }

    /**
     * Check if offer is expired.
     */
    public function isExpired()
    {
        return $this->status === 'expired' || $this->end_date < now()->toDateString();
    }

    /**
     * Check if auction is active.
     */
    public function isAuctionActive()
    {
        return $this->is_auction && 
               $this->auction_end_time && 
               $this->auction_end_time > now() && 
               $this->isActive();
    }

    /**
     * Check if auction is ended.
     */
    public function isAuctionEnded()
    {
        return $this->is_auction && 
               $this->auction_end_time && 
               $this->auction_end_time <= now();
    }

    /**
     * Get the highest bid amount.
     */
    public function getHighestBidAmount()
    {
        return $this->auctions()->max('bid_amount') ?? 0;
    }

    /**
     * Get the highest bid.
     */
    public function getHighestBid()
    {
        return $this->auctions()->orderBy('bid_amount', 'desc')->first();
    }

    /**
     * Check if user can create fractioned offers.
     */
    public function canCreateFractionedOffers()
    {
        return $this->user->profile && 
               in_array($this->user->profile->profile_type, ['inteligente', 'sabio']);
    }

    /**
     * Check if user can create auctions.
     */
    public function canCreateAuctions()
    {
        return $this->user->profile && 
               in_array($this->user->profile->profile_type, ['inteligente', 'sabio']);
    }

    /**
     * Get the number of days until auction ends.
     */
    public function getDaysUntilAuctionEnds()
    {
        if (!$this->is_auction || !$this->auction_end_time) {
            return null;
        }

        return now()->diffInDays($this->auction_end_time, false);
    }

    /**
     * Get the number of hours until auction ends.
     */
    public function getHoursUntilAuctionEnds()
    {
        if (!$this->is_auction || !$this->auction_end_time) {
            return null;
        }

        return now()->diffInHours($this->auction_end_time, false);
    }

    /**
     * Scope for active offers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for auction offers.
     */
    public function scopeAuctions($query)
    {
        return $query->where('is_auction', true);
    }

    /**
     * Scope for fractioned offers.
     */
    public function scopeFractioned($query)
    {
        return $query->where('is_fractioned', true);
    }

    /**
     * Scope for offers by city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Scope for offers by price range.
     */
    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->where(function($q) use ($minPrice, $maxPrice) {
            $q->whereBetween('price', [$minPrice, $maxPrice])
              ->orWhere(function($q2) use ($minPrice, $maxPrice) {
                  $q2->whereNotNull('price_min')
                     ->whereNotNull('price_max')
                     ->where(function($q3) use ($minPrice, $maxPrice) {
                         $q3->whereBetween('price_min', [$minPrice, $maxPrice])
                            ->orWhereBetween('price_max', [$minPrice, $maxPrice])
                            ->orWhere(function($q4) use ($minPrice, $maxPrice) {
                                $q4->where('price_min', '<=', $minPrice)
                                   ->where('price_max', '>=', $maxPrice);
                            });
                     });
              });
        });
    }

    /**
     * Scope for offers by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Check if offer is eligible for SuperDesconto (14 days active)
     */
    public function isEligibleForSuperDesconto(): bool
    {
        if ($this->super_desconto_applied || $this->mega_oferta_applied) {
            return false;
        }

        $daysActive = $this->created_at->diffInDays(now());
        return $daysActive >= 14 && $this->status === 'active';
    }

    /**
     * Apply SuperDesconto automatically
     */
    public function applySuperDesconto(float $percentage = 10.0): bool
    {
        if (!$this->isEligibleForSuperDesconto()) {
            return false;
        }

        $this->update([
            'super_desconto_applied' => true,
            'super_desconto_applied_at' => now(),
            'super_desconto_percentage' => $percentage,
            'original_price' => $this->price,
            'price' => $this->price * (1 - $percentage / 100),
        ]);

        return true;
    }

    /**
     * Check if offer is eligible for MegaOferta
     */
    public function isEligibleForMegaOferta(): bool
    {
        if ($this->mega_oferta_applied) {
            return false;
        }

        // MegaOferta pode substituir SuperDesconto se aplicado dentro da janela
        $daysActive = $this->created_at->diffInDays(now());
        return $daysActive >= 14 && $daysActive <= 21 && $this->status === 'active';
    }

    /**
     * Apply MegaOferta (substitui SuperDesconto)
     */
    public function applyMegaOferta(float $percentage = 15.0): bool
    {
        if (!$this->isEligibleForMegaOferta()) {
            return false;
        }

        $originalPrice = $this->original_price ?? $this->price;
        $newPrice = $originalPrice * (1 - $percentage / 100);
        
        // Calcular taxa adicional para cobrir comissão extra do app
        $appCommission = $newPrice * 0.05; // 5% adicional para MegaOferta

        $this->update([
            'mega_oferta_applied' => true,
            'mega_oferta_applied_at' => now(),
            'mega_oferta_percentage' => $percentage,
            'super_desconto_applied' => false, // Remove SuperDesconto
            'super_desconto_applied_at' => null,
            'super_desconto_percentage' => 0,
            'original_price' => $originalPrice,
            'price' => $newPrice,
            'app_commission' => $appCommission,
        ]);

        return true;
    }

    /**
     * Check if user is penalized
     */
    public function isPenalized(): bool
    {
        return $this->is_penalized && $this->penalty_until && $this->penalty_until > now();
    }

    /**
     * Apply penalty for non-compliance
     */
    public function applyPenalty(string $reason, int $hours = 24): void
    {
        $this->update([
            'is_penalized' => true,
            'penalty_until' => now()->addHours($hours),
            'penalty_reason' => $reason,
        ]);
    }

    /**
     * Remove penalty
     */
    public function removePenalty(): void
    {
        $this->update([
            'is_penalized' => false,
            'penalty_until' => null,
            'penalty_reason' => null,
        ]);
    }

    /**
     * Check if offer can be created (not penalized)
     */
    public function canBeCreated(): bool
    {
        return !$this->isPenalized();
    }

    /**
     * Scope for penalized offers
     */
    public function scopePenalized($query)
    {
        return $query->where('is_penalized', true)
                    ->where('penalty_until', '>', now());
    }

    /**
     * Scope for offers eligible for SuperDesconto
     */
    public function scopeEligibleForSuperDesconto($query)
    {
        return $query->where('status', 'active')
                    ->where('super_desconto_applied', false)
                    ->where('mega_oferta_applied', false)
                    ->whereRaw('DATEDIFF(NOW(), created_at) >= 14');
    }

    /**
     * Scope for offers eligible for MegaOferta
     */
    public function scopeEligibleForMegaOferta($query)
    {
        return $query->where('status', 'active')
                    ->where('mega_oferta_applied', false)
                    ->whereRaw('DATEDIFF(NOW(), created_at) >= 14')
                    ->whereRaw('DATEDIFF(NOW(), created_at) <= 21');
    }

    /**
     * Check if offer has flexible period
     */
    public function hasFlexiblePeriod(): bool
    {
        return $this->period_type === 'flexible';
    }

    /**
     * Check if offer is batch offer
     */
    public function isBatchOffer(): bool
    {
        return $this->is_batch_offer && !empty($this->batch_quota_ids);
    }

    /**
     * Get days until start date
     */
    public function getDaysUntilStart(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        return max(0, now()->diffInDays($this->start_date, false));
    }

    /**
     * Check if offer is eligible for auto discount (14 days rule)
     */
    public function isEligibleForAutoDiscount(): bool
    {
        if ($this->auto_discount_applied) {
            return false;
        }
        
        $daysUntilStart = $this->getDaysUntilStart();
        return $daysUntilStart <= 14 && $daysUntilStart > 0 && $this->status === 'active';
    }

    /**
     * Apply automatic discount (20% reduction)
     */
    public function applyAutoDiscount(): bool
    {
        if (!$this->isEligibleForAutoDiscount()) {
            return false;
        }

        $originalPrice = $this->original_price ?? $this->price;
        $discountPercentage = 20.0;
        $newPrice = $originalPrice * (1 - $discountPercentage / 100);

        $this->update([
            'auto_discount_applied' => true,
            'auto_discount_percentage' => $discountPercentage,
            'auto_discount_applied_at' => now(),
            'original_price' => $originalPrice,
            'price' => $newPrice,
        ]);

        return true;
    }

    /**
     * Mark as rented and move to metrics
     */
    public function markAsRented(string $metricsType = 'rented'): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => 'negotiated',
            'rented_at' => now(),
            'moved_to_metrics' => true,
            'metrics_type' => $metricsType,
        ]);

        // Update quota status
        if ($this->quota) {
            $this->quota->update(['status' => Quota::STATUS_RENTED]);
        }

        return true;
    }

    /**
     * Check if auction is configured properly
     */
    public function isAuctionConfigured(): bool
    {
        if (!$this->is_auction) {
            return false;
        }

        return !empty($this->auction_day) && 
               !empty($this->auction_start_hour) && 
               !empty($this->minimum_price) &&
               !empty($this->auction_duration_minutes);
    }

    /**
     * Get auction duration in hours
     */
    public function getAuctionDurationHours(): float
    {
        if (!$this->auction_duration_minutes) {
            return 0;
        }
        return $this->auction_duration_minutes / 60;
    }

    /**
     * Check if user can create auction based on profile type
     */
    public function canUserCreateAuction(User $user): bool
    {
        if (!$user->profile) {
            return false;
        }

        $profileType = $user->profile->profile_type;
        $config = $user->profile->getProfileConfig();

        // Tipo 1: 3 leilões por ano corrente
        if ($profileType === 'curioso') {
            $currentYearAuctions = Auction::where('user_id', $user->id)
                ->whereYear('created_at', now()->year)
                ->count();
            return $currentYearAuctions < 3;
        }

        // Tipo 2: 1 leilão por mês
        if ($profileType === 'inteligente') {
            $currentMonthAuctions = Auction::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            return $currentMonthAuctions < 1;
        }

        // Tipo 3: 2 leilões por mês
        if ($profileType === 'sabio') {
            $currentMonthAuctions = Auction::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            return $currentMonthAuctions < 2;
        }

        return false;
    }

    /**
     * Scope for offers with flexible period
     */
    public function scopeFlexiblePeriod($query)
    {
        return $query->where('period_type', 'flexible');
    }

    /**
     * Scope for offers with exact period
     */
    public function scopeExactPeriod($query)
    {
        return $query->where('period_type', 'exact');
    }

    /**
     * Scope for offers eligible for auto discount
     */
    public function scopeEligibleForAutoDiscount($query)
    {
        return $query->where('status', 'active')
                    ->where('auto_discount_applied', false)
                    ->whereRaw('DATEDIFF(start_date, NOW()) <= 14')
                    ->whereRaw('DATEDIFF(start_date, NOW()) > 0');
    }

    /**
     * Scope for offers by specific days (2, 3, 4, 5, 7)
     */
    public function scopeByDays($query, int $days)
    {
        return $query->where('number_of_days', $days);
    }

    /**
     * Scope for offers in a specific month
     */
    public function scopeByMonth($query, int $month, int $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereMonth('start_date', $month)
                    ->whereYear('start_date', $year);
    }

    /**
     * Status constants.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_NEGOTIATED = 'negotiated';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
}
