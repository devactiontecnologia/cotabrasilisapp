<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserProfile;

class UpdateExistingProfilesKYC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:update-kyc-fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing profiles to set kyc_completed, kyc_completed_at, and kyc_status fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating profiles KYC fields...');
        
        // Atualizar perfis que têm todos os documentos mas não têm os campos KYC preenchidos
        $profiles = UserProfile::where(function($query) {
            $query->where('kyc_completed', false)
                  ->orWhereNull('kyc_completed')
                  ->orWhereNull('kyc_completed_at');
        })
        ->whereNotNull('user_photo_path')
        ->where(function($query) {
            $query->whereNotNull('rg_photo_path')
                  ->orWhereNotNull('cnh_photo_path');
        })
        ->get();
        
        $count = 0;
        foreach ($profiles as $profile) {
            // Verificar se o perfil está completo (tem todos os documentos necessários)
            $hasDocuments = !empty($profile->user_photo_path) && 
                          (!empty($profile->rg_photo_path) || !empty($profile->cnh_photo_path));
            
            if ($hasDocuments) {
                $profile->kyc_completed = true;
                $profile->kyc_completed_at = $profile->kyc_completed_at ?? $profile->created_at ?? now();
                // Sempre definir como 'approved' quando o cadastro estiver completo
                $profile->kyc_status = 'approved';
                $profile->save();
                $count++;
            }
        }
        
        // Também atualizar perfis que já têm kyc_completed = true mas kyc_status ainda está como 'pending'
        $pendingProfiles = UserProfile::where('kyc_completed', true)
            ->where(function($query) {
                $query->where('kyc_status', 'pending')
                      ->orWhereNull('kyc_status');
            })
            ->get();
        
        $pendingCount = 0;
        foreach ($pendingProfiles as $profile) {
            $profile->kyc_status = 'approved';
            $profile->save();
            $pendingCount++;
        }
        
        if ($pendingCount > 0) {
            $this->info("Updated {$pendingCount} profiles with kyc_completed=true to kyc_status='approved'.");
        }
        
        $this->info("Updated {$count} profiles with KYC fields.");
        
        return Command::SUCCESS;
    }
}
