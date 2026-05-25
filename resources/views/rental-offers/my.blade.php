@extends('layouts.app')

@section('title', 'Minhas Ofertas de Aluguel - Cota Brasilis')

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
                        Crie uma oferta de aluguel para disponibilizar suas cotas ou frações, aumentar as opções no Cota Brasilis e gerar receita.
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
                    <a href="{{ route('quotas.index') }}" class="btn btn-success btn-lg fw-bold">
                        <i class="fas fa-calendar-check me-2"></i>Alugar
                    </a>
                </div>
            </div>
        </div>
    </div>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Minhas Ofertas de Aluguel</h4>
        </div>

        <!-- Filtros -->
        <div class="mb-4">
            <form method="GET" action="{{ route('rental-offers.my') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold small">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativas</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativas</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiradas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="is_auction" class="form-label fw-semibold small">Tipo</label>
                    <select class="form-select" id="is_auction" name="is_auction">
                        <option value="">Todos</option>
                        <option value="1" {{ request('is_auction') === '1' ? 'selected' : '' }}>Leilões</option>
                        <option value="0" {{ request('is_auction') === '0' ? 'selected' : '' }}>Ofertas Normais</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('rental-offers.my') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i>Limpar
                    </a>
                </div>
            </form>
        </div>

        @if($offers->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                    <i class="fas fa-calendar-check fa-3x text-success"></i>
                </div>
                <h3 class="fw-bold mb-3">Você ainda não possui ofertas de aluguel</h3>
                <p class="text-muted mb-0" style="max-width: 520px; margin: 0 auto;">
                    Crie uma oferta de aluguel para disponibilizar suas cotas ou frações para outros membros cadastrados.
                </p>
            </div>
        @else
            <div class="row g-4">
                @foreach($offers as $offer)
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-4 h-100 p-4 shadow-sm bg-light position-relative">
                            <!-- Status Badge -->
                            <div class="position-absolute top-0 end-0 m-3">
                                @if($offer->is_active && $offer->end_date >= now())
                                    <span class="badge bg-success">Ativa</span>
                                @elseif($offer->end_date < now())
                                    <span class="badge bg-danger">Expirada</span>
                                @else
                                    <span class="badge bg-secondary">Inativa</span>
                                @endif
                            </div>

                            <!-- Auction Badge -->
                            @if($offer->is_auction)
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-gavel me-1"></i>Leilão
                                </span>
                            </div>
                            @endif

                            <div class="mb-3">
                                <h5 class="fw-bold mb-2 text-dark">{{ $offer->display_title }}</h5>
                                @if($offer->hotel && $offer->hotel->name)
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-hotel me-2 text-success"></i>{{ $offer->hotel->name }}
                                </p>
                                @elseif($offer->city)
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-map-marker-alt me-2 text-success"></i>{{ $offer->city }}{{ $offer->state ? ', ' . $offer->state : '' }}
                                </p>
                                @endif
                            </div>

                            <ul class="list-unstyled mb-4 text-muted small">
                                @if($offer->city)
                                <li class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                    {{ $offer->city }}{{ $offer->state ? ', ' . $offer->state : '' }}
                                </li>
                                @endif
                                @if($offer->start_date)
                                <li class="mb-2">
                                    <i class="fas fa-calendar-check me-2 text-success"></i>
                                    {{ \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') }} 
                                    até {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                </li>
                                @endif
                                @if($offer->number_of_people)
                                <li class="mb-2">
                                    <i class="fas fa-users me-2 text-success"></i>
                                    {{ $offer->number_of_people }} {{ $offer->number_of_people == 1 ? 'pessoa' : 'pessoas' }}
                                </li>
                                @endif
                                @if($offer->number_of_days)
                                <li class="mb-2">
                                    <i class="fas fa-calendar-day me-2 text-success"></i>
                                    {{ $offer->number_of_days }} {{ $offer->number_of_days == 1 ? 'dia' : 'dias' }}
                                </li>
                                @endif
                                @if($offer->price)
                                <li class="mb-2">
                                    <i class="fas fa-dollar-sign me-2 text-success"></i>
                                    <strong class="text-success">R$ {{ number_format($offer->price, 2, ',', '.') }}</strong>
                                </li>
                                @endif
                                @if($offer->is_auction && $offer->auction_start_time)
                                <li>
                                    <i class="fas fa-clock me-2 text-success"></i>
                                    Leilão: {{ \Carbon\Carbon::parse($offer->auction_start_time)->format('d/m/Y H:i') }}
                                </li>
                                @endif
                            </ul>

                            <!-- Options Badges -->
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                @if($offer->accepts_exchange)
                                <span class="badge bg-info">
                                    <i class="fas fa-exchange-alt me-1"></i>Aceita Troca
                                </span>
                                @endif
                                @if($offer->accepts_sale)
                                <span class="badge bg-primary">
                                    <i class="fas fa-hand-holding-usd me-1"></i>Aceita Venda
                                </span>
                                @endif
                                @if($offer->accepts_diaria_exchange)
                                <span class="badge bg-secondary">
                                    <i class="fas fa-calendar-day me-1"></i>Troca por Diárias
                                </span>
                                @endif
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('rental-offers.show', $offer) }}" class="btn btn-outline-success btn-sm flex-fill">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                                @if($offer->status === 'active' && $offer->end_date >= now())
                                <a href="{{ route('rental-offers.edit', $offer) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($offers->hasPages())
            <div class="mt-4">
                {{ $offers->links('vendor.pagination.modern') }}
            </div>
            @endif
        @endif
    </div>
</div>
</div>

@endsection

