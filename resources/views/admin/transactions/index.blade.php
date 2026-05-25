@extends('admin.layout')

@section('title', 'Gerenciar Transações')
@section('page-title', 'Gerenciar Transações')

@section('content')
<div class="transactions-management-page">
    <!-- Statistics Cards -->
    <div class="row mb-4" data-aos="fade-down">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalTransactions }}</div>
                    <div class="stat-label">Total de Transações</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $completedTransactions }}</div>
                    <div class="stat-label">Concluídas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $pendingTransactions }}</div>
                    <div class="stat-label">Pendentes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">R$ {{ number_format($totalValue ?? 0, 2, ',', '.') }}</div>
                    <div class="stat-label">Valor Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card-modern" data-aos="fade-up">
        <div class="card-modern-header warning">
            <div class="section-icon">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div>
                <h3 class="section-title">Lista de Transações</h3>
                <p class="section-subtitle">{{ $transactions->count() }} transações nesta página</p>
            </div>
        </div>
        
        <div class="card-modern-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">Tipo</th>
                            <th class="text-start">Hotel</th>
                            <th class="text-start">Locatário</th>
                            <th class="text-start">Proprietário</th>
                            <th class="text-start">Valor</th>
                            <th class="text-center">Status</th>
                            <th class="text-start">Data</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr class="table-row-hover">
                            <td>
                                <span class="badge-custom">#{{ $transaction->id }}</span>
                            </td>
                            <td>
                                <span class="type-badge bg-{{ $transaction->transaction_type === 'rental' ? 'primary' : 'info' }}">
                                    <i class="bi bi-{{ $transaction->transaction_type === 'rental' ? 'currency-dollar' : 'arrow-left-right' }}"></i>
                                    {{ $transaction->transaction_type === 'rental' ? 'Locação' : 'Troca' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-small">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <span>{{ Str::limit($transaction->quota->hotel_name, 20) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-xs bg-gradient-primary">
                                        {{ substr($transaction->renter->name, 0, 1) }}
                                    </div>
                                    <span class="ms-2">{{ Str::limit($transaction->renter->name, 15) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-xs bg-gradient-success">
                                        {{ substr($transaction->owner->name, 0, 1) }}
                                    </div>
                                    <span class="ms-2">{{ Str::limit($transaction->owner->name, 15) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="price-badge">R$ {{ number_format($transaction->total_amount ?? 0, 2, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge status-{{ $transaction->status }}">
                                    @if($transaction->status === 'completed')
                                        <i class="bi bi-check-circle"></i>
                                    @elseif($transaction->status === 'pending')
                                        <i class="bi bi-clock"></i>
                                    @elseif($transaction->status === 'cancelled')
                                        <i class="bi bi-x-circle"></i>
                                    @endif
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="date-cell">
                                    <i class="bi bi-calendar text-muted me-1"></i>
                                    <small>{{ $transaction->created_at->format('d/m/Y') }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn-icon btn-primary" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-receipt"></i>
                                    <h5>Nenhuma transação encontrada</h5>
                                    <p>Não há transações registradas no sistema</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($transactions->hasPages())
        <div class="card-modern-footer">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.transactions-management-page {
    padding-bottom: 2rem;
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.stat-card.stat-primary {
    border-left-color: #667eea;
}

.stat-card.stat-success {
    border-left-color: #10b981;
}

.stat-card.stat-warning {
    border-left-color: #f59e0b;
}

.stat-card.stat-info {
    border-left-color: #06b6d4;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
}

.stat-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-info .stat-icon {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Card Modern */
.card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.card-modern-header {
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.section-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
}

.section-subtitle {
    font-size: 0.875rem;
    opacity: 0.9;
    margin: 0;
}

.card-modern-body {
    padding: 2rem;
}

/* Modern Table */
.modern-table {
    width: 100%;
    margin: 0;
}

.modern-table thead {
    background: #f8fafc;
}

.modern-table th {
    padding: 1rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    border-bottom: 2px solid #e2e8f0;
}

.modern-table td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-row-hover {
    transition: all 0.3s ease;
}

.table-row-hover:hover {
    background: #f8fafc;
}

.badge-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.875rem;
}

.type-badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
}

.icon-small {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    color: #667eea;
}

.user-avatar-xs {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
    flex-shrink: 0;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.price-badge {
    font-weight: 700;
    color: #10b981;
    font-size: 1rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.date-cell {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
}

.btn-icon.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}

.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
}

.empty-state h5 {
    color: #64748b;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #94a3b8;
    margin: 0;
}

.card-modern-footer {
    padding: 1.5rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: center;
}

/* Responsive */
@media (max-width: 992px) {
    .stat-card {
        padding: 1.25rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }

    .modern-table {
        font-size: 0.875rem;
    }

    .modern-table th,
    .modern-table td {
        padding: 0.75rem;
    }
}

@media (max-width: 768px) {
    .card-modern-body {
        padding: 1rem;
    }

    .modern-table {
        font-size: 0.75rem;
    }
}
</style>

<script>
if (typeof AOS !== 'undefined') {
    AOS.init({
        duration: 800,
        easing: 'ease-out-quart',
        once: true,
        offset: 100
    });
}
</script>
@endsection
