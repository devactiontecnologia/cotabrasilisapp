<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quota;
use Carbon\Carbon;

class FillMissingQuotaDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quotas = Quota::all();
        
        $this->command->info("Processando " . $quotas->count() . " cotas...");
        
        $updated = 0;
        
        foreach ($quotas as $quota) {
            $needsUpdate = false;
            
            // Calcular semanas baseado nas datas (sempre recalcular para garantir)
            if ($quota->start_date && $quota->end_date) {
                $days = $quota->start_date->diffInDays($quota->end_date) + 1;
                $calculatedWeeks = max(1, ceil($days / 7));
                if ($quota->weeks != $calculatedWeeks) {
                    $quota->weeks = $calculatedWeeks;
                    $needsUpdate = true;
                }
            } elseif ($quota->weeks === null || $quota->weeks == 0) {
                $quota->weeks = 1;
                $needsUpdate = true;
            }
            
            // Número de quartos (padrão: 1 se não tiver)
            if ($quota->number_of_rooms === null || $quota->number_of_rooms == 0) {
                $quota->number_of_rooms = 1;
                $needsUpdate = true;
            }
            
            // Sazonalidade baseada no mês (sempre recalcular)
            $month = $quota->start_date ? $quota->start_date->month : now()->month;
            $calculatedSeasonality = 'medium'; // padrão
            
            // Pico: Dezembro e Janeiro
            if (in_array($month, [12, 1])) {
                $calculatedSeasonality = 'peak';
            }
            // Alta temporada: Fevereiro, Julho
            elseif (in_array($month, [2, 7])) {
                $calculatedSeasonality = 'high';
            }
            // Média: Março, Abril, Maio, Agosto, Setembro
            elseif (in_array($month, [3, 4, 5, 8, 9])) {
                $calculatedSeasonality = 'medium';
            }
            // Baixa: Outubro, Novembro, Junho
            else {
                $calculatedSeasonality = 'low';
            }
            
            if ($quota->seasonality === null || $quota->seasonality === '' || $quota->seasonality != $calculatedSeasonality) {
                $quota->seasonality = $calculatedSeasonality;
                $needsUpdate = true;
            }
            
            // Status de pagamento (padrão: unpaid se não tiver)
            if ($quota->payment_status === null || $quota->payment_status === '') {
                $quota->payment_status = 'unpaid';
                $needsUpdate = true;
            }
            
            // Status da cota (sempre definir baseado no status)
            $calculatedQuotaStatus = 'active';
            if ($quota->status === 'cancelled') {
                $calculatedQuotaStatus = 'inactive';
            } else {
                $calculatedQuotaStatus = 'active';
            }
            
            if ($quota->quota_status === null || $quota->quota_status === '' || $quota->quota_status != $calculatedQuotaStatus) {
                $quota->quota_status = $calculatedQuotaStatus;
                $needsUpdate = true;
            }
            
            // is_owner (padrão: true se não tiver)
            if ($quota->is_owner === null) {
                $quota->is_owner = true;
                $needsUpdate = true;
            }
            
            // is_published (padrão: false se não tiver)
            if ($quota->is_published === null) {
                $quota->is_published = false;
                $needsUpdate = true;
            }
            
            // is_fractioned - calcular baseado em fraction_details
            $calculatedIsFractioned = false;
            if ($quota->fraction_details && is_array($quota->fraction_details) && count($quota->fraction_details) > 0) {
                $calculatedIsFractioned = true;
            }
            
            if ($quota->is_fractioned === null || $quota->is_fractioned != $calculatedIsFractioned) {
                $quota->is_fractioned = $calculatedIsFractioned;
                $needsUpdate = true;
            }
            
            // number_of_guests - se não tiver, usar valor padrão de 4
            if ($quota->number_of_guests === null || $quota->number_of_guests == 0) {
                $quota->number_of_guests = 4;
                $needsUpdate = true;
            }
            
            if ($needsUpdate) {
                $quota->save();
                $updated++;
                $this->command->info("Cota ID {$quota->id} atualizada: Hotel: {$quota->hotel_name}, Semanas: {$quota->weeks}, Quartos: {$quota->number_of_rooms}, Sazonalidade: {$quota->seasonality}, Status: {$quota->quota_status}");
            }
        }
        
        $this->command->info("\n✅ Processamento concluído!");
        $this->command->info("Total de cotas atualizadas: {$updated}");
        $this->command->info("Total de cotas processadas: " . $quotas->count());
    }
}

