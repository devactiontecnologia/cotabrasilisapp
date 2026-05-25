@extends('layouts.app')

@section('title', 'Ofertas de Aluguel - Cota Brasilis')

@section('content')
<div class="container-fluid py-4">
    <!-- Cards de Ação -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #009739, #007a2e);">
                <div class="card-body p-4 text-white">
                    <h4 class="fw-bold mb-3" style="color: #86efac;">
                        <i class="fas fa-hand-holding-usd me-2" style="color: #86efac;"></i>Ofertar
                    </h4>
                    <p class="mb-4" style="opacity: 0.95;">
                        Crie uma oferta de aluguel para disponibilizar suas cotas ou frações e gerar receita
                    </p>
                    <a href="{{ route('rental-offers.create') }}" class="btn btn-warning btn-lg fw-bold" style="background: #fbbf24; color: #000000; border: none;">
                        <i class="fas fa-plus me-2"></i>Criar oferta
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3 text-dark">
                        <i class="fas fa-search me-2 text-success"></i>Solicitar
                    </h4>
                    <p class="mb-4 text-muted">
                        Busque e solicite aluguel de cotas ou frações disponíveis que atendam às suas necessidades.
                    </p>
                    <a href="{{ route('rental-offers.request') }}" class="btn btn-success btn-lg fw-bold">
                        <i class="fas fa-calendar-check me-2"></i>Alugar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="fas fa-home me-2 text-primary"></i>Ofertas de Aluguel
                </h2>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('rental-offers.search') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-semibold">
                                <i class="fas fa-search me-2 text-primary"></i>Buscar
                            </label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Cidade, hotel, descrição...">
                        </div>
                        <div class="col-md-2">
                            <label for="city" class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Cidade
                            </label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="{{ request('city') }}" placeholder="Digite a cidade">
                        </div>
                        <div class="col-md-2">
                            <label for="max_price" class="form-label fw-semibold">
                                <i class="fas fa-dollar-sign me-2 text-primary"></i>Preço Máx.
                            </label>
                            <input type="number" class="form-control" id="max_price" name="max_price" 
                                   value="{{ request('max_price') }}" placeholder="1000" min="0">
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label fw-semibold">
                                <i class="fas fa-calendar me-2 text-primary"></i>Data Início
                            </label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Quick Filters -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('rental-offers.index', ['is_auction' => '1']) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-gavel me-1"></i>Leilões
                                </a>
                                <a href="{{ route('rental-offers.index', ['is_fractioned' => '1']) }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-cut me-1"></i>Fracionadas
                                </a>
                                <a href="{{ route('rental-offers.index') }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i>Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offers Grid -->
    <div class="row">
        @forelse($offers as $offer)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm offer-card" data-aos="fade-up">
                    <!-- Offer Image -->
                    <div class="position-relative">
                        @php
                            $hasOfferPhotos = $offer->photos && count($offer->photos) > 0;
                            $hasHotelImages = $offer->hotel && $offer->hotel->images && count($offer->hotel->images) > 0;
                            $imageToShow = null;
                            if ($hasOfferPhotos) {
                                $imageToShow = asset('storage/' . $offer->photos[0]);
                            } elseif ($hasHotelImages) {
                                $imageToShow = asset('storage/' . $offer->hotel->images[0]);
                            }
                        @endphp
                        @if($imageToShow)
                            <img src="{{ $imageToShow }}" 
                                 class="card-img-top" alt="{{ $offer->display_title }}" 
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        
                        <!-- Status Badges -->
                        <div class="position-absolute top-0 start-0 p-2">
                            @if($offer->is_auction)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-gavel me-1"></i>Leilão
                                </span>
                            @endif
                            @if($offer->is_fractioned)
                                <span class="badge bg-info">
                                    <i class="fas fa-cut me-1"></i>Fracionada
                                </span>
                            @endif
                        </div>
                        
                        <!-- Price Badge -->
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-success fs-6">
                                R$ {{ number_format($offer->price, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <!-- Title and Location -->
                        <h5 class="card-title fw-bold mb-2">{{ $offer->display_title }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $offer->city }}, {{ $offer->state }}
                        </p>
                        
                        <!-- Hotel Info -->
                        <p class="text-muted mb-2">
                            <i class="fas fa-hotel me-1"></i>
                            {{ $offer->hotel->name ?? ($offer->city ?? 'Hotel não informado') }}
                        </p>
                        
                        <!-- Dates and People -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') }}
                                </small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    {{ $offer->number_of_people }} pessoas
                                </small>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        @if($offer->description)
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($offer->description, 100) }}
                            </p>
                        @endif
                        
                        <!-- Auction Info -->
                        @if($offer->is_auction && $offer->isAuctionActive())
                            <div class="alert alert-warning py-2 mb-3">
                                <small>
                                    <i class="fas fa-clock me-1"></i>
                                    Leilão termina em {{ $offer->getHoursUntilAuctionEnds() }}h
                                </small>
                            </div>
                        @endif
                        
                        <!-- Stats -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ $offer->views_count }} visualizações
                                </small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="fas fa-heart me-1"></i>
                                    {{ $offer->favorites_count }} favoritos
                                </small>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <a href="{{ route('rental-offers.show', $offer) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>Ver Detalhes
                                </a>
                                @if($offer->is_auction && $offer->isAuctionActive())
                                    <a href="{{ route('auctions.show', $offer) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-gavel me-1"></i>Participar do Leilão
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Nenhuma oferta encontrada</h4>
                    <p class="text-muted">Tente ajustar os filtros de busca ou criar uma nova oferta.</p>
                    @auth
                        <a href="{{ route('rental-offers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Criar Primeira Oferta
                        </a>
                    @endauth
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($offers->hasPages())
        <div class="row">
            <div class="col-12">
                <div class="mt-4">
                    {{ $offers->links('vendor.pagination.modern') }}
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.offer-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.offer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.badge {
    font-size: 0.75rem;
}

.card-img-top {
    border-radius: 0.375rem 0.375rem 0 0;
}
</style>
@endsection