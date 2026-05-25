<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserProfile;

class DebugUserProfile extends Command
{
    protected $signature = 'debug:user-profile {email}';
    protected $description = 'Debug user profile to see what fields are missing';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuário não encontrado com email: {$email}");
            return Command::FAILURE;
        }
        
        $profile = $user->profile;
        
        if (!$profile) {
            $this->error("Perfil não encontrado para o usuário: {$email}");
            return Command::FAILURE;
        }
        
        $this->info("=== Debug do Perfil do Usuário: {$email} ===");
        $this->newLine();
        
        $requiredFields = [
            'full_name' => 'Nome Completo',
            'cpf' => 'CPF',
            'phone' => 'Telefone',
            'cep' => 'CEP',
            'street' => 'Rua',
            'neighborhood' => 'Bairro',
            'city' => 'Cidade',
            'state' => 'Estado',
            'house_number' => 'Número da Casa',
            'user_photo_path' => 'Foto do Usuário',
            'has_quota' => 'Informação sobre Cota',
            'profile_type' => 'Tipo de Perfil',
            'terms_accepted' => 'Termos Aceitos',
        ];
        
        $this->info("Campos Obrigatórios:");
        foreach ($requiredFields as $field => $label) {
            $value = $profile->$field;
            $isEmpty = in_array($field, ['has_quota']) 
                ? ($value === null) 
                : empty($value);
            
            $status = $isEmpty ? '❌ FALTANDO' : '✅ OK';
            $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : ($value ?? 'null');
            $this->line("  {$status} {$label}: {$displayValue}");
        }
        
        $this->newLine();
        $this->info("Documentos:");
        $hasRG = !empty($profile->rg_photo_path);
        $hasCNH = !empty($profile->cnh_photo_path);
        $this->line("  " . ($hasRG ? '✅' : '❌') . " RG: " . ($hasRG ? $profile->rg_photo_path : 'FALTANDO'));
        $this->line("  " . ($hasCNH ? '✅' : '❌') . " CNH: " . ($hasCNH ? $profile->cnh_photo_path : 'FALTANDO'));
        
        if (empty($profile->rg_photo_path) && empty($profile->cnh_photo_path)) {
            $this->error("  ❌ Nenhum documento (RG ou CNH) encontrado!");
        }
        
        $this->newLine();
        $hasQuotaValue = $profile->getRawOriginal('has_quota') ?? $profile->has_quota;
        $this->info("Has Quota (raw): " . var_export($hasQuotaValue, true));
        $this->info("Has Quota (casted): " . var_export($profile->has_quota, true));
        
        $hasQuota = $hasQuotaValue && $hasQuotaValue != '0' && $hasQuotaValue != 0 && $hasQuotaValue !== false;
        $this->info("Tem Cota (calculado): " . ($hasQuota ? 'SIM' : 'NÃO'));
        
        if ($hasQuota) {
            $hasContract = !empty($profile->quota_contract_photo_path) || 
                          (!empty($profile->quota_contracts) && is_array($profile->quota_contracts) && count($profile->quota_contracts) > 0);
            $this->line("  Contrato da Cota: " . ($hasContract ? '✅ OK' : '❌ FALTANDO'));
            if (!empty($profile->quota_contract_photo_path)) {
                $this->line("    - quota_contract_photo_path: {$profile->quota_contract_photo_path}");
            }
            if (!empty($profile->quota_contracts)) {
                $this->line("    - quota_contracts: " . json_encode($profile->quota_contracts));
            }
        }
        
        $this->newLine();
        $this->info("KYC Status:");
        $this->line("  kyc_completed: " . var_export($profile->kyc_completed, true));
        $this->line("  kyc_completed_at: " . ($profile->kyc_completed_at ? $profile->kyc_completed_at->format('Y-m-d H:i:s') : 'null'));
        $this->line("  kyc_status: " . ($profile->kyc_status ?? 'null'));
        
        $this->newLine();
        $this->info("CPF Validation:");
        $cpf = preg_replace('/[^0-9]/', '', $profile->cpf ?? '');
        $isValid = strlen($cpf) == 11 && !preg_match('/(\d)\1{10}/', $cpf);
        if ($isValid) {
            // Validar dígitos verificadores
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    $isValid = false;
                    break;
                }
            }
        }
        $this->line("  CPF: {$profile->cpf}");
        $this->line("  CPF (limpo): {$cpf}");
        $this->line("  Válido: " . ($isValid ? '✅ SIM' : '❌ NÃO'));
        
        return Command::SUCCESS;
    }
}
