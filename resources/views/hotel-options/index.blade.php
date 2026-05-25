@extends('layouts.app')

@section('title', 'Opções no meu hotel - Cota Brasilis')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp
<style>
    .hotel-options-page {
        max-width: 100%;
        margin: 0 auto;
    }

    .tabs-container {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .tabs-header {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.08), rgba(4, 64, 52, 0.08));
        padding: 0;
        border-bottom: 2px solid rgba(0, 151, 57, 0.1);
        display: flex;
        gap: 0;
    }

    .tab-button {
        flex: 1;
        padding: 1.5rem 2rem;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 700;
        font-size: 1.05rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tab-button:hover {
        background: rgba(0, 151, 57, 0.05);
        color: #009739;
    }

    .tab-button.active {
        color: #009739;
        background: rgba(0, 151, 57, 0.1);
        border-bottom-color: #009739;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: #009739;
    }

    .tab-button-subtitle {
        font-size: 0.75rem;
        font-weight: 400;
        letter-spacing: 0.2px;
        margin-top: 0.25rem;
        opacity: 0.8;
    }

    .tab-button.active .tab-button-subtitle {
        opacity: 0.9;
    }

    .tab-content {
        display: none;
        padding: 2.5rem;
        min-height: 400px;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
        .options-grid {
            grid-template-columns: 1fr;
        }
    }

    .option-card {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 20px;
        padding: 1.8rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .option-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
        border-color: rgba(0, 151, 57, 0.2);
    }

    .option-card__header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .option-card__title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .option-card__badge {
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(0, 151, 57, 0.12);
        color: #009739;
    }

    .option-card__info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .option-card__info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #64748b;
    }

    .option-card__info-item i {
        color: #009739;
        width: 18px;
    }

    .option-card__info-item--cadastro {
        color: #64748b !important;
        font-size: 0.85rem !important;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
    }

    .option-card__info-item--cadastro i {
        color: #94a3b8 !important;
    }

    .option-card__info-item--periodo {
        font-weight: 500;
    }

    .option-card__price {
        font-size: 1.5rem;
        font-weight: 800;
        color: #009739;
        margin-bottom: 1rem;
    }

    .option-card__actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-view {
        flex: 1;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
    }

    .btn-view-primary {
        background: #009739;
        color: #ffffff;
    }

    .btn-view-primary:hover {
        background: #007a2e;
        color: #ffffff;
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }

    .empty-state h4 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        font-size: 1rem;
        max-width: 500px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .tabs-header {
            flex-direction: column;
        }

        .tab-button {
            border-bottom: 1px solid rgba(0, 151, 57, 0.1);
            border-right: none;
        }

        .tab-button.active {
            border-right: 3px solid #009739;
            border-bottom: 1px solid rgba(0, 151, 57, 0.1);
        }

        .tab-content {
            padding: 1.5rem;
        }

        .options-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="hotel-options-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-2">Opções no meu hotel</h2>
            <p class="text-muted">Acesse e veja as melhores opções para aluguel,troca e compra em seu hotel.</p>
        </div>
    </div>

    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-button {{ $activeTab === 'aluguel' ? 'active' : '' }}" 
                    data-tab="aluguel" onclick="switchTab('aluguel')">
                <i class="fas fa-calendar-check me-2"></i>Alugar
            </button>
            <button class="tab-button {{ $activeTab === 'troca' ? 'active' : '' }}" 
                    data-tab="troca" onclick="switchTab('troca')">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                    <i class="fas fa-exchange-alt"></i>
                    <div>Trocar</div>
                    <div class="tab-button-subtitle">Troca Simples</div>
                </div>
            </button>
            <button class="tab-button {{ $activeTab === 'compra' ? 'active' : '' }}" 
                    data-tab="compra" onclick="switchTab('compra')">
                <i class="fas fa-shopping-cart me-2"></i>Comprar
            </button>
        </div>

        <!-- Tab Aluguel -->
        <div id="tab-aluguel" class="tab-content {{ $activeTab === 'aluguel' ? 'active' : '' }}">
            <!-- Filtros de Busca - Aluguel -->
            <div class="filters-section mb-4 p-4 bg-light rounded-3">
                <h5 class="mb-3 fw-bold"><i class="fas fa-filter me-2"></i>Filtros de Busca</h5>
                <form method="GET" action="{{ route('hotel-options.index') }}" id="filter-form-aluguel">
                    <input type="hidden" name="tab" value="aluguel">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="aluguel_month" class="form-label fw-semibold">Mês</label>
                            <select class="form-select" id="aluguel_month" name="aluguel_month">
                                <option value="">Todos</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('aluguel_month') == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(now()->year, $i, 1)->locale('pt_BR')->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_year" class="form-label fw-semibold">Ano</label>
                            <select class="form-select" id="aluguel_year" name="aluguel_year">
                                <option value="">Todos</option>
                                @php
                                    $currentYear = date('Y');
                                    $nextYear = $currentYear + 1;
                                @endphp
                                <option value="{{ $currentYear }}" {{ request('aluguel_year') == $currentYear ? 'selected' : '' }}>{{ $currentYear }}</option>
                                <option value="{{ $nextYear }}" {{ request('aluguel_year') == $nextYear ? 'selected' : '' }}>{{ $nextYear }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_period_start" class="form-label fw-semibold">Período - Início</label>
                            <input type="date" class="form-control" id="aluguel_period_start" name="aluguel_period_start" value="{{ request('aluguel_period_start') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_period_end" class="form-label fw-semibold">Período - Fim</label>
                            <input type="date" class="form-control" id="aluguel_period_end" name="aluguel_period_end" value="{{ request('aluguel_period_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_nights" class="form-label fw-semibold">Número de Pernoites</label>
                            <input type="number" min="1" class="form-control" id="aluguel_nights" name="aluguel_nights" value="{{ request('aluguel_nights') }}" placeholder="Ex: 7">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_rooms" class="form-label fw-semibold">Número de Quartos</label>
                            <input type="number" min="1" class="form-control" id="aluguel_rooms" name="aluguel_rooms" value="{{ request('aluguel_rooms') }}" placeholder="Ex: 2">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_people" class="form-label fw-semibold">Número de Pessoas</label>
                            <input type="number" min="1" class="form-control" id="aluguel_people" name="aluguel_people" value="{{ request('aluguel_people') }}" placeholder="Ex: 4">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_max_price" class="form-label fw-semibold">Preço Máximo (R$)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="aluguel_max_price" name="aluguel_max_price" value="{{ request('aluguel_max_price') }}" placeholder="Ex: 5000.00">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_city" class="form-label fw-semibold">Cidade</label>
                            <input type="text" class="form-control" id="aluguel_city" name="aluguel_city" value="{{ request('aluguel_city') }}" placeholder="Ex: Florianópolis">
                        </div>
                        <div class="col-md-3">
                            <label for="aluguel_state" class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="aluguel_state" name="aluguel_state">
                                <option value="">Todos</option>
                                @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                    <option value="{{ $uf }}" {{ request('aluguel_state') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100 me-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            <a href="{{ route('hotel-options.index', ['tab' => 'aluguel']) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @if($data['aluguel']->count() > 0)
                <div class="options-grid">
                    @foreach($data['aluguel'] as $offer)
                        <div class="option-card">
                            <div class="option-card__header">
                                <h5 class="option-card__title">{{ $offer->display_title }}</h5>
                                <span class="option-card__badge">Aluguel</span>
                            </div>
                            <div class="option-card__info">
                                @php
                                    $quota = $offer->quota;
                                @endphp
                                @if($quota && $quota->hotel_name)
                                <div class="option-card__info-item">
                                    <i class="fas fa-hotel"></i>
                                    <span><strong>Hotel:</strong> {{ $quota->hotel_name }}</span>
                                </div>
                                @endif
                                @if($quota && $quota->location)
                                <div class="option-card__info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Localização:</strong> {{ $quota->location }}</span>
                                </div>
                                @elseif($offer->city)
                                <div class="option-card__info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $offer->city }}{{ $offer->state ? ', ' . $offer->state : '' }}</span>
                                </div>
                                @endif
                                @if($offer->number_of_people)
                                <div class="option-card__info-item">
                                    <i class="fas fa-users"></i>
                                    <span>{{ $offer->number_of_people }} {{ $offer->number_of_people == 1 ? 'pessoa' : 'pessoas' }}</span>
                                </div>
                                @endif
                                @if($offer->start_date && $offer->end_date)
                                <div class="option-card__info-item option-card__info-item--periodo">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><strong>Período:</strong> 
                                        @php
                                            try {
                                                $startDate = $offer->start_date instanceof \Carbon\Carbon ? $offer->start_date : \Carbon\Carbon::parse($offer->start_date);
                                                $endDate = $offer->end_date instanceof \Carbon\Carbon ? $offer->end_date : \Carbon\Carbon::parse($offer->end_date);
                                                echo $startDate->format('d/m/Y') . ' até ' . $endDate->format('d/m/Y');
                                            } catch (\Exception $e) {
                                                echo 'Não informado';
                                            }
                                        @endphp
                                    </span>
                                </div>
                                @endif
                                @if($offer->created_at)
                                <div class="option-card__info-item option-card__info-item--cadastro">
                                    <i class="fas fa-clock"></i>
                                    <span><strong>Cadastrado em:</strong> 
                                        @php
                                            try {
                                                $createdAt = $offer->created_at instanceof \Carbon\Carbon ? $offer->created_at : \Carbon\Carbon::parse($offer->created_at);
                                                echo $createdAt->format('d/m/Y H:i');
                                            } catch (\Exception $e) {
                                                echo 'Não informado';
                                            }
                                        @endphp
                                    </span>
                                </div>
                                @endif
                            </div>
                            @if($offer->price)
                            <div class="option-card__price">R$ {{ number_format($offer->price, 2, ',', '.') }}</div>
                            @endif
                            <div class="option-card__actions">
                                <a href="{{ route('rental-offers.show', $offer->id) }}" class="btn btn-success w-100">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                            </div>
                        </div>
                @endforeach
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                    <div>
                        {{ $data['aluguel']->links('vendor.pagination.modern') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h4>Nenhuma oferta de aluguel encontrada</h4>
                    <p style="color: #000;">Verifique se cada um dos campos estão <b>corretamente</b> preenchidos.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            @endif
        </div>

        <!-- Tab Troca -->
        <div id="tab-troca" class="tab-content {{ $activeTab === 'troca' ? 'active' : '' }}">
            <!-- Filtros de Busca - Troca -->
            <div class="filters-section mb-4 p-4 bg-light rounded-3">
                <h5 class="mb-3 fw-bold"><i class="fas fa-filter me-2"></i>Filtros de Busca</h5>
                <form method="GET" action="{{ route('hotel-options.index') }}" id="filter-form-troca">
                    <input type="hidden" name="tab" value="troca">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="troca_month" class="form-label fw-semibold">Mês</label>
                            <select class="form-select" id="troca_month" name="troca_month">
                                <option value="">Todos</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('troca_month') == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(now()->year, $i, 1)->locale('pt_BR')->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="troca_year" class="form-label fw-semibold">Ano</label>
                            <select class="form-select" id="troca_year" name="troca_year">
                                <option value="">Todos</option>
                                @php
                                    $currentYear = date('Y');
                                    $nextYear = $currentYear + 1;
                                @endphp
                                <option value="{{ $currentYear }}" {{ request('troca_year') == $currentYear ? 'selected' : '' }}>{{ $currentYear }}</option>
                                <option value="{{ $nextYear }}" {{ request('troca_year') == $nextYear ? 'selected' : '' }}>{{ $nextYear }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="troca_period_start" class="form-label fw-semibold">Período - Início</label>
                            <input type="date" class="form-control" id="troca_period_start" name="troca_period_start" value="{{ request('troca_period_start') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="troca_period_end" class="form-label fw-semibold">Período - Fim</label>
                            <input type="date" class="form-control" id="troca_period_end" name="troca_period_end" value="{{ request('troca_period_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="troca_nights" class="form-label fw-semibold">Número de Pernoites</label>
                            <input type="number" min="1" class="form-control" id="troca_nights" name="troca_nights" value="{{ request('troca_nights') }}" placeholder="Ex: 7">
                        </div>
                        <div class="col-md-3">
                            <label for="troca_rooms" class="form-label fw-semibold">Número de Quartos</label>
                            <input type="number" min="1" class="form-control" id="troca_rooms" name="troca_rooms" value="{{ request('troca_rooms') }}" placeholder="Ex: 2">
                        </div>
                        <div class="col-md-3">
                            <label for="troca_people" class="form-label fw-semibold">Número de Pessoas</label>
                            <input type="number" min="1" class="form-control" id="troca_people" name="troca_people" value="{{ request('troca_people') }}" placeholder="Ex: 4">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100 h-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($data['troca']->count() > 0)
                <div class="options-grid">
                    @foreach($data['troca'] as $offer)
                        <div class="option-card">
                            <div class="option-card__header">
                                <h5 class="option-card__title">
                                    Troca de Cota - {{ $offer->quota ? $offer->quota->hotel_name : ($offer->desired_hotels_labels !== '' ? $offer->desired_hotels_labels : ($offer->desired_hotel ?? 'Hotel não informado')) }}
                                </h5>
                                <span class="option-card__badge option-card__badge--troca">Troca</span>
                            </div>
                            <div class="option-card__info">
                                @if($offer->quota && $offer->quota->hotel_name)
                                <div class="option-card__info-item">
                                    <i class="fas fa-hotel"></i>
                                    <span><strong>Hotel:</strong> {{ $offer->quota->hotel_name }}</span>
                                </div>
                                @endif
                                @if($offer->quota && $offer->quota->location)
                                <div class="option-card__info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Localização:</strong> {{ $offer->quota->location }}</span>
                                </div>
                                @elseif($offer->desired_city)
                                <div class="option-card__info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $offer->desired_city }}{{ $offer->desired_state ? ', ' . $offer->desired_state : '' }}</span>
                                </div>
                                @endif
                                @if($offer->desired_people)
                                <div class="option-card__info-item">
                                    <i class="fas fa-users"></i>
                                    <span>{{ $offer->desired_people }} {{ $offer->desired_people == 1 ? 'pessoa' : 'pessoas' }}</span>
                                </div>
                                @endif
                                @if($offer->desired_period_start && $offer->desired_period_end)
                                <div class="option-card__info-item option-card__info-item--periodo">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><strong>Período:</strong> 
                                        @php
                                            try {
                                                $startDate = $offer->desired_period_start instanceof \Carbon\Carbon ? $offer->desired_period_start : \Carbon\Carbon::parse($offer->desired_period_start);
                                                $endDate = $offer->desired_period_end instanceof \Carbon\Carbon ? $offer->desired_period_end : \Carbon\Carbon::parse($offer->desired_period_end);
                                                echo $startDate->format('d/m/Y') . ' até ' . $endDate->format('d/m/Y');
                                            } catch (\Exception $e) {
                                                echo 'Não informado';
                                            }
                                        @endphp
                                    </span>
                                </div>
                                @endif
                                @if($offer->created_at)
                                <div class="option-card__info-item option-card__info-item--cadastro">
                                    <i class="fas fa-clock"></i>
                                    <span><strong>Cadastrado em:</strong> 
                                        @php
                                            try {
                                                $createdAt = $offer->created_at instanceof \Carbon\Carbon ? $offer->created_at : \Carbon\Carbon::parse($offer->created_at);
                                                echo $createdAt->format('d/m/Y H:i');
                                            } catch (\Exception $e) {
                                                echo 'Não informado';
                                            }
                                        @endphp
                                    </span>
                                </div>
                                @endif
                            </div>
                            <div class="option-card__actions">
                                <a href="{{ route('exchanges.show', $offer->id) }}" class="btn btn-success w-100">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                    <div>
                        {{ $data['troca']->links('vendor.pagination.modern') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h4>Nenhuma oferta de troca encontrada</h4>
                    <p>Não há ofertas de troca disponíveis nos hotéis onde você possui cotas no momento.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            @endif
        </div>

        <!-- Tab Compra -->
        <div id="tab-compra" class="tab-content {{ $activeTab === 'compra' ? 'active' : '' }}">
            <!-- Filtros de Busca - Compra -->
            <div class="filters-section mb-4 p-4 bg-light rounded-3">
                <h5 class="mb-3 fw-bold"><i class="fas fa-filter me-2"></i>Filtros de Busca</h5>
                <form method="GET" action="{{ route('hotel-options.index') }}" id="filter-form-compra">
                    <input type="hidden" name="tab" value="compra">
                    @php
                        $compraQuotaType = request('compra_quota_type');
                        $showFixaFields = ($compraQuotaType == 'fixa' || $compraQuotaType == 'fixa_flexivel');
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="compra_quota_type" class="form-label fw-semibold">Tipo da Cota</label>
                            <select class="form-select" id="compra_quota_type" name="compra_quota_type">
                                <option value="">Todos</option>
                                <option value="fixa" {{ request('compra_quota_type') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                                <option value="flexivel" {{ request('compra_quota_type') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                                <option value="fixa_flexivel" {{ request('compra_quota_type') == 'fixa_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                            </select>
                        </div>
                        <!-- Campos para Cota Fixa: Dia de início, Dia de término e Mês -->
                        <!-- Também exibidos para "Fixa + Flexível" -->
                        <div class="col-md-3 compra-fixa-only-fields" style="display: {{ $showFixaFields ? 'block' : 'none' }};">
                            <label for="compra_day_start" class="form-label fw-semibold">Dia de Início</label>
                            <select class="form-select" id="compra_day_start" name="compra_day_start">
                                <option value="">Selecione</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ request('compra_day_start') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 compra-fixa-only-fields" style="display: {{ $showFixaFields ? 'block' : 'none' }};">
                            <label for="compra_day_end" class="form-label fw-semibold">Dia de Término</label>
                            <select class="form-select" id="compra_day_end" name="compra_day_end">
                                <option value="">Selecione</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ request('compra_day_end') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 compra-fixa-only-fields" style="display: {{ $showFixaFields ? 'block' : 'none' }};">
                            <label for="compra_month" class="form-label fw-semibold">Mês</label>
                            <select class="form-select" id="compra_month" name="compra_month">
                                <option value="">Selecione</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('compra_month') == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(now()->year, $i, 1)->locale('pt_BR')->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <!-- Campos para Cota Fixa + Flexível: Data de início e Data de término -->
                        <div class="col-md-3 compra-fixa-flexivel-fields" style="display: {{ request('compra_quota_type') == 'fixa_flexivel' ? 'block' : 'none' }};">
                            <label for="compra_period_start" class="form-label fw-semibold">Data de Início</label>
                            <input type="date" class="form-control" id="compra_period_start" name="compra_period_start" value="{{ request('compra_period_start') }}">
                        </div>
                        <div class="col-md-3 compra-fixa-flexivel-fields" style="display: {{ request('compra_quota_type') == 'fixa_flexivel' ? 'block' : 'none' }};">
                            <label for="compra_period_end" class="form-label fw-semibold">Data de Término</label>
                            <input type="date" class="form-control" id="compra_period_end" name="compra_period_end" value="{{ request('compra_period_end') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="compra_rooms" class="form-label fw-semibold">Número de Quartos</label>
                            <input type="number" min="1" class="form-control" id="compra_rooms" name="compra_rooms" value="{{ request('compra_rooms') }}" placeholder="Ex: 2">
                        </div>
                        <div class="col-md-3">
                            <label for="compra_people" class="form-label fw-semibold">Número de Pessoas</label>
                            <input type="number" min="1" class="form-control" id="compra_people" name="compra_people" value="{{ request('compra_people') }}" placeholder="Ex: 4">
                        </div>
                        <div class="col-md-3">
                            <label for="compra_days" class="form-label fw-semibold">Número de Dias da Cota</label>
                            <select class="form-select" id="compra_days" name="compra_days">
                                <option value="">Todos</option>
                                <option value="7" {{ request('compra_days') == '7' ? 'selected' : '' }}>7 dias</option>
                                <option value="14" {{ request('compra_days') == '14' ? 'selected' : '' }}>14 dias</option>
                                <option value="21" {{ request('compra_days') == '21' ? 'selected' : '' }}>21 dias</option>
                                <option value="28" {{ request('compra_days') == '28' ? 'selected' : '' }}>28 dias</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="compra_max_price" class="form-label fw-semibold">Preço Máximo (R$)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="compra_max_price" name="compra_max_price" value="{{ request('compra_max_price') }}" placeholder="Ex: 50000.00">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($data['compra']->count() > 0)
                <div class="options-grid">
                    @foreach($data['compra'] as $request)
                        <div class="option-card">
                            <div class="option-card__header">
                                <h5 class="option-card__title">
                                    Solicitação de Compra - {{ $request->desired_hotel ?? 'Hotel não informado' }}
                                </h5>
                                <span class="option-card__badge option-card__badge--compra">Compra</span>
                            </div>
                            <div class="option-card__info">
                                @if($request->desired_hotel)
                                <div class="option-card__info-item">
                                    <i class="fas fa-hotel"></i>
                                    <span><strong>Hotel:</strong> {{ $request->desired_hotel }}</span>
                                </div>
                                @endif
                                @if($request->desired_city)
                                <div class="option-card__info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Localização:</strong> {{ $request->desired_city }}{{ $request->desired_state ? ', ' . $request->desired_state : '' }}</span>
                                </div>
                                @endif
                                @if($request->number_of_people)
                                <div class="option-card__info-item">
                                    <i class="fas fa-users"></i>
                                    <span>{{ $request->number_of_people }} {{ $request->number_of_people == 1 ? 'pessoa' : 'pessoas' }}</span>
                                </div>
                                @endif
                                @if($request->desired_period_start && $request->desired_period_end)
                                <div class="option-card__info-item option-card__info-item--periodo">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><strong>Período:</strong> 
                                        @php
                                            try {
                                                $startDate = $request->desired_period_start instanceof \Carbon\Carbon ? $request->desired_period_start : \Carbon\Carbon::parse($request->desired_period_start);
                                                $endDate = $request->desired_period_end instanceof \Carbon\Carbon ? $request->desired_period_end : \Carbon\Carbon::parse($request->desired_period_end);
                                                echo $startDate->format('d/m/Y') . ' até ' . $endDate->format('d/m/Y');
                                            } catch (\Exception $e) {
                                                echo 'Não informado';
                                            }
                                        @endphp
                                    </span>
                                </div>
                                @endif
                                @if($request->created_at)
                                <div class="option-card__info-item option-card__info-item--cadastro">
                                    <i class="fas fa-clock"></i>
                                    <span><strong>Cadastrado em:</strong> 
                                        @php
                                            try {
                                                $createdAt = $request->created_at instanceof \Carbon\Carbon ? $request->created_at : \Carbon\Carbon::parse($request->created_at);
                                                echo $createdAt->format('d/m/Y H:i');
                                            } catch (\Exception $e) {
                                                echo 'Não informado';
                                            }
                                        @endphp
                                    </span>
                                </div>
                                @endif
                            </div>
                            @if($request->max_price)
                            <div class="option-card__price">R$ {{ number_format($request->max_price, 2, ',', '.') }}</div>
                            @endif
                            <div class="option-card__actions">
                                <a href="{{ route('purchases.show', $request->id) }}" class="btn btn-success w-100">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                    <div>
                        {{ $data['compra']->links('vendor.pagination.modern') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h4>Nenhuma solicitação de compra encontrada</h4>
                    <p>Não há solicitações de compra disponíveis nos hotéis onde você possui cotas no momento.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Remover active de todas as tabs
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Adicionar active na tab clicada
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`tab-${tabName}`).classList.add('active');
    
    // Atualizar URL e recarregar a página para aplicar filtros corretos
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    
    // Remover filtros de outras tabs, mantendo apenas os da tab atual
    const currentTab = tabName;
    const filterPrefixes = ['aluguel_', 'troca_', 'compra_'];
    
    filterPrefixes.forEach(prefix => {
        if (!prefix.startsWith(currentTab + '_')) {
            // Remover parâmetros de outras tabs
            const paramsToRemove = [];
            url.searchParams.forEach((value, key) => {
                if (key.startsWith(prefix)) {
                    paramsToRemove.push(key);
                }
            });
            paramsToRemove.forEach(key => url.searchParams.delete(key));
        }
    });
    
    // Redirecionar para a nova URL
    window.location.href = url.toString();
}

// Controlar exibição dos campos de data e mês para cotas fixas
document.addEventListener('DOMContentLoaded', function() {
    // Tab Compra
    const compraQuotaType = document.getElementById('compra_quota_type');
    const compraFixaOnlyFields = document.querySelectorAll('.compra-fixa-only-fields');
    const compraFixaFlexivelFields = document.querySelectorAll('.compra-fixa-flexivel-fields');
    const compraPeriodStart = document.getElementById('compra_period_start');
    const compraPeriodEnd = document.getElementById('compra_period_end');
    
    if (compraQuotaType) {
        function toggleCompraFixaFields() {
            const value = compraQuotaType.value;
            
            // Campos para cota fixa (dia início, dia término, mês)
            // Também exibir quando "Fixa + Flexível" for selecionado
            compraFixaOnlyFields.forEach(field => {
                field.style.display = (value === 'fixa' || value === 'fixa_flexivel') ? 'block' : 'none';
            });
            
            // Campos para cota fixa + flexível (data início, data término)
            // Não exibir mais, pois usamos os campos da cota fixa
            compraFixaFlexivelFields.forEach(field => {
                field.style.display = 'none';
            });
        }
        
        compraQuotaType.addEventListener('change', toggleCompraFixaFields);
        toggleCompraFixaFields(); // Executar na carga da página
    }
    
    // Calcular data de término automaticamente para Compra
    if (compraPeriodStart && compraPeriodEnd) {
        compraPeriodStart.addEventListener('change', function() {
            if (this.value) {
                const startDate = new Date(this.value);
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6); // 7 dias = adiciona 6 dias (dia inicial + 6 dias seguintes)
                
                // Formatar data para YYYY-MM-DD
                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}`;
                
                compraPeriodEnd.value = formattedDate;
            } else {
                compraPeriodEnd.value = '';
            }
        });
        
        // Habilitar campo antes de enviar formulário
        const compraForm = document.getElementById('filter-form-compra');
        if (compraForm) {
            compraForm.addEventListener('submit', function() {
                compraPeriodEnd.disabled = false;
            });
        }
    }
});
</script>
@endsection

