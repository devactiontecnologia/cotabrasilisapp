<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use Illuminate\Support\Facades\Storage;

class AddHotelImagesSeeder extends Seeder
{
    /**
     * Imagens disponíveis em public/images/exemplos/
     */
    private function getAvailableImages(): array
    {
        return [
            'public/images/exemplos/Amazônia.jpg',
            'public/images/exemplos/Bahia.jpg',
            'public/images/exemplos/Bahia1.jpg',
            'public/images/exemplos/Beach Park Ceará.webp',
            'public/images/exemplos/Beach Park Ceará1.jpg',
            'public/images/exemplos/Bonito.jpg',
            'public/images/exemplos/Bonito1.jpg',
            'public/images/exemplos/Bonito2.webp',
            'public/images/exemplos/Búzios.jpg',
            'public/images/exemplos/Búzios1.jpg',
            'public/images/exemplos/Caldas Novas.jpg',
            'public/images/exemplos/Caldas Novas1.jpg',
            'public/images/exemplos/Fernando de Noronha.webp',
            'public/images/exemplos/Fernando de Noronha1.jpg',
            'public/images/exemplos/Fernando de Noronha2.jpg',
            'public/images/exemplos/Foz do Iguaçú.jpg',
            'public/images/exemplos/Foz do Iguaçú1.jpg',
            'public/images/exemplos/Goiás. Chapada dos Veadeiros.jpg',
            'public/images/exemplos/Goiás.jpg',
            'public/images/exemplos/Gramado.webp',
            'public/images/exemplos/Gramado1.webp',
            'public/images/exemplos/Jericoacoara.webp',
            'public/images/exemplos/Jericoacoara1.jpg',
            'public/images/exemplos/Lagoa do Paraíso.jpg',
            'public/images/exemplos/Lençóis Maranhenses.jpg',
            'public/images/exemplos/Lençóis Maranhenses1.jpg',
            'public/images/exemplos/Manaus.webp',
            'public/images/exemplos/Muro Alto.jpg',
            'public/images/exemplos/Olinda.jpg',
            'public/images/exemplos/Olinda1.jpg',
            'public/images/exemplos/Pantanal.webp',
            'public/images/exemplos/Pantanal1.jpg',
            'public/images/exemplos/Parque Beto Carrero .jpg',
            'public/images/exemplos/Piauí.jpg',
            'public/images/exemplos/Preá, Fortaleza.jpg',
            'public/images/exemplos/Recife.jpg',
            'public/images/exemplos/RECIFE1.webp',
            'public/images/exemplos/Recife2.webp',
            'public/images/exemplos/Recife3.jpeg',
            'public/images/exemplos/Rio de Janeiro.jpg',
            'public/images/exemplos/Salinópolis.jpg',
            'public/images/exemplos/Santa Catarina.webp',
            'public/images/exemplos/São Paolo.jpg',
            'public/images/exemplos/São Paolo1.webp',
        ];
    }

    /**
     * Imagens de hotéis em storage/app/public/hotels/
     */
    private function getHotelStorageImages(): array
    {
        return [
            'hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg',
            'hotels/Xjjavf44CVgTOOClLkIOVRoGK1KnZkpDOY0wB9C3.jpg',
            'hotels/z9c0i6Xyatm4yiDRBt8JtnVLyHg8WRYd0PctfRN3.jpg',
        ];
    }

    /**
     * Mapear imagens por região/estado
     */
    private function getImagesByRegion(): array
    {
        return [
            // Norte
            'AC' => ['Amazônia.jpg', 'Manaus.webp'],
            'AM' => ['Amazônia.jpg', 'Manaus.webp'],
            'AP' => ['Amazônia.jpg'],
            'PA' => ['Amazônia.jpg', 'Salinópolis.jpg'],
            'RO' => ['Amazônia.jpg'],
            'RR' => ['Amazônia.jpg'],
            'TO' => ['Amazônia.jpg'],
            
            // Nordeste
            'AL' => ['Bahia.jpg', 'Bahia1.jpg', 'Muro Alto.jpg'],
            'BA' => ['Bahia.jpg', 'Bahia1.jpg', 'Muro Alto.jpg', 'Lençóis Maranhenses.jpg', 'Lençóis Maranhenses1.jpg'],
            'CE' => ['Beach Park Ceará.webp', 'Beach Park Ceará1.jpg', 'Jericoacoara.webp', 'Jericoacoara1.jpg', 'Preá, Fortaleza.jpg'],
            'MA' => ['Lençóis Maranhenses.jpg', 'Lençóis Maranhenses1.jpg'],
            'PB' => ['Bahia.jpg', 'Bahia1.jpg', 'Muro Alto.jpg'],
            'PE' => ['Recife.jpg', 'RECIFE1.webp', 'Recife2.webp', 'Recife3.jpeg', 'Olinda.jpg', 'Olinda1.jpg', 'Muro Alto.jpg'],
            'PI' => ['Piauí.jpg', 'Lençóis Maranhenses.jpg'],
            'RN' => ['Bahia.jpg', 'Bahia1.jpg', 'Muro Alto.jpg'],
            'SE' => ['Bahia.jpg', 'Bahia1.jpg'],
            
            // Centro-Oeste
            'DF' => ['Goiás.jpg', 'Goiás. Chapada dos Veadeiros.jpg'],
            'GO' => ['Goiás.jpg', 'Goiás. Chapada dos Veadeiros.jpg', 'Caldas Novas.jpg', 'Caldas Novas1.jpg'],
            'MT' => ['Pantanal.webp', 'Pantanal1.jpg'],
            'MS' => ['Pantanal.webp', 'Pantanal1.jpg', 'Bonito.jpg', 'Bonito1.jpg', 'Bonito2.webp'],
            
            // Sudeste
            'ES' => ['Rio de Janeiro.jpg', 'Búzios.jpg', 'Búzios1.jpg'],
            'MG' => ['Goiás.jpg', 'Goiás. Chapada dos Veadeiros.jpg'],
            'RJ' => ['Rio de Janeiro.jpg', 'Búzios.jpg', 'Búzios1.jpg'],
            'SP' => ['São Paolo.jpg', 'São Paolo1.webp'],
            
            // Sul
            'PR' => ['Foz do Iguaçú.jpg', 'Foz do Iguaçú1.jpg', 'Parque Beto Carrero .jpg'],
            'RS' => ['Gramado.webp', 'Gramado1.webp'],
            'SC' => ['Santa Catarina.webp', 'Gramado.webp', 'Gramado1.webp', 'Parque Beto Carrero .jpg'],
        ];
    }

