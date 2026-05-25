@extends('layouts.app')

@section('title', 'Minhas Cotas')

@section('content')
<style>
    .my-quotas-page {
        background: linear-gradient(180deg, #f7faf9 0%, #ffffff 45%);
    }
    .my-quotas-hero {
        background: linear-gradient(135deg, #0a8f3f 0%, #046143 100%);
        border-radius: 20px;
        color: #fff;
        padding: 1.8rem 1.6rem;
        box-shadow: 0 18px 45px rgba(4, 97, 67, 0.25);
    }
    .my-quotas-hero h1,
    .my-quotas-hero h1 i {
        color: #ffffff !important;
    }
    .my-quotas-page .quota-card__header,
    .my-quotas-page .quota-card__header h5,
    .my-quotas-page .quota-card__header h5 i,
    .my-quotas-page .quota-card__header .badge {
        color: #ffffff !important;
    }
    .my-quotas-hero .lead {
        color: rgba(255, 255, 255, 0.9) !important;
        margin-bottom: 0;
    }
    .section-shell {
        border: 1px solid #e6ece8;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
    }
    .section-shell .card-header {
        background: linear-gradient(90deg, #f3fbf7 0%, #ffffff 60%);
        border-bottom: 1px solid #ecf2ef;
        border-radius: 1rem 1rem 0 0 !important;
    }
    .section-title {
        font-size: 1.3rem;
        color: #0f5132;
    }
    .tabs-modern.nav-pills .nav-link {
        border-radius: 999px;
        background: #f1f5f4;
        color: #5c6d67;
        font-weight: 600;
        border: 1px solid transparent;
        transition: all .2s ease;
    }
    .tabs-modern.nav-pills .nav-link.active {
        background: #0a8f3f;
        color: #fff !important;
        border-color: #0a8f3f;
        box-shadow: 0 8px 18px rgba(10, 143, 63, .25);
    }
    .tabs-modern.nav-pills .nav-link.active i,
    .tabs-modern.nav-pills .nav-link.active span {
        color: #fff !important;
    }
    .tabs-modern .badge {
        font-weight: 700;
        font-size: .72rem;
    }
    .actions-shell {
        border-top: 1px solid #e9efec;
        padding-top: 1.25rem;
    }
</style>

<div class="container py-5 position-relative my-quotas-page">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="flex-grow-1"></div>
                <!-- Botão Voltar ao Painel - Superior Direito -->
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-light border">
                        <i class="fas fa-arrow-left me-2"></i>Voltar ao Painel de Controle
                    </a>
                </div>
            </div>
            <div class="text-center my-quotas-hero">
                <h1 class="display-6 fw-bold mb-3 text-white">
                    <i class="fas fa-user-circle me-3 text-white"></i>Minhas Cotas
                </h1>
                <p class="lead">Acompanhe suas buscas e publicações, gerencie situações das ofertas e otimize suas receitas e desfrute hoteleiros.</p>
            </div>
        </div>
    </div>

    <!-- Ofertado Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 rounded-4 mb-4 section-shell">
                <div class="card-header bg-white border-0 pb-0 pt-4">
                    <h3 class="fw-bold mb-0 section-title">
                        <i class="fas fa-hand-holding-usd me-2 text-success"></i>Ofertado
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Tabs para status -->
                    <ul class="nav nav-pills tabs-modern mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="offered-exitosas-tab" data-bs-toggle="tab" data-bs-target="#offered-exitosas" type="button" role="tab">
                                <i class="fas fa-check-circle me-2"></i>Exitosas <span class="badge bg-success ms-2">{{ $offeredExitosas->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="offered-andamento-tab" data-bs-toggle="tab" data-bs-target="#offered-andamento" type="button" role="tab">
                                <i class="fas fa-clock me-2"></i>Em andamento <span class="badge bg-warning ms-2">{{ $offeredEmAndamento->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="offered-inspiradas-tab" data-bs-toggle="tab" data-bs-target="#offered-inspiradas" type="button" role="tab">
                                <i class="fas fa-lightbulb me-2"></i>Expiradas <span class="badge bg-danger ms-2">{{ $offeredInspiradas->count() }}</span>
                            </button>
                        </li>
                    </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="offered-exitosas" role="tabpanel">
                    <div class="row">
                        @if($offeredExitosas->count() > 0)
                            @foreach($offeredExitosas as $item)
                                @php
                                    $quota = $item instanceof \App\Models\Quota ? $item : $item->quota;
                                    $isOffer = $item instanceof \App\Models\RentalOffer;
                                @endphp
                                @if($quota)
                                    @include('quotas.partials.quota-card', ['quota' => $quota, 'isOffer' => $isOffer])
                                @endif
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info text-center rounded-4 border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-2"></i>Nenhuma oferta exitosa no momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="tab-pane fade" id="offered-andamento" role="tabpanel">
                    <div class="row">
                        @if($offeredEmAndamento->count() > 0)
                            @foreach($offeredEmAndamento as $item)
                                @php
                                    $quota = $item instanceof \App\Models\Quota ? $item : $item->quota;
                                    $isOffer = $item instanceof \App\Models\RentalOffer;
                                @endphp
                                @if($quota)
                                    @include('quotas.partials.quota-card', ['quota' => $quota, 'isOffer' => $isOffer])
                                @endif
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info text-center rounded-4 border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-2"></i>Nenhuma oferta em andamento no momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="tab-pane fade" id="offered-inspiradas" role="tabpanel">
                    <div class="row">
                        @if($offeredInspiradas->count() > 0)
                            @foreach($offeredInspiradas as $item)
                                @php
                                    $quota = $item instanceof \App\Models\Quota ? $item : $item->quota;
                                    $isOffer = $item instanceof \App\Models\RentalOffer;
                                @endphp
                                @if($quota)
                                    @include('quotas.partials.quota-card', ['quota' => $quota, 'isOffer' => $isOffer])
                                @endif
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info text-center rounded-4 border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-2"></i>Nenhuma oferta inspirada no momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
                </div>
        </div>
    </div>

    <!-- Solicitado Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 rounded-4 mb-4 section-shell">
                <div class="card-header bg-white border-0 pb-0 pt-4">
                    <h3 class="fw-bold mb-0 section-title">
                        <i class="fas fa-search me-2 text-primary"></i>Solicitado
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Tabs para status -->
                    <ul class="nav nav-pills tabs-modern mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="requested-exitosas-tab" data-bs-toggle="tab" data-bs-target="#requested-exitosas" type="button" role="tab">
                                <i class="fas fa-check-circle me-2"></i>Exitosas <span class="badge bg-success ms-2">{{ $requestedExitosas->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requested-andamento-tab" data-bs-toggle="tab" data-bs-target="#requested-andamento" type="button" role="tab">
                                <i class="fas fa-clock me-2"></i>Em andamento <span class="badge bg-warning ms-2">{{ $requestedEmAndamento->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requested-inspiradas-tab" data-bs-toggle="tab" data-bs-target="#requested-inspiradas" type="button" role="tab">
                                <i class="fas fa-lightbulb me-2"></i>Expiradas <span class="badge bg-danger ms-2">{{ $requestedInspiradas->count() }}</span>
                            </button>
                        </li>
                    </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="requested-exitosas" role="tabpanel">
                    <div class="row">
                        @if($requestedExitosas->count() > 0)
                            @foreach($requestedExitosas as $transaction)
                                @include('quotas.partials.transaction-card', ['transaction' => $transaction])
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info text-center rounded-4 border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-2"></i>Nenhuma solicitação exitosa no momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="tab-pane fade" id="requested-andamento" role="tabpanel">
                    <div class="row">
                        @if($requestedEmAndamento->count() > 0)
                            @foreach($requestedEmAndamento as $transaction)
                                @include('quotas.partials.transaction-card', ['transaction' => $transaction])
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info text-center rounded-4 border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-2"></i>Nenhuma solicitação em andamento no momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="tab-pane fade" id="requested-inspiradas" role="tabpanel">
                    <div class="row">
                        @if($requestedInspiradas->count() > 0)
                            @foreach($requestedInspiradas as $transaction)
                                @include('quotas.partials.transaction-card', ['transaction' => $transaction])
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="alert alert-info text-center rounded-4 border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-2"></i>Nenhuma solicitação inspirada no momento.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
                </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-4 position-relative actions-shell">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('quotas.index') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Buscar Cotas ou Frações
                    </a>
                </div>
                <!-- Botão Voltar ao Painel - Inferior Direito -->
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Voltar ao Painel de Controle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });

    function deleteQuota(quotaId) {
        if (confirm('Tem certeza que deseja excluir esta cota? Esta ação não pode ser desfeita.')) {
            // Create a form to submit DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/quotas/${quotaId}`;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection