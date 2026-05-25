<?php

namespace App\Services;

use App\Models\SuccessFee;
use App\Models\UserProfile;

class SuccessFeeService
{
    /**
     * Calcular taxa de êxito para um perfil e número de dias
     *
     * @param string $profileType Tipo de perfil (curioso, inteligente, sabio)
     * @param int $days Número de dias do fracionamento
     * @return float Valor da taxa de êxito em reais
     */
    public function calculateFee(string $profileType, int $days): float
    {
        $fee = SuccessFee::getFeeByProfileAndDays($profileType, $days);
        return $fee ? (float) $fee->fee_amount : 0.00;
    }

    /**
     * Obter todas as taxas ativas para um tipo de perfil
     *
     * @param string $profileType Tipo de perfil
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveFeesForProfile(string $profileType)
    {
        return SuccessFee::getActiveFeesForProfile($profileType);
    }

    /**
     * Obter taxas formatadas para exibição (para uso em selects, etc)
     *
     * @param string $profileType Tipo de perfil
     * @return array Array associativo com 'days' => 'formatted_fee'
     */
    public function getFormattedFeesForProfile(string $profileType): array
    {
        $fees = $this->getActiveFeesForProfile($profileType);
        $formatted = [];

        foreach ($fees as $fee) {
            $formatted[$fee->days] = [
                'id' => $fee->id,
                'days' => $fee->days,
                'fee_amount' => (float) $fee->fee_amount,
                'formatted_fee' => $fee->formatted_fee,
                'description' => $fee->description,
            ];
        }

        return $formatted;
    }

    /**
     * Verificar se existe taxa para perfil e dias
     *
     * @param string $profileType Tipo de perfil
     * @param int $days Número de dias
     * @return bool
     */
    public function feeExists(string $profileType, int $days): bool
    {
        return SuccessFee::feeExists($profileType, $days);
    }

    /**
     * Calcular taxa de êxito baseado no perfil do usuário
     *
     * @param UserProfile $userProfile Perfil do usuário
     * @param int $days Número de dias
     * @return float Valor da taxa de êxito
     */
    public function calculateFeeForUserProfile(UserProfile $userProfile, int $days): float
    {
        return $this->calculateFee($userProfile->profile_type, $days);
    }

    /**
     * Obter taxas formatadas para um UserProfile
     *
     * @param UserProfile $userProfile Perfil do usuário
     * @return array Array de taxas formatadas
     */
    public function getFeesForUserProfile(UserProfile $userProfile): array
    {
        return $this->getFormattedFeesForProfile($userProfile->profile_type);
    }

    /**
     * Validar se o número de dias é válido para o perfil
     *
     * @param string $profileType Tipo de perfil
     * @param int $days Número de dias
     * @return bool
     */
    public function isValidDaysForProfile(string $profileType, int $days): bool
    {
        return $this->feeExists($profileType, $days);
    }

    /**
     * Obter todas as taxas agrupadas por perfil
     *
     * @return array Array associativo ['profile_type' => [fees]]
     */
    public function getAllFeesGroupedByProfile(): array
    {
        $allFees = SuccessFee::active()->ordered()->get();
        $grouped = [];

        foreach ($allFees as $fee) {
            if (!isset($grouped[$fee->profile_type])) {
                $grouped[$fee->profile_type] = [];
            }
            $grouped[$fee->profile_type][] = $fee;
        }

        return $grouped;
    }

    /**
     * Obter resumo de taxas para dashboard/admin
     *
     * @return array Estatísticas das taxas
     */
    public function getFeesSummary(): array
    {
        $totalFees = SuccessFee::count();
        $activeFees = SuccessFee::active()->count();
        $inactiveFees = $totalFees - $activeFees;
        
        $feesByProfile = SuccessFee::selectRaw('profile_type, COUNT(*) as count')
            ->groupBy('profile_type')
            ->get()
            ->pluck('count', 'profile_type')
            ->toArray();

        return [
            'total' => $totalFees,
            'active' => $activeFees,
            'inactive' => $inactiveFees,
            'by_profile' => $feesByProfile,
        ];
    }
}