    /**
     * Copiar imagem de public para storage
     */
    private function copyImageToStorage(string $sourcePath, string $destinationPath): ?string
    {
        $fullSourcePath = base_path($sourcePath);
        
        if (!file_exists($fullSourcePath)) {
            return null;
        }

        // Criar diretório se não existir
        $destinationDir = dirname(storage_path('app/public/' . $destinationPath));
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // Copiar arquivo
        $fullDestinationPath = storage_path('app/public/' . $destinationPath);
        if (copy($fullSourcePath, $fullDestinationPath)) {
            return $destinationPath;
        }

        return null;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();
        
        if ($hotels->isEmpty()) {
            $this->command->error('Nenhum hotel encontrado. Execute os seeders de hotéis primeiro.');
            return;
        }

        $imagesByRegion = $this->getImagesByRegion();
        $hotelStorageImages = $this->getHotelStorageImages();
        $allAvailableImages = $this->getAvailableImages();
        
        $updated = 0;
        $imageIndex = 0;

        foreach ($hotels as $hotel) {
            $images = [];
            
            // Primeiro, tentar usar imagens específicas da região do hotel
            $state = strtoupper($hotel->state ?? '');
            if (isset($imagesByRegion[$state])) {
                $regionImages = $imagesByRegion[$state];
                foreach ($regionImages as $imgName) {
                    $sourcePath = 'public/images/exemplos/' . $imgName;
                    $destinationPath = 'hotels/' . uniqid() . '_' . $imgName;
                    
                    $copiedPath = $this->copyImageToStorage($sourcePath, $destinationPath);
                    if ($copiedPath) {
                        $images[] = $copiedPath;
                        if (count($images) >= 3) {
                            break; // Limitar a 3 imagens por hotel
                        }
                    }
                }
            }
            
            // Se não tiver imagens suficientes, usar imagens de storage existentes
            if (count($images) < 3 && !empty($hotelStorageImages)) {
                foreach ($hotelStorageImages as $storageImage) {
                    if (count($images) >= 3) {
                        break;
                    }
                    // Verificar se a imagem existe
                    if (Storage::disk('public')->exists($storageImage)) {
                        $images[] = $storageImage;
                    }
                }
            }
            
            // Se ainda não tiver imagens suficientes, usar imagens aleatórias dos exemplos
            if (count($images) < 3) {
                $remainingImages = array_diff($allAvailableImages, array_map(function($img) {
                    return 'public/images/exemplos/' . basename($img);
                }, $images));
                
                shuffle($remainingImages);
                
                foreach (array_slice($remainingImages, 0, 3 - count($images)) as $imgPath) {
                    $imgName = basename($imgPath);
                    $destinationPath = 'hotels/' . uniqid() . '_' . $imgName;
                    
                    $copiedPath = $this->copyImageToStorage($imgPath, $destinationPath);
                    if ($copiedPath) {
                        $images[] = $copiedPath;
                    }
                }
            }
            
            // Garantir pelo menos uma imagem (usar uma das imagens de storage se disponível)
            if (empty($images) && !empty($hotelStorageImages)) {
                $images[] = $hotelStorageImages[0];
            }
            
            // Atualizar hotel com as imagens
            if (!empty($images)) {
                $hotel->images = $images;
                $hotel->save();
                $updated++;
                
                $this->command->info("✓ {$hotel->name} - " . count($images) . " imagem(ns) adicionada(s)");
            } else {
                $this->command->warn("⚠ {$hotel->name} - Nenhuma imagem disponível");
            }
        }

        $this->command->info("\n✓ {$updated} hotéis atualizados com imagens!");
    }
}


