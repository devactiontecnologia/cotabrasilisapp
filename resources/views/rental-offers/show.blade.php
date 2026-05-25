@extends('layouts.app')

@section('title', $rentalOffer->title . ' - Cota Brasilis')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Offer Details -->
            <div class="card border-0 shadow-lg mb-4 overflow-hidden" style="border-radius: 16px;">
                <!-- Photos Carousel -->
                @php
                    $imagesToShow = $rentalOffer->getDisplayImageUrls();
                @endphp
                @if(count($imagesToShow) > 0)
                    <div id="offerCarousel" class="carousel slide" data-bs-ride="carousel" style="max-height: 500px; overflow: hidden;">
                        <div class="carousel-inner">
                            @foreach($imagesToShow as $index => $imageUrl)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ $imageUrl }}" class="d-block w-100" 
                                         alt="{{ $rentalOffer->display_title ?? $rentalOffer->title }}" style="height: 500px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                        @if(count($imagesToShow) > 1)
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
                         style="height: 500px; background: linear-gradient(135deg, rgba(0, 151, 57, 0.1), rgba(4, 64, 52, 0.1));">
                        <i class="fas fa-image fa-4x text-muted opacity-50"></i>
                    </div>
                @endif
                
                <div class="card-body p-4 p-lg-5">
                    <!-- Title and Status -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @if($rentalOffer->is_auction)
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                        <i class="fas fa-gavel me-1"></i>Leilão
                                    </span>
                                @endif
                                @if($rentalOffer->is_fractioned)
                                    <span class="badge bg-info px-3 py-2 rounded-pill">
                                        <i class="fas fa-cut me-1"></i>Fracionada
                                    </span>
                                @endif
                            </div>
                            <h1 class="fw-bold mb-2" style="font-size: 2rem; line-height: 1.2;">{{ $rentalOffer->display_title }}</h1>
                            <p class="text-muted mb-0 d-flex align-items-center">
                                <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                <span>{{ $rentalOffer->city }}, {{ $rentalOffer->state }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Hotel Info -->
                    <div class="bg-light rounded-4 p-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-hotel text-success fs-5"></i>
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
                                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-star text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Avaliação</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($rentalOffer->hotel && $rentalOffer->hotel->rating)
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= floor($rentalOffer->hotel->rating) ? 'text-warning' : 'text-muted' }}" style="font-size: 1.1rem;"></i>
                                                @endfor
                                                <span class="text-muted fw-semibold">({{ number_format($rentalOffer->hotel->rating, 2, ',', '.') }}/5)</span>
                                            @else
                                                <span class="text-muted">Avaliação não disponível</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dates and People -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-calendar-alt text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">Data de Início</h6>
                                </div>
                                <p class="mb-0 fw-semibold fs-6">{{ \Carbon\Carbon::parse($rentalOffer->start_date)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-calendar-alt text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">Data de Fim</h6>
                                </div>
                                <p class="mb-0 fw-semibold fs-6">{{ \Carbon\Carbon::parse($rentalOffer->end_date)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-users text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">Pessoas</h6>
                                </div>
                                <p class="mb-0 fw-semibold fs-6">{{ $rentalOffer->number_of_people }} {{ $rentalOffer->number_of_people == 1 ? 'pessoa' : 'pessoas' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    @if($rentalOffer->description)
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-list text-success"></i>
                                <h6 class="fw-bold text-dark mb-0">Descrição</h6>
                            </div>
                            <p class="text-muted mb-0" style="line-height: 1.7;">{{ $rentalOffer->description }}</p>
                        </div>
                    @endif
                    
                    <!-- Observations -->
                    @if($rentalOffer->observations)
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-comment-dots text-success"></i>
                                <h6 class="fw-bold text-dark mb-0">Observações</h6>
                            </div>
                            <p class="text-muted mb-0" style="line-height: 1.7;">{{ $rentalOffer->observations }}</p>
                        </div>
                    @endif
                    
                    <!-- Detalhes da Cota -->
                    @if($rentalOffer->quota)
                        @php
                            $quota = $rentalOffer->quota;
                            $profile = $quota->user->profile ?? null;
                            $isOwner = $profile && ($profile->is_quota_owner ?? false);
                            $isGestor = $profile && ($profile->is_authorized_user ?? false);
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-info-circle text-success"></i>
                                <h6 class="fw-bold text-dark mb-0">Detalhes da Cota</h6>
                            </div>
                            <div class="row g-3">
                                <!-- Cards de informações removidos: Hotel em Funcionamento, Status da Cota, Documento e Usos Permitidos -->
                                
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fas fa-door-open text-success"></i>
                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">Quartos</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold fs-6">{{ $quota->number_of_rooms ?? ($profile ? ($isOwner ? ($profile->owner_quota_rooms ?? $profile->gestor_quota_rooms ?? 1) : ($profile->gestor_quota_rooms ?? 1)) : 1) }} {{ ($quota->number_of_rooms ?? 1) == 1 ? 'quarto' : 'quartos' }}</p>
                                        @foreach($quota->getRoomDetailsForDisplay() as $roomDetail)
                                        <small class="text-muted d-block mt-2">
                                            <strong>{{ $roomDetail['title'] }}:</strong> {{ $roomDetail['description'] }}
                                        </small>
                                        @endforeach
                                    </div>
                                </div>

                                @if($rentalOffer->hotel && is_array($rentalOffer->hotel->amenities))
                                @php
                                    $hotelAmenities = $rentalOffer->hotel->amenities;
                                    $amenityIcons = [
                                        'seaview' => 'water',
                                        'ocean_view' => 'water',
                                        'spa' => 'spa',
                                        'pool' => 'swimming-pool',
                                        'piscina' => 'swimming-pool',
                                        'gym' => 'dumbbell',
                                        'academia' => 'dumbbell',
                                        'fireplace' => 'fire',
                                        'lareira' => 'fire',
                                        'wine_cellar' => 'wine-bottle',
                                        'adega' => 'wine-bottle',
                                        'kids_area' => 'child',
                                        'area_kids' => 'child',
                                        'business_center' => 'briefcase',
                                        'area_trabalho' => 'briefcase',
                                        'wifi' => 'wifi',
                                        'parking' => 'parking',
                                        'estacionamento' => 'parking',
                                    ];
                                    $amenityLabels = [
                                        'seaview' => 'Vista Mar',
                                        'ocean_view' => 'Vista Mar',
                                        'spa' => 'Spa',
                                        'pool' => 'Piscina',
                                        'piscina' => 'Piscina',
                                        'gym' => 'Academia',
                                        'academia' => 'Academia',
                                        'fireplace' => 'Lareira',
                                        'lareira' => 'Lareira',
                                        'wine_cellar' => 'Adega',
                                        'adega' => 'Adega',
                                        'kids_area' => 'Área Kids',
                                        'area_kids' => 'Área Kids',
                                        'business_center' => 'Área de Trabalho',
                                        'area_trabalho' => 'Área de Trabalho',
                                        'wifi' => 'WiFi',
                                        'parking' => 'Estacionamento',
                                        'estacionamento' => 'Estacionamento',
                                    ];
                                @endphp
                                @foreach(['seaview', 'ocean_view', 'spa', 'pool', 'piscina', 'gym', 'academia', 'fireplace', 'lareira', 'wine_cellar', 'adega', 'kids_area', 'area_kids', 'business_center', 'area_trabalho', 'wifi'] as $amenityKey)
                                    @if(in_array($amenityKey, $hotelAmenities) || (isset($hotelAmenities[$amenityKey]) && $hotelAmenities[$amenityKey]))
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 h-100">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="fas fa-{{ $amenityIcons[$amenityKey] ?? 'check' }} text-success"></i>
                                                <h6 class="fw-bold text-dark mb-0 small text-uppercase">{{ $amenityLabels[$amenityKey] ?? ucfirst(str_replace('_', ' ', $amenityKey)) }}</h6>
                                            </div>
                                            <p class="mb-0 fw-semibold fs-6">Sim</p>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                                @endif

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fas fa-calendar-week text-success"></i>
                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">Semanas</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold fs-6">{{ $quota->weeks ?? 1 }} {{ ($quota->weeks ?? 1) == 1 ? 'semana' : 'semanas' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fas fa-credit-card text-success"></i>
                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">Status de Pagamento</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold fs-6">{{ $quota->payment_status ? $quota->getPaymentStatusLabel() : 'Não Quitada' }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fas fa-users text-success"></i>
                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">Hóspedes</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold fs-6">{{ $quota->number_of_guests ?? ($profile ? ($isOwner ? ($profile->owner_quota_people ?? $profile->gestor_quota_people ?? 4) : ($profile->gestor_quota_people ?? 4)) : 4) }} {{ ($quota->number_of_guests ?? 4) == 1 ? 'pessoa' : 'pessoas' }}</p>
                                    </div>
                                </div>

                                @foreach($quota->getRegistrationDetailsForDisplay() as $detail)
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fas {{ $detail['icon'] ?? 'fa-circle-info' }} text-success"></i>
                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">{{ $detail['label'] }}</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold fs-6">{{ $detail['value'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            {{-- Bloco "Comodidades do Hotel" ocultado conforme solicitação do cliente --}}
                            {{--
                            @if($rentalOffer->hotel && $rentalOffer->hotel->amenities && count($rentalOffer->hotel->amenities) > 0)
                                <div class="d-flex align-items-center gap-2 mb-3 mt-4">
                                    <i class="fas fa-star text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0">Comodidades do Hotel</h6>
                                </div>
                                <div class="row g-3">
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
                                            'piscina' => 'Piscina',
                                            'parking' => 'Estacionamento',
                                            'estacionamento' => 'Estacionamento',
                                            'wet_bar' => 'Bar Molhado',
                                            'pet_friendly' => 'Espaço Pet',
                                            'wine_cellar' => 'Adega',
                                            'adega' => 'Adega',
                                            'fireplace' => 'Lareira',
                                            'lareira' => 'Lareira',
                                            'bike_rack' => 'Bicicletário',
                                            'sports_court' => 'Quadra Poliesportiva',
                                            'rooftop' => 'Rooftop',
                                            'seaview' => 'Vista Mar',
                                            'ocean_view' => 'Vista Mar',
                                            'kids_area' => 'Área Kids',
                                            'area_kids' => 'Área Kids',
                                            'area_trabalho' => 'Área de Trabalho',
                                        ];
                                        
                                        $amenitiesIcons = [
                                            'wifi' => 'wifi',
                                            'room_service' => 'bell',
                                            'heated_pool' => 'thermometer-half',
                                            'gym' => 'dumbbell',
                                            'academia' => 'dumbbell',
                                            'bowling' => 'bowling-ball',
                                            'business_center' => 'briefcase',
                                            'area_trabalho' => 'briefcase',
                                            'restaurant' => 'utensils',
                                            'bar' => 'cocktail',
                                            'spa' => 'spa',
                                            'concierge' => 'concierge-bell',
                                            'pool' => 'swimming-pool',
                                            'piscina' => 'swimming-pool',
                                            'parking' => 'parking',
                                            'estacionamento' => 'parking',
                                            'wet_bar' => 'wine-glass',
                                            'pet_friendly' => 'paw',
                                            'wine_cellar' => 'wine-bottle',
                                            'adega' => 'wine-bottle',
                                            'fireplace' => 'fire',
                                            'lareira' => 'fire',
                                            'bike_rack' => 'bicycle',
                                            'sports_court' => 'futbol',
                                            'rooftop' => 'building',
                                            'seaview' => 'water',
                                            'ocean_view' => 'water',
                                            'kids_area' => 'child',
                                            'area_kids' => 'child',
                                        ];
                                    @endphp
                                    
                                    @foreach($rentalOffer->hotel->amenities as $amenity)
                                        @php
                                            $amenityKey = is_string($amenity) ? str_replace(' ', '_', strtolower($amenity)) : $amenity;
                                            $amenityLabel = $amenitiesTranslations[$amenityKey] ?? (is_string($amenity) ? ucwords(str_replace('_', ' ', $amenity)) : ucwords(str_replace('_', ' ', $amenityKey)));
                                            $amenityIcon = $amenitiesIcons[$amenityKey] ?? 'check-circle';
                                            
                                            // Verificar se é um array associativo
                                            if (is_array($rentalOffer->hotel->amenities) && isset($rentalOffer->hotel->amenities[$amenityKey])) {
                                                $isActive = $rentalOffer->hotel->amenities[$amenityKey];
                                            } else {
                                                $isActive = true;
                                            }
                                        @endphp
                                        @if($isActive)
                                        <div class="col-md-6">
                                            <div class="border rounded-4 p-3 h-100">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i class="fas fa-{{ $amenityIcon }} text-success"></i>
                                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">{{ $amenityLabel }}</h6>
                                                </div>
                                                <p class="mb-0 fw-semibold fs-6">Sim</p>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            --}}
                        </div>
                    @endif
                    
                    <!-- Stats -->
                    <div class="row g-3 mt-4 pt-4 border-top">
                        <div class="col-4">
                            <div class="text-center">
                                <h6 class="fw-bold text-success mb-1 fs-5">{{ $rentalOffer->views_count ?? 0 }}</h6>
                                <small class="text-muted">Visualizações</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center border-start border-end">
                                <h6 class="fw-bold text-success mb-1 fs-5">{{ $rentalOffer->favorites_count ?? 0 }}</h6>
                                <small class="text-muted">Favoritos</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h6 class="fw-bold text-success mb-1 fs-5">{{ $rentalOffer->auctions->count() ?? 0 }}</h6>
                                <small class="text-muted">Lances</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Auction Section -->
            @if($rentalOffer->is_auction)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-gavel me-2"></i>Leilão Ativo
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($rentalOffer->isAuctionActive())
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-primary mb-2">Preço Original da Oferta</h6>
                                    <h3 class="fw-bold text-success mb-0">
                                        R$ {{ number_format($rentalOffer->price, 2, ',', '.') }}
                                    </h3>
                                </div>
                            </div>
                            
                            <!-- Bid Form -->
                            @auth
                                @if(Auth::id() !== $rentalOffer->user_id)
                                    <div class="mt-4">
                                        <h6 class="fw-bold text-primary mb-3">Fazer Lance</h6>
                                        <form id="bidForm" class="row g-3">
                                            @csrf
                                            <div class="col-md-8">
                                                <input type="number" class="form-control" id="bid_amount" name="bid_amount" 
                                                       placeholder="Valor do lance" step="0.01" min="{{ $rentalOffer->minimum_price }}">
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-warning w-100">
                                                    <i class="fas fa-gavel me-1"></i>Fazer Lance
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        @else
                            <div class="text-center">
                                <h5 class="fw-bold text-muted">Leilão Encerrado</h5>
                                <p class="text-muted">Este leilão não está mais ativo.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Price Card -->
            <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px; position: sticky; top: 20px;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-success mb-2" style="font-size: 2.5rem;">
                            R$ {{ number_format($rentalOffer->price, 2, ',', '.') }}
                        </h2>
                        @if($rentalOffer->original_price && $rentalOffer->original_price != $rentalOffer->price)
                            <p class="text-muted mb-1">
                                <small>Preço original: <span class="text-decoration-line-through">R$ {{ number_format($rentalOffer->original_price, 2, ',', '.') }}</span></small>
                            </p>
                        @endif
                        <p class="text-muted mb-0">
                            <small>{{ $rentalOffer->number_of_days }} {{ $rentalOffer->number_of_days == 1 ? 'dia' : 'dias' }}</small>
                        </p>
                        @php
                            $hotelWebsite = trim((string) ($rentalOffer->hotel->website ?? ''));
                            if ($hotelWebsite !== '' && !preg_match('#^https?://#i', $hotelWebsite)) {
                                $hotelWebsite = 'https://' . $hotelWebsite;
                            }
                        @endphp
                        @if($hotelWebsite !== '')
                            <div class="mt-3 pt-3 border-top">
                                <p class="text-muted small mb-1">Compare o valor no site do hotel:</p>
                                <a href="{{ $hotelWebsite }}" target="_blank" rel="noopener noreferrer" class="fw-semibold text-success text-break">
                                    {{ $rentalOffer->hotel->website }}
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    @if($rentalOffer->is_auction && $rentalOffer->isAuctionActive())
                        <a href="{{ route('auctions.show', $rentalOffer) }}" class="btn btn-warning btn-lg w-100 mb-3 rounded-pill fw-semibold" style="padding: 12px;">
                            <i class="fas fa-gavel me-2"></i>Participar do Leilão
                        </a>
                    @else
                        <a href="{{ route('quotas.negotiate', ['quota' => $rentalOffer->quota, 'type' => 'rent']) }}" class="btn btn-success btn-lg w-100 rounded-pill fw-semibold d-grid gap-2 mb-3" style="padding: 12px; background: linear-gradient(135deg, #009739, #007a2e); border: none;">
                                <i class="fas fa-shopping-cart me-2"></i>Alugar
                        </a>
                    @endif
                    
                    <button class="btn btn-outline-danger w-100 rounded-pill fw-semibold" style="padding: 10px;">
                        <i class="fas fa-heart me-2"></i>Adicionar aos Favoritos
                    </button>
                </div>
            </div>
            
            <!-- Owner Info -->
            <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px;">
                <div class="card-header bg-success text-white rounded-top" style="border-radius: 16px 16px 0 0 !important; padding: 1rem 1.5rem;">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="fas fa-user me-2"></i>Proprietário
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <x-user-avatar :user="$rentalOffer->user" :size="60" class="me-3 border border-3 border-success" rounded="circle" />
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">{{ $rentalOffer->user->name }}</h6>
                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                {{ ucfirst($rentalOffer->user->profile->profile_type ?? 'Usuário') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row g-3 text-center border-top pt-3">
                        <div class="col-6">
                            <h6 class="fw-bold text-success mb-1 fs-5">{{ \App\Models\RentalOffer::where('user_id', $rentalOffer->user->id)->count() }}</h6>
                            <small class="text-muted">Ofertas</small>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold text-success mb-1 fs-5">4.8</h6>
                            <small class="text-muted">Avaliação</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

// Bid form submission
@if($rentalOffer->is_auction && $rentalOffer->isAuctionActive())
document.getElementById('bidForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const bidAmount = formData.get('bid_amount');
    
    if (!bidAmount || bidAmount < {{ $rentalOffer->minimum_price }}) {
        alert('Lance deve ser maior que o preço mínimo.');
        return;
    }
    
    fetch('{{ route("auctions.place-bid", $rentalOffer) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            bid_amount: bidAmount,
            message: ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Lance realizado com sucesso!');
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao realizar lance. Tente novamente.');
    });
});
@endif

</script>
@endpush

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection