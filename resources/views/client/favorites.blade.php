@extends('layouts.app')

@section('title', 'Favoritos - Cota Brasilis')

@section('content')
<style>
    .favorites-hero {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(10, 82, 52, 0.95));
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 0 24px 65px rgba(5, 74, 40, 0.25);
        position: relative;
        overflow: hidden;
        margin-bottom: 3rem;
    }

    .favorites-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent 70%);
        border-radius: 50%;
    }

    .favorites-hero-content {
        position: relative;
        z-index: 1;
    }

    .favorites-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .favorites-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .favorites-hero p {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.95);
        margin: 0;
        line-height: 1.6;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .section-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .section-icon-wrapper.primary {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
        color: #3b82f6;
    }

    .section-icon-wrapper.success {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.15), rgba(4, 64, 52, 0.15));
        color: #009739;
    }

    .section-icon-wrapper.info {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(8, 145, 178, 0.15));
        color: #06b6d4;
    }

    .section-header h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .list-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .list-card:hover {
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.12);
        transform: translateY(-4px);
    }

    .list-card-header {
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
    }

    .list-card-header.primary {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05));
    }

    .list-card-header.success {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.1), rgba(4, 64, 52, 0.05));
    }

    .list-card-header.info {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(8, 145, 178, 0.05));
    }

    .list-card-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .list-card-badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .list-card-badge.primary {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .list-card-badge.success {
        background: linear-gradient(135deg, #009739, #044034);
        color: white;
    }

    .list-card-badge.info {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
    }

    .list-card-body {
        padding: 2rem;
    }

    .quota-card-modern {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .quota-card-modern:hover {
        border-color: #009739;
        box-shadow: 0 8px 24px rgba(0, 151, 57, 0.15);
        transform: translateY(-4px);
    }

    .quota-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .quota-card-title {
        flex: 1;
    }

    .quota-card-title h6 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
    }

    .quota-location-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .quota-location-badge.primary {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .quota-location-badge.success {
        background: rgba(0, 151, 57, 0.1);
        color: #009739;
    }

    .quota-location-badge.info {
        background: rgba(6, 182, 212, 0.1);
        color: #06b6d4;
    }

    .quota-remove-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid #fee2e2;
        background: #fef2f2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .quota-remove-btn:hover {
        background: #fee2e2;
        border-color: #fecaca;
        transform: scale(1.1);
    }

    .quota-info-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1.5rem 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .quota-info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: #64748b;
    }

    .quota-info-item i {
        width: 20px;
        text-align: center;
        font-size: 1rem;
    }

    .quota-info-item.primary i {
        color: #3b82f6;
    }

    .quota-info-item.success i {
        color: #009739;
    }

    .quota-info-item.info i {
        color: #06b6d4;
    }

    .quota-card-footer {
        margin-top: auto;
    }

    .btn-view-details {
        width: 100%;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        background: linear-gradient(135deg, #009739, #044034);
        color: white;
        border: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-view-details:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 151, 57, 0.3);
        color: white;
    }

    .empty-state {
        background: white;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.1), rgba(4, 64, 52, 0.1));
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 3rem;
        color: #009739;
    }

    .empty-state h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .empty-state p {
        font-size: 1.05rem;
        color: #64748b;
        max-width: 540px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }

    .btn-add-favorite {
        padding: 1rem 2.5rem;
        border-radius: 14px;
        font-weight: 700;
        font-size: 1.05rem;
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: #000;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 8px 24px rgba(251, 191, 36, 0.4);
    }

    .btn-add-favorite:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(251, 191, 36, 0.5);
        color: #000;
    }

    @media (max-width: 768px) {
        .favorites-hero {
            padding: 2rem 1.5rem;
        }

        .favorites-hero h1 {
            font-size: 2rem;
        }

        .section-header h3 {
            font-size: 1.5rem;
        }
    }
</style>

<section class="mb-5">
    <div class="favorites-hero">
        <div class="favorites-hero-content">
            <span class="favorites-badge">
                <i class="fas fa-heart"></i>Favoritos
            </span>
            <h1>Meus favoritos</h1>
            <p>Cotas e Frações por cidade ou hotel para facilitar busca e usufruto</p>
        </div>
    </div>
</section>

