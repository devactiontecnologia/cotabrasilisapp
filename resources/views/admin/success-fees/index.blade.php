@extends('admin.layout')

@section('title', 'Taxas de Êxito')
@section('page-title', 'Taxas de Êxito')

@section('content')
<!-- Header Section -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-3">
    <div>
        <h2 class="h4 mb-1 fw-bold">
            <i class="bi bi-currency-dollar text-primary"></i>
            Taxas de Êxito
        </h2>
        <p class="text-muted mb-0">Gerencie as taxas de êxito por perfil e número de dias</p>
    </div>
    <a href="{{ route('admin.success-fees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Nova Taxa
    </a>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.success-fees.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="profile_type" class="form-label">Tipo de Perfil</label>
                <select name="profile_type" id="profile_type" class="form-select">
                    <option value="">Todos os perfis</option>
                    @foreach($profileTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('profile_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="is_active" class="form-label">Status</label>
                <select name="is_active" id="is_active" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="{{ route('admin.success-fees.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Success Message -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistics Cards -->
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="text-primary">
                        <i class="bi bi-list-ul" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Total</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $successFees->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="text-success">
                        <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Ativas</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $successFees->where('is_active', true)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="text-info">
                        <i class="bi bi-person-badge" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Perfis</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $successFees->groupBy('profile_type')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center">
                    <div class="text-warning">
                        <i class="bi bi-calendar-day" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="ms-2">
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Tipos de Dias</p>
                        <h4 class="mb-0 fw-bold" style="font-size: 1.25rem;">{{ $successFees->unique('days')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card shadow-sm border-0">
    <div class="card-body">
        @if($successFees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Perfil</th>
                            <th>Dias</th>
                            <th>Valor da Taxa</th>
                            <th>Ordem</th>
                            <th>Status</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($successFees as $fee)
                            <tr>
                                <td>{{ $fee->id }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $fee->profile_type_name }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $fee->days }}</strong> dia(s)
                                </td>
                                <td>
                                    <strong class="text-success">{{ $fee->formatted_fee }}</strong>
                                </td>
                                <td>{{ $fee->order }}</td>
                                <td>
                                    @if($fee->is_active)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ Str::limit($fee->description ?? 'Sem descrição', 30) }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.success-fees.show', $fee) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.success-fees.edit', $fee) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.success-fees.toggle-active', $fee) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-{{ $fee->is_active ? 'warning' : 'success' }}"
                                                    title="{{ $fee->is_active ? 'Desativar' : 'Ativar' }}">
                                                <i class="bi bi-{{ $fee->is_active ? 'pause' : 'play' }}-fill"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.success-fees.destroy', $fee) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir esta taxa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $successFees->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Nenhuma taxa de êxito cadastrada</p>
                <a href="{{ route('admin.success-fees.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Criar Primeira Taxa
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
