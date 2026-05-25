<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Auction;
use App\Models\RentalOffer;
use App\Models\QuotaTransaction;

class AuctionController extends Controller
{
    /**
     * Display a listing of active auctions.
     */
    public function index()
    {
        $auctions = RentalOffer::with(['user', 'quota', 'hotel', 'auctions.user'])
            ->auctions()
            ->active()
            ->where('auction_end_time', '>', now())
            ->orderBy('auction_end_time', 'asc')
            ->paginate(12);

        return view('auctions.index', compact('auctions'));
    }

    /**
     * Show the specified auction.
     */
    public function show(RentalOffer $rentalOffer)
    {
        if (!$rentalOffer->is_auction) {
            abort(404, 'Leilão não encontrado.');
        }

        $rentalOffer->load(['user', 'quota', 'hotel', 'auctions.user']);
        $bids = $rentalOffer->auctions()->with('user')->orderBy('bid_amount', 'desc')->get();

        return view('auctions.show', compact('rentalOffer', 'bids'));
    }

    /**
     * Place a bid on an auction.
     */
    public function placeBid(Request $request, RentalOffer $rentalOffer)
    {
        if (!$rentalOffer->is_auction || !$rentalOffer->isAuctionActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Leilão não está ativo.'
            ], 400);
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'bid_amount' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user can bid
        if (!$this->canUserBid($user, $rentalOffer)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode participar deste leilão.'
            ], 403);
        }

        // Check if bid is valid
        if ($request->bid_amount < $rentalOffer->minimum_price) {
            return response()->json([
                'success' => false,
                'message' => 'Lance deve ser maior que o preço mínimo.'
            ], 400);
        }

        $highestBid = $rentalOffer->getHighestBidAmount();
        if ($highestBid > 0 && $request->bid_amount <= $highestBid) {
            return response()->json([
                'success' => false,
                'message' => 'Lance deve ser maior que o lance atual.'
            ], 400);
        }

        try {
            // Create new bid
            $auction = Auction::create([
                'rental_offer_id' => $rentalOffer->id,
                'user_id' => $user->id,
                'bid_amount' => $request->bid_amount,
                'bid_at' => now(),
                'message' => $request->message,
            ]);

            // Update offer price
            $rentalOffer->update(['price' => $request->bid_amount]);

            return response()->json([
                'success' => true,
                'message' => 'Lance realizado com sucesso!',
                'bid' => $auction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar lance. Tente novamente.'
            ], 500);
        }
    }

    /**
     * End an auction and process the winner.
     */
    public function endAuction(RentalOffer $rentalOffer)
    {
        $user = Auth::user();

        // Check if user owns the offer
        if ($rentalOffer->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode encerrar este leilão.'
            ], 403);
        }

        if (!$rentalOffer->is_auction || !$rentalOffer->isAuctionActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Leilão não está ativo.'
            ], 400);
        }

        try {
            $winningBid = $rentalOffer->getHighestBid();

            if ($winningBid) {
                // Mark winning bid
                $winningBid->update(['is_winning_bid' => true]);

                // Update offer status
                $rentalOffer->update([
                    'status' => 'negotiated',
                    'negotiated_at' => now(),
                    'negotiated_with' => $winningBid->user_id,
                ]);

                // Create transaction record
                QuotaTransaction::create([
                    'quota_id' => $rentalOffer->quota_id,
                    'owner_id' => $rentalOffer->user_id,
                    'renter_id' => $winningBid->user_id,
                    'start_date' => $rentalOffer->start_date,
                    'end_date' => $rentalOffer->end_date,
                    'price' => $winningBid->bid_amount,
                    'status' => 'pending',
                    'transaction_type' => 'auction',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Leilão encerrado com sucesso!',
                    'winner' => $winningBid->user
                ]);
            } else {
                // No bids, cancel offer
                $rentalOffer->update(['status' => 'cancelled']);

                return response()->json([
                    'success' => true,
                    'message' => 'Leilão cancelado - nenhum lance recebido.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar leilão. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Get auction bids for real-time updates.
     */
    public function getBids(RentalOffer $rentalOffer)
    {
        if (!$rentalOffer->is_auction) {
            abort(404);
        }

        $bids = $rentalOffer->auctions()
            ->with('user')
            ->orderBy('bid_amount', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'bids' => $bids,
            'highest_bid' => $rentalOffer->getHighestBidAmount(),
            'auction_active' => $rentalOffer->isAuctionActive()
        ]);
    }

    /**
     * Check if user can bid on auction.
     */
    private function canUserBid($user, $rentalOffer)
    {
        // User must be authenticated
        if (!$user) {
            return false;
        }

        // User cannot bid on their own offer
        if ($rentalOffer->user_id === $user->id) {
            return false;
        }

        // User must have completed KYC
        if (!$user->profile || !$user->profile->isKYCComplete()) {
            return false;
        }

        // User must have active profile
        if (!$user->is_active || $user->is_blocked) {
            return false;
        }

        return true;
    }
}
