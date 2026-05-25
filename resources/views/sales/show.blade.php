@extends('layouts.app')

@section('title', 'Detalhes da Venda - Cota Brasilis')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Detalhes da Oferta de Venda</h4>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="border rounded-4 p-4 bg-light">
                    <h5 class="fw-bold mb-4">Informações da Oferta</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small">Hotel</label>
                            <p class="fw-semibold">{{ $saleOffer->hotel->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $saleOffer->status === 'sold' ? 'success' : ($saleOffer->status === 'negotiating' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($saleOffer->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Cidade</label>
                            <p class="fw-semibold">{{ $saleOffer->city }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Semanas</label>
                            <p class="fw-semibold">{{ $saleOffer->weeks }} {{ $saleOffer->weeks == 1 ? 'semana' : 'semanas' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Quartos</label>
                            <p class="fw-semibold">{{ $saleOffer->number_of_rooms }}</p>
                        </div>
                        @if($saleOffer->company)
                        <div class="col-md-12">
                            <label class="text-muted small">Empresa</label>
                            <p class="fw-semibold">{{ $saleOffer->company }}</p>
                        </div>
                        @endif
                    </div>

                    <h6 class="fw-bold mb-3">Preços</h6>
                    @php
                        $user = auth()->user();
                        $canSeePrices = $saleOffer->canUserSeePrices($user) || $saleOffer->user_id === $user->id || $user->isAdmin();
                        $hasAnyPrice = $saleOffer->minimum_price !== null || $saleOffer->acceptable_price !== null || $saleOffer->desired_price !== null;
                    @endphp
                    
                    @if($canSeePrices)
                        @if($hasAnyPrice)
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Preço Mínimo</label>
                            <p class="fw-semibold text-danger">{{ $saleOffer->minimum_price !== null ? 'R$ ' . number_format($saleOffer->minimum_price, 2, ',', '.') : '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Preço Aceitável</label>
                            <p class="fw-semibold text-warning">{{ $saleOffer->acceptable_price !== null ? 'R$ ' . number_format($saleOffer->acceptable_price, 2, ',', '.') : '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Preço Desejado</label>
                            <p class="fw-semibold text-success">{{ $saleOffer->desired_price !== null ? 'R$ ' . number_format($saleOffer->desired_price, 2, ',', '.') : '—' }}</p>
                        </div>
                    </div>
                        @else
                    <p class="text-muted mb-0">Valores a combinar (não informados nesta oferta).</p>
                        @endif
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Seu tipo de cadastro não permite visualizar os preços desta oferta.
                    </div>
                    @endif

                    @if($saleOffer->observations_by_price)
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Observações por Preço</h6>
                        @foreach($saleOffer->observations_by_price as $priceType => $observation)
                            <div class="mb-2">
                                <strong>{{ ucfirst($priceType) }}:</strong> {{ $observation }}
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="mt-4">
                        <label class="text-muted small">Status de Negociação</label>
                        <p class="fw-semibold">
                            @if($saleOffer->negotiation_status === 'admin')
                                <span class="badge bg-primary">Negociação com Administrador</span>
                            @elseif($saleOffer->negotiation_status === 'auction')
                                <span class="badge bg-warning">Venda via Leilão</span>
                            @else
                                <span class="badge bg-info">Negociação Direta</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                @if($saleOffer->status === 'pending' || $saleOffer->status === 'negotiating')
                    @if(($saleOffer->canUserNegotiateDirectly(auth()->user()) || auth()->user()->isAdmin()) && $saleOffer->minimum_price !== null)
                    <div class="border rounded-4 p-4 bg-light">
                        <h6 class="fw-bold mb-3">Fazer Proposta</h6>
                        <form method="POST" action="{{ route('sales.negotiate', $saleOffer) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="offer_price" class="form-label fw-semibold">Valor da Proposta (R$)</label>
                                <input type="number" class="form-control" id="offer_price" name="offer_price" 
                                       step="0.01" min="{{ $saleOffer->minimum_price }}" 
                                       @if($saleOffer->desired_price !== null) max="{{ $saleOffer->desired_price }}" @endif
                                       required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label fw-semibold">Mensagem (Opcional)</label>
                                <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Proposta
                            </button>
                        </form>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection
