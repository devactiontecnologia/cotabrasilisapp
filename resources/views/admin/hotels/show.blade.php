@extends('admin.layout')

@section('title', 'Detalhes do Hotel')
@section('page-title', 'Detalhes do Hotel')

@section('content')
<div class="hotel-details-page">
    <!-- Header Actions -->
    <div class="page-header mb-4" data-aos="fade-down">
        <div class="header-content">
            <div class="header-title-section">
                <div class="icon-wrapper bg-gradient-primary">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h1 class="page-title">{{ $hotel->name }}</h1>
                    <p class="page-subtitle">
                        <i class="bi bi-geo-alt me-2"></i>
                        {{ $hotel->location }}
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.hotels.index') }}" class="btn-outline">
                    <i class="bi bi-arrow-left me-2"></i>
                    Voltar para Lista
                </a>
                <a href="{{ route('admin.hotels.edit', $hotel) }}" class="btn-primary">
                    <i class="bi bi-pencil me-2"></i>
                    Editar Hotel
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
                        <i class="bi bi-{{ $hotel->is_active ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                    </div>
                    <div class="status-info">
                        <h5 class="mb-1 fw-bold">{{ $hotel->is_active ? 'Hotel Ativo' : 'Hotel Inativo' }}</h5>
                        <p class="mb-0 text-muted">
                            {{ $hotel->is_active ? 'Este hotel está visível e funcionando no sistema' : 'Este hotel está oculto do sistema' }}
                        </p>
                    </div>
                    <div class="status-action">
                        <form method="POST" action="{{ route('admin.hotels.toggle-active', $hotel) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn-toggle-status">
                                {{ $hotel->is_active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informações Principais -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-modern-header primary">
                    <div class="section-icon">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Informações Principais</h3>
                        <p class="section-subtitle">Dados básicos do hotel</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon bg-primary-subtle">
                                <i class="bi bi-hash text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>ID do Hotel</label>
                                <span class="badge-custom">#{{ $hotel->id }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-success-subtle">
                                <i class="bi bi-building text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Nome</label>
                                <span>{{ $hotel->name }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon bg-info-subtle">
                                <i class="bi bi-geo-alt text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Localização</label>
                                <span>{{ $hotel->location }}</span>
                            </div>
                        </div>

                        @if($hotel->city)
                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-pin-map text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Cidade</label>
                                <span>{{ $hotel->city }}, {{ $hotel->state }}</span>
                            </div>
                        </div>
                        @endif

                        @if($hotel->zip_code)
                        <div class="info-item">
                            <div class="info-icon bg-danger-subtle">
                                <i class="bi bi-postage text-danger"></i>
                            </div>
                            <div class="info-content">
                                <label>CEP</label>
                                <span>{{ $hotel->zip_code }}</span>
                            </div>
                        </div>
                        @endif

                        @if($hotel->stars)
                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Estrelas</label>
                                <span>{{ $hotel->stars }} Estrelas</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($hotel->description)
                    <div class="description-box">
                        <h6 class="section-label">
                            <i class="bi bi-file-text me-2"></i>
                            Descrição
                        </h6>
                        <p class="description-text">{{ $hotel->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contato -->
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-modern-header info">
                    <div class="section-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Informações de Contato</h3>
                        <p class="section-subtitle">Telefone, e-mail e site</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="info-grid">
                        @if($hotel->phone)
                        <div class="info-item">
                            <div class="info-icon bg-info-subtle">
                                <i class="bi bi-phone text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Telefone</label>
                                <a href="tel:{{ $hotel->phone }}" class="info-link">{{ $hotel->phone }}</a>
                            </div>
                        </div>
                        @endif

                        @if($hotel->email)
                        <div class="info-item">
                            <div class="info-icon bg-primary-subtle">
                                <i class="bi bi-envelope text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>E-mail</label>
                                <a href="mailto:{{ $hotel->email }}" class="info-link">{{ $hotel->email }}</a>
                            </div>
                        </div>
                        @endif

                        @if($hotel->website)
                        <div class="info-item">
                            <div class="info-icon bg-success-subtle">
                                <i class="bi bi-globe text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Website</label>
                                <a href="{{ $hotel->website }}" target="_blank" class="info-link" rel="noopener">
                                    Visitar Site
                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($hotel->rating)
                        <div class="info-item">
                            <div class="info-icon bg-warning-subtle">
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Avaliação</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $hotel->rating ? '-fill' : '' }} text-warning"></i>
                                        @endfor
                                    </div>
                                    <span class="rating-value">{{ number_format($hotel->rating, 1) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Comodidades -->
            @if($hotel->amenities && count($hotel->amenities) > 0)
            @php
                $amenitiesTranslations = [
                    'wifi' => 'Wi-Fi',
                    'room_service' => 'Serviço de Quarto',
                    'heated_pool' => 'Piscina Coberta Aquecida',
                    'gym' => 'Academia',
                    'bowling' => 'Boliche',
                    'business_center' => 'Centro de Negócios',
                    'restaurant' => 'Restaurante',
                    'bar' => 'Bar',
                    'spa' => 'Spa',
                    'concierge' => 'Concierge',
                    'pool' => 'Piscina',
                    'parking' => 'Estacionamento',
                    'wet_bar' => 'Bar Molhado',
                    'pet_friendly' => 'Espaço Pet',
                    'wine_cellar' => 'Adega',
                    'fireplace' => 'Lareira',
                    'bike_rack' => 'Bicicletário',
                    'sports_court' => 'Quadra Poliesportiva',
                    'rooftop' => 'Rooftop',
                ];
            @endphp
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-modern-header warning">
                    <div class="section-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Comodidades</h3>
                        <p class="section-subtitle">{{ count($hotel->amenities) }} comodidades disponíveis</p>
                    </div>
                </div>
                
                <div class="card-modern-body">
                    <div class="amenities-display">
                        @foreach($hotel->amenities as $amenity)
                        @php
                            $amenityKey = str_replace(' ', '_', strtolower($amenity));
                            $amenityLabel = $amenitiesTranslations[$amenityKey] ?? ucwords(str_replace('_', ' ', $amenity));
                        @endphp
                        <div class="amenity-badge">
                            <i class="bi bi-check-circle"></i>
                            <span>{{ $amenityLabel }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Cotas do Hotel -->
            @if($hotel->quotas->count() > 0)
            <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card-modern-header success">
                    <div class="section-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="section-title">Cotas do Hotel</h3>
                        <p class="section-subtitle">{{ $hotel->quotas->count() }} cotas cadastradas</p>
                    </div>
                </div>
                
                <div class="card-modern-body p-0">
                    <div class="table-responsive">
                        <table class="hotel-quotas-table">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Período</th>
                                    <th>Status</th>
                                    <th>Preço</th>
                                    <th>Criada em</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hotel->quotas as $quota)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-small">
                                                {{ substr($quota->user->name, 0, 1) }}
                                            </div>
                                            <span class="ms-2">{{ $quota->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="period-info">
                                            <i class="bi bi-calendar3 text-primary me-1"></i>
                                            <span>{{ $quota->start_date->format('d/m/Y') }}</span>
                                            <i class="bi bi-arrow-right mx-2 text-muted"></i>
                                            <span>{{ $quota->end_date->format('d/m/Y') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $quota->status }}">
                                            <i class="bi bi-{{ $quota->status === 'available' ? 'check-circle' : ($quota->status === 'rented' ? 'clock' : 'x-circle') }}"></i>
                                            {{ ucfirst($quota->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="price-amount">R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $quota->created_at->format('d/m/Y') }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="empty-state-card mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="empty-state-content">
                    <i class="bi bi-calendar-x"></i>
                    <h5>Nenhuma cota cadastrada</h5>
                    <p>Este hotel ainda não possui cotas cadastradas no sistema.</p>
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
                            <div class="info-label">ID</div>
                            <div class="info-value">{{ $hotel->id }}</div>
                        </div>

                        <div class="system-info-item">
                            <i class="bi bi-plus-circle text-success"></i>
                            <div class="info-label">Criado em</div>
                            <div class="info-value">{{ $hotel->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div class="system-info-item">
                            <i class="bi bi-clock text-info"></i>
                            <div class="info-label">Última atualização</div>
                            <div class="info-value">{{ $hotel->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Card de Estatísticas -->
                <div class="card-modern mb-4" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-modern-header success">
                        <div class="section-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Estatísticas</h3>
                            <p class="section-subtitle">Dados do hotel</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="stat-card">
                            <div class="stat-icon bg-info-subtle">
                                <i class="bi bi-calendar-check text-info"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number">{{ $hotel->quotas->count() }}</div>
                                <div class="stat-label">Cotas Cadastradas</div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon bg-success-subtle">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number">{{ $hotel->quotas->where('status', 'available')->count() }}</div>
                                <div class="stat-label">Cotas Disponíveis</div>
                            </div>
                        </div>

                        <div class="stat-card mb-0">
                            <div class="stat-icon bg-warning-subtle">
                                <i class="bi bi-clock-history text-warning"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-number">{{ $hotel->quotas->where('status', 'rented')->count() }}</div>
                                <div class="stat-label">Cotas Alugadas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Ações -->
                <div class="card-modern" data-aos="fade-left" data-aos-delay="300">
                    <div class="card-modern-header danger">
                        <div class="section-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Ações Rápidas</h3>
                            <p class="section-subtitle">Gerenciar hotel</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.hotels.edit', $hotel) }}" class="btn-action btn-warning-modern">
                                <i class="bi bi-pencil me-2"></i>
                                Editar Hotel
                            </a>
                            
                            <button type="button" class="btn-action btn-danger-modern" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-2"></i>
                                Excluir Hotel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o hotel <strong>{{ $hotel->name }}</strong>?</p>
                <p class="text-danger small mb-0">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        Sim, Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.hotel-details-page {
    padding-bottom: 2rem;
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
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

.btn-outline,
.btn-primary {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-outline {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.btn-primary {
    background: white;
    color: #667eea;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* Status Banner */
.status-banner {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    color: white;
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.3);
}

.status-banner.status-inactive {
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

.status-action .btn-toggle-status {
    background: white;
    color: #10b981;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.status-inactive .status-action .btn-toggle-status {
    color: #ef4444;
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

.card-modern-header.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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

.info-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.info-link:hover {
    text-decoration: underline;
}

.badge-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 700;
    display: inline-block;
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

/* Amenities Display */
.amenities-display {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}

.amenity-badge {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
}

.amenity-badge:hover {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
    transform: translateY(-2px);
}

.amenity-badge i {
    color: #667eea;
    font-size: 1.25rem;
}

.amenity-badge span {
    font-weight: 600;
    color: #334155;
}

/* Quotas Table */
.hotel-quotas-table {
    width: 100%;
    margin: 0;
}

.hotel-quotas-table thead {
    background: #f8fafc;
}

.hotel-quotas-table th {
    padding: 1rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    border-bottom: 2px solid #e2e8f0;
}

.hotel-quotas-table td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.hotel-quotas-table tbody tr:hover {
    background: #f8fafc;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
}

.period-info {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
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

.status-available {
    background: #d1fae5;
    color: #065f46;
}

.status-rented {
    background: #fef3c7;
    color: #92400e;
}

.status-exchanged {
    background: #dbeafe;
    color: #1e40af;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.price-amount {
    font-weight: 700;
    color: #10b981;
    font-size: 1.1rem;
}

/* Empty State */
.empty-state-card {
    background: white;
    border-radius: 16px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.empty-state-content i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
}

.empty-state-content h5 {
    color: #64748b;
    margin-bottom: 0.5rem;
}

.empty-state-content p {
    color: #94a3b8;
    margin: 0;
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

/* Stat Cards */
.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 1rem;
}

.stat-card:last-child {
    margin-bottom: 0;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
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

/* Action Buttons */
.btn-action {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-warning-modern {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.btn-warning-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
}

.btn-danger-modern {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-danger-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
}

/* Rating Stars */
.rating-stars {
    display: flex;
    gap: 0.125rem;
}

.rating-value {
    font-weight: 700;
    color: #64748b;
}

/* Responsive */
@media (max-width: 992px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .btn-outline,
    .btn-primary {
        flex: 1;
        text-align: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .amenities-display {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .hotel-quotas-table {
        font-size: 0.875rem;
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
