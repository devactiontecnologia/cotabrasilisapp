@extends('admin.layout')

@section('title', 'Gerenciar Hotéis')
@section('page-title', 'Gerenciar Hotéis')

@section('content')
<!-- Header Section -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-3">
    <div>
        <h2 class="h4 mb-1 fw-bold">
            <i class="bi bi-building text-primary"></i>
            Gerenciar Hotéis
        </h2>
    </div>
    <a href="{{ route('admin.hotels.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Novo Hotel
    </a>
</div>

<!-- Statistics Cards -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 hotel-stats-card">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="stats-icon text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Total</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $hotels->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 hotel-stats-card">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="stats-icon text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Ativos</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $hotels->where('is_active', true)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 hotel-stats-card">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="stats-icon text-warning">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Inativos</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $hotels->where('is_active', false)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 hotel-stats-card">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="stats-icon text-info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Cotas</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $hotels->sum('quotas_count') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="card shadow-sm border-0 hotel-table-card">
    <div class="card-header bg-white border-bottom py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-list-ul text-primary me-2"></i>
                Lista de Hotéis
            </h6>
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white border-end-0 py-1">
                    <i class="bi bi-search text-muted" style="font-size: 0.875rem;"></i>
                </span>
                <input type="text" class="form-control border-start-0 py-1" id="searchInput" placeholder="Buscar hotel..." style="font-size: 0.875rem;">
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 hotel-table" id="hotelsTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-start py-2 px-2" style="width: 50px; font-size: 0.75rem;">ID</th>
                        <th class="text-start py-2 px-2" style="min-width: 180px; font-size: 0.75rem;">Nome</th>
                        <th class="text-start py-2 px-2" style="min-width: 100px; font-size: 0.75rem;">Localização</th>
                        <th class="text-start py-2 px-2" style="min-width: 150px; font-size: 0.75rem;">Endereço</th>
                        <th class="text-start py-2 px-2" style="min-width: 100px; font-size: 0.75rem;">Telefone</th>
                        <th class="text-start py-2 px-2" style="min-width: 80px; font-size: 0.75rem;">Avaliação</th>
                        <th class="text-center py-2 px-2" style="min-width: 70px; font-size: 0.75rem;">Cotas</th>
                        <th class="text-center py-2 px-2" style="min-width: 80px; font-size: 0.75rem;">Status</th>
                        <th class="text-start py-2 px-2" style="min-width: 100px; font-size: 0.75rem;">Criado em</th>
                        <th class="text-center py-2 px-2" style="min-width: 150px; font-size: 0.75rem;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hotels as $hotel)
                    <tr class="hotel-row">
                        <td class="text-start py-1 px-2">
                            <span class="badge bg-secondary rounded-pill" style="font-size: 0.7rem;">#{{ $hotel->id }}</span>
                        </td>
                        <td class="text-start py-1 px-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary bg-gradient text-white me-2" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                    {{ substr($hotel->name, 0, 1) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size: 0.875rem;">{{ $hotel->name }}</div>
                                    @if($hotel->website)
                                    <small class="text-muted d-flex align-items-center" style="font-size: 0.7rem;">
                                        <i class="bi bi-globe me-1"></i>
                                        <a href="{{ $hotel->website }}" target="_blank" class="text-decoration-none text-muted">
                                            {{ parse_url($hotel->website, PHP_URL_HOST) }}
                                        </a>
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-start py-1 px-2" style="font-size: 0.875rem;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt text-muted me-1" style="font-size: 0.75rem;"></i>
                                <span class="text-truncate" style="max-width: 100px;" title="{{ $hotel->location }}">
                                    {{ $hotel->location }}
                                </span>
                            </div>
                        </td>
                        <td class="text-start py-1 px-2" style="font-size: 0.875rem;">
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $hotel->address }}">
                                <i class="bi bi-house text-muted me-1" style="font-size: 0.75rem;"></i>
                                {{ $hotel->address }}
                            </div>
                        </td>
                        <td class="text-start py-1 px-2" style="font-size: 0.875rem;">
                            @if($hotel->phone)
                                <a href="tel:{{ $hotel->phone }}" class="text-decoration-none">
                                    <i class="bi bi-telephone text-primary me-1" style="font-size: 0.75rem;"></i>
                                    {{ $hotel->phone }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-start py-1 px-2">
                            @if($hotel->rating)
                                <div class="d-flex align-items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $hotel->rating ? '-fill' : '' }} text-warning" style="font-size: 0.7rem;"></i>
                                    @endfor
                                    <span class="small text-muted ms-1" style="font-size: 0.7rem;">{{ number_format($hotel->rating, 1) }}</span>
                                </div>
                            @else
                                <span class="badge bg-light text-muted" style="font-size: 0.65rem;">-</span>
                            @endif
                        </td>
                        <td class="text-center py-1 px-2">
                            <span class="badge bg-info-subtle text-info-emphasis" style="font-size: 0.7rem;">
                                {{ $hotel->quotas_count }}
                            </span>
                        </td>
                        <td class="text-center py-1 px-2">
                            @if($hotel->is_active)
                                <span class="badge bg-success-subtle text-success-emphasis" style="font-size: 0.7rem;">
                                    Ativo
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger-emphasis" style="font-size: 0.7rem;">
                                    Inativo
                                </span>
                            @endif
                        </td>
                        <td class="text-start py-1 px-2" style="font-size: 0.8rem;">
                            {{ $hotel->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-center py-1 px-2">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('admin.hotels.show', $hotel) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="Visualizar"
                                   style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-eye" style="font-size: 0.8rem;"></i>
                                </a>
                                <a href="{{ route('admin.hotels.edit', $hotel) }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   title="Editar"
                                   style="padding: 0.25rem 0.5rem;">
                                    <i class="bi bi-pencil" style="font-size: 0.8rem;"></i>
                                </a>
                                
                                <form method="POST" action="{{ route('admin.hotels.toggle-active', $hotel) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-{{ $hotel->is_active ? 'danger' : 'success' }}" 
                                            title="{{ $hotel->is_active ? 'Desativar' : 'Ativar' }}"
                                            style="padding: 0.25rem 0.5rem;">
                                        <i class="bi bi-{{ $hotel->is_active ? 'pause-fill' : 'play-fill' }}" style="font-size: 0.8rem;"></i>
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" 
                                      class="d-inline" 
                                      onsubmit="return confirm('⚠️ Tem certeza que deseja remover este hotel permanentemente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir" style="padding: 0.25rem 0.5rem;">
                                        <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div data-aos="zoom-in">
                                <i class="bi bi-building text-muted" style="font-size: 4rem;"></i>
                                <h5 class="mt-3 text-muted">Nenhum hotel encontrado</h5>
                                <p class="text-muted mb-4">Comece adicionando seu primeiro hotel ao sistema</p>
                                <a href="{{ route('admin.hotels.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Adicionar Hotel
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($hotels->hasPages())
    <div class="card-footer bg-white border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Mostrando {{ $hotels->firstItem() }} a {{ $hotels->lastItem() }} de {{ $hotels->total() }} resultados
            </div>
            <div>
                {{ $hotels->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<style>
/* Hotel Statistics Cards */
.hotel-stats-card {
    transition: transform 0.2s ease;
}

.hotel-stats-card:hover {
    transform: translateY(-2px);
}

.hotel-stats-card .stats-icon {
    font-size: 1.5rem;
    opacity: 0.9;
}

/* Avatar Circle */
.avatar-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Hotel Table */
.hotel-table-card {
    border-radius: 8px;
}

.hotel-table-card .card-header {
    background: #f8f9fa;
}

.hotel-table tbody tr {
    transition: background-color 0.15s ease;
}

.hotel-table tbody tr:hover {
    background-color: #f8f9fa !important;
}

.hotel-table th {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    white-space: nowrap;
}

/* Action Buttons */
.hotel-table .btn {
    border-radius: 4px;
    transition: all 0.15s ease;
    border-width: 1px;
}

.hotel-table .btn:hover {
    transform: translateY(-1px);
}

/* Badge Styles */
.badge {
    font-weight: 500;
}

/* Empty State */
.hotel-table .text-center {
    min-height: 300px;
}

/* Search Highlight */
.search-highlight {
    background-color: #fef3c7;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Pagination */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    border-radius: 4px;
    margin: 0 1px;
    border-color: #e2e8f0;
    color: #64748b;
    padding: 0.375rem 0.75rem;
}

.pagination .page-link:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
}

.pagination .page-item.active .page-link {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('.hotel-row');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection