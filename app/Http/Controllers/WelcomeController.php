<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quota;
use App\Models\Hotel;
use App\Models\QuotaTransaction;
use Carbon\Carbon;
use App\Models\SuccessFee;
use App\Models\Faq;

class WelcomeController extends Controller
{
    public function index()
    {
        try {
            // Buscar todas as cotas disponíveis para processar
            $allQuotas = Quota::where('status', 'available')
                ->whereNotNull('end_date')
                ->whereDate('end_date', '>=', Carbon::today())
                ->withMarketplaceListing()
                ->with(['user', 'rentalOffers', 'exchangeOffers', 'saleOffers'])
                ->latest('created_at')
                ->get();
            
            // Buscar todos os hotéis ativos de uma vez
            $hotelNames = $allQuotas->pluck('hotel_name')->filter()->unique();
            $hotels = collect();
            
            if ($hotelNames->isNotEmpty()) {
                $hotels = Hotel::whereIn('name', $hotelNames)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('name');
            }
            
            $now = now();
            
            // Métricas principais da plataforma
            $totalRentals = 0;
            $totalExchanges = 0;
            
            try {
                $completedRentalsQuery = QuotaTransaction::where('transaction_type', 'rental')
                    ->where('status', 'completed');
                $totalRentals = (clone $completedRentalsQuery)->count();
                
                $totalExchanges = QuotaTransaction::where('transaction_type', 'exchange')
                    ->where('status', 'completed')
                    ->count();
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar métricas: ' . $e->getMessage());
            }

            $heroStats = [
                'rented' => $totalRentals,
                'exchanged' => $totalExchanges,
                'purchased' => $totalRentals,
                'sold' => $totalRentals,
            ];

        // Filtrar cotas novas (1 a 7 dias)
        $newQuotas = $allQuotas
            ->filter(function($quota) use ($hotels, $now) {
                if (!$quota || !$quota->hotel_name) {
                    return false;
                }
                $hotel = $hotels->get($quota->hotel_name);
                if (!$hotel) {
                    return false;
                }
                $daysDiff = $quota->created_at ? $quota->created_at->diffInDays($now) : null;
                $hasImages = $hotel->images && (is_array($hotel->images) || is_object($hotel->images));
                $imageCount = $hasImages ? (is_array($hotel->images) ? count($hotel->images) : count((array)$hotel->images)) : 0;
                return $imageCount > 0 && $daysDiff !== null && $daysDiff <= 7;
            })
            ->take(10)
            ->values();
        
        // Filtrar cotas recentes (8 a 15 dias)
        $recentQuotas = $allQuotas
            ->filter(function($quota) use ($hotels, $now) {
                if (!$quota || !$quota->hotel_name) {
                    return false;
                }
                $hotel = $hotels->get($quota->hotel_name);
                if (!$hotel) {
                    return false;
                }
                $daysDiff = $quota->created_at ? $quota->created_at->diffInDays($now) : null;
                $hasImages = $hotel->images && (is_array($hotel->images) || is_object($hotel->images));
                $imageCount = $hasImages ? (is_array($hotel->images) ? count($hotel->images) : count((array)$hotel->images)) : 0;
                return $imageCount > 0 && $daysDiff !== null && $daysDiff >= 8 && $daysDiff <= 15;
            })
            ->take(10)
            ->values();
        
        // Filtrar demais cotas (mais de 15 dias)
        $moreQuotas = $allQuotas
            ->filter(function($quota) use ($hotels, $now) {
                if (!$quota || !$quota->hotel_name) {
                    return false;
                }
                $hotel = $hotels->get($quota->hotel_name);
                if (!$hotel) {
                    return false;
                }
                $daysDiff = $quota->created_at ? $quota->created_at->diffInDays($now) : null;
                $hasImages = $hotel->images && (is_array($hotel->images) || is_object($hotel->images));
                $imageCount = $hasImages ? (is_array($hotel->images) ? count($hotel->images) : count((array)$hotel->images)) : 0;
                return $imageCount > 0 && $daysDiff !== null && $daysDiff > 15;
            })
            ->take(10)
            ->values();

        // Destacar uma cota para cada categoria
        $highlightConfigs = [
            [
                'title' => 'Novas',
                'badge_class' => 'badge-novas',
                'collection' => $newQuotas,
            ],
            [
                'title' => 'Recentes',
                'badge_class' => 'badge-recentes',
                'collection' => $recentQuotas,
            ],
            [
                'title' => '+ Cadastradas',
                'badge_class' => 'badge-demais',
                'collection' => $moreQuotas,
            ],
        ];

        $highlightCards = [];

        foreach ($highlightConfigs as $config) {
            $quota = $config['collection']->first();
            $hotel = $quota ? $hotels->get($quota->hotel_name) : null;

            $highlightCards[] = [
                'title' => $config['title'],
                'badge_class' => $config['badge_class'],
                'quota' => $quota,
                'hotel' => $hotel,
                'has_data' => $quota && $hotel && $hotel->images && is_array($hotel->images) && count($hotel->images) > 0,
            ];
        }
        
            // Buscar estados que têm cotas disponíveis através dos hotéis vinculados
            $statesWithQuotas = [];
            try {
                $hotelNamesForStates = $allQuotas->pluck('hotel_name')->filter()->unique();
                if ($hotelNamesForStates->isNotEmpty()) {
                    $hotelsWithQuotas = Hotel::whereIn('name', $hotelNamesForStates)
                        ->where('is_active', true)
                        ->whereNotNull('state')
                        ->where('state', '!=', '')
                        ->distinct()
                        ->pluck('state')
                        ->map(function($state) {
                            return strtolower(trim($state));
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    $statesWithQuotas = $hotelsWithQuotas;
                }
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar estados: ' . $e->getMessage());
                $statesWithQuotas = [];
            }
            
            // Buscar taxas de êxito para cada perfil
            try {
                $successFees = [
                    'curioso' => SuccessFee::getActiveFeesForProfile('curioso'),
                    'inteligente' => SuccessFee::getActiveFeesForProfile('inteligente'),
                    'sabio' => SuccessFee::getActiveFeesForProfile('sabio'),
                ];
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar taxas de êxito: ' . $e->getMessage());
                // Em caso de erro, retornar collections vazias
                $successFees = [
                    'curioso' => collect(),
                    'inteligente' => collect(),
                    'sabio' => collect(),
                ];
            }
            
            // Passar hotéis para a view para evitar queries adicionais
            return view('welcome', compact('heroStats', 'highlightCards', 'newQuotas', 'recentQuotas', 'moreQuotas', 'statesWithQuotas', 'hotels', 'successFees'));
        } catch (\Exception $e) {
            \Log::error('Erro no WelcomeController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Retornar view com dados vazios em caso de erro
            return view('welcome', [
                'heroStats' => ['rented' => 0, 'exchanged' => 0, 'purchased' => 0, 'sold' => 0],
                'highlightCards' => [],
                'newQuotas' => collect(),
                'recentQuotas' => collect(),
                'moreQuotas' => collect(),
                'statesWithQuotas' => [],
                'hotels' => collect(),
                'successFees' => [
                    'curioso' => collect(),
                    'inteligente' => collect(),
                    'sabio' => collect(),
                ],
            ]);
        }
    }

    /**
     * Exibe a página de Perguntas Frequentes.
     */
    public function faq()
    {
        $faqs = Faq::query()->active()->ordered()->get();

        return view('faq', compact('faqs'));
    }
}
