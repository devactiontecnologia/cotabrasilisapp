@extends('admin.layout')

@section('title', 'Dashboard Administrativo')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="admin-card stats-card h-100" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stats-label">Total de Usuários</div>
                        <div class="stats-number">{{ $stats['total_users'] }}</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="admin-card stats-card success h-100" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stats-label">Usuários Ativos</div>
                        <div class="stats-number">{{ $stats['active_users'] }}</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="admin-card stats-card warning h-100" data-aos="fade-up" data-aos-delay="300">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stats-label">Total de Cotas</div>
                        <div class="stats-number">{{ $stats['total_quotas'] }}</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="admin-card stats-card danger h-100" data-aos="fade-up" data-aos-delay="400">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stats-label">Transações</div>
                        <div class="stats-number">{{ $stats['total_transactions'] }}</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recent Quotas -->
    <div class="col-xl-6 col-lg-7">
        <div class="admin-card" data-aos="fade-up" data-aos-delay="500">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-check me-2"></i>
                    Cotas Recentes
                </h6>
                <a href="{{ route('admin.quotas.index') }}" class="btn btn-admin btn-admin-primary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    Ver Todas
                </a>
            </div>
            <div class="card-body">
                <div class="admin-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hotel</th>
                                <th>Usuário</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_quotas as $quota)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ substr($quota->hotel_name, 0, 1) }}
                                        </div>
                                        {{ $quota->hotel_name }}
                                    </div>
                                </td>
                                <td>{{ $quota->user->name }}</td>
                                <td>
                                    <span class="badge-admin bg-{{ $quota->status === 'available' ? 'success' : ($quota->status === 'rented' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($quota->status) }}
                                    </span>
                                </td>
                                <td>{{ $quota->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-calendar-check fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Nenhuma cota encontrada</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-xl-6 col-lg-5">
        <div class="admin-card" data-aos="fade-up" data-aos-delay="600">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-people me-2"></i>
                    Usuários Recentes
                </h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-admin btn-admin-primary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                <div class="admin-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge-admin bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                        {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-people fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Nenhum usuário encontrado</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Transactions -->
    <div class="col-xl-8 col-lg-7">
        <div class="admin-card" data-aos="fade-up" data-aos-delay="700">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-arrow-left-right me-2"></i>
                    Transações Recentes
                </h6>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-admin btn-admin-primary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    Ver Todas
                </a>
            </div>
            <div class="card-body">
                <div class="admin-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Hotel</th>
                                <th>Locatário</th>
                                <th>Proprietário</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_transactions as $transaction)
                            <tr>
                                <td>
                                    <span class="badge-admin bg-{{ $transaction->type === 'rental' ? 'primary' : 'info' }}">
                                        {{ $transaction->type === 'rental' ? 'Locação' : 'Troca' }}
                                    </span>
                                </td>
                                <td>{{ $transaction->quota->hotel_name }}</td>
                                <td>{{ $transaction->renter->name }}</td>
                                <td>{{ $transaction->owner->name }}</td>
                                <td>
                                    <span class="badge-admin bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-arrow-left-right fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Nenhuma transação encontrada</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Admin Logs -->
    <div class="col-xl-4 col-lg-5">
        <div class="admin-card" data-aos="fade-up" data-aos-delay="800">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-journal-text me-2"></i>
                    Logs Recentes
                </h6>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-admin btn-admin-primary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                <div class="admin-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ação</th>
                                <th>Admin</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_logs as $log)
                            <tr>
                                <td>
                                    <span class="badge-admin bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'updated' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>{{ $log->admin->name }}</td>
                                <td>{{ $log->created_at->format('d/m H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <i class="bi bi-journal-text fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Nenhum log encontrado</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>
@endsection