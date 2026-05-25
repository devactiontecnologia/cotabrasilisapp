<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se já existem hotéis
        if (Hotel::count() == 0) {
            // Criar hotel de exemplo
            Hotel::create([
                'name' => 'Hotel Exemplo',
                'location' => 'São Paulo, SP',
                'city' => 'São Paulo',
                'state' => 'SP',
                'address' => 'Rua das Flores, 123',
                'zip_code' => '01234-567',
                'phone' => '(11) 1234-5678',
                'email' => 'contato@hotelexemplo.com.br',
                'description' => 'Hotel de exemplo para demonstração do sistema',
                'stars' => 4,
                'is_active' => true,
                'is_functioning' => true,
            ]);

            // Criar mais alguns hotéis de exemplo
            Hotel::create([
                'name' => 'Resort Praia Dourada',
                'location' => 'Rio de Janeiro, RJ',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'address' => 'Av. Beira Mar, 456',
                'zip_code' => '22000-000',
                'phone' => '(21) 9876-5432',
                'email' => 'reservas@praiadourada.com.br',
                'description' => 'Resort à beira-mar com vista para o oceano',
                'stars' => 5,
                'is_active' => true,
                'is_functioning' => true,
            ]);

            Hotel::create([
                'name' => 'Pousada Serra Verde',
                'location' => 'Campos do Jordão, SP',
                'city' => 'Campos do Jordão',
                'state' => 'SP',
                'address' => 'Rua da Montanha, 789',
                'zip_code' => '12460-000',
                'phone' => '(12) 3456-7890',
                'email' => 'contato@serraverde.com.br',
                'description' => 'Pousada aconchegante na serra paulista',
                'stars' => 3,
                'is_active' => true,
                'is_functioning' => true,
            ]);

            Hotel::create([
                'name' => 'Hotel Business Center',
                'location' => 'Belo Horizonte, MG',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'address' => 'Av. Afonso Pena, 1000',
                'zip_code' => '30130-000',
                'phone' => '(31) 2345-6789',
                'email' => 'reservas@businesscenter.com.br',
                'description' => 'Hotel executivo no centro da cidade',
                'stars' => 4,
                'is_active' => true,
                'is_functioning' => true,
            ]);

            Hotel::create([
                'name' => 'Resort Amazônia',
                'location' => 'Manaus, AM',
                'city' => 'Manaus',
                'state' => 'AM',
                'address' => 'Rodovia AM-010, Km 15',
                'zip_code' => '69000-000',
                'phone' => '(92) 3456-7890',
                'email' => 'contato@amazoniaresort.com.br',
                'description' => 'Resort ecológico na floresta amazônica',
                'stars' => 5,
                'is_active' => true,
                'is_functioning' => true,
            ]);

            $this->command->info('Hotéis de exemplo criados com sucesso!');
        } else {
            $this->command->info('Hotéis já existem no banco de dados.');
        }
    }
}
