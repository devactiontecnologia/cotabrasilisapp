<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RentalOffer;
use Illuminate\Http\Request;

class RentalOfferApiController extends Controller
{
    /**
     * Lista ofertas de aluguel (pública)
     */
    public function index(Request $request)
    {
        $query = RentalOffer::with(['quota', 'hotel'])->active()->orderBy('created_at', 'desc');

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        $offers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $offers->getCollection()->map(fn ($o) => $this->offerToArray($o)),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'total' => $offers->total(),
            ],
        ]);
    }

    /**
     * Detalhe de uma oferta
     */
    public function show(Request $request, RentalOffer $rentalOffer)
    {
        $rentalOffer->load('quota', 'hotel');
        return response()->json([
            'success' => true,
            'data' => $this->offerToArray($rentalOffer, true),
        ]);
    }

    /**
     * Minhas ofertas (auth)
     */
    public function myOffers(Request $request)
    {
        $offers = RentalOffer::where('user_id', $request->user()->id)
            ->with(['quota', 'hotel'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offers->map(fn ($o) => $this->offerToArray($o)),
        ]);
    }

    private function offerToArray($offer, $full = false)
    {
        $hotel = $offer->relationLoaded('hotel') ? $offer->hotel : null;
        $hotelImages = ($hotel && isset($hotel->images) && is_array($hotel->images))
            ? collect($hotel->images)->map(fn ($p) => $this->imageToUrl($p))->filter()->values()->all()
            : [];
        $arr = [
            'id' => $offer->id,
            'title' => $offer->title ?? $offer->display_title ?? 'Oferta de Aluguel',
            'city' => $offer->city,
            'state' => $offer->state,
            'start_date' => $offer->start_date?->format('Y-m-d'),
            'end_date' => $offer->end_date?->format('Y-m-d'),
            'number_of_days' => $offer->number_of_days,
            'number_of_people' => $offer->number_of_people,
            'price' => (float) $offer->price,
            'is_auction' => (bool) $offer->is_auction,
            'is_fractioned' => (bool) $offer->is_fractioned,
            'hotel' => $hotel ? [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'city' => $hotel->city ?? null,
                'state' => $hotel->state ?? null,
                'images' => $hotelImages,
            ] : null,
        ];
        if ($full) {
            $arr['description'] = $offer->description;
            $arr['observations'] = $offer->observations;
            $photos = $offer->photos ?? [];
        $arr['photos'] = collect($photos)->map(fn ($p) => $this->imageToUrl($p))->filter()->values()->all();
            $arr['accepts_exchange'] = (bool) ($offer->accepts_exchange ?? false);
            $arr['accepts_sale'] = (bool) ($offer->accepts_sale ?? false);
            $arr['views_count'] = $offer->views_count ?? 0;
        }
        return $arr;
    }

    private function imageToUrl($path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with((string) $path, 'http://') || str_starts_with((string) $path, 'https://')) {
            return $path;
        }
        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
