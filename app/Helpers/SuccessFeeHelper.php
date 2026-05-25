<?php

if (!function_exists('format_success_fees_for_display')) {
    /**
     * Formatar taxas de êxito para exibição
     *
     * @param string $profileType Tipo de perfil (curioso, inteligente, sabio)
     * @return string String formatada com as taxas (ex: "R$65 / 2 dias, R$90 / 3 dias")
     */
    function format_success_fees_for_display(string $profileType): string
    {
        $fees = \App\Models\SuccessFee::getActiveFeesForProfile($profileType);
        
        if ($fees->isEmpty()) {
            return 'Taxas não disponíveis';
        }

        $formatted = [];
        foreach ($fees as $fee) {
            $formatted[] = $fee->formatted_fee . ' / ' . $fee->days . ' dia' . ($fee->days > 1 ? 's' : '');
        }

        return implode(', ', $formatted);
    }
}

if (!function_exists('get_success_fees_for_profile')) {
    /**
     * Obter taxas de êxito para um perfil
     *
     * @param string $profileType Tipo de perfil
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function get_success_fees_for_profile(string $profileType)
    {
        return \App\Models\SuccessFee::getActiveFeesForProfile($profileType);
    }
}
