@extends('admin.layout')

@section('title', 'Editar Hotel')
@section('page-title', 'Editar Hotel')

@section('content')
<div class="edit-hotel-page">
    <!-- Header Section -->
    <div class="page-header mb-4" data-aos="fade-down">
        <div class="header-content">
            <div class="header-title-section">
                <div class="icon-wrapper">
                    <i class="bi bi-building-gear"></i>
                </div>
                <div>
                    <h1 class="page-title">Editar Hotel</h1>
                    <p class="page-subtitle">Atualize as informações do {{ $hotel->name }}</p>
                </div>
            </div>
            <a href="{{ route('admin.hotels.index') }}" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>
                Voltar para Lista
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.hotels.update', $hotel) }}" id="hotelForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
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
                        <!-- Nome do Hotel -->
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-group-modern">
                                    <label for="name" class="form-label-modern">
                                        <i class="bi bi-building label-icon"></i>
                                        Nome do Hotel *
                                    </label>
                                    <input type="text" class="form-control-modern @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $hotel->name) }}" 
                                           placeholder="Digite o nome do hotel" required>
                                    @error('name')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Localização -->
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-group-modern">
                                    <label for="location" class="form-label-modern">
                                        <i class="bi bi-geo-alt label-icon"></i>
                                        Localização *
                                    </label>
                                    <input type="text" class="form-control-modern @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $hotel->location) }}" 
                                           placeholder="Ex: São Paulo, SP" required>
                                    @error('location')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-group-modern">
                                    <label for="address" class="form-label-modern">
                                        <i class="bi bi-map label-icon"></i>
                                        Endereço Completo *
                                    </label>
                                    <textarea class="form-control-modern @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="2" required>{{ old('address', $hotel->address) }}</textarea>
                                    @error('address')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Website -->
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-group-modern mb-0">
                                    <label for="website" class="form-label-modern">
                                        <i class="bi bi-globe label-icon"></i>
                                        Website
                                    </label>
                                    <input type="url" class="form-control-modern @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website', $hotel->website) }}" 
                                           placeholder="https://www.hotel.com">
                                    @error('website')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
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
                            <p class="section-subtitle">Telefone e e-mail</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label for="phone" class="form-label-modern">
                                        <i class="bi bi-phone label-icon"></i>
                                        Telefone
                                    </label>
                                    <input type="text" class="form-control-modern @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $hotel->phone) }}" 
                                           placeholder="(00) 00000-0000">
                                    @error('phone')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group-modern mb-0">
                                    <label for="email" class="form-label-modern">
                                        <i class="bi bi-envelope label-icon"></i>
                                        E-mail
                                    </label>
                                    <input type="email" class="form-control-modern @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $hotel->email) }}" 
                                           placeholder="contato@hotel.com">
                                    @error('email')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classificação -->
                <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-modern-header success">
                        <div class="section-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Classificação e Descrição</h3>
                            <p class="section-subtitle">Avaliação e informações adicionais</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label for="rating" class="form-label-modern">
                                        <i class="bi bi-star-fill label-icon text-warning"></i>
                                        Avaliação (0-5)
                                    </label>
                                    <input type="number" class="form-control-modern @error('rating') is-invalid @enderror" 
                                           id="rating" name="rating" value="{{ old('rating', $hotel->rating) }}" 
                                           min="0" max="5" step="0.1" placeholder="0.0">
                                    <div class="form-help">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Avalie de 0 a 5 estrelas
                                    </div>
                                    @error('rating')
                                        <div class="error-message">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Status do Hotel</label>
                                    <div class="status-selector">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" hidden 
                                               {{ old('is_active', $hotel->is_active) ? 'checked' : '' }}>
                                        <div class="status-options">
                                            <label class="status-option active-option" for="is_active" id="activeLabel">
                                                <div class="status-icon-wrapper">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                </div>
                                                <div class="status-text">
                                                    <span class="status-title">Ativo</span>
                                                    <span class="status-subtitle">Visível nas buscas</span>
                                                </div>
                                                <div class="status-indicator">
                                                    <i class="bi bi-circle-fill"></i>
                                                </div>
                                            </label>
                                            <label class="status-option inactive-option" id="inactiveLabel">
                                                <div class="status-icon-wrapper">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                </div>
                                                <div class="status-text">
                                                    <span class="status-title">Inativo</span>
                                                    <span class="status-subtitle">Oculto do sistema</span>
                                                </div>
                                                <div class="status-indicator">
                                                    <i class="bi bi-circle"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group-modern mb-0">
                            <label for="description" class="form-label-modern">
                                <i class="bi bi-file-text label-icon"></i>
                                Descrição do Hotel
                            </label>
                            <textarea class="form-control-modern @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="5" 
                                      placeholder="Descreva as principais características, comodidades e destaques do hotel...">{{ old('description', $hotel->description) }}</textarea>
                            @error('description')
                                <div class="error-message">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Imagens do Hotel -->
                <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-modern-header secondary">
                        <div class="section-icon">
                            <i class="bi bi-images"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Imagens do Hotel</h3>
                            <p class="section-subtitle">Adicione mais imagens (máx 6 no total)</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        <!-- Imagens Existentes -->
                        @if($hotel->images && count($hotel->images) > 0)
                        <div class="mb-4">
                            <h6 class="text-dark mb-3">Imagens Existentes ({{ count($hotel->images) }} imagens)</h6>
                            <div class="existing-images-grid">
                                @foreach($hotel->images as $index => $image)
                                <div class="existing-image-item">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Imagem {{ $index + 1 }}">
                                    <span class="existing-image-label">Imagem {{ $index + 1 }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Upload de Novas Imagens -->
                        <div class="form-group-modern">
                            <label for="hotel_images" class="form-label-modern">
                                <i class="bi bi-camera label-icon"></i>
                                Adicionar Novas Imagens
                            </label>
                            <input type="file" class="form-control-modern @error('hotel_images') is-invalid @enderror" 
                                   id="hotel_images" name="hotel_images[]" 
                                   accept="image/jpeg,image/jpg,image/png" multiple>
                            <div class="form-help">
                                <i class="bi bi-info-circle me-1"></i>
                                Máximo: 10 imagens no total | Formatos aceitos: JPEG, JPG, PNG | Tamanho máximo: 5MB por imagem
                            </div>
                            @error('hotel_images')
                                <div class="error-message">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            @error('hotel_images.*')
                                <div class="error-message">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Preview Area -->
                        <div id="imagePreviewContainer" class="image-preview-container mt-4">
                            <div class="preview-grid" id="previewGrid"></div>
                            <div class="preview-info text-center mt-3">
                                <span id="imageCount" class="badge bg-primary">0 novas imagens selecionadas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comodidades -->
                <div class="card-modern mb-4" data-aos="fade-up" data-aos-delay="450">
                    <div class="card-modern-header warning">
                        <div class="section-icon">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <div>
                            <h3 class="section-title">Comodidades e Serviços</h3>
                            <p class="section-subtitle">Selecione as comodidades disponíveis</p>
                        </div>
                    </div>
                    
                    <div class="card-modern-body">
                        @php
                            $amenities = [
                                'wifi' => ['label' => 'Wi-Fi', 'icon' => 'wifi', 'color' => '#10b981'],
                                'room_service' => ['label' => 'Serviço de Quarto', 'icon' => 'bell', 'color' => '#14b8a6'],
                                'heated_pool' => ['label' => 'Piscina Coberta Aquecida', 'icon' => 'thermometer-half', 'color' => '#06b6d4'],
                                'gym' => ['label' => 'Academia', 'icon' => 'activity', 'color' => '#8b5cf6'],
                                'bowling' => ['label' => 'Boliche', 'icon' => 'circle', 'color' => '#4338ca'],
                                'business_center' => ['label' => 'Centro de Negócios', 'icon' => 'briefcase', 'color' => '#8b5cf6'],
                                'restaurant' => ['label' => 'Restaurante', 'icon' => 'cup-hot', 'color' => '#f59e0b'],
                                'bar' => ['label' => 'Bar', 'icon' => 'cup-straw', 'color' => '#ef4444'],
                                'spa' => ['label' => 'Spa/Jacuzzi/Massagens', 'icon' => 'flower1', 'color' => '#ec4899'],
                                'concierge' => ['label' => 'Concierge', 'icon' => 'person-badge', 'color' => '#6366f1'],
                                'pool' => ['label' => 'Piscina', 'icon' => 'droplet', 'color' => '#3b82f6'],
                                'parking' => ['label' => 'Estacionamento', 'icon' => 'car-front', 'color' => '#06b6d4'],
                                'wet_bar' => ['label' => 'Bar Molhado', 'icon' => 'cup-fill', 'color' => '#f97316'],
                                'pet_friendly' => ['label' => 'Espaço Pet', 'icon' => 'heart-fill', 'color' => '#f43f5e'],
                                'wine_cellar' => ['label' => 'Adega', 'icon' => 'flask', 'color' => '#8b0000'],
                                'fireplace' => ['label' => 'Fire Place', 'icon' => 'fire', 'color' => '#dc2626'],
                                'bike_rack' => ['label' => 'Bicicletário', 'icon' => 'bicycle', 'color' => '#0891b2'],
                                'sports_court' => ['label' => 'Quadra Poliesportiva', 'icon' => 'trophy', 'color' => '#ca8a04'],
                                'rooftop' => ['label' => 'Rooftop', 'icon' => 'building', 'color' => '#7c3aed'],
                                'cinema' => ['label' => 'Cinema', 'icon' => 'film', 'color' => '#1f2937'],
                                'market' => ['label' => 'Mercado', 'icon' => 'shop', 'color' => '#059669'],
                                'convenience_store' => ['label' => 'Loja de Conveniência', 'icon' => 'bag', 'color' => '#16a34a'],
                                'themed_parties' => ['label' => 'Festas Temáticas', 'icon' => 'gift', 'color' => '#e11d48'],
                                'beauty_salon' => ['label' => 'Salão de Beleza', 'icon' => 'scissors', 'color' => '#d946ef'],
                                'fireplace_internal' => ['label' => 'Lareira', 'icon' => 'brightness-high', 'color' => '#dc2626'],
                            ];
                            $selectedAmenities = old('amenities', $hotel->amenities ?? []);
                        @endphp
                        
                        <div class="amenities-grid">
                            @foreach($amenities as $key => $data)
                            <label class="amenity-option">
                                <input type="checkbox" name="amenities[]" value="{{ $key }}" 
                                       {{ in_array($key, $selectedAmenities) ? 'checked' : '' }}>
                                <div class="amenity-card">
                                    <div class="amenity-icon" style="--icon-color: {{ $data['color'] }}">
                                        <i class="bi bi-{{ $data['icon'] }}"></i>
                                    </div>
                                    <span class="amenity-label">{{ $data['label'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        
                        @error('amenities')
                            <div class="error-message mt-3">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions" data-aos="fade-up" data-aos-delay="500">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle me-2"></i>
                        Atualizar Hotel
                    </button>
                    <a href="{{ route('admin.hotels.index') }}" class="btn-cancel">
                        <i class="bi bi-x-circle me-2"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sidebar-sticky" data-aos="fade-left">
                <div class="help-card">
                    <div class="help-header">
                        <div class="help-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <h5>Informações do Hotel</h5>
                    </div>
                    
                    <div class="help-body">
                        <div class="help-item">
                            <div class="help-item-icon info">
                                <i class="bi bi-hash"></i>
                            </div>
                            <div>
                                <h6>ID do Hotel</h6>
                                <p>{{ $hotel->id }}</p>
                            </div>
                        </div>
                        
                        <div class="help-item">
                            <div class="help-item-icon success">
                                <i class="bi bi-calendar"></i>
                            </div>
                            <div>
                                <h6>Criado em</h6>
                                <p>{{ $hotel->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="help-item">
                            <div class="help-item-icon warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h6>Última Atualização</h6>
                                <p>{{ $hotel->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="help-item">
                            <div class="help-item-icon primary">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div>
                                <h6>Estatísticas</h6>
                                <p>{{ $hotel->quotas_count ?? 0 }} Cota(s)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <i class="bi bi-shield-check text-success"></i>
                        <h6>Status Atual</h6>
                    </div>
                    <ul class="info-list">
                        <li>
                            <i class="bi bi-check"></i>
                            Status: <strong>{{ $hotel->is_active ? 'Ativo' : 'Inativo' }}</strong>
                        </li>
                        @if($hotel->rating)
                        <li>
                            <i class="bi bi-star-fill"></i>
                            Avaliação: <strong>{{ $hotel->rating }}/5</strong>
                        </li>
                        @endif
                        @if($hotel->images)
                        <li>
                            <i class="bi bi-images"></i>
                            <strong>{{ count($hotel->images) }} Imagem(ns)</strong>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.edit-hotel-page {
    padding-bottom: 2rem;
}

/* Existing Images Styles */
.existing-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
}

.existing-image-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: white;
}

.existing-image-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.existing-image-label {
    display: block;
    padding: 0.5rem;
    font-size: 0.875rem;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #64748b;
    font-weight: 500;
}

/* All other styles from create.blade.php */
.create-hotel-page,
.edit-hotel-page {
    padding-bottom: 2rem;
}

/* Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-title-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
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
}

.btn-back {
    background: white;
    color: #667eea;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    color: #667eea;
}

/* Card Modern */
.card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
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

.card-modern-header.secondary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.section-icon {
    width: 48px;
    height: 48px;
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

/* Form Modern */
.form-group-modern {
    margin-bottom: 1.5rem;
}

.form-label-modern {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.label-icon {
    margin-right: 0.5rem;
    font-size: 1rem;
    color: #64748b;
}

.form-control-modern {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9375rem;
    transition: all 0.3s ease;
}

.form-control-modern:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-help {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.5rem;
}

.error-message {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

/* Status Selector */
.status-selector {
    position: relative;
}

.status-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
}

.status-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 10px;
    background: white;
    border: 2px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.status-option:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.status-option.active-option {
    background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%);
    border-color: #10b981;
}

.status-option.inactive-option {
    background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
    border-color: #ef4444;
}

.status-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.active-option .status-icon-wrapper {
    background: #10b981;
    color: white;
}

.inactive-option .status-icon-wrapper {
    background: #ef4444;
    color: white;
}

.status-text {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.status-title {
    font-weight: 700;
    font-size: 1rem;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.status-subtitle {
    font-size: 0.8rem;
    color: #64748b;
}

.status-indicator {
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* Hidden checkbox for form submission */
.status-selector input[type="checkbox"] {
    position: absolute;
    opacity: 0;
}

/* Amenities Grid */
.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.amenity-option {
    display: block;
    cursor: pointer;
    margin: 0;
}

.amenity-option input {
    display: none;
}

.amenity-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.amenity-card:hover {
    border-color: #667eea;
    background: #f0f4ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.amenity-option input:checked + .amenity-card {
    background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
    border-color: #667eea;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.amenity-icon {
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
    color: var(--icon-color, #667eea);
}

.amenity-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
    display: block;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding: 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.btn-cancel {
    background: white;
    color: #64748b;
    border: 2px solid #e2e8f0;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #64748b;
}

/* Sidebar */
.sidebar-sticky {
    position: sticky;
    top: 20px;
}

.help-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.help-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.help-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.help-header h5 {
    margin: 0;
    font-weight: 700;
    color: #1e293b;
}

.help-body {
    padding: 1.5rem;
}

.help-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.help-item:last-child {
    margin-bottom: 0;
}

.help-item-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.help-item-icon.success {
    background: #d1fae5;
    color: #10b981;
}

.help-item-icon.info {
    background: #dbeafe;
    color: #3b82f6;
}

.help-item-icon.warning {
    background: #fef3c7;
    color: #f59e0b;
}

.help-item-icon.primary {
    background: #ede9fe;
    color: #8b5cf6;
}

.help-item h6 {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.help-item p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.info-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.info-card-header {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.info-card-header h6 {
    margin: 0;
    font-weight: 700;
    color: #92400e;
}

.info-list {
    list-style: none;
    padding: 1.5rem;
    margin: 0;
}

.info-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    color: #64748b;
    font-size: 0.875rem;
}

.info-list li i {
    color: #10b981;
}

/* Select Styling */
.form-control-modern select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23475569' d='M6 9L1 4h10L6 9z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 3rem;
}

/* Image Preview Styles */
.image-preview-container {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px dashed #cbd5e1;
    transition: all 0.3s ease;
}

.image-preview-container.has-images {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.preview-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: white;
}

.preview-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.preview-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

.preview-item-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.preview-item:hover .preview-item-overlay {
    opacity: 1;
}

.remove-image-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.remove-image-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.preview-info {
    margin-top: 1rem;
}

.preview-info .badge {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.image-validation-error {
    background: #fee2e2;
    border: 2px solid #ef4444;
    border-radius: 10px;
    padding: 1rem;
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 1rem;
}

.image-validation-error strong {
    display: block;
    margin-bottom: 0.5rem;
}

/* Responsive */
@media (max-width: 992px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .amenities-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    .preview-grid,
    .existing-images-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}
</style>

<script>
// Phone mask function
function applyPhoneMask(inputId) {
    const element = document.getElementById(inputId);
    if (element) {
        element.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = value;
        });
    }
}

// Apply phone mask
applyPhoneMask('phone');

// Image Upload Preview
const hotelImagesInput = document.getElementById('hotel_images');
const previewGrid = document.getElementById('previewGrid');
const imageCount = document.getElementById('imageCount');
const imagePreviewContainer = document.getElementById('imagePreviewContainer');
let selectedFiles = [];

if (hotelImagesInput) {
    hotelImagesInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        selectedFiles = files;
        
        // Clear previous previews
        previewGrid.innerHTML = '';
        
        // Display previews
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.dataset.index = index;
                
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <div class="preview-item-overlay">
                        <button type="button" class="remove-image-btn" onclick="removeImage(${index})">
                            <i class="bi bi-trash me-1"></i>Remover
                        </button>
                    </div>
                `;
                
                previewGrid.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
        
        // Update count badge
        updateImageCount();
        
        // Add has-images class
        imagePreviewContainer.classList.add('has-images');
    });
}

function removeImage(index) {
    // Create a new DataTransfer object
    const dt = new DataTransfer();
    
    // Add all files except the one to be removed
    selectedFiles.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    // Update the file input
    hotelImagesInput.files = dt.files;
    selectedFiles = Array.from(dt.files);
    
    // Trigger change event to update preview
    hotelImagesInput.dispatchEvent(new Event('change'));
}

function updateImageCount() {
    const count = selectedFiles.length;
    imageCount.textContent = `${count} nova${count > 1 ? 's' : ''} imagem${count > 1 ? 'ns' : ''} selecionada${count > 1 ? 's' : ''}`;
    
    // Update badge color
    if (count > 0) {
        imageCount.className = 'badge bg-success';
    } else {
        imageCount.className = 'badge bg-primary';
    }
}

// Status toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('is_active');
    const activeLabel = document.getElementById('activeLabel');
    const inactiveLabel = document.getElementById('inactiveLabel');
    
    if (!checkbox || !activeLabel || !inactiveLabel) return;
    
    // Toggle on click
    activeLabel.addEventListener('click', function() {
        if (!checkbox.checked) {
            checkbox.checked = true;
            updateStatusVisuals();
        }
    });
    
    inactiveLabel.addEventListener('click', function() {
        if (checkbox.checked) {
            checkbox.checked = false;
            updateStatusVisuals();
        }
    });
    
    function updateStatusVisuals() {
        const activeOption = document.querySelector('.active-option');
        const inactiveOption = document.querySelector('.inactive-option');
        const activeIcon = activeOption.querySelector('.status-icon-wrapper');
        const inactiveIcon = inactiveOption.querySelector('.status-icon-wrapper');
        const activeIndicator = activeOption.querySelector('.status-indicator');
        const inactiveIndicator = inactiveOption.querySelector('.status-indicator');
        
        if (checkbox.checked) {
            // Ativo selecionado
            activeOption.style.background = 'linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%)';
            activeOption.style.borderColor = '#10b981';
            activeOption.style.boxShadow = '0 4px 20px rgba(16, 185, 129, 0.2)';
            
            inactiveOption.style.background = 'white';
            inactiveOption.style.borderColor = '#e2e8f0';
            inactiveOption.style.boxShadow = 'none';
            
            activeIndicator.innerHTML = '<i class="bi bi-circle-fill" style="color: #10b981;"></i>';
            inactiveIndicator.innerHTML = '<i class="bi bi-circle" style="color: #94a3b8;"></i>';
        } else {
            // Inativo selecionado
            inactiveOption.style.background = 'linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%)';
            inactiveOption.style.borderColor = '#ef4444';
            inactiveOption.style.boxShadow = '0 4px 20px rgba(239, 68, 68, 0.2)';
            
            activeOption.style.background = 'white';
            activeOption.style.borderColor = '#e2e8f0';
            activeOption.style.boxShadow = 'none';
            
            inactiveIndicator.innerHTML = '<i class="bi bi-circle-fill" style="color: #ef4444;"></i>';
            activeIndicator.innerHTML = '<i class="bi bi-circle" style="color: #94a3b8;"></i>';
        }
    }
    
    // Initialize visuals
    updateStatusVisuals();
});

// Form submission
document.getElementById('hotelForm').addEventListener('submit', function(e) {
    const submitBtn = document.querySelector('.btn-submit');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Atualizando...';
    submitBtn.disabled = true;
});

// Initialize AOS animations
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
