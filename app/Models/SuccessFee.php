<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuccessFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_type',
        'days',
        'fee_amount',
        'is_active',
        'order',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'fee_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'order' => 'integer',
            'days' => 'integer',
        ];
    }

    /**
     * Constantes para tipos de perfil
     */
    public const PROFILE_CURIOSO = 'curioso';
    public const PROFILE_INTELIGENTE = 'inteligente';
    public const PROFILE_SABIO = 'sabio';

    /**
     * Obter todos os tipos de perfil disponíveis
     */
    public static function getProfileTypes(): array
    {
        return [
            self::PROFILE_CURIOSO => 'Curioso',
            self::PROFILE_INTELIGENTE => 'Inteligente',
            self::PROFILE_SABIO => 'Sábio',
        ];
    }

    /**
     * Obter nome formatado do tipo de perfil
     */
    public function getProfileTypeNameAttribute(): string
    {
        return self::getProfileTypes()[$this->profile_type] ?? $this->profile_type;
    }

    /**
     * Scope para filtrar por tipo de perfil
     */
    public function scopeForProfileType($query, string $profileType)
    {
        return $query->where('profile_type', $profileType);
    }

    /**
     * Scope para taxas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por ordem e dias
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('days');
    }

    /**
     * Obter taxa por tipo de perfil e número de dias
     */
    public static function getFeeByProfileAndDays(string $profileType, int $days): ?self
    {
        return self::where('profile_type', $profileType)
            ->where('days', $days)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obter todas as taxas ativas para um tipo de perfil
     */
    public static function getActiveFeesForProfile(string $profileType): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('profile_type', $profileType)
            ->where('is_active', true)
            ->ordered()
            ->get();
    }

    /**
     * Calcular taxa de êxito para um perfil e número de dias
     */
    public static function calculateFee(string $profileType, int $days): float
    {
        $fee = self::getFeeByProfileAndDays($profileType, $days);
        return $fee ? (float) $fee->fee_amount : 0.00;
    }

    /**
     * Verificar se existe taxa para perfil e dias
     */
    public static function feeExists(string $profileType, int $days): bool
    {
        return self::where('profile_type', $profileType)
            ->where('days', $days)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Obter formato formatado do valor da taxa
     */
    public function getFormattedFeeAttribute(): string
    {
        return 'R$ ' . number_format($this->fee_amount, 2, ',', '.');
    }

    /**
     * Formatar valores de fracionamento baseado no tipo de fração
     * Ex: "2_2_3" -> "2 dias (R$50) + 2 dias (R$50) + 3 dias (R$70)"
     * 
     * @param string $profileType Tipo de perfil (curioso, inteligente, sabio)
     * @param string $fractionType Tipo de fração (ex: "2_2_3", "3_4", "2_5")
     * @return string String formatada com os valores
     */
    public static function formatFractionPrices(string $profileType, string $fractionType): string
    {
        // Dividir o tipo de fração em partes (ex: "2_2_3" -> [2, 2, 3])
        $days = explode('_', $fractionType);
        
        $parts = [];
        foreach ($days as $dayCount) {
            $dayCount = (int) $dayCount;
            $fee = self::getFeeByProfileAndDays($profileType, $dayCount);
            
            if ($fee) {
                $formattedPrice = 'R$' . number_format($fee->fee_amount, 0, ',', '.');
                $parts[] = "{$dayCount} dia" . ($dayCount > 1 ? 's' : '') . " ({$formattedPrice})";
            } else {
                // Se não encontrar a taxa, usar valor padrão ou mostrar como não disponível
                $parts[] = "{$dayCount} dia" . ($dayCount > 1 ? 's' : '') . " (N/A)";
            }
        }
        
        return implode(' + ', $parts);
    }
}