<?php

namespace App\Http\Controllers;

use App\Models\Quota;
use App\Models\FavoriteList;
use App\Models\WishlistSearch;
use App\Services\WishlistMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientPreferenceController extends Controller
{
    public function __construct(
        protected WishlistMatchingService $wishlistMatching
    ) {}

    /**
     * Show favorites page with lists organized by transaction type and location.
     */
    public function favorites()
    {
        $user = Auth::user();

        $favoriteLists = FavoriteList::where('user_id', $user->id)
            ->has('quotas')
            ->with(['quotas' => function ($query) {
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

        $listType = $request->input('list_type', 'city');
        $transactionType = $this->wishlistMatching->normalizeTransactionType(
            $request->input('transaction_type', 'rental')
        );
        $listName = match ($listType) {
            'city' => ($quota->location ?? 'Sem localização'),
            'hotel' => ($quota->hotel_name ?? 'Sem hotel'),
            'state' => $this->wishlistMatching->extractStateFromQuota($quota),
            default => 'Sem nome'
        };

        $favoriteList = FavoriteList::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => $listName,
                'type' => $listType,
                'transaction_type' => $transactionType,
            ]
        );

        if ($favoriteList->quotas()->where('quota_id', $quota->id)->exists()) {
            $favoriteList->quotas()->detach($quota->id);

            if ($favoriteList->quotas()->count() === 0) {
                $favoriteList->delete();
            }

            return back()->with('status', 'Cota removida dos favoritos.');
        }

        $favoriteList->quotas()->attach($quota->id);

        return back()->with('status', 'Cota adicionada aos favoritos na lista "'.$listName.'".');
    }

    /**
     * Desejados: cotas marcadas + buscas salvas, agrupadas Aluguel → Troca → Compra → Estado/Cidade/Hotel.
     */
    public function wishlist()
    {
        $user = Auth::user();

        $sessionQuotaIds = array_filter(array_map('intval', session('user_wishlist', [])));
        if ($sessionQuotaIds !== []) {
            $user->wishlistQuotas()->syncWithoutDetaching(
                collect($sessionQuotaIds)->mapWithKeys(fn ($id) => [
                    $id => [
                        'transaction_type' => 'rental',
                        'list_type' => 'city',
                    ],
                ])->all()
            );
            session()->forget('user_wishlist');
        }

        $wishlistSearches = WishlistSearch::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $wishlistQuotas = $user->wishlistQuotas()
            ->with(['user', 'hotel'])
            ->orderByPivot('created_at', 'desc')
            ->get();

        $groupedSearches = $this->wishlistMatching->groupWishlistSearches($wishlistSearches);
        $groupedQuotas = $this->wishlistMatching->groupWishlistQuotas($wishlistQuotas);

        $transactionTypes = [
            'rental' => ['title' => 'Alugar', 'icon' => 'fa-calendar-check', 'color' => 'primary'],
            'exchange' => ['title' => 'Troca', 'icon' => 'fa-exchange-alt', 'color' => 'info'],
            'purchase' => ['title' => 'Comprar', 'icon' => 'fa-shopping-cart', 'color' => 'success'],
        ];
        $listTypes = [
            'state' => ['title' => 'Por Estado', 'icon' => 'fa-map', 'color' => 'info'],
            'city' => ['title' => 'Por Cidade', 'icon' => 'fa-map-marker-alt', 'color' => 'primary'],
            'hotel' => ['title' => 'Por Hotel', 'icon' => 'fa-hotel', 'color' => 'success'],
        ];

        return view('client.wishlist', compact(
            'groupedSearches',
            'groupedQuotas',
            'transactionTypes',
            'listTypes',
            'wishlistSearches',
            'wishlistQuotas'
        ));
    }

    /**
     * Save a search as wishlist (when no results found).
     */
    public function saveWishlistSearch(Request $request)
    {
        $user = Auth::user();

        $hasCriteria = $request->filled('hotel_name')
            || $request->filled('city')
            || $request->filled('state')
            || $request->filled('start_date')
            || $request->filled('end_date');

        if (! $hasCriteria) {
            return back()->with('error', 'Por favor, preencha pelo menos um critério de busca antes de salvar.');
        }

        $transactionType = $this->wishlistMatching->normalizeTransactionType(
            $request->input('transaction_type', 'rental')
        );
        $listType = $this->wishlistMatching->inferListType(
            $request->input('hotel_name'),
            $request->input('city'),
            $request->input('state')
        );

        try {
            $wishlistSearch = WishlistSearch::create([
                'user_id' => $user->id,
                'transaction_type' => $transactionType,
                'list_type' => $listType,
                'hotel_name' => $request->input('hotel_name'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'start_date' => $request->input('start_date') ?: null,
                'end_date' => $request->input('end_date') ?: null,
                'number_of_guests' => $request->input('number_of_guests') ? (int) $request->input('number_of_guests') : null,
                'number_of_rooms' => $request->input('number_of_rooms') ? (int) $request->input('number_of_rooms') : null,
                'nights' => $request->input('nights') ? (int) $request->input('nights') : null,
                'seasonality' => $request->input('seasonality'),
                'quota_type' => $request->input('quota_type'),
                'price_min' => $request->input('price_min') ? (float) $request->input('price_min') : null,
                'price_max' => $request->input('price_max') ? (float) $request->input('price_max') : null,
                'apartment_amenities' => $request->input('apartment_amenities'),
                'notified' => false,
            ]);

            $this->wishlistMatching->processSavedSearch($wishlistSearch);

            return redirect()->route('client.wishlist')->with(
                'success',
                'Busca salva nos Desejados! Avisaremos você quando houver ofertas e alertaremos proprietários compatíveis para publicar.'
            );
        } catch (\Exception $e) {
            Log::error('Erro ao salvar wishlist: '.$e->getMessage());

            return back()->with('error', 'Erro ao salvar busca. Por favor, tente novamente.');
        }
    }

    public function removeWishlistSearch(WishlistSearch $wishlistSearch)
    {
        if ($wishlistSearch->user_id !== Auth::id()) {
            abort(403);
        }
        $wishlistSearch->delete();

        return back()->with('status', 'Busca removida dos desejados.');
    }

    public function toggleWishlist(Request $request, Quota $quota)
    {
        $user = Auth::user();
        $transactionType = $this->wishlistMatching->normalizeTransactionType(
            $request->input('transaction_type', 'rental')
        );
        [$listType] = $this->wishlistMatching->listBucketForQuota(
            $quota,
            $request->input('list_type')
        );

        if ($user->wishlistQuotas()->where('quota_id', $quota->id)->exists()) {
            $user->wishlistQuotas()->detach($quota->id);
            $message = 'Cota removida dos desejados.';
        } else {
            $user->wishlistQuotas()->attach($quota->id, [
                'transaction_type' => $transactionType,
                'list_type' => $listType,
            ]);
            $message = 'Cota adicionada aos desejados.';
        }

        session()->forget('user_wishlist');

        return back()->with('status', $message);
    }
}
