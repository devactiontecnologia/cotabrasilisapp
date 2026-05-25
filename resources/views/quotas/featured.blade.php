@extends('layouts.app')

@section('title', 'Cotas em Destaque - Cota Brasilis')

@section('content')
<style>
    .hero-section-featured {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(4, 64, 52, 0.9));
        border-radius: 28px;
        padding: 48px 40px;
        color: #fff;
        box-shadow: 0 28px 60px rgba(5, 74, 40, 0.25);
        position: relative;
        overflow: hidden;
    }
    .hero-section-featured::after {
        content: "";
        position: absolute;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.14);
        top: -120px;
        right: -80px;
    }
    
    .quota-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        height: 100%;
    }
    
    .quota-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .quota-image {
        height: 250px;
        object-fit: cover;
        width: 100%;
    }
    
    .quota-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 10;
    }
    
    .price-tag {
        font-size: 1.5rem;
        font-weight: bold;
        color: #009739;
    }
</style>

<!-- Hero Section -->
<section class="py-4">
    <div class="container">
        <div class="hero-section-featured">
            <div class="row align-items-center">
                <div class="col-lg-8 text-lg-start text-center">
                    <span class="badge bg-light text-success fw-semibold mb-3 px-3 py-2">
                        <i class="fas fa-crown me-2"></i>Destaques verificados
                    </span>
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="fas fa-star me-2"></i>Cotas em Destaque
                    </h1>
                    <p class="lead mb-0" style="max-width: 540px;">
                        Experiências premium impulsionadas pelos anfitriões. Visibilidade máxima para oportunidades confirmadas e prontas para negociação.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end text-center mt-4 mt-lg-0">
                    <a href="{{ route('quotas.my') }}" class="btn btn-light text-success fw-semibold px-4 py-2 rounded-3">
                        <i class="fas fa-bullhorn me-2"></i>Destacar minha cota
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Quotas Section -->
<section class="py-5">
    <div class="container">
        @if(count($featuredQuotas) > 0)
            <div class="row g-4">
                @foreach($featuredQuotas as $quota)
                    <div class="col-lg-4 col-md-6">
                        <div class="card quota-card shadow-sm position-relative">
                            @if($quota->hotel && count($quota->hotel_images) > 0)
                                <div id="carousel-{{ $quota->id }}" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @foreach($quota->hotel_images as $index => $image)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                <img src="{{ asset('storage/' . $image) }}" 
                                                     alt="{{ $quota->hotel_name }}" 
                                                     class="quota-image">
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($quota->hotel_images) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $quota->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $quota->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                    <i class="fas fa-hotel text-muted" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                            
                            <span class="badge bg-warning quota-badge">
                                <i class="fas fa-star me-1"></i>Destaque
                            </span>
                            
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">{{ $quota->hotel_name }}</h5>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>{{ $quota->location }}
                                </p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Período</small>
                                        <strong class="small">{{ \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') }}</strong>
                                        <br>
                                        <strong class="small">{{ \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Hóspedes</small>
                                        <strong>{{ $quota->number_of_guests }} {{ $quota->number_of_guests == 1 ? 'pessoa' : 'pessoas' }}</strong>
                                    </div>
                                </div>
                                
                                @php $featuredPrice = $quota->getPrimaryMarketplaceDisplayPrice(); @endphp
                                @if($featuredPrice !== null)
                                    <div class="price-tag mb-3">
                                        @if((float) $featuredPrice === 0.0)
                                            <span class="d-block text-muted small mb-1">Troca</span>
                                            R$ {{ number_format(0, 2, ',', '.') }}
                                        @else
                                            R$ {{ number_format($featuredPrice, 2, ',', '.') }}
                                        @endif
                                    </div>
                                @endif
                                
                                @if($quota->allowed_uses)
                                    <div class="mb-3">
                                        @foreach($quota->allowed_uses as $use)
                                            @if($use === 'rent')
                                                <span class="badge bg-success me-1">Aluguel</span>
                                            @elseif($use === 'exchange')
                                                <span class="badge bg-info me-1">Troca</span>
                                            @elseif($use === 'sell')
                                                <span class="badge bg-warning me-1">Venda</span>
                                            @elseif($use === 'buy')
                                                <span class="badge bg-primary me-1">Compra</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                
                                <a href="{{ route('public.quotas.show', $quota) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-star text-muted" style="font-size: 5rem;"></i>
                <h3 class="mt-3 mb-2">Nenhuma cota em destaque no momento</h3>
                <p class="text-muted">As cotas em destaque aparecerão aqui quando forem publicadas e pagas.</p>
                <a href="{{ route('public.quotas.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-search me-2"></i>Ver Todas as Cotas
                </a>
            </div>
        @endif
    </div>
</section>
@endsection

