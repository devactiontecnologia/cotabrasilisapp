@extends('layouts.app')

@section('title', 'Detalhes da Cota - Cota Brasilis')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-light btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

@php
    use Carbon\Carbon;
    try {
        $startDate = ($quota->start_date && !empty($quota->start_date)) ? Carbon::parse($quota->start_date) : null;
    } catch (\Exception $e) {
        $startDate = null;
    }
    
    try {
        $endDate = ($quota->end_date && !empty($quota->end_date)) ? Carbon::parse($quota->end_date) : null;
    } catch (\Exception $e) {
        $endDate = null;
    }
    
    $periodBreakdown = $quota->getPeriodNightsBreakdown();
@endphp

<style>
    :root {
        --brand-green: #009739;
        --brand-green-dark: #046143;
        --brand-green-light: #e6f5eb;
        --brand-ink: #0f172a;
        --brand-muted: #64748b;
        --surface: #ffffff;
        --surface-alt: #f8fafc;
    }

    .quota-detail-page {
        background: var(--surface-alt);
        min-height: 100vh;
    }

    /* Hero Section */
    .hero-detail {
        background: linear-gradient(135deg, var(--brand-green) 0%, var(--brand-green-dark) 100%);
        color: white;
        padding: 4rem 0 2rem;
        margin-top: -80px;
        position: relative;
        overflow: hidden;
    }

    .hero-detail::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .hero-detail__content {
        position: relative;
        z-index: 1;
    }

    .hero-detail__back {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .hero-detail__back:hover {
        color: white;
        transform: translateX(-5px);
    }
    
    .hero-detail h1 {
        font-size: 2.75rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        line-height: 1.2;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .hero-detail__location {
        font-size: 1.25rem;
        opacity: 0.95;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Image Gallery */
    .image-gallery {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        background: var(--surface);
    }

    .image-gallery__main {
        width: 100%;
        height: 500px;
        object-fit: cover;
        display: block;
    }

    .image-gallery__placeholder {
        width: 100%;
        height: 500px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--brand-green-light) 0%, #f0f9f4 100%);
        color: var(--brand-green-dark);
    }

    .image-gallery__placeholder i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1;
        background: white;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        filter: invert(32%) sepia(94%) saturate(1352%) hue-rotate(104deg) brightness(96%) contrast(101%);
        width: 24px;
        height: 24px;
    }

    .carousel-control-prev {
        left: 20px;
    }

    .carousel-control-next {
        right: 20px;
    }

    /* Cards */
    .detail-card {
        background: var(--surface);
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: none;
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .detail-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .detail-card__header {
        background: linear-gradient(135deg, var(--brand-green-light) 0%, #f0f9f4 100%);
        padding: 1.25rem 1.75rem;
        border-bottom: 2px solid var(--brand-green-light);
    }

    .detail-card__header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--brand-green-dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .detail-card__body {
        padding: 1.75rem;
    }

    /* Info Items */
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        background: var(--surface-alt);
        border-radius: 12px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: var(--brand-green-light);
        transform: translateX(5px);
    }

    .info-item__icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: var(--brand-green);
        color: white;
        flex-shrink: 0;
    }

    .info-item__content {
        flex: 1;
    }

    .info-item__label {
        font-size: 0.875rem;
        color: var(--brand-muted);
        font-weight: 500;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item__value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--brand-ink);
        margin: 0;
    }

    /* Amenities */
    .amenity-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.125rem;
        background: var(--brand-green-light);
        color: var(--brand-green-dark);
        border-radius: 50px;
        font-size: 0.9375rem;
        font-weight: 600;
        margin: 0.375rem;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .amenity-tag:hover {
        background: var(--brand-green);
        color: white;
        border-color: var(--brand-green-dark);
        transform: translateY(-2px);
    }

    .amenity-tag i {
        font-size: 0.875rem;
    }

    /* Sidebar */
    .sidebar-card {
        background: var(--surface);
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        border: none;
        margin-bottom: 1.5rem;
        overflow: hidden;
        position: sticky;
        top: 100px;
    }

    .sidebar-card__header {
        background: linear-gradient(135deg, var(--brand-green) 0%, var(--brand-green-dark) 100%);
        color: white;
        padding: 1.5rem 1.75rem;
        text-align: center;
    }

    .sidebar-card__header h5 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .sidebar-card__price {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--brand-green);
        margin: 1.5rem 0;
        line-height: 1;
    }

    .sidebar-card__price-label {
        color: var(--brand-muted);
        font-size: 0.9375rem;
        margin-bottom: 1.5rem;
    }

    .sidebar-card__action {
        padding: 0 1.75rem 1.75rem;
    }

    .btn-primary-action {
        background: linear-gradient(135deg, var(--brand-green) 0%, var(--brand-green-dark) 100%);
        border: none;
        padding: 1rem 2rem;
        font-size: 1.125rem;
        font-weight: 700;
        border-radius: 12px;
        width: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 151, 57, 0.3);
    }

    .btn-primary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(0, 151, 57, 0.4);
    }

    .btn-secondary-action {
        background: transparent;
        border: 2px solid var(--brand-muted);
        color: var(--brand-muted);
        padding: 0.875rem 2rem;
        font-weight: 600;
        border-radius: 12px;
        width: 100%;
        margin-top: 0.75rem;
        transition: all 0.3s ease;
    }

    .btn-secondary-action:hover {
        border-color: var(--brand-green);
        color: var(--brand-green);
        background: var(--brand-green-light);
    }

    /* Owner Card */
    .owner-card {
        text-align: center;
        padding: 1.75rem;
    }

    .owner-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brand-green) 0%, var(--brand-green-dark) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        color: white;
        font-size: 2.5rem;
        box-shadow: 0 8px 25px rgba(0, 151, 57, 0.25);
    }

    .owner-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--brand-ink);
        margin-bottom: 0.5rem;
    }

    .profile-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--brand-green-light);
        color: var(--brand-green-dark);
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.875rem;
    }

    /* Featured Badge */
    .featured-badge {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3);
    }

    .featured-badge i {
        font-size: 3rem;
        color: #ff8c00;
        margin-bottom: 0.75rem;
    }

    .featured-badge h6 {
        font-weight: 700;
        color: #856404;
        margin-bottom: 0.25rem;
    }

    /* Contact Info */
    .contact-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 0;
        border-bottom: 1px solid var(--surface-alt);
    }

    .contact-item:last-child {
        border-bottom: none;
    }

    .contact-item__icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--brand-green-light);
        color: var(--brand-green-dark);
        flex-shrink: 0;
    }

    .contact-item__content {
        flex: 1;
    }

    .contact-item__label {
        font-size: 0.8125rem;
        color: var(--brand-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .contact-item__value {
        font-size: 0.9375rem;
        color: var(--brand-ink);
        font-weight: 500;
        margin: 0;
    }
    
    .contact-item__value a {
        color: var(--brand-green);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-item__value a:hover {
        color: var(--brand-green-dark);
        text-decoration: underline;
    }

    /* Rating Stars */
    .rating-stars {
        display: inline-flex;
        gap: 0.25rem;
        margin-right: 0.75rem;
    }

    .rating-stars i {
        color: #ffc107;
        font-size: 1.125rem;
    }

    .rating-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--brand-ink);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-detail h1 {
            font-size: 2rem;
        }

        .image-gallery__main,
        .image-gallery__placeholder {
            height: 300px;
        }

        .sidebar-card {
            position: relative;
            top: 0;
        }
    }

    /* Informe permanente — sempre visível, fixo ao rolar (sticky) */
    .public-quota-register-notice {
        position: sticky;
        top: 0;
        z-index: 1040;
        background: linear-gradient(90deg, #fffdf5 0%, #f0fdf4 100%);
        border-bottom: 2px solid var(--brand-green);
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
        font-size: 0.9375rem;
        font-weight: 600;
    }

    .public-quota-register-notice p {
        color: #b91c1c;
    }

    .public-quota-register-notice .container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .public-quota-register-notice i {
        color: var(--brand-green);
        font-size: 1.15rem;
    }
</style>

<!-- Hero Section -->
<section class="hero-detail">
    <div class="container hero-detail__content">
        <a href="{{ route('public.quotas.index') }}" class="hero-detail__back">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar para busca</span>
                </a>
        <h1>{{ $quota->hotel_name }}</h1>
        <div class="hero-detail__location">
            <i class="fas fa-map-marker-alt"></i>
            <span>{{ $quota->location }}</span>
        </div>
        @if(!empty($quota->is_fractioned))
            <div class="mt-2">
                <span class="badge bg-success" style="font-size: 0.85rem;">
                    <i class="fas fa-layer-group me-1"></i>Publicações da cota (inteira ou fracionada)
                </span>
            </div>
        @endif
    </div>
</section>

<div class="public-quota-register-notice" role="status" aria-live="polite">
    <div class="container py-2 py-md-3">
        <i class="fas fa-info-circle flex-shrink-0" aria-hidden="true"></i>
        <p class="mb-0">É preciso se cadastrar para desfrutar das funções da plataforma.</p>
    </div>
</div>

<div class="quota-detail-page">
<div class="container py-5">
    <div class="row">
            <!-- Main Content -->
        <div class="col-lg-8">
                <!-- Image Gallery -->
            @if($hotel && $hotel->images && count($hotel->images) > 0)
                    <div class="image-gallery">
                        <div id="hotelCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach($hotel->images as $index => $image)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             alt="{{ $hotel->name }}" 
                                             class="image-gallery__main">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($hotel->images) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#hotelCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#hotelCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Próximo</span>
                                </button>
                            @endif
                        </div>
                    </div>
            @else
                    <div class="image-gallery">
                        <div class="image-gallery__placeholder">
                            <i class="fas fa-hotel"></i>
                            <p class="mb-0 fw-semibold">Imagens do hotel não disponíveis</p>
                    </div>
                </div>
            @endif

            <!-- Hotel Information -->
            @if($hotel)
                    <div class="detail-card">
                        <div class="detail-card__header">
                            <h5>
                                <i class="fas fa-info-circle"></i>
                                Sobre o Hotel
                        </h5>
                    </div>
                        <div class="detail-card__body">
                        @if($hotel->description)
                                <p class="mb-4" style="font-size: 1.0625rem; line-height: 1.8; color: var(--brand-ink);">{{ $hotel->description }}</p>
                        @endif
                        
                            <div class="row">
                        @if($hotel->address)
                                    <div class="col-md-6 mb-3">
                                        <div class="contact-item">
                                            <div class="contact-item__icon">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <div class="contact-item__content">
                                                <div class="contact-item__label">Endereço</div>
                                                <div class="contact-item__value">{{ $hotel->address }}</div>
                                            </div>
                                        </div>
                            </div>
                        @endif
                        
                        
                        @if($hotel->website)
                                    <div class="col-md-6 mb-3">
                                        <div class="contact-item">
                                            <div class="contact-item__icon">
                                                <i class="fas fa-globe"></i>
                                            </div>
                                            <div class="contact-item__content">
                                                <div class="contact-item__label">Website</div>
                                                <div class="contact-item__value">
                                                    <a href="{{ $hotel->website }}" target="_blank" rel="noopener">{{ $hotel->website }}</a>
                                                </div>
                                            </div>
                                        </div>
                            </div>
                        @endif
                        
                        @if($hotel->rating)
                                    <div class="col-md-6 mb-3">
                                        <div class="contact-item">
                                            <div class="contact-item__icon">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="contact-item__content">
                                                <div class="contact-item__label">Avaliação</div>
                                                <div class="contact-item__value">
                                                    <span class="rating-stars">
                                    @for($i = 0; $i < 5; $i++)
                                                            @if($i < floor($hotel->rating))
                                                                <i class="fas fa-star"></i>
                                                            @endif
                                                            @if($i >= floor($hotel->rating))
                                                                <i class="far fa-star"></i>
                                                            @endif
                                    @endfor
                                                    </span>
                                                    <span class="rating-value">{{ number_format($hotel->rating, 1) }}/5.0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        
                        @if($hotel->stars)
                            <div class="mb-3">
                                    <div class="contact-item">
                                        <div class="contact-item__icon">
                                            <i class="fas fa-award"></i>
                                        </div>
                                        <div class="contact-item__content">
                                            <div class="contact-item__label">Classificação</div>
                                            <div class="contact-item__value">
                                                <span class="rating-stars">
                                    @for($i = 0; $i < $hotel->stars; $i++)
                                                        <i class="fas fa-star"></i>
                                    @endfor
                                                </span>
                                                <span class="rating-value">{{ $hotel->stars }} estrelas</span>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        @endif
                        
                        @if($hotel->amenities && count($hotel->amenities) > 0)
                            @php
                                $amenitiesTranslations = [
                                    'wifi' => 'Wi-Fi',
                                    'room_service' => 'Serviço de Quarto',
                                    'heated_pool' => 'Piscina Coberta Aquecida',
                                    'gym' => 'Academia',
                                    'bowling' => 'Boliche',
                                    'business_center' => 'Centro de Negócios',
                                    'restaurant' => 'Restaurante',
                                    'bar' => 'Bar',
                                    'spa' => 'Spa',
                                    'concierge' => 'Concierge',
                                    'pool' => 'Piscina',
                                    'parking' => 'Estacionamento',
                                    'wet_bar' => 'Bar Molhado',
                                    'pet_friendly' => 'Espaço Pet',
                                    'wine_cellar' => 'Adega',
                                    'fireplace' => 'Lareira',
                                    'bike_rack' => 'Bicicletário',
                                    'sports_court' => 'Quadra Poliesportiva',
                                    'rooftop' => 'Rooftop',
                                ];
                            @endphp
                            {{-- Bloco de Comodidades do Hotel ocultado conforme solicitação do cliente --}}
                            {{-- 
                            <div class="mt-4 pt-4 border-top">
                                <h6 class="mb-3 fw-bold" style="color: var(--brand-green-dark);">
                                    <i class="fas fa-check-circle me-2"></i>Comodidades do Hotel
                                </h6>
                                <div class="d-flex flex-wrap">
                                @foreach($hotel->amenities as $amenity)
                                    @php
                                        $amenityKey = str_replace(' ', '_', strtolower($amenity));
                                        if (isset($amenitiesTranslations[$amenityKey])) {
                                            $amenityLabel = $amenitiesTranslations[$amenityKey];
                                        } else {
                                            $amenityLabel = ucwords(str_replace('_', ' ', $amenity));
                                        }
                                    @endphp
                                    <span class="amenity-tag">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $amenityLabel }}
                                    </span>
                                @endforeach
                                </div>
                            </div>
                            --}}
                        @endif
                    </div>
                </div>
            @endif

            <!-- Quota Details -->
                <div class="detail-card">
                    <div class="detail-card__header">
                        <h5>
                            <i class="fas fa-calendar-alt"></i>
                            Detalhes da Cota
                    </h5>
                </div>
                    <div class="detail-card__body">
                        <div class="row">
                            @foreach($periodBreakdown as $periodItem)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-item__icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-item__content">
                                        <div class="info-item__label">{{ trim($periodItem['label']) }}</div>
                                        <div class="info-item__value">{{ $periodItem['formatted'] }}</div>
                                        <small class="text-muted">{{ $periodItem['nights'] }} {{ $periodItem['nights'] == 1 ? 'pernoite' : 'pernoites' }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @if($periodBreakdown === [] && $startDate && $endDate)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-item__icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-item__content">
                                        <div class="info-item__label">Período</div>
                                        <div class="info-item__value">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($periodBreakdown === [] && !$startDate && !$endDate)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-item__icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-item__content">
                                        <div class="info-item__label">Período</div>
                                        <div class="info-item__value">Não informado</div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-item__icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="info-item__content">
                                        <div class="info-item__label">Capacidade</div>
                                        <div class="info-item__value">
                                            {{ $quota->number_of_guests }} {{ $quota->number_of_guests == 1 ? 'pessoa' : 'pessoas' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-item__icon">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                    <div class="info-item__content">
                                        <div class="info-item__label">Quartos</div>
                                        <div class="info-item__value">
                                            {{ $quota->number_of_rooms ?? 1 }} {{ ($quota->number_of_rooms ?? 1) == 1 ? 'quarto' : 'quartos' }}
                                        </div>
                                        @foreach($quota->getRoomDetailsForDisplay() as $roomDetail)
                                            <small class="text-muted d-block mt-1">
                                                <strong>{{ $roomDetail['title'] }}:</strong> {{ $roomDetail['description'] }}
                                            </small>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Publicações da cota (inteira ou fracionada) --}}
                            @php
                                // Considerar cota fracionada se o flag is_fractioned estiver ativo,
                                // mesmo que fraction_details ainda não tenha todos os detalhes
                                $isFractionedQuota = !empty($quota->is_fractioned);
                                $fractionSummaries = [];

                                if ($isFractionedQuota && !empty($quota->fraction_details) && is_array($quota->fraction_details)) {
                                    $details = $quota->fraction_details;

                                    // Estrutura principal: fraction_details['fraction_weeks'][week]['periods'][period]
                                    if (isset($details['fraction_weeks']) && is_array($details['fraction_weeks'])) {
                                        foreach ($details['fraction_weeks'] as $weekNumber => $weekData) {
                                            $periods = [];
                                            if (isset($weekData['periods']) && is_array($weekData['periods'])) {
                                                $periods = $weekData['periods'];
                                            } elseif (is_array($weekData) && isset($weekData[0]) && is_array($weekData[0])) {
                                                $periods = $weekData;
                                            }

                                            foreach ($periods as $periodIndex => $period) {
                                                if (!is_array($period)) continue;
                                                // Só exibir períodos em que "Desejo alugar ou trocar" está habilitado e ação foi escolhida
                                                if (!\App\Models\Quota::isPeriodEnabledWithAction($period)) {
                                                    continue;
                                                }

                                                $startDateRaw = $period['start'] ?? $period['start_date'] ?? null;
                                                $endDateRaw = $period['end'] ?? $period['end_date'] ?? null;

                                                if (!$startDateRaw || !$endDateRaw) {
                                                    continue;
                                                }

                                                try {
                                                    $start = \Carbon\Carbon::parse($startDateRaw);
                                                    $end = \Carbon\Carbon::parse($endDateRaw);
                                                    $days = $start->diffInDays($end) + 1;
                                                    $nights = max(0, $days - 1);
                                                    $fractionSummaries[] = [
                                                        'week' => $weekNumber,
                                                        'label' => "Semana {$weekNumber} - " . $start->format('d/m/Y') . ' a ' . $end->format('d/m/Y'),
                                                        'days' => $days,
                                                        'nights' => $nights,
                                                    ];
                                                } catch (\Throwable $e) {
                                                    // Ignorar frações com datas inválidas
                                                }
                                            }
                                        }
                                    }
                                    // Estrutura alternativa: array direto de frações
                                    elseif (isset($details[0]) && is_array($details[0])) {
                                        foreach ($details as $idx => $fraction) {
                                            if (!is_array($fraction) || !\App\Models\Quota::isPeriodEnabledWithAction($fraction) || !isset($fraction['start_date'], $fraction['end_date'])) {
                                                continue;
                                            }
                                            try {
                                                $start = \Carbon\Carbon::parse($fraction['start_date']);
                                                $end = \Carbon\Carbon::parse($fraction['end_date']);
                                                $days = $start->diffInDays($end) + 1;
                                                $nights = max(0, $days - 1);
                                                $fractionSummaries[] = [
                                                    'week' => null,
                                                    'label' => 'Período ' . ($idx + 1) . ' - ' . $start->format('d/m/Y') . ' a ' . $end->format('d/m/Y'),
                                                    'days' => $days,
                                                    'nights' => $nights,
                                                ];
                                            } catch (\Throwable $e) {
                                                // Ignorar frações com datas inválidas
                                            }
                                        }
                                    }
                                }
                            @endphp

                            @if($isFractionedQuota)
                                <div class="col-12 mt-3">
                                    <div class="info-item">
                                        <div class="info-item__icon">
                                            <i class="fas fa-cut"></i>
                                        </div>
                                        <div class="info-item__content">
                                            <div class="info-item__label">Publicações da cota (inteira ou fracionada)</div>
                                            <div class="info-item__value">
                                                <span class="badge bg-success me-2">Publicação em frações</span>
                                            </div>
                                            @if(count($fractionSummaries) > 0)
                                                <ul class="mt-2 mb-0 ps-3" style="font-size: 0.9rem;">
                                                    @foreach($fractionSummaries as $fraction)
                                                        <li>
                                                            {{ $fraction['label'] }}
                                                            ({{ $fraction['days'] }} dias / {{ $fraction['nights'] }} {{ $fraction['nights'] == 1 ? 'pernoite' : 'pernoites' }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="mt-2 mb-0" style="font-size: 0.9rem;">
                                                    Esta cota possui publicações fracionadas. Os detalhes dos períodos poderão aparecer aqui assim que forem configurados.
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row mt-2">
                            @foreach($quota->getRegistrationDetailsForDisplay() as $detail)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-item__icon">
                                        <i class="fas {{ $detail['icon'] ?? 'fa-circle-info' }}"></i>
                                    </div>
                                    <div class="info-item__content">
                                        <div class="info-item__label">{{ $detail['label'] }}</div>
                                        <div class="info-item__value">{{ $detail['value'] }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($quota->allowed_uses && count($quota->allowed_uses) > 0)
                            <div class="mt-4 pt-4 border-top">
                                <h6 class="mb-3 fw-bold" style="color: var(--brand-green-dark);">
                                    <i class="fas fa-list-check me-2"></i>Usos Permitidos
                                </h6>
                                <div class="d-flex flex-wrap">
                                @foreach($quota->allowed_uses as $use)
                                    @if($use === 'rent')
                                            <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 me-2 mb-2" style="font-size: 0.9375rem;">
                                                <i class="fas fa-key me-1"></i>Aluguel
                                            </span>
                                    @elseif($use === 'exchange')
                                            <span class="badge bg-info-subtle text-info fw-semibold px-3 py-2 me-2 mb-2" style="font-size: 0.9375rem;">
                                                <i class="fas fa-exchange-alt me-1"></i>Troca
                                            </span>
                                    @elseif($use === 'sell')
                                            <span class="badge bg-warning-subtle text-warning fw-semibold px-3 py-2 me-2 mb-2" style="font-size: 0.9375rem;">
                                                <i class="fas fa-dollar-sign me-1"></i>Venda
                                            </span>
                                    @elseif($use === 'buy')
                                            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 me-2 mb-2" style="font-size: 0.9375rem;">
                                                <i class="fas fa-shopping-cart me-1"></i>Compra
                                            </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($quota->observations)
                            <div class="mt-4 pt-4 border-top">
                                <h6 class="mb-3 fw-bold" style="color: var(--brand-green-dark);">
                                    <i class="fas fa-sticky-note me-2"></i>Observações
                                </h6>
                                <div class="p-3" style="background: var(--surface-alt); border-radius: 12px;">
                                    <p class="mb-0" style="line-height: 1.8; color: var(--brand-ink);">{{ $quota->observations }}</p>
                                </div>
                            </div>
                    @endif
                </div>
            </div>
        </div>

            <!-- Sidebar -->
        <div class="col-lg-4">
            @php
                $allowedUses = collect($quota->allowed_uses ?? []);
                $primaryUse = $allowedUses->contains('rent')
                    ? 'rent'
                    : ($allowedUses->contains('exchange') ? 'exchange' : (($allowedUses->contains('sell') || $allowedUses->contains('buy')) ? 'buy' : 'rent'));
                $primaryActionLabel = $primaryUse === 'rent' ? 'Alugar' : ($primaryUse === 'exchange' ? 'Trocar' : 'Comprar');
                $txPriceCard = request('transaction_type', $primaryUse);
                if ($txPriceCard === 'rental') {
                    $txPriceCard = 'rent';
                }
                if ($txPriceCard === 'purchase') {
                    $txPriceCard = 'buy';
                }
                $cardListPrice = $quota->getMarketplaceListPrice($txPriceCard);
                if ($txPriceCard === 'exchange') {
                    $showPriceCard = true;
                } elseif (in_array($txPriceCard, ['sell', 'buy'], true)) {
                    $showPriceCard = $cardListPrice !== null;
                } else {
                    $showPriceCard = $cardListPrice !== null || $quota->rental_price;
                }
            @endphp

            {{-- CTAs ao lado da foto --}}
            <div class="sidebar-card mb-4">
                <div class="sidebar-card__header">
                    <h5>
                        <i class="fas fa-bolt"></i>
                        Ação rápida
                    </h5>
                </div>
                <div class="sidebar-card__action" style="padding: 1.25rem 1.75rem 1.75rem;">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary-action w-100">
                            <i class="fas fa-user me-2"></i>Minha conta
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-secondary-action w-100">
                            <i class="fas fa-user-plus me-2"></i>Cadastre-se
                        </a>
                    @endauth

                    <a
                        class="btn btn-primary-action text-white w-100 mt-3"
                        href="@if($primaryUse === 'rent')
                            @auth
                                {{ route('quotas.negotiate', ['quota' => $quota, 'type' => 'rent']) }}
                            @else
                                {{ route('login') }}
                            @endauth
                        @elseif($primaryUse === 'exchange')
                            @auth
                                {{ route('exchanges.create') }}
                            @else
                                {{ route('login') }}
                            @endauth
                        @else
                            @auth
                                {{ route('purchases.create') }}
                            @else
                                {{ route('login') }}
                            @endauth
                        @endif"
                    >
                        <i class="fas fa-hand-pointer me-2"></i>{{ $primaryActionLabel }}
                    </a>
                </div>
            </div>

            <!-- Price Card -->
            @if($showPriceCard)
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h5>
                                <i class="fas fa-dollar-sign"></i>
                                Preço
                        </h5>
                    </div>
                        <div class="text-center">
                            <div class="sidebar-card__price">
                                @if($txPriceCard === 'exchange')
                                    R$ {{ number_format(0, 2, ',', '.') }}
                                @elseif($cardListPrice !== null)
                                    R$ {{ number_format($cardListPrice, 2, ',', '.') }}
                                @elseif($quota->rental_price)
                                    R$ {{ number_format($quota->rental_price, 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </div>
                            <p class="sidebar-card__price-label">
                                @if($txPriceCard === 'exchange')
                                    Troca — valor na negociação (período conforme anúncio)
                                @elseif($txPriceCard === 'sell' || $txPriceCard === 'buy')
                                    Valor referente à venda da cota
                                @else
                                    Valor total para o período
                                @endif
                            </p>
                        
                            <div class="sidebar-card__action">
                        @auth
                                    @if($txPriceCard === 'rent')
                                    <a href="{{ route('quotas.negotiate', ['quota' => $quota, 'type' => 'rent']) }}" class="btn btn-primary-action text-white">
                                    <i class="fas fa-shopping-cart me-2"></i>Alugar Agora
                                    </a>
                                    @elseif($txPriceCard === 'exchange')
                                    <a href="{{ route('exchanges.create') }}" class="btn btn-primary-action text-white">
                                    <i class="fas fa-exchange-alt me-2"></i>Propor troca
                                    </a>
                                    @else
                                    <a href="{{ route('purchases.create') }}" class="btn btn-primary-action text-white">
                                    <i class="fas fa-shopping-cart me-2"></i>Tenho interesse
                                    </a>
                                    @endif
                        @else
                                    <a href="{{ route('login') }}" class="btn btn-primary-action text-white">
                                <i class="fas fa-sign-in-alt me-2"></i>Fazer login para continuar
                            </a>
                        @endauth
                        
                                <a href="{{ route('public.quotas.index') }}" class="btn btn-secondary-action">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar para Busca
                        </a>
                            </div>
                    </div>
                </div>
            @endif

            <!-- Owner Card -->
                <div class="detail-card">
                    <div class="detail-card__header">
                        <h5>
                            <i class="fas fa-user"></i>
                            Proprietário
                    </h5>
                </div>
                    <div class="owner-card">
                        <div class="owner-avatar p-0 overflow-hidden border-0 bg-transparent">
                            <x-user-avatar :user="$quota->user" :size="72" rounded="circle" />
                        </div>
                        <h6 class="owner-name">{{ $quota->user->name }}</h6>
                    
                    @if($quota->user->profile)
                            <span class="profile-badge">
                                <i class="fas fa-crown"></i>
                                @switch($quota->user->profile->profile_type)
                                    @case('curioso')
                                        Curioso
                                        @break
                                    @case('inteligente')
                                        Inteligente
                                        @break
                                    @case('sabio')
                                        Sábio
                                        @break
                                @endswitch
                            </span>
                    @endif
                </div>
            </div>

            <!-- Featured Badge -->
            @if($quota->is_published && $quota->payment_status === 'paid')
                    <div class="featured-badge">
                        <i class="fas fa-star"></i>
                        <h6>Cota em Destaque</h6>
                        <small style="color: #856404;">Esta Cota/Fração  tem maior probabilidade de ser negociada</small>
                    </div>
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
