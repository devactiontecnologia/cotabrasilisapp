@extends('layouts.app')

@section('title', 'Trocar Cotas - Cota Brasilis')

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
                    Crie ofertas de troca de cota ou fração para otimizar suas chances de usufruto de hospedagens
                </p>
                <a href="{{ route('exchanges.create') }}" class="btn btn-warning btn-lg fw-bold" style="background: #fbbf24; color: #000000; border: none;">
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
                    Busque e solicite troca de cotas ou frações disponíveis que atendam às suas necessidades.
                </p>
                <a href="{{ route('exchanges.refine') }}" class="btn btn-success btn-lg fw-bold">
                    <i class="fas fa-exchange-alt me-2"></i>Trocar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Minhas Ofertas de Troca</h4>
        </div>

        @if($exchanges->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                    <i class="fas fa-exchange-alt fa-3x text-success"></i>
                </div>
                <h3 class="fw-bold mb-3">Você ainda não possui ofertas de troca</h3>
                <p class="text-muted mb-0" style="max-width: 520px; margin: 0 auto;">
                    Crie uma oferta de troca para encontrar outras cotas que se adequem às suas necessidades.
                </p>
            </div>
        @else
            <div class="row g-4">
                @foreach($exchanges as $exchange)
                    <div class="col-md-6">
                        <div class="border rounded-4 h-100 p-4 shadow-sm bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Troca de {{ $exchange->exchange_type === 'semana' ? 'Semana' : 'Titularidade' }}</h5>
                                <span class="badge bg-{{ $exchange->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($exchange->status) }}
                                </span>
                            </div>
                            <ul class="list-unstyled mb-4 text-muted small">
                                @if($exchange->desired_cities_labels !== '')
                                <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-success"></i>{{ $exchange->desired_cities_labels }}</li>
                                @endif
                                @if($periodLabel = $exchange->getDesiredPeriodLabel())
                                <li class="mb-2"><i class="fas fa-calendar-check me-2 text-success"></i>{{ $periodLabel }}</li>
                                @endif
                                @if($exchange->validity_until)
                                <li><i class="fas fa-clock me-2 text-success"></i>Válido até {{ $exchange->validity_until->format('d/m/Y H:i') }}</li>
                                @endif
                            </ul>
                            <div class="d-flex gap-2">
                                <a href="{{ route('exchanges.show', $exchange) }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                                @if($exchange->status === 'active')
                                <a href="{{ route('exchanges.edit', $exchange) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $exchanges->links('vendor.pagination.modern') }}
            </div>
        @endif
    </div>
</div>
</div>
@endsection

