<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Quota;
use App\Models\UserProfile;

class UpdateQuotaTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Atualizar todas as cotas
        // Se is_fractioned = true, definir como 'flexivel'
        // Se is_fractioned = false ou null, definir como 'fixa'
        $quotas = Quota::all();
        
        foreach ($quotas as $quota) {
            if ($quota->is_fractioned) {
                $quota->quota_type = 'flexivel';
            } else {
                $quota->quota_type = 'fixa';
            }
            $quota->save();
        }
        
        // Atualizar perfis de usuários
        // Para owner_quota_type e gestor_quota_type, usar a mesma lógica
        $profiles = UserProfile::all();
        
        foreach ($profiles as $profile) {
            // Atualizar owner_quota_type baseado no is_fractioned das cotas do usuário
            $userQuotas = Quota::where('user_id', $profile->user_id)->get();
            if ($userQuotas->isNotEmpty()) {
                $hasFractioned = $userQuotas->where('is_fractioned', true)->isNotEmpty();
                $hasNonFractioned = $userQuotas->where('is_fractioned', false)->isNotEmpty();
                
                if ($hasFractioned && $hasNonFractioned) {
                    $profile->owner_quota_type = 'fix_flexivel';
                } elseif ($hasFractioned) {
                    $profile->owner_quota_type = 'flexivel';
                } elseif ($hasNonFractioned) {
                    $profile->owner_quota_type = 'fixa';
                }
            }
            
            $profile->save();
        }
        
        $this->command->info('Tipo de cota atualizado para todas as cotas e perfis!');
    }
}
