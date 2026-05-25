<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\SaleOffer;
use App\Models\Quota;
use App\Models\Hotel;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\EmailService;

class SaleController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = SaleOffer::where('user_id', $user->id)
            ->with(['quota', 'hotel']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->latest()->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $user = Auth::user();
        $quotas = $user->quotas()->where('status', 'available')->get();
        $hotels = Hotel::where('is_active', true)->get();
        
        return view('sales.create', compact('quotas', 'hotels'));
    }

    public function getQuotaData($quotaId)
    {
        $user = Auth::user();
        
        $quota = Quota::where('id', $quotaId)
            ->where('user_id', $user->id)
            ->with('hotel')
            ->first();
        
        if (!$quota) {
            return response()->json(['error' => 'Cota não encontrada'], 404);
        }
        
        // Buscar hotel pelo relacionamento ou pelo nome
        $hotel = $quota->hotel;
        if (!$hotel && $quota->hotel_name) {
            $hotel = Hotel::where('name', $quota->hotel_name)->first();
        }
        
        // Buscar dados do perfil do usuário
        $profile = $user->profile;
        
        // Mapear sazonalidade de low/medium/high/peak para baixa/media/alta/altissima
        $seasonalityMap = [
            'low' => 'baixa',
            'medium' => 'media',
            'high' => 'alta',
            'peak' => 'altissima'
        ];
        $seasonality = $seasonalityMap[$quota->seasonality] ?? 'media';
        
        // Mapear tipo de cota do perfil
        $quotaType = null;
        if ($profile) {
            $hasQuotaValue = $profile->getRawOriginal('has_quota') ?? $profile->has_quota;
            if ($hasQuotaValue == '1' || $hasQuotaValue === 1 || $hasQuotaValue === true) {
                $quotaType = $profile->owner_quota_type ?? null;
            } elseif ($hasQuotaValue == '2' || $hasQuotaValue === 2) {
                $quotaType = $profile->gestor_quota_type ?? null;
            }
        }
        
        // Extrair estado e cidade do location
        $locationParts = explode(', ', $quota->location ?? '');
        $city = trim($locationParts[0] ?? '');
        $state = trim($locationParts[1] ?? '');
        
        // Se não encontrou no location, buscar do hotel
        if ($hotel) {
            if (empty($city)) $city = $hotel->city ?? '';
            if (empty($state)) $state = $hotel->state ?? '';
        }
        
        // Limitar semanas a 4 (máximo permitido no formulário)
        $weeks = min($quota->weeks ?? 1, 4);
        
        return response()->json([
            'hotel_id' => $hotel ? $hotel->id : null,
            'weeks' => $weeks,
            'number_of_rooms' => $quota->number_of_rooms ?? 1,
            'state' => $state,
            'city' => $city,
            'quota_type' => $quotaType,
            'seasonality' => $seasonality,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'quota_id' => 'nullable|exists:quotas,id',
            'hotel_id' => 'required|exists:hotels,id',
            'weeks' => 'required|integer|min:1|max:4',
            'number_of_rooms' => 'required|integer|min:1',
            'city' => 'required|string|max:100',
            'company' => 'nullable|string|max:255',
            'minimum_price' => 'nullable|numeric|min:0',
            'acceptable_price' => 'nullable|numeric|min:0',
            'desired_price' => 'nullable|numeric|min:0',
            'observations_by_price' => 'nullable|array',
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request) {
            $hasMin = $request->filled('minimum_price');
            $hasAcc = $request->filled('acceptable_price');
            $hasDes = $request->filled('desired_price');
            if (! $hasMin && ! $hasAcc && ! $hasDes) {
                return;
            }
            if (! $hasMin || ! $hasAcc || ! $hasDes) {
                $v->errors()->add('minimum_price', 'Informe os três preços (mínimo, aceitável e desejado) ou deixe todos em branco.');

                return;
            }
            $min = (float) $request->minimum_price;
            $acc = (float) $request->acceptable_price;
            $des = (float) $request->desired_price;
            if ($acc < $min) {
                $v->errors()->add('acceptable_price', 'O preço aceitável deve ser maior ou igual ao preço mínimo.');
            }
            if ($des < $acc) {
                $v->errors()->add('desired_price', 'O preço desejado deve ser maior ou igual ao preço aceitável.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Verificar se a cota pertence ao usuário (se fornecida)
        if ($request->quota_id) {
            $quota = Quota::where('id', $request->quota_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$quota) {
                return redirect()->back()->with('error', 'Cota não encontrada ou não pertence a você.');
            }
        }

        // Determinar status de negociação baseado no tipo de perfil
        $profile = $user->profile;
        $negotiationStatus = 'direct';
        
        if ($profile && $profile->profile_type === 'sabio') {
            $negotiationStatus = 'admin'; // Negociação direta com admin
        } elseif ($profile && $profile->profile_type === 'inteligente') {
            $negotiationStatus = 'direct'; // Vê preços, negociação direta
        } else {
            $negotiationStatus = 'direct'; // Tipo 1: não vê preços, mas pode criar oferta
        }

        $saleOffer = SaleOffer::create([
            'user_id' => $user->id,
            'quota_id' => $request->quota_id,
            'hotel_id' => $request->hotel_id,
            'weeks' => $request->weeks,
            'number_of_rooms' => $request->number_of_rooms,
            'city' => $request->city,
            'company' => $request->company,
            'minimum_price' => $request->filled('minimum_price') ? $request->minimum_price : null,
            'acceptable_price' => $request->filled('acceptable_price') ? $request->acceptable_price : null,
            'desired_price' => $request->filled('desired_price') ? $request->desired_price : null,
            'observations_by_price' => $request->observations_by_price,
            'status' => 'pending',
            'negotiation_status' => $negotiationStatus,
        ]);

        // Se for negociação com admin, notificar admin
        if ($negotiationStatus === 'admin') {
            $admin = User::where('is_admin', true)->first();
            if ($admin) {
                $this->notificationService->sendEmail(
                    $admin,
                    'Nova oferta de venda para negociação',
                    "O usuário {$user->name} criou uma oferta de venda que requer negociação direta."
                );
            }
        }

        // Enviar email de notificação
        try {
            $emailService = new EmailService();
            $emailService->sendSaleOfferCreatedEmail($user, $saleOffer);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de notificação de oferta de venda: ' . $e->getMessage());
            // Não interrompe o fluxo se o email falhar
        }

        return redirect()->route('sales.show', $saleOffer)
            ->with('success', 'Oferta de venda criada com sucesso!');
    }

    public function show(SaleOffer $saleOffer)
    {
        $user = Auth::user();
        
        if ($saleOffer->user_id !== $user->id && !$user->isAdmin()) {
            // Verificar se usuário pode ver preços
            if (!$saleOffer->canUserSeePrices($user)) {
                // Tipo 1: não vê preços nem nomes
                $saleOffer->makeHidden(['minimum_price', 'acceptable_price', 'desired_price', 'user_id']);
            }
        }

        $saleOffer->load(['quota', 'hotel', 'user', 'admin']);

        return view('sales.show', compact('saleOffer'));
    }

    public function edit(SaleOffer $saleOffer)
    {
        $user = Auth::user();
        
        // Verificar se a oferta pertence ao usuário
        if ($saleOffer->user_id !== $user->id) {
            return redirect()->route('sales.index')
                ->with('error', 'Você não tem permissão para editar esta oferta.');
        }
        
        // Verificar se pode editar (não pode se houver negociação em andamento)
        $canEdit = $this->canEditOffer($saleOffer);
        if (!$canEdit) {
            return redirect()->route('sales.show', $saleOffer)
                ->with('error', 'Não é possível editar esta oferta pois há uma negociação em andamento.');
        }
        
        $quotas = $user->quotas()->where('status', 'available')->get();
        $hotels = Hotel::where('is_active', true)->get();
        
        return view('sales.edit', compact('saleOffer', 'quotas', 'hotels'));
    }
    
    public function update(Request $request, SaleOffer $saleOffer)
    {
        $user = Auth::user();
        
        // Verificar se a oferta pertence ao usuário
        if ($saleOffer->user_id !== $user->id) {
            return redirect()->route('sales.index')
                ->with('error', 'Você não tem permissão para editar esta oferta.');
        }
        
        // Verificar se pode editar
        $canEdit = $this->canEditOffer($saleOffer);
        if (!$canEdit) {
            return redirect()->route('sales.show', $saleOffer)
                ->with('error', 'Não é possível editar esta oferta pois há uma negociação em andamento.');
        }

        $validator = Validator::make($request->all(), [
            'quota_id' => 'nullable|exists:quotas,id',
            'hotel_id' => 'required|exists:hotels,id',
            'weeks' => 'required|integer|min:1|max:4',
            'number_of_rooms' => 'required|integer|min:1',
            'city' => 'required|string|max:100',
            'company' => 'nullable|string|max:255',
            'minimum_price' => 'required|numeric|min:0',
            'acceptable_price' => 'required|numeric|min:0|gte:minimum_price',
            'desired_price' => 'required|numeric|min:0|gte:acceptable_price',
            'observations_by_price' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Verificar se a cota pertence ao usuário (se fornecida)
        if ($request->quota_id) {
            $quota = Quota::where('id', $request->quota_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$quota) {
                return redirect()->back()->with('error', 'Cota não encontrada ou não pertence a você.')->withInput();
            }
        }

        // Atualizar a oferta
        $saleOffer->update([
            'quota_id' => $request->quota_id,
            'hotel_id' => $request->hotel_id,
            'weeks' => $request->weeks,
            'number_of_rooms' => $request->number_of_rooms,
            'city' => $request->city,
            'company' => $request->company,
            'minimum_price' => $request->minimum_price,
            'acceptable_price' => $request->acceptable_price,
            'desired_price' => $request->desired_price,
            'observations_by_price' => $request->observations_by_price,
        ]);

        return redirect()->route('sales.show', $saleOffer)
            ->with('success', 'Oferta de venda atualizada com sucesso!');
    }
    
    /**
     * Verificar se a oferta pode ser editada
     */
    private function canEditOffer(SaleOffer $saleOffer)
    {
        // Se a oferta está cancelada ou vendida, não pode editar
        if ($saleOffer->status === 'cancelled' || $saleOffer->status === 'sold') {
            return false;
        }
        
        // Verificar se há transações em andamento
        $hasActiveTransaction = \App\Models\QuotaTransaction::where('quota_id', $saleOffer->quota_id)
            ->whereIn('status', [
                \App\Models\QuotaTransaction::STATUS_PENDING,
                \App\Models\QuotaTransaction::STATUS_NEGOTIATING,
                \App\Models\QuotaTransaction::STATUS_PAYMENT_PENDING,
                \App\Models\QuotaTransaction::STATUS_DOCUMENT_PENDING
            ])
            ->exists();
        
        // Se há transação ativa ou está em negociação, não pode editar
        if ($hasActiveTransaction || $saleOffer->status === 'negotiating') {
            return false;
        }
        
        return true;
    }

    public function negotiate(Request $request, SaleOffer $saleOffer)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && $saleOffer->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Você não tem permissão para negociar esta oferta.');
        }

        $validator = Validator::make($request->all(), [
            'offer_price' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $offerPrice = $request->offer_price;

        if ($saleOffer->minimum_price === null) {
            return redirect()->back()->with('error', 'Esta oferta não possui preço mínimo definido; não é possível enviar proposta por valor aqui.');
        }

        if ($offerPrice < $saleOffer->minimum_price) {
            return redirect()->back()->with('error', 'O preço oferecido está abaixo do mínimo aceito.');
        }

        if ($saleOffer->desired_price !== null && $offerPrice > $saleOffer->desired_price) {
            return redirect()->back()->with('error', 'O preço oferecido está acima do valor desejado pelo anunciante.');
        }

        // Atualizar status
        $saleOffer->update([
            'status' => 'negotiating',
            'negotiation_status' => $user->isAdmin() ? 'admin' : 'direct',
            'admin_id' => $user->isAdmin() ? $user->id : $saleOffer->admin_id,
        ]);

        // Notificar o outro lado
        $otherUser = $saleOffer->user_id === $user->id ? User::find($saleOffer->admin_id) : $saleOffer->user;
        if ($otherUser) {
            $this->notificationService->sendEmail(
                $otherUser,
                'Nova proposta de negociação',
                "Uma nova proposta de R$ " . number_format($offerPrice, 2, ',', '.') . " foi feita para sua oferta de venda."
            );
        }

        return redirect()->back()->with('success', 'Proposta enviada com sucesso!');
    }
}
