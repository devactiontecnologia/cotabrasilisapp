@extends('layouts.app')

@section('title', $rentalOffer->title . ' - Leilão - Cota Brasilis')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

@php
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
    
    $isAuctionActive = $rentalOffer->isAuctionActive();
    $highestBidAmount = $rentalOffer->getHighestBidAmount();
    $highestBid = $rentalOffer->getHighestBid();
    $canBid = Auth::check() && Auth::user()->id !== $rentalOffer->user_id;
    $user = Auth::user();
@endphp

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Offer Details -->
            <div class="card border-0 shadow-lg mb-4 overflow-hidden" style="border-radius: 16px;">
                <!-- Photos Carousel -->
                @php
                    $auctionImagesToShow = $rentalOffer->getDisplayImageUrls();
                @endphp
                @if(count($auctionImagesToShow) > 0)
                    <div id="offerCarousel" class="carousel slide" data-bs-ride="carousel" style="max-height: 500px; overflow: hidden;">
                        <div class="carousel-inner">
                            @foreach($auctionImagesToShow as $index => $imageUrl)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ $imageUrl }}" class="d-block w-100" 
                                         alt="{{ $rentalOffer->display_title ?? $rentalOffer->title }}" style="height: 500px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                        @if(count($auctionImagesToShow) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#offerCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#offerCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próximo</span>
                            </button>
                        @endif
                    </div>
                @else
                    <div class="bg-gradient d-flex align-items-center justify-content-center position-relative" 
                         style="height: 500px; background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 152, 0, 0.1));">
                        <i class="fas fa-gavel fa-4x text-warning opacity-50"></i>
                    </div>
                @endif
                
                <div class="card-body p-4 p-lg-5">
                    <!-- Title and Status -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                    <i class="fas fa-gavel me-1"></i>Leilão Ativo
                                </span>
                                @if($rentalOffer->is_fractioned)
                                    <span class="badge bg-info px-3 py-2 rounded-pill">
                                        <i class="fas fa-cut me-1"></i>Fracionada
                                    </span>
                                @endif
                            </div>
                            <h1 class="fw-bold mb-2" style="font-size: 2rem; line-height: 1.2;">{{ $rentalOffer->display_title }}</h1>
                            <p class="text-muted mb-0 d-flex align-items-center">
                                <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                                <span>{{ $rentalOffer->city }}, {{ $rentalOffer->state }}</span>
                            </p>
                        </div>
                    </div>
                    
                    @if($rentalOffer->isAuctionEnded())
                        <div class="bg-danger bg-opacity-10 rounded-4 p-4 mb-4 border border-danger">
                            <div class="text-center">
                                <h6 class="fw-bold text-danger mb-2">
                                    <i class="fas fa-times-circle me-2"></i>Leilão Encerrado
                                </h6>
                                <p class="mb-0 text-muted">Este leilão já foi finalizado.</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Current Bid Info -->
                    <div class="bg-light rounded-4 p-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6 class="fw-bold text-dark mb-2">Lance Atual</h6>
                                    <div class="fs-3 fw-bold text-success" id="current-bid-amount">
                                        R$ {{ number_format($highestBidAmount > 0 ? $highestBidAmount : $rentalOffer->minimum_price, 2, ',', '.') }}
                                    </div>
                                    @if($highestBidAmount > 0 && $highestBid)
                                        <small class="text-muted">Por: {{ $highestBid->user->name ?? 'Usuário' }}</small>
                                    @else
                                        <small class="text-muted">Preço mínimo inicial</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6 class="fw-bold text-dark mb-2">Total de Lances</h6>
                                    <div class="fs-3 fw-bold text-primary" id="total-bids">
                                        {{ $bids->count() }}
                                    </div>
                                    <small class="text-muted">Participantes ativos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hotel Info -->
                    <div class="bg-light rounded-4 p-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-hotel text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Hotel</h6>
                                        <p class="mb-1 fw-semibold">{{ $rentalOffer->hotel->name ?? ($rentalOffer->city ?? 'Hotel não informado') }}</p>
                                        <small class="text-muted">{{ $rentalOffer->hotel->address ?? 'Endereço não informado' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-calendar-alt text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Período</h6>
                                        <p class="mb-0 fw-semibold">
                                            {{ Carbon::parse($rentalOffer->start_date)->format('d/m/Y') }} a 
                                            {{ Carbon::parse($rentalOffer->end_date)->format('d/m/Y') }}
                                        </p>
                                        <small class="text-muted">{{ $rentalOffer->number_of_days ?? 7 }} diárias</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    @if($rentalOffer->description)
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-list text-warning"></i>
                                <h6 class="fw-bold text-dark mb-0">Descrição</h6>
                            </div>
                            <p class="text-muted mb-0" style="line-height: 1.7;">{{ $rentalOffer->description }}</p>
                        </div>
                    @endif
                    
                    <!-- Observations -->
                    @if($rentalOffer->observations)
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-comment-dots text-warning"></i>
                                <h6 class="fw-bold text-dark mb-0">Observações</h6>
                            </div>
                            <p class="text-muted mb-0" style="line-height: 1.7;">{{ $rentalOffer->observations }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Sidebar: Bid Form and Bids List -->
        <div class="col-lg-4">
            <!-- Bid Form -->
            @if($isAuctionActive && $canBid)
                <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-gavel me-2"></i>Fazer um Lance
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="bid-form">
                            @csrf
                            <div class="mb-3">
                                <label for="bid_amount" class="form-label fw-semibold">
                                    Valor do Lance (R$) *
                                </label>
                                <input type="number" 
                                       class="form-control form-control-lg @error('bid_amount') is-invalid @enderror" 
                                       id="bid_amount" 
                                       name="bid_amount" 
                                       step="0.01" 
                                       min="{{ max($rentalOffer->minimum_price, $highestBidAmount + 0.01) }}" 
                                       value="{{ max($rentalOffer->minimum_price, $highestBidAmount + 0.01) }}"
                                       required>
                                <small class="text-muted">
                                    Lance mínimo: R$ {{ number_format(max($rentalOffer->minimum_price, $highestBidAmount + 0.01), 2, ',', '.') }}
                                </small>
                                @error('bid_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="bid_message" class="form-label fw-semibold">
                                    Mensagem (Opcional)
                                </label>
                                <textarea class="form-control" 
                                          id="bid_message" 
                                          name="message" 
                                          rows="3" 
                                          placeholder="Adicione uma mensagem ao seu lance..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                                <i class="fas fa-gavel me-2"></i>Fazer Lance
                            </button>
                        </form>
                    </div>
                </div>
            @elseif(!$isAuctionActive)
                <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <h6 class="fw-bold text-dark mb-2">Leilão Encerrado</h6>
                        <p class="text-muted mb-0">Este leilão não está mais aceitando lances.</p>
                    </div>
                </div>
            @elseif(!$canBid && Auth::check())
                <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                        <h6 class="fw-bold text-dark mb-2">Você é o dono desta oferta</h6>
                        <p class="text-muted mb-0">Você não pode fazer lances na sua própria oferta.</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                        <h6 class="fw-bold text-dark mb-2">Faça login para participar</h6>
                        <a href="{{ route('login') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Entrar
                        </a>
                    </div>
                </div>
            @endif
            
            <!-- Bids List -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list me-2"></i>Histórico de Lances
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="bids-list" class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                        @if($bids->count() > 0)
                            @foreach($bids as $bid)
                                <div class="list-group-item border-0 {{ $bid->isHighestBid() ? 'bg-warning bg-opacity-10' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong class="text-dark">{{ $bid->user->name ?? 'Usuário' }}</strong>
                                                @if($bid->isHighestBid())
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-crown me-1"></i>Lance Atual
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="fw-bold text-success fs-5">
                                                R$ {{ number_format($bid->bid_amount, 2, ',', '.') }}
                                            </div>
                                            @if($bid->message)
                                                <small class="text-muted d-block mt-1">{{ $bid->message }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                {{ Carbon::parse($bid->bid_at)->format('H:i') }}
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                {{ Carbon::parse($bid->bid_at)->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="list-group-item border-0 text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Nenhum lance ainda</p>
                                <small class="text-muted">Seja o primeiro a fazer um lance!</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Bid Form Submission
@if($isAuctionActive && $canBid)
document.getElementById('bid-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
    submitBtn.disabled = true;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                          document.querySelector('input[name="_token"]')?.value || 
                          formData.get('_token');
        
        const response = await fetch('{{ route("auctions.place-bid", $rentalOffer) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('bid-form'));
            
            // Reset form
            this.reset();
            document.getElementById('bid_amount').value = parseFloat(document.getElementById('bid_amount').min);
            
            // Reload bids
            loadBids();
        } else {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('bid-form'));
        }
    } catch (error) {
        console.error('Error:', error);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>Erro ao enviar lance. Tente novamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.card-body').insertBefore(alert, document.getElementById('bid-form'));
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Load bids function
async function loadBids() {
    try {
        const response = await fetch('{{ route("auctions.bids", $rentalOffer) }}');
        const data = await response.json();
        
        if (data.success) {
            // Update current bid
            const currentBid = data.highest_bid > 0 ? data.highest_bid : {{ $rentalOffer->minimum_price }};
            document.getElementById('current-bid-amount').textContent = 
                'R$ ' + currentBid.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            // Update total bids
            document.getElementById('total-bids').textContent = data.bids.length;
            
            // Update bids list
            const bidsList = document.getElementById('bids-list');
            if (data.bids.length > 0) {
                bidsList.innerHTML = data.bids.map(bid => {
                    const bidDate = new Date(bid.bid_at);
                    const isHighest = bid.bid_amount === data.highest_bid;
                    return `
                        <div class="list-group-item border-0 ${isHighest ? 'bg-warning bg-opacity-10' : ''}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <strong class="text-dark">${bid.user ? bid.user.name : 'Usuário'}</strong>
                                        ${isHighest ? '<span class="badge bg-warning text-dark"><i class="fas fa-crown me-1"></i>Lance Atual</span>' : ''}
                                    </div>
                                    <div class="fw-bold text-success fs-5">
                                        R$ ${parseFloat(bid.bid_amount).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    </div>
                                    ${bid.message ? `<small class="text-muted d-block mt-1">${bid.message}</small>` : ''}
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        ${bidDate.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        ${bidDate.toLocaleDateString('pt-BR')}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                bidsList.innerHTML = `
                    <div class="list-group-item border-0 text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Nenhum lance ainda</p>
                        <small class="text-muted">Seja o primeiro a fazer um lance!</small>
                    </div>
                `;
            }
            
            // Update bid amount minimum
            const bidAmountInput = document.getElementById('bid_amount');
            if (bidAmountInput) {
                const newMin = Math.max({{ $rentalOffer->minimum_price }}, currentBid + 0.01);
                bidAmountInput.min = newMin;
                bidAmountInput.value = newMin;
            }
        }
    } catch (error) {
        console.error('Error loading bids:', error);
    }
}

// Auto-refresh bids every 5 seconds if auction is active
@if($isAuctionActive)
setInterval(loadBids, 5000);
@endif
@endif
</script>
@endpush

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection

