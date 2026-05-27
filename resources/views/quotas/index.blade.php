@extends('layouts.app')

@section('title', 'Buscar Cotas ou Frações')

@section('content')
@php
    use Illuminate\Support\Str;

    $isPaginator = $quotas instanceof \Illuminate\Contracts\Pagination\Paginator;
    $quotasCollection = $isPaginator ? collect($quotas->items()) : collect($quotas);
    $totalQuotas = $isPaginator && method_exists($quotas, 'total') ? $quotas->total() : $quotasCollection->count();
    $exchangeCount = $quotasCollection->where('is_exchange', true)->count();
    $txAvg = request('transaction_type', 'rent');
    if ($txAvg === 'rental') {
        $txAvg = 'rent';
    }
    if ($txAvg === 'purchase') {
        $txAvg = 'buy';
    }
    $averageTicket = $txAvg === 'exchange'
        ? null
        : $quotasCollection
            ->map(fn ($quota) => $quota->getMarketplaceListPrice($txAvg))
            ->filter(fn ($p) => $p !== null && (float) $p > 0)
            ->avg();
    $favoriteIds = isset($favoriteIds) ? $favoriteIds : collect();
    $wishlistIds = isset($wishlistIds) ? collect($wishlistIds) : collect(session('user_wishlist', []));
    $amenityOptions = [
        'pool' => 'Piscina',
        'spa' => 'Spa',
        'gym' => 'Academia',
        'parking' => 'Estacionamento',
        'wifi' => 'Wi-Fi Premium',
        'seaview' => 'Vista para o mar',
        'petfriendly' => 'Pet friendly',
        'kids' => 'Área kids',
        'business' => 'Business center',
    ];
    $selectedAmenities = collect(request('amenities', []));
@endphp


