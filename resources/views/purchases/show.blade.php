@extends('layouts.app')

@section('title', 'Detalhes da Compra - Cota Brasilis')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Detalhes da Solicitação de Compra</h4>
            <div class="d-flex gap-2">
                @if(!$purchaseRequest->delegated_to_admin && $purchaseRequest->status === 'active')
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#delegateModal">
                    <i class="fas fa-user-shield me-2"></i>Delegar ao Admin
                </button>
                @endif
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="border rounded-4 p-4 bg-light">
                    <h5 class="fw-bold mb-4">Informações da Solicitação</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $purchaseRequest->status === 'purchased' ? 'success' : ($purchaseRequest->status === 'matched' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($purchaseRequest->status) }}
                                </span>
                            </p>
                        </div>
                        @if($purchaseRequest->hotel)
                        <div class="col-md-6">
                            <label class="text-muted small">Hotel</label>
                            <p class="fw-semibold">{{ $purchaseRequest->hotel->name }}</p>
                        </div>
                        @endif
                        @if($purchaseRequest->city)
                        <div class="col-md-6">
                            <label class="text-muted small">Cidade</label>
                            <p class="fw-semibold">{{ $purchaseRequest->city }}</p>
                        </div>
                        @endif
                        @if($purchaseRequest->weeks)
                        <div class="col-md-3">
                            <label class="text-muted small">Semanas</label>
                            <p class="fw-semibold">{{ $purchaseRequest->weeks }} {{ $purchaseRequest->weeks == 1 ? 'semana' : 'semanas' }}</p>
                        </div>
                        @endif
                        @if($purchaseRequest->month)
                        <div class="col-md-3">
                            <label class="text-muted small">Mês</label>
                            <p class="fw-semibold">{{ \Carbon\Carbon::create()->month($purchaseRequest->month)->locale('pt_BR')->monthName }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="text-muted small">Tipo de Período</label>
                            <p class="fw-semibold">{{ $purchaseRequest->period_type === 'fixo' ? 'Fixo' : 'Flexível' }}</p>
                        </div>
                        @if($purchaseRequest->company)
                        <div class="col-md-6">
                            <label class="text-muted small">Empresa</label>
                            <p class="fw-semibold">{{ $purchaseRequest->company }}</p>
                        </div>
                        @endif
                        @if($purchaseRequest->price_range_min || $purchaseRequest->price_range_max)
                        <div class="col-md-12">
                            <label class="text-muted small">Faixa de Preço</label>
                            <p class="fw-semibold">
                                R$ {{ number_format($purchaseRequest->price_range_min ?? 0, 2, ',', '.') }} 
                                até 
                                R$ {{ number_format($purchaseRequest->price_range_max ?? 0, 2, ',', '.') }}
                            </p>
                        </div>
                        @endif
                        @if($purchaseRequest->delegated_to_admin)
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-user-shield me-2"></i>
                                <strong>Delegado ao Administrador</strong>
                                @if($purchaseRequest->max_price)
                                <p class="mb-0 mt-2">Preço máximo: R$ {{ number_format($purchaseRequest->max_price, 2, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($purchaseRequest->observations)
                    <div class="mt-4">
                        <label class="text-muted small">Observações</label>
                        <p class="fw-semibold">{{ $purchaseRequest->observations }}</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <label class="text-muted small">Taxa de Compra</label>
                        <p class="fw-semibold">{{ number_format($purchaseRequest->purchase_fee_percentage, 2) }}%</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                @if($matches && $matches->count() > 0)
                <div class="border rounded-4 p-4 bg-light">
                    <h6 class="fw-bold mb-3">Ofertas Correspondentes</h6>
                    <p class="text-muted small mb-3">{{ $matches->count() }} ofertas de venda correspondem aos seus critérios</p>
                    @foreach($matches->take(3) as $match)
                        <div class="border rounded p-3 mb-3">
                            <h6 class="fw-bold small">{{ $match->hotel->name ?? 'Hotel' }}</h6>
                            <p class="text-muted small mb-2">{{ $match->city }}</p>
                            <a href="{{ route('sales.show', $match) }}" class="btn btn-sm btn-outline-success w-100">
                                <i class="fas fa-eye me-2"></i>Ver Oferta
                            </a>
                        </div>
                    @endforeach
                    @if($matches->count() > 3)
                        <a href="#" class="btn btn-sm btn-outline-primary w-100">
                            Ver todas ({{ $matches->count() }})
                        </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para Delegar ao Admin -->
<div class="modal fade" id="delegateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delegar Compra ao Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('purchases.delegate', $purchaseRequest) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Ao delegar ao administrador, você define um preço máximo. O admin ficará com a diferença ou 20% do valor máximo.</p>
                    <div class="mb-3">
                        <label for="max_price" class="form-label fw-semibold">Preço Máximo (R$) *</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" 
                               step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Delegar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection
