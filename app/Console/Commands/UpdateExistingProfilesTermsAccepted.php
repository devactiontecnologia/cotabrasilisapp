<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserProfile;

class UpdateExistingProfilesTermsAccepted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:update-terms-accepted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing profiles to set terms_accepted to true if profile is complete';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating profiles...');
        
        // Update profiles that have all required fields but terms_accepted is false or null
        $profiles = UserProfile::where(function($query) {
            $query->where('terms_accepted', false)
                  ->orWhereNull('terms_accepted');
        })
        ->whereNotNull('full_name')
        ->whereNotNull('phone')
        ->whereNotNull('cep')
        ->whereNotNull('street')
        ->whereNotNull('neighborhood')
        ->whereNotNull('city')
        ->whereNotNull('state')
        ->whereNotNull('house_number')
        ->get();
        
        $count = 0;
        foreach ($profiles as $profile) {
            $profile->terms_accepted = true;
            $profile->terms_accepted_at = $profile->terms_accepted_at ?? now();
            $profile->save();
            $count++;
        }
        
        $this->info("Updated {$count} profiles.");
        
        return Command::SUCCESS;
    }
}
