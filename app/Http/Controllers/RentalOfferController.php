<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\RentalOffer;
use App\Models\Quota;
use App\Models\Hotel;
use App\Models\CidadeCapital;
use App\Services\FileUploadService;
use App\Services\EmailService;
use Carbon\Carbon;

class RentalOfferController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display user's own rental offers.
     */
    public function myOffers(Request $request)
    {
        $user = Auth::user();
        
        $query = RentalOffer::where('user_id', $user->id)
            ->with(['quota', 'hotel'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'active')->where('end_date', '>=', now());
            } elseif ($request->status === 'inactive') {
                $query->where('status', '!=', 'active');
            } elseif ($request->status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        if ($request->filled('is_auction')) {
            $query->where('is_auction', $request->is_auction == '1');
        }

        $offers = $query->paginate(15);

        return view('rental-offers.my', compact('offers'));
    }

    /**
     * Display a listing of rental offers.
     */
    public function index(Request $request)
    {
        $query = RentalOffer::with(['user', 'quota', 'hotel'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->filled('min_price') ? (float) $request->min_price : 0;
            $maxPrice = $request->filled('max_price') ? (float) $request->max_price : 999999999.99;
            $query->byPriceRange($minPrice, $maxPrice);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }
        
        // Filtro por dias específicos (2, 3, 4, 5, 7)
        if ($request->filled('days')) {
            $query->byDays($request->days);
        }
        
        // Filtro por mês
        if ($request->filled('month') && $request->filled('year')) {
            $query->byMonth($request->month, $request->year);
        }
        
        // Filtro por hotel
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }
        
        // Filtro por período (exato ou flexível)
        if ($request->filled('period_type')) {
            if ($request->period_type === 'flexible') {
                $query->flexiblePeriod();
            } else {
                $query->exactPeriod();
            }
        }

        if ($request->filled('is_auction')) {
            $query->auctions();
        }

        if ($request->filled('is_fractioned')) {
            $query->fractioned();
        }
        
        // Filtro por aceita troca
        if ($request->filled('accepts_exchange')) {
            $query->where('accepts_exchange', true);
        }
        
        // Filtro por aceita venda
        if ($request->filled('accepts_sale')) {
            $query->where('accepts_sale', true);
        }
        
        // Filtro por aceita troca por diárias
        if ($request->filled('accepts_diaria_exchange')) {
            $query->where('accepts_diaria_exchange', true);
        }

        $offers = $query->paginate(12);

        // Buscar hotéis para filtro
        $hotels = Hotel::where('is_active', true)->orderBy('name')->get();

        return view('rental-offers.index', compact('offers', 'hotels'));
    }

    /**
     * Show the form for creating a new rental offer.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check if user has basic profile (terms accepted)
        if (!$user->profile || !$user->profile->terms_accepted) {
            return redirect()->route('profile.complete')
                ->with('warning', 'Complete seu cadastro para criar ofertas.');
        }

        // Buscar TODAS as cotas disponíveis (incluindo fracionadas) para processar frações
        // Incluir status 'available' ou null (cotas criadas por outros fluxos que não definem status)
        $allQuotas = $user->quotas()
            ->where(function ($q) {
                $q->where('status', Quota::STATUS_AVAILABLE)->orWhereNull('status');
            })
            ->with(['hotel'])
            ->get()
            ->filter(fn (Quota $q) => $q->allowsRentalPublicationFromRegistration())
            ->values();

        // Filtrar apenas cotas NÃO fracionadas para mostrar como opções inteiras no dropdown
        // Cotas fracionadas não devem aparecer inteiras, apenas suas frações
        $quotas = $allQuotas->where('is_fractioned', false)->values();
        
        // Buscar também ofertas de aluguel fracionadas ativas do usuário
        // Essas são as frações que foram criadas a partir de uma cota fracionada
        // e que ainda não foram negociadas/alugadas
        $fractionedOffers = \App\Models\RentalOffer::where('user_id', $user->id)
            ->where('is_fractioned', true)
            ->where('status', 'active')
            ->whereNull('negotiated_at') // Ainda não negociadas
            ->with(['quota.hotel', 'hotel'])
            ->get()
            ->filter(fn (RentalOffer $offer) => $offer->quota && $offer->quota->allowsRentalPublicationFromRegistration())
            ->values();
        
        // Também buscar frações que estão no campo fraction_details das cotas FRACIONADAS
        // mas que ainda não foram criadas como RentalOffers
        $fractionsFromQuotas = collect();
        // Buscar apenas cotas fracionadas para extrair suas frações
        $fractionedQuotas = $allQuotas->where('is_fractioned', true);
        foreach ($fractionedQuotas as $quota) {
            Log::info('Checking quota ID: ' . $quota->id . ', is_fractioned: ' . ($quota->is_fractioned ? 'true' : 'false'));
            if ($quota->is_fractioned && $quota->fraction_details && is_array($quota->fraction_details)) {
                Log::info('Quota ' . $quota->id . ' has fraction_details: ' . json_encode($quota->fraction_details));
                // Verificar se fraction_details tem fraction_weeks (estrutura do cadastro)
                if (isset($quota->fraction_details['fraction_weeks']) && is_array($quota->fraction_details['fraction_weeks'])) {
                    // Estrutura: fraction_details['fraction_weeks'][weekNumber]['periods'][periodNumber]
                    foreach ($quota->fraction_details['fraction_weeks'] as $weekNumber => $weekData) {
                        Log::info('Processing week ' . $weekNumber . ' for quota ' . $quota->id . ': ' . json_encode($weekData));
                        // weekData pode ser um array com 'periods' ou pode ser diretamente os períodos
                        if (isset($weekData['periods']) && is_array($weekData['periods'])) {
                            $periods = $weekData['periods'];
                        } elseif (is_array($weekData) && isset($weekData[0]) && is_array($weekData[0])) {
                            // Se weekData é diretamente um array de períodos
                            $periods = $weekData;
                        } else {
                            Log::info('Skipping week ' . $weekNumber . ' - invalid structure');
                            continue;
                        }
                        
                        foreach ($periods as $periodNumber => $period) {
                            if (!is_array($period)) continue;
                            
                            // Verificar se o período tem start e end (pode ser 'start'/'end' ou 'start_date'/'end_date')
                            $startDate = $period['start'] ?? $period['start_date'] ?? null;
                            $endDate = $period['end'] ?? $period['end_date'] ?? null;
                            
                            if (!$startDate || !$endDate) continue;
                            
                            // Verificar se já existe uma RentalOffer para esta fração
                            $existingOffer = \App\Models\RentalOffer::where('quota_id', $quota->id)
                                ->where('start_date', $startDate)
                                ->where('end_date', $endDate)
                                ->where('is_fractioned', true)
                                ->first();
                            
                            if ($existingOffer) continue; // Já existe, pular

                            if (!\App\Models\Quota::isPeriodEnabledWithAction($period)) {
                                continue;
                            }
                            $actionVal = trim((string) ($period['action'] ?? ''));
                            if (!in_array($actionVal, ['rent', 'rent_exchange'], true)) {
                                continue;
                            }

                            Log::info('Adding fraction for quota ' . $quota->id . ': ' . $startDate . ' to ' . $endDate);
                            // Criar um objeto temporário para exibir no select
                            $quotaHotel = $quota->hotel;
                            if (!$quotaHotel && $quota->hotel_name) {
                                $quotaHotel = \App\Models\Hotel::where('name', $quota->hotel_name)->first();
                            }

                            $fractionsFromQuotas->push((object)[
                                'quota_id' => $quota->id,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'number_of_days' => \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1,
                                'number_of_people' => $quota->number_of_guests,
                                'quota' => $quota,
                                'hotel' => $quotaHotel,
                                'city' => $quotaHotel->city ?? ($quota->location ? (explode(',', $quota->location)[0] ?? '') : ''),
                                'state' => $quotaHotel->state ?? ($quota->location ? trim(explode(',', $quota->location)[1] ?? '') : ''),
                                'is_from_fraction_details' => true,
                                'week_number' => $weekNumber,
                                'allows_rent_exchange' => $actionVal === 'rent_exchange',
                            ]);
                        }
                    }
                } elseif (isset($quota->fraction_details[0]) && is_array($quota->fraction_details[0])) {
                    // Estrutura alternativa: array direto de frações
                    foreach ($quota->fraction_details as $index => $fraction) {
                        if (!is_array($fraction) || !isset($fraction['start_date'], $fraction['end_date'])) {
                            continue;
                        }
                        if (!\App\Models\Quota::isPeriodEnabledWithAction($fraction)) {
                            continue;
                        }
                        if (!in_array(trim((string)($fraction['action'] ?? '')), ['rent', 'rent_exchange'], true)) {
                            continue;
                        }
                        // Verificar se já existe uma RentalOffer para esta fração
                        $existingOffer = \App\Models\RentalOffer::where('quota_id', $quota->id)
                            ->where('start_date', $fraction['start_date'])
                            ->where('end_date', $fraction['end_date'])
                            ->where('is_fractioned', true)
                            ->first();
                        
                        if (!$existingOffer) {
                                // Criar um objeto temporário para exibir no select
                                $quotaHotel = $quota->hotel;
                                if (!$quotaHotel && $quota->hotel_name) {
                                    $quotaHotel = \App\Models\Hotel::where('name', $quota->hotel_name)->first();
                                }
                                
                                $legacyAction = trim((string) ($fraction['action'] ?? ''));
                                $fractionsFromQuotas->push((object)[
                                    'quota_id' => $quota->id,
                                    'start_date' => $fraction['start_date'],
                                    'end_date' => $fraction['end_date'],
                                    'number_of_days' => \Carbon\Carbon::parse($fraction['start_date'])->diffInDays(\Carbon\Carbon::parse($fraction['end_date'])) + 1,
                                    'number_of_people' => $quota->number_of_guests,
                                    'quota' => $quota,
                                    'hotel' => $quotaHotel,
                                    'city' => $quotaHotel->city ?? ($quota->location ? (explode(',', $quota->location)[0] ?? '') : ''),
                                    'state' => $quotaHotel->state ?? ($quota->location ? trim(explode(',', $quota->location)[1] ?? '') : ''),
                                    'is_from_fraction_details' => true,
                                    'week_number' => $index + 1,
                                    'period_index' => 0,
                                    'allows_rent_exchange' => $legacyAction === 'rent_exchange',
                                ]);
                            }
                        }
                    }
                }
            }
        
        // Combinar ambas as listas
        Log::info('Total fractionedOffers: ' . $fractionedOffers->count());
        Log::info('Total fractionsFromQuotas: ' . $fractionsFromQuotas->count());
        // Usar uma collection "base" para evitar chamadas a getKey() em objetos stdClass
        $allFractions = $fractionedOffers->toBase()->merge($fractionsFromQuotas);
        Log::info('Total allFractions: ' . $allFractions->count());
        
        $hotels = Hotel::where('is_active', true)->get();

        // Obter limite de cidades baseado no perfil
        $profileConfig = $user->profile->getProfileConfig();
        $maxCitiesAlerts = $profileConfig['max_cities_alerts'] ?? 0;
        $profileType = $user->profile->profile_type ?? 'curioso';

        $informeCidades = CidadeCapital::orderedForInforme();

        return view('rental-offers.create', compact('quotas', 'fractionedOffers', 'allFractions', 'hotels', 'maxCitiesAlerts', 'profileType', 'informeCidades'));
    }

    /**
     * Store a newly created rental offer.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $isBatchCreate = $request->filled('batch_quota_ids') && count((array) $request->batch_quota_ids) > 0;
        $primaryQuotaForRules = $request->filled('quota_id')
            ? Quota::where('id', $request->quota_id)->where('user_id', $user->id)->first()
            : null;
        $requiresTitularidadeSaleChoice = ! $isBatchCreate
            && $primaryQuotaForRules
            && $this->requiresTitularidadeSaleChoiceForStore($request, $primaryQuotaForRules);

        $validator = Validator::make($request->all(), [
            'quota_id' => 'required_without:batch_quota_ids|exists:quotas,id',
            'batch_quota_ids' => 'required_without:quota_id|array|min:1',
            'batch_quota_ids.*' => 'exists:quotas,id',
            'hotel_id' => 'nullable|exists:hotels,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'period_type' => 'required|in:exact,flexible',
            'start_date' => 'required_if:period_type,exact|nullable|date',
            'end_date' => 'required_if:period_type,exact|nullable|date|after_or_equal:start_date',
            'flexible_weeks' => 'required_if:period_type,flexible|nullable|array|min:1',
            'flexible_weeks.*' => 'date',
            'number_of_people' => 'required|integer|min:1|max:10',
            'price' => 'required_without_all:price_min,price_max|nullable|numeric|min:0',
            'price_min' => 'required_without:price|nullable|numeric|min:0',
            'price_max' => 'required_with:price_min|nullable|numeric|min:0|gte:price_min',
            'is_fractioned' => 'boolean',
            'fraction_details' => 'nullable|array',
            'is_auction' => 'boolean',
            'minimum_price' => 'required_if:is_auction,1|nullable|numeric|min:0',
            'auction_day' => 'required_if:is_auction,1|nullable|date|after_or_equal:today',
            'auction_start_hour' => 'required_if:is_auction,1|nullable|date_format:H:i',
            'auction_duration_minutes' => 'required_if:is_auction,1|nullable|integer|min:20|max:1440', // 20 min a 24h
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,jpg,png',
            'observations' => 'nullable|string|max:500',
            'accepts_exchange' => 'boolean',
            'accepts_sale' => [
                Rule::requiredIf($requiresTitularidadeSaleChoice),
                'boolean',
            ],
            'accepts_diaria_exchange' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if user owns the quota(s)
        $quotaIds = $request->batch_quota_ids ?? [$request->quota_id];
        $quotas = Quota::whereIn('id', $quotaIds)
            ->where('user_id', $user->id)
            ->get();

        if ($quotas->count() !== count($quotaIds)) {
            return redirect()->back()
                ->withErrors(['quota_id' => 'Uma ou mais cotas não foram encontradas ou não pertencem ao usuário.'])
                ->withInput();
        }

        $quota = $quotas->first();
        if (! $this->requiresTitularidadeSaleChoiceForStore($request, $quota)) {
            $request->merge(['accepts_sale' => false]);
        }

        foreach ($quotas as $q) {
            if (!$q->allowsRentalPublicationFromRegistration()) {
                return redirect()->back()
                    ->withErrors([
                        'quota_id' => 'Só é possível publicar oferta para cotas ou frações em que você autorizou alugar ou alugar e trocar no cadastro.',
                    ])
                    ->withInput();
            }
        }

        $wantsExchangeFlags = $request->boolean('accepts_exchange')
            || $request->boolean('accepts_diaria_exchange')
            || $request->boolean('fair_exchange');

        if ($wantsExchangeFlags) {
            $exchangeRuleMessage = 'Troca Simples ou Troca Justa só ficam disponíveis quando o cadastro da cota ou fração autoriza "Alugar e trocar" para o período publicado.';
            if ($isBatchCreate) {
                foreach ($quotas as $q) {
                    if (!$q->start_date || !$q->end_date) {
                        return redirect()->back()
                            ->withErrors(['accepts_exchange' => $exchangeRuleMessage])
                            ->withInput();
                    }
                    if (!$q->periodInRegistrationHasRentExchange(
                        Carbon::parse($q->start_date)->toDateString(),
                        Carbon::parse($q->end_date)->toDateString()
                    )) {
                        return redirect()->back()
                            ->withErrors(['accepts_exchange' => $exchangeRuleMessage])
                            ->withInput();
                    }
                }
            } else {
                $pubStart = null;
                $pubEnd = null;
                if ($request->period_type === 'exact' && $request->start_date && $request->end_date) {
                    $pubStart = Carbon::parse($request->start_date)->toDateString();
                    $pubEnd = Carbon::parse($request->end_date)->toDateString();
                } elseif ($request->period_type === 'flexible' && !empty($request->flexible_weeks[0])) {
                    $firstWeek = Carbon::parse($request->flexible_weeks[0]);
                    $pubStart = $firstWeek->copy()->startOfWeek()->toDateString();
                    $pubEnd = $firstWeek->copy()->endOfWeek()->toDateString();
                }
                $firstQuota = $quotas->first();
                if ($pubStart && $pubEnd && $firstQuota && !$firstQuota->periodInRegistrationHasRentExchange($pubStart, $pubEnd)) {
                    return redirect()->back()
                        ->withErrors(['accepts_exchange' => $exchangeRuleMessage])
                        ->withInput();
                }
            }
        }

        // Check if user can create fractioned offers
        if ($request->is_fractioned && !$this->canCreateFractionedOffers($user)) {
            return redirect()->back()
                ->withErrors(['is_fractioned' => 'Seu perfil não permite criar ofertas fracionadas.'])
                ->withInput();
        }

        // Validar data do leilão (deve ser até a penúltima data de validade da cota)
        if ($request->is_auction && $request->auction_day && $quota && $quota->end_date) {
            $endDate = Carbon::parse($quota->end_date);
            $penultimateDate = $endDate->copy()->subDay(); // Penúltima data (end_date - 1 dia)
            $auctionDay = Carbon::parse($request->auction_day);
            
            if ($auctionDay->gt($penultimateDate)) {
                return redirect()->back()
                    ->withErrors(['auction_day' => 'O dia do leilão deve ser até a penúltima data de validade da cota ou fração (' . $penultimateDate->format('d/m/Y') . ').'])
                    ->withInput();
            }
        }

        // Check if user can create auctions
        if ($request->is_auction) {
            if (!$this->canCreateAuctions($user)) {
            return redirect()->back()
                ->withErrors(['is_auction' => 'Seu perfil não permite criar leilões.'])
                ->withInput();
            }
            
            // Verificar limites de leilão por tipo de cadastro
            $offer = new RentalOffer();
            if (!$offer->canUserCreateAuction($user)) {
                return redirect()->back()
                    ->withErrors(['is_auction' => 'Você atingiu o limite de leilões permitido para seu tipo de cadastro.'])
                    ->withInput();
            }
            
            // Validar duração do leilão (deve ser 20 minutos OU múltiplo de 30 minutos)
            $duration = (int) $request->auction_duration_minutes;
            if ($duration !== 20 && $duration % 30 !== 0) {
                return redirect()->back()
                    ->withErrors(['auction_duration_minutes' => 'A duração do leilão deve ser em saltos de 30 minutos (20 min, 30 min, 1h, 1h30, etc.).'])
                    ->withInput();
            }
        }

        try {
            // Autofill hotel_id from quota, profile, or request
            $hotelId = $request->hotel_id;
            
            // Se não foi fornecido, buscar do hotel relacionado à cota
            if (!$hotelId && $quota) {
                $hotel = $quota->hotel ?? ($quota->hotel_name ? Hotel::where('name', $quota->hotel_name)->first() : null);
                if ($hotel) {
                    $hotelId = $hotel->id;
                }
            }
            
            // Se ainda não tiver, buscar do perfil
            if (!$hotelId) {
                $profile = $user->profile;
                $profileHotelId = $profile->quota_details['hotel_id'] ?? null;
                $hotelId = $profileHotelId;
            }
            
            if (!$hotelId) {
                return redirect()->back()->withErrors(['hotel_id' => 'Selecione um hotel.'])->withInput();
            }
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return redirect()->back()->withErrors(['hotel_id' => 'Hotel inválido.'])->withInput();
            }
            // Block rent if hotel not functioning
            if (!$hotel->is_functioning && !$request->is_auction) {
                return redirect()->back()->withErrors(['hotel_id' => 'Hotel inoperante: ofertas de aluguel indisponíveis.'])->withInput();
            }
            $data = $request->only([
                'title', 'description', 'city', 'state',
                'number_of_people', 'is_fractioned',
                'fraction_details', 'is_auction', 'minimum_price', 'observations',
                'period_type', 'price_min', 'price_max',
                'accepts_exchange', 'accepts_sale', 'accepts_diaria_exchange'
            ]);

            $data['user_id'] = $user->id;
            $data['hotel_id'] = $hotelId;
            $data['quota_id'] = $request->quota_id ?? $quotaIds[0]; // Primeira cota como principal
            
            // Autofill city/state from hotel if not provided
            if (empty($data['city']) && !empty($hotel->city)) {
                $data['city'] = $hotel->city;
            }
            if (empty($data['state']) && !empty($hotel->state)) {
                $data['state'] = $hotel->state;
            }
            
            // Handle period type
            if ($request->period_type === 'exact') {
                $data['start_date'] = $request->start_date;
                $data['end_date'] = $request->end_date;
            $data['number_of_days'] = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
            } else {
                // Período flexível - usar primeira semana como referência
                $flexibleWeeks = $request->flexible_weeks ?? [];
                if (!empty($flexibleWeeks)) {
                    $firstWeek = Carbon::parse($flexibleWeeks[0]);
                    $data['start_date'] = $firstWeek->copy()->startOfWeek();
                    $data['end_date'] = $firstWeek->copy()->endOfWeek();
                    $data['number_of_days'] = 7; // Padrão para período flexível
                }
                $data['flexible_weeks'] = $flexibleWeeks;
            }
            
            // Handle price
            if ($request->filled('price')) {
                $data['price'] = $request->price;
            $data['original_price'] = $request->price;
            } else {
                // Usar preço mínimo como referência
                $data['price'] = $request->price_min ?? 0;
                $data['original_price'] = $request->price_min ?? 0;
            }
            
            // Handle batch offers
            if (count($quotaIds) > 1) {
                $data['is_batch_offer'] = true;
                $data['batch_quota_ids'] = $quotaIds;
            }

            // Handle auction
            if ($request->is_auction && $request->is_auction != '0' && $request->is_auction != false) {
                if ($request->auction_day && $request->auction_start_hour && $request->minimum_price) {
                $auctionDay = Carbon::parse($request->auction_day);
                $auctionStartHour = Carbon::createFromFormat('H:i', $request->auction_start_hour);
                $auctionStart = $auctionDay->copy()
                    ->setTime($auctionStartHour->hour, $auctionStartHour->minute);
                
                // Garantir que a duração seja um inteiro
                $auctionDurationMinutes = (int) $request->auction_duration_minutes;
                
                    $data['minimum_price'] = $request->minimum_price;
                $data['auction_day'] = $auctionDay;
                $data['auction_start_hour'] = $auctionStart;
                $data['auction_start_time'] = $auctionStart;
                $data['auction_duration_minutes'] = $auctionDurationMinutes;
                $data['auction_end_time'] = $auctionStart->copy()->addMinutes($auctionDurationMinutes);
                }
            }
            
            // Calculate days until start
            if (!empty($data['start_date'])) {
                $data['days_until_start'] = max(0, Carbon::parse($data['start_date'])->diffInDays(now(), false));
            }

            // Handle photos upload
            if ($request->hasFile('photos')) {
                $photos = [];
                foreach ($request->file('photos') as $photo) {
                    $uploadResult = $this->fileUploadService->uploadUserPhoto($photo);
                    if ($uploadResult['valid']) {
                        $photos[] = $uploadResult['path'];
                    }
                }
                $data['photos'] = $photos;
            }

            $offer = RentalOffer::create($data);

            // Enviar email de notificação
            try {
                $emailService = new EmailService();
                $emailService->sendRentalOfferCreatedEmail($user, $offer);
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar email de notificação de oferta de aluguel: ' . $e->getMessage());
                // Não interrompe o fluxo se o email falhar
            }

            return redirect()->route('rental-offers.show', $offer)
                ->with('success', 'Oferta criada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar oferta. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified rental offer.
     */
    public function show(RentalOffer $rentalOffer)
    {
        // Increment view count
        $rentalOffer->increment('views_count');

        $rentalOffer->load(['user.profile', 'quota.user.profile', 'hotel', 'auctions.user']);

        return view('rental-offers.show', compact('rentalOffer'));
    }

    /**
     * Show the form for editing the specified rental offer.
     */
    public function edit(RentalOffer $rentalOffer)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        // Verificar se o usuário é o dono da oferta
        if ($rentalOffer->user_id !== $currentUser->id) {
            return redirect()->route('rental-offers.index')
                ->with('error', 'Você não tem permissão para editar esta oferta.');
        }
        
        $quotas = $currentUser->quotas()
            ->where(function ($q) {
                $q->where('status', Quota::STATUS_AVAILABLE)->orWhereNull('status');
            })
            ->get()
            ->filter(function (Quota $q) use ($rentalOffer) {
                return $q->allowsRentalPublicationFromRegistration() || (int) $q->id === (int) $rentalOffer->quota_id;
            })
            ->values();
        $hotels = Hotel::where('is_active', true)->get();

        $requiresTitularidadeSaleChoice = $this->requiresTitularidadeSaleChoiceForUpdate($rentalOffer, request());

        $quotaForExchange = $rentalOffer->quota;
        $allowsRentExchangePublication = $quotaForExchange
            && $rentalOffer->start_date
            && $rentalOffer->end_date
            && $quotaForExchange->periodInRegistrationHasRentExchange(
                Carbon::parse($rentalOffer->start_date)->toDateString(),
                Carbon::parse($rentalOffer->end_date)->toDateString()
            );

        $informeCidades = CidadeCapital::orderedForInforme();

        return view('rental-offers.edit', compact('rentalOffer', 'quotas', 'hotels', 'requiresTitularidadeSaleChoice', 'allowsRentExchangePublication', 'informeCidades'));
    }

    /**
     * Update the specified rental offer.
     */
    public function update(Request $request, RentalOffer $rentalOffer)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        
        // Verificar se o usuário é o dono da oferta
        if ($rentalOffer->user_id !== $currentUser->id) {
            return redirect()->route('rental-offers.index')
                ->with('error', 'Você não tem permissão para editar esta oferta.');
        }

        $requiresTitularidadeSaleChoice = $this->requiresTitularidadeSaleChoiceForUpdate($rentalOffer, $request);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required_without_all:price_min,price_max|nullable|numeric|min:0',
            'price_min' => 'required_without:price|nullable|numeric|min:0',
            'price_max' => 'required_with:price_min|nullable|numeric|min:0|gte:price_min',
            'observations' => 'nullable|string|max:500',
            'super_desconto' => 'boolean',
            'mega_oferta' => 'boolean',
            'mega_oferta_price' => 'nullable|numeric|min:0',
            'is_auction' => 'boolean',
            'minimum_price' => 'required_if:is_auction,1|nullable|numeric|min:0',
            'auction_day' => 'required_if:is_auction,1|nullable|date|after_or_equal:today',
            'auction_start_hour' => 'required_if:is_auction,1|nullable|date_format:H:i',
            'is_fractioned' => 'boolean',
            'accepts_exchange' => 'boolean',
            'accepts_sale' => [
                Rule::requiredIf($requiresTitularidadeSaleChoice),
                'boolean',
            ],
            'accepts_diaria_exchange' => 'boolean',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $quotaForExchange = $rentalOffer->quota;
        $allowsRentExchangePublication = $quotaForExchange
            && $rentalOffer->start_date
            && $rentalOffer->end_date
            && $quotaForExchange->periodInRegistrationHasRentExchange(
                Carbon::parse($rentalOffer->start_date)->toDateString(),
                Carbon::parse($rentalOffer->end_date)->toDateString()
            );

        $wantsExchangeFlags = $request->boolean('accepts_exchange') || $request->boolean('accepts_diaria_exchange');
        if ($wantsExchangeFlags && !$allowsRentExchangePublication) {
            return redirect()->back()
                ->withErrors([
                    'accepts_exchange' => 'Troca Simples ou Troca Justa só ficam disponíveis quando o cadastro da cota ou fração autoriza "Alugar e trocar" para o período publicado.',
                ])
                ->withInput();
        }

        // Validar data do leilão (deve ser até a penúltima data de validade da cota)
        if ($request->is_auction && $request->auction_day && $rentalOffer->quota && $rentalOffer->quota->end_date) {
            $endDate = Carbon::parse($rentalOffer->quota->end_date);
            $penultimateDate = $endDate->copy()->subDay(); // Penúltima data (end_date - 1 dia)
            $auctionDay = Carbon::parse($request->auction_day);
            
            if ($auctionDay->gt($penultimateDate)) {
                return redirect()->back()
                    ->withErrors(['auction_day' => 'O dia do leilão deve ser até a penúltima data de validade da cota ou fração (' . $penultimateDate->format('d/m/Y') . ').'])
                    ->withInput();
            }
        }

        try {
            $data = $request->only([
                'title', 'description', 'price', 'price_min', 'price_max', 'observations',
                'is_fractioned', 'accepts_exchange', 'accepts_sale', 'accepts_diaria_exchange'
            ]);
            
            // Handle price type
            if ($request->filled('price')) {
                $data['price'] = $request->price;
                $data['original_price'] = $request->price;
            } else {
                // Usar preço mínimo como referência
                $data['price'] = $request->price_min ?? 0;
                $data['original_price'] = $request->price_min ?? 0;
            }
            
            // Handle super desconto
            if ($request->filled('super_desconto')) {
                $data['super_desconto_applied'] = true;
            }
            
            // Handle mega oferta
            if ($request->filled('mega_oferta')) {
                $data['mega_oferta_applied'] = true;
                if ($request->filled('mega_oferta_price')) {
                    $data['mega_oferta_percentage'] = (($data['price'] - $request->mega_oferta_price) / $data['price']) * 100;
                }
            }
            
            // Handle auction
            if ($request->is_auction && $request->is_auction != '0' && $request->is_auction != false) {
                if ($request->auction_day && $request->auction_start_hour && $request->minimum_price) {
                $auctionDay = Carbon::parse($request->auction_day);
                $auctionStartHour = Carbon::createFromFormat('H:i', $request->auction_start_hour);
                $auctionStart = $auctionDay->copy()
                    ->setTime($auctionStartHour->hour, $auctionStartHour->minute);
                
                // Garantir que a duração seja um inteiro
                $auctionDurationMinutes = 20; // Fixo
                
                $data['is_auction'] = true;
                $data['minimum_price'] = $request->minimum_price;
                $data['auction_day'] = $auctionDay;
                $data['auction_start_hour'] = $auctionStart;
                $data['auction_start_time'] = $auctionStart;
                $data['auction_duration_minutes'] = $auctionDurationMinutes;
                $data['auction_end_time'] = $auctionStart->copy()->addMinutes($auctionDurationMinutes);
                } else {
                    $data['is_auction'] = false;
                }
            } else {
                $data['is_auction'] = false;
            }
            
            // Handle photos upload
            if ($request->hasFile('photos')) {
                $photos = $rentalOffer->photos ?? [];
                foreach ($request->file('photos') as $photo) {
                    $uploadResult = $this->fileUploadService->uploadUserPhoto($photo);
                    if ($uploadResult['valid']) {
                        $photos[] = $uploadResult['path'];
                    }
                }
                $data['photos'] = $photos;
            }
            
            $rentalOffer->update($data);

            return redirect()->route('rental-offers.show', $rentalOffer)
                ->with('success', 'Oferta atualizada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar oferta. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified rental offer.
     */
    public function destroy(RentalOffer $rentalOffer)
    {
        $this->authorize('delete', $rentalOffer);

        try {
            $rentalOffer->update(['status' => 'cancelled']);

            return redirect()->route('rental-offers.index')
                ->with('success', 'Oferta cancelada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao cancelar oferta. Tente novamente.']);
        }
    }

    /**
     * Search for rental offers.
     */
    public function search(Request $request)
    {
        $query = RentalOffer::with(['user', 'quota', 'hotel'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Apply search filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('city', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }
        
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->filled('min_price') ? (float) $request->min_price : 0;
            $maxPrice = $request->filled('max_price') ? (float) $request->max_price : 999999999.99;
            $query->byPriceRange($minPrice, $maxPrice);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }
        
        // Filtro por dias específicos (2, 3, 4, 5, 7)
        if ($request->filled('days')) {
            $query->byDays($request->days);
        }
        
        // Filtro por mês
        if ($request->filled('month') && $request->filled('year')) {
            $query->byMonth($request->month, $request->year);
        }
        
        // Filtro por hotel
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }
        
        // Filtro por período (exato ou flexível)
        if ($request->filled('period_type')) {
            if ($request->period_type === 'flexible') {
                $query->flexiblePeriod();
            } else {
                $query->exactPeriod();
            }
        }

        if ($request->filled('is_auction')) {
            $query->auctions();
        }

        if ($request->filled('is_fractioned')) {
            $query->fractioned();
        }
        
        // Filtro por aceita troca
        if ($request->filled('accepts_exchange')) {
            $query->where('accepts_exchange', true);
        }
        
        // Filtro por aceita venda
        if ($request->filled('accepts_sale')) {
            $query->where('accepts_sale', true);
        }
        
        // Filtro por aceita troca por diárias
        if ($request->filled('accepts_diaria_exchange')) {
            $query->where('accepts_diaria_exchange', true);
        }

        $offers = $query->paginate(12);

        // Buscar hotéis para filtro
        $hotels = Hotel::where('is_active', true)->orderBy('name')->get();

        return view('rental-offers.search', compact('offers', 'hotels'));
    }

    /**
     * Oferta de aluguel com cota inteira de 7 pernoites (período exato = período integral da cota).
     */
    private function requiresTitularidadeSaleChoiceForStore(Request $request, Quota $quota): bool
    {
        if ($request->input('period_type') !== 'exact' || ! $request->filled('start_date') || ! $request->filled('end_date')) {
            return false;
        }
        if (! $quota->start_date || ! $quota->end_date) {
            return false;
        }
        $rs = Carbon::parse($request->start_date)->startOfDay();
        $re = Carbon::parse($request->end_date)->startOfDay();
        $qs = Carbon::parse($quota->start_date)->startOfDay();
        $qe = Carbon::parse($quota->end_date)->startOfDay();
        if (! $rs->equalTo($qs) || ! $re->equalTo($qe)) {
            return false;
        }
        $days = $qs->diffInDays($qe) + 1;
        $nights = $days > 0 ? $days - 1 : 0;

        return $nights === 7;
    }

    /**
     * Edição: mesma regra (oferta cobre a cota inteira com 7 pernoites e não é fracionada).
     */
    private function requiresTitularidadeSaleChoiceForUpdate(RentalOffer $rentalOffer, Request $request): bool
    {
        $quota = $rentalOffer->quota;
        if (! $quota || ! $quota->start_date || ! $quota->end_date) {
            return false;
        }
        if ($request->boolean('is_fractioned')) {
            return false;
        }
        if (! $rentalOffer->start_date || ! $rentalOffer->end_date) {
            return false;
        }
        $rs = Carbon::parse($rentalOffer->start_date)->startOfDay();
        $re = Carbon::parse($rentalOffer->end_date)->startOfDay();
        $qs = Carbon::parse($quota->start_date)->startOfDay();
        $qe = Carbon::parse($quota->end_date)->startOfDay();
        if (! $rs->equalTo($qs) || ! $re->equalTo($qe)) {
            return false;
        }
        $days = $qs->diffInDays($qe) + 1;
        $nights = $days > 0 ? $days - 1 : 0;

        return $nights === 7;
    }

    /**
     * Check if user can create fractioned offers.
     */
    private function canCreateFractionedOffers($user)
    {
        return $user->profile && 
               in_array($user->profile->profile_type, ['inteligente', 'sabio']);
    }

    /**
     * Check if user can create auctions.
     */
    private function canCreateAuctions($user)
    {
        return $user->profile && 
               in_array($user->profile->profile_type, ['inteligente', 'sabio']);
    }

    /**
     * Display the request/search page for rental (without action buttons).
     */
    public function request(Request $request)
    {
        // Redirect to quotas index with transaction_type=rent and hide_buttons parameter
        return redirect()->route('quotas.index', array_merge($request->all(), [
            'transaction_type' => 'rent',
            'hide_buttons' => true
        ]));
    }
}
