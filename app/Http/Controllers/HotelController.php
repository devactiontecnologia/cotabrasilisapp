<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;

class HotelController extends Controller
{
    /**
     * Public search endpoint for hotels autocomplete.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->get('query', ''));
        $state = trim((string) $request->get('state', ''));
        $type = trim((string) $request->get('type', 'hotel')); // 'hotel' ou 'city'

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        // Construir query base
        $results = Hotel::query();

        if ($type === 'city') {
            $results->where('is_active', true)
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->whereRaw('LOWER(city) LIKE ?', ['%' . strtolower($query) . '%']);

            if (!empty($state)) {
                $results->where('state', $state);
            }

            $cities = $results->select('city', 'state')
                ->distinct()
                ->orderBy('city')
                ->orderBy('state')
                ->limit(25)
                ->get();
            
            return response()->json([
                'data' => $cities->map(function ($item) {
                    return [
                        'name' => $item->city,
                        'city' => $item->city,
                        'state' => $item->state,
                        'type' => 'city',
                        'label' => $item->city . ', ' . $item->state,
                    ];
                }),
            ]);
        } else {
            // Buscar hotéis pelo nome (substring, case-insensitive) — alinhado ao refine da busca interna
            $results->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%']);
            
            // Se estado foi fornecido, filtrar por estado
            if (!empty($state)) {
                $results->where('state', $state);
            }
            
            $hotels = $results->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'city', 'state', 'is_functioning']);

            return response()->json([
                'data' => $hotels->map(function ($hotel) {
                    return [
                        'id' => $hotel->id,
                        'name' => $hotel->name,
                        'city' => $hotel->city,
                        'state' => $hotel->state,
                        'is_functioning' => (bool) $hotel->is_functioning,
                        'type' => 'hotel',
                        'label' => trim($hotel->name . ' - ' . ($hotel->city ?? '') . ' ' . ($hotel->state ?? '')),
                    ];
                }),
            ]);
        }
    }

    /**
     * Public JSON details for a given hotel.
     */
    public function show(Hotel $hotel)
    {
        return response()->json([
            'data' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'city' => $hotel->city,
                'state' => $hotel->state,
                'is_functioning' => (bool) $hotel->is_functioning,
                'description' => $hotel->description,
                'website' => $hotel->website,
                'amenities' => $hotel->amenities,
            ],
        ]);
    }
}

