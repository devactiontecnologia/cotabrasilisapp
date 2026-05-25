<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@cotasbrasilis.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'whatsapp' => '(11) 99999-9999',
                'role' => 'admin',
                'is_admin' => true,
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        // Update admin user to ensure correct role
        $admin->update([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        // Create admin profile if not exists
        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'profile_type' => 'sabio',
                'full_name' => 'Administrador',
                'cpf' => '000.000.000-00',
                'phone' => '(11) 99999-9999',
                'street' => 'Endereço Administrativo',
                'city' => 'São Paulo',
                'state' => 'SP',
                'quota_paid_off' => true,
                'hotel_operational' => true,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'has_quota' => true,
            ]
        );

        // Create moderator user if not exists
        $moderator = User::firstOrCreate(
            ['email' => 'moderador@cotasbrasilis.com'],
            [
                'name' => 'Moderador',
                'password' => Hash::make('moderador123'),
                'whatsapp' => '(11) 88888-8888',
                'role' => 'moderator',
                'is_admin' => false,
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        // Update moderator user to ensure correct role
        $moderator->update([
            'role' => 'moderator',
            'is_admin' => false,
        ]);

        // Create moderator profile if not exists
        UserProfile::firstOrCreate(
            ['user_id' => $moderator->id],
            [
                'profile_type' => 'inteligente',
                'full_name' => 'Moderador',
                'cpf' => '111.111.111-11',
                'phone' => '(11) 88888-8888',
                'street' => 'Endereço Moderador',
                'city' => 'São Paulo',
                'state' => 'SP',
                'quota_paid_off' => true,
                'hotel_operational' => true,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'has_quota' => false,
            ]
        );

        $this->command->info('Usuários administrativos configurados:');
        $this->command->info('Admin: admin@cotasbrasilis.com / admin123');
        $this->command->info('Moderador: moderador@cotasbrasilis.com / moderador123');
    }
}
