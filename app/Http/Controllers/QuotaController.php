<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Quota;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Support\Facades\DB;
use App\Models\QuotaTransaction;
use App\Models\DigitalContract;
use App\Models\RentalOffer;
use App\Models\FavoriteListItem;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class QuotaController extends Controller
{
    // Middleware is handled in routes/web.php

    /**
     * Display a listing of available quotas (public version for non-authenticated users).
     */
    public function publicIndex(Request $request)
    {
        // Verificar se há algum filtro aplicado
        $hasAnyFilter = $request->filled('hotel_name') ||
                       $request->filled('city') ||
                       $request->filled('state') ||
                       $request->filled('month') ||
                       $request->filled('year') ||
                       $request->filled('start_date') ||
                       $request->filled('end_date') ||
                       $request->filled('guests') ||
                       $request->filled('location') ||
                       $request->filled('search'); // Campo hidden que indica que houve uma busca

        // Se não houver nenhum filtro aplicado, retornar resultado vazio
        if (!$hasAnyFilter) {
            $quotas = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                12,
                1,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
            $quotas->withQueryString();
            
            return view('quotas.public-index', compact('quotas'));
        }

        $query = Quota::where('status', Quota::STATUS_AVAILABLE)
            ->whereStayPeriodNotEnded();

        // Apply filters
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('hotel_name')) {
            $query->where('hotel_name', 'like', '%' . $request->hotel_name . '%');
        }
        
        // Filtro por cidade
        if ($request->filled('city')) {
            $query->where(function($q) use ($request) {
                $q->where('location', 'like', '%' . $request->city . '%');
            });
        }

        // Filtro por estado - buscar APENAS através do Hotel relacionado para evitar resultados incorretos
        // Ex: buscar "SP" não deve trazer "Caldas Novas, GO" que pode conter "SP" no location
        if ($request->filled('state')) {
            $state = $request->state;
            // Buscar hotéis que têm o estado especificado (busca exata)
            $hotelNamesWithState = Hotel::where('state', $state)->pluck('name')->toArray();
            
            // Aplicar filtro APENAS se encontrar hotéis com esse estado
            // Não usar fallback no location para evitar resultados incorretos
            if (!empty($hotelNamesWithState)) {
                $query->whereIn('hotel_name', $hotelNamesWithState);
            } else {
                // Se não encontrou hotéis com esse estado, retornar resultado vazio
                $query->whereRaw('1 = 0'); // Força resultado vazio
            }
        }

        // Filtro por ano - quando apenas o ano é selecionado (sem mês)
        if ($request->filled('year') && !$request->filled('month')) {
            $year = (int) $request->year;
            $yearStart = sprintf('%04d-01-01', $year);
            $yearEnd = sprintf('%04d-12-31', $year);
            $nextYearStart = sprintf('%04d-01-01', $year + 1);
            
            // Garantir que a cota tenha pelo menos uma data dentro do ano especificado
            // A cota deve começar antes ou durante o ano E terminar durante ou após o ano
            $query->where(function($q) use ($yearStart, $yearEnd, $year, $nextYearStart) {
                // Verificar período principal - deve ter sobreposição com o ano
                $q->where(function($mainQuery) use ($yearStart, $yearEnd, $nextYearStart) {
                    $mainQuery->where('start_date', '<=', $yearEnd)
                              ->where('end_date', '>=', $yearStart)
                              // Garantir que pelo menos uma data esteja dentro do ano
                              ->where(function($yearQuery) use ($yearStart, $nextYearStart) {
                                  $yearQuery->where('start_date', '>=', $yearStart)
                                            ->where('start_date', '<', $nextYearStart)
                                            ->orWhere(function($endYearQuery) use ($yearStart, $nextYearStart) {
                                                $endYearQuery->where('end_date', '>=', $yearStart)
                                                             ->where('end_date', '<', $nextYearStart);
                                            });
                              });
                })
                // OU verificar fraction_details que contenham o ano
                ->orWhere(function($fractionQuery) use ($year) {
                    $fractionQuery->whereNotNull('fraction_details')
                                  ->where('fraction_details', 'like', '%' . $year . '%');
                });
            });
        }

        // Filtro por mês e ano - considera período principal e fraction_details (frações)
        if ($request->filled('month')) {
            $month = (int) $request->month;

            if ($request->filled('year')) {
                $year = (int) $request->year;
                $monthStart = sprintf('%04d-%02d-01', $year, $month);
                $monthEnd = date('Y-m-t', strtotime($monthStart));
                $nextYearStart = sprintf('%04d-01-01', $year + 1);
                $yearStart = sprintf('%04d-01-01', $year);
                $query->where(function ($q) use ($monthStart, $monthEnd, $year, $month, $yearStart, $nextYearStart) {
                    $q->where(function ($mainQuery) use ($monthStart, $monthEnd, $yearStart, $nextYearStart) {
                        $mainQuery->where('start_date', '<=', $monthEnd)
                            ->where('end_date', '>=', $monthStart)
                            ->where(function ($yearQuery) use ($yearStart, $nextYearStart) {
                                $yearQuery->where('start_date', '>=', $yearStart)
                                    ->where('start_date', '<', $nextYearStart)
                                    ->orWhere(function ($endYearQuery) use ($yearStart, $nextYearStart) {
                                        $endYearQuery->where('end_date', '>=', $yearStart)
                                            ->where('end_date', '<', $nextYearStart);
                                    });
                            });
                    })
                    ->orWhere(function ($fractionQuery) use ($year, $month) {
                        $fractionQuery->whereNotNull('fraction_details')
                            ->where(function ($subQuery) use ($year, $month) {
                                $monthStr = sprintf('%04d-%02d', $year, $month);
                                $subQuery->where('fraction_details', 'like', '%' . $monthStr . '%');
                            });
                    });
                });
            } else {
                $currentYear = date('Y');
                $nextYear = $currentYear + 1;

                $query->where(function ($q) use ($month, $currentYear, $nextYear) {
                    $q->where(function ($subQ) use ($month, $currentYear) {
                        $monthStart = sprintf('%04d-%02d-01', $currentYear, $month);
                        $monthEnd = date('Y-m-t', strtotime($monthStart));

                        $subQ->where('start_date', '<=', $monthEnd)
                            ->where('end_date', '>=', $monthStart);
                    })
                    ->orWhere(function ($subQ) use ($month, $nextYear) {
                        $monthStart = sprintf('%04d-%02d-01', $nextYear, $month);
                        $monthEnd = date('Y-m-t', strtotime($monthStart));

                        $subQ->where('start_date', '<=', $monthEnd)
                            ->where('end_date', '>=', $monthStart);
                    })
                    ->orWhere(function ($fractionQuery) use ($currentYear, $nextYear, $month) {
                        $fractionQuery->whereNotNull('fraction_details')
                            ->where(function ($subQuery) use ($currentYear, $nextYear, $month) {
                                $monthStrCurrent = sprintf('-%02d', $month);
                                $subQuery->where('fraction_details', 'like', '%' . $currentYear . $monthStrCurrent . '%')
                                    ->orWhere('fraction_details', 'like', '%' . $nextYear . $monthStrCurrent . '%');
                            });
                    });
                });
            }
        }

        // Filtro por datas (entrada/saída): sobreposição com período principal ou frações em fraction_details
        if (!$request->filled('month')) {
            $checkIn = $request->filled('start_date') ? $request->start_date : null;
            $checkOut = $request->filled('end_date') ? $request->end_date : null;

            if ($checkIn && $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where(function ($mainQuery) use ($checkIn, $checkOut) {
                        $mainQuery->where('start_date', '<=', $checkOut)
                            ->where('end_date', '>=', $checkIn);
                    })
                    ->orWhere(function ($fractionQuery) use ($checkIn, $checkOut) {
                        $fractionQuery->whereNotNull('fraction_details')
                            ->where(function ($subQuery) use ($checkIn, $checkOut) {
                                $subQuery->where('fraction_details', 'like', '%"start":"' . $checkIn . '"%')
                                    ->orWhere('fraction_details', 'like', '%"start":"' . $checkOut . '"%')
                                    ->orWhere('fraction_details', 'like', '%"end":"' . $checkIn . '"%')
                                    ->orWhere('fraction_details', 'like', '%"end":"' . $checkOut . '"%')
                                    ->orWhere('fraction_details', 'like', '%"start_date":"' . $checkIn . '"%')
                                    ->orWhere('fraction_details', 'like', '%"start_date":"' . $checkOut . '"%')
                                    ->orWhere('fraction_details', 'like', '%"end_date":"' . $checkIn . '"%')
                                    ->orWhere('fraction_details', 'like', '%"end_date":"' . $checkOut . '"%');
                            });
                    });
                });
            } elseif ($checkIn) {
                $query->where(function ($q) use ($checkIn) {
                    $q->where(function ($m) use ($checkIn) {
                        $m->where('start_date', '<=', $checkIn)->where('end_date', '>=', $checkIn);
                    })
                    ->orWhere(function ($fractionQuery) use ($checkIn) {
                        $fractionQuery->whereNotNull('fraction_details')
                            ->where('fraction_details', 'like', '%' . $checkIn . '%');
                    });
                });
            } elseif ($checkOut) {
                $query->where(function ($q) use ($checkOut) {
                    $q->where(function ($m) use ($checkOut) {
                        $m->where('start_date', '<=', $checkOut)->where('end_date', '>=', $checkOut);
                    })
                    ->orWhere(function ($fractionQuery) use ($checkOut) {
                        $fractionQuery->whereNotNull('fraction_details')
                            ->where('fraction_details', 'like', '%' . $checkOut . '%');
                    });
                });
            }
        }

        if ($request->filled('guests')) {
            $query->where('number_of_guests', '>=', $request->guests);
        }

        if ($request->filled('transaction_type')) {
            if ($request->transaction_type === 'rental') {
                $query->where('is_exchange', false);
            } elseif ($request->transaction_type === 'exchange') {
                $query->where('is_exchange', true);
            }
        }

        $effectiveTx = $request->filled('transaction_type') ? (string) $request->transaction_type : 'rental';
        if ($effectiveTx === 'exchange') {
            $query->whereHasActiveExchangeListing();
        } else {
            $query->whereHasActiveRentalListing();
        }

        $query->with(['rentalOffers', 'exchangeOffers', 'saleOffers', 'user']);

        $quotas = $query->orderBy('created_at', 'desc')->paginate(12);

        \Log::info('PublicIndex result count', [
            'count' => $quotas->total(),
        ]);
        
        // Buscar todos os hotéis de uma vez
        $hotelNames = $quotas->pluck('hotel_name')->unique();
        $hotels = Hotel::whereIn('name', $hotelNames)->get()->keyBy('name');
        
        // Adicionar badge e buscar dados do hotel
        foreach ($quotas as $quota) {
            $daysSinceCreation = now()->diffInDays($quota->created_at);
            if ($daysSinceCreation <= 8) {
                $quota->badge = 'Nova';
                $quota->badge_color = 'success';
            } elseif ($daysSinceCreation <= 15) {
                $quota->badge = 'Recente';
                $quota->badge_color = 'info';
            } else {
                $quota->badge = null;
            }
            
            // Buscar imagens do hotel
            $hotel = $hotels[$quota->hotel_name] ?? null;
            if ($hotel) {
                $quota->hotel_images = $hotel->images ?? [];
                
                // Calcular preço no Booking.com (simulado: 30% a mais que o valor de listagem)
                $listPrice = $quota->getMarketplaceListPrice($request->filled('transaction_type') && $request->transaction_type === 'exchange' ? 'exchange' : 'rent');
                if (! $quota->is_exchange && $listPrice) {
                    $quota->booking_price = $listPrice * 1.3;
                    $quota->discount_percentage = round((($quota->booking_price - $listPrice) / $quota->booking_price) * 100);
                }
            } else {
                $quota->hotel_images = [];
            }
            
            // Buscar comodidades da cota (não do hotel)
            $quota->quota_amenities = $this->getQuotaAmenities($quota);
        }

        return view('quotas.public-index', compact('quotas'));
    }

    /**
     * Display featured quotas (paid to appear in highlights).
     */
    public function featured()
    {
        $featuredQuotas = Quota::where('status', 'available')
            ->whereStayPeriodNotEnded()
            ->where('is_published', true)
            ->where('payment_status', 'paid')
            ->withMarketplaceListing()
            ->with(['user', 'rentalOffers', 'exchangeOffers', 'saleOffers'])
            ->latest('published_at')
            ->get();

        // Buscar hotéis para cada cota
        $hotelNames = $featuredQuotas->pluck('hotel_name')->unique();
        $hotels = Hotel::whereIn('name', $hotelNames)
            ->where('is_active', true)
            ->get()
            ->keyBy('name');

        // Adicionar informações do hotel a cada cota
        foreach ($featuredQuotas as $quota) {
            $hotel = $hotels->get($quota->hotel_name);
            if ($hotel) {
                $quota->hotel = $hotel;
                $quota->hotel_images = $hotel->images ?? [];
                $quota->hotel_amenities = $hotel->amenities ?? [];
            } else {
                $quota->hotel = null;
                $quota->hotel_images = [];
                $quota->hotel_amenities = [];
            }
        }

        return view('quotas.featured', compact('featuredQuotas'));
    }

    /**
     * Display the specified quota (public version).
     */
    public function publicShow(Quota $quota)
    {
        // Verificar se a cota está disponível
        if ($quota->status !== 'available') {
            abort(404, 'Cota não encontrada ou não disponível.');
        }

        if (!$quota->end_date || $quota->end_date->copy()->startOfDay()->lt(Carbon::today()->startOfDay())) {
            abort(404, 'Cota não encontrada ou não disponível.');
        }

        $quota->load(['user.profile', 'rentalOffers', 'exchangeOffers', 'saleOffers']);
        
        // Perfil do proprietário/gestor da cota
        $profile = $quota->user->profile ?? null;
        // Flag indicando se a cota é do proprietário (1) ou gestor (2)
        $isOwner = $quota->is_owner ?? ($profile ? true : false);

        // Buscar informações do hotel
        $hotel = null;
        if (!empty($quota->hotel_name)) {
            $hotel = Hotel::where('name', $quota->hotel_name)
                ->where('is_active', true)
                ->first();
        }

        return view('quotas.public-show', compact('quota', 'hotel', 'profile', 'isOwner'));
    }

    /**
     * Display a listing of available quotas.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        $query = Quota::where('status', Quota::STATUS_AVAILABLE)
            ->with(['user', 'hotel']);

        // Verificar se há algum filtro aplicado (transaction_type não conta como filtro)
        $hasAnyFilter = $request->filled('hotel_name') ||
                       $request->filled('city') ||
                       $request->filled('state') ||
                       $request->filled('month') ||
                       $request->filled('year') ||
                       $request->filled('check_in') ||
                       $request->filled('check_out') ||
                       $request->filled('start_date') ||
                       $request->filled('end_date') ||
                       $request->filled('people') ||
                       $request->filled('guests') ||
                       $request->filled('rooms') ||
                       $request->filled('stay_duration') ||
                       $request->filled('seasonality') ||
                       $request->filled('quota_type') ||
                       ($request->filled('price_min') && (float)$request->price_min > 0) ||
                       ($request->filled('price_max') && (float)$request->price_max < 250000) ||
                       $request->filled('hidromassagem') ||
                       $request->filled('academia') ||
                       $request->filled('estacionamento_gratuito') ||
                       $request->filled('vista_mar') ||
                       $request->filled('lareira') ||
                       $request->filled('adega') ||
                       $request->filled('area_kids') ||
                       $request->filled('area_trabalho') ||
                       $request->filled('spa') ||
                       $request->filled('piscina') ||
                       $request->filled('wifi') ||
                       $request->filled('breakfast') ||
                       $request->filled('sofa_mais') ||
                       $request->filled('search'); // Campo hidden que indica que houve uma busca

        // Se não houver nenhum filtro aplicado, retornar resultado vazio
        if (!$hasAnyFilter) {
            $quotas = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                12,
                1,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
            $quotas->withQueryString();
            
            $favoriteIds = collect();
            $wishlistIds = $this->wishlistQuotaIdsForUser();

            return view('quotas.index', compact('quotas', 'profile', 'favoriteIds', 'wishlistIds'));
        }

        // Apply filters - todos funcionam individualmente e em conjunto
        
        // Filtro por hotel - buscar hotéis que INICIAM com o termo digitado
        if ($request->filled('hotel_name')) {
            $query->where('hotel_name', 'like', $request->hotel_name . '%');
        }
        
        // Filtro por cidade - buscar através do Hotel relacionado
        if ($request->filled('city')) {
            $city = $request->city;
            // Buscar hotéis que têm a cidade especificada
            $hotelNamesWithCity = Hotel::where('city', 'like', '%' . $city . '%')->pluck('name')->toArray();
            
            $query->where(function($q) use ($hotelNamesWithCity, $city) {
                if (!empty($hotelNamesWithCity)) {
                    // Se encontrou hotéis com essa cidade, buscar cotas desses hotéis
                    $q->whereIn('hotel_name', $hotelNamesWithCity);
                } else {
                    // Se não encontrou, buscar no campo location (fallback)
                    $q->where('location', 'like', '%' . $city . '%');
                }
            });
        }

        // Filtro por estado - buscar APENAS através do Hotel relacionado para evitar resultados incorretos
        // Ex: buscar "SP" não deve trazer "Caldas Novas, GO" que pode conter "SP" no location
        if ($request->filled('state')) {
            $state = $request->state;
            // Buscar hotéis que têm o estado especificado (busca exata)
            $hotelNamesWithState = Hotel::where('state', $state)->pluck('name')->toArray();
            
            // Aplicar filtro APENAS se encontrar hotéis com esse estado
            // Não usar fallback no location para evitar resultados incorretos
            if (!empty($hotelNamesWithState)) {
                $query->whereIn('hotel_name', $hotelNamesWithState);
            } else {
                // Se não encontrou hotéis com esse estado, retornar resultado vazio
                $query->whereRaw('1 = 0'); // Força resultado vazio
            }
        }

        // Filtro por ano - quando apenas o ano é selecionado (sem mês)
        if ($request->filled('year') && !$request->filled('month')) {
            $year = (int) $request->year;
            $yearStart = sprintf('%04d-01-01', $year);
            $yearEnd = sprintf('%04d-12-31', $year);
            $nextYearStart = sprintf('%04d-01-01', $year + 1);
            
            // Garantir que a cota tenha pelo menos uma data dentro do ano especificado
            // A cota deve começar antes ou durante o ano E terminar durante ou após o ano
            $query->where(function($q) use ($yearStart, $yearEnd, $year, $nextYearStart) {
                // Verificar período principal - deve ter sobreposição com o ano
                $q->where(function($mainQuery) use ($yearStart, $yearEnd, $nextYearStart) {
                    $mainQuery->where('start_date', '<=', $yearEnd)
                              ->where('end_date', '>=', $yearStart)
                              // Garantir que pelo menos uma data esteja dentro do ano
                              ->where(function($yearQuery) use ($yearStart, $nextYearStart) {
                                  $yearQuery->where('start_date', '>=', $yearStart)
                                            ->where('start_date', '<', $nextYearStart)
                                            ->orWhere(function($endYearQuery) use ($yearStart, $nextYearStart) {
                                                $endYearQuery->where('end_date', '>=', $yearStart)
                                                             ->where('end_date', '<', $nextYearStart);
                                            });
                              });
                })
                // OU verificar fraction_details que contenham o ano
                ->orWhere(function($fractionQuery) use ($year) {
                    $fractionQuery->whereNotNull('fraction_details')
                                  ->where('fraction_details', 'like', '%' . $year . '%');
                });
            });
        }

        // Filtro por mês e ano - buscar cotas que tenham pelo menos um dia no mês/ano selecionado
        // Considera tanto a cota principal quanto semanas em fraction_details
        if ($request->filled('month')) {
            $month = (int) $request->month;
            
            if ($request->filled('year')) {
                // Se ano foi especificado, usar apenas esse ano
                $year = (int) $request->year;
                $monthStart = sprintf('%04d-%02d-01', $year, $month);
                $monthEnd = date('Y-m-t', strtotime($monthStart)); // Último dia do mês
                
                // Cota que começa antes ou no fim do mês E termina após ou no início do mês
                // OU que tenha semanas em fraction_details que se sobrepõem ao mês
                // E garantir que pelo menos uma data esteja no ano especificado
                $nextYearStart = sprintf('%04d-01-01', $year + 1);
                $yearStart = sprintf('%04d-01-01', $year);
                $query->where(function($q) use ($monthStart, $monthEnd, $year, $month, $yearStart, $nextYearStart) {
                    // Verificar período principal
                    $q->where(function($mainQuery) use ($monthStart, $monthEnd, $yearStart, $nextYearStart) {
                        $mainQuery->where('start_date', '<=', $monthEnd)
                                  ->where('end_date', '>=', $monthStart)
                                  // Garantir que pelo menos uma data esteja no ano especificado
                                  ->where(function($yearQuery) use ($yearStart, $nextYearStart) {
                                      $yearQuery->where('start_date', '>=', $yearStart)
                                                ->where('start_date', '<', $nextYearStart)
                                                ->orWhere(function($endYearQuery) use ($yearStart, $nextYearStart) {
                                                    $endYearQuery->where('end_date', '>=', $yearStart)
                                                                 ->where('end_date', '<', $nextYearStart);
                                                });
                                  });
                    })
                    // OU verificar semanas em fraction_details
                    ->orWhere(function($fractionQuery) use ($year, $month) {
                        $fractionQuery->whereNotNull('fraction_details')
                                      ->where(function($subQuery) use ($year, $month) {
                                          // Buscar por datas no formato YYYY-MM no JSON
                                          $monthStr = sprintf('%04d-%02d', $year, $month);
                                          $subQuery->where('fraction_details', 'like', '%' . $monthStr . '%');
                                      });
                    });
                });
            } else {
                // Se ano não foi especificado, usar ano atual e próximo (comportamento padrão)
                $currentYear = date('Y');
                $nextYear = $currentYear + 1;
                
                // Buscar cotas que se sobrepõem ao mês selecionado (ano atual ou próximo)
                $query->where(function($q) use ($month, $currentYear, $nextYear) {
                    // Mês no ano atual
                    $q->where(function($subQ) use ($month, $currentYear) {
                        $monthStart = sprintf('%04d-%02d-01', $currentYear, $month);
                        $monthEnd = date('Y-m-t', strtotime($monthStart)); // Último dia do mês
                        
                        // Cota que começa antes ou no fim do mês E termina após ou no início do mês
                        $subQ->where('start_date', '<=', $monthEnd)
                             ->where('end_date', '>=', $monthStart);
                    })
                    // Mês no próximo ano
                    ->orWhere(function($subQ) use ($month, $nextYear) {
                        $monthStart = sprintf('%04d-%02d-01', $nextYear, $month);
                        $monthEnd = date('Y-m-t', strtotime($monthStart)); // Último dia do mês
                        
                        // Cota que começa antes ou no fim do mês E termina após ou no início do mês
                        $subQ->where('start_date', '<=', $monthEnd)
                             ->where('end_date', '>=', $monthStart);
                    })
                    // OU verificar semanas em fraction_details para ambos os anos
                    ->orWhere(function($fractionQuery) use ($currentYear, $nextYear, $month) {
                        $fractionQuery->whereNotNull('fraction_details')
                                      ->where(function($subQuery) use ($currentYear, $nextYear, $month) {
                                          // Buscar por datas no formato YYYY-MM no JSON
                                          $monthStrCurrent = sprintf('-%02d', $month);
                                          $subQuery->where('fraction_details', 'like', '%' . $currentYear . $monthStrCurrent . '%')
                                                   ->orWhere('fraction_details', 'like', '%' . $nextYear . $monthStrCurrent . '%');
                                      });
                    });
                });
            }
        }

        // Filtro por período - verificar sobreposição de datas
        // Usa check_in/check_out do formulário ou start_date/end_date
        // Só aplica se não tiver filtro de mês (para evitar conflito)
        if (!$request->filled('month')) {
            $checkIn = $request->filled('check_in') ? $request->check_in : ($request->filled('start_date') ? $request->start_date : null);
            $checkOut = $request->filled('check_out') ? $request->check_out : ($request->filled('end_date') ? $request->end_date : null);
            
            if ($checkIn && $checkOut) {
                // Buscar cotas que se sobrepõem ao período desejado
                // Considera tanto a cota principal quanto semanas individuais em fraction_details
                $query->where(function($q) use ($checkIn, $checkOut) {
                    // Verificar sobreposição com período principal da cota
                    $q->where(function($mainQuery) use ($checkIn, $checkOut) {
                        $mainQuery->where('start_date', '<=', $checkOut)
                                  ->where('end_date', '>=', $checkIn);
                    })
                    // OU verificar se fraction_details contém períodos que se sobrepõem
                    ->orWhere(function($fractionQuery) use ($checkIn, $checkOut) {
                        $fractionQuery->whereNotNull('fraction_details')
                                      ->where(function($subQuery) use ($checkIn, $checkOut) {
                                          // Buscar por JSON que contenha períodos sobrepostos
                                          // Usar LIKE para buscar datas no JSON (compatível com SQLite e MySQL)
                                          $subQuery->where('fraction_details', 'like', '%"start":"' . $checkIn . '"%')
                                                   ->orWhere('fraction_details', 'like', '%"start":"' . $checkOut . '"%')
                                                   ->orWhere('fraction_details', 'like', '%"end":"' . $checkIn . '"%')
                                                   ->orWhere('fraction_details', 'like', '%"end":"' . $checkOut . '"%')
                                                   // Buscar também por períodos que contenham o range desejado
                                                   ->orWhere(function($rangeQuery) use ($checkIn, $checkOut) {
                                                       // Verificar se há algum período que começa antes ou no checkOut e termina após ou no checkIn
                                                       $rangeQuery->where('fraction_details', 'like', '%"start"%')
                                                                  ->where('fraction_details', 'like', '%"end"%');
                                                   });
                                      });
                    });
                });
            } elseif ($checkIn) {
                // Se só tem data de início, buscar cotas que terminam após essa data
                $query->where(function($q) use ($checkIn) {
                    $q->where('end_date', '>=', $checkIn)
                      ->orWhere(function($fractionQuery) use ($checkIn) {
                          $fractionQuery->whereNotNull('fraction_details')
                                        ->where('fraction_details', 'like', '%"end":"' . $checkIn . '"%')
                                        ->orWhere('fraction_details', 'like', '%"end_date":"' . $checkIn . '"%');
                      });
                });
            } elseif ($checkOut) {
                // Se só tem data de fim, buscar cotas que começam antes dessa data
                $query->where(function($q) use ($checkOut) {
                    $q->where('start_date', '<=', $checkOut)
                      ->orWhere(function($fractionQuery) use ($checkOut) {
                          $fractionQuery->whereNotNull('fraction_details')
                                        ->where('fraction_details', 'like', '%"start":"' . $checkOut . '"%')
                                        ->orWhere('fraction_details', 'like', '%"start_date":"' . $checkOut . '"%');
                      });
                });
            }
        }

        // Filtro por número de pessoas - busca exata ou maior/igual
        // Aceita tanto 'people' quanto 'guests' para compatibilidade
        $peopleValue = $request->filled('people') ? $request->people : ($request->filled('guests') ? $request->guests : null);
        if ($peopleValue) {
            $people = (int) $peopleValue;
            $query->where('number_of_guests', '>=', $people);
        }

        // Filtro por número de quartos - busca exata ou maior/igual
        if ($request->filled('rooms')) {
            $rooms = (int) $request->rooms;
            // Se for "6+", buscar >= 6, senão busca exata ou maior/igual
            if ($rooms == 6) {
                $query->where('number_of_rooms', '>=', 6);
            } else {
                $query->where('number_of_rooms', '>=', $rooms);
            }
        }

        // Filtro por duração de estadia (pernoites) - busca exata
        if ($request->filled('stay_duration')) {
            $nights = (int) $request->stay_duration;
            // Busca exata: se o usuário quer 7 pernoites, a diferença entre end_date e start_date deve ser exatamente 7 dias
            // Exemplo: check-in dia 1, check-out dia 8 = 7 pernoites (diferença de 7 dias)
            // Considera tanto a cota principal quanto frações em fraction_details
            $query->where(function($q) use ($nights) {
                // Verificar período principal
                $q->whereRaw('DATEDIFF(end_date, start_date) = ?', [$nights])
                  // OU verificar se fraction_details tem períodos com essa duração
                  ->orWhere(function($fractionQuery) use ($nights) {
                      $fractionQuery->whereNotNull('fraction_details')
                                    ->where(function($subQuery) use ($nights) {
                                        // Buscar por períodos que tenham exatamente essa duração
                                        // Usar LIKE para buscar no JSON (compatível)
                                        $subQuery->where('fraction_details', 'like', '%"number_of_days":' . ($nights + 1) . '%')
                                                 ->orWhere('fraction_details', 'like', '%"number_of_days":"' . ($nights + 1) . '"%');
                                    });
                  });
            });
        }

        // Filtro por sazonalidade - mapear valores do formulário para valores do banco
        if ($request->filled('seasonality')) {
            $seasonalityMap = [
                'baixa' => 'low',
                'media' => 'medium',
                'alta' => 'high',
                'altissima' => 'peak',
            ];
            
            $seasonalityValue = $request->seasonality;
            // Se o valor já estiver no formato do banco, usar diretamente
            if (in_array($seasonalityValue, ['low', 'medium', 'high', 'peak'])) {
                $query->where('seasonality', $seasonalityValue);
            } elseif (isset($seasonalityMap[$seasonalityValue])) {
                // Mapear do formato do formulário para o formato do banco
                $query->where('seasonality', $seasonalityMap[$seasonalityValue]);
            }
        }

        // Filtro por tipo de cota (fixa, flexivel, fixa_flexivel)
        if ($request->filled('quota_type')) {
            $quotaType = $request->quota_type;
            
            if ($quotaType === 'fixa') {
                // Cota fixa: 1 semana (weeks = 1) e não fracionada
                $query->where(function($q) {
                    $q->where('weeks', 1)
                      ->orWhere(function($q2) {
                          // Se não tiver weeks, calcular baseado nas datas (7 dias = 1 semana)
                          $q2->whereNull('weeks')
                             ->whereRaw('DATEDIFF(end_date, start_date) = 6'); // 7 dias = 6 noites
                      });
                });
            } elseif ($quotaType === 'flexivel') {
                // Cota flexível: múltiplas semanas (weeks > 1) ou fracionada
                $query->where(function($q) {
                    $q->where('weeks', '>', 1)
                      ->orWhere('is_fractioned', true)
                      ->orWhere(function($q2) {
                          // Se não tiver weeks, calcular baseado nas datas (> 7 dias)
                          $q2->whereNull('weeks')
                             ->whereRaw('DATEDIFF(end_date, start_date) > 6');
                      });
                });
            } elseif ($quotaType === 'fixa_flexivel') {
                // Cota fixa + flexível: aceita ambos os tipos
                // Não aplica filtro, aceita todas
            }
        }

        // Filtro por preço - apenas se não for troca
        // Regra:
        // - Se o usuário NÃO mexeu no slider (min = 0 e max = 250000), NÃO filtra por preço
        // - Só aplica filtro quando min > 0 ou max < 250000 (teto do slider em filters.blade.php)
        $transactionType = $request->filled('transaction_type') ? $request->transaction_type : 'rent';
        if ($transactionType !== 'exchange') {
            $priceMin = $request->input('price_min');
            $priceMax = $request->input('price_max');

            $hasCustomMin = $priceMin !== null && $priceMin !== '' && (float) $priceMin > 0;
            $hasCustomMax = $priceMax !== null && $priceMax !== '' && (float) $priceMax < 250000;

            if ($hasCustomMin || $hasCustomMax) {
                // Preço na cota OU em oferta de aluguel ativa (listagem pública)
                $query->where(function ($wq) use ($hasCustomMin, $hasCustomMax, $priceMin, $priceMax) {
                    $wq->where(function ($q) use ($hasCustomMin, $hasCustomMax, $priceMin, $priceMax) {
                        $q->whereNotNull('rental_price');
                        if ($hasCustomMin) {
                            $q->where('rental_price', '>=', (float) $priceMin);
                        }
                        if ($hasCustomMax) {
                            $q->where('rental_price', '<=', (float) $priceMax);
                        }
                    })->orWhereHas('rentalOffers', function ($q) use ($hasCustomMin, $hasCustomMax, $priceMin, $priceMax) {
                        $q->where('status', 'active')->whereNull('negotiated_at');
                        if ($hasCustomMin) {
                            $q->where('price', '>=', (float) $priceMin);
                        }
                        if ($hasCustomMax) {
                            $q->where('price', '<=', (float) $priceMax);
                        }
                    });
                });
            }
        }
        
        // Filtros de amenidades - buscar através do relacionamento com Hotel
        $amenityFilters = [
            'hidromassagem' => 'spa',
            'academia' => 'gym',
            'estacionamento_gratuito' => 'parking',
            'vista_mar' => 'ocean_view',
            'lareira' => 'fireplace',
            'adega' => 'wine_cellar',
            'area_kids' => 'kids_area',
            'area_trabalho' => 'business_center',
            'spa' => 'spa',
            'piscina' => 'pool',
            'wifi' => 'wifi',
            'breakfast' => 'breakfast',
        ];
        
        // Coletar todos os filtros de amenidades que precisam ser aplicados
        $amenityFiltersToApply = [];
        foreach ($amenityFilters as $requestKey => $amenityKey) {
            if ($request->filled($requestKey)) {
                $value = $request->input($requestKey);
                if ($value === '1' || $value === 1 || $value === 'true') {
                    $amenityFiltersToApply[] = ['key' => $amenityKey, 'type' => 'has'];
                } elseif ($value === '0' || $value === 0 || $value === 'false') {
                    $amenityFiltersToApply[] = ['key' => $amenityKey, 'type' => 'not_has'];
                }
            }
        }
        
        // Aplicar filtros de amenidades usando whereHas para fazer join com Hotel
        // Todos os filtros devem ser aplicados com AND (todos devem ser satisfeitos)
        // Agrupar todos os filtros em uma única query whereHas para melhor performance
        if (!empty($amenityFiltersToApply)) {
            $query->whereHas('hotel', function($hotelQuery) use ($amenityFiltersToApply) {
                foreach ($amenityFiltersToApply as $filter) {
                    $amenityKey = $filter['key'];
                    $type = $filter['type'];
                    
                    if ($type === 'has') {
                        // Buscar cotas cujo hotel tem essa amenidade
                        $hotelQuery->where(function($subQuery) use ($amenityKey) {
                            $subQuery->whereJsonContains('amenities', $amenityKey)
                                    ->orWhere('amenities', 'like', '%"' . $amenityKey . '"%')
                                    ->orWhere('amenities', 'like', "%'$amenityKey'%");
                        });
                    } else {
                        // Buscar cotas cujo hotel NÃO tem essa amenidade
                        $hotelQuery->where(function($subQuery) use ($amenityKey) {
                            $subQuery->whereNull('amenities')
                                    ->orWhere(function($notLikeQuery) use ($amenityKey) {
                                        $notLikeQuery->where('amenities', 'not like', '%"' . $amenityKey . '"%')
                                                   ->where('amenities', 'not like', "%'$amenityKey'%");
                                    });
                        });
                    }
                }
            });
        }

        if ($request->filled('sofa_mais')) {
            $this->applySofaMaisProfileFilter($query, $request->input('sofa_mais'));
        }

        // Filtro por tipo de transação - só aplicar se houver algum filtro ou se search=1
        // Se não houver filtros e não houver search=1, não aplicar filtro de transaction_type
        if ($hasAnyFilter && $request->filled('transaction_type')) {
            $transactionType = $request->transaction_type;
            
            if ($transactionType === 'rent') {
                // Alugar: cotas fracionadas ou inteiras para alugar
                // Cotas que têm 'rent' no allowed_uses
                $query->where(function($q) {
                    $q->whereJsonContains('allowed_uses', 'rent')
                      ->orWhere('is_exchange', false); // Fallback para compatibilidade
                });
            } elseif ($transactionType === 'exchange') {
                // Trocar: cotas fracionadas ou inteiras para troca
                // Cotas que têm 'exchange' no allowed_uses
                $query->where(function($q) {
                    $q->whereJsonContains('allowed_uses', 'exchange')
                      ->orWhere('is_exchange', true); // Fallback para compatibilidade
                });
            } elseif ($transactionType === 'sell') {
                // Vender: cotas inteiras para venda
                // Cotas que têm 'sell' no allowed_uses E não são fracionadas
                $query->where(function($q) {
                    $q->whereJsonContains('allowed_uses', 'sell')
                      ->where('is_fractioned', false);
                });
            } elseif ($transactionType === 'buy') {
                // Comprar: cotas inteiras para compra
                // Cotas que têm 'buy' no allowed_uses E não são fracionadas
                $query->where(function($q) {
                    $q->whereJsonContains('allowed_uses', 'buy')
                      ->where('is_fractioned', false);
                });
            }
        } elseif ($hasAnyFilter) {
            // Se não especificado mas há filtros, padrão é 'rent'
            $query->where(function($q) {
                $q->whereJsonContains('allowed_uses', 'rent')
                  ->orWhere('is_exchange', false);
            });
        }
        // Se não houver filtros, não aplicar filtro de transaction_type (já retornará vazio acima)

        // Só listar cotas com oferta pública (aluguel / troca / venda), conforme o tipo da busca
        $effectiveTx = $request->filled('transaction_type') ? (string) $request->transaction_type : 'rent';
        if ($effectiveTx === 'rental') {
            $effectiveTx = 'rent';
        }
        if ($effectiveTx === 'purchase') {
            $effectiveTx = 'buy';
        }
        if ($effectiveTx === 'exchange') {
            $query->whereHasActiveExchangeListing();
        } elseif (in_array($effectiveTx, ['sell', 'buy'], true)) {
            $query->whereHasActiveSaleListing();
        } else {
            $query->whereHasActiveRentalListing();
        }

        $query->with(['rentalOffers', 'exchangeOffers', 'saleOffers']);

        $quotas = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Adicionar badge baseado em dias e verificar favoritos
        $favoriteIds = collect();
        if (Auth::check()) {
            $favoriteIds = \App\Models\FavoriteListItem::whereHas('favoriteList', function($q) {
                $q->where('user_id', Auth::id());
            })->pluck('quota_id');
        }
        
        foreach ($quotas as $quota) {
            $daysSinceCreation = now()->diffInDays($quota->created_at);
            if ($daysSinceCreation <= 8) {
                $quota->badge = 'Nova';
                $quota->badge_color = 'success';
            } elseif ($daysSinceCreation <= 15) {
                $quota->badge = 'Recente';
                $quota->badge_color = 'info';
            } else {
                $quota->badge = null;
            }
            $quota->is_favorite = $favoriteIds->contains($quota->id);
        }

        $wishlistIds = $this->wishlistQuotaIdsForUser();

        return view('quotas.index', compact('quotas', 'profile', 'favoriteIds', 'wishlistIds'));
    }

    /**
     * IDs de cotas marcadas como desejadas (persistido no banco).
     */
    private function wishlistQuotaIdsForUser(): \Illuminate\Support\Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return Auth::user()->wishlistQuotas()->pluck('quotas.id');
    }

    /**
     * Show the form for creating a new quota.
     */
    public function create()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        // Check if user can publish quotas
        $config = $profile->getProfileConfig();
        if (!$config['can_publish']) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu perfil não permite publicar cotas.');
        }

        // Buscar hotéis para o select
        $hotels = Hotel::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Buscar taxas de êxito para cada perfil (para exibir valores de fracionamento)
        $successFees = [
            'curioso' => \App\Models\SuccessFee::getActiveFeesForProfile('curioso'),
            'inteligente' => \App\Models\SuccessFee::getActiveFeesForProfile('inteligente'),
            'sabio' => \App\Models\SuccessFee::getActiveFeesForProfile('sabio'),
        ];

        $quota = null;

        return view('quotas.create', compact('profile', 'hotels', 'successFees', 'quota'));
    }

    /**
     * Store a newly created quota.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        // Check if user can publish quotas
        $config = $profile->getProfileConfig();
        if (!$config['can_publish']) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu perfil não permite publicar cotas.');
        }

        $hotelId = $request->input('owner_hotel_id') ?: $request->input('gestor_hotel_id');
        $hotelModel = $hotelId ? Hotel::find($hotelId) : null;
        if ($hotelModel && ! $request->filled('hotel_name')) {
            $request->merge(['hotel_name' => $hotelModel->name]);
        }

        $rules = [
            'hotel_name' => 'required|string|max:255',
            'owner_hotel_id' => 'nullable|exists:hotels,id',
            'gestor_hotel_id' => 'nullable|exists:hotels,id',
            'fraction_details_json' => 'nullable|string',
            'is_exchange' => 'boolean',
        ];

        // Se o usuário está informando dados do perfil (has_quota = 1), validar campos relacionados
        // Mas apenas se esses campos estiverem presentes no request (opcional para criação de cota)
        if ($request->has('has_quota') && $request->has_quota == '1') {
            // Verificar se o hotel está operacional (se não estiver, não validar campos adicionais)
            $hotelOperational = $request->has('hotel_operational') && $request->hotel_operational == '1';
            
            if ($hotelOperational && $request->has('owner_quota_observations')) {
                // Validar campos básicos do proprietário apenas se estiverem presentes
                $rules['owner_quota_observations'] = 'nullable|string|max:1000';
                
                // Validar campos de quartos se owner_quota_rooms foi informado E o valor é maior que 0
                if ($request->has('owner_quota_rooms') && $request->owner_quota_rooms) {
                    $numRooms = (int) $request->owner_quota_rooms;
                    if ($numRooms > 0) {
                        // Verificar se os campos dos quartos foram enviados (indicando que foram criados dinamicamente)
                        $roomsConfigVisible = $request->has('owner_room_1_suite') || 
                                            $request->has('owner_room_1_double_bed') || 
                                            $request->has('owner_room_1_people');
                        
                        if ($roomsConfigVisible) {
                            for ($i = 1; $i <= $numRooms; $i++) {
                                $rules["owner_room_{$i}_suite"] = 'required|in:0,1';
                                $rules["owner_room_{$i}_double_bed"] = 'required|in:0,1,2';
                                $rules["owner_room_{$i}_single_bed"] = 'required|in:0,1,2,3,4,5';
                                $rules["owner_room_{$i}_sofa_bed"] = 'required|in:0,1,2';
                                $rules["owner_room_{$i}_bunk_bed"] = 'required|in:0,1,2,3,4,5';
                                $rules["owner_room_{$i}_people"] = 'required|in:1,2,3,4,5,6,7,8,9,10';
                            }
                        }
                    }
                }
            }
        }

        $messages = [
            'hotel_name.required' => 'O nome do hotel é obrigatório.',
        ];

        // Adicionar mensagens de erro para os campos dos quartos
        if ($request->has('has_quota') && $request->has_quota == '1' && $request->has('owner_quota_rooms') && $request->owner_quota_rooms) {
            $numRooms = (int) $request->owner_quota_rooms;
            if ($numRooms > 0) {
                for ($i = 1; $i <= $numRooms; $i++) {
                    $messages["owner_room_{$i}_suite.required"] = "O campo Suíte é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_double_bed.required"] = "O campo Cama de Casal é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_single_bed.required"] = "O campo Cama de Solteiro é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_sofa_bed.required"] = "O campo Sofá Cama é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_bunk_bed.required"] = "O campo Beliche é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_people.required"] = "O campo Pessoas é obrigatório para o Quarto {$i}.";
                }
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        [$startDate, $endDate] = $this->deriveQuotaDatesFromFractionJson($request->input('fraction_details_json'));
        $location = $this->deriveLocationFromHotel($hotelModel);
        $fractionDetails = $this->decodeFractionDetailsJson($request->input('fraction_details_json'));
        $fractionType = is_array($fractionDetails) ? ($fractionDetails['fraction_type'] ?? null) : null;
        $isFractioned = (bool) ($fractionType && $fractionType !== '7');

        $observations = $request->input('owner_quota_observations')
            ?: $request->input('gestor_quota_observations');

        $data = [
            'user_id' => $user->id,
            'hotel_name' => $request->hotel_name,
            'location' => $location,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'number_of_guests' => $this->deriveGuestCountFromWizardRequest($request),
            'is_exchange' => false,
            'observations' => $observations,
            'rental_price' => null,
            'contract_photo_path' => '',
            'is_fractioned' => $isFractioned,
            'fraction_details' => $fractionDetails,
        ];

        $data = array_merge($data, $this->buildWizardQuotaAttributes($request));

        if ($request->hasFile('contract_photo')) {
            $data['contract_photo_path'] = $request->file('contract_photo')->store('contracts', 'public');
        }

        $quota = Quota::create($data);

        $this->syncProfileFromQuotaWizard($profile, $request);

        return redirect()->route('quotas.show', $quota)
            ->with('success', 'Cota publicada com sucesso!');
    }

    /**
     * Display the specified quota.
     */
    public function show(Request $request, Quota $quota)
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        $quota->load(['user', 'rentalOffers', 'exchangeOffers', 'saleOffers']);
        // Resolve hotel functioning (if available)
        $hotel = null;
        $hotelFirstImage = null;
        if (!empty($quota->hotel_name)) {
            $hotel = Hotel::where('name', $quota->hotel_name)->first();
            if ($hotel && $hotel->images && is_array($hotel->images) && count($hotel->images) > 0) {
                $hotelFirstImage = asset('storage/' . $hotel->images[0]);
            }
        }
        $hotelInoperant = $hotel ? !$hotel->is_functioning : false;
        
        // Get transaction type from request (rent, exchange, buy)
        $transactionType = $request->input('transaction_type', 'rent');
        
        return view('quotas.show', compact('quota', 'profile', 'hotelInoperant', 'hotel', 'hotelFirstImage', 'transactionType'));
    }

    /**
     * Show the form for editing the specified quota (mesmo fluxo em etapas de "Cadastrar nova cota").
     */
    public function edit(Quota $quota)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if ($quota->user_id !== $user->id) {
            return redirect()->route('quotas.index')
                ->with('error', 'Você não tem permissão para editar esta cota.');
        }

        $config = $profile->getProfileConfig();
        if (! $config['can_publish']) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu perfil não permite publicar cotas.');
        }

        $hotels = Hotel::where('is_active', true)
            ->orderBy('name')
            ->get();

        $successFees = [
            'curioso' => \App\Models\SuccessFee::getActiveFeesForProfile('curioso'),
            'inteligente' => \App\Models\SuccessFee::getActiveFeesForProfile('inteligente'),
            'sabio' => \App\Models\SuccessFee::getActiveFeesForProfile('sabio'),
        ];

        return view('quotas.create', compact('profile', 'hotels', 'successFees', 'quota'));
    }

    /**
     * Update quota a partir do wizard (mesmas regras do store).
     */
    public function update(Request $request, Quota $quota)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if ($quota->user_id !== $user->id) {
            return redirect()->route('quotas.index')
                ->with('error', 'Você não tem permissão para editar esta cota.');
        }

        $config = $profile->getProfileConfig();
        if (! $config['can_publish']) {
            return redirect()->route('dashboard')
                ->with('error', 'Seu perfil não permite publicar cotas.');
        }

        // Na edição, tipo de posse, documentos de posse/autorização, status e usos permitidos não podem ser alterados pelo request.
        $profileHasQuotaIsGestor = in_array((int) ($profile->has_quota ?? 1), [2, 3], true);
        $request->merge([
            'has_quota' => $profileHasQuotaIsGestor ? '2' : '1',
        ]);
        if ($profileHasQuotaIsGestor) {
            $guses = $profile->gestor_allowed_uses ?? $quota->allowed_uses ?? [];
            $request->merge([
                'gestor_allowed_uses' => is_array($guses) ? array_values(array_filter($guses)) : [],
                'gestor_quota_status' => $profile->gestor_quota_status ?? '',
            ]);
        } else {
            $uses = $profile->allowed_uses ?? $quota->allowed_uses ?? [];
            $request->merge([
                'allowed_uses' => is_array($uses) ? array_values(array_filter($uses)) : [],
                'quota_status' => $profile->quota_status ?? '',
            ]);
        }

        $hotelId = $request->input('owner_hotel_id') ?: $request->input('gestor_hotel_id');
        $hotelModel = $hotelId ? Hotel::find($hotelId) : null;
        if ($hotelModel && ! $request->filled('hotel_name')) {
            $request->merge(['hotel_name' => $hotelModel->name]);
        }

        $rules = [
            'hotel_name' => 'required|string|max:255',
            'owner_hotel_id' => 'nullable|exists:hotels,id',
            'gestor_hotel_id' => 'nullable|exists:hotels,id',
            'fraction_details_json' => 'nullable|string',
            'is_exchange' => 'boolean',
        ];

        if ($request->has('has_quota') && $request->has_quota == '1') {
            $hotelOperational = $request->has('hotel_operational') && $request->hotel_operational == '1';

            if ($hotelOperational && $request->has('owner_quota_observations')) {
                $rules['owner_quota_observations'] = 'nullable|string|max:1000';

                if ($request->has('owner_quota_rooms') && $request->owner_quota_rooms) {
                    $numRooms = (int) $request->owner_quota_rooms;
                    if ($numRooms > 0) {
                        $roomsConfigVisible = $request->has('owner_room_1_suite')
                            || $request->has('owner_room_1_double_bed')
                            || $request->has('owner_room_1_people');

                        if ($roomsConfigVisible) {
                            for ($i = 1; $i <= $numRooms; $i++) {
                                $rules["owner_room_{$i}_suite"] = 'required|in:0,1';
                                $rules["owner_room_{$i}_double_bed"] = 'required|in:0,1,2';
                                $rules["owner_room_{$i}_single_bed"] = 'required|in:0,1,2,3,4,5';
                                $rules["owner_room_{$i}_sofa_bed"] = 'required|in:0,1,2';
                                $rules["owner_room_{$i}_bunk_bed"] = 'required|in:0,1,2,3,4,5';
                                $rules["owner_room_{$i}_people"] = 'required|in:1,2,3,4,5,6,7,8,9,10';
                            }
                        }
                    }
                }
            }
        }

        $messages = [
            'hotel_name.required' => 'O nome do hotel é obrigatório.',
        ];

        if ($request->has('has_quota') && $request->has_quota == '1' && $request->has('owner_quota_rooms') && $request->owner_quota_rooms) {
            $numRooms = (int) $request->owner_quota_rooms;
            if ($numRooms > 0) {
                for ($i = 1; $i <= $numRooms; $i++) {
                    $messages["owner_room_{$i}_suite.required"] = "O campo Suíte é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_double_bed.required"] = "O campo Cama de Casal é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_single_bed.required"] = "O campo Cama de Solteiro é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_sofa_bed.required"] = "O campo Sofá Cama é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_bunk_bed.required"] = "O campo Beliche é obrigatório para o Quarto {$i}.";
                    $messages["owner_room_{$i}_people.required"] = "O campo Pessoas é obrigatório para o Quarto {$i}.";
                }
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        [$startDate, $endDate] = $this->deriveQuotaDatesFromFractionJson($request->input('fraction_details_json'));
        $location = $this->deriveLocationFromHotel($hotelModel);
        $fractionDetails = $this->decodeFractionDetailsJson($request->input('fraction_details_json'));
        $fractionType = is_array($fractionDetails) ? ($fractionDetails['fraction_type'] ?? null) : null;
        $isFractioned = (bool) ($fractionType && $fractionType !== '7');

        $observations = $request->input('owner_quota_observations')
            ?: $request->input('gestor_quota_observations');

        $data = [
            'hotel_name' => $request->hotel_name,
            'location' => $location,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'number_of_guests' => $this->deriveGuestCountFromWizardRequest($request),
            'is_exchange' => false,
            'observations' => $observations,
            'rental_price' => null,
            'is_fractioned' => $isFractioned,
            'fraction_details' => $fractionDetails,
        ];

        $data = array_merge($data, $this->buildWizardQuotaAttributes($request));

        if ($request->hasFile('contract_photo')) {
            if ($quota->contract_photo_path) {
                Storage::disk('public')->delete($quota->contract_photo_path);
            }
            $data['contract_photo_path'] = $request->file('contract_photo')->store('contracts', 'public');
        }

        $quota->update($data);

        $this->syncProfileFromQuotaWizard($profile, $request);

        return redirect()->route('quotas.my')
            ->with('success', 'Cota atualizada com sucesso!');
    }

    /**
     * Show transfer ownership form.
     */
    public function showTransferOwnership(Quota $quota)
    {
        $user = Auth::user();
        if ($quota->user_id !== $user->id) {
            return redirect()->route('quotas.show', $quota)
                ->with('error', 'Você não tem permissão para transferir esta cota.');
        }
        return view('quotas.transfer', compact('quota'));
    }

    /**
     * Handle ownership transfer submission.
     */
    public function transferOwnershipAction(Request $request, Quota $quota)
    {
        $user = Auth::user();
        if ($quota->user_id !== $user->id) {
            return redirect()->route('quotas.show', $quota)
                ->with('error', 'Você não tem permissão para transferir esta cota.');
        }

        $request->validate([
            'new_owner_email' => 'required|email|exists:users,email',
        ], [
            'new_owner_email.required' => 'Informe o e-mail do novo titular.',
            'new_owner_email.email' => 'E-mail inválido.',
            'new_owner_email.exists' => 'Usuário não encontrado pelo e-mail informado.',
        ]);

        $newOwner = User::where('email', $request->new_owner_email)->first();
        if ($newOwner->id === $user->id) {
            return redirect()->back()->with('error', 'Você não pode transferir para si mesmo.');
        }

        // Get hotel category for fee calculation
        $hotel = Hotel::where('name', $quota->hotel_name)->first();
        $category = $hotel ? $hotel->category : 'B'; // Default to B if not found
        $transferFee = $this->getTransferFeeByCategory($category);

        // Create transfer transaction
        $transaction = QuotaTransaction::create([
            'quota_id' => $quota->id,
            'renter_id' => $newOwner->id,
            'owner_id' => $user->id,
            'transaction_type' => 'ownership_transfer',
            'total_amount' => $transferFee,
            'owner_amount' => 0,
            'platform_fee' => $transferFee,
            'status' => 'pending',
            'payment_method' => 'transfer_fee',
            'payment_status' => 'pending',
            'transaction_date' => now(),
        ]);

        // Create digital contract for transfer
        DigitalContract::create([
            'transaction_id' => $transaction->id,
            'contract_type' => 'ownership_transfer_agreement',
            'contract_content' => $this->generateTransferContract($transaction, $quota, $user, $newOwner, $transferFee),
            'is_completed' => false,
        ]);

        // Perform transfer and cancel active offers
        $quota->transferOwnership($newOwner->id);

        // Send email notifications
        $this->sendTransferNotifications($user, $newOwner, $quota, $transferFee);

        return redirect()->route('quotas.show', $quota)
            ->with('success', 'Titularidade transferida com sucesso. Taxa de transferência: R$ ' . number_format($transferFee, 2, ',', '.') . '. Ofertas ativas foram canceladas.');
    }

    /**
     * Get transfer fee by hotel category.
     */
    private function getTransferFeeByCategory($category)
    {
        $fees = [
            'B' => 1000.00,    // Bom
            'MB' => 1500.00,   // Muito Bom
            'OT' => 2000.00,   // Ótimo
            'IN' => 3000.00,   // Incrível
            'UN' => 5000.00,   // Único
        ];

        return $fees[$category] ?? 1000.00;
    }

    /**
     * Generate transfer contract content.
     */
    private function generateTransferContract($transaction, $quota, $oldOwner, $newOwner, $fee)
    {
        return "CONTRATO DE TRANSFERÊNCIA DE TITULARIDADE DE COTA HOTELEIRA\n\n" .
               "Transferidor (Antigo Titular): {$oldOwner->name}\n" .
               "E-mail: {$oldOwner->email}\n\n" .
               "Transfere para (Novo Titular): {$newOwner->name}\n" .
               "E-mail: {$newOwner->email}\n\n" .
               "Cota Transferida:\n" .
               "Hotel: {$quota->hotel_name}\n" .
               "Localização: {$quota->location}\n" .
               "Período: {$quota->start_date} a {$quota->end_date}\n" .
               "Número de Hóspedes: {$quota->number_of_guests}\n\n" .
               "Taxa de Transferência: R$ " . number_format($fee, 2, ',', '.') . "\n" .
               "Data da Transferência: " . now()->format('d/m/Y H:i') . "\n\n" .
               "Termos e Condições:\n" .
               "1. O transferidor declara ser o legítimo proprietário da cota.\n" .
               "2. A transferência é irrevogável e definitiva.\n" .
               "3. O novo titular assume todos os direitos e obrigações da cota.\n" .
               "4. Ofertas ativas foram automaticamente canceladas.\n" .
               "5. A taxa de transferência é devida à plataforma.\n\n" .
               "Este contrato é válido e vinculante para ambas as partes.";
    }

    /**
     * Send transfer notifications.
     */
    private function sendTransferNotifications($oldOwner, $newOwner, $quota, $fee)
    {
        // Send to old owner
        Mail::raw(
            "Olá {$oldOwner->name},\n\n" .
            "A transferência de titularidade da sua cota foi realizada com sucesso:\n\n" .
            "Hotel: {$quota->hotel_name}\n" .
            "Período: {$quota->start_date} a {$quota->end_date}\n" .
            "Novo titular: {$newOwner->name} ({$newOwner->email})\n" .
            "Taxa cobrada: R$ " . number_format($fee, 2, ',', '.') . "\n\n" .
            "Atenciosamente,\nEquipe Cota Brasilis",
            function ($message) use ($oldOwner) {
                $message->to($oldOwner->email)
                        ->subject('Transferência de Cota Realizada - Cota Brasilis');
            }
        );

        // Send to new owner
        Mail::raw(
            "Olá {$newOwner->name},\n\n" .
            "Você recebeu a titularidade de uma cota hoteleira:\n\n" .
            "Hotel: {$quota->hotel_name}\n" .
            "Período: {$quota->start_date} a {$quota->end_date}\n" .
            "Transferida por: {$oldOwner->name} ({$oldOwner->email})\n" .
            "Taxa paga: R$ " . number_format($fee, 2, ',', '.') . "\n\n" .
            "Agora você pode gerenciar esta cota em sua conta.\n\n" .
            "Atenciosamente,\nEquipe Cota Brasilis",
            function ($message) use ($newOwner) {
                $message->to($newOwner->email)
                        ->subject('Nova Cota Recebida - Cota Brasilis');
            }
        );
    }

    /**
     * Remove the specified quota.
     */
    public function destroy(Quota $quota)
    {
        $user = Auth::user();
        
        // Check if user owns the quota
        if ($quota->user_id !== $user->id) {
            return redirect()->route('quotas.index')
                ->with('error', 'Você não tem permissão para excluir esta cota.');
        }

        // Delete contract photo
        if ($quota->contract_photo_path) {
            Storage::disk('public')->delete($quota->contract_photo_path);
        }

        $quota->delete();

        return redirect()->route('quotas.index')
            ->with('success', 'Cota excluída com sucesso!');
    }

    /**
     * Show user's own quotas.
     */
    public function myQuotas()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ofertado: cotas do usuário e ofertas de aluguel
        $offeredQuotas = $user->quotas()->orderBy('created_at', 'desc')->get();
        $offeredRentalOffers = RentalOffer::where('user_id', $user->id)
            ->with('quota')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Solicitado: transações onde o usuário é o locatário
        $requestedTransactions = QuotaTransaction::where('renter_id', $user->id)
            ->with(['quota', 'owner'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Separar por status - Ofertado
        $offeredExitosas = collect();
        $offeredEmAndamento = collect();
        $offeredInspiradas = collect();
        
        foreach ($offeredQuotas as $quota) {
            if (in_array($quota->status, [Quota::STATUS_RENTED, Quota::STATUS_EXCHANGED], true)) {
                // Cotas já alugadas ou trocadas = exitosas
                $offeredExitosas->push($quota);
            } elseif (in_array($quota->status, [Quota::STATUS_AVAILABLE, Quota::STATUS_NEGOTIATING], true)) {
                // Disponíveis ou em negociação = em andamento
                $offeredEmAndamento->push($quota);
            } elseif ($quota->status === Quota::STATUS_CANCELLED) {
                // Canceladas = inspiradas
                $offeredInspiradas->push($quota);
            }
        }
        
        foreach ($offeredRentalOffers as $offer) {
            if ($offer->status === 'completed') {
                $offeredExitosas->push($offer);
            } elseif (in_array($offer->status, ['active', 'pending'])) {
                $offeredEmAndamento->push($offer);
            } elseif ($offer->status === 'cancelled') {
                $offeredInspiradas->push($offer);
            }
        }
        
        // Separar por status - Solicitado (usando os novos status da QuotaTransaction)
        $requestedExitosas = $requestedTransactions->where('status', QuotaTransaction::STATUS_COMPLETED);

        $requestedEmAndamento = $requestedTransactions->whereIn('status', [
            QuotaTransaction::STATUS_PENDING,
            QuotaTransaction::STATUS_NEGOTIATING,
            QuotaTransaction::STATUS_PAYMENT_PENDING,
            QuotaTransaction::STATUS_DOCUMENT_PENDING,
        ]);

        $requestedInspiradas = $requestedTransactions->whereIn('status', [
            QuotaTransaction::STATUS_CANCELLED,
            QuotaTransaction::STATUS_EXPIRED,
        ]);
        
        return view('quotas.my', compact(
            'offeredExitosas',
            'offeredEmAndamento',
            'offeredInspiradas',
            'requestedExitosas',
            'requestedEmAndamento',
            'requestedInspiradas'
        ));
    }

    /**
     * Get amenities from quota (not from hotel)
     */
    private function getQuotaAmenities($quota)
    {
        $amenities = [];
        $profile = $quota->user->profile ?? null;
        
        if (!$profile) {
            return $amenities;
        }
        
        // Verificar se é proprietário ou gestor
        $isOwner = $profile->is_quota_owner ?? false;
        $isGestor = $profile->is_authorized_user ?? false;
        
        if ($isOwner) {
            // Comodidades básicas do proprietário (campos booleanos diretos)
            if ($profile->owner_quota_jacuzzi ?? false) {
                $amenities[] = 'hidromassagem';
            }
            if ($profile->owner_quota_breakfast ?? false) {
                $amenities[] = 'cafe_da_manha';
            }
            if ($profile->owner_quota_sofa_mais ?? false) {
                $amenities[] = 'sofa_mais';
            }
            if ($profile->owner_quota_parking ?? false) {
                $amenities[] = 'estacionamento_gratuito';
            }
            if ($profile->owner_quota_kitchen ?? false) {
                $amenities[] = 'cozinha';
            }
        } elseif ($isGestor) {
            // Comodidades básicas do gestor (campos booleanos diretos)
            if ($profile->gestor_quota_jacuzzi ?? false) {
                $amenities[] = 'hidromassagem';
            }
            if ($profile->gestor_quota_breakfast ?? false) {
                $amenities[] = 'cafe_da_manha';
            }
            if ($profile->gestor_quota_sofa_mais ?? false) {
                $amenities[] = 'sofa_mais';
            }
            if ($profile->gestor_quota_parking ?? false) {
                $amenities[] = 'estacionamento_gratuito';
            }
            if ($profile->gestor_quota_kitchen ?? false) {
                $amenities[] = 'cozinha';
            }
        }
        
        // Buscar comodidades adicionais do quota_details se existir
        $quotaDetails = $profile->quota_details ?? [];
        if (is_array($quotaDetails)) {
            // Mapeamento de campos do quota_details para chaves de comodidades
            $amenityFields = [
                'wifi' => 'wifi',
                'owner_quota_wifi' => 'wifi',
                'gestor_quota_wifi' => 'wifi',
                'area_kids' => 'area_kids',
                'owner_quota_area_kids' => 'area_kids',
                'gestor_quota_area_kids' => 'area_kids',
                'academia' => 'academia',
                'owner_quota_academia' => 'academia',
                'gestor_quota_academia' => 'academia',
                'piscina' => 'piscina',
                'spa' => 'spa',
                'restaurante' => 'restaurante',
                'bar' => 'bar',
                'centro_negocios' => 'centro_negocios',
                'business_center' => 'centro_negocios',
                'owner_quota_area_trabalho' => 'area_trabalho',
                'gestor_quota_area_trabalho' => 'area_trabalho',
                'lareira' => 'lareira',
                'owner_quota_lareira' => 'lareira',
                'gestor_quota_lareira' => 'lareira',
                'adega' => 'adega',
                'owner_quota_adega' => 'adega',
                'gestor_quota_adega' => 'adega',
                'vista_mar' => 'vista_mar',
                'owner_quota_vista_mar' => 'vista_mar',
                'gestor_quota_vista_mar' => 'vista_mar',
                'owner_quota_sofa_mais' => 'sofa_mais',
                'gestor_quota_sofa_mais' => 'sofa_mais',
                'sofa_mais' => 'sofa_mais',
            ];
            
            foreach ($amenityFields as $field => $amenityKey) {
                // Verificar se o campo existe e tem valor verdadeiro (1, '1', true)
                $value = $quotaDetails[$field] ?? null;
                if ($value && ($value === 1 || $value === '1' || $value === true)) {
                    if (!in_array($amenityKey, $amenities)) {
                        $amenities[] = $amenityKey;
                    }
                }
            }
        }
        
        return $amenities;
    }

    /**
     * @return array{0: string, 1: string} [start Y-m-d, end Y-m-d]
     */
    protected function deriveQuotaDatesFromFractionJson(?string $json): array
    {
        $defaultStart = Carbon::now()->addDay()->startOfDay();
        $defaultEnd = Carbon::now()->addDays(8)->startOfDay();

        if (! $json) {
            return [$defaultStart->format('Y-m-d'), $defaultEnd->format('Y-m-d')];
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [$defaultStart->format('Y-m-d'), $defaultEnd->format('Y-m-d')];
        }

        $starts = [];
        $ends = [];
        foreach ($data['fraction_weeks'] ?? [] as $week) {
            if (! is_array($week)) {
                continue;
            }
            foreach ($week['periods'] ?? [] as $period) {
                if (! is_array($period)) {
                    continue;
                }
                if (! empty($period['start'])) {
                    $starts[] = $period['start'];
                }
                if (! empty($period['end'])) {
                    $ends[] = $period['end'];
                }
            }
        }

        if ($starts === [] || $ends === []) {
            return [$defaultStart->format('Y-m-d'), $defaultEnd->format('Y-m-d')];
        }

        sort($starts);
        sort($ends);

        try {
            $startC = Carbon::parse($starts[0])->startOfDay();
            $endC = Carbon::parse($ends[count($ends) - 1])->startOfDay();
        } catch (\Throwable $e) {
            return [$defaultStart->format('Y-m-d'), $defaultEnd->format('Y-m-d')];
        }

        if ($endC->lte($startC)) {
            return [$defaultStart->format('Y-m-d'), $defaultEnd->format('Y-m-d')];
        }

        return [$startC->format('Y-m-d'), $endC->format('Y-m-d')];
    }

    protected function deriveLocationFromHotel(?Hotel $hotel): string
    {
        if (! $hotel) {
            return '—';
        }

        $fromCityState = trim(implode(', ', array_filter([$hotel->city, $hotel->state])));

        if ($fromCityState !== '') {
            return $fromCityState;
        }

        return $hotel->location ?: '—';
    }

    protected function decodeFractionDetailsJson(?string $json): ?array
    {
        if (! $json) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function deriveGuestCountFromWizardRequest(Request $request): int
    {
        $max = 0;
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^(owner|gestor)_room_(\d+)_people$/', (string) $key) && $value !== '' && $value !== null) {
                $max = max($max, (int) $value);
            }
        }

        return $max >= 1 ? min(10, $max) : 2;
    }

    /**
     * Campos do wizard de cota aplicados à linha em `quotas` (sazonalidade, semanas, etc.).
     */
    protected function buildWizardQuotaAttributes(Request $request): array
    {
        $out = [];

        $weeksFromForm = (int) ($request->input('owner_quota_weeks_count') ?: $request->input('gestor_quota_weeks_count') ?: 0);
        if ($weeksFromForm >= 1) {
            $out['weeks'] = min(6, $weeksFromForm);
        }

        $roomsInput = $request->input('owner_quota_rooms') ?? $request->input('gestor_quota_rooms');
        if ($roomsInput !== null && $roomsInput !== '') {
            $out['number_of_rooms'] = max(1, min(10, (int) $roomsInput));
        }

        $seasonInput = $request->input('owner_quota_seasonality') ?? $request->input('gestor_quota_seasonality');
        if (is_string($seasonInput) && $seasonInput !== '') {
            $out['seasonality'] = $this->normalizeSeasonalityForQuotaColumn($seasonInput);
        }

        $typeInput = $request->input('owner_quota_type') ?? $request->input('gestor_quota_type');
        if (is_string($typeInput) && $typeInput !== '') {
            $out['quota_type'] = $typeInput;
        }

        $allowedUses = $request->input('allowed_uses', $request->input('gestor_allowed_uses', []));
        if (is_array($allowedUses) && $allowedUses !== []) {
            $out['allowed_uses'] = array_values(array_filter($allowedUses));
        }

        if ($request->has('has_quota')) {
            $out['is_owner'] = $request->input('has_quota') === '1';
        }

        return $out;
    }

    /**
     * Valores aceitos na coluna enum `quotas.seasonality`: low, medium, high, peak.
     */
    protected function normalizeSeasonalityForQuotaColumn(string $seasonInput): string
    {
        $s = strtolower(trim($seasonInput));

        return match ($s) {
            'baixa', 'low' => 'low',
            'media', 'medium' => 'medium',
            'alta', 'high' => 'high',
            'pico', 'peak', 'altissima', 'altíssima' => 'peak',
            default => in_array($s, ['low', 'medium', 'high', 'peak'], true) ? $s : 'medium',
        };
    }

    /**
     * Persiste comodidades e sazonalidade no perfil / quota_details para as telas que leem o cadastro pelo UserProfile.
     */
    protected function syncProfileFromQuotaWizard(UserProfile $profile, Request $request): void
    {
        $has = $request->input('has_quota');
        if (! in_array($has, ['1', '2'], true)) {
            return;
        }

        $quotaDetails = is_array($profile->quota_details) ? $profile->quota_details : [];

        if ($has === '1') {
            foreach ([
                'owner_quota_balcony',
                'owner_quota_vista_mar',
                'owner_quota_spa',
                'owner_quota_piscina',
                'owner_quota_academia',
                'owner_quota_lareira',
                'owner_quota_adega',
                'owner_quota_area_kids',
                'owner_quota_area_trabalho',
                'owner_quota_wifi',
                'owner_quota_hidromassagem',
            ] as $field) {
                if ($request->exists($field)) {
                    $quotaDetails[$field] = (int) $request->input($field);
                }
            }

            if ($request->has('owner_hotel_id')) {
                $profile->owner_hotel_id = $request->input('owner_hotel_id');
            }
            if ($request->has('owner_quota_rooms')) {
                $profile->owner_quota_rooms = $request->input('owner_quota_rooms');
            }
            if ($request->has('owner_quota_size')) {
                $profile->owner_quota_size = $request->input('owner_quota_size');
            }
            if ($request->has('owner_quota_seasonality')) {
                $profile->owner_quota_seasonality = $request->input('owner_quota_seasonality');
            }
            if ($request->has('owner_quota_type')) {
                $profile->owner_quota_type = $request->input('owner_quota_type');
            }
            if ($request->has('owner_quota_observations')) {
                $profile->owner_quota_observations = $request->input('owner_quota_observations');
            }
            foreach (['owner_quota_jacuzzi', 'owner_quota_kitchen', 'owner_quota_parking', 'owner_quota_breakfast', 'owner_quota_sofa_mais'] as $boolField) {
                if ($request->has($boolField)) {
                    $profile->{$boolField} = (bool) (int) $request->input($boolField);
                }
            }
            if ($request->has('owner_quota_number')) {
                $profile->owner_quota_number = $request->input('owner_quota_number');
            }
            if ($request->has('owner_quota_block')) {
                $profile->owner_quota_block = $request->input('owner_quota_block');
            }
            if ($request->has('owner_apartment_number')) {
                $profile->owner_apartment_number = $request->input('owner_apartment_number');
            }
            if ($request->has('hotel_operational')) {
                $profile->hotel_operational = $request->input('hotel_operational') === '1' || $request->input('hotel_operational') === 'true';
            }
            if ($request->has('allowed_uses')) {
                $uses = $request->input('allowed_uses', []);
                $profile->allowed_uses = is_array($uses) ? array_values(array_filter($uses)) : $profile->allowed_uses;
            }
        } else {
            foreach ([
                'gestor_quota_balcony',
                'gestor_quota_vista_mar',
                'gestor_quota_spa',
                'gestor_quota_piscina',
                'gestor_quota_academia',
                'gestor_quota_lareira',
                'gestor_quota_adega',
                'gestor_quota_area_kids',
                'gestor_quota_area_trabalho',
                'gestor_quota_wifi',
                'gestor_quota_hidromassagem',
            ] as $field) {
                if ($request->exists($field)) {
                    $quotaDetails[$field] = (int) $request->input($field);
                }
            }

            if ($request->has('gestor_hotel_id')) {
                $profile->gestor_hotel_id = $request->input('gestor_hotel_id');
            }
            if ($request->has('gestor_quota_rooms')) {
                $profile->gestor_quota_rooms = $request->input('gestor_quota_rooms');
            }
            if ($request->has('gestor_quota_size')) {
                $profile->gestor_quota_size = $request->input('gestor_quota_size');
            }
            if ($request->has('gestor_quota_seasonality')) {
                $profile->gestor_quota_seasonality = $request->input('gestor_quota_seasonality');
            }
            if ($request->has('gestor_quota_type')) {
                $profile->gestor_quota_type = $request->input('gestor_quota_type');
            }
            if ($request->has('gestor_quota_observations')) {
                $profile->gestor_quota_observations = $request->input('gestor_quota_observations');
            }
            foreach (['gestor_quota_jacuzzi', 'gestor_quota_kitchen', 'gestor_quota_parking', 'gestor_quota_breakfast', 'gestor_quota_sofa_mais'] as $boolField) {
                if ($request->has($boolField)) {
                    $profile->{$boolField} = (bool) (int) $request->input($boolField);
                }
            }
            if ($request->has('gestor_quota_number')) {
                $profile->gestor_quota_number = $request->input('gestor_quota_number');
            }
            if ($request->has('gestor_quota_block')) {
                $profile->gestor_quota_block = $request->input('gestor_quota_block');
            }
            if ($request->has('gestor_apartment_number')) {
                $profile->gestor_apartment_number = $request->input('gestor_apartment_number');
            }
            if ($request->has('gestor_hotel_operational')) {
                $profile->gestor_hotel_operational = $request->input('gestor_hotel_operational') === '1' || $request->input('gestor_hotel_operational') === 'true';
            }
            if ($request->has('gestor_quota_status')) {
                $profile->gestor_quota_status = $request->input('gestor_quota_status');
            }
            if ($request->has('gestor_quota_payment_deadline')) {
                $profile->gestor_quota_payment_deadline = $request->input('gestor_quota_payment_deadline');
            }
            if ($request->has('gestor_allowed_uses')) {
                $guses = $request->input('gestor_allowed_uses', []);
                $profile->gestor_allowed_uses = is_array($guses) ? array_values(array_filter($guses)) : $profile->gestor_allowed_uses;
            }
        }

        $profile->quota_details = $quotaDetails;
        $profile->save();
    }

    /**
     * Filtro "Sofá mais" (informação da cota no perfil do anunciante).
     */
    private function applySofaMaisProfileFilter(\Illuminate\Database\Eloquent\Builder $query, $rawValue): void
    {
        $wantsYes = $rawValue === '1' || $rawValue === 1 || $rawValue === 'true';

        $query->whereHas('user.profile', function ($pq) use ($wantsYes) {
            if ($wantsYes) {
                $pq->where(function ($w) {
                    $w->where(function ($o) {
                        $o->where('has_quota', 1)->where('owner_quota_sofa_mais', true);
                    })->orWhere(function ($g) {
                        $g->where('has_quota', 2)->where('gestor_quota_sofa_mais', true);
                    });
                });
            } else {
                $pq->where(function ($w) {
                    $w->where(function ($o) {
                        $o->where('has_quota', 1)->where(function ($x) {
                            $x->where('owner_quota_sofa_mais', false)->orWhereNull('owner_quota_sofa_mais');
                        });
                    })->orWhere(function ($g) {
                        $g->where('has_quota', 2)->where(function ($x) {
                            $x->where('gestor_quota_sofa_mais', false)->orWhereNull('gestor_quota_sofa_mais');
                        });
                    });
                });
            }
        });
    }
}
