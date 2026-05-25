<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Quota;
use App\Models\RentalOffer;
use App\Models\Hotel;
use App\Models\User;
use App\Models\AdvancedAuction;
use App\Models\AuctionLimit;
use App\Services\FileUploadService;

class QuotaManagementController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of user's quotas.
     */
    public function index()
    {
        $user = Auth::user();
        $quotas = $user->quotas()->with(['hotel', 'rentalOffers'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('quota-management.index', compact('quotas'));
    }

    /**
     * Show the form for creating a new quota.
     */
    public function create()
    {
        $hotels = Hotel::where('is_active', true)->get();
        return view('quota-management.create', compact('hotels'));
    }

    /**
     * Store a newly created quota.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'weeks' => 'required|integer|min:1|max:4',
            'number_of_rooms' => 'required|integer|min:1|max:10',
            'seasonality' => 'required|in:low,medium,high,peak',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'number_of_guests' => 'required|integer|min:1|max:20',
            'rental_price' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'contract_photo' => 'required|image|mimes:jpeg,jpg,png',
            'authorizations' => 'nullable|array',
            'authorizations.*' => 'file|mimes:pdf,jpeg,jpg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $hotel = Hotel::findOrFail($request->hotel_id);
            
            // Upload contract photo
            $contractPhoto = $this->fileUploadService->uploadUserPhoto($request->file('contract_photo'));
            
            // Upload authorizations
            $authorizations = [];
            if ($request->hasFile('authorizations')) {
                foreach ($request->file('authorizations') as $auth) {
                    $uploadResult = $this->fileUploadService->uploadUserPhoto($auth);
                    if ($uploadResult['valid']) {
                        $authorizations[] = $uploadResult['path'];
                    }
                }
            }

            $quota = Quota::create([
                'user_id' => Auth::id(),
                'hotel_id' => $request->hotel_id,
                'hotel_name' => $hotel->name,
                'location' => $hotel->city . ', ' . $hotel->state,
                'weeks' => $request->weeks,
                'number_of_rooms' => $request->number_of_rooms,
                'seasonality' => $request->seasonality,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'number_of_guests' => $request->number_of_guests,
                'rental_price' => $request->rental_price,
                'observations' => $request->observations,
                'contract_photo_path' => $contractPhoto['path'],
                'authorizations' => $authorizations,
                'payment_status' => 'unpaid',
                'is_owner' => true,
                'quota_status' => 'active',
                'status' => Quota::STATUS_AVAILABLE,
            ]);

            return redirect()->route('quota-management.show', $quota)
                ->with('success', 'Cota criada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar cota. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified quota.
     */
    public function show(Quota $quota)
    {
        $this->authorize('view', $quota);
        
        $quota->load(['hotel', 'rentalOffers', 'transactions']);
        
        return view('quota-management.show', compact('quota'));
    }

    /**
     * Show the form for editing the specified quota.
     */
    public function edit(Quota $quota)
    {
        $this->authorize('update', $quota);
        
        $hotels = Hotel::where('is_active', true)->get();
        
        return view('quota-management.edit', compact('quota', 'hotels'));
    }

    /**
     * Update the specified quota.
     */
    public function update(Request $request, Quota $quota)
    {
        $this->authorize('update', $quota);

        $validator = Validator::make($request->all(), [
            'weeks' => 'required|integer|min:1|max:4',
            'number_of_rooms' => 'required|integer|min:1|max:10',
            'seasonality' => 'required|in:low,medium,high,peak',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'number_of_guests' => 'required|integer|min:1|max:20',
            'rental_price' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'contract_photo' => 'nullable|image|mimes:jpeg,jpg,png',
            'authorizations' => 'nullable|array',
            'authorizations.*' => 'file|mimes:pdf,jpeg,jpg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->only([
                'weeks', 'number_of_rooms', 'seasonality', 'start_date', 
                'end_date', 'number_of_guests', 'rental_price', 'observations'
            ]);

            // Upload new contract photo if provided
            if ($request->hasFile('contract_photo')) {
                $contractPhoto = $this->fileUploadService->uploadUserPhoto($request->file('contract_photo'));
                $data['contract_photo_path'] = $contractPhoto['path'];
            }

            // Upload new authorizations if provided
            if ($request->hasFile('authorizations')) {
                $authorizations = [];
                foreach ($request->file('authorizations') as $auth) {
                    $uploadResult = $this->fileUploadService->uploadUserPhoto($auth);
                    if ($uploadResult['valid']) {
                        $authorizations[] = $uploadResult['path'];
                    }
                }
                $data['authorizations'] = $authorizations;
            }

            $quota->update($data);

            return redirect()->route('quota-management.show', $quota)
                ->with('success', 'Cota atualizada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar cota. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Publish the quota.
     */
    public function publish(Quota $quota)
    {
        $this->authorize('update', $quota);

        if ($quota->publish()) {
            return redirect()->back()
                ->with('success', 'Cota publicada com sucesso!');
        } else {
            return redirect()->back()
                ->withErrors(['error' => 'Não é possível publicar esta cota. Verifique se está quitada e ativa.']);
        }
    }

    /**
     * Unpublish the quota.
     */
    public function unpublish(Quota $quota)
    {
        $this->authorize('update', $quota);

        $quota->unpublish();

        return redirect()->back()
            ->with('success', 'Cota despublicada com sucesso!');
    }

    /**
     * Transfer ownership of the quota.
     */
    public function transferOwnership(Request $request, Quota $quota)
    {
        $this->authorize('transfer', $quota);

        $validator = Validator::make($request->all(), [
            'new_owner_email' => 'required|email|exists:users,email',
            'transfer_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $newOwner = User::where('email', $request->new_owner_email)->first();
            
            $quota->transferOwnership($newOwner->id);

            // Notificar novo proprietário
            // TODO: Implementar sistema de notificações

            return redirect()->route('quota-management.index')
                ->with('success', 'Titularidade transferida com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao transferir titularidade. Tente novamente.']);
        }
    }

    /**
     * Create a fractioned offer for the quota.
     */
    public function createFractionedOffer(Request $request, Quota $quota)
    {
        $this->authorize('update', $quota);

        $user = Auth::user();
        
        // Verificar se pode criar ofertas fracionadas
        if (!$quota->canBeFractioned($user->profile)) {
            return redirect()->back()
                ->withErrors(['error' => 'Seu perfil não permite criar ofertas fracionadas.']);
        }

        $validator = Validator::make($request->all(), [
            'fraction_details' => 'required|array',
            'fraction_details.*.start_date' => 'required|date|after:today',
            'fraction_details.*.end_date' => 'required|date|after:start_date',
            'fraction_details.*.price' => 'required|numeric|min:0',
            'fraction_details.*.description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Criar ofertas fracionadas
            foreach ($request->fraction_details as $fraction) {
                RentalOffer::create([
                    'user_id' => $user->id,
                    'quota_id' => $quota->id,
                    'hotel_id' => $quota->hotel_id,
                    'title' => "Fração da cota - {$quota->hotel_name}",
                    'description' => $fraction['description'],
                    'city' => $quota->hotel->city,
                    'state' => $quota->hotel->state,
                    'start_date' => $fraction['start_date'],
                    'end_date' => $fraction['end_date'],
                    'number_of_days' => \Carbon\Carbon::parse($fraction['start_date'])->diffInDays(\Carbon\Carbon::parse($fraction['end_date'])) + 1,
                    'number_of_people' => $quota->number_of_guests,
                    'price' => $fraction['price'],
                    'is_fractioned' => true,
                    'fraction_details' => $fraction,
                    'status' => 'active',
                ]);
            }

            return redirect()->route('quota-management.show', $quota)
                ->with('success', 'Ofertas fracionadas criadas com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar ofertas fracionadas. Tente novamente.']);
        }
    }

    /**
     * Mark quota as paid.
     */
    public function markAsPaid(Quota $quota)
    {
        $this->authorize('update', $quota);

        $quota->update(['payment_status' => 'paid']);

        return redirect()->back()
            ->with('success', 'Cota marcada como quitada!');
    }

    /**
     * Mark quota as unpaid.
     */
    public function markAsUnpaid(Quota $quota)
    {
        $this->authorize('update', $quota);

        $quota->update(['payment_status' => 'unpaid']);

        return redirect()->back()
            ->with('success', 'Cota marcada como não quitada!');
    }

    /**
     * Create different types of offers (rent, exchange, sell, buy).
     */
    public function createOffer(Request $request, Quota $quota)
    {
        $this->authorize('update', $quota);

        $validator = Validator::make($request->all(), [
            'offer_type' => 'required|in:rent,exchange,sell,buy',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'number_of_people' => 'required|integer|min:1|max:20',
            'price' => 'nullable|numeric|min:0',
            'is_flexible_period' => 'boolean',
            'flexible_dates' => 'nullable|array',
            'min_days' => 'nullable|integer|min:1',
            'max_days' => 'nullable|integer|min:1',
            'sale_minimum_price' => 'nullable|numeric|min:0',
            'sale_acceptable_price' => 'nullable|numeric|min:0',
            'sale_desired_price' => 'nullable|numeric|min:0',
            'exchange_options' => 'nullable|array',
            'exchange_validity_hours' => 'nullable|integer|min:24|max:72',
            'is_auction' => 'boolean',
            'auction_minimum_price' => 'nullable|numeric|min:0',
            'auction_duration_hours' => 'nullable|integer|min:1|max:24',
            'auction_start_time' => 'nullable|date|after:now',
            'observations' => 'nullable|string|max:1000',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();
            $data = $request->only([
                'offer_type', 'title', 'description', 'city', 'state',
                'start_date', 'end_date', 'number_of_people', 'price',
                'is_flexible_period', 'flexible_dates', 'min_days', 'max_days',
                'sale_minimum_price', 'sale_acceptable_price', 'sale_desired_price',
                'exchange_options', 'exchange_validity_hours', 'is_auction',
                'auction_minimum_price', 'auction_duration_hours', 'auction_start_time',
                'observations'
            ]);

            $data['user_id'] = $user->id;
            $data['quota_id'] = $quota->id;
            $data['hotel_id'] = $quota->hotel_id;
            $data['number_of_days'] = \Carbon\Carbon::parse($request->start_date)
                ->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;

            // Upload photos if provided
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

            // Handle auction configuration
            if ($request->is_auction && $request->auction_minimum_price) {
                $data['minimum_price'] = $request->auction_minimum_price;
                $auctionDurationHours = (int) ($request->auction_duration_hours ?? 24);
                $data['auction_end_time'] = $request->auction_start_time 
                    ? \Carbon\Carbon::parse($request->auction_start_time)
                        ->addHours($auctionDurationHours)
                    : now()->addHours($auctionDurationHours);
            }

            $offer = RentalOffer::create($data);

            // Create auction if requested
            if ($request->is_auction) {
                $this->createAdvancedAuction($offer, $request);
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
     * Create advanced auction for an offer.
     */
    protected function createAdvancedAuction(RentalOffer $offer, Request $request)
    {
        $user = Auth::user();
        
        // Check auction limits
        if (!$this->canCreateAuction($user, $offer->quota_id)) {
            throw new \Exception('Limite de leilões excedido para este perfil/cota.');
        }

        $auctionDurationHours = (int) ($request->auction_duration_hours ?? 24);
        $auctionEndTime = $request->auction_start_time 
            ? \Carbon\Carbon::parse($request->auction_start_time)
                ->addHours($auctionDurationHours)
            : now()->addHours($auctionDurationHours);
        
        $auction = AdvancedAuction::create([
            'rental_offer_id' => $offer->id,
            'user_id' => $user->id,
            'start_time' => $request->auction_start_time ?? now(),
            'end_time' => $auctionEndTime,
            'minimum_price' => $request->auction_minimum_price,
            'duration_minutes' => $auctionDurationHours * 60,
            'bid_extension_minutes' => 1,
            'status' => 'scheduled',
            'auto_extend' => true,
            'max_extensions' => 3,
        ]);

        // Update auction limits
        $this->updateAuctionLimits($user, $offer->quota_id);

        return $auction;
    }

    /**
     * Check if user can create auction based on profile limits.
     */
    protected function canCreateAuction(User $user, $quotaId)
    {
        $profile = $user->profile;
        if (!$profile) {
            throw new \Exception('Perfil não encontrado. Complete seu cadastro primeiro.');
        }

        $config = $profile->getProfileConfig();
        $limitPeriod = $config['auction_limit_period'];
        $maxAuctions = $config['max_auctions'];

        // Verificar se o usuário não está penalizado
        if ($this->isUserPenalized($user)) {
            throw new \Exception('Usuário está penalizado e não pode criar leilões.');
        }

        // Verificar se a cota pertence ao usuário
        $quota = Quota::find($quotaId);
        if (!$quota || $quota->user_id !== $user->id) {
            throw new \Exception('Você não possui esta cota.');
        }

        // Verificar se a cota está ativa e publicada
        if (!$quota->is_published || $quota->quota_status !== 'active') {
            throw new \Exception('A cota deve estar ativa e publicada para criar leilões.');
        }

        $periodStart = $this->getPeriodStart($limitPeriod);
        $periodEnd = $this->getPeriodEnd($limitPeriod);

        // Verificar limites por período
        $auctionCount = AuctionLimit::where('user_id', $user->id)
            ->where('quota_id', $quotaId)
            ->where('limit_period', $limitPeriod)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->value('auctions_used') ?? 0;

        if ($auctionCount >= $maxAuctions) {
            $periodLabel = $this->getPeriodLabel($limitPeriod);
            throw new \Exception("Limite de leilões excedido. Máximo de {$maxAuctions} leilões por {$periodLabel} para esta cota.");
        }

        // Verificar se já existe leilão ativo para esta cota
        $activeAuction = AdvancedAuction::where('user_id', $user->id)
            ->whereHas('rentalOffer', function($query) use ($quotaId) {
                $query->where('quota_id', $quotaId);
            })
            ->whereIn('status', ['scheduled', 'active'])
            ->exists();

        if ($activeAuction) {
            throw new \Exception('Já existe um leilão ativo ou agendado para esta cota.');
        }

        return true;
    }

    /**
     * Check if user is penalized
     */
    protected function isUserPenalized(User $user): bool
    {
        // Verificar se o usuário tem ofertas penalizadas
        $penalizedOffers = RentalOffer::where('user_id', $user->id)
            ->where('is_penalized', true)
            ->where('penalty_until', '>', now())
            ->exists();

        return $penalizedOffers;
    }

    /**
     * Get period label for display
     */
    protected function getPeriodLabel($limitPeriod): string
    {
        return match($limitPeriod) {
            'year' => 'ano',
            'month' => 'mês',
            'usage' => 'uso',
            default => 'período'
        };
    }

    /**
     * Update auction limits for user and quota.
     */
    protected function updateAuctionLimits(User $user, $quotaId)
    {
        $profile = $user->profile;
        $config = $profile->getProfileConfig();
        $limitPeriod = $config['auction_limit_period'];

        $periodStart = $this->getPeriodStart($limitPeriod);
        $periodEnd = $this->getPeriodEnd($limitPeriod);

        AuctionLimit::updateOrCreate(
            [
                'user_id' => $user->id,
                'quota_id' => $quotaId,
                'limit_period' => $limitPeriod,
                'period_start' => $periodStart,
            ],
            [
                'auctions_used' => \DB::raw('auctions_used + 1'),
                'auctions_limit' => $config['max_auctions'],
                'period_end' => $periodEnd,
            ]
        );
    }

    /**
     * Get period start date based on limit period.
     */
    protected function getPeriodStart($limitPeriod)
    {
        switch ($limitPeriod) {
            case 'year':
                return now()->startOfYear();
            case 'month':
                return now()->startOfMonth();
            case 'usage':
                return now()->startOfDay();
            default:
                return now()->startOfYear();
        }
    }

    /**
     * Get period end date based on limit period.
     */
    protected function getPeriodEnd($limitPeriod)
    {
        switch ($limitPeriod) {
            case 'year':
                return now()->endOfYear();
            case 'month':
                return now()->endOfMonth();
            case 'usage':
                return now()->endOfDay();
            default:
                return now()->endOfYear();
        }
    }

    /**
     * Search for quotas with advanced filters.
     */
    public function search(Request $request)
    {
        $query = Quota::with(['hotel', 'user'])
            ->where('is_published', true)
            ->where('quota_status', 'active');

        // Apply filters
        if ($request->filled('city')) {
            $query->where('location', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('location', 'like', '%' . $request->state . '%');
        }

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        if ($request->filled('weeks')) {
            $query->where('weeks', $request->weeks);
        }

        if ($request->filled('seasonality')) {
            $query->where('seasonality', $request->seasonality);
        }

        if ($request->filled('min_price')) {
            $query->where('rental_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('rental_price', '<=', $request->max_price);
        }

        // Implementar busca por período com pelo menos um dia de sobreposição
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $searchStartDate = $request->start_date;
            $searchEndDate = $request->end_date;
            
            $query->where(function($q) use ($searchStartDate, $searchEndDate) {
                $q->where(function($subQuery) use ($searchStartDate, $searchEndDate) {
                    // Cota que começa antes ou no período de busca e termina durante ou depois
                    $subQuery->where('start_date', '<=', $searchEndDate)
                            ->where('end_date', '>=', $searchStartDate);
                });
            });
        } elseif ($request->filled('start_date')) {
            $query->where('end_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }

        if ($request->filled('number_of_rooms')) {
            $query->where('number_of_rooms', '>=', $request->number_of_rooms);
        }

        $quotas = $query->orderBy('created_at', 'desc')->paginate(12);
        $hotels = Hotel::where('is_active', true)->get();

        return view('quota-management.search', compact('quotas', 'hotels'));
    }

    /**
     * Search for rental offers with period overlap logic.
     */
    public function searchOffers(Request $request)
    {
        $query = RentalOffer::with(['quota', 'hotel', 'user'])
            ->where('status', 'active');

        // Apply filters
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Implementar busca por período com pelo menos um dia de sobreposição
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $searchStartDate = $request->start_date;
            $searchEndDate = $request->end_date;
            
            $query->where(function($q) use ($searchStartDate, $searchEndDate) {
                $q->where(function($subQuery) use ($searchStartDate, $searchEndDate) {
                    // Oferta que começa antes ou no período de busca e termina durante ou depois
                    $subQuery->where('start_date', '<=', $searchEndDate)
                            ->where('end_date', '>=', $searchStartDate);
                });
            });
        } elseif ($request->filled('start_date')) {
            $query->where('end_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }

        if ($request->filled('number_of_people')) {
            $query->where('number_of_people', '>=', $request->number_of_people);
        }

        if ($request->filled('offer_type')) {
            $query->where('offer_type', $request->offer_type);
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(12);
        $hotels = Hotel::where('is_active', true)->get();

        return view('rental-offers.search', compact('offers', 'hotels'));
    }

    /**
     * Delegate purchase to manager (paid service).
     */
    public function delegatePurchase(Request $request, Quota $quota)
    {
        $validator = Validator::make($request->all(), [
            'delegation_type' => 'required|in:full,partial',
            'max_price' => 'required|numeric|min:0',
            'preferences' => 'nullable|string|max:1000',
            'contact_method' => 'required|in:email,phone,whatsapp',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // TODO: Implement delegation system
            // This would create a delegation request for a manager to handle the purchase

            return redirect()->back()
                ->with('success', 'Solicitação de delegação enviada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao enviar solicitação de delegação.']);
        }
    }

    /**
     * Get quota statistics for dashboard.
     */
    public function getStats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_quotas' => $user->quotas()->count(),
            'published_quotas' => $user->quotas()->where('is_published', true)->count(),
            'active_offers' => $user->quotas()->withCount('activeRentalOffers')->get()->sum('active_rental_offers_count'),
            'total_revenue' => $user->quotas()->sum('rental_price'),
            'pending_transfers' => $user->quotas()->where('quota_status', 'transferred')->count(),
        ];

        return response()->json($stats);
    }
}
