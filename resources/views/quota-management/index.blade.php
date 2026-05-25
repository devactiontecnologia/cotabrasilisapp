@extends('layouts.app')

@section('title', 'Gestão de Cotas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-hotel me-2"></i>Gestão de Cotas
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('quota-management.search') }}" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Buscar Cotas
                    </a>
                    <a href="{{ route('quota-management.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Nova Cota
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total de Cotas</h6>
                                    <h3 class="mb-0">{{ $quotas->total() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-hotel fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Publicadas</h6>
                                    <h3 class="mb-0">{{ $quotas->where('is_published', true)->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-eye fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Ofertas Ativas</h6>
                                    <h3 class="mb-0">{{ $quotas->sum(function($quota) { return $quota->activeRentalOffers->count(); }) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tags fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Receita Total</h6>
                                    <h3 class="mb-0">R$ {{ number_format($quotas->sum('rental_price'), 2, ',', '.') }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('quota-management.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspenso</option>
                                <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferido</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="payment_status" class="form-label">Pagamento</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="">Todos</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Quitado</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Não Quitado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="seasonality" class="form-label">Sazonalidade</label>
                            <select name="seasonality" id="seasonality" class="form-select">
                                <option value="">Todas</option>
                                <option value="low" {{ request('seasonality') == 'low' ? 'selected' : '' }}>Baixa</option>
                                <option value="medium" {{ request('seasonality') == 'medium' ? 'selected' : '' }}>Média</option>
                                <option value="high" {{ request('seasonality') == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="peak" {{ request('seasonality') == 'peak' ? 'selected' : '' }}>Pico</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="published" class="form-label">Publicação</label>
                            <select name="published" id="published" class="form-select">
                                <option value="">Todas</option>
                                <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Publicadas</option>
                                <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>Não Publicadas</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('quota-management.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quotas Grid -->
            <div class="row">
                @forelse($quotas as $quota)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 quota-card">
                        <div class="position-relative">
                            @if($quota->contract_photo_path)
                            <img src="{{ asset('storage/' . $quota->contract_photo_path) }}" 
                                 class="card-img-top" 
                                 alt="Foto da Cota" 
                                 style="height: 200px; object-fit: cover;">
                            @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-hotel fa-3x text-muted"></i>
                            </div>
                            @endif
                            
                            <!-- Status Badges -->
                            <div class="position-absolute top-0 end-0 p-2">
                                @if($quota->is_published)
                                <span class="badge bg-success">Publicada</span>
                                @else
                                <span class="badge bg-secondary">Não Publicada</span>
                                @endif
                                
                                @if($quota->payment_status == 'paid')
                                <span class="badge bg-success ms-1">Quitada</span>
                                @else
                                <span class="badge bg-warning ms-1">Não Quitada</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $quota->hotel_name }}</h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $quota->location }}
                            </p>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Semanas</small>
                                    <div class="fw-bold">{{ $quota->weeks }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Quartos</small>
                                    <div class="fw-bold">{{ $quota->number_of_rooms }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Sazonalidade</small>
                                    <div class="fw-bold">{{ $quota->getSeasonalityLabel() }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Período</small>
                                    <div class="fw-bold">
                                        {{ \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') }} - 
                                        {{ \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>

                            @if($quota->rental_price)
                            <div class="mb-3">
                                <span class="h5 text-primary">R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                            </div>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('quota-management.show', $quota) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Ver
                                    </a>
                                    
                                    <a href="{{ route('quota-management.edit', $quota) }}" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </a>
                                    
                                    @if($quota->canBePublished())
                                    <form method="POST" action="{{ route('quota-management.publish', $quota) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-eye me-1"></i>Publicar
                                        </button>
                                    </form>
                                    @elseif($quota->is_published)
                                    <form method="POST" action="{{ route('quota-management.unpublish', $quota) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-eye-slash me-1"></i>Despublicar
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Criada em {{ $quota->created_at->format('d/m/Y') }}
                                </small>
                                <small class="text-muted">
                                    {{ $quota->activeRentalOffers->count() }} ofertas ativas
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-hotel fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Nenhuma cota encontrada</h4>
                        <p class="text-muted">Você ainda não possui cotas cadastradas.</p>
                        <a href="{{ route('quota-management.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Criar Primeira Cota
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($quotas->hasPages())
            <div class="mt-4">
                {{ $quotas->links('vendor.pagination.modern') }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.quota-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.quota-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.quota-card .card-img-top {
    border-radius: 0.375rem 0.375rem 0 0;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush
@endsection