<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ExchangeOffer;
use App\Models\Quota;
use App\Models\Hotel;
use App\Models\CidadeCapital;
use App\Services\NotificationService;
use App\Services\EmailService;
use Carbon\Carbon;

class ExchangeController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = ExchangeOffer::where('user_id', $user->id)
            ->with(['quota']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('exchange_type')) {
            $query->where('exchange_type', $request->exchange_type);
        }

        $exchanges = $query->latest()->paginate(15);

        return view('exchanges.index', compact('exchanges'));
    }

    /**
     * Refine search page for requesting exchanges (fractions only).
     * This reuses the quotas search UI but forces "exchange" mode and
     * restricts results to fractioned quotas whose fraction_details contain action=exchange.
     */
    public function refine(Request $request)
    {
        return redirect()->route('quotas.index', array_merge($request->all(), [
            'transaction_type' => 'exchange',
            'exchange_refine' => 1,
            'search' => $request->input('search', 1),
        ]));
    }

    public function create()
    {
        $user = Auth::user();
        // Incluir cotas com status 'available' ou null (fluxos que não definem status)
        $allQuotas = $user->quotas()
            ->where(function ($q) {
                $q->where('status', Quota::STATUS_AVAILABLE)->orWhereNull('status');
            })
            ->with(['hotel'])
            ->get();

        // Cota "inteira" no select: apenas quando não está fracionada no cadastro (tipo ≠ divisão em subperíodos).
        // Cotas fracionadas aparecem só como linhas de fração com ação de troca — evita duplicar cota inteira + fração.
        $quotas = $allQuotas->where('is_fractioned', false)->values();

        $hotels = Hotel::where('is_active', true)->get();

        // Frações derivadas do fracionamento (fraction_details), só para cotas marcadas como fracionadas
        $fractionsFromQuotas = collect();
        foreach ($allQuotas->where('is_fractioned', true) as $quota) {
            if (!$quota->fraction_details || !is_array($quota->fraction_details)) {
                continue;
            }

            // Estrutura com fraction_weeks (semanas e períodos)
            if (isset($quota->fraction_details['fraction_weeks']) && is_array($quota->fraction_details['fraction_weeks'])) {
                foreach ($quota->fraction_details['fraction_weeks'] as $weekNumber => $weekData) {
                    if (!isset($weekData['periods']) || !is_array($weekData['periods'])) {
                        continue;
                    }
                    foreach ($weekData['periods'] as $periodIndex => $period) {
                        $startDate = $period['start'] ?? $period['start_date'] ?? null;
                        $endDate = $period['end'] ?? $period['end_date'] ?? null;
                        if (!$startDate || !$endDate) {
                            continue;
                        }

                        if (!Quota::isPeriodEnabledWithAction($period)) {
                            continue;
                        }
                        $actionVal = trim((string) ($period['action'] ?? ''));
                        if (!in_array($actionVal, ['exchange', 'rent_exchange'], true)) {
                            continue;
                        }

                        $quotaHotel = $quota->hotel;
                        if (!$quotaHotel && $quota->hotel_name) {
                            $quotaHotel = Hotel::where('name', $quota->hotel_name)->first();
                        }

                        $fractionsFromQuotas->push((object)[
                            'quota_id' => $quota->id,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'number_of_days' => \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1,
                            'quota' => $quota,
                            'hotel' => $quotaHotel,
                            'city' => $quotaHotel->city ?? ($quota->location ? (explode(',', $quota->location)[0] ?? '') : ''),
                            'state' => $quotaHotel->state ?? ($quota->location ? trim(explode(',', $quota->location)[1] ?? '') : ''),
                            'week_number' => $weekNumber,
                            'period_index' => $periodIndex,
                        ]);
                    }
                }
            } elseif (isset($quota->fraction_details[0]) && is_array($quota->fraction_details[0])) {
                // Estrutura alternativa: array direto de frações
                foreach ($quota->fraction_details as $index => $fraction) {
                    if (!is_array($fraction) || !isset($fraction['start_date'], $fraction['end_date'])) {
                        continue;
                    }
                    if (!Quota::isPeriodEnabledWithAction($fraction)) {
                        continue;
                    }
                    if (!in_array(trim((string)($fraction['action'] ?? '')), ['exchange', 'rent_exchange'], true)) {
                        continue;
                    }

                    $quotaHotel = $quota->hotel;
                    if (!$quotaHotel && $quota->hotel_name) {
                        $quotaHotel = Hotel::where('name', $quota->hotel_name)->first();
                    }

                    $fractionsFromQuotas->push((object)[
                        'quota_id' => $quota->id,
                        'start_date' => $fraction['start_date'],
                        'end_date' => $fraction['end_date'],
                        'number_of_days' => \Carbon\Carbon::parse($fraction['start_date'])->diffInDays(\Carbon\Carbon::parse($fraction['end_date'])) + 1,
                        'quota' => $quota,
                        'hotel' => $quotaHotel,
                        'city' => $quotaHotel->city ?? ($quota->location ? (explode(',', $quota->location)[0] ?? '') : ''),
                        'state' => $quotaHotel->state ?? ($quota->location ? trim(explode(',', $quota->location)[1] ?? '') : ''),
                        'week_number' => $index + 1,
                        'period_index' => 0,
                    ]);
                }
            }
        }
        
        // Obter configuração do perfil
        $profile = $user->profile;
        $profileType = $profile->profile_type ?? 'curioso';
        $maxOptions = ExchangeOffer::getMaxOptionsByProfileType($profileType);
        $validityHours = ExchangeOffer::getValidityHoursByProfileType($profileType);
        
        // Obter limites de cidades e hotéis por perfil
        $limits = $this->getProfileLimits($profileType);
        
        // Contar cidades e hotéis já usados em ofertas ativas do usuário
        $usedCities = $this->getUsedCities($user->id);
        $usedHotels = $this->getUsedHotels($user->id);
        
        // Contar alertas enviados no mês atual
        $alertsSentThisMonth = $this->getAlertsSentThisMonth($user->id);
        $alertsRemaining = max(0, $limits['max_alerts_per_month'] - $alertsSentThisMonth);
        $maxCitiesAlerts = (int) (($profile->getProfileConfig()['max_cities_alerts'] ?? 0));

        $informeCidades = CidadeCapital::orderedForInforme();

        return view('exchanges.create', compact(
            'quotas',
            'fractionsFromQuotas',
            'hotels',
            'maxOptions',
            'validityHours',
            'limits',
            'usedCities',
            'usedHotels',
            'alertsSentThisMonth',
            'alertsRemaining',
            'profile',
            'profileType',
            'maxCitiesAlerts',
            'informeCidades',
        ));
    }
    
    /**
     * Contar alertas enviados no mês atual
     */
    private function getAlertsSentThisMonth($userId)
    {
        return \App\Models\Notification::where('user_id', $userId)
            ->where('type', 'exchange_alert')
            ->where('sent', true)
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
    }
    
    /**
     * Obter limites de cidades e hotéis por perfil
     */
    private function getProfileLimits($profileType)
    {
        return match($profileType) {
            'curioso' => [
                'max_cities' => 2,
                'max_hotels' => 4,
                'max_alerts_per_month' => 0, // Não envia alertas
            ],
            'inteligente' => [
                'max_cities' => 4,
                'max_hotels' => 6,
                'max_alerts_per_month' => 2,
            ],
            'sabio' => [
                'max_cities' => 7,
                'max_hotels' => 10,
                'max_alerts_per_month' => 4,
            ],
            default => [
                'max_cities' => 2,
                'max_hotels' => 4,
                'max_alerts_per_month' => 0,
            ],
        };
    }
    
    /**
     * Obter cidades já usadas em ofertas ativas do usuário
     */
    /**
     * Cidades distintas já usadas em ofertas ativas (critério “cidade desejada”).
     *
     * @return list<string>
     */
    private function getUsedCities(int $userId, ?int $ignoreExchangeOfferId = null): array
    {
        $query = ExchangeOffer::query()
            ->where('user_id', $userId)
            ->where('status', 'active');

        if ($ignoreExchangeOfferId !== null) {
            $query->where('id', '!=', $ignoreExchangeOfferId);
        }

        $all = [];
        foreach ($query->get(['desired_city', 'desired_cities']) as $offer) {
            $all = array_merge($all, $offer->getDesiredCitiesList());
        }

        return array_values(array_unique(array_filter($all)));
    }
    
    /**
     * Hotéis distintos já usados em ofertas ativas (critério “hotel desejado”).
     *
     * @return list<string>
     */
    private function getUsedHotels(int $userId, ?int $ignoreExchangeOfferId = null): array
    {
        $query = ExchangeOffer::query()
            ->where('user_id', $userId)
            ->where('status', 'active');

        if ($ignoreExchangeOfferId !== null) {
            $query->where('id', '!=', $ignoreExchangeOfferId);
        }

        $all = [];
        foreach ($query->get(['desired_hotel', 'desired_hotels']) as $offer) {
            $all = array_merge($all, $offer->getDesiredHotelsList());
        }

        return array_values(array_unique(array_filter($all)));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->back()->with('error', 'Complete seu perfil primeiro.');
        }

        $validator = Validator::make($request->all(), [
            'quota_id' => 'required|exists:quotas,id',
            'exchange_type' => 'required|in:semana,titularidade',
            'desired_state' => 'nullable|string|size:2',
            'desired_cities' => 'nullable|array|max:20',
            'desired_cities.*' => 'required|string|max:120',
            'desired_period_day_start' => 'nullable|integer|min:1|max:30',
            'desired_period_day_end' => 'nullable|integer|min:1|max:30',
            'desired_period_month' => 'required|integer|min:1|max:12',
            'desired_period_year' => 'required|integer|min:' . (int) date('Y') . '|max:' . ((int) date('Y') + 1),
            'desired_hotels' => 'nullable|array|max:30',
            'desired_hotels.*' => 'required|string|max:255',
            'desired_people' => 'nullable|integer|min:1|max:10',
            'desired_rooms' => 'nullable|integer|min:1',
            'price_range_min' => 'nullable|numeric|min:0',
            'price_range_max' => 'nullable|numeric|min:0|gte:price_range_min',
            'exchange_mode' => 'required|in:simples,mais',
            'complement_trade_type' => 'required_if:exchange_mode,mais|nullable|in:diarias,diarias_dinheiro',
            'days_difference' => [
                'nullable',
                'string',
                'max:12',
                Rule::requiredIf(fn () => $request->input('exchange_mode') === 'mais' && $request->input('complement_trade_type') === 'diarias'),
            ],
            'nights_plus_money' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(fn () => $request->input('exchange_mode') === 'mais' && $request->input('complement_trade_type') === 'diarias_dinheiro'),
            ],
            'city_promotion' => 'nullable|boolean',
            'promotion_cities' => 'nullable|array|max:20',
            'promotion_cities.*' => 'nullable|string|max:120',
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request, $user) {
            $this->validateDesiredPeriodInput($v, $request);
            $this->validateDaysDifferenceInput($v, $request, $user->id, null);
            $cities = $this->normalizeDesiredCitiesInput($request->input('desired_cities'));
            foreach ($cities as $cityName) {
                $q = Hotel::query()->where('is_active', true)->where('city', $cityName);
                if ($request->filled('desired_state')) {
                    $q->where('state', $request->desired_state);
                }
                if (! $q->exists()) {
                    $v->errors()->add('desired_cities', 'Cidade não cadastrada na base de hotéis: ' . $cityName);
                }
            }
            $hotels = $this->normalizeDesiredHotelsInput($request->input('desired_hotels'));
            foreach ($hotels as $hotelName) {
                $q = Hotel::query()->where('is_active', true)->where('name', $hotelName);
                if ($request->filled('desired_state')) {
                    $q->where('state', $request->desired_state);
                }
                if (! $q->exists()) {
                    $v->errors()->add('desired_hotels', 'Hotel não cadastrado ou inativo na base: ' . $hotelName);
                }
            }
            $promoCities = $this->normalizeDesiredCitiesInput($request->input('promotion_cities'));
            foreach ($promoCities as $cityToken) {
                if ($cityToken === '' || ! ctype_digit($cityToken)) {
                    $v->errors()->add('promotion_cities', 'Selecione cidades apenas na lista oficial (código IBGE inválido).');

                    continue;
                }
                if (! \App\Models\CidadeCapital::query()->where('codigo_ibge', (int) $cityToken)->exists()) {
                    $v->errors()->add('promotion_cities', 'Cidade não encontrada na base oficial (IBGE): ' . $cityToken);
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $parsedDaysDifference = $this->normalizeDaysDifferenceForStorage($request);
        $resolvedPeriod = $this->resolveDesiredPeriodFromRequest($request);
        $normalizedCities = $this->normalizeDesiredCitiesInput($request->input('desired_cities'));
        $normalizedHotels = $this->normalizeDesiredHotelsInput($request->input('desired_hotels'));
        $normalizedPromotion = $this->normalizeDesiredCitiesInput($request->input('promotion_cities'));

        $profileType = $profile->profile_type ?? 'curioso';
        $limits = $this->getProfileLimits($profileType);
        $maxCitiesAlerts = (int) (($profile->getProfileConfig()['max_cities_alerts'] ?? 0));

        if ($request->boolean('city_promotion')) {
            if ($maxCitiesAlerts <= 0) {
                return redirect()->back()
                    ->withErrors(['city_promotion' => 'Seu perfil não permite selecionar cidades para o informe de ofertas por cidade.'])
                    ->withInput();
            }
            if ($normalizedPromotion === []) {
                return redirect()->back()
                    ->withErrors(['promotion_cities' => 'Marque o informe e escolha ao menos uma cidade, ou desmarque o informe.'])
                    ->withInput();
            }
            if (count($normalizedPromotion) > $maxCitiesAlerts) {
                $word = $maxCitiesAlerts === 1 ? 'cidade' : 'cidades';

                return redirect()->back()
                    ->withErrors(['promotion_cities' => "Você pode escolher no máximo {$maxCitiesAlerts} {$word} para o informe (limite do seu perfil)."])
                    ->withInput();
            }
        }

        // Verificar se a cota pertence ao usuário
        $quota = Quota::where('id', $request->quota_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$quota) {
            return redirect()->back()->with('error', 'Cota não encontrada ou não pertence a você.');
        }

        if ($normalizedCities !== []) {
            $usedCities = $this->getUsedCities($user->id);
            $union = array_values(array_unique(array_merge($usedCities, $normalizedCities)));
            if (count($union) > $limits['max_cities']) {
                return redirect()->back()
                    ->with('error', "Você atingiu o limite de {$limits['max_cities']} cidades distintas entre suas ofertas ativas. Remova cidades de outra oferta ou faça upgrade do seu perfil.")
                    ->withInput();
            }
        }

        if ($normalizedHotels !== []) {
            $usedHotels = $this->getUsedHotels($user->id);
            $union = array_values(array_unique(array_merge($usedHotels, $normalizedHotels)));
            if (count($union) > $limits['max_hotels']) {
                return redirect()->back()
                    ->with('error', "Você atingiu o limite de {$limits['max_hotels']} hotéis distintos entre suas ofertas ativas. Remova hotéis de outra oferta ou faça upgrade do seu perfil.")
                    ->withInput();
            }
        }

        // Criar oferta de troca
        $exchangeOffer = ExchangeOffer::create([
            'user_id' => $user->id,
            'quota_id' => $quota->id,
            'exchange_type' => $request->exchange_type,
            'desired_cities' => $normalizedCities === [] ? null : $normalizedCities,
            'desired_city' => $this->legacyDesiredCitySummary($normalizedCities),
            'desired_period_start' => $resolvedPeriod['desired_period_start'],
            'desired_period_end' => $resolvedPeriod['desired_period_end'],
            'desired_period_month' => $resolvedPeriod['desired_period_month'],
            'desired_period_year' => $resolvedPeriod['desired_period_year'],
            'desired_hotels' => $normalizedHotels === [] ? null : $normalizedHotels,
            'desired_hotel' => $this->legacyDesiredHotelSummary($normalizedHotels),
            'promotion_cities' => $request->boolean('city_promotion') ? $normalizedPromotion : null,
            'desired_people' => $request->desired_people,
            'desired_rooms' => $request->desired_rooms,
            'price_range_min' => $request->price_range_min,
            'price_range_max' => $request->price_range_max,
            'exchange_mode' => $request->exchange_mode,
            'complement_trade_type' => $request->exchange_mode === 'mais' ? $request->complement_trade_type : null,
            'additional_value' => null,
            'days_difference' => $request->exchange_mode === 'mais' && $request->complement_trade_type === 'diarias'
                ? $parsedDaysDifference
                : null,
            'nights_plus_money' => $request->exchange_mode === 'mais' && $request->complement_trade_type === 'diarias_dinheiro'
                ? $request->nights_plus_money
                : null,
            'status' => 'active',
        ]);

        // Definir validade baseada no tipo de perfil
        $exchangeOffer->setValidityByProfileType($profile->profile_type);

        // Enviar email de notificação
        try {
            $emailService = new EmailService();
            $emailService->sendExchangeOfferCreatedEmail($user, $exchangeOffer);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de notificação de oferta de troca: ' . $e->getMessage());
            // Não interrompe o fluxo se o email falhar
        }

        // Processar envio de alertas se solicitado
        if ($request->boolean('city_promotion') && $limits['max_alerts_per_month'] > 0 && $normalizedPromotion !== []) {
            $alertsSentThisMonth = $this->getAlertsSentThisMonth($user->id);

            if ($alertsSentThisMonth >= $limits['max_alerts_per_month']) {
                return redirect()->back()
                    ->with('error', "Você atingiu o limite de {$limits['max_alerts_per_month']} alertas por mês. Faça upgrade do seu perfil para enviar mais alertas.")
                    ->withInput();
            }

            try {
                $this->notificationService->sendExchangeAlerts($exchangeOffer);
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar alertas de troca: ' . $e->getMessage());
            }
        }

        return redirect()->route('exchanges.show', $exchangeOffer)
            ->with('success', 'Oferta de troca criada com sucesso!');
    }

    public function show(ExchangeOffer $exchangeOffer)
    {
        $user = Auth::user();
        
        if ($exchangeOffer->user_id !== $user->id) {
            return redirect()->route('exchanges.index')
                ->with('error', 'Você não tem permissão para visualizar esta oferta.');
        }

        $exchangeOffer->load(['quota', 'user']);

        // Buscar matches potenciais
        $matches = $this->findMatches($exchangeOffer);

        return view('exchanges.show', compact('exchangeOffer', 'matches'));
    }

    public function edit(ExchangeOffer $exchangeOffer)
    {
        $user = Auth::user();
        
        if ($exchangeOffer->user_id !== $user->id) {
            return redirect()->route('exchanges.index')
                ->with('error', 'Você não tem permissão para editar esta oferta.');
        }

        if ($exchangeOffer->status !== 'active') {
            return redirect()->route('exchanges.show', $exchangeOffer)
                ->with('error', 'Não é possível editar uma oferta que não está ativa.');
        }

        $quotas = $user->quotas()
            ->where(function ($q) {
                $q->where('status', Quota::STATUS_AVAILABLE)->orWhereNull('status');
            })
            ->get();
        $hotels = Hotel::where('is_active', true)->get();
        $profile = $user->profile;
        $profileType = optional($profile)->profile_type ?? 'curioso';
        $maxOptions = ExchangeOffer::getMaxOptionsByProfileType($profileType);
        $validityHours = ExchangeOffer::getValidityHoursByProfileType($profileType);
        $limits = $this->getProfileLimits($profileType);
        $citiesRemaining = max(0, $limits['max_cities'] - count($this->getUsedCities($user->id, $exchangeOffer->id)));
        $hotelsRemaining = max(0, $limits['max_hotels'] - count($this->getUsedHotels($user->id, $exchangeOffer->id)));

        return view('exchanges.edit', compact(
            'exchangeOffer',
            'quotas',
            'hotels',
            'maxOptions',
            'validityHours',
            'limits',
            'citiesRemaining',
            'hotelsRemaining'
        ));
    }

    public function update(Request $request, ExchangeOffer $exchangeOffer)
    {
        $user = Auth::user();
        
        if ($exchangeOffer->user_id !== $user->id) {
            return redirect()->route('exchanges.index')
                ->with('error', 'Você não tem permissão para editar esta oferta.');
        }

        $validator = Validator::make($request->all(), array_merge([
            'desired_state' => 'nullable|string|size:2',
            'desired_cities' => 'nullable|array|max:20',
            'desired_cities.*' => 'required|string|max:120',
            'desired_period_day_start' => 'nullable|integer|min:1|max:30',
            'desired_period_day_end' => 'nullable|integer|min:1|max:30',
            'desired_period_month' => 'required|integer|min:1|max:12',
            'desired_period_year' => 'required|integer|min:' . (int) date('Y') . '|max:' . ((int) date('Y') + 1),
            'desired_hotels' => 'nullable|array|max:30',
            'desired_hotels.*' => 'required|string|max:255',
            'desired_people' => 'nullable|integer|min:1|max:10',
            'desired_rooms' => 'nullable|integer|min:1',
            'price_range_min' => 'nullable|numeric|min:0',
            'price_range_max' => 'nullable|numeric|min:0|gte:price_range_min',
            'nights_plus_money' => 'nullable|string|max:500',
        ], $exchangeOffer->exchange_mode === 'mais' ? [
            'complement_trade_type' => 'required|in:diarias,diarias_dinheiro',
            'days_difference' => [
                'nullable',
                'string',
                'max:12',
                Rule::requiredIf(fn () => $request->input('complement_trade_type') === 'diarias'),
            ],
            'nights_plus_money' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf(fn () => $request->input('complement_trade_type') === 'diarias_dinheiro'),
            ],
        ] : []));

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request, $user, $exchangeOffer) {
            $this->validateDesiredPeriodInput($v, $request);
            $this->validateDaysDifferenceInput($v, $request, $user->id, $exchangeOffer);
            $cities = $this->normalizeDesiredCitiesInput($request->input('desired_cities'));
            foreach ($cities as $cityName) {
                $q = Hotel::query()->where('is_active', true)->where('city', $cityName);
                if ($request->filled('desired_state')) {
                    $q->where('state', $request->desired_state);
                }
                if (! $q->exists()) {
                    $v->errors()->add('desired_cities', 'Cidade não cadastrada na base de hotéis: ' . $cityName);
                }
            }
            $hotels = $this->normalizeDesiredHotelsInput($request->input('desired_hotels'));
            foreach ($hotels as $hotelName) {
                $q = Hotel::query()->where('is_active', true)->where('name', $hotelName);
                if ($request->filled('desired_state')) {
                    $q->where('state', $request->desired_state);
                }
                if (! $q->exists()) {
                    $v->errors()->add('desired_hotels', 'Hotel não cadastrado ou inativo na base: ' . $hotelName);
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $parsedDaysDifference = $this->normalizeDaysDifferenceForStorage($request, $exchangeOffer);
        $resolvedPeriod = $this->resolveDesiredPeriodFromRequest($request);
        $normalizedCities = $this->normalizeDesiredCitiesInput($request->input('desired_cities'));
        $normalizedHotels = $this->normalizeDesiredHotelsInput($request->input('desired_hotels'));
        $profileType = optional($user->profile)->profile_type ?? 'curioso';
        $limits = $this->getProfileLimits($profileType);
        if ($normalizedCities !== []) {
            $usedCities = $this->getUsedCities($user->id, $exchangeOffer->id);
            $union = array_values(array_unique(array_merge($usedCities, $normalizedCities)));
            if (count($union) > $limits['max_cities']) {
                return redirect()->back()
                    ->with('error', "Você atingiu o limite de {$limits['max_cities']} cidades distintas entre suas ofertas ativas.")
                    ->withInput();
            }
        }

        if ($normalizedHotels !== []) {
            $usedHotels = $this->getUsedHotels($user->id, $exchangeOffer->id);
            $unionH = array_values(array_unique(array_merge($usedHotels, $normalizedHotels)));
            if (count($unionH) > $limits['max_hotels']) {
                return redirect()->back()
                    ->with('error', "Você atingiu o limite de {$limits['max_hotels']} hotéis distintos entre suas ofertas ativas.")
                    ->withInput();
            }
        }

        $payload = [
            'desired_period_start' => $resolvedPeriod['desired_period_start'],
            'desired_period_end' => $resolvedPeriod['desired_period_end'],
            'desired_period_month' => $resolvedPeriod['desired_period_month'],
            'desired_period_year' => $resolvedPeriod['desired_period_year'],
            'desired_people' => $request->desired_people,
            'desired_rooms' => $request->desired_rooms,
            'price_range_min' => $request->price_range_min,
            'price_range_max' => $request->price_range_max,
        ];
        if ($exchangeOffer->exchange_mode === 'mais') {
            $payload['complement_trade_type'] = $request->complement_trade_type;
            $payload['days_difference'] = $request->complement_trade_type === 'diarias' ? $parsedDaysDifference : null;
            $payload['nights_plus_money'] = $request->complement_trade_type === 'diarias_dinheiro' ? $request->nights_plus_money : null;
            $payload['additional_value'] = null;
        } else {
            $payload['nights_plus_money'] = $request->nights_plus_money;
        }
        $payload['desired_cities'] = $normalizedCities === [] ? null : $normalizedCities;
        $payload['desired_city'] = $this->legacyDesiredCitySummary($normalizedCities);
        $payload['desired_hotels'] = $normalizedHotels === [] ? null : $normalizedHotels;
        $payload['desired_hotel'] = $this->legacyDesiredHotelSummary($normalizedHotels);

        $exchangeOffer->update($payload);

        return redirect()->route('exchanges.show', $exchangeOffer)
            ->with('success', 'Oferta de troca atualizada com sucesso!');
    }

    public function destroy(ExchangeOffer $exchangeOffer)
    {
        $user = Auth::user();
        
        if ($exchangeOffer->user_id !== $user->id) {
            return redirect()->route('exchanges.index')
                ->with('error', 'Você não tem permissão para excluir esta oferta.');
        }

        $exchangeOffer->update(['status' => 'cancelled']);

        return redirect()->route('exchanges.index')
            ->with('success', 'Oferta de troca cancelada com sucesso!');
    }

    /**
     * Encontrar matches potenciais para a oferta de troca
     */
    private function findMatches(ExchangeOffer $exchangeOffer)
    {
        $query = Quota::where('status', 'available')
            ->where('user_id', '!=', $exchangeOffer->user_id)
            ->where('is_exchange', true)
            ->whereHasActiveExchangeListing();

        // Filtros: localização (cidade) ou nome do hotel — qualquer critério atendido
        $cities = $exchangeOffer->getDesiredCitiesList();
        $hotels = $exchangeOffer->getDesiredHotelsList();
        if ($cities !== [] || $hotels !== []) {
            $query->where(function ($q) use ($cities, $hotels) {
                foreach ($cities as $city) {
                    $q->orWhere('location', 'like', '%' . $city . '%');
                }
                foreach ($hotels as $hotelName) {
                    $q->orWhere('hotel_name', 'like', '%' . $hotelName . '%');
                }
            });
        }

        if ($exchangeOffer->desired_period_start && $exchangeOffer->desired_period_end) {
            $query->where(function($q) use ($exchangeOffer) {
                $q->whereBetween('start_date', [$exchangeOffer->desired_period_start, $exchangeOffer->desired_period_end])
                  ->orWhereBetween('end_date', [$exchangeOffer->desired_period_start, $exchangeOffer->desired_period_end])
                  ->orWhere(function($q2) use ($exchangeOffer) {
                      $q2->where('start_date', '<=', $exchangeOffer->desired_period_start)
                         ->where('end_date', '>=', $exchangeOffer->desired_period_end);
                  });
            });
        }

        if ($exchangeOffer->desired_people) {
            $query->where('number_of_guests', '>=', $exchangeOffer->desired_people);
        }

        return $query->limit(10)->get();
    }

    /**
     * @param  mixed  $raw
     * @return list<string>
     */
    private function normalizeDesiredCitiesInput($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $item) {
            $s = trim((string) $item);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Coluna legada desired_city: resumo para listagens e LIKE simples.
     *
     * @param  list<string>  $cities
     */
    private function legacyDesiredCitySummary(array $cities): ?string
    {
        if ($cities === []) {
            return null;
        }
        $joined = implode(', ', $cities);
        if (strlen($joined) <= 250) {
            return $joined;
        }

        return substr($joined, 0, 247) . '...';
    }

    /**
     * @param  mixed  $raw
     * @return list<string>
     */
    private function normalizeDesiredHotelsInput($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $item) {
            $s = trim((string) $item);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Coluna legada desired_hotel: resumo para listagens e LIKE simples.
     *
     * @param  list<string>  $hotels
     */
    private function legacyDesiredHotelSummary(array $hotels): ?string
    {
        if ($hotels === []) {
            return null;
        }
        $joined = implode(', ', $hotels);
        if (strlen($joined) <= 250) {
            return $joined;
        }

        return substr($joined, 0, 247) . '...';
    }

    private function validateDesiredPeriodInput(\Illuminate\Validation\Validator $v, Request $request): void
    {
        $month = (int) $request->input('desired_period_month');
        $year = (int) $request->input('desired_period_year');
        $dayStart = $request->filled('desired_period_day_start') ? (int) $request->input('desired_period_day_start') : null;
        $dayEnd = $request->filled('desired_period_day_end') ? (int) $request->input('desired_period_day_end') : null;

        if ($month < 1 || $month > 12 || $year < 1) {
            return;
        }

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        if ($dayStart !== null) {
            if ($dayStart > $daysInMonth) {
                $v->errors()->add('desired_period_day_start', 'O dia de início é inválido para o mês selecionado.');
            }
            if ($dayEnd === null) {
                $v->errors()->add('desired_period_day_end', 'Informe o período fim ou selecione o período início novamente.');
            } elseif ($dayEnd > $daysInMonth) {
                $v->errors()->add('desired_period_day_end', 'O dia de fim é inválido para o mês selecionado.');
            } elseif ($dayEnd < $dayStart) {
                $v->errors()->add('desired_period_day_end', 'O período fim deve ser igual ou posterior ao período início.');
            }
        } elseif ($dayEnd !== null) {
            $v->errors()->add('desired_period_day_start', 'Selecione o período início ao informar o período fim.');
        }
    }

    /**
     * @return array{
     *     desired_period_start: ?string,
     *     desired_period_end: ?string,
     *     desired_period_month: int,
     *     desired_period_year: int
     * }
     */
    private function resolveDesiredPeriodFromRequest(Request $request): array
    {
        $month = (int) $request->input('desired_period_month');
        $year = (int) $request->input('desired_period_year');
        $dayStart = $request->filled('desired_period_day_start') ? (int) $request->input('desired_period_day_start') : null;
        $dayEnd = $request->filled('desired_period_day_end') ? (int) $request->input('desired_period_day_end') : null;

        if ($dayStart === null) {
            return [
                'desired_period_start' => null,
                'desired_period_end' => null,
                'desired_period_month' => $month,
                'desired_period_year' => $year,
            ];
        }

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $dayStart = min($dayStart, $daysInMonth);
        $dayEnd = min($dayEnd ?? $dayStart, $daysInMonth);

        return [
            'desired_period_start' => Carbon::create($year, $month, $dayStart)->toDateString(),
            'desired_period_end' => Carbon::create($year, $month, $dayEnd)->toDateString(),
            'desired_period_month' => $month,
            'desired_period_year' => $year,
        ];
    }

    private function requiresDaysDifferenceInput(Request $request, ?ExchangeOffer $offer = null): bool
    {
        if ($request->input('complement_trade_type') !== 'diarias') {
            return false;
        }

        if ($request->input('exchange_mode') === 'mais') {
            return true;
        }

        return $offer !== null && $offer->exchange_mode === 'mais';
    }

    /**
     * @return int|false|null int quando válido, false quando inválido, null quando vazio e opcional
     */
    private function parseDaysDifferenceInput(?string $raw, bool $required): int|false|null
    {
        if ($raw === null || trim($raw) === '') {
            return $required ? false : null;
        }

        $normalized = preg_replace('/\s+/', '', trim($raw));
        if (! preg_match('/^([+-])(\d+)$/', $normalized, $matches)) {
            return false;
        }

        $magnitude = (int) $matches[2];
        if ($magnitude <= 0) {
            return false;
        }

        return $matches[1] === '-' ? -$magnitude : $magnitude;
    }

    private function normalizeDaysDifferenceForStorage(Request $request, ?ExchangeOffer $offer = null): ?int
    {
        if (! $this->requiresDaysDifferenceInput($request, $offer)) {
            return null;
        }

        $parsed = $this->parseDaysDifferenceInput(
            $request->input('days_difference'),
            true
        );

        return $parsed === false ? null : $parsed;
    }

    private function validateDaysDifferenceInput(
        \Illuminate\Validation\Validator $v,
        Request $request,
        int $userId,
        ?ExchangeOffer $offer = null
    ): void {
        if (! $this->requiresDaysDifferenceInput($request, $offer)) {
            return;
        }

        $parsed = $this->parseDaysDifferenceInput($request->input('days_difference'), true);
        if ($parsed === false) {
            $v->errors()->add(
                'days_difference',
                'Informe +N para solicitar diárias extras ou -N para ofertar diárias extras (ex.: +2 ou -3).'
            );
        }
    }

}
