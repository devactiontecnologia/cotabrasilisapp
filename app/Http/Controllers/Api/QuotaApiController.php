<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quota;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuotaApiController extends Controller
{
    /**
     * Busca pública de cotas (sem auth)
     */
    public function search(Request $request)
    {
        $query = Quota::where('status', Quota::STATUS_AVAILABLE)
            ->with(['hotel', 'rentalOffers', 'exchangeOffers', 'saleOffers'])
            ->whereHasActiveRentalListing();

        if ($request->filled('hotel_name')) {
            $query->where('hotel_name', 'like', '%' . $request->hotel_name . '%');
        }
        if ($request->filled('city')) {
            $query->where('location', 'like', '%' . $request->city . '%');
        }
        if ($request->filled('state')) {
            $hotelNames = Hotel::where('state', $request->state)->pluck('name');
            $query->whereIn('hotel_name', $hotelNames);
        }
        if ($request->filled('guests')) {
            $query->where('number_of_guests', '>=', $request->guests);
        }
        if ($request->filled('year')) {
            $year = (int) $request->year;
            $query->where(function ($q) use ($year) {
                $q->where('start_date', '<=', "{$year}-12-31")
                  ->where('end_date', '>=', "{$year}-01-01");
            });
        }
        if ($request->filled('month') && $request->filled('year')) {
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $start = "{$request->year}-{$month}-01";
            $end = date('Y-m-t', strtotime($start));
            $query->where('start_date', '<=', $end)->where('end_date', '>=', $start);
        }

        $quotas = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $quotas->getCollection()->map(fn ($q) => $this->quotaToArray($q)),
            'meta' => [
                'current_page' => $quotas->currentPage(),
                'last_page' => $quotas->lastPage(),
                'per_page' => $quotas->perPage(),
                'total' => $quotas->total(),
            ],
        ]);
    }

    /**
     * Cotas em destaque
     */
    public function featured(Request $request)
    {
        $quotas = Quota::where('status', Quota::STATUS_AVAILABLE)
            ->where('is_published', true)
            ->where('payment_status', 'paid')
            ->withMarketplaceListing()
            ->with(['hotel', 'rentalOffers', 'exchangeOffers', 'saleOffers'])
            ->latest('published_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quotas->map(fn ($q) => $this->quotaToArray($q)),
        ]);
    }

    /**
     * Detalhe de uma cota
     */
    public function show(Request $request, Quota $quota)
    {
        $quota->load(['hotel', 'user', 'rentalOffers', 'exchangeOffers', 'saleOffers']);
        return response()->json([
            'success' => true,
            'data' => $this->quotaToArray($quota, true),
        ]);
    }

    /**
     * Minhas cotas (auth)
     */
    public function myQuotas(Request $request)
    {
        $quotas = Quota::where('user_id', $request->user()->id)
            ->with('hotel')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quotas->map(fn ($q) => $this->quotaToArray($q)),
        ]);
    }

    /**
     * Lista simplificada para dropdown (auth)
     */
    public function myQuotasList(Request $request)
    {
        $quotas = Quota::where('user_id', $request->user()->id)
            ->select('id', 'hotel_name', 'start_date', 'end_date', 'is_fractioned')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quotas->map(fn ($q) => [
                'id' => $q->id,
                'label' => $q->hotel_name . ' - ' . ($q->start_date?->format('d/m/Y') ?? '-') . ' a ' . ($q->end_date?->format('d/m/Y') ?? '-'),
                'hotel_name' => $q->hotel_name,
                'start_date' => $q->start_date?->format('Y-m-d'),
                'end_date' => $q->end_date?->format('Y-m-d'),
                'is_fractioned' => (bool) $q->is_fractioned,
            ]),
        ]);
    }

    private function quotaToArray($quota, $full = false)
    {
        $hotel = $quota->relationLoaded('hotel') ? $quota->hotel : null;
        $images = ($hotel && isset($hotel->images)) ? (is_array($hotel->images) ? $hotel->images : []) : [];
        $imageUrls = collect($images)->map(function ($img) {
            return $this->imageToUrl($img);
        })->filter()->values()->all();

        $tx = request()->input('transaction_type', 'rent');
        if ($tx === 'rental') {
            $tx = 'rent';
        }
        if ($tx === 'purchase') {
            $tx = 'buy';
        }
        $listPrice = $quota->getMarketplaceListPrice($tx);

        $arr = [
            'id' => $quota->id,
            'hotel_name' => $quota->hotel_name,
            'location' => $quota->location,
            'start_date' => $quota->start_date?->format('Y-m-d'),
            'end_date' => $quota->end_date?->format('Y-m-d'),
            'number_of_guests' => $quota->number_of_guests,
            'rental_price' => (float) ($listPrice ?? $quota->rental_price ?? 0),
            'marketplace_list_price' => $listPrice,
            'number_of_rooms' => $quota->number_of_rooms ?? null,
            'is_fractioned' => (bool) $quota->is_fractioned,
            'hotel' => $hotel ? [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'city' => $hotel->city ?? null,
                'state' => $hotel->state ?? null,
                'images' => $imageUrls,
                'amenities' => $hotel->amenities ?? [],
            ] : null,
        ];
        if ($full) {
            $arr['observations'] = $quota->observations;
            $arr['allowed_uses'] = $quota->allowed_uses ?? [];
            $arr['fraction_details'] = $quota->fraction_details;
        }
        return $arr;
    }

    private function imageToUrl($path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
