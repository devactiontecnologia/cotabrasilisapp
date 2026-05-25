<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quota;
use App\Models\FavoriteList;
use App\Models\WishlistSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientPreferenceController extends Controller
{
    /**
     * Show favorites page with lists organized by city/hotel.
     */
    public function favorites()
    {
        $user = Auth::user();
        
        // Buscar apenas listas que têm pelo menos uma cota
        $favoriteLists = FavoriteList::where('user_id', $user->id)
            ->has('quotas') // Apenas listas que têm cotas
            ->with(['quotas' => function($query) {
                $query->with('user');
            }])
            ->get()
            ->groupBy(['transaction_type', 'type']);

        return view('client.favorites', compact('favoriteLists'));
    }

    /**
     * Add quota to favorite list (create list if needed).
     */
    public function toggleFavorite(Request $request, Quota $quota)
    {
        $user = Auth::user();
        
        // Determinar tipo e nome da lista
        $listType = $request->input('list_type', 'city'); // 'city', 'hotel' ou 'state'
        $transactionType = $request->input('transaction_type', 'rental'); // 'rental', 'purchase', 'exchange'
        $listName = match($listType) {
            'city' => ($quota->location ?? 'Sem localização'),
            'hotel' => ($quota->hotel_name ?? 'Sem hotel'),
            'state' => (function() use ($quota) {
                if (!$quota->location) {
                    return 'Sem estado';
                }
                // Tentar extrair o estado da location (formato: "Cidade, UF" ou "Cidade, Estado")
                $parts = preg_split('/,\s*/', $quota->location);
                if (count($parts) >= 2) {
                    return trim($parts[1]);
                }
                // Se não conseguir extrair, tentar pegar do hotel relacionado
                if ($quota->hotel && $quota->hotel->state) {
                    return $quota->hotel->state;
                }
                return 'Sem estado';
            })(),
            default => 'Sem nome'
        };

        // Buscar ou criar lista
        $favoriteList = FavoriteList::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => $listName,
                'type' => $listType,
                'transaction_type' => $transactionType,
            ]
        );

        // Verificar se já está na lista
        if ($favoriteList->quotas()->where('quota_id', $quota->id)->exists()) {
            $favoriteList->quotas()->detach($quota->id);
            
            // Verificar se a lista ficou vazia e deletá-la se necessário
            if ($favoriteList->quotas()->count() === 0) {
                $favoriteList->delete();
            }
            
            return back()->with('status', 'Cota removida dos favoritos.');
        }

        // Adicionar à lista
        $favoriteList->quotas()->attach($quota->id);

        return back()->with('status', 'Cota adicionada aos favoritos na lista "' . $listName . '".');
    }

    /**
     * Show wishlist page with saved searches.
     */
    public function wishlist()
    {
        $user = Auth::user();

        $sessionQuotaIds = array_filter(array_map('intval', session('user_wishlist', [])));
        if ($sessionQuotaIds !== []) {
            $user->wishlistQuotas()->syncWithoutDetaching($sessionQuotaIds);
            session()->forget('user_wishlist');
        }

        $wishlistSearches = WishlistSearch::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $wishlistQuotas = $user->wishlistQuotas()
            ->with('user')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('client.wishlist', compact('wishlistSearches', 'wishlistQuotas'));
    }

    /**
     * Save a search as wishlist (when no results found).
     */
    public function saveWishlistSearch(Request $request)
    {
        $user = Auth::user();

        // Validar que pelo menos algum critério foi preenchido
        $hasCriteria = $request->filled('hotel_name') || 
                       $request->filled('city') || 
                       $request->filled('state') || 
                       $request->filled('start_date') || 
                       $request->filled('end_date');

        if (!$hasCriteria) {
            return back()->with('error', 'Por favor, preencha pelo menos um critério de busca antes de salvar.');
        }

        try {
            $wishlistSearch = WishlistSearch::create([
                'user_id' => $user->id,
                'hotel_name' => $request->input('hotel_name'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'start_date' => $request->input('start_date') ? $request->input('start_date') : null,
                'end_date' => $request->input('end_date') ? $request->input('end_date') : null,
                'number_of_guests' => $request->input('number_of_guests') ? (int)$request->input('number_of_guests') : null,
                'number_of_rooms' => $request->input('number_of_rooms') ? (int)$request->input('number_of_rooms') : null,
                'nights' => $request->input('nights') ? (int)$request->input('nights') : null,
                'seasonality' => $request->input('seasonality'),
                'quota_type' => $request->input('quota_type'),
                'price_min' => $request->input('price_min') ? (float)$request->input('price_min') : null,
                'price_max' => $request->input('price_max') ? (float)$request->input('price_max') : null,
                'apartment_amenities' => $request->input('apartment_amenities'),
                'notified' => false,
            ]);

            return redirect()->route('client.wishlist')->with('success', 'Busca salva nos desejados! Você será avisado quando houver ofertas disponíveis.');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar wishlist: ' . $e->getMessage());
            return back()->with('error', 'Erro ao salvar busca. Por favor, tente novamente.');
        }
    }

    /**
     * Remove wishlist search.
     */
    public function removeWishlistSearch(WishlistSearch $wishlistSearch)
    {
        if ($wishlistSearch->user_id !== Auth::id()) {
            abort(403);
        }
        $wishlistSearch->delete();

        return back()->with('status', 'Busca removida dos desejados.');
    }

    /**
     * Marca ou desmarca cota como desejada (persistido — aparece em /cliente/desejados).
     */
    public function toggleWishlist(Request $request, Quota $quota)
    {
        $user = Auth::user();

        if ($user->wishlistQuotas()->where('quota_id', $quota->id)->exists()) {
            $user->wishlistQuotas()->detach($quota->id);
            $message = 'Cota removida dos desejados.';
        } else {
            $user->wishlistQuotas()->syncWithoutDetaching([$quota->id]);
            $message = 'Cota adicionada aos desejados.';
        }

        session()->forget('user_wishlist');

        return back()->with('status', $message);
    }
}


