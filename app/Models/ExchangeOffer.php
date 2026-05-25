<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ExchangeOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quota_id',
        'exchange_type',
        'desired_city',
        'desired_cities',
        'desired_period_start',
        'desired_period_end',
        'desired_period_month',
        'desired_period_year',
        'desired_hotel',
        'desired_hotels',
        'promotion_cities',
        'desired_people',
        'desired_rooms',
        'price_range_min',
        'price_range_max',
        'exchange_mode',
        'complement_trade_type',
        'additional_value',
        'days_difference',
        'observations',
        'nights_plus_money',
        'status',
        'validity_until',
        'selected_options',
        'max_options',
    ];

    protected function casts(): array
    {
        return [
            'desired_period_start' => 'date',
            'desired_period_end' => 'date',
            'price_range_min' => 'decimal:2',
            'price_range_max' => 'decimal:2',
            'additional_value' => 'decimal:2',
            'selected_options' => 'array',
            'desired_cities' => 'array',
            'desired_hotels' => 'array',
            'promotion_cities' => 'array',
            'validity_until' => 'datetime',
        ];
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quota
     */
    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }

    /**
     * Check if offer is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->validity_until || $this->validity_until > now());
    }

    /**
     * Check if offer is expired
     */
    public function isExpired(): bool
    {
        return $this->validity_until && $this->validity_until <= now();
    }

    /**
     * Get max options based on user profile type
     */
    public static function getMaxOptionsByProfileType(string $profileType): int
    {
        return match($profileType) {
            'curioso' => 3,
            'inteligente' => 5,
            'sabio' => 10,
            default => 3,
        };
    }

    /**
     * Get validity hours based on user profile type
     */
    public static function getValidityHoursByProfileType(string $profileType): int
    {
        return match($profileType) {
            'curioso' => 48,
            'inteligente' => 48,
            'sabio' => 72,
            default => 48,
        };
    }

    /**
     * Set validity based on profile type
     */
    /**
     * Lista de nomes de cidade para critérios (novo JSON ou legado em desired_city).
     *
     * @return list<string>
     */
    public function getDesiredCitiesList(): array
    {
        $fromJson = $this->desired_cities;
        if (is_array($fromJson) && count($fromJson) > 0) {
            return array_values(array_unique(array_filter(array_map('strval', $fromJson))));
        }
        if ($this->desired_city) {
            return array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', (string) $this->desired_city))));
        }

        return [];
    }

    /**
     * Cidades escolhidas para informe / disparo de alertas (quando preenchido).
     *
     * @return list<string>
     */
    public function getPromotionCitiesList(): array
    {
        $raw = $this->promotion_cities;
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('strval', $raw))));
    }

    /**
     * Cidades usadas para filtrar destinatários de alertas de troca (informe ou critérios desejados).
     *
     * @return list<string>
     */
    public function getCitiesForExchangeAlerts(): array
    {
        $promo = $this->getPromotionCitiesList();
        if ($promo !== []) {
            return CidadeCapital::locationTermsForPromotionValues($promo);
        }

        return $this->getDesiredCitiesList();
    }

    public function getDesiredCitiesLabelsAttribute(): string
    {
        $list = $this->getDesiredCitiesList();

        return $list === [] ? '' : implode(', ', $list);
    }

    /**
     * Lista de nomes de hotel para critérios (JSON ou legado em desired_hotel).
     *
     * @return list<string>
     */
    public function getDesiredHotelsList(): array
    {
        $fromJson = $this->desired_hotels;
        if (is_array($fromJson) && count($fromJson) > 0) {
            return array_values(array_unique(array_filter(array_map('strval', $fromJson))));
        }
        if ($this->desired_hotel) {
            return [trim((string) $this->desired_hotel)];
        }

        return [];
    }

    public function getDesiredHotelsLabelsAttribute(): string
    {
        $list = $this->getDesiredHotelsList();

        return $list === [] ? '' : implode(', ', $list);
    }

    /**
     * Texto amigável do período desejado (dias específicos ou apenas mês/ano).
     */
    public function getDaysDifferenceLabel(): ?string
    {
        if ($this->days_difference === null) {
            return null;
        }

        $n = abs((int) $this->days_difference);
        if ($n <= 0) {
            return null;
        }

        if ((int) $this->days_difference > 0) {
            return "Solicita +{$n} diária(s) além do período informado";
        }

        return "Oferece +{$n} diária(s) além do período informado na troca";
    }

    public function getDesiredPeriodLabel(): ?string
    {
        if ($this->desired_period_start && $this->desired_period_end) {
            $start = $this->desired_period_start instanceof Carbon
                ? $this->desired_period_start
                : Carbon::parse($this->desired_period_start);
            $end = $this->desired_period_end instanceof Carbon
                ? $this->desired_period_end
                : Carbon::parse($this->desired_period_end);

            return $start->format('d/m/Y') . ' até ' . $end->format('d/m/Y');
        }

        $month = (int) ($this->desired_period_month ?? 0);
        $year = (int) ($this->desired_period_year ?? 0);
        if ($month >= 1 && $month <= 12 && $year > 0) {
            return sprintf('%02d/%d', $month, $year);
        }

        return null;
    }

    public function setValidityByProfileType(string $profileType): void
    {
        $hours = self::getValidityHoursByProfileType($profileType);
        $this->update([
            'validity_until' => now()->addHours($hours),
            'max_options' => self::getMaxOptionsByProfileType($profileType),
        ]);
    }

    /**
     * Check if can add more options
     */
    public function canAddOption(): bool
    {
        $currentOptions = $this->selected_options ?? [];
        return count($currentOptions) < $this->max_options;
    }

    /**
     * Add selected option
     */
    public function addOption(array $option): bool
    {
        if (!$this->canAddOption()) {
            return false;
        }

        $options = $this->selected_options ?? [];
        $options[] = $option;
        
        $this->update(['selected_options' => $options]);
        return true;
    }
}
