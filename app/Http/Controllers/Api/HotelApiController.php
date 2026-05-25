<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelApiController extends Controller
{
    /**
     * Lista hotéis
     */
    public function index(Request $request)
    {
        $query = Hotel::where('is_active', true);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%");
            });
        }
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $hotels = $query->orderBy('name')->limit(50)->get(['id', 'name', 'city', 'state', 'images', 'amenities', 'stars']);

        return response()->json([
            'success' => true,
            'data' => $hotels->map(fn ($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'city' => $h->city ?? null,
                'state' => $h->state ?? null,
                'images' => $h->images ?? [],
                'amenities' => $h->amenities ?? [],
                'stars' => $h->stars ?? null,
            ]),
        ]);
    }

    /**
     * Detalhe do hotel
     */
    public function show(Request $request, Hotel $hotel)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'city' => $hotel->city ?? null,
                'state' => $hotel->state ?? null,
                'address' => $hotel->address ?? null,
                'phone' => $hotel->phone ?? null,
                'email' => $hotel->email ?? null,
                'description' => $hotel->description ?? null,
                'images' => $hotel->images ?? [],
                'amenities' => $hotel->amenities ?? [],
                'stars' => $hotel->stars ?? null,
                'website' => $hotel->website ?? null,
            ],
        ]);
    }
}
