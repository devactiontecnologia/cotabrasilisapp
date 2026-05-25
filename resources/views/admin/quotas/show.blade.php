@extends('admin.layout')

@section('title', 'Detalhes da Cota')
@section('page-title', 'Detalhes da Cota')

@section('content')
<div class="quota-details-page">
    <!-- Header Actions -->
    <div class="page-header mb-4" data-aos="fade-down">
        <div class="header-content">
            <div class="header-title-section">
                <div class="icon-wrapper bg-gradient-success">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <h1 class="page-title">Cota #{{ $quota->id }}</h1>
                    <p class="page-subtitle">
                        <i class="bi bi-building me-2"></i>
                        {{ $quota->hotel_name }} - {{ $quota->location }}
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.quotas.index') }}" class="btn-outline">
                    <i class="bi bi-arrow-left me-2"></i>
                    Voltar para Lista
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            
            <!-- Status Card -->
            <div class="status-banner mb-4" data-aos="fade-up">
                <div class="status-content">
                    <div class="status-icon">
                        <i class="bi bi-{{ $quota->status === 'available' ? 'check-circle-fill' : ($quota->status === 'rented' ? 'clock-fill' : 'x-circle-fill') }}"></i>
                    </div>
                    <div class="status-info">
                        <h5 class="mb-1 fw-bold">{{ ucfirst($quota->status) }}</h5>
                        <p class="mb-0 text-muted">
                            @if($quota->status === 'available')
                                Esta cota está disponível para aluguel ou troca
                            @elseif($quota->status === 'rented')
                                Esta cota foi alugada e está em uso
                            @else
                                Esta cota foi cancelada
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informações da Cota -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-modern-header success">
                    <div class="section-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Informações da Cota</h3>
                        <p class="section-subtitle">Dados básicos e período</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon bg-success-subtle">
                                <i class="bi bi-building text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Hotel</label>
                                <span>{{ $quota->hotel_name }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-primary-subtle">
                                <i class="bi bi-geo-alt text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>Localização</label>
                                <span>{{ $quota->location }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-info-subtle">
                                <i class="bi bi-calendar-range text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Período</label>
                                <span>{{ $quota->start_date->format('d/m/Y') }} - {{ $quota->end_date->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-people text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Número de Hóspedes</label>
                                <span>{{ $quota->number_of_guests }} pessoas</span>
                            </div>
                        </div>

                        @if($quota->number_of_rooms)
                        <div class="info-item">
                            <div class="info-icon bg-danger-subtle">
                                <i class="bi bi-door-open text-danger"></i>
                            </div>
                            <div class="info-content">
                                <label>Número de Quartos</label>
                                <span>{{ $quota->number_of_rooms }}</span>
                            </div>
                        </div>
                        @endif

                        @if($quota->weeks)
                        <div class="info-item">
                            <div class="info-icon bg-purple-subtle">
                                <i class="bi bi-calendar-week text-purple"></i>
                            </div>
                            <div class="info-content">
                                <label>Semanas</label>
                                <span>{{ $quota->weeks }} semanas</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($quota->observations)
                    <div class="description-box">
                        <h6 class="section-label">
                            <i class="bi bi-file-text me-2"></i>
                            Observações
                        </h6>
                        <p class="description-text">{{ $quota->observations }}</p>
                    </div>
                    @endif

                    <div class="description-box">
                        <div class="info-grid">
                            @foreach($quota->getRegistrationDetailsForDisplay() as $detail)
                            <div class="info-item">
                                <div class="info-icon bg-success-subtle">
                                    <i class="fas {{ $detail['icon'] ?? 'fa-circle-info' }} text-success"></i>
                                </div>
                                <div class="info-content">
                                    <label>{{ $detail['label'] }}</label>
                                    <span>{{ $detail['value'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Financeiras -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-modern-header info">
                    <div class="section-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Informações Financeiras</h3>
                        <p class="section-subtitle">Valores e transações</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="info-grid">
                        @if($quota->rental_price)
                        <div class="info-item">
                            <div class="info-icon bg-success-subtle">
                                <i class="bi bi-tag text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Preço de Aluguel</label>
                                <span class="price-amount">R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="info-item">
                            <div class="info-icon bg-primary-subtle">
                                <i class="bi bi-arrow-left-right text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>Tipo</label>
                                <span>{{ $quota->is_exchange ? 'Troca' : 'Aluguel' }}</span>
                            </div>
                        </div>

                        @if($quota->seasonality)
                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-graph-up text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Sazonalidade</label>
                                <span>
                                    @if($quota->seasonality === 'low') Baixa Temporada
                                    @elseif($quota->seasonality === 'medium') Média Temporada
                                    @elseif($quota->seasonality === 'high') Alta Temporada
                                    @else Altíssima
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif

                        @if($quota->is_fractioned)
                        <div class="info-item full-width">
                            <div class="info-icon bg-info-subtle">
                                <i class="bi bi-scissors text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Cota Fracionada</label>
                                <span class="badge bg-info">Sim</span>
                                @if($quota->fraction_details)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Detalhes: {{ json_encode($quota->fraction_details, JSON_UNESCAPED_UNICODE) }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informações do Proprietário -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-modern-header primary">
                    <div class="section-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Proprietário da Cota</h3>
                        <p class="section-subtitle">Informações do titular</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="owner-info">
                        <div class="owner-avatar">
                            {{ substr($quota->user->name, 0, 1) }}
                        </div>
                        <div class="owner-details">
                            <h5 class="mb-1">{{ $quota->user->name }}</h5>
                            <p class="text-muted mb-2">{{ $quota->user->email }}</p>
                            @if($quota->user->whatsapp)
                            <p class="mb-0">
                                <i class="bi bi-whatsapp text-success me-2"></i>
                                {{ $quota->user->whatsapp }}
                            </p>
                            @endif
                        </div>
                        <div class="owner-status">
                            <span class="badge bg-{{ $quota->user->is_active ? 'success' : 'danger' }}">
                                {{ $quota->user->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transações -->
            @if($quota->transactions->count() > 0)
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card-modern-header warning">
                    <div class="section-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Transações</h3>
                        <p class="section-subtitle">{{ $quota->transactions->count() }} transação(ões) registrada(s)</p>
                    </div>
                </div>
                
                <div class="card-modern-body p-0">
                    <div class="transactions-list">
                        @foreach($quota->transactions as $transaction)
                        <div class="transaction-item">
                            <div class="transaction-icon">
                                <i class="bi bi-{{ $transaction->transaction_type === 'rental' ? 'currency-dollar' : 'arrow-left-right' }}"></i>
                            </div>
                            <div class="transaction-info">
                                <h6 class="mb-1">
                                    {{ $transaction->transaction_type === 'rental' ? 'Aluguel' : 'Troca' }}
                                    <span class="badge-custom ms-2">{{ ucfirst($transaction->status) }}</span>
                                </h6>
                                <p class="mb-1">
                                    <i class="bi bi-person me-1"></i>
                                    Locatário: {{ $transaction->renter->name }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-person-check me-1"></i>
                                    Proprietário: {{ $transaction->owner->name }}
                                </p>
                                @if($transaction->total_amount)
                                <p class="mb-0 text-success fw-bold">
                                    <i class="bi bi-tag me-1"></i>
                                    Valor: R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}
                                </p>
                                @endif
                            </div>
                            <div class="transaction-date">
                                <small class="text-muted">{{ $transaction->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sidebar-sticky">
                
                <!-- Card de Informações do Sistema -->
                <div class="card-modern mb-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-modern-header primary">
                        <div class="section-icon">
                            <i class="bi bi-database"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Informações</h3>
                            <p class="section-subtitle">Dados do sistema</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="system-info-item">
                            <i class="bi bi-hash text-primary"></i>
                            <div class="info-label">ID da Cota</div>
                            <div class="info-value">#{{ $quota->id }}</div>
                        </div>

                        <div class="system-info-item">
                            <i class="bi bi-plus-circle text-success"></i>
                            <div class="info-label">Criado em</div>
                            <div class="info-value">{{ $quota->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div class="system-info-item">
                            <i class="bi bi-clock text-info"></i>
                            <div class="info-label">Última atualização</div>
                            <div class="info-value">{{ $quota->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Card de Status -->
                <div class="card-modern mb-4" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-modern-header success">
                        <div class="section-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Status</h3>
                            <p class="section-subtitle">Estado da cota</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="status-display">
                            <div class="status-badge-large status-{{ $quota->status }}">
                                <i class="bi bi-{{ $quota->status === 'available' ? 'check-circle' : ($quota->status === 'rented' ? 'clock' : 'x-circle') }}"></i>
                                <div>
                                    <span class="status-title">{{ ucfirst($quota->status) }}</span>
                                    <span class="status-desc">
                                        @if($quota->status === 'available')
                                            Disponível para uso
                                        @elseif($quota->status === 'rented')
                                            Alugada no momento
                                        @else
                                            Cancelada
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($quota->is_published)
                        <div class="mt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted">Publicada</span>
                                <span class="badge bg-success">Sim</span>
                            </div>
                            @if($quota->published_at)
                            <small class="text-muted">Em {{ $quota->published_at->format('d/m/Y') }}</small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Card de Estatísticas -->
                <div class="card-modern mb-4" data-aos="fade-left" data-aos-delay="300">
                    <div class="card-modern-header info">
                        <div class="section-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Estatísticas</h3>
                            <p class="section-subtitle">Dados da cota</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="stat-card-small">
                            <div class="stat-icon-small bg-info-subtle">
                                <i class="bi bi-receipt text-info"></i>
                            </div>
                            <div class="stat-info-small">
                                <div class="stat-number-small">{{ $quota->transactions->count() }}</div>
                                <div class="stat-label-small">Transações</div>
                            </div>
                        </div>

                        <div class="stat-card-small mb-0">
                            <div class="stat-icon-small bg-primary-subtle">
                                <i class="bi bi-currency-dollar text-primary"></i>
                            </div>
                            <div class="stat-info-small">
                                <div class="stat-number-small">
                                    @if($quota->transactions->sum('total_amount') > 0)
                                        R$ {{ number_format($quota->transactions->sum('total_amount'), 2, ',', '.') }}
                                    @else
                                        R$ 0,00
                                    @endif
                                </div>
                                <div class="stat-label-small">Total em Transações</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quota-details-page {
    padding-bottom: 2rem;
}

/* Page Header - Reusing from hotel details */
.page-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.header-title-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.icon-wrapper {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    flex-shrink: 0;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.page-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.btn-outline {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Status Banner - Reusing from hotel details with adjustments */
.status-banner {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    color: white;
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.3);
}

.status-banner.status-rented {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.status-banner.status-cancelled {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.status-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.status-icon {
    font-size: 3rem;
    opacity: 0.9;
}

.status-info {
    flex: 1;
}

/* Card Modern - Reusing from hotel details */
.card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
    margin-bottom: 1.5rem;
}

.card-modern:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.card-modern-header {
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card-modern-header.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-modern-header.info {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.card-modern-header.success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-modern-header.warning {
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

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
}

.info-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.bg-purple-subtle {
    background: #ede9fe;
}

.text-purple {
    color: #8b5cf6;
}

.info-content {
    flex: 1;
}

.info-content label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.info-content span {
    display: block;
    font-weight: 600;
    color: #1e293b;
}

.price-amount {
    font-weight: 700;
    color: #10b981;
    font-size: 1.25rem;
}

/* Description Box */
.description-box {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 12px;
}

.section-label {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.description-text {
    color: #475569;
    line-height: 1.7;
    margin: 0;
}

/* Owner Info */
.owner-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 12px;
}

.owner-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.owner-details {
    flex: 1;
}

.owner-details h5 {
    color: #1e293b;
    font-weight: 700;
}

.owner-status {
    flex-shrink: 0;
}

/* Transactions */
.transactions-list {
    padding: 1rem;
}

.transaction-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.transaction-item:last-child {
    border-bottom: none;
}

.transaction-item:hover {
    background: #f8fafc;
}

.transaction-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.transaction-info {
    flex: 1;
}

.transaction-info h6 {
    color: #1e293b;
    font-weight: 700;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.transaction-date {
    flex-shrink: 0;
}

.badge-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Status Display */
.status-display {
    padding: 1rem;
}

.status-badge-large {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 12px;
}

.status-badge-large i {
    font-size: 2.5rem;
}

.status-title {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
}

.status-desc {
    display: block;
    font-size: 0.875rem;
    color: #64748b;
}

.status-available .status-badge-large {
    background: #d1fae5;
}

.status-available .status-badge-large i {
    color: #10b981;
}

.status-rented .status-badge-large {
    background: #fef3c7;
}

.status-rented .status-badge-large i {
    color: #f59e0b;
}

.status-cancelled .status-badge-large {
    background: #fee2e2;
}

.status-cancelled .status-badge-large i {
    color: #ef4444;
}

/* System Info Items */
.system-info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.system-info-item:last-child {
    margin-bottom: 0;
}

.system-info-item i {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
}

.info-value {
    font-weight: 700;
    color: #1e293b;
}

/* Stat Cards Small */
.stat-card-small {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.stat-card-small:last-child {
    margin-bottom: 0;
}

.stat-icon-small {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-info-small {
    flex: 1;
}

.stat-number-small {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label-small {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Responsive */
@media (max-width: 992px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .owner-info {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 1.5rem;
    }

    .page-title {
        font-size: 1.5rem;
    }

    .status-content {
        flex-direction: column;
        text-align: center;
    }

    .card-modern-body {
        padding: 1.5rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
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


