<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quota;
use App\Models\RentalOffer;
use App\Models\ExchangeOffer;
use App\Models\SaleOffer;
use App\Models\PurchaseRequest;
use App\Models\Hotel;

class HotelOptionsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeTab = $request->get('tab', 'aluguel');

        // Obter todos os hotéis onde o usuário tem cotas (por nome)
        $userHotelNames = $user->quotas()
            ->whereNotNull('hotel_name')
            ->distinct()
            ->pluck('hotel_name')
            ->toArray();

        // Buscar por localização (location) das cotas
        $userCities = $user->quotas()
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->toArray();

        // Buscar hotel_id das ofertas de aluguel que o usuário criou (se houver)
        $userHotels = RentalOffer::where('user_id', $user->id)
            ->whereNotNull('hotel_id')
            ->distinct()
            ->pluck('hotel_id')
            ->toArray();

        // Dados para cada tab
        $data = [
            'aluguel' => $this->getRentalOffers($userHotels, $userHotelNames, $userCities, $request),
            'troca' => $this->getExchangeOffers($userHotelNames, $userCities, $request),
            'venda' => $this->getSaleOffers($userHotels, $userHotelNames, $userCities, $request),
            'compra' => $this->getPurchaseRequests($userHotels, $userHotelNames, $userCities, $request),
        ];

        return view('hotel-options.index', compact('activeTab', 'data'));
    }

    private function getRentalOffers(array $hotelIds, array $hotelNames, array $cities, Request $request)
    {
        $user = Auth::user();
        
        // Verificar se há algum parâmetro de busca preenchido
        $hasSearchParams = $this->hasSearchParams($request, 'aluguel');
        
        // Se não houver parâmetros de busca, retornar resultado vazio
        if (!$hasSearchParams) {
            return RentalOffer::whereRaw('1 = 0')->paginate(12);
        }
        
        $query = RentalOffer::where('status', 'active')
            ->where('user_id', '!=', $user->id) // Excluir ofertas do próprio usuário
            ->with(['user.profile', 'quota', 'hotel']);

        // Se cidade foi selecionada, buscar cotas disponíveis para aluguel naquela cidade
        if ($request->filled('aluguel_city') && $request->input('aluguel_city') !== '') {
            $city = $request->input('aluguel_city');
            // Buscar hotéis que têm a cidade especificada
            $hotelNamesWithCity = Hotel::where('city', 'like', '%' . $city . '%')->pluck('name')->toArray();
            
            if (!empty($hotelNamesWithCity)) {
                // Filtrar por hotéis dessa cidade através da quota relacionada
                $query->whereHas('quota', function($q) use ($hotelNamesWithCity) {
                    $q->whereIn('hotel_name', $hotelNamesWithCity)
                      ->where('status', Quota::STATUS_AVAILABLE); // Apenas cotas disponíveis
                });
            } else {
                // Se não encontrou hotéis com essa cidade, retornar resultado vazio
                return RentalOffer::whereRaw('1 = 0')->paginate(12);
            }
        }
        // Se estado foi selecionado (e não cidade), buscar cotas disponíveis para aluguel naquele estado
        elseif ($request->filled('aluguel_state') && $request->input('aluguel_state') !== '') {
            $state = $request->input('aluguel_state');
            // Buscar hotéis que têm o estado especificado
            $hotelNamesWithState = Hotel::where('state', $state)->pluck('name')->toArray();
                
            if (!empty($hotelNamesWithState)) {
                // Filtrar por hotéis desse estado através da quota relacionada
                $query->whereHas('quota', function($q) use ($hotelNamesWithState) {
                    $q->whereIn('hotel_name', $hotelNamesWithState)
                      ->where('status', Quota::STATUS_AVAILABLE); // Apenas cotas disponíveis
            });
        } else {
                // Se não encontrou hotéis com esse estado, retornar resultado vazio
            return RentalOffer::whereRaw('1 = 0')->paginate(12);
            }
        }
        // Se não houver filtro de cidade ou estado, mas houver outros filtros, buscar todas as ofertas ativas
        // (os outros filtros serão aplicados depois)

        // Aplicar filtros de busca
        // Filtro por mês e ano (só aplicar se não for "Todos" - valor vazio)
        if ($request->filled('aluguel_month') && $request->input('aluguel_month') !== '') {
            $month = $request->input('aluguel_month');
            $query->whereMonth('start_date', $month);
        }
        if ($request->filled('aluguel_year') && $request->input('aluguel_year') !== '') {
            $year = $request->input('aluguel_year');
            $query->whereYear('start_date', $year);
        }

        // Filtro por período - buscar ofertas que se sobrepõem ao período desejado
        if ($request->filled('aluguel_period_start') && $request->filled('aluguel_period_end')) {
            $periodStart = $request->input('aluguel_period_start');
            $periodEnd = $request->input('aluguel_period_end');
            $query->where(function($q) use ($periodStart, $periodEnd) {
                $q->where(function($q2) use ($periodStart, $periodEnd) {
                    // Oferta começa antes ou no início do período desejado E termina após ou no início
                    $q2->where('start_date', '<=', $periodEnd)
                       ->where('end_date', '>=', $periodStart);
                });
            });
        } elseif ($request->filled('aluguel_period_start')) {
            $query->where('end_date', '>=', $request->input('aluguel_period_start'));
        } elseif ($request->filled('aluguel_period_end')) {
            $query->where('start_date', '<=', $request->input('aluguel_period_end'));
        }

        // Filtro por número de pernoites
        if ($request->filled('aluguel_nights') && $request->input('aluguel_nights') !== '') {
            $nights = (int) $request->input('aluguel_nights');
            $query->where('number_of_days', $nights);
        }

        // Filtro por número de quartos (verificar na quota relacionada)
        if ($request->filled('aluguel_rooms') && $request->input('aluguel_rooms') !== '') {
            $rooms = (int) $request->input('aluguel_rooms');
            $query->whereHas('quota', function($q) use ($rooms) {
                $q->where('number_of_rooms', '>=', $rooms);
            });
        }

        // Filtro por número de pessoas
        if ($request->filled('aluguel_people') && $request->input('aluguel_people') !== '') {
            $people = (int) $request->input('aluguel_people');
            $query->where('number_of_people', '>=', $people);
        }

        // Filtro por preço máximo
        if ($request->filled('aluguel_max_price') && $request->input('aluguel_max_price') !== '') {
            $maxPrice = (float) $request->input('aluguel_max_price');
            $query->where(function($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice)
                  ->orWhere(function($q2) use ($maxPrice) {
                      $q2->whereNotNull('price_max')
                         ->where('price_max', '<=', $maxPrice);
                  });
            });
        }

        // Sempre ordenar por data de criação decrescente (mais recente primeiro)
        return $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc') // Ordenação secundária para garantir consistência
                    ->paginate(12)
                    ->appends($request->query());
    }

    private function getExchangeOffers(array $hotelNames, array $cities, Request $request)
    {
        $user = Auth::user();
        
        // Verificar se há algum parâmetro de busca preenchido
        $hasSearchParams = $this->hasSearchParams($request, 'troca');
        
        // Se não houver parâmetros de busca, retornar resultado vazio
        if (!$hasSearchParams) {
            return ExchangeOffer::whereRaw('1 = 0')->paginate(12);
        }
        
        $query = ExchangeOffer::where('status', 'active')
            ->where('user_id', '!=', $user->id) // Excluir ofertas do próprio usuário
            ->with(['user.profile', 'quota']);

        // Aplicar filtros de busca
        // Filtro por mês e ano (só aplicar se não for "Todos" - valor vazio)
        if ($request->filled('troca_month') && $request->input('troca_month') !== '') {
            $month = (int) $request->input('troca_month');
            $query->where(function ($q) use ($month) {
                $q->where('desired_period_month', $month)
                    ->orWhere(function ($q2) use ($month) {
                        $q2->whereNull('desired_period_month')
                            ->whereMonth('desired_period_start', $month);
                    });
            });
        }
        if ($request->filled('troca_year') && $request->input('troca_year') !== '') {
            $year = (int) $request->input('troca_year');
            $query->where(function ($q) use ($year) {
                $q->where('desired_period_year', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('desired_period_year')
                            ->whereYear('desired_period_start', $year);
                    });
            });
        }

        // Filtro por período - buscar ofertas que se sobrepõem ao período desejado
        if ($request->filled('troca_period_start') && $request->filled('troca_period_end')) {
            $periodStart = $request->input('troca_period_start');
            $periodEnd = $request->input('troca_period_end');
            $query->where(function($q) use ($periodStart, $periodEnd) {
                $q->where(function($q2) use ($periodStart, $periodEnd) {
                    // Oferta começa antes ou no início do período desejado E termina após ou no início
                    $q2->where('desired_period_start', '<=', $periodEnd)
                       ->where('desired_period_end', '>=', $periodStart);
                });
            });
        } elseif ($request->filled('troca_period_start')) {
            $query->where('desired_period_end', '>=', $request->input('troca_period_start'));
        } elseif ($request->filled('troca_period_end')) {
            $query->where('desired_period_start', '<=', $request->input('troca_period_end'));
        }

        // Filtro por número de pernoites
        if ($request->filled('troca_nights') && $request->input('troca_nights') !== '') {
            $nights = (int) $request->input('troca_nights');
            $query->whereRaw('DATEDIFF(desired_period_end, desired_period_start) = ?', [$nights - 1]);
        }

        // Filtro por número de quartos
        if ($request->filled('troca_rooms') && $request->input('troca_rooms') !== '') {
            $rooms = (int) $request->input('troca_rooms');
            $query->where('desired_rooms', '>=', $rooms);
        }

        // Filtro por número de pessoas
        if ($request->filled('troca_people') && $request->input('troca_people') !== '') {
            $people = (int) $request->input('troca_people');
            $query->where('desired_people', '>=', $people);
        }

        // Sempre ordenar por data de criação decrescente (mais recente primeiro)
        return $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc') // Ordenação secundária para garantir consistência
                    ->paginate(12)
                    ->appends($request->query());
    }

    private function getSaleOffers(array $hotelIds, array $hotelNames, array $cities, Request $request)
    {
        $user = Auth::user();
        
        // Verificar se há algum parâmetro de busca preenchido
        $hasSearchParams = $this->hasSearchParams($request, 'venda');
        
        // Se não houver parâmetros de busca, retornar resultado vazio
        if (!$hasSearchParams) {
            return SaleOffer::whereRaw('1 = 0')->paginate(12);
        }
        
        $query = SaleOffer::whereIn('status', ['pending', 'negotiating'])
            ->where('user_id', '!=', $user->id) // Excluir ofertas do próprio usuário
            ->with(['user.profile', 'hotel', 'quota']);

        // Aplicar filtros de busca
        // Filtro por tipo da cota (só aplicar se não for "Todos" - valor vazio)
        if ($request->filled('venda_quota_type') && $request->input('venda_quota_type') !== '') {
            $quotaType = $request->input('venda_quota_type');
            $query->whereHas('quota', function($q) use ($quotaType) {
                if ($quotaType === 'fixa') {
                    // Assumindo que período fixo significa que não é flexível
                    $q->where('is_exchange', false);
                } elseif ($quotaType === 'flexivel') {
                    // Assumindo que flexível significa que aceita troca
                    $q->where('is_exchange', true);
                } elseif ($quotaType === 'fixa_flexivel') {
                    // Ambos os tipos
                    // Não aplicar filtro específico
                }
            });
        }

        // Filtro por número de quartos (verificar na quota relacionada)
        if ($request->filled('venda_rooms') && $request->input('venda_rooms') !== '') {
            $rooms = (int) $request->input('venda_rooms');
            $query->whereHas('quota', function($q) use ($rooms) {
                $q->where('number_of_rooms', '>=', $rooms);
            });
        }

        // Filtro por número de pessoas (precisamos verificar na quota relacionada)
        if ($request->filled('venda_people')) {
            $people = (int) $request->input('venda_people');
            $query->whereHas('quota', function($q) use ($people) {
                $q->where('number_of_guests', '>=', $people);
            });
        }

        // Filtro por número de dias da cota (7, 14, 21, 28 - múltiplos de 7)
        if ($request->filled('venda_days')) {
            $days = (int) $request->input('venda_days');
            $query->whereHas('quota', function($q) use ($days) {
                $q->whereRaw('DATEDIFF(end_date, start_date) + 1 = ?', [$days]);
            });
        }

        // Filtro por período (data de início e término) para cotas fixas
        if ($request->filled('venda_period_start') && $request->filled('venda_period_end')) {
            $periodStart = $request->input('venda_period_start');
            $periodEnd = $request->input('venda_period_end');
            $query->whereHas('quota', function($q) use ($periodStart, $periodEnd) {
                // Buscar cotas que se sobrepõem ao período desejado
                $q->where(function($q2) use ($periodStart, $periodEnd) {
                    $q2->where('start_date', '<=', $periodEnd)
                       ->where('end_date', '>=', $periodStart);
                });
            });
        } elseif ($request->filled('venda_period_start')) {
            $periodStart = $request->input('venda_period_start');
            $query->whereHas('quota', function($q) use ($periodStart) {
                $q->where('end_date', '>=', $periodStart);
            });
        } elseif ($request->filled('venda_period_end')) {
            $periodEnd = $request->input('venda_period_end');
            $query->whereHas('quota', function($q) use ($periodEnd) {
                $q->where('start_date', '<=', $periodEnd);
            });
        }

        // Filtro por mês da cota (apenas para cotas fixas)
        if ($request->filled('venda_month') && $request->input('venda_month') !== '') {
            $month = (int) $request->input('venda_month');
            $query->whereHas('quota', function($q) use ($month) {
                $q->whereMonth('start_date', $month);
            });
        }

        // Filtro por preço
        if ($request->filled('venda_price')) {
            $price = (float) $request->input('venda_price');
            $query->where(function ($q) use ($price) {
                $q->where(function ($q2) use ($price) {
                    $q2->whereNotNull('desired_price')->where('desired_price', '<=', $price);
                })->orWhere(function ($q2) use ($price) {
                    $q2->whereNotNull('acceptable_price')->where('acceptable_price', '<=', $price);
                })->orWhere(function ($q2) use ($price) {
                    $q2->whereNotNull('minimum_price')->where('minimum_price', '<=', $price);
                });
            });
        }

        // Sempre ordenar por data de criação decrescente (mais recente primeiro)
        return $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc') // Ordenação secundária para garantir consistência
                    ->paginate(12)
                    ->appends($request->query());
    }

    private function getPurchaseRequests(array $hotelIds, array $hotelNames, array $cities, Request $request)
    {
        $user = Auth::user();
        
        // Verificar se há algum parâmetro de busca preenchido
        $hasSearchParams = $this->hasSearchParams($request, 'compra');
        
        // Se não houver parâmetros de busca, retornar resultado vazio
        if (!$hasSearchParams) {
            return PurchaseRequest::whereRaw('1 = 0')->paginate(12);
        }
        
        $query = PurchaseRequest::where('status', 'active')
            ->where('user_id', '!=', $user->id) // Excluir solicitações do próprio usuário
            ->with(['user', 'hotel']);

        // Aplicar filtros de busca
        // Filtro por tipo da cota (só aplicar se não for "Todos" - valor vazio)
        if ($request->filled('compra_quota_type') && $request->input('compra_quota_type') !== '') {
            $quotaType = $request->input('compra_quota_type');
            if ($quotaType === 'fixa') {
                $query->where('period_type', 'fixo');
            } elseif ($quotaType === 'flexivel') {
                $query->where('period_type', 'flexivel');
            }
            // Para 'fixa_flexivel' não aplicamos filtro
        }

        // Filtro por número de quartos
        // Nota: purchase_requests não tem campo number_of_rooms diretamente
        // Se necessário, pode ser adicionado via migration ou filtrado por hotel relacionado
        if ($request->filled('compra_rooms') && $request->input('compra_rooms') !== '') {
            $rooms = (int) $request->input('compra_rooms');
            // Verificar se a coluna existe antes de aplicar o filtro
            if (\Schema::hasColumn('purchase_requests', 'number_of_rooms')) {
                $query->where('number_of_rooms', '>=', $rooms);
            }
            // Se o campo não existir, o filtro será ignorado silenciosamente
        }

        // Filtro por número de pessoas (assumindo que weeks representa semanas e precisamos calcular pessoas)
        // Como não temos campo direto, vamos pular este filtro ou usar weeks como aproximação
        // Nota: Este filtro pode precisar ser ajustado conforme a estrutura real do banco

        // Filtro por número de dias da cota (7, 14, 21, 28 - múltiplos de 7)
        if ($request->filled('compra_days')) {
            $days = (int) $request->input('compra_days');
            $weeks = $days / 7;
            $query->where('weeks', $weeks);
        }

        // Filtro por período (data de início e término) para cotas fixas
        // Nota: purchase_requests não tem campos de data específica (start_date/end_date)
        // Este filtro será aplicado apenas se houver campos de data na tabela
        // Por enquanto, vamos usar o campo month se disponível
        if ($request->filled('compra_period_start') || $request->filled('compra_period_end')) {
            // Se houver campos de data na tabela purchase_requests, aplicar filtro
            // Caso contrário, usar apenas o filtro de mês
        }

        // Filtro por mês da cota (apenas para cotas fixas)
        if ($request->filled('compra_month') && $request->input('compra_month') !== '') {
            $month = (int) $request->input('compra_month');
            // O campo month já existe na tabela purchase_requests
            $query->where('month', $month);
        }

        // Filtro por preço máximo
        if ($request->filled('compra_max_price')) {
            $maxPrice = (float) $request->input('compra_max_price');
            $query->where(function($q) use ($maxPrice) {
                $q->where(function($q2) use ($maxPrice) {
                    $q2->where('price_range_max', '<=', $maxPrice)
                       ->orWhere('max_price', '<=', $maxPrice)
                       ->orWhere('price_range_min', '<=', $maxPrice);
                });
            });
        }

        // Sempre ordenar por data de criação decrescente (mais recente primeiro)
        return $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc') // Ordenação secundária para garantir consistência
                    ->paginate(12)
                    ->appends($request->query());
    }

    /**
     * Verifica se há parâmetros de busca preenchidos para uma tab específica
     */
    private function hasSearchParams(Request $request, string $tabPrefix): bool
    {
        // Lista de campos de busca para cada tab
        $searchFields = [];
        
        switch ($tabPrefix) {
            case 'aluguel':
                $searchFields = [
                    'aluguel_city',
                    'aluguel_state',
                    'aluguel_month',
                    'aluguel_year',
                    'aluguel_period_start',
                    'aluguel_period_end',
                    'aluguel_nights',
                    'aluguel_rooms',
                    'aluguel_people',
                    'aluguel_max_price',
                ];
                break;
            case 'troca':
                $searchFields = [
                    'troca_month',
                    'troca_year',
                    'troca_period_start',
                    'troca_period_end',
                    'troca_nights',
                    'troca_rooms',
                    'troca_people',
                ];
                break;
            case 'venda':
                $searchFields = [
                    'venda_quota_type',
                    'venda_rooms',
                    'venda_people',
                    'venda_days',
                    'venda_period_start',
                    'venda_period_end',
                    'venda_month',
                    'venda_max_price',
                ];
                break;
            case 'compra':
                $searchFields = [
                    'compra_quota_type',
                    'compra_hotel',
                    'compra_state',
                    'compra_city',
                    'compra_seasonality',
                    'compra_rooms',
                    'compra_price_max',
                    'compra_day_start',
                    'compra_day_end',
                    'compra_month',
                ];
                break;
        }
        
        // Verificar se pelo menos um campo está preenchido
        foreach ($searchFields as $field) {
            if ($request->filled($field) && $request->input($field) !== '') {
                return true;
            }
        }
        
        return false;
    }
}

