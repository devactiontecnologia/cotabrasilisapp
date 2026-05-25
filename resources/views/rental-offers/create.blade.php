@extends('layouts.app')

@section('title', 'Criar Oferta de Aluguel - Cota Brasilis')

@section('content')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #009739 0%, #007a2e 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        --card-shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.12);
    }

    .create-offer-page {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .offer-header {
        background: var(--primary-gradient);
        border-radius: 24px 24px 0 0;
        padding: 3rem 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .offer-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent 70%);
        border-radius: 50%;
    }

    .offer-header-content {
        position: relative;
        z-index: 1;
    }

    .offer-header-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .offer-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .offer-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0;
    }

    .offer-card {
        background: white;
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .step-indicator {
        background: #f8fafc;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .step-indicator .steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    .step-indicator .step {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    .step-indicator .step::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #e2e8f0;
        z-index: -1;
    }

    .step-indicator .step:first-child::before {
        display: none;
    }

    .step-indicator .step.active::before,
    .step-indicator .step.completed::before {
        background: #009739;
    }

    .step-indicator .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .step-indicator .step.active .step-number {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(0, 151, 57, 0.3);
        transform: scale(1.1);
    }

    .step-indicator .step.completed .step-number {
        background: #10b981;
        color: white;
    }

    .step-indicator .step-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
    }

    .step-indicator .step.active .step-label {
        color: #009739;
        font-weight: 700;
    }

    .step-content {
        padding: 2.5rem;
    }

    .step-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .step-title i {
        color: #009739;
        font-size: 1.75rem;
    }

    .step-subtitle {
        color: #64748b;
        margin-bottom: 2rem;
        font-size: 0.95rem;
    }

    .form-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .form-label-modern {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
    }

    .form-label-modern i {
        color: #009739;
        font-size: 1.1rem;
    }

    .form-control-modern {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.875rem 1.25rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: #009739;
        box-shadow: 0 0 0 3px rgba(0, 151, 57, 0.1);
    }

    .form-select-modern {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.875rem 1.25rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    }

    .form-select-modern:focus {
        border-color: #009739;
        box-shadow: 0 0 0 3px rgba(0, 151, 57, 0.1);
    }

    .quota-info-card {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.05) 0%, rgba(0, 151, 57, 0.02) 100%);
        border: 2px solid rgba(0, 151, 57, 0.2);
        border-radius: 16px;
        padding: 1.75rem;
        margin-bottom: 2rem;
    }

    .quota-info-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 12px;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .quota-info-item:last-child {
        margin-bottom: 0;
    }

    .quota-info-icon {
        width: 48px;
        height: 48px;
        background: rgba(0, 151, 57, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #009739;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .quota-info-content {
        flex: 1;
    }

    .quota-info-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .quota-info-value {
        font-size: 1rem;
        color: #1e293b;
        font-weight: 700;
        margin: 0;
    }

    .btn-modern {
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary-modern {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(0, 151, 57, 0.3);
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 151, 57, 0.4);
        color: white;
    }

    .btn-success-modern {
        background: var(--success-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-success-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .btn-outline-modern {
        border: 2px solid #e2e8f0;
        color: #64748b;
        background: white;
    }

    .btn-outline-modern:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    .price-type-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .price-type-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .price-type-card:hover {
        border-color: #009739;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 151, 57, 0.1);
    }

    .price-type-card.selected {
        border-color: #009739;
        background: rgba(0, 151, 57, 0.05);
        box-shadow: 0 4px 15px rgba(0, 151, 57, 0.2);
    }

    .price-type-card input[type="radio"] {
        display: none;
    }

    .price-type-card i {
        font-size: 2rem;
        color: #009739;
        margin-bottom: 0.75rem;
    }

    .price-type-card label {
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        cursor: pointer;
    }

    .option-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .option-card:hover {
        border-color: #009739;
        box-shadow: 0 4px 15px rgba(0, 151, 57, 0.1);
    }

    .option-card input[type="checkbox"]:checked ~ .option-content {
        color: #009739;
    }

    .option-card input[type="checkbox"]:checked ~ .option-content .option-icon {
        background: rgba(0, 151, 57, 0.1);
        color: #009739;
    }

    .option-card--titularidade-sale .form-check-input:checked ~ .form-check-label {
        color: #009739;
    }

    .option-card.selected {
        border-color: #009739;
        background: rgba(0, 151, 57, 0.05);
    }

    .option-card.border-danger {
        border-color: #dc2626 !important;
        background: rgba(220, 38, 38, 0.05) !important;
        animation: shake 0.3s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    /* Estilo para mensagem de aviso de limite de cidades */
    #city_limit_warning {
        border-left: 4px solid #ffc107;
        background-color: #fff3cd;
        border-color: #ffc107;
        animation: shake 0.5s;
    }

    .option-card input[type="checkbox"]:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }

    .option-card input[type="checkbox"]:disabled ~ .option-content {
        opacity: 0.9;
        cursor: default;
    }

    .option-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .option-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .option-text {
        flex: 1;
    }

    .option-title {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .option-description {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    .step-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 2rem;
        margin-top: 2rem;
        border-top: 2px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .offer-header {
            padding: 2rem 1.5rem;
        }

        .offer-header h1 {
            font-size: 1.75rem;
        }

        .step-content {
            padding: 1.5rem;
        }

        .price-type-selector {
            grid-template-columns: 1fr;
        }

        .step-indicator .step-label {
            font-size: 0.75rem;
        }
    }

    /* Estilos para seleção de cidades */
    .city-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 90px;
    }

    .city-card:hover {
        border-color: #009739;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 151, 57, 0.15);
    }

    .city-card.selected {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.1) 0%, rgba(0, 151, 57, 0.05) 100%);
        border-color: #009739;
        box-shadow: 0 4px 12px rgba(0, 151, 57, 0.2);
    }

    .city-card-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
    }

    .city-card i {
        color: #64748b;
        font-size: 1.25rem;
        transition: all 0.3s ease;
    }

    .city-card.selected i {
        color: #009739;
    }

    .city-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        word-break: break-word;
    }

    .city-card.selected .city-name {
        color: #009739;
    }

    .city-item {
        margin-bottom: 0.5rem;
    }

    .city-item.hidden {
        display: none;
    }

    #cities_container {
        scrollbar-width: thin;
        scrollbar-color: #009739 #e2e8f0;
    }

    #cities_container::-webkit-scrollbar {
        width: 8px;
    }

    #cities_container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    #cities_container::-webkit-scrollbar-thumb {
        background: #009739;
        border-radius: 4px;
    }

    #cities_container::-webkit-scrollbar-thumb:hover {
        background: #007a2e;
    }

    #selected_cities_tags .badge {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.8);
        }
    }

    #city_search:focus {
        border-color: #009739;
        box-shadow: 0 0 0 3px rgba(0, 151, 57, 0.1);
    }

    /* Estilo para mensagem de aviso de limite de cidades */
    #city_limit_warning {
        border-left: 4px solid #ffc107;
        background-color: #fff3cd;
        border-color: #ffc107;
    }
</style>

