@extends('layouts.app')

@section('title', 'Vender Cotas - Cota Brasilis')

@section('content')
<style>
    .sales-ofertar-action-card .sales-ofertar-title,
    .sales-ofertar-action-card .sales-ofertar-title i {
        color: #009739 !important;
    }
</style>
<div class="container-fluid py-4">
<!-- Card de Ação -->
<div class="row mb-4">
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm h-100 sales-ofertar-action-card bg-white">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3 sales-ofertar-title text-success">
                    <i class="fas fa-hand-holding-usd me-2 text-success"></i>Ofertar
                </h4>
                <p class="mb-4 text-muted">
                    Crie uma oferta de venda para negociar suas cotas com outros usuários ou solicite que profissionais agregados ao Cota Brasilis negocie para você.
                </p>
                <a href="{{ route('sales.create') }}" class="btn btn-warning btn-lg fw-bold" style="background: #fbbf24; color: #000000; border: none;">
                    <i class="fas fa-plus me-2"></i>Criar oferta
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Minhas Ofertas de Venda</h4>
        </div>

        @if($sales->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                    <i class="fas fa-hand-holding-usd fa-3x text-success"></i>
                </div>
                <h3 class="fw-bold mb-3">Você ainda não possui ofertas de venda</h3>
                <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                    Crie uma oferta de venda para negociar suas cotas com outros usuários ou solicite que profissionais agregados ao Cota Brasilis negocie para você.
                </p>
                <a href="{{ route('sales.create') }}" class="btn btn-success btn-lg px-4">
                    <i class="fas fa-plus me-2"></i>Criar oferta
                </a>
            </div>
        @else
            <div class="row g-4">
                @foreach($sales as $sale)
                    <div class="col-md-6">
                        <div class="border rounded-4 h-100 p-4 shadow-sm bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">{{ $sale->hotel->name ?? 'Hotel' }}</h5>
                                <span class="badge bg-{{ $sale->status === 'sold' ? 'success' : ($sale->status === 'negotiating' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </div>
                            <ul class="list-unstyled mb-4 text-muted small">
                                <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-success"></i>{{ $sale->city }}</li>
                                <li class="mb-2"><i class="fas fa-calendar-week me-2 text-success"></i>{{ $sale->weeks }} {{ $sale->weeks == 1 ? 'semana' : 'semanas' }}</li>
                                <li><i class="fas fa-dollar-sign me-2 text-success"></i>
                                    @if($sale->desired_price !== null)
                                        Preço desejado: R$ {{ number_format($sale->desired_price, 2, ',', '.') }}
                                    @else
                                        Valores a combinar
                                    @endif
                                </li>
                            </ul>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-eye me-2"></i>Ver detalhes
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $sales->links('vendor.pagination.modern') }}
            </div>
        @endif
    </div>
</div>
</div>
@endsection