<div class="container">
    @php
        $hasAnyFavorites = false;
        foreach(['rental', 'purchase', 'exchange'] as $transType) {
            if ($favoriteLists->has($transType)) {
                foreach(['city', 'hotel', 'state'] as $listType) {
                    if ($favoriteLists->get($transType)->has($listType) && $favoriteLists->get($transType)->get($listType)->isNotEmpty()) {
                        $hasAnyFavorites = true;
                        break 2;
                    }
                }
            }
        }
    @endphp
    
    @if(!$hasAnyFavorites)
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-heart"></i>
            </div>
            <h3>Ainda não há favoritos</h3>
            <p>Ao encontrar uma cota imperdível, clique no ícone de coração para salvar. As cotas serão organizadas automaticamente por tipo de transação (Alugar, Comprar, Troca) e depois por cidade, hotel ou estado.</p>
            <a href="{{ route('quotas.index') }}" class="btn-add-favorite">
                <i class="fas fa-heart"></i>Adicionar meu primeiro favorito
            </a>
        </div>
    @else
        @php
            $transactionTypes = [
                'rental' => ['title' => 'Alugar', 'icon' => 'fa-calendar-check', 'color' => 'primary'],
                'purchase' => ['title' => 'Comprar', 'icon' => 'fa-shopping-cart', 'color' => 'success'],
                'exchange' => ['title' => 'Troca', 'icon' => 'fa-exchange-alt', 'color' => 'info']
            ];
            $listTypes = [
                'state' => ['title' => 'Por Estado', 'icon' => 'fa-map', 'color' => 'info'],
                'city' => ['title' => 'Por Cidade', 'icon' => 'fa-map-marker-alt', 'color' => 'primary'],
                'hotel' => ['title' => 'Por Hotel', 'icon' => 'fa-hotel', 'color' => 'success']
            ];
        @endphp
        
        @foreach($transactionTypes as $transType => $transInfo)
            @if($favoriteLists->has($transType))
                @php
                    $transactionLists = $favoriteLists->get($transType);
                    $hasTransactionLists = false;
                    foreach($listTypes as $listType => $listInfo) {
                        if ($transactionLists->has($listType) && $transactionLists->get($listType)->isNotEmpty()) {
                            $hasTransactionLists = true;
                            break;
                        }
                    }
                @endphp
                
                @if($hasTransactionLists)
                    <div class="mb-5">
                        <div class="section-header mb-4">
                            <div class="section-icon-wrapper {{ $transInfo['color'] }}" style="width: 64px; height: 64px;">
                                <i class="fas {{ $transInfo['icon'] }} fa-lg"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold mb-1">{{ $transInfo['title'] }}</h2>
                                <p class="text-muted mb-0">Favoritos de {{ strtolower($transInfo['title']) }}</p>
                            </div>
                        </div>
                        
                        @foreach($listTypes as $listType => $listInfo)
                            @if($transactionLists->has($listType) && $transactionLists->get($listType)->isNotEmpty())
                                <div class="mb-4">
                                    <div class="section-header">
                                        <div class="section-icon-wrapper {{ $listInfo['color'] }}">
                                            <i class="fas {{ $listInfo['icon'] }}"></i>
                                        </div>
                                        <h3>{{ $listInfo['title'] }}</h3>
                                    </div>
                                    @foreach($transactionLists->get($listType) as $list)
                                        @if($list->quotas->count() > 0)
                                        <div class="list-card">
                                            <div class="list-card-header {{ $listInfo['color'] }}">
                                                <h5 class="list-card-title">
                                                    <i class="fas {{ $listInfo['icon'] == 'fa-map-marker-alt' ? 'fa-city' : ($listInfo['icon'] == 'fa-map' ? 'fa-map' : 'fa-hotel') }}"></i>{{ $list->name }}
                                                </h5>
                                                <span class="list-card-badge {{ $listInfo['color'] }}">{{ $list->quotas->count() }} {{ $list->quotas->count() == 1 ? 'cota' : 'cotas' }}</span>
                                            </div>
                                            <div class="list-card-body">
                                                <div class="row g-4">
                                                    @foreach($list->quotas as $quota)
                                                        <div class="col-lg-6 col-xl-4">
                                                            <div class="quota-card-modern">
                                                                <div class="quota-card-header">
                                                                    <div class="quota-card-title">
                                                                        <h6>{{ $quota->hotel_name }}</h6>
                                                                        <span class="quota-location-badge {{ $listInfo['color'] }}">{{ $quota->location }}</span>
                                                                    </div>
                                                                    <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                                        @csrf
                                                                        <input type="hidden" name="list_type" value="{{ $listType }}">
                                                                        <input type="hidden" name="transaction_type" value="{{ $transType }}">
                                                                        <button type="submit" class="quota-remove-btn" title="Remover dos favoritos">
                                                                            <i class="fas fa-heart-broken"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                                <ul class="quota-info-list">
                                                                    <li class="quota-info-item {{ $listInfo['color'] }}">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                        <span>{{ optional($quota->start_date)->format('d/m/Y') }} a {{ optional($quota->end_date)->format('d/m/Y') }}</span>
                                                                    </li>
                                                                    <li class="quota-info-item {{ $listInfo['color'] }}">
                                                                        <i class="fas fa-users"></i>
                                                                        <span>{{ $quota->number_of_guests }} {{ $quota->number_of_guests == 1 ? 'hóspede' : 'hóspedes' }}</span>
                                                                    </li>
                                                                    @if($quota->rental_price)
                                                                        <li class="quota-info-item {{ $listInfo['color'] }}">
                                                                            <i class="fas fa-dollar-sign"></i>
                                                                            <span>R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                                <div class="quota-card-footer">
                                                                    <a href="{{ route('quotas.show', $quota) }}" class="btn-view-details">
                                                                        <i class="fas fa-eye"></i>Ver detalhes
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif
        @endforeach
        
        <!-- Código antigo removido - mantido apenas para referência -->
        @if(false)
        <!-- Listas por Cidade -->
        @if($favoriteLists->has('city') && $favoriteLists->get('city')->isNotEmpty())
            <div class="mb-5">
                <div class="section-header">
                    <div class="section-icon-wrapper primary">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Por Cidade</h3>
                </div>
                @foreach($favoriteLists->get('city') as $list)
                    @if($list->quotas->count() > 0)
                    <div class="list-card">
                        <div class="list-card-header primary">
                            <h5 class="list-card-title">
                                <i class="fas fa-city"></i>{{ $list->name }}
                            </h5>
                            <span class="list-card-badge primary">{{ $list->quotas->count() }} {{ $list->quotas->count() == 1 ? 'cota' : 'cotas' }}</span>
                        </div>
                        <div class="list-card-body">
                            <div class="row g-4">
                                @foreach($list->quotas as $quota)
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="quota-card-modern">
                                            <div class="quota-card-header">
                                                <div class="quota-card-title">
                                                    <h6>{{ $quota->hotel_name }}</h6>
                                                    <span class="quota-location-badge primary">{{ $quota->location }}</span>
                                                </div>
                                                <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="list_type" value="city">
                                                    <button type="submit" class="quota-remove-btn" title="Remover dos favoritos">
                                                        <i class="fas fa-heart-broken"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <ul class="quota-info-list">
                                                <li class="quota-info-item primary">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <span>{{ optional($quota->start_date)->format('d/m/Y') }} a {{ optional($quota->end_date)->format('d/m/Y') }}</span>
                                                </li>
                                                <li class="quota-info-item primary">
                                                    <i class="fas fa-users"></i>
                                                    <span>{{ $quota->number_of_guests }} {{ $quota->number_of_guests == 1 ? 'hóspede' : 'hóspedes' }}</span>
                                                </li>
                                                @if($quota->rental_price)
                                                    <li class="quota-info-item primary">
                                                        <i class="fas fa-dollar-sign"></i>
                                                        <span>R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                                                    </li>
                                                @endif
                                            </ul>
                                            <div class="quota-card-footer">
                                                <a href="{{ route('quotas.show', $quota) }}" class="btn-view-details">
                                                    <i class="fas fa-eye"></i>Ver detalhes
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Listas por Hotel -->
        @if($favoriteLists->has('hotel') && $favoriteLists->get('hotel')->isNotEmpty())
            <div class="mb-5">
                <div class="section-header">
                    <div class="section-icon-wrapper success">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <h3>Por Hotel</h3>
                </div>
                @foreach($favoriteLists->get('hotel') as $list)
                    @if($list->quotas->count() > 0)
                    <div class="list-card">
                        <div class="list-card-header success">
                            <h5 class="list-card-title">
                                <i class="fas fa-hotel"></i>{{ $list->name }}
                            </h5>
                            <span class="list-card-badge success">{{ $list->quotas->count() }} {{ $list->quotas->count() == 1 ? 'cota' : 'cotas' }}</span>
                        </div>
                        <div class="list-card-body">
                            <div class="row g-4">
                                @foreach($list->quotas as $quota)
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="quota-card-modern">
                                            <div class="quota-card-header">
                                                <div class="quota-card-title">
                                                    <h6>{{ $quota->hotel_name }}</h6>
                                                    <span class="quota-location-badge success">{{ $quota->location }}</span>
                                                </div>
                                                <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="list_type" value="hotel">
                                                    <button type="submit" class="quota-remove-btn" title="Remover dos favoritos">
                                                        <i class="fas fa-heart-broken"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <ul class="quota-info-list">
                                                <li class="quota-info-item success">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <span>{{ optional($quota->start_date)->format('d/m/Y') }} a {{ optional($quota->end_date)->format('d/m/Y') }}</span>
                                                </li>
                                                <li class="quota-info-item success">
                                                    <i class="fas fa-users"></i>
                                                    <span>{{ $quota->number_of_guests }} {{ $quota->number_of_guests == 1 ? 'hóspede' : 'hóspedes' }}</span>
                                                </li>
                                                @if($quota->rental_price)
                                                    <li class="quota-info-item success">
                                                        <i class="fas fa-dollar-sign"></i>
                                                        <span>R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                                                    </li>
                                                @endif
                                            </ul>
                                            <div class="quota-card-footer">
                                                <a href="{{ route('quotas.show', $quota) }}" class="btn-view-details">
                                                    <i class="fas fa-eye"></i>Ver detalhes
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif

        @endif
    @endif
</div>
@endsection
