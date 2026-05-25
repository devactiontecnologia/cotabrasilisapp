<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RentalOffer;
use App\Models\SaleOffer;
use App\Models\ExchangeOffer;
use App\Models\QuotaTransaction;

class OfferOptimizationController extends Controller
{
    /**
     * Exibir tela de otimização de êxito
     */
    public function index()
    {
        $user = Auth::user();
        
        // Buscar todas as ofertas do usuário
        $rentalOffers = RentalOffer::where('user_id', $user->id)
            ->with(['quota', 'hotel'])
            ->latest()
            ->get()
            ->map(function ($offer) {
                $offer->can_edit = $this->canEditOffer($offer, 'rental');
                return $offer;
            });
        
        $saleOffers = SaleOffer::where('user_id', $user->id)
            ->with(['quota', 'hotel'])
            ->latest()
            ->get()
            ->map(function ($offer) {
                $offer->can_edit = $this->canEditOffer($offer, 'sale');
                return $offer;
            });
        
        $exchangeOffers = ExchangeOffer::where('user_id', $user->id)
            ->with(['quota'])
            ->latest()
            ->get()
            ->map(function ($offer) {
                $offer->can_edit = $this->canEditOffer($offer, 'exchange');
                return $offer;
            });
        
        return view('offer-optimization.index', compact('rentalOffers', 'saleOffers', 'exchangeOffers'));
    }
    
    /**
     * Verificar se a oferta pode ser editada
     */
    private function canEditOffer($offer, $type)
    {
        // Se a oferta está cancelada ou expirada, não pode editar
        if ($offer->status === 'cancelled' || $offer->status === 'expired') {
            return false;
        }
        
        // Verificar se há transações em andamento
        $hasActiveTransaction = false;
        
        if ($type === 'rental') {
            // Verificar se há transação de aluguel em andamento
            $hasActiveTransaction = QuotaTransaction::where('quota_id', $offer->quota_id)
                ->where('transaction_type', QuotaTransaction::TYPE_RENTAL)
                ->whereIn('status', [
                    QuotaTransaction::STATUS_PENDING,
                    QuotaTransaction::STATUS_NEGOTIATING,
                    QuotaTransaction::STATUS_PAYMENT_PENDING,
                    QuotaTransaction::STATUS_DOCUMENT_PENDING
                ])
                ->exists();
            
            // Verificar se a oferta já foi negociada
            if ($offer->status === 'negotiated' || $offer->negotiated_at) {
                return false;
            }
        } elseif ($type === 'sale') {
            // Verificar se há transação de compra em andamento
            $hasActiveTransaction = QuotaTransaction::where('quota_id', $offer->quota_id)
                ->where('transaction_type', 'purchase')
                ->whereIn('status', [
                    QuotaTransaction::STATUS_PENDING,
                    QuotaTransaction::STATUS_NEGOTIATING,
                    QuotaTransaction::STATUS_PAYMENT_PENDING,
                    QuotaTransaction::STATUS_DOCUMENT_PENDING
                ])
                ->exists();
            
            // Verificar se a oferta já foi vendida ou está em negociação
            if ($offer->status === 'sold' || $offer->status === 'negotiating') {
                return false;
            }
        } elseif ($type === 'exchange') {
            // Verificar se há transação de troca em andamento
            $hasActiveTransaction = QuotaTransaction::where('quota_id', $offer->quota_id)
                ->where('transaction_type', QuotaTransaction::TYPE_EXCHANGE)
                ->whereIn('status', [
                    QuotaTransaction::STATUS_PENDING,
                    QuotaTransaction::STATUS_NEGOTIATING,
                    QuotaTransaction::STATUS_PAYMENT_PENDING,
                    QuotaTransaction::STATUS_DOCUMENT_PENDING
                ])
                ->exists();
            
            // Verificar se a oferta já foi completada ou está em negociação
            if ($offer->status === 'completed' || $offer->status === 'negotiating') {
                return false;
            }
        }
        
        // Se há transação ativa, não pode editar
        if ($hasActiveTransaction) {
            return false;
        }
        
        return true;
    }
}
