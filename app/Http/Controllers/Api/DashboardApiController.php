<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quota;
use App\Models\RentalOffer;
use App\Models\QuotaTransaction;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Resumo do dashboard do usuário
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $quotasCount = Quota::where('user_id', $user->id)->count();
        $offersCount = RentalOffer::where('user_id', $user->id)->active()->count();
        $transactionsCount = QuotaTransaction::where('owner_id', $user->id)
            ->orWhere('renter_id', $user->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'quotas_count' => $quotasCount,
                'offers_count' => $offersCount,
                'transactions_count' => $transactionsCount,
            ],
        ]);
    }
}
