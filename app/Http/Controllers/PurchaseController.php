<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\PurchaseRequest;
use App\Models\Hotel;
use App\Models\User;
use App\Services\NotificationService;

class PurchaseController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = PurchaseRequest::where('user_id', $user->id)
            ->with(['hotel']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest()->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function request(Request $request)
    {
        return redirect()->route('quotas.index', array_merge($request->all(), [
            'transaction_type' => 'purchase',
            'hide_buttons' => true,
        ]));
    }

    /** @deprecated Use purchases.request */
    public function refine(Request $request)
    {
        return $this->request($request);
    }

    public function create()
    {
        $hotels = Hotel::where('is_active', true)->get();
        
        return view('purchases.create', compact('hotels'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'hotel_id' => 'nullable|exists:hotels,id',
            'weeks' => 'nullable|integer|min:1|max:4',
            'month' => 'nullable|integer|min:1|max:12',
            'period_type' => 'required|in:fixo,flexivel',
            'city' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
            'price_range_min' => 'nullable|numeric|min:0',
            'price_range_max' => 'nullable|numeric|min:0|gte:price_range_min',
            'observations' => 'nullable|string|max:1000',
            'delegated_to_admin' => 'boolean',
            'max_price' => 'required_if:delegated_to_admin,true|nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $purchaseRequest = PurchaseRequest::create([
            'user_id' => $user->id,
            'hotel_id' => $request->hotel_id,
            'weeks' => $request->weeks,
            'month' => $request->month,
            'period_type' => $request->period_type,
            'city' => $request->city,
            'company' => $request->company,
            'price_range_min' => $request->price_range_min,
            'price_range_max' => $request->price_range_max,
            'observations' => $request->observations,
            'status' => 'active',
            'delegated_to_admin' => $request->has('delegated_to_admin'),
            'max_price' => $request->max_price,
            'purchase_fee_percentage' => 10.00, // Taxa inicial 10%
        ]);

        // Se delegado ao admin, notificar admin
        if ($purchaseRequest->delegated_to_admin) {
            $admin = User::where('is_admin', true)->first();
            if ($admin) {
                $this->notificationService->sendEmail(
                    $admin,
                    'Nova solicitação de compra delegada',
                    "O usuário {$user->name} delegou uma compra ao administrador. Preço máximo: R$ " . number_format($purchaseRequest->max_price, 2, ',', '.')
                );
            }
        }

        return redirect()->route('purchases.show', $purchaseRequest)
            ->with('success', 'Solicitação de compra criada com sucesso!');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $user = Auth::user();
        
        if ($purchaseRequest->user_id !== $user->id && !$user->isAdmin()) {
            return redirect()->route('purchases.index')
                ->with('error', 'Você não tem permissão para visualizar esta solicitação.');
        }

        $purchaseRequest->load(['hotel', 'user']);

        // Buscar matches potenciais
        $matches = $this->findMatches($purchaseRequest);

        return view('purchases.show', compact('purchaseRequest', 'matches'));
    }

    public function delegate(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = Auth::user();
        
        if ($purchaseRequest->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Você não tem permissão para delegar esta solicitação.');
        }

        $validator = Validator::make($request->all(), [
            'max_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $purchaseRequest->delegateToAdmin($request->max_price);

        // Notificar admin
        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            $this->notificationService->sendEmail(
                $admin,
                'Solicitação de compra delegada',
                "O usuário {$user->name} delegou uma compra ao administrador. Preço máximo: R$ " . number_format($request->max_price, 2, ',', '.')
            );
        }

        return redirect()->back()->with('success', 'Compra delegada ao administrador com sucesso!');
    }

    /**
     * Encontrar matches potenciais para a solicitação de compra
     */
    private function findMatches(PurchaseRequest $purchaseRequest)
    {
        // Buscar ofertas de venda que correspondem aos critérios
        $query = \App\Models\SaleOffer::where('status', 'pending')
            ->orWhere('status', 'negotiating');

        if ($purchaseRequest->hotel_id) {
            $query->where('hotel_id', $purchaseRequest->hotel_id);
        }

        if ($purchaseRequest->city) {
            $query->where('city', 'like', '%' . $purchaseRequest->city . '%');
        }

        if ($purchaseRequest->weeks) {
            $query->where('weeks', $purchaseRequest->weeks);
        }

        if ($purchaseRequest->price_range_min && $purchaseRequest->price_range_max) {
            $query->where(function($q) use ($purchaseRequest) {
                $q->whereBetween('desired_price', [$purchaseRequest->price_range_min, $purchaseRequest->price_range_max])
                  ->orWhereBetween('acceptable_price', [$purchaseRequest->price_range_min, $purchaseRequest->price_range_max])
                  ->orWhereBetween('minimum_price', [$purchaseRequest->price_range_min, $purchaseRequest->price_range_max]);
            });
        }

        return $query->limit(10)->get();
    }
}
