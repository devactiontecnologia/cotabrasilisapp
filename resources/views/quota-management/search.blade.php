@extends('layouts.app')

@section('title', 'Buscar Cotas')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-search me-2"></i>Buscar Cotas
                </h1>
                <a href="{{ route('quota-management.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Minhas Cotas
                </a>
            </div>

            <!-- Search Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-filter me-2"></i>Filtros de Busca
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('quota-management.search') }}" id="searchForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" name="city" id="city" class="form-control" 
                                       value="{{ request('city') }}" placeholder="Ex: São Paulo">
                            </div>
                            <div class="col-md-3">
                                <label for="state" class="form-label">Estado</label>
                                <select name="state" id="state" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="SP" {{ request('state') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                                    <option value="RJ" {{ request('state') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                                    <option value="MG" {{ request('state') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                                    <option value="RS" {{ request('state') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                                    <option value="PR" {{ request('state') == 'PR' ? 'selected' : '' }}>Paraná</option>
                                    <option value="SC" {{ request('state') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                                    <option value="BA" {{ request('state') == 'BA' ? 'selected' : '' }}>Bahia</option>
                                    <option value="PE" {{ request('state') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                                    <option value="CE" {{ request('state') == 'CE' ? 'selected' : '' }}>Ceará</option>
                                    <option value="DF" {{ request('state') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="hotel_id" class="form-label">Hotel</label>
                                <select name="hotel_id" id="hotel_id" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                        {{ $hotel->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="weeks" class="form-label">Semanas</label>
                                <select name="weeks" id="weeks" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="1" {{ request('weeks') == '1' ? 'selected' : '' }}>1 Semana</option>
                                    <option value="2" {{ request('weeks') == '2' ? 'selected' : '' }}>2 Semanas</option>
                                    <option value="3" {{ request('weeks') == '3' ? 'selected' : '' }}>3 Semanas</option>
                                    <option value="4" {{ request('weeks') == '4' ? 'selected' : '' }}>4 Semanas</option>
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
                                <label for="max_price" class="form-label">Preço Máximo (R$)</label>
                                <input type="number" name="max_price" id="max_price" class="form-control" 
                                       value="{{ request('max_price') }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Data Início</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" 
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Data Fim</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" 
                                       value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="number_of_rooms" class="form-label">Mín. Quartos</label>
                                <select name="number_of_rooms" id="number_of_rooms" class="form-select">
                                    <option value="">Qualquer</option>
                                    @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ request('number_of_rooms') == $i ? 'selected' : '' }}>
                                        {{ $i }} Quarto{{ $i > 1 ? 's' : '' }}
                                    </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Buscar
                                </button>
                                <a href="{{ route('quota-management.search') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Count -->
            @if(request()->hasAny(['city', 'state', 'hotel_id', 'weeks', 'seasonality', 'max_price', 'start_date', 'end_date', 'number_of_rooms']))
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                Encontradas <strong>{{ $quotas->total() }}</strong> cota{{ $quotas->total() != 1 ? 's' : '' }} 
                com os filtros aplicados.
            </div>
            @endif

            <!-- Results Grid -->
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
                                <span class="badge bg-success">Disponível</span>
                                <span class="badge bg-{{ $quota->payment_status == 'paid' ? 'success' : 'warning' }} ms-1">
                                    {{ $quota->getPaymentStatusLabel() }}
                                </span>
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
                                        <i class="fas fa-eye me-1"></i>Ver Detalhes
                                    </a>
                                    
                                    <button type="button" class="btn btn-outline-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#contactModal{{ $quota->id }}">
                                        <i class="fas fa-envelope me-1"></i>Contatar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $quota->user->name }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $quota->created_at->format('d/m/Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Modal -->
                <div class="modal fade" id="contactModal{{ $quota->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Contatar Proprietário</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Entre em contato com <strong>{{ $quota->user->name }}</strong> sobre a cota:</p>
                                <p><strong>{{ $quota->hotel_name }}</strong> - {{ $quota->location }}</p>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>E-mail:</strong> {{ $quota->user->email }}
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Lembre-se de sempre verificar a autenticidade das informações antes de fechar qualquer negócio.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <a href="mailto:{{ $quota->user->email }}" class="btn btn-primary">
                                    <i class="fas fa-envelope me-1"></i>Enviar E-mail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Nenhuma cota encontrada</h4>
                        <p class="text-muted">
                            @if(request()->hasAny(['city', 'state', 'hotel_id', 'weeks', 'seasonality', 'max_price', 'start_date', 'end_date', 'number_of_rooms']))
                                Tente ajustar os filtros de busca para encontrar mais resultados.
                            @else
                                Não há cotas disponíveis no momento.
                            @endif
                        </p>
                        @if(request()->hasAny(['city', 'state', 'hotel_id', 'weeks', 'seasonality', 'max_price', 'start_date', 'end_date', 'number_of_rooms']))
                        <a href="{{ route('quota-management.search') }}" class="btn btn-primary">
                            <i class="fas fa-times me-1"></i>Limpar Filtros
                        </a>
                        @endif
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