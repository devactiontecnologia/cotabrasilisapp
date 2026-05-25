@extends('layouts.app')

@section('title', 'Editar Oferta de Aluguel - Cota Brasilis')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('rental-offers.show', $rentalOffer) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-4">
                    <div class="text-center">
                        <h2 class="fw-bold mb-2">
                            <i class="fas fa-edit me-2"></i>Editar Oferta de Aluguel
                        </h2>
                        <p class="mb-0">Atualize as informações da sua oferta de aluguel.</p>
                    </div>
                </div>
                
                <div class="card-body p-5">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('rental-offers.update', $rentalOffer) }}" enctype="multipart/form-data" id="offerForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Step 1: Seleção de Cota -->
                        <div class="step" id="step1">
                            <h5 class="fw-bold mb-4 text-primary">
                                <i class="fas fa-info-circle me-2"></i>Publicar cotas e frações
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="quota_id" class="form-label fw-semibold">
                                        <i class="fas fa-file-contract me-2 text-primary"></i>Minhas Cotas ou Frações *
                                    </label>
                                    <select class="form-select form-select-lg @error('quota_id') is-invalid @enderror" 
                                            id="quota_id" name="quota_id" required disabled>
                                        <option value="">Selecione uma cota ou fração</option>
                                        @foreach($quotas as $quota)
                                            @php
                                                $startDate = $quota->start_date ? \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') : 'N/A';
                                                $endDate = $quota->end_date ? \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') : 'N/A';
                                                $days = $quota->start_date && $quota->end_date ? \Carbon\Carbon::parse($quota->start_date)->diffInDays(\Carbon\Carbon::parse($quota->end_date)) + 1 : 'N/A';
                                                
                                                // Buscar hotel para obter cidade, estado e ID
                                                $hotel = $quota->hotel ?? ($quota->hotel_name ? \App\Models\Hotel::where('name', $quota->hotel_name)->first() : null);
                                                $city = $hotel->city ?? ($quota->location ? (explode(',', $quota->location)[0] ?? '') : '');
                                                $state = $hotel->state ?? ($quota->location ? trim(explode(',', $quota->location)[1] ?? '') : '');
                                                $hotelId = $hotel->id ?? null;
                                                
                                                // Mapear sazonalidade
                                                $seasonalityMap = [
                                                    'low' => 'Baixa',
                                                    'medium' => 'Média',
                                                    'high' => 'Alta',
                                                    'peak' => 'Altíssima',
                                                ];
                                                $seasonalityLabel = $seasonalityMap[$quota->seasonality] ?? ($quota->seasonality ? ucfirst($quota->seasonality) : 'Não informada');
                                            @endphp
                                            <option value="{{ $quota->id }}" 
                                                    data-hotel-name="{{ $quota->hotel_name ?? '' }}"
                                                    data-hotel-id="{{ $hotelId }}"
                                                    data-location="{{ $quota->location ?? '' }}"
                                                    data-start-date="{{ $quota->start_date ? \Carbon\Carbon::parse($quota->start_date)->format('Y-m-d') : '' }}"
                                                    data-end-date="{{ $quota->end_date ? \Carbon\Carbon::parse($quota->end_date)->format('Y-m-d') : '' }}"
                                                    data-start-date-formatted="{{ $startDate }}"
                                                    data-end-date-formatted="{{ $endDate }}"
                                                    data-duration="{{ $days }}"
                                                    data-guests="{{ $quota->number_of_guests ?? '' }}"
                                                    data-rooms="{{ $quota->number_of_rooms ?? '' }}"
                                                    data-seasonality="{{ $quota->seasonality ?? '' }}"
                                                    data-seasonality-label="{{ $seasonalityLabel }}"
                                                    data-city="{{ $city }}"
                                                    data-state="{{ $state }}"
                                                    {{ ($rentalOffer->quota_id == $quota->id || old('quota_id') == $quota->id) ? 'selected' : '' }}>
                                                @php
                                                    // Exibir pernoites: sempre 1 a menos que o número de dias
                                                    $nights = $days > 0 ? $days - 1 : 0;
                                                @endphp
                                                {{ $quota->hotel_name ?? 'Hotel não informado' }} - Período: {{ $startDate }} a {{ $endDate }} - {{ $nights }} {{ $nights == 1 ? 'pernoite' : 'pernoites' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">A cota não pode ser alterada após a criação da oferta</small>
                                    @error('quota_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <label for="title" class="form-label fw-semibold">
                                        <i class="fas fa-heading me-2 text-primary"></i>Título da Oferta *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $rentalOffer->title) }}" 
                                           placeholder="Ex: Cota de 7 dias no Hotel Copacabana Palace" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label for="description" class="form-label fw-semibold">
                                    <i class="fas fa-align-left me-2 text-primary"></i>Descrição
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Descreva os detalhes da sua oferta...">{{ old('description', $rentalOffer->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 2: Location and Dates -->
                        <div class="step d-none" id="step2">
                            <h5 class="fw-bold mb-4 text-primary">
                                <i class="fas fa-map-marker-alt me-2"></i>Localização e Datas
                            </h5>
                            
                            <!-- Informações da Cota Selecionada -->
                            <div id="quota_info_display" class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informações da Cota Selecionada
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-hotel text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Hotel</small>
                                                    <p class="mb-0 fw-semibold" id="quota_hotel_name">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-map-marker-alt text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Localização</small>
                                                    <p class="mb-0 fw-semibold" id="quota_location">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-calendar-check text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Data de Início</small>
                                                    <p class="mb-0 fw-semibold" id="quota_start_date">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-calendar-alt text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Data de Fim</small>
                                                    <p class="mb-0 fw-semibold" id="quota_end_date">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-moon text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Duração</small>
                                                    <p class="mb-0 fw-semibold" id="quota_duration">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-users text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Número de Pessoas</small>
                                                    <p class="mb-0 fw-semibold" id="quota_guests">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-bed text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Número de Quartos</small>
                                                    <p class="mb-0 fw-semibold" id="quota_rooms">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-success-subtle rounded-circle p-2">
                                                    <i class="fas fa-sun text-success"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase fw-semibold d-block">Sazonalidade</small>
                                                    <p class="mb-0 fw-semibold" id="quota_seasonality">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Campos hidden para o formulário -->
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ old('hotel_id', $rentalOffer->hotel_id) }}">
                            <input type="hidden" id="city" name="city" value="{{ old('city', $rentalOffer->city) }}">
                            <input type="hidden" id="state" name="state" value="{{ old('state', $rentalOffer->state) }}">
                            <input type="hidden" id="start_date" name="start_date" value="{{ old('start_date', $rentalOffer->start_date ? \Carbon\Carbon::parse($rentalOffer->start_date)->format('Y-m-d') : '') }}">
                            <input type="hidden" id="end_date" name="end_date" value="{{ old('end_date', $rentalOffer->end_date ? \Carbon\Carbon::parse($rentalOffer->end_date)->format('Y-m-d') : '') }}">
                            <input type="hidden" id="number_of_people" name="number_of_people" value="{{ old('number_of_people', $rentalOffer->number_of_people) }}">
                            <input type="hidden" name="period_type" value="exact">
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 3: Price and Options -->
                        <div class="step d-none" id="step3">
                            <h5 class="fw-bold mb-4 text-primary">
                                <i class="fas fa-dollar-sign me-2"></i>Preço e Opções
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-dollar-sign me-2 text-primary"></i>Tipo de Preço *
                                    </label>
                                    <div class="btn-group w-100" role="group">
                                        @php
                                            $priceType = old('price_type', ($rentalOffer->price_min && $rentalOffer->price_max) ? 'range' : 'fixed');
                                        @endphp
                                        <input type="radio" class="btn-check" name="price_type" id="price_fixed" value="fixed" {{ $priceType == 'fixed' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="price_fixed">
                                            <i class="fas fa-tag me-2"></i>Preço Fixo
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="price_type" id="price_range" value="range" {{ $priceType == 'range' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="price_range">
                                            <i class="fas fa-sliders-h me-2"></i>Faixa de Preço
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Preço Fixo -->
                            <div class="row g-3 mt-2" id="fixed_price_fields" style="{{ $priceType == 'range' ? 'display: none;' : '' }}">
                                <div class="col-md-6">
                                    <label for="price" class="form-label fw-semibold">
                                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>Preço original (R$) *
                                    </label>
                                    <input type="number" class="form-control form-control-lg @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $rentalOffer->price) }}" 
                                           placeholder="0.00" step="0.01" min="0">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Faixa de Preço -->
                            <div class="row g-3 mt-2" id="range_price_fields" style="{{ $priceType == 'fixed' ? 'display: none;' : '' }}">
                                <div class="col-md-6">
                                    <label for="price_min" class="form-label fw-semibold">
                                        <i class="fas fa-arrow-down me-2 text-primary"></i>Preço Mínimo (R$) *
                                    </label>
                                    <input type="number" class="form-control form-control-lg @error('price_min') is-invalid @enderror" 
                                           id="price_min" name="price_min" value="{{ old('price_min', $rentalOffer->price_min) }}" 
                                           placeholder="0.00" step="0.01" min="0">
                                    @error('price_min')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="price_max" class="form-label fw-semibold">
                                        <i class="fas fa-arrow-up me-2 text-primary"></i>Preço Máximo (R$) *
                                    </label>
                                    <input type="number" class="form-control form-control-lg @error('price_max') is-invalid @enderror" 
                                           id="price_max" name="price_max" value="{{ old('price_max', $rentalOffer->price_max) }}" 
                                           placeholder="0.00" step="0.01" min="0">
                                    @error('price_max')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-cogs me-2 text-primary"></i>Filtros de Otimização
                                    </label>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="super_desconto" 
                                                   id="super_desconto" value="1" {{ old('super_desconto', $rentalOffer->super_desconto_applied) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="super_desconto">
                                                <i class="fas fa-tag me-2 text-danger"></i>Super Desconto
                                            </label>
                                            <small class="text-muted">Ativação automática 14 dias antes do início. Redução automática de 20% do preço original da oferta Só funciona se marcado.</small>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="mega_oferta" 
                                                   id="mega_oferta" value="1" {{ old('mega_oferta', $rentalOffer->mega_oferta_applied) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="mega_oferta">
                                                <i class="fas fa-star me-2 text-warning"></i>Mega Oferta
                                            </label>
                                            <small class="text-muted">
                                                Campo de novo preço. Ativação por perfil: 
                                                Curioso → 3 dias antes | Inteligente → 5 dias antes | Sábio → 7 dias antes
                                            </small>
                                        </div>
                                        <div class="mt-2 {{ old('mega_oferta', $rentalOffer->mega_oferta_applied) ? '' : 'd-none' }}" id="mega_oferta_price_field">
                                            <label for="mega_oferta_price" class="form-label fw-semibold">Preço da Mega Oferta (R$)</label>
                                            <input type="number" class="form-control" id="mega_oferta_price" name="mega_oferta_price" value="{{ old('mega_oferta_price') }}" step="0.01" min="0" placeholder="0.00">
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_auction" 
                                                   id="is_auction" value="1" {{ old('is_auction', $rentalOffer->is_auction) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is_auction">
                                                <i class="fas fa-gavel me-2 text-warning"></i>Leilão
                                            </label>
                                            <small class="text-muted">Campos: Data/hora início, Preço mínimo. Duração fixa: 20 minutos. Mostrar lances em tempo real.</small>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="sofa_plus" 
                                                   id="sofa_plus" value="1" {{ old('sofa_plus') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="sofa_plus">
                                                <i class="fas fa-couch me-2 text-info"></i>Sofá Mais
                                            </label>
                                            <small class="text-muted">Quem alugar pode revender leitos do sofá. Valor: 20% do valor pago por leito. Automático.</small>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="city_promotion" 
                                                   id="city_promotion" value="1" {{ old('city_promotion') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="city_promotion">
                                                <i class="fas fa-bullhorn me-2 text-success"></i>Divulgação por Cidade
                                            </label>
                                            <small class="text-muted">Abrir campo para cidades. Limite por perfil. Enviar e-mail e WhatsApp.</small>
                                        </div>
                                        <div class="mt-3 d-none" id="city_promotion_cities">
                                            <label for="promotion_cities" class="form-label fw-semibold">Selecione as cidades</label>
                                            <select class="form-select" id="promotion_cities" name="promotion_cities[]" multiple>
                                                @foreach(($informeCidades ?? collect()) as $cidade)
                                                    <option value="{{ $cidade->codigo_ibge }}" {{ in_array((string) $cidade->codigo_ibge, array_map('strval', old('promotion_cities', [])), true) ? 'selected' : '' }}>
                                                        {{ $cidade->nome }} ({{ $cidade->uf }}){{ $cidade->is_capital ? ' — Capital' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Mantenha Ctrl (ou Cmd no Mac) pressionado para selecionar múltiplas cidades. Limite baseado no seu perfil.</small>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_fractioned" 
                                                   id="is_fractioned" value="1" {{ old('is_fractioned', $rentalOffer->is_fractioned) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is_fractioned">
                                                <i class="fas fa-cut me-2 text-info"></i>Oferecer fracionamento
                                            </label>
                                            <small class="text-muted">Permite dividir a cota em períodos menores</small>
                                        </div>
                                        
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" name="accept_auto_discounts" 
                                                   id="accept_auto_discounts" value="1" {{ old('accept_auto_discounts', $rentalOffer->auto_discount_applied) ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-semibold" for="accept_auto_discounts">
                                                Ciente e aceito participar dos descontos automáticos e promoções deste aplicativo. *
                                            </label>
                                        </div>
                                        
                                        @if(!empty($allowsRentExchangePublication ?? false))
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="accepts_exchange" 
                                                   id="accepts_exchange" value="1" {{ old('accepts_exchange', $rentalOffer->accepts_exchange) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="accepts_exchange">
                                                <i class="fas fa-exchange-alt me-2 text-success"></i>Aceita troca
                                            </label>
                                            <small class="text-muted">Permite trocar esta cota por outra</small>
                                        </div>
                                        @else
                                            <input type="hidden" name="accepts_exchange" value="0">
                                        @endif
                                        
                                        @php
                                            $persistAcceptsSale = old('accepts_sale', $rentalOffer->accepts_sale ? '1' : '0');
                                            $persistAcceptsSale = ($persistAcceptsSale === '1' || $persistAcceptsSale === 1 || $persistAcceptsSale === true) ? '1' : '0';
                                            $oldSaleYes = $persistAcceptsSale === '1';
                                            $oldSaleNo = $persistAcceptsSale === '0';
                                        @endphp
                                        @if(!empty($requiresTitularidadeSaleChoice ?? false))
                                            <div class="border rounded-3 p-3 mb-2 bg-light">
                                                <div class="fw-semibold mb-2">
                                                    <i class="fas fa-hand-holding-usd me-2 text-success"></i>Aceita Troca de titularidade
                                                    <span class="text-danger" title="Obrigatório">*</span>
                                                </div>
                                                <small class="text-muted d-block mb-3">Permite vender a titularidade da cota. Informe Sim ou Não (obrigatório para cota inteira com 7 pernoites).</small>
                                                <div class="d-flex flex-wrap gap-4">
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="accepts_sale" id="accepts_sale_yes" value="1" required {{ $oldSaleYes ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold" for="accepts_sale_yes">Sim, aceito</label>
                                                    </div>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="accepts_sale" id="accepts_sale_no" value="0" {{ $oldSaleNo ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold" for="accepts_sale_no">Não aceito</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <input type="hidden" name="accepts_sale" value="{{ $persistAcceptsSale }}">
                                        @endif
                                        
                                        @if(!empty($allowsRentExchangePublication ?? false))
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="accepts_diaria_exchange" 
                                                   id="accepts_diaria_exchange" value="1" {{ old('accepts_diaria_exchange', $rentalOffer->accepts_diaria_exchange) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="accepts_diaria_exchange">
                                                <i class="fas fa-calendar-day me-2 text-success"></i>Aceita pagamento por troca de diárias
                                            </label>
                                            <small class="text-muted">Permite pagamento através de troca de diárias</small>
                                        </div>
                                        @else
                                            <input type="hidden" name="accepts_diaria_exchange" value="0">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Auction Options -->
                            <div class="mt-4 {{ old('is_auction', $rentalOffer->is_auction) ? '' : 'd-none' }}" id="auction_options">
                                <h6 class="fw-bold text-warning mb-3">
                                    <i class="fas fa-gavel me-2"></i>Configurações do Leilão
                                </h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="minimum_price" class="form-label fw-semibold">
                                            <i class="fas fa-arrow-up me-2 text-warning"></i>Preço Mínimo (R$) *
                                        </label>
                                        <input type="number" class="form-control @error('minimum_price') is-invalid @enderror" 
                                               id="minimum_price" name="minimum_price" value="{{ old('minimum_price', $rentalOffer->minimum_price) }}" 
                                               placeholder="0.00" step="0.01" min="0">
                                        @error('minimum_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="auction_day" class="form-label fw-semibold">
                                            <i class="fas fa-calendar-day me-2 text-warning"></i>Dia do Leilão *
                                        </label>
                                        <input type="date" class="form-control @error('auction_day') is-invalid @enderror" 
                                               id="auction_day" name="auction_day" value="{{ old('auction_day', $rentalOffer->auction_day ? \Carbon\Carbon::parse($rentalOffer->auction_day)->format('Y-m-d') : '') }}" 
                                               min="{{ date('Y-m-d') }}">
                                        @error('auction_day')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="auction_start_hour" class="form-label fw-semibold">
                                            <i class="fas fa-clock me-2 text-warning"></i>Horário de Início *
                                        </label>
                                        <input type="time" class="form-control @error('auction_start_hour') is-invalid @enderror" 
                                               id="auction_start_hour" name="auction_start_hour" value="{{ old('auction_start_hour', $rentalOffer->auction_start_hour ? \Carbon\Carbon::parse($rentalOffer->auction_start_hour)->format('H:i') : '') }}">
                                        @error('auction_start_hour')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="auction_duration_minutes" class="form-label fw-semibold">
                                            <i class="fas fa-hourglass-half me-2 text-warning"></i>Duração *
                                        </label>
                                        <input type="hidden" name="auction_duration_minutes" value="20">
                                        <input type="text" class="form-control" value="20 minutos" readonly disabled style="background-color: #e9ecef;">
                                        <small class="text-muted">Duração fixa: 20 minutos</small>
                                        @error('auction_duration_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Step 4: Photos and Final Details -->
                        <div class="step d-none" id="step4">
                            <h5 class="fw-bold mb-4 text-primary">
                                <i class="fas fa-images me-2"></i>Fotos e Detalhes Finais
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="photos" class="form-label fw-semibold">
                                        <i class="fas fa-camera me-2 text-primary"></i>Fotos da Cota
                                    </label>
                                    <input type="file" class="form-control @error('photos') is-invalid @enderror" 
                                           id="photos" name="photos[]" accept="image/*" multiple>
                                    <div class="form-text">Selecione até 5 fotos (JPG, PNG). Tamanho máximo: 2MB cada.</div>
                                    @if($rentalOffer->photos && count($rentalOffer->photos) > 0)
                                        <div class="mt-2">
                                            <small class="text-muted d-block mb-2">Fotos atuais:</small>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($rentalOffer->photos as $photo)
                                                    <img src="{{ asset('storage/' . $photo) }}" alt="Foto" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @error('photos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label for="observations" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-2 text-primary"></i>Observações Adicionais
                                </label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" 
                                          id="observations" name="observations" rows="4" 
                                          placeholder="Informações importantes sobre a cota, regras, restrições, etc...">{{ old('observations', $rentalOffer->observations) }}</textarea>
                                @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mt-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-star me-2 text-warning"></i>Destacar Publicação
                                </label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="highlight_duration" id="highlight_7" value="7" {{ old('highlight_duration') == '7' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="highlight_7">
                                            7 dias → R$ 10,00
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="highlight_duration" id="highlight_14" value="14" {{ old('highlight_duration') == '14' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="highlight_14">
                                            14 dias → R$ 15,00
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="highlight_duration" id="highlight_30" value="30" {{ old('highlight_duration') == '30' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="highlight_30">
                                            30 dias → R$ 20,00
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">Destaque sua publicação para maior visibilidade</small>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 4;

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            document.getElementById(`step${currentStep}`).classList.add('d-none');
            currentStep++;
            document.getElementById(`step${currentStep}`).classList.remove('d-none');
            
            // Se estiver indo para a etapa 2, carregar dados da cota selecionada
            if (currentStep === 2) {
                const quotaSelect = document.getElementById('quota_id');
                if (quotaSelect && quotaSelect.value) {
                    // Disparar evento change para carregar os dados
                    quotaSelect.dispatchEvent(new Event('change'));
                }
            }
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        document.getElementById(`step${currentStep}`).classList.add('d-none');
        currentStep--;
        document.getElementById(`step${currentStep}`).classList.remove('d-none');
    }
}

function validateCurrentStep() {
    const currentStepElement = document.getElementById(`step${currentStep}`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            return false;
        } else {
            field.classList.remove('is-invalid');
        }
    }
    
    return true;
}

// Toggle price type
document.querySelectorAll('input[name="price_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const fixedFields = document.getElementById('fixed_price_fields');
        const rangeFields = document.getElementById('range_price_fields');
        
        if (this.value === 'fixed') {
            fixedFields.classList.remove('d-none');
            rangeFields.classList.add('d-none');
            document.getElementById('price').required = true;
            document.getElementById('price_min').required = false;
            document.getElementById('price_max').required = false;
        } else {
            fixedFields.classList.add('d-none');
            rangeFields.classList.remove('d-none');
            document.getElementById('price').required = false;
            document.getElementById('price_min').required = true;
            document.getElementById('price_max').required = true;
        }
    });
});

// Carregar dados da cota automaticamente ao selecionar
document.getElementById('quota_id')?.addEventListener('change', function() {
    const quotaId = this.value;
    const quotaInfoDisplay = document.getElementById('quota_info_display');
    
    if (!quotaId) {
        quotaInfoDisplay.style.display = 'none';
        return;
    }
    
    // Buscar dados do option selecionado
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.dataset) {
        const data = selectedOption.dataset;
        
        // Exibir informações na etapa 2
        document.getElementById('quota_hotel_name').textContent = data.hotelName || 'Não informado';
        document.getElementById('quota_location').textContent = data.location || 'Não informado';
        document.getElementById('quota_start_date').textContent = data.startDateFormatted || 'Não informado';
        document.getElementById('quota_end_date').textContent = data.endDateFormatted || 'Não informado';
        // Exibir pernoites: sempre 1 a menos que o número de dias (8 dias = 7 pernoites)
        let nightsCount = data.duration ? parseInt(data.duration) : 0;
        if (nightsCount > 0) {
            nightsCount = nightsCount - 1;
            document.getElementById('quota_duration').textContent = nightsCount + ' ' + (nightsCount == 1 ? 'pernoite' : 'pernoites');
        } else {
            document.getElementById('quota_duration').textContent = 'Não informado';
        }
        document.getElementById('quota_guests').textContent = data.guests ? data.guests + ' ' + (data.guests == 1 ? 'pessoa' : 'pessoas') : 'Não informado';
        document.getElementById('quota_rooms').textContent = data.rooms ? data.rooms + ' ' + (data.rooms == 1 ? 'quarto' : 'quartos') : 'Não informado';
        
        // Usar label de sazonalidade já formatado ou mapear
        const seasonalityLabel = data.seasonalityLabel || (() => {
            const seasonalityMap = {
                'low': 'Baixa',
                'medium': 'Média',
                'high': 'Alta',
                'peak': 'Altíssima',
                'baixa': 'Baixa',
                'media': 'Média',
                'alta': 'Alta',
                'altissima': 'Altíssima'
            };
            return seasonalityMap[data.seasonality] || (data.seasonality ? data.seasonality : 'Não informada');
        })();
        document.getElementById('quota_seasonality').textContent = seasonalityLabel;
        
        // Preencher campos hidden para o formulário
        document.getElementById('hotel_id').value = data.hotelId || '';
        document.getElementById('city').value = data.city || '';
        // Extrair estado (pode estar no formato "Estado" ou "Cidade, Estado")
        let stateValue = data.state ? data.state.trim() : '';
        // Se o estado contém vírgula, pegar a última parte
        if (stateValue.includes(',')) {
            const parts = stateValue.split(',');
            stateValue = parts[parts.length - 1].trim();
        }
        // Se o estado contém hífen, pegar a parte após o hífen (ex: "Rio de Janeiro - RJ")
        if (stateValue.includes('-')) {
            const parts = stateValue.split('-');
            stateValue = parts[parts.length - 1].trim();
        }
        document.getElementById('state').value = stateValue;
        document.getElementById('start_date').value = data.startDate || '';
        document.getElementById('end_date').value = data.endDate || '';
        document.getElementById('number_of_people').value = data.guests || '';
        
        // Exibir o card de informações
        quotaInfoDisplay.style.display = 'block';
    }
});

// Toggle mega oferta price field
document.getElementById('mega_oferta')?.addEventListener('change', function() {
    const priceField = document.getElementById('mega_oferta_price_field');
    if (priceField) {
        priceField.classList.toggle('d-none', !this.checked);
    }
});

// Toggle city promotion cities
document.getElementById('city_promotion')?.addEventListener('change', function() {
    const citiesField = document.getElementById('city_promotion_cities');
    if (citiesField) {
        citiesField.classList.toggle('d-none', !this.checked);
    }
});

// Toggle auction options
document.getElementById('is_auction')?.addEventListener('change', function() {
    const auctionOptions = document.getElementById('auction_options');
    const minimumPrice = document.getElementById('minimum_price');
    const auctionDay = document.getElementById('auction_day');
    const auctionStartHour = document.getElementById('auction_start_hour');
    
    if (this.checked) {
        auctionOptions.classList.remove('d-none');
        if (minimumPrice) minimumPrice.required = true;
        if (auctionDay) auctionDay.required = true;
        if (auctionStartHour) auctionStartHour.required = true;
    } else {
        auctionOptions.classList.add('d-none');
        if (minimumPrice) minimumPrice.required = false;
        if (auctionDay) auctionDay.required = false;
        if (auctionStartHour) auctionStartHour.required = false;
        if (minimumPrice) minimumPrice.value = '';
        if (auctionDay) auctionDay.value = '';
        if (auctionStartHour) auctionStartHour.value = '';
    }
});

// Initialize form state
document.addEventListener('DOMContentLoaded', function() {
    // Carregar dados da cota ao carregar a página se já houver uma selecionada
    const quotaSelect = document.getElementById('quota_id');
    if (quotaSelect && quotaSelect.value) {
        // Disparar evento change para carregar os dados
        quotaSelect.dispatchEvent(new Event('change'));
    }
    
    // Mostrar card de informações da cota na etapa 2
    const quotaInfoDisplay = document.getElementById('quota_info_display');
    if (quotaInfoDisplay && quotaSelect && quotaSelect.value) {
        quotaInfoDisplay.style.display = 'block';
    }
});

// Form submission
document.getElementById('offerForm').addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando alterações...';
    submitBtn.disabled = true;
});
</script>
@endpush
@endsection