<div class="create-offer-page">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-xl-12">
                <div class="offer-card">
                    <!-- Header -->
                    <div class="offer-header">
                        <div class="offer-header-content">
                            <div class="offer-header-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <h1>Publicar Cota ou Fração</h1>
                            <p>Usufrua de sua cota ou frações, e ainda as transforme em receita caso queiras</p>
                        </div>
                    </div>

                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="steps">
                            <div class="step active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Informações</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Localização</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Preço</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-label">Finalizar</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <div class="step-content">
                        <form method="POST" action="{{ route('rental-offers.store') }}" enctype="multipart/form-data" id="offerForm" novalidate>
                            @csrf
                            
                            <!-- Step 1: Informações Básicas -->
                            <div class="step" id="step1">
                                <h3 class="step-title">
                                    <i class="fas fa-info-circle"></i>
                                    Informações Básicas
                                </h3>
                                <p class="step-subtitle">Selecione uma das suas cotas ou frações cadastradas e autorizadas para uso</p>
                                
                                <div class="form-section">
                                    <label for="quota_id" class="form-label-modern">
                                        <i class="fas fa-file-contract"></i>
                                        Minhas Cotas ou Frações <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-modern form-select-lg @error('quota_id') is-invalid @enderror" 
                                            id="quota_id" name="quota_id" required>
                                        <option value="" data-allows-rent-exchange="0">Selecione uma cota ou fração</option>
                                        
                                        {{-- Cotas inteiras --}}
                                        @foreach($quotas as $quota)
                                            @php
                                                $startDate = $quota->start_date ? \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') : 'N/A';
                                                $endDate = $quota->end_date ? \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') : 'N/A';
                                                $days = $quota->start_date && $quota->end_date ? \Carbon\Carbon::parse($quota->start_date)->diffInDays(\Carbon\Carbon::parse($quota->end_date)) + 1 : 'N/A';
                                                
                                                $hotel = $quota->hotel ?? ($quota->hotel_name ? \App\Models\Hotel::where('name', $quota->hotel_name)->first() : null);
                                                $city = $hotel->city ?? ($quota->location ? (explode(',', $quota->location)[0] ?? '') : '');
                                                $state = $hotel->state ?? ($quota->location ? trim(explode(',', $quota->location)[1] ?? '') : '');
                                                $hotelId = $hotel->id ?? null;
                                                
                                                $seasonalityMap = [
                                                    'low' => 'Baixa',
                                                    'medium' => 'Média',
                                                    'high' => 'Alta',
                                                    'peak' => 'Altíssima',
                                                ];
                                                $seasonalityLabel = $seasonalityMap[$quota->seasonality] ?? ($quota->seasonality ? ucfirst($quota->seasonality) : 'Não informada');
                                                $allowsRentExchangeOpt = ($quota->start_date && $quota->end_date)
                                                    ? $quota->periodInRegistrationHasRentExchange(
                                                        \Carbon\Carbon::parse($quota->start_date)->toDateString(),
                                                        \Carbon\Carbon::parse($quota->end_date)->toDateString()
                                                    )
                                                    : false;
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
                                                    data-is-fraction="false"
                                                    data-allows-rent-exchange="{{ $allowsRentExchangeOpt ? '1' : '0' }}"
                                                    {{ old('quota_id') == $quota->id ? 'selected' : '' }}>
                                                @php
                                                    // Exibir pernoites: sempre 1 a menos que o número de dias
                                                    $nights = $days > 0 ? $days - 1 : 0;
                                                @endphp
                                                {{ $quota->hotel_name ?? 'Hotel não informado' }} - {{ $startDate }} a {{ $endDate }} ({{ $nights }} {{ $nights == 1 ? 'pernoite' : 'pernoites' }})
                                            </option>
                                        @endforeach
                                        
                                        {{-- Frações (ofertas de aluguel fracionadas e frações de fraction_details) --}}
                                        @if(isset($allFractions) && $allFractions->count() > 0)
                                            @foreach($allFractions as $fractionOffer)
                                                @php
                                                    $fractionStartDate = $fractionOffer->start_date ? \Carbon\Carbon::parse($fractionOffer->start_date)->format('d/m/Y') : 'N/A';
                                                    $fractionEndDate = $fractionOffer->end_date ? \Carbon\Carbon::parse($fractionOffer->end_date)->format('d/m/Y') : 'N/A';
                                                    $fractionDays = $fractionOffer->number_of_days ?? 0;
                                                    $fractionNights = $fractionDays > 0 ? $fractionDays - 1 : 0;
                                                    
                                                    // Verificar se é uma RentalOffer ou um objeto temporário de fraction_details
                                                    if (isset($fractionOffer->is_from_fraction_details) && $fractionOffer->is_from_fraction_details) {
                                                        // É de fraction_details
                                                        $fractionHotel = $fractionOffer->hotel ?? null;
                                                        $fractionCity = $fractionOffer->city ?? '';
                                                        $fractionState = $fractionOffer->state ?? '';
                                                        $fractionHotelId = $fractionHotel->id ?? null;
                                                        $fractionHotelName = $fractionHotel->name ?? ($fractionOffer->quota->hotel_name ?? 'Hotel não informado');
                                                        $fractionLocation = $fractionOffer->quota->location ?? ($fractionCity . ($fractionState ? ', ' . $fractionState : ''));
                                                        $fractionSeasonality = $fractionOffer->quota->seasonality ?? '';
                                                    } else {
                                                        // É uma RentalOffer
                                                        $fractionHotel = $fractionOffer->hotel ?? ($fractionOffer->quota->hotel ?? null);
                                                        $fractionCity = $fractionHotel->city ?? ($fractionOffer->city ?? '');
                                                        $fractionState = $fractionHotel->state ?? ($fractionOffer->state ?? '');
                                                        $fractionHotelId = $fractionHotel->id ?? null;
                                                        $fractionHotelName = $fractionHotel->name ?? ($fractionOffer->quota->hotel_name ?? 'Hotel não informado');
                                                        $fractionLocation = $fractionOffer->quota->location ?? ($fractionCity . ($fractionState ? ', ' . $fractionState : ''));
                                                        $fractionSeasonality = $fractionOffer->quota->seasonality ?? '';
                                                    }
                                                    
                                                    $fractionSeasonalityMap = [
                                                        'low' => 'Baixa',
                                                        'medium' => 'Média',
                                                        'high' => 'Alta',
                                                        'peak' => 'Altíssima',
                                                    ];
                                                    $fractionSeasonalityLabel = $fractionSeasonalityMap[$fractionSeasonality] ?? ($fractionSeasonality ? ucfirst($fractionSeasonality) : 'Não informada');

                                                    $allowsRentExchangeOpt = false;
                                                    if (isset($fractionOffer->is_from_fraction_details) && $fractionOffer->is_from_fraction_details) {
                                                        $allowsRentExchangeOpt = (bool) ($fractionOffer->allows_rent_exchange ?? false);
                                                    } elseif ($fractionOffer instanceof \App\Models\RentalOffer && $fractionOffer->quota && $fractionOffer->start_date && $fractionOffer->end_date) {
                                                        $allowsRentExchangeOpt = $fractionOffer->quota->periodInRegistrationHasRentExchange(
                                                            \Carbon\Carbon::parse($fractionOffer->start_date)->toDateString(),
                                                            \Carbon\Carbon::parse($fractionOffer->end_date)->toDateString()
                                                        );
                                                    }
                                                @endphp
                                                @php
                                                    $weekLabel = isset($fractionOffer->week_number) ? 'Semana ' . $fractionOffer->week_number . ' - ' : '';
                                                @endphp
                                                <option value="{{ $fractionOffer->quota_id }}" 
                                                        data-hotel-name="{{ $fractionHotelName }}"
                                                        data-hotel-id="{{ $fractionHotelId }}"
                                                        data-location="{{ $fractionLocation }}"
                                                        data-start-date="{{ $fractionOffer->start_date ? \Carbon\Carbon::parse($fractionOffer->start_date)->format('Y-m-d') : '' }}"
                                                        data-end-date="{{ $fractionOffer->end_date ? \Carbon\Carbon::parse($fractionOffer->end_date)->format('Y-m-d') : '' }}"
                                                        data-start-date-formatted="{{ $fractionStartDate }}"
                                                        data-end-date-formatted="{{ $fractionEndDate }}"
                                                        data-duration="{{ $fractionDays }}"
                                                        data-guests="{{ $fractionOffer->number_of_people ?? ($fractionOffer->quota->number_of_guests ?? '') }}"
                                                        data-rooms="{{ $fractionOffer->quota->number_of_rooms ?? '' }}"
                                                        data-seasonality="{{ $fractionSeasonality }}"
                                                        data-seasonality-label="{{ $fractionSeasonalityLabel }}"
                                                        data-city="{{ $fractionCity }}"
                                                        data-state="{{ $fractionState }}"
                                                        data-is-fraction="true"
                                                        data-allows-rent-exchange="{{ $allowsRentExchangeOpt ? '1' : '0' }}"
                                                        {{ old('quota_id') == $fractionOffer->quota_id ? 'selected' : '' }}>
                                                    {{ $weekLabel }}[Fração] {{ $fractionHotelName }} - {{ $fractionStartDate }} a {{ $fractionEndDate }} ({{ $fractionNights }} {{ $fractionNights == 1 ? 'pernoite' : 'pernoites' }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Selecione uma das suas cotas ou frações cadastradas
                                    </small>
                                    @error('quota_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-section">
                                    <label for="title" class="form-label-modern">
                                        <i class="fas fa-heading"></i>
                                        Título da Oferta <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-modern form-control-lg @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Ex: Cota de 7 dias no Hotel Copacabana Palace" required>
                                    @error('title')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                
                                <div class="step-navigation">
                                    <div></div>
                                    <button type="button" class="btn btn-primary-modern btn-modern" onclick="nextStep()">
                                        Próximo <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Localização e Datas -->
                            <div class="step d-none" id="step2">
                              
                                
                                <!-- Informações da Cota Selecionada -->
                                <div id="quota_info_display" class="quota-info-card" style="display: none;">
                                    <h5 class="fw-bold mb-3 text-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Informações da Cota ou Fração Selecionada
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-hotel"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Hotel</div>
                                                    <p class="quota-info-value" id="quota_hotel_name">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Localização</div>
                                                    <p class="quota-info-value" id="quota_location">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-calendar-check"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Data de Início</div>
                                                    <p class="quota-info-value" id="quota_start_date">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Data de Fim</div>
                                                    <p class="quota-info-value" id="quota_end_date">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-moon"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Duração</div>
                                                    <p class="quota-info-value" id="quota_duration">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Número de Pessoas</div>
                                                    <p class="quota-info-value" id="quota_guests">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-bed"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Número de Quartos</div>
                                                    <p class="quota-info-value" id="quota_rooms">-</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quota-info-item">
                                                <div class="quota-info-icon">
                                                    <i class="fas fa-sun"></i>
                                                </div>
                                                <div class="quota-info-content">
                                                    <div class="quota-info-label">Sazonalidade</div>
                                                    <p class="quota-info-value" id="quota_seasonality">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Campos hidden -->
                                <input type="hidden" id="hotel_id" name="hotel_id" value="{{ old('hotel_id') }}">
                                <input type="hidden" id="city" name="city" value="{{ old('city') }}">
                                <input type="hidden" id="state" name="state" value="{{ old('state') }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ old('start_date') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                <input type="hidden" id="number_of_people" name="number_of_people" value="{{ old('number_of_people') }}">
                                <input type="hidden" name="period_type" value="exact">
                                
                                <div class="step-navigation">
                                    <button type="button" class="btn btn-outline-modern btn-modern" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Voltar
                                    </button>
                                    <button type="button" class="btn btn-primary-modern btn-modern" onclick="nextStep()">
                                        Próximo <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Preço e Opções -->
                            <div class="step d-none" id="step3">
                                <h3 class="step-title">
                                    <i class="fas fa-dollar-sign"></i>
                                    Preço e Opções
                                </h3>
                                <p class="step-subtitle">Defina o preço e configure os filtros de otimização do êxito da sua oferta</p>
                                
                                <div class="form-section">
                                   
                                    <div class="price-type-selector" style="display: none;">
                                        <div class="price-type-card selected">
                                            <input type="hidden" name="price_type" value="fixed">
                                            <i class="fas fa-tag"></i>
                                            <label>Preço Fixo</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Preço Fixo -->
                                    <div id="fixed_price_fields">
                                        <label for="price" class="form-label-modern">
                                            <i class="fas fa-money-bill-wave"></i>
                                            Preço original (R$) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border: 2px solid #e2e8f0; border-right: none; border-radius: 12px 0 0 12px; background: #f8fafc; font-weight: 600;">R$</span>
                                            <input type="number" class="form-control form-control-modern @error('price') is-invalid @enderror" 
                                                   id="price" name="price" value="{{ old('price') }}" 
                                                   placeholder="0.00" step="0.01" min="0" style="border-left: none; border-radius: 0 12px 12px 0;">
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <label class="form-label-modern">
                                        <i class="fas fa-cogs"></i>
                                        Filtros de Otimização
                                        <span id="quota_period_display" class="text-muted ms-2" style="font-weight: normal; font-size: 0.9em;">
                                            - Selecione uma cota ou fração
                                        </span>
                                    </label>
                                    
                                    <div class="option-card selected" style="background: rgba(220, 38, 38, 0.05); border-color: rgba(220, 38, 38, 0.3);">
                                        <input type="hidden" name="super_desconto" value="1">
                                        <input type="checkbox" id="super_desconto" value="1" checked disabled>
                                        <div class="option-content">
                                            <div class="option-icon" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-tag text-danger"></i>
                                                    Super Desconto
                                                </div>
                                                <p class="option-description">Ativação automática, 14 dias antes do início da validade da Cota ou Fração. <b style="color:rgb(126, 0, 0);">Redução automática de 20% do preço original da oferta</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="mega_oferta" id="mega_oferta" value="1" {{ old('mega_oferta') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-star text-warning"></i>
                                                    Mega Oferta
                                                </div>
                                                <p class="option-description">
                                                    Ajustes de preço. Ativação por perfil: 
                                                    Curioso → 3 dias antes | Inteligente → 5 dias antes | Sábio → 7 dias antes da validade da Cota ou Fração. <b>Feito pelo usuário.</b>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 d-none" id="mega_oferta_price_field">
                                        <label for="mega_oferta_price" class="form-label-modern">Preço da Mega Oferta (R$)</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border: 2px solid #e2e8f0; border-right: none; border-radius: 12px 0 0 12px; background: #f8fafc; font-weight: 600;">R$</span>
                                            <input type="number" class="form-control form-control-modern" id="mega_oferta_price" name="mega_oferta_price" value="{{ old('mega_oferta_price') }}" step="0.01" min="0" placeholder="0.00" style="border-left: none; border-radius: 0 12px 12px 0;">
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="is_auction" id="is_auction" value="1" {{ old('is_auction') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-gavel"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-gavel text-warning"></i>
                                                    Leilão
                                                </div>
                                                <p class="option-description">Data e hora de início, preço mínimo. Duração: 20 minutos. Mostra lances em tempo real e o nome do comprador. <b>Feito pelo usuário.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Auction Options -->
                                    <div class="mt-3 d-none" id="auction_options">
                                        <div class="form-section" style="background: rgba(251, 191, 36, 0.05); border-color: rgba(251, 191, 36, 0.3);">
                                            <h6 class="fw-bold text-warning mb-3">
                                                <i class="fas fa-gavel me-2"></i>Configurações do Leilão
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="minimum_price" class="form-label-modern">
                                                        <i class="fas fa-arrow-up"></i>
                                                        Preço Mínimo (R$) <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text" style="border: 2px solid #e2e8f0; border-right: none; border-radius: 12px 0 0 12px; background: #f8fafc; font-weight: 600;">R$</span>
                                                        <input type="number" class="form-control form-control-modern @error('minimum_price') is-invalid @enderror" 
                                                               id="minimum_price" name="minimum_price" value="{{ old('minimum_price') }}" 
                                                               placeholder="0.00" step="0.01" min="0" style="border-left: none; border-radius: 0 12px 12px 0;">
                                                    </div>
                                                    @error('minimum_price')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="auction_day" class="form-label-modern">
                                                        <i class="fas fa-calendar-day"></i>
                                                        Dia do Leilão <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control form-control-modern @error('auction_day') is-invalid @enderror" 
                                                           id="auction_day" name="auction_day" value="{{ old('auction_day') }}" 
                                                           min="{{ date('Y-m-d') }}">
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="fas fa-info-circle me-1"></i>O dia do leilão pode ser até a penúltima data de validade da cota ou fração.
                                                    </small>
                                                    @error('auction_day')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="auction_start_hour" class="form-label-modern">
                                                        <i class="fas fa-clock"></i>
                                                        Horário de Início <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="time" class="form-control form-control-modern @error('auction_start_hour') is-invalid @enderror" 
                                                           id="auction_start_hour" name="auction_start_hour" value="{{ old('auction_start_hour') }}">
                                                    @error('auction_start_hour')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label-modern">
                                                        <i class="fas fa-hourglass-half"></i>
                                                        Duração
                                                    </label>
                                                    <input type="hidden" name="auction_duration_minutes" value="20">
                                                    <input type="text" class="form-control form-control-modern" value="20 minutos" readonly disabled style="background-color: #f1f5f9; border-color: #cbd5e1;">
                                                    <small class="text-muted">Duração fixa: 20 minutos</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="sofa_plus" id="sofa_plus" value="1" {{ old('sofa_plus') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-couch"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-couch text-info"></i>
                                                    Sofá Mais
                                                </div>
                                                <p class="option-description">Garanta seu quarto privado, disponibilize leitos do sofá-cama para terceiros, e todos pagam menos no mesmo apartamento. <b>Feito pelos usuários.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="city_promotion" id="city_promotion" value="1" {{ old('city_promotion') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-bullhorn"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-bullhorn text-success"></i>
                                                    Informe de ofertas disponíveis por cidade 
                                                </div>
                                                <p class="option-description">Avisar em certas cidades que sua oferta está disponível. Envio por <i>e-mail</i> e <i>WhatsApp</i>. <b>Feito pelo usuário e de acordo com seu perfil.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 d-none" id="city_promotion_cities">
                                        <label class="form-label-modern mb-3">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Selecione as cidades 
                                            <span class="text-muted small">(Máximo: <span id="max_cities_limit">{{ $maxCitiesAlerts ?? 0 }}</span> 
                                            @if(isset($profileType))
                                                @if($profileType === 'curioso')
                                                    - Perfil Curioso
                                                @elseif($profileType === 'inteligente')
                                                    - Perfil Inteligente
                                                @elseif($profileType === 'sabio')
                                                    - Perfil Sábio
                                                @endif
                                            @endif
                                            )</span>
                                        </label>
                                        
                                        <!-- Mensagem de aviso -->
                                        <div id="city_limit_warning" class="alert alert-warning d-none mb-3" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span id="city_limit_message"></span>
                                        </div>
                                        
                                        <!-- Campo de busca -->
                                        <div class="mb-3">
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-search text-muted"></i>
                                                </span>
                                                <input type="text" class="form-control border-start-0" id="city_search" 
                                                       placeholder="Buscar cidade..." autocomplete="off">
                                            </div>
                                        </div>
                                        
                                        <!-- Tags de cidades selecionadas -->
                                        <div id="selected_cities_tags" class="mb-3 d-flex flex-wrap gap-2" style="min-height: 40px;">
                                            @if(old('promotion_cities'))
                                                @foreach(old('promotion_cities') as $selectedCode)
                                                    @php $tagLabel = \App\Models\CidadeCapital::labelForPromotionValue((string) $selectedCode); @endphp
                                                    <span id="tag_{{ $selectedCode }}" class="badge bg-success px-3 py-2 d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $tagLabel }}
                                                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.7rem;" onclick="removeCity(@json((string) $selectedCode))" aria-label="Remover"></button>
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        
                                        <!-- Grid de cidades -->
                                        <div id="cities_container" class="border rounded p-3" style="max-height: 400px; overflow-y: auto; background: #f8fafc;">
                                            @php
                                                $selectedCities = array_map('strval', old('promotion_cities', []));
                                            @endphp
                                            <div class="row g-2" id="cities_grid">
                                            @foreach(($informeCidades ?? collect()) as $cidade)
                                                    <div class="col-md-3 col-sm-4 col-6 city-item" data-city="{{ strtolower($cidade->nome.' '.$cidade->uf) }}">
                                                        <div class="city-card {{ in_array((string) $cidade->codigo_ibge, $selectedCities, true) ? 'selected' : '' }}"
                                                             data-city-label="{{ $cidade->nome }}/{{ $cidade->uf }}"
                                                             onclick="toggleCity('{{ $cidade->codigo_ibge }}')">
                                                            <input type="checkbox"
                                                                   name="promotion_cities[]"
                                                                   value="{{ $cidade->codigo_ibge }}"
                                                                   id="city_{{ $loop->index }}"
                                                                   {{ in_array((string) $cidade->codigo_ibge, $selectedCities, true) ? 'checked' : '' }}
                                                                   style="display: none;">
                                                            <div class="city-card-content">
                                                                <i class="fas fa-map-marker-alt mb-2"></i>
                                                                <span class="city-name">{{ $cidade->nome }}</span>
                                                                <span class="city-uf d-block small text-muted">{{ $cidade->uf }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                            @endforeach
                                            </div>
                                            <div id="no_cities_found" class="text-center text-muted py-4 d-none">
                                                <i class="fas fa-search fa-2x mb-2"></i>
                                                <p>Nenhuma cidade encontrada</p>
                                            </div>
                                        </div>
                                        
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Clique nas cidades para selecioná-las. Limite baseado no seu perfil.
                                        </small>
                                    </div>
                                    
                                    <div id="rent_exchange_options_wrap" class="d-none">
                                    <div class="option-card">
                                        <input type="checkbox" name="accepts_exchange" id="accepts_exchange" value="1" {{ old('accepts_exchange') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-exchange-alt"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-exchange-alt text-success"></i>
                                                    Troca Simples
                                                </div>
                                                <p class="option-description">Permite trocar o periodo de uso desta Cota ou Fração por outra. <b>Feito pelo usuário.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="accepts_diaria_exchange" id="accepts_diaria_exchange" value="1" {{ old('accepts_diaria_exchange', old('fair_exchange')) ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-balance-scale"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-balance-scale text-info"></i>
                                                    Troca Justa
                                                </div>
                                                <p class="option-description">Realiza Troca de diárias entre Hotéis de classificação e Cotas distintas. Permite inclusive acertos com pagamento. <b>Feito pelos usuários.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    
                                    @php
                                        $oldAcceptsSale = old('accepts_sale');
                                        $oldAcceptsSaleYes = $oldAcceptsSale === '1' || $oldAcceptsSale === 1 || $oldAcceptsSale === true;
                                        $oldAcceptsSaleNo = $oldAcceptsSale === '0' || $oldAcceptsSale === 0 || $oldAcceptsSale === false;
                                    @endphp
                                    <div class="option-card option-card--titularidade-sale d-none" id="titularidade_sale_section">
                                        <input type="hidden" name="accepts_sale" value="0" id="accepts_sale_hidden_zero">
                                        <div class="option-content" id="titularidade_sale_radios_inner">
                                            <div class="option-icon">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </div>
                                            <div class="option-text flex-grow-1">
                                                <div class="option-title">
                                                    <i class="fas fa-hand-holding-usd text-success"></i>
                                                    Aceita Troca de titularidade
                                                    <span class="text-danger js-titularidade-required-mark d-none" title="Obrigatório">*</span>
                                                </div>
                                                <p class="option-description mb-0">Permite vender a cota. Obrigatório informar Sim ou Não quando a oferta for para <strong>cota inteira</strong> com <strong>7 pernoites</strong>.</p>
                                                <div class="d-flex flex-wrap gap-4 mt-3">
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" id="accepts_sale_yes" value="1" {{ $oldAcceptsSaleYes ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold" for="accepts_sale_yes">Sim, aceito</label>
                                                    </div>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" id="accepts_sale_no" value="0" {{ $oldAcceptsSaleNo ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold" for="accepts_sale_no">Não aceito</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="is_unique_offer" id="is_unique_offer" value="1" {{ old('is_unique_offer') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-star text-warning"></i>
                                                    Oferta Única
                                                </div>
                                                <p class="option-description">Sua Oferta irrecusável em dias e horários desafiadores. <b>Feito pelo usuário.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="torei_na_Véspera" id="torei_na_Véspera" value="1" {{ old('torei_na_Véspera') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-moon"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-moon text-primary"></i>
                                                    Torei na Véspera
                                                </div>
                                                <p class="option-description">Ative um ajuste especial com pelo menos <b>40% de desconto</b> do preço da oferta original.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card">
                                        <input type="checkbox" name="torei_no_dia" id="torei_no_dia" value="1" {{ old('torei_no_dia') ? 'checked' : '' }}>
                                        <div class="option-content">
                                            <div class="option-icon">
                                                <i class="fas fa-sun"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-sun text-warning"></i>
                                                    Torei no Dia
                                                </div>
                                                <p class="option-description">Ative um ajuste especial com pelo menos <b>55% de desconto</b> do preço da oferta original.>.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="option-card" style="background: rgba(0, 151, 57, 0.05); border-color: rgba(0, 151, 57, 0.3);">
                                        <input type="checkbox" name="accept_auto_discounts" id="accept_auto_discounts" value="1" {{ old('accept_auto_discounts') ? 'checked' : '' }} required>
                                        <div class="option-content">
                                            <div class="option-icon" style="background: rgba(0, 151, 57, 0.1); color: #009739;">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="option-text">
                                                <div class="option-title">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                    Aceito participar dos descontos automáticos <span class="text-danger">*</span>
                                                </div>
                                                <p class="option-description">Ciente e aceito participar dos descontos automáticos e promoções deste aplicativo.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-navigation">
                                    <button type="button" class="btn btn-outline-modern btn-modern" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Voltar
                                    </button>
                                    <button type="button" class="btn btn-primary-modern btn-modern" onclick="nextStep()">
                                        Próximo <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 4: Finalizar -->
                            <div class="step d-none" id="step4">
                                <h3 class="step-title">
                                    <i class="fas fa-check-circle"></i>
                                    Finalizar
                                </h3>
                                <p class="step-subtitle">Dê um <i>upgrade</i> no destaque de sua oferta e a publique</p>
                                
                                <div class="form-section">
                                    <label class="form-label-modern">
                                        <i class="fas fa-star"></i>
                                        Destacar Publicação
                                    </label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="option-card" onclick="selectHighlight(7)">
                                                <input type="radio" name="highlight_duration" id="highlight_7" value="7">
                                                <div class="option-content">
                                                    <div class="option-icon" style="background: rgba(251, 191, 36, 0.1); color: #f59e0b;">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                    <div class="option-text">
                                                        <div class="option-title">
                                                            7 dias
                                                        </div>
                                                        <p class="option-description mb-0"><strong>R$ 10,00</strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="option-card" onclick="selectHighlight(14)">
                                                <input type="radio" name="highlight_duration" id="highlight_14" value="14">
                                                <div class="option-content">
                                                    <div class="option-icon" style="background: rgba(251, 191, 36, 0.1); color: #f59e0b;">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                    <div class="option-text">
                                                        <div class="option-title">
                                                            14 dias
                                                        </div>
                                                        <p class="option-description mb-0"><strong>R$ 15,00</strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="option-card" onclick="selectHighlight(30)">
                                                <input type="radio" name="highlight_duration" id="highlight_30" value="30">
                                                <div class="option-content">
                                                    <div class="option-icon" style="background: rgba(251, 191, 36, 0.1); color: #f59e0b;">
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                    <div class="option-text">
                                                        <div class="option-title">
                                                            30 dias
                                                        </div>
                                                        <p class="option-description mb-0"><strong>R$ 25,00</strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Destaque sua publicação para maior visibilidade
                                    </small>
                                </div>
                                
                                <div class="step-navigation">
                                    <button type="button" class="btn btn-outline-modern btn-modern" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Voltar
                                    </button>
                                    <button type="submit" class="btn btn-success-modern btn-modern">
                                        <i class="fas fa-check me-2"></i>Publicar Oferta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 4;

function updateStepIndicator() {
    document.querySelectorAll('.step-indicator .step').forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');
        if (stepNum < currentStep) {
            step.classList.add('completed');
        } else if (stepNum === currentStep) {
            step.classList.add('active');
        }
    });
}

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            document.getElementById(`step${currentStep}`).classList.add('d-none');
            currentStep++;
            document.getElementById(`step${currentStep}`).classList.remove('d-none');
            updateStepIndicator();
            
            if (currentStep === 2) {
                const quotaSelect = document.getElementById('quota_id');
                if (quotaSelect && quotaSelect.value) {
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
        updateStepIndicator();
    }
}

function validateCurrentStep() {
    const currentStepElement = document.getElementById(`step${currentStep}`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;
    
    for (let field of requiredFields) {
        // Skip hidden fields
        if (field.offsetParent === null) {
            continue;
        }
        
        let fieldValid = false;
        
        // Checkbox validation
        if (field.type === 'checkbox') {
            fieldValid = field.checked;
        } 
        // Radio button validation
        else if (field.type === 'radio') {
            const radioGroup = currentStepElement.querySelectorAll(`input[type="radio"][name="${field.name}"]`);
            fieldValid = Array.from(radioGroup).some(radio => radio.checked);
        }
        // Other fields (text, select, textarea, etc.)
        else {
            fieldValid = field.value.trim() !== '';
        }
        
        if (!fieldValid) {
            field.classList.add('is-invalid');
            isValid = false;
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
            
            // Also add invalid class to parent option-card if it exists
            const optionCard = field.closest('.option-card');
            if (optionCard) {
                optionCard.classList.add('border-danger');
                optionCard.style.borderColor = '#dc2626';
            }
        } else {
            field.classList.remove('is-invalid');
            
            // Remove invalid styling from option-card
            const optionCard = field.closest('.option-card');
            if (optionCard) {
                optionCard.classList.remove('border-danger');
                optionCard.style.borderColor = '';
            }
        }
    }
    
    if (!isValid) {
        // Scroll to first invalid field
        if (firstInvalidField) {
            setTimeout(() => {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
        alert('Por favor, preencha todos os campos obrigatórios.');
    }
    
    return isValid;
}

// Função selectPriceType removida - não é mais necessária pois só existe preço fixo

function selectHighlight(days) {
    document.querySelectorAll('.option-card').forEach(card => {
        if (card.querySelector('input[type="radio"][name="highlight_duration"]')) {
            card.classList.remove('selected');
        }
    });
    
    const selectedCard = event.currentTarget;
    selectedCard.classList.add('selected');
    selectedCard.querySelector('input[type="radio"]').checked = true;
}

// Initialize price type cards
document.addEventListener('DOMContentLoaded', function() {
    // Preço fixo é sempre selecionado, não há mais opção de faixa de preço
    const priceCards = document.querySelectorAll('.price-type-card');
    priceCards.forEach(card => {
            card.classList.add('selected');
    });
    
    // Initialize checkbox cards
    document.querySelectorAll('.option-card input[type="checkbox"]').forEach(checkbox => {
        if (checkbox.checked) {
            checkbox.closest('.option-card').classList.add('selected');
        }
        // Skip disabled checkboxes (like super_desconto)
        if (!checkbox.disabled) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    this.closest('.option-card').classList.add('selected');
                } else {
                    this.closest('.option-card').classList.remove('selected');
                }
                
                // Remove invalid styling when checkbox is checked
                if (this.checked) {
                    this.classList.remove('is-invalid');
                    const optionCard = this.closest('.option-card');
                    if (optionCard) {
                        optionCard.classList.remove('border-danger');
                        optionCard.style.borderColor = '';
                    }
                }
            });
        }
    });

    syncTitularidadeSaleField();
    syncRentExchangeOptionsFromQuota();

    document.getElementById('offerForm')?.addEventListener('submit', function() {
        syncTitularidadeSaleField();
        syncRentExchangeOptionsFromQuota();
    });
});

function syncTitularidadeSaleField() {
    const quotaSelect = document.getElementById('quota_id');
    const section = document.getElementById('titularidade_sale_section');
    const hiddenZero = document.getElementById('accepts_sale_hidden_zero');
    const radioYes = document.getElementById('accepts_sale_yes');
    const radioNo = document.getElementById('accepts_sale_no');
    const mark = section ? section.querySelector('.js-titularidade-required-mark') : null;
    if (!quotaSelect || !section || !hiddenZero || !radioYes || !radioNo) {
        return;
    }

    const clearRadioNames = () => {
        radioYes.removeAttribute('name');
        radioNo.removeAttribute('name');
    };
    const setRadioNames = () => {
        radioYes.name = 'accepts_sale';
        radioNo.name = 'accepts_sale';
    };

    const opt = quotaSelect.options[quotaSelect.selectedIndex];
    if (!opt || !quotaSelect.value) {
        section.classList.add('d-none');
        section.classList.remove('selected');
        radioYes.checked = false;
        radioNo.checked = false;
        radioYes.required = false;
        clearRadioNames();
        radioYes.disabled = true;
        radioNo.disabled = true;
        hiddenZero.disabled = false;
        hiddenZero.setAttribute('name', 'accepts_sale');
        hiddenZero.value = '0';
        if (mark) {
            mark.classList.add('d-none');
        }
        return;
    }

    const isFraction = opt.dataset.isFraction === 'true';
    const duration = parseInt(opt.dataset.duration || '0', 10);
    const nights = duration > 0 ? duration - 1 : -1;
    const show = !isFraction && nights === 7;

    if (show) {
        section.classList.remove('d-none');
        hiddenZero.disabled = true;
        hiddenZero.removeAttribute('name');
        radioYes.disabled = false;
        radioNo.disabled = false;
        setRadioNames();
        radioYes.required = true;
        if (mark) {
            mark.classList.remove('d-none');
        }
        if (radioYes.checked || radioNo.checked) {
            section.classList.add('selected');
        } else {
            section.classList.remove('selected');
        }
    } else {
        section.classList.add('d-none');
        section.classList.remove('selected');
        radioYes.checked = false;
        radioNo.checked = false;
        radioYes.required = false;
        radioYes.disabled = true;
        radioNo.disabled = true;
        clearRadioNames();
        hiddenZero.disabled = false;
        hiddenZero.setAttribute('name', 'accepts_sale');
        hiddenZero.value = '0';
        if (mark) {
            mark.classList.add('d-none');
        }
    }
}

// Carregar dados da cota
document.getElementById('quota_id')?.addEventListener('change', function() {
    const quotaId = this.value;
    const quotaInfoDisplay = document.getElementById('quota_info_display');
    
    if (!quotaId) {
        quotaInfoDisplay.style.display = 'none';
        // Limpar período quando nenhuma cota estiver selecionada
        const periodDisplay = document.getElementById('quota_period_display');
        if (periodDisplay) {
            periodDisplay.textContent = '- Selecione uma cota ou fração';
        }
        syncTitularidadeSaleField();
        syncRentExchangeOptionsFromQuota();
        return;
    }
    
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.dataset) {
        const data = selectedOption.dataset;
        
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
        
        // Atualizar período ao lado do título "Filtros de Otimização"
        const periodDisplay = document.getElementById('quota_period_display');
        if (periodDisplay && data.startDateFormatted && data.endDateFormatted) {
            periodDisplay.textContent = '- ' + data.startDateFormatted + ' a ' + data.endDateFormatted;
        } else if (periodDisplay) {
            periodDisplay.textContent = '- Período não informado';
        }
        document.getElementById('quota_rooms').textContent = data.rooms ? data.rooms + ' ' + (data.rooms == 1 ? 'quarto' : 'quartos') : 'Não informado';
        
        const seasonalityLabel = data.seasonalityLabel || (() => {
            const seasonalityMap = {
                'low': 'Baixa', 'medium': 'Média', 'high': 'Alta', 'peak': 'Altíssima',
                'baixa': 'Baixa', 'media': 'Média', 'alta': 'Alta', 'altissima': 'Altíssima'
            };
            return seasonalityMap[data.seasonality] || (data.seasonality ? data.seasonality : 'Não informada');
        })();
        document.getElementById('quota_seasonality').textContent = seasonalityLabel;
        
        document.getElementById('hotel_id').value = data.hotelId || '';
        document.getElementById('city').value = data.city || '';
        let stateValue = data.state ? data.state.trim() : '';
        if (stateValue.includes(',')) {
            const parts = stateValue.split(',');
            stateValue = parts[parts.length - 1].trim();
        }
        if (stateValue.includes('-')) {
            const parts = stateValue.split('-');
            stateValue = parts[parts.length - 1].trim();
        }
        document.getElementById('state').value = stateValue;
        document.getElementById('start_date').value = data.startDate || '';
        document.getElementById('end_date').value = data.endDate || '';
        document.getElementById('number_of_people').value = data.guests || '';
        
        // Atualizar limite máximo do dia do leilão (penúltima data de validade)
        updateAuctionDayMax(data.endDate);
        
        quotaInfoDisplay.style.display = 'block';
    }
    syncTitularidadeSaleField();
    syncRentExchangeOptionsFromQuota();
});

function syncRentExchangeOptionsFromQuota() {
    const wrap = document.getElementById('rent_exchange_options_wrap');
    const quotaSelect = document.getElementById('quota_id');
    if (!wrap || !quotaSelect) {
        return;
    }
    const opt = quotaSelect.options[quotaSelect.selectedIndex];
    const allowed = Boolean(quotaSelect.value && opt && opt.dataset && opt.dataset.allowsRentExchange === '1');
    if (allowed) {
        wrap.classList.remove('d-none');
    } else {
        wrap.classList.add('d-none');
        const ex = document.getElementById('accepts_exchange');
        const di = document.getElementById('accepts_diaria_exchange');
        if (ex) {
            ex.checked = false;
            ex.closest('.option-card')?.classList.remove('selected');
        }
        if (di) {
            di.checked = false;
            di.closest('.option-card')?.classList.remove('selected');
        }
    }
}

document.getElementById('accepts_sale_yes')?.addEventListener('change', function() {
    const section = document.getElementById('titularidade_sale_section');
    if (section && this.checked) {
        section.classList.add('selected');
    }
});
document.getElementById('accepts_sale_no')?.addEventListener('change', function() {
    const section = document.getElementById('titularidade_sale_section');
    if (section && this.checked) {
        section.classList.add('selected');
    }
});

// Função para atualizar o limite máximo do dia do leilão
function updateAuctionDayMax(endDate) {
    const auctionDay = document.getElementById('auction_day');
    if (!auctionDay || !endDate) {
        return;
    }
    
    // Calcular a penúltima data (endDate - 1 dia)
    const endDateObj = new Date(endDate);
    endDateObj.setDate(endDateObj.getDate() - 1);
    
    // Formatar como YYYY-MM-DD
    const year = endDateObj.getFullYear();
    const month = String(endDateObj.getMonth() + 1).padStart(2, '0');
    const day = String(endDateObj.getDate()).padStart(2, '0');
    const maxDate = `${year}-${month}-${day}`;
    
    // Definir o atributo max
    auctionDay.setAttribute('max', maxDate);
    
    // Se o valor atual for maior que o máximo, limpar o campo
    if (auctionDay.value && auctionDay.value > maxDate) {
        auctionDay.value = '';
        // Mostrar mensagem de aviso
        const existingWarning = document.querySelector('.auction-day-max-warning');
        if (existingWarning) {
            existingWarning.remove();
        }
        const warning = document.createElement('small');
        warning.className = 'text-warning d-block mt-1 auction-day-max-warning';
        warning.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>O dia do leilão foi ajustado. A data máxima permitida é a penúltima data de validade da cota.';
        auctionDay.parentElement.appendChild(warning);
        setTimeout(() => {
            if (warning.parentElement) {
                warning.remove();
            }
        }, 5000);
    }
}

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
        if (this.checked) {
            // Focar no campo de busca quando mostrar
            setTimeout(() => {
                document.getElementById('city_search')?.focus();
            }, 100);
        }
    }
});

// Função para alternar seleção de cidade (valor = código IBGE do município)
function toggleCity(ibge) {
    const code = String(ibge);
    const checkbox = document.querySelector(`input[name="promotion_cities[]"][value="${code}"]`);
    if (!checkbox) return;

    const cityCard = checkbox.closest('.city-card');
    const isSelected = checkbox.checked;

    if (isSelected) {
        checkbox.checked = false;
        cityCard.classList.remove('selected');
        removeCityTag(code);
        hideCityLimitWarning();
    } else {
        const maxCities = parseInt(document.getElementById('max_cities_limit')?.textContent || '0');
        const selectedCount = document.querySelectorAll('input[name="promotion_cities[]"]:checked').length;

        if (maxCities > 0 && selectedCount >= maxCities) {
            showCityLimitWarning(maxCities);
            return;
        }

        checkbox.checked = true;
        cityCard.classList.add('selected');
        addCityTag(code);
        hideCityLimitWarning();
    }
}

// Função para adicionar tag de cidade selecionada
function addCityTag(ibge) {
    const tagsContainer = document.getElementById('selected_cities_tags');
    if (!tagsContainer) return;

    const code = String(ibge);
    const checkbox = document.querySelector(`input[name="promotion_cities[]"][value="${code}"]`);
    const label = checkbox?.closest('.city-card')?.dataset?.cityLabel || code;

    if (document.getElementById('tag_' + code)) {
        return;
    }

    const tag = document.createElement('span');
    tag.id = 'tag_' + code;
    tag.className = 'badge bg-success px-3 py-2 d-flex align-items-center gap-2';
    tag.style.cssText = 'font-size: 0.9rem; animation: fadeIn 0.3s ease;';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close btn-close-white';
    btn.style.fontSize = '0.7rem';
    btn.setAttribute('aria-label', 'Remover');
    btn.addEventListener('click', function () { removeCity(code); });
    const icon = document.createElement('i');
    icon.className = 'fas fa-map-marker-alt';
    const labelSpan = document.createElement('span');
    labelSpan.textContent = label;
    tag.appendChild(icon);
    tag.appendChild(labelSpan);
    tag.appendChild(btn);
    tagsContainer.appendChild(tag);
}

// Função para remover tag de cidade
function removeCityTag(ibge) {
    const code = String(ibge);
    const tag = document.getElementById('tag_' + code);
    if (tag) {
        tag.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            tag.remove();
        }, 300);
    }
}

// Função para remover cidade (usado pelo botão X nas tags)
function removeCity(ibge) {
    const code = String(ibge);
    const checkbox = document.querySelector(`input[name="promotion_cities[]"][value="${code}"]`);
    if (checkbox) {
        checkbox.checked = false;
        const cityCard = checkbox.closest('.city-card');
        if (cityCard) {
            cityCard.classList.remove('selected');
        }
        removeCityTag(code);
        hideCityLimitWarning();
    }
}

// Função para mostrar aviso de limite de cidades
function showCityLimitWarning(maxCities) {
    const warningDiv = document.getElementById('city_limit_warning');
    const messageSpan = document.getElementById('city_limit_message');
    
    if (warningDiv && messageSpan) {
        let profileType = '{{ $profileType ?? "curioso" }}';
        let profileName = '';
        
        if (profileType === 'curioso') {
            profileName = 'Curioso';
        } else if (profileType === 'inteligente') {
            profileName = 'Inteligente';
        } else if (profileType === 'sabio') {
            profileName = 'Sábio';
        }
        
        if (maxCities === 0) {
            messageSpan.textContent = `Seu perfil (${profileName}) não permite selecionar cidades. Para selecionar cidades, é necessário atualizar seu perfil.`;
        } else {
            const cityText = maxCities === 1 ? 'cidade' : 'cidades';
            messageSpan.textContent = `Você atingiu o limite máximo de ${maxCities} ${cityText} permitido para o seu perfil (${profileName}). Remova uma cidade antes de selecionar outra.`;
        }
        
        warningDiv.classList.remove('d-none');
        
        // Scroll para o aviso
        warningDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Animação de shake
        warningDiv.style.animation = 'shake 0.5s';
        setTimeout(() => {
            warningDiv.style.animation = '';
        }, 500);
    }
}

// Função para ocultar aviso de limite de cidades
function hideCityLimitWarning() {
    const warningDiv = document.getElementById('city_limit_warning');
    if (warningDiv) {
        const selectedCount = document.querySelectorAll('input[name="promotion_cities[]"]:checked').length;
        const maxCities = parseInt(document.getElementById('max_cities_limit')?.textContent || '0');
        
        // Só ocultar se estiver abaixo do limite
        if (selectedCount < maxCities || maxCities === 0) {
            warningDiv.classList.add('d-none');
        }
    }
}

// Busca de cidades
document.getElementById('city_search')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const cityItems = document.querySelectorAll('.city-item');
    const noCitiesFound = document.getElementById('no_cities_found');
    let visibleCount = 0;

    cityItems.forEach(item => {
        const cityName = item.getAttribute('data-city') || '';
        const cityCard = item.querySelector('.city-card');
        const cityNameElement = cityCard ? cityCard.querySelector('.city-name') : null;
        const labelText = cityNameElement ? cityNameElement.textContent.toLowerCase() : '';

        if (searchTerm === '' || cityName.includes(searchTerm) || labelText.includes(searchTerm)) {
            item.classList.remove('hidden');
            item.style.display = '';
            visibleCount++;
        } else {
            item.classList.add('hidden');
            item.style.display = 'none';
        }
    });

    // Mostrar mensagem "nenhuma cidade encontrada"
    if (noCitiesFound) {
        if (visibleCount === 0 && searchTerm !== '') {
            noCitiesFound.classList.remove('d-none');
        } else {
            noCitiesFound.classList.add('d-none');
        }
    }
});

// Inicializar tags quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    const checkedCities = document.querySelectorAll('input[name="promotion_cities[]"]:checked');
    checkedCities.forEach(checkbox => {
        addCityTag(checkbox.value);
    });
});

// Toggle auction options
document.getElementById('is_auction')?.addEventListener('change', function() {
    const auctionOptions = document.getElementById('auction_options');
    const minimumPrice = document.getElementById('minimum_price');
    const auctionDay = document.getElementById('auction_day');
    const auctionStartHour = document.getElementById('auction_start_hour');
    const auctionDurationMinutes = document.getElementById('auction_duration_minutes');
    
    if (this.checked) {
        auctionOptions.classList.remove('d-none');
        if (minimumPrice) {
            minimumPrice.required = true;
            minimumPrice.removeAttribute('disabled');
        }
        if (auctionDay) {
            auctionDay.required = true;
            auctionDay.removeAttribute('disabled');
            // Atualizar limite máximo do dia do leilão quando habilitado
            const endDate = document.getElementById('end_date')?.value;
            if (endDate) {
                updateAuctionDayMax(endDate);
            }
        }
        if (auctionStartHour) {
            auctionStartHour.required = true;
            auctionStartHour.removeAttribute('disabled');
        }
        if (auctionDurationMinutes) {
            auctionDurationMinutes.required = true;
            auctionDurationMinutes.removeAttribute('disabled');
        }
    } else {
        auctionOptions.classList.add('d-none');
        if (minimumPrice) {
            minimumPrice.required = false;
            minimumPrice.value = '';
            minimumPrice.setAttribute('disabled', 'disabled');
        }
        if (auctionDay) {
            auctionDay.required = false;
            auctionDay.value = '';
            auctionDay.setAttribute('disabled', 'disabled');
        }
        if (auctionStartHour) {
            auctionStartHour.required = false;
            auctionStartHour.value = '';
            auctionStartHour.setAttribute('disabled', 'disabled');
        }
        if (auctionDurationMinutes) {
            auctionDurationMinutes.required = false;
            auctionDurationMinutes.value = '';
            auctionDurationMinutes.setAttribute('disabled', 'disabled');
        }
    }
});

// Initialize step indicator
updateStepIndicator();

// Load quota data on page load if already selected
document.addEventListener('DOMContentLoaded', function() {
    const quotaSelect = document.getElementById('quota_id');
    if (quotaSelect && quotaSelect.value) {
        quotaSelect.dispatchEvent(new Event('change'));
    }
});

// Helper function to check if element is visible
function isElementVisible(element) {
    if (!element || element.offsetParent === null) {
        return false;
    }
    
    let current = element;
    while (current && current !== document.body) {
        const style = window.getComputedStyle(current);
        if (style.display === 'none' || style.visibility === 'hidden' || current.classList.contains('d-none')) {
            return false;
        }
        current = current.parentElement;
    }
    
    return true;
}

// Form submission
document.getElementById('offerForm').addEventListener('submit', function(e) {
    // If auction is not checked, clear auction fields to prevent validation errors
    const isAuctionChecked = document.getElementById('is_auction')?.checked;
    if (!isAuctionChecked) {
        const minimumPrice = document.getElementById('minimum_price');
        const auctionDay = document.getElementById('auction_day');
        const auctionStartHour = document.getElementById('auction_start_hour');
        const auctionDurationMinutes = document.getElementById('auction_duration_minutes');
        
        if (minimumPrice) {
            minimumPrice.value = '';
            minimumPrice.removeAttribute('required');
        }
        if (auctionDay) {
            auctionDay.value = '';
            auctionDay.removeAttribute('required');
        }
        if (auctionStartHour) {
            auctionStartHour.value = '';
            auctionStartHour.removeAttribute('required');
        }
        if (auctionDurationMinutes) {
            auctionDurationMinutes.value = '';
            auctionDurationMinutes.removeAttribute('required');
        }
    }
    
    // Remove required attribute from hidden fields to prevent HTML5 validation blocking
    const allRequiredFields = this.querySelectorAll('[required]');
    allRequiredFields.forEach(field => {
        if (!isElementVisible(field)) {
            field.removeAttribute('required');
            // Store original state to restore if needed
            field.dataset.wasRequired = 'true';
        }
    });
    
    // Validate all visible required fields manually
    let allStepsValid = true;
    let firstInvalidField = null;
    
    for (let step = 1; step <= totalSteps; step++) {
        const stepElement = document.getElementById(`step${step}`);
        if (stepElement) {
            const requiredFields = stepElement.querySelectorAll('[required]');
            for (let field of requiredFields) {
                if (isElementVisible(field)) {
                    const isValid = field.type === 'checkbox' ? field.checked : field.value.trim() !== '';
                    if (!isValid) {
                        field.classList.add('is-invalid');
                        allStepsValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = field;
                        }
                    } else {
                        field.classList.remove('is-invalid');
                    }
                }
            }
        }
    }
    
    if (!allStepsValid) {
        e.preventDefault();
        // Restore required attributes
        allRequiredFields.forEach(field => {
            if (field.dataset.wasRequired === 'true') {
                field.setAttribute('required', 'required');
                delete field.dataset.wasRequired;
            }
        });
        
        // Show step with first invalid field
        if (firstInvalidField) {
            for (let step = 1; step <= totalSteps; step++) {
                const stepElement = document.getElementById(`step${step}`);
                if (stepElement && stepElement.contains(firstInvalidField)) {
                    document.querySelectorAll('.step').forEach(s => s.classList.add('d-none'));
                    stepElement.classList.remove('d-none');
                    currentStep = step;
                    updateStepIndicator();
                    setTimeout(() => {
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidField.focus();
                    }, 100);
                    break;
                }
            }
        }
        alert('Por favor, preencha todos os campos obrigatórios.');
        return false;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Publicando...';
        submitBtn.disabled = true;
    }
    
    return true;
});
</script>
@endpush
@endsection