<section class="bg-light py-5">
    <div class="container">
        <!-- Card Opções no meu hotel -->
        <article class="hotel-options-card mb-4">
            <div class="hotel-options-content">
                <div class="hotel-options-text">
                    <h3 class="hotel-options-title text-white">Opções no meu hotel</h3>
                    <p class="hotel-options-subtitle">Acesse e veja as melhores opções para 
                        <span style="background:#fbbf24; color:#000000; padding: 4px 12px; border-radius: 10px; display: inline-block;"><b>Aluguel</b>,<b>Troca</b> e <b>Compra</b>  em seu hotel.</span></p>
                </div>
                <a href="{{ route('hotel-options.index') }}" class="hotel-options-button">
                    Visualizar
                </a>
            </div>
        </article>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        @if(request('exchange_refine'))
                            <h4 class="fw-semibold mb-1">Refine sua busca por Frações para Troca</h4>
                            <p class="text-muted mb-0">Aplique filtros para encontrar somente frações liberadas para <strong>troca</strong> no fracionamento.</p>
                        @elseif(request('purchase_refine'))
                            <h4 class="fw-semibold mb-1">Refine sua busca por Cotas ou Frações para Compra</h4>
                            <p class="text-muted mb-0">Aplique filtros para encontrar somente cotas ou frações liberadas para <strong>compra</strong>.</p>
                        @else
                            <h4 class="fw-semibold mb-1">Refine sua busca por Cotas ou frações</h4>
                            <p class="text-muted mb-0">Aplique filtros combinados para encontrar a cota ou fração ideal para alugar com rapidez.</p>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="fas fa-arrow-left me-2"></i>Voltar ao painel
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2">
                            <i class="fas fa-compass me-1"></i>Busca inteligente
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary fw-semibold px-3 py-2">
                            <i class="fas fa-bolt me-1"></i>Atualização instantânea
                        </span>
                    </div>
                </div>

                <!-- Filters Form -->
                <form method="GET" action="{{ route('quotas.index') }}">
                    {{-- Flag para indicar que o usuário clicou em "Buscar cota ou fração ideal",
                         mesmo que nenhum filtro tenha sido preenchido --}}
                    <input type="hidden" name="search" value="1">
                    @if(request('hide_buttons'))
                        <input type="hidden" name="hide_buttons" value="1">
                    @endif
                    @if(request('transaction_type'))
                        <input type="hidden" name="transaction_type" value="{{ request('transaction_type') }}">
                    @endif
                    @if(request('exchange_refine'))
                        <input type="hidden" name="exchange_refine" value="1">
                    @endif
                    @if(request('purchase_refine'))
                        <input type="hidden" name="purchase_refine" value="1">
                    @endif
                    @include('quotas.partials.filters')
                </form>
            </div>
        </div>

        <!-- Results -->
        @if(request()->hasAny(['search', 'hotel_name', 'city', 'state', 'people', 'check_in', 'check_out', 'month', 'year', 'rooms', 'stay_duration', 'seasonality', 'quota_type', 'price_min', 'price_max', 'hidromassagem', 'academia', 'estacionamento_gratuito', 'vista_mar', 'lareira', 'adega', 'area_kids', 'area_trabalho', 'spa', 'piscina', 'wifi', 'breakfast', 'sofa_mais', 'transaction_type']))
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="fw-semibold mb-1">Resultados da sua pesquisa</h4>
                <span class="text-muted">Encontramos <strong>{{ $quotas->count() }}</strong> cotas ou frações alinhadas aos filtros selecionados.</span>
        </div>
            <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar ao painel
                    </a>
                </div>
            </div>

            @if($quotas->count() > 0)
            <div class="row g-4">
                    @foreach($quotas as $quota)
                    <div class="col-lg-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden quota-card">
                                @if(isset($quota->badge))
                                <div class="position-absolute top-0 end-0 p-3">
                                    <span class="badge bg-{{ $quota->badge_color }} text-white px-3 py-2 rounded-pill">
                                        <i class="fas fa-{{ $quota->badge == 'Nova' ? 'sparkles' : 'star' }} me-1"></i>{{ $quota->badge }}
                                    </span>
                                </div>
                                @endif
                                
                            @php
                                // Buscar imagem do hotel
                                $hotel = \App\Models\Hotel::where('name', $quota->hotel_name)->first();
                                $hotelImage = null;
                                if ($hotel && $hotel->images && count($hotel->images) > 0) {
                                    $hotelImage = asset('storage/' . $hotel->images[0]);
                                }
                            @endphp
                            <div class="ratio ratio-16x9 bg-success d-flex align-items-center justify-content-center position-relative overflow-hidden" style="background: @if($hotelImage) url('{{ $hotelImage }}') center center / cover; @else linear-gradient(135deg, #009739, #007a2e); @endif">
                                @if($hotelImage)
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 151, 57, 0.7); z-index: 1;"></div>
                                @endif
                                <div class="text-center text-white position-relative" style="z-index: 2;">
                                    @if(!$hotelImage)
                                        <i class="fas fa-suitcase-rolling fa-2x mb-2"></i>
                                        <p class="mb-0 fw-semibold">Experiência sob medida</p>
                                    @endif
                                </div>
                            </div>
                                
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                    <h5 class="fw-bold mb-1">{{ $quota->hotel_name }}</h5>
                                    <span class="badge bg-success-subtle text-success fw-semibold">{{ Str::title($quota->location) }}</span>
                                        </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @auth
                                            <form method="POST" action="{{ route('client.wishlist.toggle', $quota) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $wishlistIds->contains($quota->id) ? 'btn-warning text-white' : 'btn-outline-warning' }}" title="{{ $wishlistIds->contains($quota->id) ? 'Remover dos desejados' : 'Adicionar aos desejados' }}">
                                                    <i class="{{ $wishlistIds->contains($quota->id) ? 'fas' : 'far' }} fa-star"></i>
                                                </button>
                                            </form>
                                            @php
                                                // Determinar transaction_type baseado na rota ou request
                                                $transactionType = request('transaction_type', 'rental');
                                                // Se veio de rental-offers.request, é rental
                                                if (request()->routeIs('rental-offers.request') || request()->routeIs('rental-offers.search')) {
                                                    $transactionType = 'rental';
                                                }
                                                // Se veio de purchases ou sales, é purchase
                                                elseif (request()->routeIs('purchases.*') || request()->routeIs('sales.*')) {
                                                    $transactionType = 'purchase';
                                                }
                                                // Se veio de exchanges, é exchange
                                                elseif (request()->routeIs('exchanges.*')) {
                                                    $transactionType = 'exchange';
                                                }
                                            @endphp
                                            @if($favoriteIds->contains($quota->id))
                                                <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="list_type" value="city">
                                                    <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="Remover dos favoritos">
                                                        <i class="fas fa-heart"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown" title="Adicionar aos favoritos">
                                                        <i class="far fa-heart"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="list_type" value="city">
                                                                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-map-marker-alt me-2"></i>Por Cidade
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="list_type" value="hotel">
                                                                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-hotel me-2"></i>Por Hotel
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('client.favorites.toggle', $quota) }}" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="list_type" value="state">
                                                                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-map me-2"></i>Por Estado
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-warning" title="Entre para salvar nos desejados">
                                                <i class="far fa-star"></i>
                                            </a>
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger" title="Entre para favoritar">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        @endauth
                                        @php
                                            $txCard = request('transaction_type', 'rent');
                                            if ($txCard === 'rental') {
                                                $txCard = 'rent';
                                            }
                                            if ($txCard === 'purchase') {
                                                $txCard = 'buy';
                                            }
                                            $listPriceCard = $quota->getMarketplaceListPrice($txCard);
                                        @endphp
                                        <span class="badge rounded-pill bg-{{ $txCard === 'exchange' ? 'warning' : ($txCard === 'sell' || $txCard === 'buy' ? 'danger' : 'success') }} text-white">
                                            <i class="fas {{ $txCard === 'exchange' ? 'fa-exchange-alt' : ($txCard === 'buy' ? 'fa-shopping-cart' : ($txCard === 'sell' ? 'fa-hand-holding-usd' : 'fa-dollar-sign')) }} me-1"></i>{{ $txCard === 'exchange' ? 'Troca' : ($txCard === 'sell' ? 'Venda' : ($txCard === 'buy' ? 'Compra' : 'Aluguel')) }}
                                        </span>
                                        </div>
                                        </div>

                                <ul class="list-unstyled text-muted small mb-4">
                                    @foreach($quota->getPeriodDisplayLines() as $periodLine)
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-alt text-success me-2"></i>
                                        <strong>{{ trim($periodLine['label']) }}</strong> {{ $periodLine['formatted'] }}
                                    </li>
                                    @endforeach
                                    @if($quota->getPeriodDisplayLines() === [])
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-alt text-success me-2"></i>
                                        {{ $quota->start_date ? \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') : '-' }} a {{ $quota->end_date ? \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') : '-' }}
                                    </li>
                                    @endif
                                    <li class="mb-2">
                                        <i class="fas fa-users text-success me-2"></i>
                                        {{ $quota->number_of_guests }} {{ Str::plural('hóspede', $quota->number_of_guests) }}
                                    </li>
                                        @if($txCard === 'exchange')
                                        <li class="mb-2">
                                            <i class="fas fa-exchange-alt text-warning me-2"></i>
                                            <span class="fw-semibold text-secondary">R$ {{ number_format(0, 2, ',', '.') }}</span>
                                            <small class="text-muted d-block">Troca — valor na negociação</small>
                                        </li>
                                        @elseif($listPriceCard !== null)
                                        <li class="mb-2">
                                            <i class="fas fa-money-check-alt text-success me-2"></i>
                                            <span class="fw-semibold text-success">R$ {{ number_format($listPriceCard, 2, ',', '.') }}</span>
                                        </li>
                                        @else
                                        <li class="mb-2">
                                            <i class="fas fa-money-check-alt text-muted me-2"></i>
                                            <span class="text-muted small">Preço sob consulta</span>
                                        </li>
                                        @endif
                                    <li>
                                        <i class="fas fa-user-circle text-success me-2"></i>
                                        Publicado por {{ $quota->user->name }}
                                    </li>
                                </ul>

                                    @if($quota->observations)
                                    <div class="p-3 rounded-3 bg-success bg-opacity-10 mb-3">
                                        <small class="text-success d-block fw-semibold mb-1">
                                            <i class="fas fa-lightbulb me-1"></i>Diferenciais
                                            </small>
                                        <small class="text-muted">{{ Str::limit($quota->observations, 110) }}</small>
                                        </div>
                                    @endif
                                </div>

                            <div class="card-footer bg-white border-0 pt-0 pb-4 px-4">
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('quotas.show', array_merge([$quota], request()->only('transaction_type'))) }}" class="btn btn-success w-100">
                                        <i class="fas fa-eye me-2"></i>Ver detalhes completos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($quotas->hasPages())
                    <div class="mt-5">
                        {{ $quotas->links('vendor.pagination.modern') }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: rgba(0, 151, 57, 0.12);">
                    <i class="fas fa-search fa-3x text-success"></i>
                    </div>
                <h4 class="fw-semibold mb-3">Nenhuma cota ou fração encontrada</h4>
                <p class="text-muted mb-4">Revise os filtros selecionados ou salve esta busca para ser avisado quando houver ofertas disponíveis.</p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                    @auth
                        <form method="POST" action="{{ route('client.wishlist.save') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="hotel_name" value="{{ request('hotel_name') }}">
                            <input type="hidden" name="city" value="{{ request('city') }}">
                            <input type="hidden" name="state" value="{{ request('state') }}">
                            <input type="hidden" name="month" value="{{ request('month') }}">
                            <input type="hidden" name="year" value="{{ request('year') }}">
                            <input type="hidden" name="start_date" value="{{ request('check_in') }}">
                            <input type="hidden" name="end_date" value="{{ request('check_out') }}">
                            <input type="hidden" name="number_of_guests" value="{{ request('people') }}">
                            <input type="hidden" name="number_of_rooms" value="{{ request('rooms') }}">
                            <input type="hidden" name="nights" value="{{ request('stay_duration') }}">
                            <input type="hidden" name="seasonality" value="{{ request('seasonality') }}">
                            <input type="hidden" name="quota_type" value="{{ request('quota_type') }}">
                            <input type="hidden" name="price_min" value="{{ request('price_min', 0) }}">
                            <input type="hidden" name="price_max" value="{{ request('price_max', 250000) }}">
                            <input type="hidden" name="hidromassagem" value="{{ request('hidromassagem') }}">
                            <input type="hidden" name="academia" value="{{ request('academia') }}">
                            <input type="hidden" name="estacionamento_gratuito" value="{{ request('estacionamento_gratuito') }}">
                            <input type="hidden" name="vista_mar" value="{{ request('vista_mar') }}">
                            <input type="hidden" name="lareira" value="{{ request('lareira') }}">
                            <input type="hidden" name="adega" value="{{ request('adega') }}">
                            <input type="hidden" name="area_kids" value="{{ request('area_kids') }}">
                            <input type="hidden" name="area_trabalho" value="{{ request('area_trabalho') }}">
                            <input type="hidden" name="spa" value="{{ request('spa') }}">
                            <input type="hidden" name="piscina" value="{{ request('piscina') }}">
                            <input type="hidden" name="wifi" value="{{ request('wifi') }}">
                            <button type="submit" class="btn btn-primary" style="background: #6d28d9; border-color: #6d28d9;">
                                <i class="fas fa-star me-2"></i>Salvar busca nos Desejados
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary" style="background: #6d28d9; border-color: #6d28d9;">
                            <i class="fas fa-star me-2"></i>Entre para salvar busca
                        </a>
                    @endauth
                    <a href="{{ route('quotas.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Publicar nova cota
                        </a>
                        <a href="{{ route('quotas.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>Reiniciar busca
                        </a>
                    </div>
                </div>
            @endif
        @else
        <div class="text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: rgba(0, 151, 57, 0.12);">
                <i class="fas fa-search fa-3x text-success"></i>
            </div>
            <h4 class="fw-semibold mb-3">Busque cotas ou frações</h4>
            <p class="text-muted mb-4">Use os filtros acima para encontrar a cota ou fração ideal para você.</p>
        </div>
        @endif
        </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(request('exchange_refine') || request('purchase_refine'))
        // Nas telas de refine, evita múltiplos cliques em "Buscar" e "Limpar filtros".
        function mountRefineLoading() {
            const form = document.querySelector('form[action="{{ route('quotas.index') }}"]');
            const submitBtn = document.querySelector('.js-refine-submit');
            const clearBtn = document.querySelector('.js-refine-clear');
            const searchLabel = @json(request('purchase_refine') ? 'Buscando cotas e frações para compra...' : 'Buscando cotas e frações para troca...');
            let busy = false;

            const showLoading = function(label) {
                if (busy) return false;
                busy = true;
                const overlay = document.createElement('div');
                overlay.className = 'exchange-refine-loading-overlay';
                overlay.innerHTML = `
                    <div class="exchange-refine-loading-box">
                        <div class="spinner-border text-success mb-3" role="status" aria-hidden="true"></div>
                        <div class="fw-semibold text-dark">${label}</div>
                        <div class="small text-muted">Aguarde, estamos processando sua solicitação...</div>
                    </div>
                `;
                document.body.appendChild(overlay);

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('disabled');
                }
                if (clearBtn) {
                    clearBtn.classList.add('disabled');
                    clearBtn.style.pointerEvents = 'none';
                }
                return true;
            };

            if (form) {
                form.addEventListener('submit', function() {
                    return showLoading(searchLabel);
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    if (busy) {
                        e.preventDefault();
                        return;
                    }
                    showLoading('Limpando filtros...');
                });
            }
        }
        mountRefineLoading();
        @endif

        // Função para inicializar sliders de preço em cada tab
        function initPriceSliders() {
            const priceSliders = document.querySelectorAll('.price-range-slider');
            
            priceSliders.forEach(slider => {
                const container = slider.closest('.w-100');
                const priceMaxDisplay = container ? container.querySelector('.price-max-display') : null;
                const priceMaxInput = container ? container.querySelector('.price-max-input') : null;
                
                if (slider && priceMaxDisplay && priceMaxInput) {
                    function updateDisplay(value) {
                        const numValue = parseInt(value) || 0;
                        const formattedValue = numValue.toLocaleString('pt-BR', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                        priceMaxDisplay.textContent = formattedValue;
                        priceMaxInput.value = numValue;
                    }
                    
                    // Inicializar valor
                    updateDisplay(slider.value);
                    
                    // Event listeners
                    slider.addEventListener('input', function() {
                        updateDisplay(this.value);
                    });
                    
                    slider.addEventListener('change', function() {
                        updateDisplay(this.value);
                    });
                }
            });
        }

        // Inicializar sliders quando a página carregar
        initPriceSliders();

        // Melhorar aparência dos campos select quando uma opção está selecionada
        function updateSelectStyles() {
            const selects = document.querySelectorAll('.form-select');
            selects.forEach(select => {
                if (select.value && select.value !== '') {
                    select.classList.add('has-selection');
                } else {
                    select.classList.remove('has-selection');
                }
            });
        }

        // Aplicar estilos inicialmente
        updateSelectStyles();

        // Adicionar event listeners para todos os selects
        document.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', function() {
                updateSelectStyles();
            });
        });

        // Add hover effects to quota cards
        const quotaCards = document.querySelectorAll('.quota-card');
        
        quotaCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Autocomplete para campo de hotel (funciona em todas as tabs)
        function initHotelAutocomplete() {
            const hotelInputs = document.querySelectorAll('#hotel_name');
            
            hotelInputs.forEach((hotelInput, inputIndex) => {
                const wrapper = hotelInput.closest('.hotel-autocomplete-wrapper');
                if (!wrapper) return;
                
                let autocompleteList = wrapper.querySelector('.hotel-autocomplete-list');
                if (!autocompleteList) {
                    autocompleteList = document.createElement('div');
                    autocompleteList.className = 'hotel-autocomplete-list';
                    autocompleteList.id = `hotel-autocomplete-${inputIndex}`;
                    wrapper.appendChild(autocompleteList);
                }
                
                let searchTimeout;
                let selectedIndex = -1;
                
                // Função para buscar hotéis
                function searchHotels(query) {
                    if (query.length < 1) {
                        autocompleteList.innerHTML = '';
                        autocompleteList.style.display = 'none';
                        return;
                    }

                fetch(`{{ route('api.hotels.search') }}?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.data && data.data.length > 0) {
                            autocompleteList.innerHTML = '';
                            data.data.forEach((hotel, index) => {
                                const item = document.createElement('div');
                                item.className = 'hotel-autocomplete-item';
                                item.dataset.index = index;
                                item.innerHTML = `
                                    <i class="fas fa-hotel me-2"></i>
                                    <span class="hotel-name">${hotel.name}</span>
                                    ${hotel.city || hotel.state ? `<span class="hotel-location"> - ${[hotel.city, hotel.state].filter(Boolean).join(', ')}</span>` : ''}
                                `;
                                item.addEventListener('click', function() {
                                    hotelInput.value = hotel.name;
                                    autocompleteList.innerHTML = '';
                                    autocompleteList.style.display = 'none';
                                    selectedIndex = -1;
                                });
                                item.addEventListener('mouseenter', function() {
                                    document.querySelectorAll('.hotel-autocomplete-item').forEach(i => i.classList.remove('active'));
                                    this.classList.add('active');
                                    selectedIndex = parseInt(this.dataset.index);
                                });
                                autocompleteList.appendChild(item);
                            });
                            autocompleteList.style.display = 'block';
                        } else {
                            autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Nenhum hotel encontrado</div>';
                            autocompleteList.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar hotéis:', error);
                    });
            }

            // Event listener para input - busca a cada letra digitada
            hotelInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                // Busca imediatamente se tiver pelo menos 1 caractere
                if (query.length >= 1) {
                    searchTimeout = setTimeout(() => {
                        searchHotels(query);
                    }, 100); // Pequeno delay para evitar muitas requisições
                } else {
                    autocompleteList.innerHTML = '';
                    autocompleteList.style.display = 'none';
                }
            });

            // Fechar autocomplete ao clicar fora
            document.addEventListener('click', function(e) {
                if (!hotelInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                    autocompleteList.style.display = 'none';
                }
            });

            // Navegação com teclado
            hotelInput.addEventListener('keydown', function(e) {
                const items = autocompleteList.querySelectorAll('.hotel-autocomplete-item:not(.hotel-autocomplete-no-results)');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % items.length;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    if (items[selectedIndex]) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    if (items[selectedIndex]) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                } else if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
                    e.preventDefault();
                    items[selectedIndex].click();
                } else if (e.key === 'Escape') {
                    autocompleteList.style.display = 'none';
                    selectedIndex = -1;
                }
            });

                // Focar no campo ao clicar no autocomplete
                hotelInput.addEventListener('focus', function() {
                    if (this.value.trim().length >= 1) {
                        searchHotels(this.value.trim());
                    }
                });
            });
        }

        // Inicializar autocomplete quando a página carregar
        initHotelAutocomplete();
        
        // Reinicializar quando trocar de tab
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(initHotelAutocomplete, 200);
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
/* Estilos para campos de seleção (select) */
.form-select {
    color: #212529;
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
    font-weight: 500;
}

.form-select:focus {
    border-color: #009739;
    box-shadow: 0 0 0 0.2rem rgba(0, 151, 57, 0.25);
    outline: none;
}

/* Quando uma opção está selecionada, aplicar estilo especial */
.form-select.has-selection {
    color: #ffffff !important;
    background-color: #009739 !important;
    font-weight: 600;
    border-color: #009739;
}

.form-select.has-selection:focus {
    color: #ffffff !important;
    background-color: #007a2e !important;
    border-color: #007a2e;
}

/* Estilo para as opções no dropdown */
.form-select option {
    color: #212529;
    background-color: #ffffff;
    padding: 0.5rem;
}

.form-select option:checked,
.form-select option[selected] {
    color: #ffffff !important;
    background-color: #009739 !important;
    font-weight: 600;
}

/* Melhorar aparência dos inputs de texto */
.form-control {
    color: #212529;
    transition: all 0.3s ease;
    font-weight: 500;
}

.form-control:focus {
    border-color: #009739;
    box-shadow: 0 0 0 0.2rem rgba(0, 151, 57, 0.25);
    color: #212529;
    outline: none;
}

.form-control::placeholder {
    color: #6c757d;
    opacity: 0.7;
}

/* Paginação Moderna e Profissional */
.pagination-wrapper-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-top: 1px solid rgba(0, 151, 57, 0.1);
    margin-top: 2rem;
}

.pagination-info-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
}

.pagination-info-modern strong {
    color: #009739;
    font-weight: 600;
}

.pagination-modern-list {
    display: flex !important;
    align-items: center;
    gap: 0.35rem !important;
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination-modern-item {
    margin: 0 !important;
    list-style: none !important;
}

.pagination-modern-link {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 36px !important;
    height: 36px !important;
    padding: 0 0.5rem !important;
    background: #ffffff !important;
    border: 1px solid rgba(0, 151, 57, 0.2) !important;
    border-radius: 6px !important;
    color: #64748b !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    margin: 0 !important;
}

.pagination-modern-link:hover:not(.pagination-modern-link-disabled):not(.pagination-modern-link-active) {
    background: rgba(0, 151, 57, 0.06) !important;
    border-color: rgba(0, 151, 57, 0.35) !important;
    color: #009739 !important;
    box-shadow: 0 2px 6px rgba(0, 151, 57, 0.12) !important;
}

.pagination-modern-link-active {
    background: #009739 !important;
    border-color: #009739 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(0, 151, 57, 0.2) !important;
    font-weight: 600 !important;
    cursor: default !important;
}

.pagination-modern-link-disabled {
    background: #f8f9fa !important;
    border-color: #e2e8f0 !important;
    color: #cbd5e1 !important;
    cursor: not-allowed !important;
    opacity: 0.6 !important;
    transform: none !important;
    box-shadow: none !important;
}

.pagination-modern-link-disabled:hover {
    background: #f8f9fa !important;
    border-color: #e2e8f0 !important;
    color: #cbd5e1 !important;
    transform: none !important;
    box-shadow: none !important;
}

.pagination-modern-link-prev,
.pagination-modern-link-next {
    min-width: 36px !important;
    font-weight: 500 !important;
}

.pagination-modern-link i {
    font-size: 0.75rem;
}

.pagination-modern-item-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
}

.pagination-modern-dots {
    color: #cbd5e1;
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1;
}

@media (max-width: 768px) {
    .pagination-wrapper-modern {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem 0;
    }

    .pagination-info-modern {
        margin: 0;
        text-align: center;
        width: 100%;
        justify-content: center;
        font-size: 0.8rem;
    }

    .pagination-modern-link {
        min-width: 32px !important;
        height: 32px !important;
        padding: 0 0.4rem !important;
        font-size: 0.8rem !important;
    }
    
    .pagination-modern-link-prev,
    .pagination-modern-link-next {
        min-width: 32px !important;
    }
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

@media (max-width: 768px) {
}

/* Estilos para Autocomplete de Hotéis */
.hotel-autocomplete-wrapper {
    width: 100%;
}

.hotel-autocomplete-list {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.hotel-autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
}

.hotel-autocomplete-item:last-child {
    border-bottom: none;
}

.hotel-autocomplete-item:hover,
.hotel-autocomplete-item.active {
    background-color: rgba(0, 151, 57, 0.1);
    color: #009739;
}

.hotel-autocomplete-item i {
    color: #009739;
    width: 20px;
}

.hotel-name {
    font-weight: 600;
    color: #212529;
}

.hotel-location {
    color: #6c757d;
    font-size: 0.9rem;
    margin-left: 0.5rem;
}

.hotel-autocomplete-no-results {
    color: #6c757d;
    font-style: italic;
    cursor: default;
}

.hotel-autocomplete-no-results:hover {
    background-color: transparent;
    color: #6c757d;
}

/* Estilos para o Card Opções no meu hotel */
.hotel-options-card {
    width: 100%;
    max-width: 100%;
    background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(10, 82, 52, 0.95));
    border-radius: 24px;
    padding: 2.5rem 2.8rem;
    box-shadow: 0 24px 65px rgba(5, 74, 40, 0.25);
    position: relative;
    overflow: hidden;
}

.hotel-options-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent 70%);
    border-radius: 50%;
}

.hotel-options-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.hotel-options-text {
    flex: 1;
}

.hotel-options-title {
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff !important;
    margin: 0 0 0.75rem 0;
    line-height: 1.2;
}

.hotel-options-subtitle {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    line-height: 1.6;
    max-width: 600px;
}

.hotel-options-button {
    background: #fbbf24;
    color: #000000;
    padding: 1rem 2.5rem;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1.05rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(251, 191, 36, 0.4);
    white-space: nowrap;
}

.hotel-options-button:hover {
    background: #f59e0b;
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(251, 191, 36, 0.5);
    color: #000000;
}

@media (max-width: 768px) {
    .hotel-options-card {
        padding: 2rem 1.8rem;
    }

    .hotel-options-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.5rem;
    }

    .hotel-options-title {
        font-size: 1.65rem;
    }

    .hotel-options-subtitle {
        font-size: 1rem;
    }

    .hotel-options-button {
        width: 100%;
        padding: 0.9rem 2rem;
    }
}

@media (max-width: 576px) {
    .hotel-options-card {
        padding: 1.8rem 1.5rem;
        border-radius: 20px;
    }

    .hotel-options-title {
        font-size: 1.5rem;
    }

    .hotel-options-subtitle {
        font-size: 0.95rem;
    }
}
</style>
<style>
.exchange-refine-loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.35);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}
.exchange-refine-loading-box {
    background: #fff;
    border-radius: 12px;
    padding: 20px 22px;
    min-width: 320px;
    max-width: 90vw;
    text-align: center;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
}
</style>
@endpush
