@extends('layouts.app')

@section('title', 'Comprar Cotas - Cota Brasilis')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #009739, #007a2e);">
                <div class="card-body p-4 text-white">
                    <h4 class="fw-bold mb-3" style="color: #86efac;">
                        <i class="fas fa-hand-holding-usd me-2" style="color: #86efac;"></i>Ofertar
                    </h4>
                    <p class="mb-4" style="opacity: 0.95;">
                        Crie uma oferta de venda para disponibilizar suas cotas ou frações
                    </p>
                    <a href="{{ route('sales.create') }}" class="btn btn-warning btn-lg fw-bold" style="background: #fbbf24; color: #000000; border: none;">
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
                        Busque e solicite compra de cotas ou frações disponíveis que atendam às suas necessidades.
                    </p>
                    <a href="{{ route('purchases.request') }}" class="btn btn-success btn-lg fw-bold">
                        <i class="fas fa-shopping-cart me-2"></i>Comprar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-semibold mb-0">Minhas Solicitações de Compra</h4>
            </div>

            @if($purchases->isEmpty())
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                        <i class="fas fa-shopping-cart fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Você ainda não possui solicitações de compra</h3>
                    <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                        Use o botão Comprar acima para buscar cotas ou frações à venda.
                    </p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($purchases as $purchase)
                        <div class="col-md-6">
                            <div class="border rounded-4 h-100 p-4 shadow-sm bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">{{ $purchase->hotel->name ?? 'Hotel' }}</h5>
                                    <span class="badge bg-{{ $purchase->status === 'purchased' ? 'success' : ($purchase->status === 'matched' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </div>
                                <ul class="list-unstyled mb-4 text-muted small">
                                    @if($purchase->city)
                                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-success"></i>{{ $purchase->city }}</li>
                                    @endif
                                    @if($purchase->weeks)
                                        <li class="mb-2"><i class="fas fa-calendar-week me-2 text-success"></i>{{ $purchase->weeks }} {{ $purchase->weeks == 1 ? 'semana' : 'semanas' }}</li>
                                    @endif
                                    @if($purchase->delegated_to_admin)
                                        <li><i class="fas fa-user-shield me-2 text-success"></i>Delegado ao administrador</li>
                                    @endif
                                </ul>
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $purchases->links('vendor.pagination.modern') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
