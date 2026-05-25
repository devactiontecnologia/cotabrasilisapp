<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Quota;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deletar usuários existentes e suas dependências
        $user1 = User::where('email', 'cliente@cotasbrasilis.com')->first();
        $user2 = User::where('email', 'cliente2@cotasbrasilis.com')->first();
        
        if ($user1) {
            // Deletar cotas
            Quota::where('user_id', $user1->id)->delete();
            // Deletar perfil
            UserProfile::where('user_id', $user1->id)->delete();
            // Deletar usuário
            $user1->delete();
            $this->command->info('Usuário 1 e dependências deletados');
        }
        
        if ($user2) {
            // Deletar cotas
            Quota::where('user_id', $user2->id)->delete();
            // Deletar perfil
            UserProfile::where('user_id', $user2->id)->delete();
            // Deletar usuário
            $user2->delete();
            $this->command->info('Usuário 2 e dependências deletados');
        }
        
        // Criar primeiro usuário: cliente@cotasbrasilis.com
        $user1 = User::create([
            'name' => 'João Silva Santos',
            'email' => 'cliente@cotasbrasilis.com',
            'password' => Hash::make('12345678'),
            'whatsapp' => '11999999999',
            'ingress_date' => now(),
            'is_active' => true,
        ]);

        // Criar perfil do primeiro usuário
        $profile1 = UserProfile::create([
            'user_id' => $user1->id,
            'profile_type' => 'inteligente',
            'full_name' => 'João Silva Santos',
            'cpf' => '123.456.789-09', // CPF válido para testes
            'phone' => '11999999999',
            'cep' => '01310-100',
            'street' => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'house_number' => '1000',
            'has_quota' => true,
            'quota_status' => 'paid',
            'hotel_operational' => true,
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
            'kyc_status' => 'approved',
            'kyc_completed' => true,
            'kyc_completed_at' => now(),
            'user_photo_path' => 'demo/user_photo1.jpg',
            'cnh_photo_path' => 'demo/cnh_photo1.jpg',
            'rg_photo_path' => 'demo/rg_photo1.jpg',
            'quota_contract_photo_path' => 'demo/contract1.jpg',
        ]);

        // Criar primeira cota do primeiro usuário (aluguel, troca, venda e compra)
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => 'Hotel Demo 1',
            'location' => 'São Paulo, SP',
            'start_date' => Carbon::now()->addDays(30),
            'end_date' => Carbon::now()->addDays(36),
            'number_of_guests' => 4,
            'rental_price' => 1500.00,
            'is_exchange' => false,
            'observations' => 'Cota demonstrativa - permite aluguel, troca, venda e compra',
            'contract_photo_path' => 'demo/contract1.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'medium',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => now(),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
        ]);

        // Criar segunda cota do primeiro usuário (somente aluguel)
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => 'Hotel Demo 2',
            'location' => 'Rio de Janeiro, RJ',
            'start_date' => Carbon::now()->addDays(45),
            'end_date' => Carbon::now()->addDays(51),
            'number_of_guests' => 2,
            'rental_price' => 1200.00,
            'is_exchange' => false,
            'observations' => 'Cota demonstrativa - somente aluguel',
            'contract_photo_path' => 'demo/contract2.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'medium',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => now(),
            'quota_status' => 'active',
            'allowed_uses' => ['rent'],
        ]);

        // Criar segundo usuário: cliente2@cotasbrasilis.com
        $user2 = User::create([
            'name' => 'Maria Oliveira Costa',
            'email' => 'cliente2@cotasbrasilis.com',
            'password' => Hash::make('12345678'),
            'whatsapp' => '11888888888',
            'ingress_date' => now(),
            'is_active' => true,
        ]);

        // Criar perfil do segundo usuário
        $profile2 = UserProfile::create([
            'user_id' => $user2->id,
            'profile_type' => 'inteligente',
            'full_name' => 'Maria Oliveira Costa',
            'cpf' => '987.654.321-00', // CPF válido para testes
            'phone' => '11888888888',
            'cep' => '20040-020',
            'street' => 'Avenida Atlântica',
            'neighborhood' => 'Copacabana',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'house_number' => '2000',
            'has_quota' => true,
            'quota_status' => 'paid',
            'hotel_operational' => true,
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
            'kyc_status' => 'approved',
            'kyc_completed' => true,
            'kyc_completed_at' => now(),
            'user_photo_path' => 'demo/user_photo2.jpg',
            'cnh_photo_path' => 'demo/cnh_photo2.jpg',
            'rg_photo_path' => 'demo/rg_photo2.jpg',
            'quota_contract_photo_path' => 'demo/contract3.jpg',
        ]);

        // Criar primeira cota do segundo usuário (aluguel, troca, venda e compra)
        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => 'Hotel Demo 3',
            'location' => 'Florianópolis, SC',
            'start_date' => Carbon::now()->addDays(60),
            'end_date' => Carbon::now()->addDays(66),
            'number_of_guests' => 3,
            'rental_price' => 1800.00,
            'is_exchange' => false,
            'observations' => 'Cota demonstrativa - permite aluguel, troca, venda e compra',
            'contract_photo_path' => 'demo/contract3.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'high',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => now(),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
        ]);

        // Criar segunda cota do segundo usuário (somente aluguel)
        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => 'Hotel Demo 4',
            'location' => 'Salvador, BA',
            'start_date' => Carbon::now()->addDays(75),
            'end_date' => Carbon::now()->addDays(81),
            'number_of_guests' => 2,
            'rental_price' => 1000.00,
            'is_exchange' => false,
            'observations' => 'Cota demonstrativa - somente aluguel',
            'contract_photo_path' => 'demo/contract4.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'medium',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => now(),
            'quota_status' => 'active',
            'allowed_uses' => ['rent'],
        ]);

        // Criar cotas novas (últimos 7 dias) - para aparecer em "Cotas Novas"
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => 'Hotel Demo 1',
            'location' => 'São Paulo, SP',
            'start_date' => Carbon::now()->addDays(15),
            'end_date' => Carbon::now()->addDays(21),
            'number_of_guests' => 3,
            'rental_price' => 2000.00,
            'is_exchange' => false,
            'observations' => 'Cota nova - criada recentemente',
            'contract_photo_path' => 'demo/contract1.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'high',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(3), // Criada há 3 dias
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange'],
            'created_at' => Carbon::now()->subDays(3),
        ]);

        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => 'Hotel Demo 3',
            'location' => 'Florianópolis, SC',
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(26),
            'number_of_guests' => 4,
            'rental_price' => 2200.00,
            'is_exchange' => false,
            'observations' => 'Cota nova - criada recentemente',
            'contract_photo_path' => 'demo/contract3.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'high',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(5), // Criada há 5 dias
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell'],
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // Criar cotas antigas (mais de 30 dias) - para aparecer em "Mais Cotas"
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => 'Hotel Demo 2',
            'location' => 'Rio de Janeiro, RJ',
            'start_date' => Carbon::now()->addDays(90),
            'end_date' => Carbon::now()->addDays(96),
            'number_of_guests' => 2,
            'rental_price' => 1300.00,
            'is_exchange' => false,
            'observations' => 'Cota antiga - disponível há mais tempo',
            'contract_photo_path' => 'demo/contract2.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'medium',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(45), // Criada há 45 dias
            'quota_status' => 'active',
            'allowed_uses' => ['rent'],
            'created_at' => Carbon::now()->subDays(45),
        ]);

        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => 'Hotel Demo 4',
            'location' => 'Salvador, BA',
            'start_date' => Carbon::now()->addDays(100),
            'end_date' => Carbon::now()->addDays(106),
            'number_of_guests' => 3,
            'rental_price' => 1100.00,
            'is_exchange' => false,
            'observations' => 'Cota antiga - disponível há mais tempo',
            'contract_photo_path' => 'demo/contract4.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'low',
            'payment_status' => 'paid',
            'is_owner' => true,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(60), // Criada há 60 dias
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange'],
            'created_at' => Carbon::now()->subDays(60),
        ]);

        $this->command->info('Contas e cotas demonstrativas criadas com sucesso!');
        $this->command->info('Email 1: cliente@cotasbrasilis.com | Senha: 12345678');
        $this->command->info('Email 2: cliente2@cotasbrasilis.com | Senha: 12345678');
    }
}
