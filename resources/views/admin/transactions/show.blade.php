@extends('admin.layout')

@section('title', 'Detalhes da Transação')
@section('page-title', 'Detalhes da Transação')

@section('content')
<div class="transaction-details-page">
    <!-- Header Actions -->
    <div class="page-header mb-4" data-aos="fade-down">
        <div class="header-content">
            <div class="header-title-section">
                <div class="icon-wrapper bg-gradient-info">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h1 class="page-title">Transação #{{ $transaction->id }}</h1>
                    <p class="page-subtitle">
                        <i class="bi bi-{{ $transaction->transaction_type === 'rental' ? 'currency-dollar' : 'arrow-left-right' }} me-2"></i>
                        {{ $transaction->transaction_type === 'rental' ? 'Locação' : 'Troca' }}
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.transactions.index') }}" class="btn-outline">
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
            <div class="status-banner mb-4 {{ $transaction->status }}" data-aos="fade-up">
                <div class="status-content">
                    <div class="status-icon">
                        <i class="bi bi-{{ $transaction->status === 'completed' ? 'check-circle-fill' : ($transaction->status === 'pending' ? 'clock-fill' : 'x-circle-fill') }}"></i>
                    </div>
                    <div class="status-info">
                        <h5 class="mb-1 fw-bold">{{ ucfirst($transaction->status) }}</h5>
                        <p class="mb-0 text-muted">
                            @if($transaction->status === 'completed')
                                Esta transação foi concluída com sucesso
                            @elseif($transaction->status === 'pending')
                                Esta transação está aguardando processamento
                            @else
                                Esta transação foi cancelada
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informações da Transação -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-modern-header primary">
                    <div class="section-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Informações da Transação</h3>
                        <p class="section-subtitle">Dados principais</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon bg-primary-subtle">
                                <i class="bi bi-hash text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>ID da Transação</label>
                                <span class="badge-custom">#{{ $transaction->id }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-{{ $transaction->transaction_type === 'rental' ? 'success' : 'info' }}-subtle">
                                <i class="bi bi-{{ $transaction->transaction_type === 'rental' ? 'currency-dollar' : 'arrow-left-right' }} text-{{ $transaction->transaction_type === 'rental' ? 'success' : 'info' }}"></i>
                            </div>
                            <div class="info-content">
                                <label>Tipo</label>
                                <span>{{ $transaction->transaction_type === 'rental' ? 'Locação' : 'Troca' }}</span>
                            </div>
                        </div>

                        @if($transaction->total_amount)
                        <div class="info-item">
                            <div class="info-icon bg-success-subtle">
                                <i class="bi bi-tag text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Valor Total</label>
                                <span class="price-amount">R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif

                        @if($transaction->owner_amount)
                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-wallet text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Valor do Proprietário</label>
                                <span class="price-amount">R$ {{ number_format($transaction->owner_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif

                        @if($transaction->platform_fee)
                        <div class="info-item">
                            <div class="info-icon bg-info-subtle">
                                <i class="bi bi-percent text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Taxa da Plataforma</label>
                                <span class="price-amount">R$ {{ number_format($transaction->platform_fee, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="info-item">
                            <div class="info-icon bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}-subtle">
                                <i class="bi bi-{{ $transaction->status === 'completed' ? 'check-circle' : ($transaction->status === 'pending' ? 'clock' : 'x-circle') }} text-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}"></i>
                            </div>
                            <div class="info-content">
                                <label>Status</label>
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($transaction->status) }}</span>
                            </div>
                        </div>
                    </div>

                    @if($transaction->transaction_date)
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-2"></i>
                            Data da Transação: {{ $transaction->transaction_date->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informações da Cota -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-modern-header success">
                    <div class="section-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Cota</h3>
                        <p class="section-subtitle">Informações da cota relacionada</p>
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
                                <span>{{ $transaction->quota->hotel_name }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-primary-subtle">
                                <i class="bi bi-geo-alt text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>Localização</label>
                                <span>{{ $transaction->quota->location }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-info-subtle">
                                <i class="bi bi-calendar-range text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Período</label>
                                <span>{{ $transaction->quota->start_date->format('d/m/Y') }} - {{ $transaction->quota->end_date->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-people text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Número de Hóspedes</label>
                                <span>{{ $transaction->quota->number_of_guests }} pessoas</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="info-grid">
                            @foreach($transaction->quota->getRegistrationDetailsForDisplay() as $detail)
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

                    <div class="mt-3">
                        <a href="{{ route('admin.quotas.show', $transaction->quota) }}" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-2"></i>
                            Ver Detalhes da Cota
                        </a>
                    </div>
                </div>
            </div>

            <!-- Participantes -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-modern-header info">
                    <div class="section-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Participantes</h3>
                        <p class="section-subtitle">Locatário e proprietário</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="info-grid">
                        <div class="info-item full-width">
                            <div class="participant-avatar bg-gradient-primary">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="info-content">
                                <label>Locatário</label>
                                <h6 class="mb-1">{{ $transaction->renter->name }}</h6>
                                <p class="text-muted mb-0">{{ $transaction->renter->email }}</p>
                            </div>
                        </div>

                        <div class="info-item full-width">
                            <div class="participant-avatar bg-gradient-success">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="info-content">
                                <label>Proprietário</label>
                                <h6 class="mb-1">{{ $transaction->owner->name }}</h6>
                                <p class="text-muted mb-0">{{ $transaction->owner->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                            <div class="info-label">ID</div>
                            <div class="info-value">#{{ $transaction->id }}</div>
                        </div>

                        <div class="system-info-item">
                            <i class="bi bi-plus-circle text-success"></i>
                            <div class="info-label">Criado em</div>
                            <div class="info-value">{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div class="system-info-item">
                            <i class="bi bi-clock text-info"></i>
                            <div class="info-label">Última atualização</div>
                            <div class="info-value">{{ $transaction->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Card de Pagamento -->
                <div class="card-modern mb-4" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-modern-header success">
                        <div class="section-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Pagamento</h3>
                            <p class="section-subtitle">Status e detalhes</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="status-display">
                            <div class="status-badge-large status-{{ $transaction->payment_status ?? 'pending' }}">
                                <i class="bi bi-{{ $transaction->payment_status === 'completed' ? 'check-circle' : ($transaction->payment_status === 'pending' ? 'clock' : 'x-circle') }}"></i>
                                <div>
                                    <span class="status-title">{{ ucfirst($transaction->payment_status ?? 'pending') }}</span>
                                    <span class="status-desc">
                                        @if($transaction->payment_status === 'completed')
                                            Pagamento confirmado
                                        @elseif($transaction->payment_status === 'pending')
                                            Aguardando pagamento
                                        @else
                                            Pagamento falhou
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($transaction->payment_method)
                        <div class="mt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted">Método</span>
                                <span class="badge bg-info">{{ ucfirst($transaction->payment_method) }}</span>
                            </div>
                        </div>
                        @endif

                        @if($transaction->payment_reference)
                        <div class="mt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted">Referência</span>
                                <span class="small text-primary">{{ $transaction->payment_reference }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.transaction-details-page {
    padding-bottom: 2rem;
}

/* Reusing styles from quota details with specific adjustments */
.page-header {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 40px rgba(6, 182, 212, 0.3);
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

/* Status Banner */
.status-banner {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    color: white;
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.3);
}

.status-banner.pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.status-banner.cancelled {
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

/* Card Modern */
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
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.badge-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 700;
    display: inline-block;
}

/* Participant */
.participant-avatar {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.info-content h6 {
    color: #1e293b;
    font-weight: 700;
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

.status-completed .status-badge-large {
    background: #d1fae5;
}

.status-completed .status-badge-large i {
    color: #10b981;
}

.status-pending .status-badge-large {
    background: #fef3c7;
}

.status-pending .status-badge-large i {
    color: #f59e0b;
}

.status-failed .status-badge-large {
    background: #fee2e2;
}

.status-failed .status-badge-large i {
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

/* Responsive */
@media (max-width: 992px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-grid {
        grid-template-columns: 1fr;
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


