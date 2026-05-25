@extends('layouts.app')

@section('title', 'Cadastro - Cota Brasilis')

@php
    // Helper function para detectar se é erro de duplicação
    function isDuplicateError($field, $errors) {
        if (!$errors->has($field)) {
            return false;
        }
        $message = strtolower($errors->first($field));
        // Verificar se a mensagem contém palavras-chave de duplicação
        $duplicateKeywords = [
            'já está em uso', 
            'já foi utilizado', 
            'já existe', 
            'já foi cadastrado',
            'already been taken', 
            'already exists', 
            'unique', 
            'duplicado',
            'has already been taken',
            'must be unique'
        ];
        foreach ($duplicateKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    // Helper para verificar se uma mensagem de erro é de duplicação
    function isDuplicateMessage($message) {
        $message = strtolower($message);
        $duplicateKeywords = [
            'já está em uso', 
            'já foi utilizado', 
            'já existe', 
            'já foi cadastrado',
            'already been taken', 
            'already exists', 
            'unique', 
            'duplicado',
            'has already been taken',
            'must be unique'
        ];
        foreach ($duplicateKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
@endphp

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-lg-10">
        <div class="card border-0 shadow-lg" data-aos="fade-up">
            <div class="card-body p-5">
                <div class="text-center mb-5">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="Cota Brasilis" class="mb-4" style="height: 120px; max-width: 400px; object-fit: contain;">
                    <h2 class="fw-bold mb-2">Criar Conta</h2>
                    <p class="text-muted">Participe da plataforma profissional e pioneira de Hospedagem com Cotas de Multipropriedade Hoteleira, resgate sua alegria por se hospedar, turistar e usar bem o que é seu, ou oferecer para aluguel, troca ou venda.</p>
                </div>

                <!-- Alertas de Erro Geral -->
                @if($errors->any())
                    <div class="mb-4">
                        @foreach($errors->all() as $error)
                            @if(isDuplicateMessage($error))
                                <div class="alert alert-warning-custom mb-2" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Atenção:</strong> {{ $error }}
                                </div>
                            @else
                                <div class="alert alert-danger mb-2" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><strong>Erro:</strong> {{ $error }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerForm" enctype="multipart/form-data" novalidate>
                    @csrf

                    <!-- Step 1: Basic Information -->
                    <div class="step" id="step1">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-user me-2"></i>Informações login
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">
                                    <i class="fas fa-at me-2 text-primary"></i>Nome de usuário *
                                </label>
                                <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', session('register.step1.name', '')) }}" minlength="6"
                                    placeholder="Digite seu nome de usuário" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">O nome de usuário deve ter pelo menos 6 caracteres</small>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>E-mail *
                                </label>
                                <input type="email" class="form-control form-control-lg @error('email') @if(isDuplicateError('email', $errors)) is-warning @else is-invalid @endif @enderror"
                                    id="email" name="email" value="{{ old('email', session('register.step1.email', '')) }}"
                                    placeholder="Digite seu e-mail" required>
                                @error('email')
                                <div class="@if(isDuplicateError('email', $errors)) warning-feedback @else invalid-feedback @endif">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Senha *
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        id="password" name="password" minlength="8" placeholder="Digite sua senha" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">A senha deve ter pelo menos 8 caracteres</small>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Confirmar Senha *
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg"
                                        id="password_confirmation" minlength="8" name="password_confirmation"
                                        placeholder="Confirme sua senha" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">A confirmação de senha deve ter pelo menos 8 caracteres</small>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-primary btn-lg px-4" id="step1_next_button" onclick="nextStep()">
                                Próximo <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Personal Information -->
                    <div class="step d-none" id="step2">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-id-card me-2"></i>Informações Pessoais Obrigatórias
                        </h5>

                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atenção:</strong> Todos os campos marcados com * são obrigatórios. O cadastro não será c
                            oncluído se algum campo obrigatório não for preenchido.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-primary"></i>Nome Completo *
                                </label>
                                <input type="text" class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                                    id="full_name" name="full_name" value="{{ old('full_name', session('register.step2.full_name', '')) }}" minlength="10"
                                    placeholder="Digite seu nome completo" required>
                                @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">O nome completo deve ter pelo menos 10 caracteres</small>
                            </div>
                            <div class="col-md-6">
                                <label for="cpf" class="form-label fw-semibold">
                                    <i class="fas fa-id-card me-2 text-primary"></i>CPF *
                                </label>
                                <input type="text" class="form-control form-control-lg @error('cpf') @if(isDuplicateError('cpf', $errors)) is-warning @else is-invalid @endif @enderror"
                                    id="cpf" name="cpf" value="{{ old('cpf', session('register.step2.cpf', '')) }}"
                                    placeholder="000.000.000-00" minlength="11" maxlength="14" required>
                                @error('cpf')
                                <div class="@if(isDuplicateError('cpf', $errors)) warning-feedback @else invalid-feedback @endif">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">
                                    <i class="fas fa-phone me-2 text-primary"></i>Telefone *
                                </label>
                                <input type="text" class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}"
                                    placeholder="(00) 00000-0000" minlength="11" maxlength="15" required>
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="ingress_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar me-2 text-primary"></i>Data de Nascimento *
                                </label>
                                <input type="date" class="form-control form-control-lg @error('ingress_date') is-invalid @enderror"
                                    id="ingress_date" name="ingress_date" value="{{ old('ingress_date') }}" required>
                                @error('ingress_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="mt-4">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fas fa-map-marker-alt me-2"></i>Endereço
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="cep" class="form-label fw-semibold">
                                        <i class="fas fa-mail-bulk me-2 text-primary"></i>CEP *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('cep') is-invalid @enderror"
                                        id="cep" name="cep" value="{{ old('cep') }}"
                                        placeholder="07980-000" maxlength="9" required>
                                    @error('cep')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="street" class="form-label fw-semibold">
                                        <i class="fas fa-road me-2 text-primary"></i>Rua *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('street') is-invalid @enderror"
                                        id="street" name="street" value="{{ old('street') }}"
                                        placeholder="Nome da rua" required>
                                    @error('street')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="neighborhood" class="form-label fw-semibold">
                                        <i class="fas fa-map me-2 text-primary"></i>Bairro *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('neighborhood') is-invalid @enderror"
                                        id="neighborhood" name="neighborhood" value="{{ old('neighborhood') }}"
                                        placeholder="Nome do bairro" required>
                                    @error('neighborhood')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <label for="city" class="form-label fw-semibold">
                                        <i class="fas fa-city me-2 text-primary"></i>Cidade *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('city') is-invalid @enderror"
                                        id="city" name="city" value="{{ old('city') }}"
                                        placeholder="Nome da cidade" required>
                                    @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="state" class="form-label fw-semibold">
                                        <i class="fas fa-flag me-2 text-primary"></i>Estado *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('state') is-invalid @enderror"
                                        id="state" name="state" value="{{ old('state') }}"
                                        placeholder="Nome do estado" required>
                                    @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="house_number" class="form-label fw-semibold">
                                        <i class="fas fa-home me-2 text-primary"></i>Nº *
                                    </label>
                                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="10" class="form-control form-control-lg @error('house_number') is-invalid @enderror"
                                        id="house_number" name="house_number" value="{{ old('house_number') }}"
                                        placeholder="123" required>
                                    @error('house_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <label for="complement" class="form-label fw-semibold">
                                        <i class="fas fa-building me-2 text-primary"></i>Complemento *
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('complement') is-invalid @enderror"
                                        id="complement" name="complement" value="{{ old('complement') }}"
                                        placeholder="Apto, bloco, referência"
                                        maxlength="120"
                                        required
                                        autocomplete="address-line2">
                                    <small class="text-muted d-block mt-1">Caso não haja complemento digite um ponto( . ).</small>
                                    @error('complement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                Próximo <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Step 3: Document Upload -->
                    <div class="step d-none" id="step3">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-id-card me-2"></i>Documentos Obrigatórios
                        </h5>

                        <style>
                            .upload-card { border: 1px solid #e6edf3; border-radius: 12px; background: #fbfdff; padding: 14px; }
                            .upload-drop { border: 2px dashed #d1e7ff; border-radius: 8px; padding: 14px; text-align:center; cursor: pointer; background:#fff; }
                            .upload-drop.dragover { border-color: #3b82f6; background: #f0f8ff; }
                            .upload-drop.is-invalid-field {
                                border-color: #dc3545 !important;
                                background-color: #fff5f5 !important;
                                box-shadow: 0 0 0 1px rgba(220, 53, 69, 0.25);
                            }
                            .upload-preview { max-height:140px; max-width:100%; display:block; margin:0 auto 8px auto; object-fit:contain; }
                            .upload-meta { font-size:0.9rem; color:#6b7280; }
                            .btn-clear-file { background: transparent; border: none; color: #dc2626; cursor:pointer; }
                        </style>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-camera me-2 text-primary"></i>Foto de rosto Usuário *
                                </label>
                                <div class="upload-card">
                                    <div id="user_photo_drop" class="upload-drop" tabindex="0">
                                        <img id="user_photo_preview" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="preview" class="upload-preview" style="display:none;">
                                        <div class="upload-instructions">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                            <div class="mt-2 fw-semibold">Arraste a foto aqui ou clique para selecionar</div>
                                            <div class="upload-meta">Formatos: JPG, PNG, PDF — Máx: 10MB</div>
                                        </div>
                                        <input type="file" class="d-none @error('user_photo') is-invalid @enderror" id="user_photo" name="user_photo" accept=".jpg,.jpeg,.png,.pdf" required>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div id="user_photo_info" class="upload-meta">Nenhum arquivo selecionado</div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('user_photo').click();">Escolher</button>
                                            <button type="button" id="clear_user_photo" class="btn-clear-file ms-2" title="Remover arquivo">Remover</button>
                                        </div>
                                    </div>
                                    @error('user_photo')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="document_type" class="form-label fw-semibold">
                                    <i class="fas fa-id-badge me-2 text-primary"></i>Tipo de Documento *
                                </label>
                                <select class="form-select form-select-lg @error('document_type') is-invalid @enderror" id="document_type" name="document_type" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="rg" {{ old('document_type') == 'rg' ? 'selected' : '' }}>RG</option>
                                    <option value="cnh" {{ old('document_type') == 'cnh' ? 'selected' : '' }}>CNH</option>
                                </select>
                                @error('document_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="mt-3 upload-card">
                                    <label class="form-label fw-semibold mb-2"><i class="fas fa-file-image me-2 text-primary"></i>Foto do Documento (RG ou CNH) *</label>
                                    <div id="document_photo_drop" class="upload-drop" tabindex="0">
                                        <img id="document_photo_preview" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="preview" class="upload-preview" style="display:none;">
                                        <div class="upload-instructions">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                            <div class="mt-2 fw-semibold">Arraste a imagem do documento aqui ou clique para selecionar</div>
                                            <div class="upload-meta">Formatos: JPG, PNG, PDF — Máx: 10MB. Documento deve estar legível.</div>
                                        </div>
                                        <input type="file" class="d-none @error('document_photo') is-invalid @enderror" id="document_photo" name="document_photo" accept=".jpg,.jpeg,.png,.pdf" required>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div id="document_photo_info" class="upload-meta">Nenhum arquivo selecionado</div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('document_photo').click();">Escolher</button>
                                            <button type="button" id="clear_document_photo" class="btn-clear-file ms-2" title="Remover arquivo">Remover</button>
                                        </div>
                                    </div>
                                    @error('document_photo')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                Próximo <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>



                    <!-- Step 4: Quota Information -->
                    <div class="step d-none" id="step4">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-file-contract me-2"></i>Informações da Cota
                        </h5>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> A escolha de posse de cota define quais funcionalidades estarão disponíveis para você na plataforma.
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-4" style="font-size: 25px;">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>Possuo Cota Hoteleira? *
                                </label>

                                <!-- Botões de seleção estilizados -->
                                <div class="quota-selection-container">
                                    <div class="quota-option-card" data-value="1">
                                        <input type="radio" name="has_quota" id="has_quota_yes" value="1"
                                            {{ old('has_quota') == '1' ? 'checked' : '' }} required>
                                        <label for="has_quota_yes" class="quota-option-label">
                                            <div class="quota-option-icon success">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="quota-option-content">
                                                <h6 class="quota-option-title">Sim</h6>
                                                <p class="quota-option-description">Tenho Cota Hoteleira e gostaria de usá-la</p>
                                            </div>
                                            <div class="quota-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="quota-option-card" data-value="0">
                                        <input type="radio" name="has_quota" id="has_quota_no" value="0"
                                            {{ old('has_quota') == '0' ? 'checked' : '' }} required>
                                        <label for="has_quota_no" class="quota-option-label">
                                            <div class="quota-option-icon danger">
                                                <i class="fas fa-times-circle"></i>
                                            </div>
                                            <div class="quota-option-content">
                                                <h6 class="quota-option-title">Não</h6>
                                                <p class="quota-option-description">Não tenho Cota Hoteleira, mas quero usar a plataforma</p>
                                            </div>
                                            <div class="quota-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="quota-option-card" data-value="2">
                                        <input type="radio" name="has_quota" id="has_quota_manager" value="2"
                                            {{ old('has_quota') == '2' ? 'checked' : '' }} required>
                                        <label for="has_quota_manager" class="quota-option-label">
                                            <div class="quota-option-icon info">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div class="quota-option-content">
                                                <h6 class="quota-option-title">Não, mas tenho autorização para ser gestor</h6>
                                                <p class="quota-option-description">Tenho autorização para gerenciar Cota(s) de terceiro(s)</p>
                                            </div>
                                            <div class="quota-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="quota-option-card" data-value="3">
                                        <input type="radio" name="has_quota" id="has_quota_owner_delegate" value="3"
                                            {{ old('has_quota') == '3' ? 'checked' : '' }} required>
                                        <label for="has_quota_owner_delegate" class="quota-option-label">
                                            <div class="quota-option-icon info">
                                                <i class="fas fa-handshake"></i>
                                            </div>
                                            <div class="quota-option-content">
                                                <h6 class="quota-option-title">Possuo Cota e Autorizarei Outra pessoa geri-la.</h6>
                                                <p class="quota-option-description">Sou titular da cota e autorizo outra pessoa a geri-la na plataforma .</p>
                                            </div>
                                            <div class="quota-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                @error('has_quota')
                                <div class="text-danger small mt-3">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mensagem de aviso para quem não possui cota -->
                        <div class="mt-4 d-none" id="no_quota_warning">
                            <div class="alert alert-info border-0 shadow-sm">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle me-3 mt-1 text-info"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">Informação Importante</h6>
                                        <p class="mb-0">
                                            Como você não possui Cotas, notificamos que independente do perfil que escolher na próxima tela,
                                            você terá acesso somente às principais funções da plataforma que são <strong>COMPRAR</strong> ou
                                            <strong>ALUGAR</strong> Cotas Hoteleiras.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Seção para quem possui cota -->
                        <div class="mt-4 d-none" id="quota_owner_section">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="fas fa-home me-2"></i>Informações da Sua Cota 
                            </h6>

                            <!-- 1. Hotel operacional -->
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <label class="form-label fw-semibold mb-4">
                                        <i class="fas fa-hotel me-2 text-primary"></i>O Hotel onde você é(será) Gestor da Cota está em funcionamento? *
                                    </label>

                                    <!-- Botões de seleção estilizados lado a lado -->
                                    <div class="hotel-operational-container">
                                        <div class="hotel-option-card" data-value="1">
                                            <input type="radio" name="hotel_operational" id="hotel_operational_yes" value="1"
                                                {{ old('hotel_operational', '1') == '1' ? 'checked' : '' }} required>
                                            <label for="hotel_operational_yes" class="hotel-option-label">
                                                <div class="hotel-option-icon success">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="hotel-option-content">
                                                    <h6 class="hotel-option-title">Sim</h6>
                                                    <p class="hotel-option-description">O hotel está em pleno funcionamento</p>
                                                </div>
                                                <div class="hotel-option-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="hotel-option-card" data-value="0">
                                            <input type="radio" name="hotel_operational" id="hotel_operational_no" value="0"
                                                {{ old('hotel_operational') === '0' ? 'checked' : '' }} required>
                                            <label for="hotel_operational_no" class="hotel-option-label">
                                                <div class="hotel-option-icon danger">
                                                    <i class="fas fa-times-circle"></i>
                                                </div>
                                                <div class="hotel-option-content">
                                                    <h6 class="hotel-option-title">Não</h6>
                                                    <p class="hotel-option-description">O hotel não está em funcionamento</p>
                                                </div>
                                                <div class="hotel-option-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3 d-none" id="hotel_not_operational_notice">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Aviso!</strong> Não é possível cadastrar cotas quando o hotel não está em funcionamento. Retorne para o cadastro quando ele estiver em funcionamento.
                            </div>

                            <div id="owner_additional_fields">

                                <!-- 2. Documento de confirmação da Posse da Cota -->
                                <div class="mt-3 mb-4">
                                    <label for="quota_contract" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Documento de confirmação da Posse da Cota *
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('quota_contracts') is-invalid @enderror"
                                        id="quota_contract" name="quota_contracts[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                    <div class="form-text">
                                        <strong>Obrigatório:</strong> Foto da primeira folha do contrato de compra e venda da Cota, ou outra folha do contrato, que contenha informações do nome do hotel, nome do titular, CPF, endereço, telefone, email, número da cota, do bloco e do apartamento. Formatos de documentos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB
                                    </div>
                                    @error('quota_contracts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                               <label class="form-label fw-semibold mb-4 mt-4 pt-2">Resumo de identificação da Cota</label>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="owner_quota_number" class="form-label fw-semibold">Número da cota <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('owner_quota_number') is-invalid @enderror"
                                            id="owner_quota_number" name="owner_quota_number" value="{{ old('owner_quota_number') }}" required maxlength="50" placeholder="Ex.: 101">
                                        @error('owner_quota_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="owner_quota_block" class="form-label fw-semibold">Bloco do apartamento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('owner_quota_block') is-invalid @enderror"
                                            id="owner_quota_block" name="owner_quota_block" value="{{ old('owner_quota_block') }}" required maxlength="50" placeholder="Ex.: A">
                                        @error('owner_quota_block')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="owner_apartment_number" class="form-label fw-semibold">Número do apartamento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('owner_apartment_number') is-invalid @enderror"
                                            id="owner_apartment_number" name="owner_apartment_number" value="{{ old('owner_apartment_number') }}" required maxlength="50" placeholder="Ex.: 205">
                                        @error('owner_apartment_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Termo de Autorização de Hospedagem para Terceiros -->
                                <div class="mt-3">
                                    <label for="hospitality_authorization_term" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Termo de Autorização de Hospedagem para Terceiros
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('hospitality_authorization_term') is-invalid @enderror"
                                        id="hospitality_authorization_term" name="hospitality_authorization_term" accept=".pdf,image/*">
                                    <div class="form-text">
                                        Anexe o termo oficial do hotel onde você cadastrará cotas aqui. Cada hotel tem o seu próprio termo em pdf editável além do sistema digital. Formatos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB.
                                    </div>
                                    @error('hospitality_authorization_term')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Termo de Intenção de Troca de Titularidade (apenas quando Hotel operacional = Sim) -->
                                <div class="mt-3 d-none" id="intention_transfer_section">
                                    <h6 class="fw-bold text-black mb-2">Termo de Intenção de Troca de Titularidade <small class="text-muted">(Apenas para as funções <strong>COMPRA</strong> e <strong>VENDA</strong>)</small></h6>

                                    <label for="intention_notary_address" class="form-label fw-semibold">
                                        Informe o endereço do Cartório que trata da sua Cota
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('intention_notary_address') is-invalid @enderror"
                                        id="intention_notary_address" name="intention_notary_address" value="{{ old('intention_notary_address') }}" placeholder="Digite o endereço do Cartório">
                                    <small class="text-muted d-block mt-2">
                                        Informe o endereço do cartório que trata da sua Cota Hoteleira cadastrada nesse aplicativo para fins de emissão do termo
                                        de intenção de COMPRA e VENDA caso seja de seu interesse.
                                    </small>
                                    @error('intention_notary_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 3. Status da Cota e Prazo de Quitação -->
                                <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                        <label for="quota_status" class="form-label fw-semibold">
                                            <i class="fas fa-credit-card me-2 text-primary"></i>Status da Cota *
                                        </label>
                                        <select class="form-select form-select-lg @error('quota_status') is-invalid @enderror"
                                            id="quota_status" name="quota_status" required>
                                            <option value="">Selecione o status</option>
                                            <option value="paid" {{ old('quota_status') == 'paid' ? 'selected' : '' }}>Quitada</option>
                                            <option value="unpaid" {{ old('quota_status') == 'unpaid' ? 'selected' : '' }}>Não Quitada</option>
                                        </select>
                                        @error('quota_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6" id="quota_payment_deadline_section">
                                        <label for="quota_payment_deadline" class="form-label fw-semibold">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>Prazo para Quitação
                                        </label>
                                        <input type="date" class="form-control form-control-lg @error('quota_payment_deadline') is-invalid @enderror"
                                            id="quota_payment_deadline" name="quota_payment_deadline" value="{{ old('quota_payment_deadline') }}">
                                        <div class="form-text">Informe o prazo para quitação da Cota</div>
                                        @error('quota_payment_deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- 4. Usos permitidos -->
                                <div class="row g-3 mt-2">
                                    <div class="col-12"><br><br>
                                        <label class="form-label fw-semibold mb-4" style="font-size: 18px;">
                                            <i class="fas fa-sliders-h me-2 text-primary"></i>Usos permitidos *
                                        </label>
                                        <small>Escolha quais funcionalidades você quer usar</small>

                                        <!-- Cards de usos permitidos lado a lado -->
                                        <div class="allowed-uses-container">
                                            <div class="use-option-card" data-value="rent">
                                                <input type="checkbox" name="allowed_uses[]" id="use_rent" value="rent"
                                                    {{ is_array(old('allowed_uses')) && in_array('rent', old('allowed_uses')) ? 'checked' : '' }}>
                                                <label for="use_rent" class="use-option-label">
                                                    <div class="use-option-icon rent">
                                                        <i class="fas fa-bed"></i>
                                                    </div>
                                                    <div class="use-option-content">
                                                        <h6 class="use-option-title">Alugar</h6>
                                                        <p class="use-option-description">Oferecer minha Cota ou Fração para aluguel</p>
                                                    </div>
                                                    <div class="use-option-check">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                </label>
                                            </div>



                                            <div class="use-option-card" data-value="exchange">
                                                <input type="checkbox" name="allowed_uses[]" id="use_exchange" value="exchange"
                                                    {{ is_array(old('allowed_uses')) && in_array('exchange', old('allowed_uses')) ? 'checked' : '' }}>
                                                <label for="use_exchange" class="use-option-label">
                                                    <div class="use-option-icon exchange">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </div>
                                                    <div class="use-option-content">
                                                        <h6 class="use-option-title">Trocar</h6>
                                                        <p class="use-option-description">Trocar o periodo de uso da minha Cota ou Fração por outra</p>
                                                    </div>
                                                    <div class="use-option-check">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                </label>
                                            </div>

                                            <div class="use-option-card" data-value="sell">
                                                <input type="checkbox" name="allowed_uses[]" id="use_sell" value="sell"
                                                    {{ is_array(old('allowed_uses')) && in_array('sell', old('allowed_uses')) ? 'checked' : '' }}>
                                                <label for="use_sell" class="use-option-label">
                                                    <div class="use-option-icon sell">
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </div>
                                                    <div class="use-option-content">
                                                        <h6 class="use-option-title">Vender</h6>
                                                        <p class="use-option-description">Vender minha Cota</p>
                                                    </div>
                                                    <div class="use-option-check">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                </label>
                                            </div>

                                            <div class="use-option-card" data-value="buy">
                                                <input type="checkbox" name="allowed_uses[]" id="use_buy" value="buy"
                                                    {{ is_array(old('allowed_uses')) && in_array('buy', old('allowed_uses')) ? 'checked' : '' }}>
                                                <label for="use_buy" class="use-option-label">
                                                    <div class="use-option-icon buy">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </div>
                                                    <div class="use-option-content">
                                                        <h6 class="use-option-title">Comprar</h6>
                                                        <p class="use-option-description">Comprar Cotas disponíveis</p>
                                                    </div>
                                                    <div class="use-option-check">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        @error('allowed_uses')
                                        <div class="text-danger small mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Informações da Cota -->
                                <div class="mb-4 mt-4">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-hotel me-2"></i>Informações da Cota e Facilidades
                                    </h6>

                                    <!-- Hotel -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="owner_hotel_id" class="form-label fw-semibold mb-1">
                                                <i class="fas fa-hotel me-1 text-success"></i>Hotel *
                                            </label>
                                            <div class="alert alert-warning hotel-choice-warning-static small mb-2 mb-md-3 py-2 shadow-sm" role="alert">
                                                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                                                Atenção! No caso de escolha errônea do nome do hotel, você só poderá corrigir com o apoio da equipe de suporte. Isso pode demorar!
                                            </div>
                                            <select class="form-select @error('owner_hotel_id') is-invalid @enderror"
                                                id="owner_hotel_id" name="owner_hotel_id" required>
                                                <option value="">Selecione um hotel</option>
                                                @if(isset($hotels) && count($hotels) > 0)
                                                @foreach($hotels as $hotel)
                                                <option value="{{ $hotel->id }}" {{ old('owner_hotel_id') == $hotel->id ? 'selected' : '' }}>
                                                    {{ $hotel->name }} - {{ $hotel->city }}/{{ $hotel->state }}
                                                </option>
                                                @endforeach
                                                @else
                                                <option value="1" {{ old('owner_hotel_id') == '1' ? 'selected' : '' }}>
                                                    Hotel Exemplo - São Paulo/SP
                                                </option>
                                                @endif
                                            </select>
                                            @error('owner_hotel_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Quartos -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_rooms" class="form-label fw-semibold">
                                                <i class="fas fa-bed me-1 text-success"></i>Quartos *
                                            </label>
                                            <select class="form-select @error('owner_quota_rooms') is-invalid @enderror"
                                                id="owner_quota_rooms" name="owner_quota_rooms" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_rooms') == '1' ? 'selected' : '' }}>1 Quarto</option>
                                                <option value="2" {{ old('owner_quota_rooms') == '2' ? 'selected' : '' }}>2 Quartos</option>
                                                <option value="3" {{ old('owner_quota_rooms') == '3' ? 'selected' : '' }}>3 Quartos</option>
                                            </select>
                                            @error('owner_quota_rooms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Blocos dinâmicos de quartos -->
                                        <div id="rooms-configuration" class="col-12 mt-4 d-none">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-bed me-2"></i>Configuração dos Quartos
                                            </h6>
                                            <small style="font-size: 0.8rem;">Aviso: O número de pessoas deve ser igual ou menor que o número de leitos</small><br>
                                            <div id="rooms-container">
                                                <!-- Os blocos de quartos serão inseridos aqui dinamicamente -->
                                            </div>
                                        </div>

                                        <!-- Tamanho -->
                                        <div class="col-md-4">
                                            <label for="owner_quota_size" class="form-label fw-semibold">
                                                <i class="fas fa-expand-arrows-alt me-1 text-success"></i>Tamanho (m²) *
                                            </label>
                                            <input type="text" class="form-control @error('owner_quota_size') is-invalid @enderror"
                                                id="owner_quota_size" name="owner_quota_size"
                                                placeholder="Ex: 45, 50-60, 70+"
                                                value="{{ old('owner_quota_size') }}" required>
                                            @error('owner_quota_size')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Varanda -->
                                        <div class="col-md-4">
                                            <label for="owner_quota_balcony" class="form-label fw-semibold">
                                                <i class="fas fa-door-open me-1 text-success"></i>Varanda *
                                            </label>
                                            <select class="form-select @error('owner_quota_balcony') is-invalid @enderror"
                                                id="owner_quota_balcony" name="owner_quota_balcony" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_balcony') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_balcony') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_balcony')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Cozinha Completa, Vista Mar, Jacuzzi -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_kitchen" class="form-label fw-semibold">
                                                <i class="fas fa-utensils me-1 text-success"></i>Cozinha Completa *
                                            </label>
                                            <select class="form-select @error('owner_quota_kitchen') is-invalid @enderror"
                                                id="owner_quota_kitchen" name="owner_quota_kitchen" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_kitchen') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_kitchen') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_kitchen')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_vista_mar" class="form-label fw-semibold">
                                                <i class="fas fa-water me-1 text-success"></i>Vista Mar *
                                            </label>
                                            <select class="form-select @error('owner_quota_vista_mar') is-invalid @enderror"
                                                id="owner_quota_vista_mar" name="owner_quota_vista_mar" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_vista_mar') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_vista_mar') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_vista_mar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_jacuzzi" class="form-label fw-semibold">
                                                <i class="fas fa-hot-tub me-1 text-success"></i>Jacuzzi *
                                            </label>
                                            <select class="form-select @error('owner_quota_jacuzzi') is-invalid @enderror"
                                                id="owner_quota_jacuzzi" name="owner_quota_jacuzzi" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_jacuzzi') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_jacuzzi') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_jacuzzi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Spa, Piscina, Academia -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_spa" class="form-label fw-semibold">
                                                <i class="fas fa-spa me-1 text-success"></i>Spa *
                                            </label>
                                            <select class="form-select @error('owner_quota_spa') is-invalid @enderror"
                                                id="owner_quota_spa" name="owner_quota_spa" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_spa') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_spa') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_spa')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_piscina" class="form-label fw-semibold">
                                                <i class="fas fa-swimming-pool me-1 text-success"></i>Piscina *
                                            </label>
                                            <select class="form-select @error('owner_quota_piscina') is-invalid @enderror"
                                                id="owner_quota_piscina" name="owner_quota_piscina" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_piscina') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_piscina') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_piscina')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_academia" class="form-label fw-semibold">
                                                <i class="fas fa-dumbbell me-1 text-success"></i>Academia *
                                            </label>
                                            <select class="form-select @error('owner_quota_academia') is-invalid @enderror"
                                                id="owner_quota_academia" name="owner_quota_academia" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_academia') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_academia') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_academia')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Lareira, Adega, Área Kids -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_lareira" class="form-label fw-semibold">
                                                <i class="fas fa-fire me-1 text-success"></i>Lareira *
                                            </label>
                                            <select class="form-select @error('owner_quota_lareira') is-invalid @enderror"
                                                id="owner_quota_lareira" name="owner_quota_lareira" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_lareira') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_lareira') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_lareira')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_adega" class="form-label fw-semibold">
                                                <i class="fas fa-wine-bottle me-1 text-success"></i>Adega *
                                            </label>
                                            <select class="form-select @error('owner_quota_adega') is-invalid @enderror"
                                                id="owner_quota_adega" name="owner_quota_adega" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_adega') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_adega') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_adega')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_area_kids" class="form-label fw-semibold">
                                                <i class="fas fa-child me-1 text-success"></i>Área <i>Kids</i> *
                                            </label>
                                            <select class="form-select @error('owner_quota_area_kids') is-invalid @enderror"
                                                id="owner_quota_area_kids" name="owner_quota_area_kids" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_area_kids') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_area_kids') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_area_kids')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Área de Trabalho, WiFi, Estacionamento Gratuito -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_area_trabalho" class="form-label fw-semibold">
                                                <i class="fas fa-briefcase me-1 text-success"></i>Área de Trabalho *
                                            </label>
                                            <select class="form-select @error('owner_quota_area_trabalho') is-invalid @enderror"
                                                id="owner_quota_area_trabalho" name="owner_quota_area_trabalho" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_area_trabalho') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_area_trabalho') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_area_trabalho')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_wifi" class="form-label fw-semibold">
                                                <i class="fas fa-wifi me-1 text-success"></i>WiFi *
                                            </label>
                                            <select class="form-select @error('owner_quota_wifi') is-invalid @enderror"
                                                id="owner_quota_wifi" name="owner_quota_wifi" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_wifi') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_wifi') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_wifi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_parking" class="form-label fw-semibold">
                                                <i class="fas fa-parking me-1 text-success"></i>Estacionamento Gratuito *
                                            </label>
                                            <select class="form-select @error('owner_quota_parking') is-invalid @enderror"
                                                id="owner_quota_parking" name="owner_quota_parking" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_parking') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_parking') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_parking')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Sazonalidade, Tipo de Cota, Café da Manhã Gratuito -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_seasonality" class="form-label fw-semibold">
                                                <i class="fas fa-calendar-alt me-1 text-success"></i>Sazonalidade *
                                            </label>
                                            <select class="form-select @error('owner_quota_seasonality') is-invalid @enderror"
                                                id="owner_quota_seasonality" name="owner_quota_seasonality" required>
                                                <option value="">Selecione</option>
                                                <option value="baixa" {{ old('owner_quota_seasonality') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                                <option value="media" {{ old('owner_quota_seasonality') == 'media' ? 'selected' : '' }}>Média</option>
                                                <option value="alta" {{ old('owner_quota_seasonality') == 'alta' ? 'selected' : '' }}>Alta</option>
                                                <option value="pico" {{ old('owner_quota_seasonality') == 'pico' ? 'selected' : '' }}>Altíssima</option>
                                            </select>
                                            @error('owner_quota_seasonality')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_type" class="form-label fw-semibold">
                                                <i class="fas fa-layer-group me-1 text-success"></i>Tipo de Cota *
                                            </label>
                                            <select class="form-select @error('owner_quota_type') is-invalid @enderror"
                                                id="owner_quota_type" name="owner_quota_type" required>
                                                <option value="">Selecione</option>
                                                <option value="fixa" {{ old('owner_quota_type') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                                                <option value="flexivel" {{ old('owner_quota_type') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                                                <option value="fix_flexivel" {{ old('owner_quota_type') == 'fix_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                                            </select>
                                            <div class="form-text">Informe como o uso da cota está definido no contrato.</div>
                                            @error('owner_quota_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_breakfast" class="form-label fw-semibold">
                                                <i class="fas fa-coffee me-1 text-success"></i>Café da Manhã Gratuito *
                                            </label>
                                            <select class="form-select @error('owner_quota_breakfast') is-invalid @enderror"
                                                id="owner_quota_breakfast" name="owner_quota_breakfast" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_breakfast') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_breakfast') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_breakfast')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_sofa_mais" class="form-label fw-semibold">
                                                <i class="fas fa-couch me-1 text-success"></i>Sofá mais *
                                            </label>
                                            <select class="form-select @error('owner_quota_sofa_mais') is-invalid @enderror"
                                                id="owner_quota_sofa_mais" name="owner_quota_sofa_mais" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_sofa_mais') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_sofa_mais') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_sofa_mais')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="owner_quota_weeks_count" class="form-label fw-semibold">
                                                <i class="fas fa-calendar-week me-1 text-success"></i>Quantas semanas nessa cota você possui neste hotel? *
                                            </label>
                                            <select class="form-select @error('owner_quota_weeks_count') is-invalid @enderror"
                                                id="owner_quota_weeks_count" name="owner_quota_weeks_count" required>
                                                <option value="">Selecione</option>
                                                @for ($week = 1; $week <= 6; $week++)
                                                    <option value="{{ $week }}" {{ old('owner_quota_weeks_count') == $week ? 'selected' : '' }}>{{ $week }}</option>
                                                    @endfor
                                            </select>
                                            @error('owner_quota_weeks_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div id="owner_weeks_container"></div>

                                    <!-- Observações -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="owner_quota_observations" class="form-label fw-semibold">
                                                <i class="fas fa-sticky-note me-1 text-success"></i>Observação do Apartamento/Cota
                                            </label>
                                            <textarea class="form-control @error('owner_quota_observations') is-invalid @enderror"
                                                id="owner_quota_observations" name="owner_quota_observations"
                                                rows="4" placeholder='Escreva em detalhes o que diferencia a sua Cota para melhor atrair o público.
Ex: Vista mar ou do vale, acesso facilitado à piscina, à academia, se tem lareira ou adega, etc.
Possibilidade de serviços extras gratuitos, ou com desconto.
Seja o mais detalhista possivel'>{{ old('owner_quota_observations') }}</textarea>
                                            @error('owner_quota_observations')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Informações oficiais do hotel -->
                                <div class="mb-4">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <!-- <h6 class="fw-bold mb-2">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Informações oficiais do hotel
                            </h6>
                            <p class="text-muted mb-0">Selecione um hotel para ver a descrição e site.</p>--->
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary btn-lg px-4" id="owner_next_button" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>

                        </div>

                        <!-- Seção para quem não possui cota -->
                        <div class="mt-4 d-none" id="no_quota_section">

                            <div class="alert alert-info">
                                <p class="mb-2"><strong>Como usuário sem cota, você terá acesso apenas a:</strong></p>
                                <ul class="mb-0">
                                    <li><i class="fas fa-bed me-2"></i>Alugar cotas de outros usuários</li>
                                    <li><i class="fas fa-shopping-cart me-2"></i>Comprar cotas ou pacotes turísticos</li>
                                </ul>
                            </div>

                            <div class="mt-4">
                                <label class="form-label fw-semibold mb-4" style="font-size: 22px;">
                                    <i class="fas fa-sliders-h me-2 text-primary"></i>Usos permitidos *
                                </label>

                                <!-- Cards de usos permitidos para usuários sem cota -->
                                <div class="allowed-uses-container">
                                    <div class="use-option-card" data-value="rent">
                                        <input type="checkbox" name="allowed_uses[]" id="use_rent_noq" value="rent" required>
                                        <label for="use_rent_noq" class="use-option-label">
                                            <div class="use-option-icon rent">
                                                <i class="fas fa-bed"></i>
                                            </div>
                                            <div class="use-option-content">
                                                <h6 class="use-option-title">Alugar</h6>
                                                <p class="use-option-description">Alugar cotas de outros usuários</p>
                                            </div>
                                            <div class="use-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="use-option-card" data-value="buy">
                                        <input type="checkbox" name="allowed_uses[]" id="use_buy_noq" value="buy">
                                        <label for="use_buy_noq" class="use-option-label">
                                            <div class="use-option-icon buy">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                            <div class="use-option-content">
                                                <h6 class="use-option-title">Comprar</h6>
                                                <p class="use-option-description">Comprar cotas disponíveis</p>
                                            </div>
                                            <div class="use-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="use-option-card disabled" data-value="sell">
                                        <input type="checkbox" id="use_sell_noq" value="sell" disabled>
                                        <label for="use_sell_noq" class="use-option-label">
                                            <div class="use-option-icon sell">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                            <div class="use-option-content">
                                                <h6 class="use-option-title">Vender </h6>
                                                <p class="use-option-description">(bloqueado)</p>
                                            </div>
                                            <div class="use-option-check">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="use-option-card disabled" data-value="exchange">
                                        <input type="checkbox" id="use_exchange_noq" value="exchange" disabled>
                                        <label for="use_exchange_noq" class="use-option-label">
                                            <div class="use-option-icon exchange">
                                                <i class="fas fa-exchange-alt"></i>
                                            </div>
                                            <div class="use-option-content">
                                                <h6 class="use-option-title">Trocar </h6>
                                                <p class="use-option-description">(bloqueado)</p>
                                            </div>
                                            <div class="use-option-check">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <small class="text-muted mt-3 d-block">Sem cota, somente Alugar e Comprar estão disponíveis.</small>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Seção para gestores autorizados -->
                        <div class="mt-4 d-none" id="gestor_section">
                            <h6 class="fw-bold text-warning mb-3 {{ old('has_quota') == '3' ? 'd-none' : '' }}" id="gestor_information_title">
                                <i class="fas fa-user-tie me-2"></i>Informações do Gestor
                            </h6>

                            <div class="alert alert-info border-0 shadow-sm mb-3 d-none" id="gestor_banner_classic" role="status">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Gestor autorizado:</strong> você não é titular da cota e possui autorização do dono para gerenciá-la. Preencha os campos abaixo como na opção &quot;Não, mas tenho autorização para ser gestor&quot;.
                            </div>
                            <div class="alert alert-success border-0 shadow-sm mb-3 d-none" id="gestor_banner_owner_delegate" role="status">
                                <i class="fas fa-handshake me-2"></i>
                                <strong>Titular com delegação:</strong> você possui a cota e autoriza outra pessoa a geri-la. O formulário é o mesmo da opção gestor — preencha todos os campos abaixo.
                            </div>

                            <!-- Campo Hotel Operacional para Gestores -->
                            <div class="mb-4" id="gestor_hotel_operational_section">
                                <label class="form-label fw-semibold mb-3" style="font-size: 18px;">
                                    <i class="fas fa-building me-2 text-primary"></i>O Hotel onde você é(será) Gestor da Cota está em funcionamento? *
                                </label>

                                <div class="hotel-operational-container">
                                    <div class="hotel-option-card" data-value="1">
                                        <input type="radio" name="gestor_hotel_operational" id="gestor_hotel_operational_yes" value="1"
                                            {{ old('gestor_hotel_operational', '1') == '1' ? 'checked' : '' }} required>
                                        <label for="gestor_hotel_operational_yes" class="hotel-option-label">
                                            <div class="hotel-option-icon success">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="hotel-option-content">
                                                <h6 class="hotel-option-title">Sim</h6>
                                                <p class="hotel-option-description">O hotel está em pleno funcionamento</p>
                                            </div>
                                            <div class="hotel-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="hotel-option-card" data-value="0">
                                        <input type="radio" name="gestor_hotel_operational" id="gestor_hotel_operational_no" value="0"
                                            {{ old('gestor_hotel_operational') === '0' ? 'checked' : '' }} required>
                                        <label for="gestor_hotel_operational_no" class="hotel-option-label">
                                            <div class="hotel-option-icon danger">
                                                <i class="fas fa-times-circle"></i>
                                            </div>
                                            <div class="hotel-option-content">
                                                <h6 class="hotel-option-title">Não</h6>
                                                <p class="hotel-option-description">O hotel não está em funcionamento</p>
                                            </div>
                                            <div class="hotel-option-check">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3 d-none" id="gestor_hotel_not_operational_notice">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Aviso!</strong> Não é possível cadastrar cotas quando o hotel não está em funcionamento. Retorne para o cadastro quando ele estiver em funcionamento.
                            </div>

                            <div id="gestor_additional_fields">
                                <div id="classic_gestor_owner_verify_block" class="mb-4 {{ old('has_quota') == '2' ? '' : 'd-none' }}">
                                    <label for="gestor_claimed_owner_cpf" class="form-label fw-semibold">
                                        <i class="fas fa-user-shield me-2 text-primary"></i>Informe o CPF do proprietário da Cota que você será Gestor <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-end">
                                        <input type="text" class="form-control form-control-lg flex-grow-1"
                                            id="gestor_claimed_owner_cpf" autocomplete="off" maxlength="14" placeholder="000.000.000-00"
                                            {{ old('has_quota') == '2' ? '' : 'disabled' }}>
                                        <button type="button" class="btn btn-outline-primary btn-lg flex-shrink-0" id="gestor_verify_owner_btn">
                                            <i class="fas fa-check-circle me-1"></i>Verificar
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1"><b>O dono da Cota precisa estar cadastrado em "Possuo Cota e autorizarei outra pessoa geri-la", ter preenchido devidamente e assinado pelo Gov.br o Termo de Autorização de Gestão de Cota por Terceiro, e ainda ter informado o seu CPF no campo gestor. Você terá que baixar esse documento, checar as informações, assiná-lo pelo Gov.br se estiver de acordo, e inseri-lo no campo adequado quando do seu cadastro no <b>Cota Brasilis</b> para ser liberado seu uso.</b></small>
                                    <input type="hidden" name="gestor_linked_owner_user_id" id="gestor_linked_owner_user_id" value="{{ old('gestor_linked_owner_user_id') }}">

                                    <div id="gestor_owner_verify_success" class="mt-3 d-none" role="status">
                                        <p class="mb-3 text-white" id="gestor_owner_verify_instruction">
                                            Clique no botão baixar para ter em mãos o documento de autorização de gestão de cota por terceiros preenchido pelo cotista:
                                            <strong id="gestor_owner_verify_name" class="text-success"></strong>
                                        </p>
                                        <button type="button" id="gestor_owner_verify_doc_btn" class="btn btn-success btn-lg" disabled>
                                            <i class="fas fa-download me-1"></i>Baixar
                                        </button>
                                    </div>
                                    <div id="gestor_owner_verify_error" class="mt-3 d-none fw-bold text-danger" role="alert"></div>
                                </div>

                                <!-- Documento de Autorização (upload: titular que autoriza gestor — has_quota 3) -->
                                <div id="gestor_classic_authorization_upload_wrap" class="mb-4 {{ old('has_quota') == '2' ? 'd-none' : '' }}">
                                    <label for="gestor_authorization_document" class="form-label fw-semibold">
                                        <i class="fas fa-file-signature me-2 text-primary"></i>
                                        <span id="gestor_authorization_document_label_classic" class="d-none">Documento de Autorização <span class="text-danger">*</span></span>
                                        <span id="gestor_authorization_document_label_owner_delegate" class="{{ old('has_quota') == '3' ? '' : 'd-none' }}">Documento de Autorização de Gestão de Cota por Terceiros. <span class="text-danger">*</span></span>
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('gestor_authorization_document') is-invalid @enderror"
                                        id="gestor_authorization_document" name="gestor_authorization_document"
                                        accept=".pdf,.jpg,.jpeg,.png" {{ old('has_quota') == '3' ? 'required' : '' }}>
                                    <div class="form-text d-none" id="gestor_authorization_document_help_classic">
                                        <strong>Obrigatório:</strong> Documento de autorização do dono da Cota para você, Gestor da Cota, mencionando nome do hotel, números da cota, do bloco e do apartamento, os nomes completos do dono e do gestor com seus CPFs, endereços residenciais, números telefônicos, emails, permitindo ao Gestor usá-lo em seu nome no aplicativo Cota Brasilis para as funções de Aluguel e Troca. Datar e assinar digitalmente o documento pelo Gov.br. Formatos de documento aceitos são PDF, JPG, JPEG, PNG. Tamanho máximo 10MB
                                    </div>
                                    <div class="form-text {{ old('has_quota') == '3' ? '' : 'd-none' }}" id="gestor_authorization_document_help_owner_delegate">
                                        <strong>Obrigatório:</strong> Documento de autorização do dono da Cota a uma outra pessoa, a qual atuará como Gestor da sua Cota. O documento deve ser devidamente preenchido, e assinado digitalmente pelo Gov.br. Para cada Cota a ser gerida por terceiros, deve ser feito esse procedimento. Formatos de documento aceitos são PDF, JPG, JPEG, PNG. Tamanho máximo 10MB
                                    </div>
                                    @error('gestor_authorization_document')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4 {{ old('has_quota') == '3' ? '' : 'd-none' }}" id="owner_delegate_gestor_cpf_wrap">
                                    <label for="gestor_delegate_cpf" class="form-label fw-semibold">
                                        <i class="fas fa-id-card me-2 text-primary"></i>Informe o CPF do gestor da sua cota <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('gestor_delegate_cpf') is-invalid @enderror"
                                        id="gestor_delegate_cpf" name="gestor_delegate_cpf"
                                        value="{{ old('gestor_delegate_cpf') }}"
                                        inputmode="numeric" autocomplete="off" maxlength="14" placeholder="000.000.000-00"
                                        {{ old('has_quota') == '3' ? 'required' : '' }}
                                        {{ old('has_quota') == '3' ? '' : 'disabled' }}>
                                    <small class="text-muted d-block mt-1">Usado para vincular os dados quando o gestor criar conta na plataforma.</small>
                                    @error('gestor_delegate_cpf')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4 {{ old('has_quota') == '2' ? '' : 'd-none' }}" id="gestor_classic_possession_section">
                                    <label for="gestor_quota_contracts" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Documento de confirmação da Posse da Cota *
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('gestor_quota_contracts') is-invalid @enderror @error('gestor_quota_contracts.*') is-invalid @enderror"
                                        id="gestor_quota_contracts" name="gestor_quota_contracts[]"
                                        accept=".pdf,.jpg,.jpeg,.png" multiple
                                        {{ old('has_quota') == '2' ? 'required' : '' }}
                                        {{ old('has_quota') == '2' ? '' : 'disabled' }}>
                                    <div class="form-text">
                                        <strong>Obrigatório:</strong> Foto da primeira folha do contrato de compra e venda da Cota, ou outra folha do contrato, que contenha informações do nome do hotel, nome do titular, CPF, endereço, telefone, email, número da cota, do bloco e do apartamento. Formatos de documentos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB
                                    </div>
                                    @error('gestor_quota_contracts')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('gestor_quota_contracts.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4 d-none" id="owner_delegate_possession_section">
                                    <label for="owner_delegate_possession_confirmation" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Documento de confirmação da Posse da Cota *
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('owner_delegate_possession_confirmation') is-invalid @enderror"
                                        id="owner_delegate_possession_confirmation" name="owner_delegate_possession_confirmation"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">
                                    <b>Obrigatório:</b> Foto da primeira folha do contrato de compra e venda da Cota, ou outra folha do contrato, que contenha informações do nome do hotel, nome do titular, CPF, endereço, telefone, email, número da cota, do bloco e do apartamento.
                                    Formatos de documentos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB
                                    </div>
                                    @error('owner_delegate_possession_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                               <label class="form-label fw-semibold mb-4 mt-4 pt-2">Resumo de identificação da Cota</label>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="gestor_quota_number" class="form-label fw-semibold">Número da cota <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('gestor_quota_number') is-invalid @enderror"
                                            id="gestor_quota_number" name="gestor_quota_number" value="{{ old('gestor_quota_number') }}" required maxlength="50" placeholder="Ex.: 101">
                                        @error('gestor_quota_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="gestor_quota_block" class="form-label fw-semibold">Bloco do apartamento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('gestor_quota_block') is-invalid @enderror"
                                            id="gestor_quota_block" name="gestor_quota_block" value="{{ old('gestor_quota_block') }}" required maxlength="50" placeholder="Ex.: A">
                                        @error('gestor_quota_block')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="gestor_apartment_number" class="form-label fw-semibold">Número do apartamento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg @error('gestor_apartment_number') is-invalid @enderror"
                                            id="gestor_apartment_number" name="gestor_apartment_number" value="{{ old('gestor_apartment_number') }}" required maxlength="50" placeholder="Ex.: 205">
                                        @error('gestor_apartment_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Termo de Autorização de Hospedagem para Terceiros -->
                                <div class="mb-4" id="gestor_quota_facilities_section">
                                    <label for="gestor_hospitality_authorization_term" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Termo de Autorização de Hospedagem para Terceiros
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('gestor_hospitality_authorization_term') is-invalid @enderror"
                                        id="gestor_hospitality_authorization_term" name="gestor_hospitality_authorization_term" accept=".pdf,image/*">
                                    <div class="form-text">
                                        Anexe o termo oficial do hotel onde você cadastrará cotas aqui. Cada hotel tem o seu próprio termo em pdf editável além do sistema digital. Formatos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB.
                                    </div>
                                    @error('gestor_hospitality_authorization_term')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status da Cota e Prazo de Quitação lado a lado (visível apenas se hotel operacional = sim) -->
                                <div class="mb-4" id="gestor_quota_status_section">
                                    <div class="row g-3">
                                        <div class="col-md-6" id="gestor_quota_payment_deadline_section">
                                            <h6 class="fw-semibold mb-2">
                                                <i class="fas fa-credit-card me-2 text-primary"></i>Situação da Cota <span class="text-danger">*</span>
                                            </h6>
                                            <select class="form-select form-select-lg @error('gestor_quota_status') is-invalid @enderror"
                                                name="gestor_quota_status" id="gestor_quota_status" aria-label="Situação da Cota">
                                                <option value="">Selecione o status</option>
                                                <option value="paid" {{ old('gestor_quota_status') == 'paid' ? 'selected' : '' }}>Quitada</option>
                                                <option value="unpaid" {{ old('gestor_quota_status') == 'unpaid' ? 'selected' : '' }}>Não Quitada</option>
                                            </select>
                                            @error('gestor_quota_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold mb-2">
                                                <i class="fas fa-calendar-alt me-2 text-primary"></i>Prazo de Quitação
                                            </h6>
                                            <input type="date" class="form-control form-control-lg @error('gestor_quota_payment_deadline') is-invalid @enderror"
                                                id="gestor_quota_payment_deadline" name="gestor_quota_payment_deadline"
                                                value="{{ old('gestor_quota_payment_deadline') }}"
                                                aria-label="Prazo de Quitação">
                                            @error('gestor_quota_payment_deadline')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Usos permitidos para gestores -->
                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="fas fa-sliders-h me-2 text-primary"></i>Usos permitidos <span class="text-danger">*</span>
                                    </h6>
                                    <small class="d-block mb-3">Escolha quais funcionalidades você quer usar</small>

                                    <!-- Cards de usos permitidos para gestores -->
                                    <div class="allowed-uses-container">
                                        <div class="use-option-card" data-value="rent">
                                            <input type="checkbox" name="gestor_allowed_uses[]" id="gestor_use_rent" value="rent"
                                                {{ is_array(old('gestor_allowed_uses')) && in_array('rent', old('gestor_allowed_uses')) ? 'checked' : '' }}>
                                            <label for="gestor_use_rent" class="use-option-label">
                                                <div class="use-option-icon rent">
                                                    <i class="fas fa-bed"></i>
                                                </div>
                                                <div class="use-option-content">
                                                    <h6 class="use-option-title">Alugar</h6>
                                                    <p class="use-option-description">Oferecer minha Cota ou Fração para aluguel</p>
                                                </div>
                                                <div class="use-option-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="use-option-card" data-value="exchange">
                                            <input type="checkbox" name="gestor_allowed_uses[]" id="gestor_use_exchange" value="exchange"
                                                {{ is_array(old('gestor_allowed_uses')) && in_array('exchange', old('gestor_allowed_uses')) ? 'checked' : '' }}>
                                            <label for="gestor_use_exchange" class="use-option-label">
                                                <div class="use-option-icon exchange">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </div>
                                                <div class="use-option-content">
                                                    <h6 class="use-option-title">Trocar</h6>
                                                    <p class="use-option-description">Trocar o periodo de uso da minha Cota ou Fração por outro</p>
                                                </div>
                                                <div class="use-option-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="use-option-card disabled" data-value="sell">
                                            <input type="checkbox" id="gestor_use_sell" value="sell" disabled>
                                            <label for="gestor_use_sell" class="use-option-label">
                                                <div class="use-option-icon sell">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </div>
                                                <div class="use-option-content">
                                                    <h6 class="use-option-title">Vender</h6>
                                                    <p class="use-option-description">(indisponível para gestor)</p>
                                                </div>
                                                <div class="use-option-check">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="use-option-card disabled" data-value="buy">
                                            <input type="checkbox" id="gestor_use_buy" value="buy" disabled>
                                            <label for="gestor_use_buy" class="use-option-label">
                                                <div class="use-option-icon buy">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </div>
                                                <div class="use-option-content">
                                                    <h6 class="use-option-title">Comprar</h6>
                                                    <p class="use-option-description">(indisponível para gestor)</p>
                                                </div>
                                                <div class="use-option-check">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    @error('gestor_allowed_uses')
                                    <div class="text-danger small mt-3">{{ $message }}</div>
                                    @enderror
                                </div>



                                <!-- Informações da Cota - Novos Campos (apenas gestor clássico; oculto para "autorizo outra pessoa") -->
                                <div class="mb-4" id="gestor_quota_details_classic_only">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-hotel me-2"></i>Informações da Cota e Facilidades
                                    </h6>

                                    <!-- Hotel -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="gestor_hotel_id" class="form-label fw-semibold mb-1">
                                                <i class="fas fa-hotel me-1 text-success"></i>Hotel *
                                            </label>
                                            <div class="alert alert-warning hotel-choice-warning-static small mb-2 mb-md-3 py-2 shadow-sm" role="alert">
                                                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                                                Atenção! No caso de escolha errônea do nome do hotel, você só poderá corrigir com o apoio da equipe de suporte. Isso pode demorar!
                                            </div>
                                            <select class="form-select @error('gestor_hotel_id') is-invalid @enderror"
                                                id="gestor_hotel_id" name="gestor_hotel_id" required>
                                                <option value="">Selecione um hotel</option>
                                                @if(isset($hotels) && count($hotels) > 0)
                                                @foreach($hotels as $hotel)
                                                <option value="{{ $hotel->id }}" {{ old('gestor_hotel_id') == $hotel->id ? 'selected' : '' }}>
                                                    {{ $hotel->name }} - {{ $hotel->city }}/{{ $hotel->state }}
                                                </option>
                                                @endforeach
                                                @else
                                                <option value="1" {{ old('gestor_hotel_id') == '1' ? 'selected' : '' }}>
                                                    Hotel Exemplo - São Paulo/SP
                                                </option>
                                                @endif
                                            </select>
                                            @error('gestor_hotel_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Quartos -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_rooms" class="form-label fw-semibold">
                                                <i class="fas fa-bed me-1 text-success"></i>Quartos *
                                            </label>
                                            <select class="form-select @error('gestor_quota_rooms') is-invalid @enderror"
                                                id="gestor_quota_rooms" name="gestor_quota_rooms" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_rooms') == '1' ? 'selected' : '' }}>1 Quarto</option>
                                                <option value="2" {{ old('gestor_quota_rooms') == '2' ? 'selected' : '' }}>2 Quartos</option>
                                                <option value="3" {{ old('gestor_quota_rooms') == '3' ? 'selected' : '' }}>3 Quartos</option>
                                            </select>
                                            @error('gestor_quota_rooms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Blocos dinâmicos de quartos -->
                                        <div id="gestor-rooms-configuration" class="col-12 mt-4 d-none">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="fas fa-bed me-2"></i>Configuração dos Quartos
                                            </h6>
                                            <small style="font-size: 0.8rem;">Aviso: O número de pessoas deve ser igual ou menor que o número de leitos</small><br>
                                            <div id="gestor-rooms-container">
                                                <!-- Os blocos de quartos serão inseridos aqui dinamicamente -->
                                            </div>
                                        </div>

                                        <!-- Tamanho -->
                                        <div class="col-md-4">
                                            <label for="gestor_quota_size" class="form-label fw-semibold">
                                                <i class="fas fa-expand-arrows-alt me-1 text-success"></i>Tamanho (m²) *
                                            </label>
                                            <input type="text" class="form-control @error('gestor_quota_size') is-invalid @enderror"
                                                id="gestor_quota_size" name="gestor_quota_size"
                                                placeholder="Ex: 45, 50-60, 70+"
                                                value="{{ old('gestor_quota_size') }}" required>
                                            @error('gestor_quota_size')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Varanda -->
                                        <div class="col-md-4">
                                            <label for="gestor_quota_balcony" class="form-label fw-semibold">
                                                <i class="fas fa-door-open me-1 text-success"></i>Varanda *
                                            </label>
                                            <select class="form-select @error('gestor_quota_balcony') is-invalid @enderror"
                                                id="gestor_quota_balcony" name="gestor_quota_balcony" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_balcony') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_balcony') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_balcony')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Cozinha Completa, Vista Mar, Jacuzzi -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_kitchen" class="form-label fw-semibold">
                                                <i class="fas fa-utensils me-1 text-success"></i>Cozinha Completa *
                                            </label>
                                            <select class="form-select @error('gestor_quota_kitchen') is-invalid @enderror"
                                                id="gestor_quota_kitchen" name="gestor_quota_kitchen" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_kitchen') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_kitchen') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_kitchen')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_vista_mar" class="form-label fw-semibold">
                                                <i class="fas fa-water me-1 text-success"></i>Vista Mar *
                                            </label>
                                            <select class="form-select @error('gestor_quota_vista_mar') is-invalid @enderror"
                                                id="gestor_quota_vista_mar" name="gestor_quota_vista_mar" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_vista_mar') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_vista_mar') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_vista_mar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_jacuzzi" class="form-label fw-semibold">
                                                <i class="fas fa-hot-tub me-1 text-success"></i>Jacuzzi *
                                            </label>
                                            <select class="form-select @error('gestor_quota_jacuzzi') is-invalid @enderror"
                                                id="gestor_quota_jacuzzi" name="gestor_quota_jacuzzi" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_jacuzzi') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_jacuzzi') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_jacuzzi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Spa, Piscina, Academia -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_spa" class="form-label fw-semibold">
                                                <i class="fas fa-spa me-1 text-success"></i>Spa *
                                            </label>
                                            <select class="form-select @error('gestor_quota_spa') is-invalid @enderror"
                                                id="gestor_quota_spa" name="gestor_quota_spa" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_spa') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_spa') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_spa')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_piscina" class="form-label fw-semibold">
                                                <i class="fas fa-swimming-pool me-1 text-success"></i>Piscina *
                                            </label>
                                            <select class="form-select @error('gestor_quota_piscina') is-invalid @enderror"
                                                id="gestor_quota_piscina" name="gestor_quota_piscina" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_piscina') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_piscina') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_piscina')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_academia" class="form-label fw-semibold">
                                                <i class="fas fa-dumbbell me-1 text-success"></i>Academia *
                                            </label>
                                            <select class="form-select @error('gestor_quota_academia') is-invalid @enderror"
                                                id="gestor_quota_academia" name="gestor_quota_academia" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_academia') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_academia') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_academia')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Lareira, Adega, Área Kids -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_lareira" class="form-label fw-semibold">
                                                <i class="fas fa-fire me-1 text-success"></i>Lareira *
                                            </label>
                                            <select class="form-select @error('gestor_quota_lareira') is-invalid @enderror"
                                                id="gestor_quota_lareira" name="gestor_quota_lareira" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_lareira') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_lareira') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_lareira')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_adega" class="form-label fw-semibold">
                                                <i class="fas fa-wine-bottle me-1 text-success"></i>Adega *
                                            </label>
                                            <select class="form-select @error('gestor_quota_adega') is-invalid @enderror"
                                                id="gestor_quota_adega" name="gestor_quota_adega" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_adega') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_adega') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_adega')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_area_kids" class="form-label fw-semibold">
                                                <i class="fas fa-child me-1 text-success"></i>Área <i>Kids</i> *
                                            </label>
                                            <select class="form-select @error('gestor_quota_area_kids') is-invalid @enderror"
                                                id="gestor_quota_area_kids" name="gestor_quota_area_kids" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_area_kids') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_area_kids') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_area_kids')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Área de Trabalho, WiFi, Estacionamento Gratuito -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_area_trabalho" class="form-label fw-semibold">
                                                <i class="fas fa-briefcase me-1 text-success"></i>Área de Trabalho *
                                            </label>
                                            <select class="form-select @error('gestor_quota_area_trabalho') is-invalid @enderror"
                                                id="gestor_quota_area_trabalho" name="gestor_quota_area_trabalho" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_area_trabalho') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_area_trabalho') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_area_trabalho')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_wifi" class="form-label fw-semibold">
                                                <i class="fas fa-wifi me-1 text-success"></i>WiFi *
                                            </label>
                                            <select class="form-select @error('gestor_quota_wifi') is-invalid @enderror"
                                                id="gestor_quota_wifi" name="gestor_quota_wifi" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_wifi') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_wifi') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_wifi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_parking" class="form-label fw-semibold">
                                                <i class="fas fa-parking me-1 text-success"></i>Estacionamento Gratuito *
                                            </label>
                                            <select class="form-select @error('gestor_quota_parking') is-invalid @enderror"
                                                id="gestor_quota_parking" name="gestor_quota_parking" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_parking') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_parking') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_parking')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Sazonalidade, Tipo de Cota, Café da Manhã Gratuito -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_seasonality" class="form-label fw-semibold">
                                                <i class="fas fa-calendar-alt me-1 text-success"></i>Sazonalidade *
                                            </label>
                                            <select class="form-select @error('gestor_quota_seasonality') is-invalid @enderror"
                                                id="gestor_quota_seasonality" name="gestor_quota_seasonality" required>
                                                <option value="">Selecione</option>
                                                <option value="baixa" {{ old('gestor_quota_seasonality') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                                <option value="media" {{ old('gestor_quota_seasonality') == 'media' ? 'selected' : '' }}>Média</option>
                                                <option value="alta" {{ old('gestor_quota_seasonality') == 'alta' ? 'selected' : '' }}>Alta</option>
                                                <option value="pico" {{ old('gestor_quota_seasonality') == 'pico' ? 'selected' : '' }}>Altíssima</option>
                                            </select>
                                            <div class="form-text">Conforme o seu contrato de compra e venda</div>
                                            @error('gestor_quota_seasonality')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_type" class="form-label fw-semibold">
                                                <i class="fas fa-layer-group me-1 text-success"></i>Tipo de Cota *
                                            </label>
                                            <select class="form-select @error('gestor_quota_type') is-invalid @enderror"
                                                id="gestor_quota_type" name="gestor_quota_type" required>
                                                <option value="">Selecione</option>
                                                <option value="fixa" {{ old('gestor_quota_type') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                                                <option value="flexivel" {{ old('gestor_quota_type') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                                                <option value="fix_flexivel" {{ old('gestor_quota_type') == 'fix_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                                            </select>
                                            <div class="form-text">Informe como o uso da cota está definido no contrato.</div>
                                            @error('gestor_quota_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_breakfast" class="form-label fw-semibold">
                                                <i class="fas fa-coffee me-1 text-success"></i>Café da Manhã Gratuito *
                                            </label>
                                            <select class="form-select @error('gestor_quota_breakfast') is-invalid @enderror"
                                                id="gestor_quota_breakfast" name="gestor_quota_breakfast" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_breakfast') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_breakfast') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_breakfast')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="gestor_quota_sofa_mais" class="form-label fw-semibold">
                                                <i class="fas fa-couch me-1 text-success"></i>Sofá mais *
                                            </label>
                                            <select class="form-select @error('gestor_quota_sofa_mais') is-invalid @enderror"
                                                id="gestor_quota_sofa_mais" name="gestor_quota_sofa_mais" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('gestor_quota_sofa_mais') == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('gestor_quota_sofa_mais') == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('gestor_quota_sofa_mais')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="gestor_quota_weeks_count" class="form-label fw-semibold">
                                                <i class="fas fa-calendar-week me-1 text-success"></i>Quantas semanas nessa cota você possui neste hotel? *
                                            </label>
                                            <select class="form-select @error('gestor_quota_weeks_count') is-invalid @enderror"
                                                id="gestor_quota_weeks_count" name="gestor_quota_weeks_count" required>
                                                <option value="">Selecione</option>
                                                @for ($week = 1; $week <= 6; $week++)
                                                    <option value="{{ $week }}" {{ old('gestor_quota_weeks_count') == $week ? 'selected' : '' }}>{{ $week }}</option>
                                                    @endfor
                                            </select>
                                            @error('gestor_quota_weeks_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div id="gestor_weeks_container"></div>

                                    <!-- Observações -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="gestor_quota_observations" class="form-label fw-semibold">
                                                <i class="fas fa-sticky-note me-1 text-success"></i>Observações do apartamento / cota
                                            </label>
                                            <textarea class="form-control @error('gestor_quota_observations') is-invalid @enderror"
                                                id="gestor_quota_observations" name="gestor_quota_observations"
                                                rows="4" placeholder='Escreva em detalhes o que diferencia a sua Cota para melhor atrair o público.
Ex: Vista mar ou do vale, acesso facilitado à piscina, à academia, se tem lareira ou adega, etc.
Possibilidade de serviços extras gratuitos, ou com desconto.
Seja o mais detalhista possivel'>{{ old('gestor_quota_observations') }}</textarea>
                                            @error('gestor_quota_observations')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Informações oficiais do hotel -->
                                <div class="mb-4">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <!-- <h6 class="fw-bold mb-2">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Informações oficiais do hotel
                            </h6>
                            <p class="text-muted mb-0">Selecione um hotel para ver a descrição e site.</p>--->
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary btn-lg px-4" id="gestor_next_button" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Step 5: Profile Selection -->
                    <div class="step d-none" id="step5">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-user-tag me-2"></i>Escolha Seu Perfil
                        </h5>

                        <!-- Horizontal Profile Cards -->
                        <div class="profile-cards-container">
                            <!-- Curioso Card -->
                            <div class="profile-card-horizontal" data-profile="curioso">
                                <div class="profile-card-content">
                                    <div class="profile-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div class="profile-info">
                                        <div class="profile-header">
                                            <h4 class="profile-title">Curioso</h4>
                                            <p class="profile-subtitle">Para iniciar bem</p>
                                        </div>
                                        <div class="profile-features">
                                            <div class="feature-item">
                                                <i class="fas fa-close" style="color: red;"></i>
                                                <span>Não <b>Fraciona</b> cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-close" style="color: red;"></i>
                                                <span>Não escolhe cidade para enviar <b>Alerta</b> de Ofertas</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Escolhe <b>Destacar</b> a publicação da Cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>No uso <b>COMPRAR</b> tem acesso aos nomes do hotel, tipo e preço das cotas à venda</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>3 <b>Leilões</b> por período e por cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Acesso a 3 opções no resultado das buscas, a cada 24 horas e por uso</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa do <b>Super Desconto</b> que inicia 14 dias antes do começo da validade da cota ou fração</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa da <b><b>Mega Oferta</b></b> iniciada 3 dias antes do começo da validade da cota ou fração</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Acesso aos vídeos básicos e explicativos sobre o funcionamento do aplicativo </span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Sofá Mais</b> - Opção de <b>Alugar</b> 1 ou 2 leitos no sofá-cama</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Torei na Véspera </b> - outra opotunidade para não deixar suas diárias expirarem sem uso. Ajuste o desconto em pelo menos 40% do valor inicial anunciado.</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Torei no Dia </b> - última opotunidade para não deixar suas diárias expirarem sem uso. Ajuste o desconto em pelo menos 55% do valor inicial anunciado.</span>
                                            </div>
                                            <div class="feature-item d-flex justify-content-between align-items-start flex-wrap gap-2" style="flex-direction: row;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-check me-2"></i>
                                                    <span>Participa da Oferta única</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-check me-2"></i>
                                                <span>Taxa de êxito Curioso: 
                                                    @if(isset($successFees['curioso']) && $successFees['curioso']->count() > 0)
                                                        @foreach($successFees['curioso'] as $index => $fee)
                                                            R$ {{ number_format($fee->fee_amount, 2, ',', '.') }} / {{ $fee->days }} dia{{ $fee->days > 1 ? 's' : '' }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @else
                                                        Taxas não disponíveis
                                                    @endif
                                                    /aluguel. Troca por R$120,00 no meu hotel, e R$175,00 em outro hotel.
                                                </span>
                                            </div>
                                         

                                        </div>
                                    </div>
                                    <div class="profile-pricing">
                                        <div class="price">
                                            <span class="price-amount"></span>
                                        </div>
                                        <div class="profile-radio">
                                            <input class="form-check-input" type="radio" name="profile_type"
                                                id="profile_curioso" value="curioso" {{ old('profile_type') == 'curioso' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="profile_curioso">
                                                Escolher
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inteligente Card -->
                            <div class="profile-card-horizontal popular" data-profile="inteligente">
                                <div class="popular-badge">
                                    <span>Mais Popular</span>
                                </div>
                                <div class="profile-card-content">
                                    <div class="profile-icon">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <div class="profile-info">
                                        <div class="profile-header">
                                            <h4 class="profile-title">Inteligente</h4>
                                            <p class="profile-subtitle">Para usar bem</p>
                                        </div>
                                        <div class="profile-features">
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Fraciona</b> cotas em 3 e 4 dias.</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Escolhe 2 cidade, por uso e por mês, para enviar <b>Alerta</b> de Ofertas</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Escolhe <b>Destacar</b> a publicação da Cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>No uso <b>COMPRAR</b>, além do que acessa o Curioso, tem os preços com desconto no dia da busca</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>2 <b>Leilões</b> por mês, por uso, por período e por cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Acesso a 5 opções no resultado das buscas, a cada 24 horas e por uso</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa do <b>Super Desconto</b> que inicia 14 dias antes do começo da validade da cota ou fração</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa da <b>Mega Oferta</b>, iniciada 5 dias antes do começo da validade da cota ou fração </span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa da <b>Oferta Única</b></span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Acesso aos <b>Vídeos</b> do Curioso e mais aos do <b>Leilão</b>, alertas, Troca de Perfil</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Sofá Mais</b> - Opção de <b>Alugar</b> 1 ou 2 leitos no sofá-cama</span>
                                            </div>
                                               <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Torei na Véspera </b> - outra opotunidade para não deixar suas diárias expirarem sem uso. Ajuste o desconto em pelo menos 40% do valor inicial anunciado.</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Torei no Dia </b> - última opotunidade para não deixar suas diárias expirarem sem uso. Ajuste o desconto em pelo menos 55% do valor inicial anunciado.</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Taxas de êxito Inteligente: 
                                                    @if(isset($successFees['inteligente']) && $successFees['inteligente']->count() > 0)
                                                        @foreach($successFees['inteligente'] as $index => $fee)
                                                            R$ {{ number_format($fee->fee_amount, 2, ',', '.') }} / {{ $fee->days }} dia{{ $fee->days > 1 ? 's' : '' }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @else
                                                        Taxas não disponíveis
                                                    @endif
                                                    para aluguel e troca(em qualquer hotel)
                                                </span>
                                            </div>
                                         
                                        </div>
                                    </div>
                                    <div class="profile-pricing">
                                        <div class="price">
                                            <span class="price-amount"></span>
                                        </div>
                                        <div class="profile-radio">
                                            <input class="form-check-input" type="radio" name="profile_type"
                                                id="profile_inteligente" value="inteligente" {{ old('profile_type') == 'inteligente' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="profile_inteligente">
                                                Escolher
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sábio Card -->
                            <div class="profile-card-horizontal" data-profile="sabio">
                                <div class="profile-card-content">
                                    <div class="profile-icon">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <div class="profile-info">
                                        <div class="profile-header">
                                            <h4 class="profile-title">Sábio</h4>
                                            <p class="profile-subtitle">Para usar como profissional</p>
                                        </div>
                                        <div class="profile-features">
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Fraciona</b> cota em 2x2 + 1x3, 1x3 + 1x4, 1x2 + 1x5</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Escolhe 4 cidades, por uso e por mês, para enviar <b>Alerta</b></span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Escolhe <b>Destacar</b> a publicação da Cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>No uso <b>Comprar</b>, além do acesso do Inteligente, acessa os descontos programados para toda aquela semana</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>3 <b>Leilões</b> por mês, por uso, por período e por cota</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Acesso a 10 opções no resultado das buscas, a cada 24 horas e por uso</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa do <b>Super Desconto</b> que inicia 14 dias antes do começo da validade da cota ou fração</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa da <b>Mega Oferta</b> iniciada 7 dias antes do começo da validade da cota ou fração</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Participa da <b>Oferta Única</b></span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Acesso aos vídeos do Inteligente e mais aos do <b>Super Desconto</b>, <b>Mega Oferta</b>, <b>Oferta Única</b>, alguns dos hotéis cadastrados, realidade da multiproriedade hoteleira, dúvidas e orientações mais recorrentes dos cotistas, distrato, compra e venda de cotas, etc..</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Compra</b> e <b>Venda</b> da sua cota realizada por profissionais do Turismo do Cota Brasilis</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Sofá Mais</b> - Opção de <b>Alugar</b> 1 ou 2 leitos no sofá-cama</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Torei na Véspera </b> - outra opotunidade para não deixar suas diárias expirarem sem uso. Ajuste o desconto em pelo menos 40% do valor inicial anunciado.</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span><b>Torei no Dia </b> - última opotunidade para não deixar suas diárias expirarem sem uso. Ajuste o desconto em pelo menos 55% do valor inicial anunciado.</span>
                                            </div>
                                            <div class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>Taxas de êxito Sábio: 
                                                    @if(isset($successFees['sabio']) && $successFees['sabio']->count() > 0)
                                                        @foreach($successFees['sabio'] as $index => $fee)
                                                            R$ {{ number_format($fee->fee_amount, 2, ',', '.') }} / {{ $fee->days }} dia{{ $fee->days > 1 ? 's' : '' }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @else
                                                        Taxas não disponíveis
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="profile-pricing">
                                        <div class="price">
                                            <span class="price-amount"></span>
                                        </div>
                                        <div class="profile-radio">
                                            <input class="form-check-input" type="radio" name="profile_type"
                                                id="profile_sabio" value="sabio" {{ old('profile_type') == 'sabio' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="profile_sabio">
                                                Escolher
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('profile_type')
                        <div class="text-danger small mt-3 text-center">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-primary btn-lg px-4" onclick="nextStep()">
                                Próximo <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
            </div>

            <!-- Step 6: Quota Fractionation -->
            <div class="step d-none" id="step6">
                <h5 class="fw-bold mb-4 text-primary">
                    <i class="fas fa-layer-group me-2"></i>Publicações da cota (inteira ou fracionada)
                </h5>

                <!-- Campo oculto para enviar o JSON completo do fracionamento ao back-end -->
                <input type="hidden" name="fraction_details_json" id="fraction_details_json" value="">

                <!-- Container dinâmico para seções de fracionamento por semana -->
                <div id="fraction_weeks_container"></div>

                <!-- Mensagem para Curioso (mantida para referência, mas não será mais usada diretamente) -->
                <div id="fraction_curioso" class="d-none">
                    <div class="alert alert-info" role="alert" style="display: block !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Perfil Curioso:</strong> Neste perfil, você não fraciona sua cota. Você utilizará os 7 dias completos por período.
                        <hr>
                        <div class="small">
                            <i class="fas fa-gavel me-2"></i><strong>Leilão:</strong> 3 para aluguel, 3 para troca, 3 para compra, 3 para venda, por mês<br>
                            <i class="fas fa-bell me-2"></i><strong>Alerta:</strong> 0 para aluguel, 0 para troca, 0 para compra, 0 para venda, por mês<br>
                            <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> até o final do período da Cota no ano vigente<br>
                            <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> não pode fracionar a(s) semana(s) da cota
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-calendar-week fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold mb-2">Uso Integral</h5>
                            <p class="text-muted mb-0">{{ \App\Models\SuccessFee::formatFractionPrices('curioso', '7') }}</p>
                            <div class="mt-3">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opções para Inteligente -->
                <div id="fraction_inteligente" class="d-none">
                    <div class="alert alert-info" role="alert" style="display: block !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Perfil Inteligente:</strong> Escolha como deseja fracionar seus 7 dias de cota
                        <hr>
                        <div class="small">
                            <i class="fas fa-gavel me-2"></i><strong>Leilão:</strong> 2 para aluguel, 2 para troca, 2 para compra, 2 para venda, por mês<br>
                            <i class="fas fa-bell me-2"></i><strong>Alerta:</strong> 1 para aluguel, 1 para troca, 1 para compra, 1 para venda, por mês<br>
                            <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> até o final do período da Cota no ano vigente<br>
                            <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> pode fracionar ou não a(s) semana(s) da cota, e ofertar o que desejar delas
                        </div>
                    </div>


                    <div class="row g-3 container">
                        <div class="col-md-4">
                            <div class="card fraction-card h-100" data-value="7">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_integral_inteligente" value="7" class="form-check-input" checked>
                                    <label for="fraction_integral_inteligente" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-week fa-3x text-info mb-4"></i>
                                            <h5 class="fw-bold mb-3">Sem fracionar</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">
                                                {{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '7') }}
                                            </p>
                                            <div class="mt-4">
                                                <span></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card fraction-card h-100" data-value="3_4">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_3_4" value="3_4" class="form-check-input">
                                    <label for="fraction_3_4" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-alt fa-3x text-primary mb-4"></i>
                                            <h5 class="fw-bold mb-3">3 + 4 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">
                                                {{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '3_4') }}
                                            </p>

                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card fraction-card h-100" data-value="4_3">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_4_3" value="4_3" class="form-check-input">
                                    <label for="fraction_4_3" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-alt fa-3x text-success mb-4"></i>
                                            <h5 class="fw-bold mb-3">4 + 3 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">
                                                {{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '4_3') }}
                                            </p>
                                            <div class="mt-4">

                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opções para Sábio -->
                <div id="fraction_sabio" class="d-none">
                    <div class="alert alert-info" role="alert" style="display: block !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Perfil Sábio:</strong> Escolha a melhor combinação de fracionamento para seus 7 dias
                        <hr>
                        <div class="small">
                            <i class="fas fa-gavel me-2"></i><strong>Leilão:</strong> 3 para aluguel, 3 para troca, 3 para compra, 3 para venda, por mês<br>
                            <i class="fas fa-bell me-2"></i><strong>Alerta:</strong> 3 para aluguel, 3 para troca, 3 para compra, 3 para venda, por mês<br>
                            <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> até o final do período da Cota no ano vigente<br>
                            <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> pode fracionar ou não a(s) semana(s) da cota, e ofertar o que desejar delas
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="7">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_7" value="7" class="form-check-input">
                                    <label for="fraction_7" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-week fa-3x text-info mb-4"></i>
                                            <h5 class="fw-bold mb-3">Sem fracionar</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '7') }}</p>
                                            <div class="mt-4">
                                                <span></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="2_2_3">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_2_2_3" value="2_2_3" class="form-check-input">
                                    <label for="fraction_2_2_3" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-check fa-3x text-primary mb-4"></i>
                                            <h5 class="fw-bold mb-3">2 + 2 + 3 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '2_2_3') }}</p>
                                            <div class="mt-4">

                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="3_4">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_sabio_3_4" value="3_4" class="form-check-input">
                                    <label for="fraction_sabio_3_4" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-check fa-3x text-success mb-4"></i>
                                            <h5 class="fw-bold mb-3">3 + 4 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '3_4') }}</p>
                                            <div class="mt-4">
                                                <span></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="2_5">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_2_5" value="2_5" class="form-check-input">
                                    <label for="fraction_2_5" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-check fa-3x text-warning mb-4"></i>
                                            <h5 class="fw-bold mb-3">2 + 5 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '2_5') }}</p>
                                            <div class="mt-4">
                                                <span></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="3_2_2">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_3_2_2" value="3_2_2" class="form-check-input">
                                    <label for="fraction_3_2_2" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-plus fa-3x text-primary mb-4"></i>
                                            <h5 class="fw-bold mb-3">3 + 2 + 2 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '3_2_2') }}</p>
                                            <div class="mt-4">

                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="4_3">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_4_3" value="4_3" class="form-check-input">
                                    <label for="fraction_4_3" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-plus fa-3x text-success mb-4"></i>
                                            <h5 class="fw-bold mb-3">4 + 3 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '4_3') }}</p>
                                            <div class="mt-4">
                                                <span></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card fraction-card h-100" data-value="5_2">
                                <div class="card-body">
                                    <input type="radio" name="fraction_type" id="fraction_5_2" value="5_2" class="form-check-input">
                                    <label for="fraction_5_2" class="fraction-label w-100">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-plus fa-3x text-warning mb-4"></i>
                                            <h5 class="fw-bold mb-3">5 + 2 dias</h5>
                                            <hr class="my-3" style="border-color: #e0e0e0;">
                                            <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '5_2') }}</p>
                                            <div class="mt-4">
                                                <span></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Calendar Fields -->
                <div id="calendar-fields" class="mt-4 d-none">
                    <h6 class="fw-bold mb-3 text-secondary">
                        <i class="fas fa-calendar-alt me-2"></i>Selecione os períodos de hospedagem
                    </h6>
                    <div id="calendar-containers">
                        <!-- Calendários serão inseridos aqui dinamicamente -->
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </button>
                    <button type="button" class="btn btn-primary btn-lg px-5" onclick="nextStep()">
                        Próximo <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 7: Terms and Digital Signature -->
        <div class="step d-none" id="step7">


            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-file-contract me-2 text-primary"></i>Aceite de Termos e Condições, e Políticas de Privacidade. Li e aceito os Termos e Condições de Uso, e Políticas de Privacidade da plataforma.
                    </h6>


                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="accept_terms"
                            id="accept_terms" value="1" required>
                        <label class="form-check-label fw-semibold" for="accept_terms">
                            Li e aceito os Termos e Condições de Uso e Politicas de Privacidade da plataforma*
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="accept_promotional_periods"
                            id="accept_promotional_periods" value="1">
                        <label class="form-check-label fw-semibold" for="accept_promotional_periods">
                        Ciente e Aceito participar de descontos automáticos, ofertas e promoções dessa plataforma.
                        </label>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </button>
                <button type="submit" class="btn btn-success btn-lg px-5" id="finalSubmit" disabled>
                    <i class="fas fa-check me-2"></i>Concluir Cadastro
                </button>
            </div>
        </div>
    </div>
    </form>

    <div class="text-center mt-4">
        <p class="mb-0 text-muted">Já tem uma conta?
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">
                Faça login aqui
            </a>
        </p>
    </div>
</div>
</div>
@endsection

@push('styles')
<style>
    .profile-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .profile-card.selected {
        border: 3px solid var(--primary-color) !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .step {
        min-height: 400px;
    }

    /* Aviso sob o rótulo "Hotel": sempre visível (layout global anima .alert; aqui forçamos exibição fixa). */
    .hotel-choice-warning-static {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        animation: none !important;
        position: relative;
        z-index: 2;
    }

    .form-check-input:checked+.form-check-label {
        color: var(--primary-color);
        font-weight: 600;
    }

    /* Fraction Cards Styles */
    .fraction-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
        border-radius: 15px;
        overflow: hidden;
    }

    .fraction-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 30px rgba(0, 123, 255, 0.2);
        border-color: #007bff;
        background: linear-gradient(to bottom, #f8f9ff, #ffffff);
    }

    .fraction-card.selected {
        border: 3px solid #009739;
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 35px rgba(0, 151, 57, 0.25);
        background: linear-gradient(to bottom, #e8f5e9, #ffffff);
    }

    .fraction-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .fraction-label {
        margin: 0;
        width: 100%;
        cursor: pointer;
    }

    .fraction-card .card-body {
        padding: 2rem 1.5rem;
    }

    .fraction-card .fas {
        transition: all 0.3s ease;
    }

    .fraction-card:hover .fas,
    .fraction-card.selected .fas {
        transform: scale(1.1);
    }

    .fraction-card h5 {
        font-weight: 700;
        color: #2c3e50;
        margin-top: 1rem;
    }

    .fraction-card p {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .fraction-card .badge {
        padding: 0.5rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
    }

    /* Calendar Fields Styles */
    #calendar-fields {
        animation: slideInUp 0.4s ease-out;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #calendar-containers .card {
        border-left: 4px solid #009739;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    #calendar-containers .card:hover {
        box-shadow: 0 8px 25px rgba(0, 151, 57, 0.15);
        transform: translateX(5px);
    }

    #calendar-containers h6 {
        color: #009739;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e8f5e9;
    }

    #calendar-containers .form-label {
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
    }

    #calendar-containers input[type="date"] {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 0.85rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    #calendar-containers input[type="date"]:focus {
        border-color: #009739;
        box-shadow: 0 0 0 0.25rem rgba(0, 151, 57, 0.2);
        transform: translateY(-2px);
    }

    #calendar-containers small {
        font-size: 0.85rem;
        display: block;
        margin-top: 0.5rem;
        color: #6c757d;
    }

    /* Room Blocks Styles */
    .room-block {
        transition: all 0.3s ease;
        border: 2px solid #e9ecef !important;
    }

    .room-block:hover {
        border-color: #007bff !important;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }

    .room-block h6 {
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
    }

    /* Enhanced Layout Styles */
    .step {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Progress Bar Animation */
    .progress-bar {
        transition: width 0.6s ease;
    }

    /* Enhanced Card Styles */
    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    /* Enhanced Input Styles */
    .form-control,
    .form-select {
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #009739;
        box-shadow: 0 0 0 0.2rem rgba(0, 151, 57, 0.15);
        transform: translateY(-1px);
    }

    /* Enhanced Button Styles */
    .btn {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn:hover::before {
        width: 300px;
        height: 300px;
    }

    /* Improved Section Spacing */
    .step h5 {
        padding-bottom: 15px;
        border-bottom: 3px solid #009739;
        position: relative;
    }

    .step h5::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 50px;
        height: 3px;
        background: #007A2F;
    }

    /* Enhanced Alert Styles */
    .alert {
        border-left: 4px solid;
        animation: slideInLeft 0.4s ease;
    }

    @keyframes slideInLeft {
        from {
            transform: translateX(-30px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Better Form Layout */
    .row.g-3>div {
        margin-bottom: 1rem;
    }

    /* Enhanced Labels */
    .form-label {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.75rem;
    }

    .form-label i {
        margin-right: 8px;
    }

    /* Smooth Transitions */
    * {
        transition: all 0.3s ease;
    }

    /* Responsive Improvements */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }

        .step h5 {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 1rem !important;
        }
    }
    /* Fraction card styles */
    .fraction-card {
        cursor: pointer;
        transition: transform 260ms ease, box-shadow 260ms ease;
        border-radius: 12px;
    }
    .fraction-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    .fraction-card.selected {
        border: 2px solid #198754;
        box-shadow: 0 12px 36px rgba(25,135,84,0.12);
    }

    /* Progress bar visual */
    #fraction_weeks_stepper .progress {
        margin-top: 8px;
    }

    /* Animation for switching weeks */
    .fade-in {
        animation: fadeInSlide 320ms ease both;
    }
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateX(12px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* Invalid field stronger highlight */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.15rem rgba(220,53,69,0.15);
    }
    .invalid-feedback i { color: #dc3545; margin-right: 6px; }

    /* Larger primary buttons */
    .btn-md {
        padding: .5rem .9rem;
        font-size: .95rem;
        border-radius: .5rem;
    }

</style>
@endpush

@push('scripts')
<!-- jQuery (necessário para busca de CEP) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    @php
        $__registerAjaxRoot = rtrim(request()->root(), '/');
    @endphp
    const registerAjaxCheckEmail = @json($__registerAjaxRoot . route('register.check_email', [], false));
    const registerAjaxCheckCpf = @json($__registerAjaxRoot . route('register.check_cpf', [], false));
    const registerAjaxCheckDelegatedGestor = @json($__registerAjaxRoot . route('register.check_delegated_gestor', [], false));
    const registerAjaxDownloadDelegatedGestorDoc = @json($__registerAjaxRoot . route('register.download_delegated_gestor_document', [], false));

    let currentStep = 1;
    const totalSteps = 7;

    function setSectionEnabled(section, enabled) {
        if (!section) {
            return;
        }

        const fields = section.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            if (enabled) {
                if (field.dataset.disabledByToggle === 'true') {
                    field.disabled = false;
                    delete field.dataset.disabledByToggle;
                }
                if (field.dataset.originalRequired === 'true') {
                    field.required = true;
                }
            } else {
                if (!field.disabled) {
                    field.dataset.disabledByToggle = 'true';
                }
                field.disabled = true;
                if (field.required) {
                    field.dataset.originalRequired = 'true';
                }
                field.required = false;

                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;
                } else if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else if (field.type !== 'hidden' && field.type !== 'file') {
                    field.value = '';
                } else if (field.type === 'file') {
                    field.value = '';
                }
            }
        });
    }

    let ownerAdditionalFieldsContainer = null;
    let ownerAdditionalFieldsInputs = [];
    let ownerHotelNotice = null;
    let ownerNextButton = null;

    let gestorAdditionalFieldsContainer = null;
    let gestorAdditionalFieldsInputs = [];
    let gestorHotelNotice = null;
    let gestorNextButton = null;

    function appendErrorToStep(stepId, errorElement) {
        const stepElement = document.getElementById(stepId);
        if (!stepElement || !errorElement) {
            return;
        }
        const target = stepElement.querySelector('.card-body') || stepElement;
        target.appendChild(errorElement);
    }

    // Function to update progress bar (removed - barra de progresso foi removida)
    function updateProgressBar() {
        // Barra de progresso removida - função mantida para compatibilidade
    }

    // Function to validate field length and show error message
    function validateFieldLength(fieldId, value, minLength, errorMessage) {
        const field = document.getElementById(fieldId);
        const numericValue = value.replace(/\D/g, '');

        // Remove existing error message
        const existingError = field.parentNode.querySelector('.field-error-message');
        if (existingError) {
            existingError.remove();
        }

        // Remove existing error styling
        field.classList.remove('is-invalid');

        if (numericValue.length > 0 && numericValue.length < minLength) {
            // Add error styling
            field.classList.add('is-invalid');

            // Create and add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error-message invalid-feedback d-block';
            errorDiv.textContent = errorMessage;
            field.parentNode.appendChild(errorDiv);

            return false;
        }

        return true;
    }

    function validateUserName(showErrors = true) {
        const nameField = document.getElementById('name');

        if (!nameField) {
            return false;
        }

        const nameValue = (nameField.value || '').trim();

        const existingErrors = nameField.parentNode.querySelectorAll('.name-error-message');
        existingErrors.forEach(error => error.remove());
        nameField.classList.remove('is-invalid');

        if (nameValue.length === 0) {
            return false;
        }

        if (nameValue.length < 6) {
            if (showErrors) {
                nameField.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'name-error-message invalid-feedback d-block';
                errorDiv.textContent = 'Nome de usuário deve ter pelo menos 6 caracteres';
                nameField.parentNode.appendChild(errorDiv);
            }
            return false;
        }

        return true;
    }

    function validateFullName(showErrors = true) {
        const fullNameField = document.getElementById('full_name');

        if (!fullNameField) {
            return false;
        }

        const fullNameValue = (fullNameField.value || '').trim();

        const existingErrors = fullNameField.parentNode.querySelectorAll('.full-name-error-message');
        existingErrors.forEach(error => error.remove());
        fullNameField.classList.remove('is-invalid');

        if (fullNameValue.length === 0) {
            return false;
        }

        if (fullNameValue.length < 10) {
            if (showErrors) {
                fullNameField.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'full-name-error-message invalid-feedback d-block';
                errorDiv.textContent = 'O nome completo deve ter pelo menos 10 caracteres';
                fullNameField.parentNode.appendChild(errorDiv);
            }
            return false;
        }

        return true;
    }

    // Function to validate password match and minimum length
    function validatePasswordMatch(showErrors = true) {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('password_confirmation');

        if (!passwordField || !confirmPasswordField) {
            return false;
        }

        const password = passwordField.value || '';
        const confirmPassword = confirmPasswordField.value || '';

        // Remove existing error messages
        const removeErrors = (field) => {
            const messages = field.parentNode.querySelectorAll('.password-error-message');
            messages.forEach(message => message.remove());
            field.classList.remove('is-invalid');
        };

        removeErrors(passwordField);
        removeErrors(confirmPasswordField);

        let isValid = true;

        const addError = (field, message) => {
            if (!showErrors) {
                return;
            }
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'password-error-message invalid-feedback d-block';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        };

        if (password.length < 8) {
            isValid = false;
            addError(passwordField, 'A senha deve ter pelo menos 8 caracteres');
        }

        if (confirmPassword.length < 8) {
            isValid = false;
            addError(confirmPasswordField, 'A confirmação deve ter pelo menos 8 caracteres');
        }

        if (password.length >= 8 && confirmPassword.length >= 8 && password !== confirmPassword) {
            isValid = false;
            addError(passwordField, 'As senhas não coincidem');
            addError(confirmPasswordField, 'As senhas não coincidem');
        }

        if (isValid) {
            passwordField.classList.remove('is-invalid');
            confirmPasswordField.classList.remove('is-invalid');
        }

        return isValid;
    }

    function updateStep1ValidationState() {
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('password_confirmation');
        const nextButton = document.getElementById('step1_next_button');

        if (!passwordField || !confirmPasswordField || !nextButton) {
            return;
        }

        const nameValid = validateUserName(false);
        const passwordValid = validatePasswordMatch(false);

        nextButton.disabled = !(nameValid && passwordValid);
    }

    // Function to check if all required fields are valid
    function areAllFieldsValid() {
        const cpfField = document.getElementById('cpf');
        const phoneField = document.getElementById('phone');
        const cepField = document.getElementById('cep');

        const cpfValue = cpfField.value.replace(/\D/g, '');
        const phoneValue = phoneField.value.replace(/\D/g, '');
        const cepValue = cepField.value.replace(/\D/g, '');

        const fullNameValid = validateFullName();
        const cpfValid = validateFieldLength('cpf', cpfValue, 11, 'CPF deve ter 11 dígitos');
        const phoneValid = validateFieldLength('phone', phoneValue, 11, 'Telefone deve ter 11 dígitos');
        const cepValid = validateFieldLength('cep', cepValue, 8, 'CEP deve ter 8 dígitos');

        const complementField = document.getElementById('complement');
        let complementValid = true;
        if (complementField) {
            complementValid = complementField.value.trim().length > 0;
            complementField.classList.toggle('is-invalid', !complementValid);
        }

        return fullNameValid && cpfValid && phoneValid && cepValid && complementValid;
    }

    // Função para configurar o Step 6 baseado no perfil selecionado
    function configureFractionationStep(profileValue) {
        // Esconder todas as opções de fracionamento antigas (mantidas para backward compatibility)
        const oldCurioso = document.getElementById('fraction_curioso');
        const oldInteligente = document.getElementById('fraction_inteligente');
        const oldSabio = document.getElementById('fraction_sabio');
        
        if (oldCurioso) oldCurioso.classList.add('d-none');
        if (oldInteligente) oldInteligente.classList.add('d-none');
        if (oldSabio) oldSabio.classList.add('d-none');

        // Atualizar semanas autorizadas e renderizar seções dinâmicas
        updateAuthorizedWeeks();
        setTimeout(() => {
            renderFractionSectionsForWeeks();
        }, 100);
    }

    // Função para renderizar seções de fracionamento para cada semana
    function renderFractionSectionsForWeeks() {
        const container = document.getElementById('fraction_weeks_container');
        if (!container) return;

        // Limpar container
        container.innerHTML = '';

        // Obter semanas autorizadas
        const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
        if (!authorizedWeeks.length) {
            container.innerHTML = '<div class="alert alert-warning">Nenhuma semana autorizada encontrada. Por favor, autorize pelo menos uma semana no passo anterior.</div>';
            return;
        }

        // Obter perfil selecionado
        const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
        if (!selectedProfile) {
            container.innerHTML = '<div class="alert alert-warning">Por favor, selecione um perfil primeiro.</div>';
            return;
        }

        const profileType = selectedProfile.value;

        // Se for perfil curioso, mostrar mensagem especial
        if (profileType === 'curioso') {
            authorizedWeeks.forEach(weekNumber => {
                const weekSection = createCuriosoFractionSection(weekNumber);
                container.appendChild(weekSection);
            });
            return;
        }

        // Para outros perfis, criar seção de fracionamento para cada semana
        authorizedWeeks.forEach(weekNumber => {
            const weekSection = createFractionSectionForWeek(weekNumber, profileType);
            // mark week number
            weekSection.setAttribute('data-week-number', weekNumber);
            container.appendChild(weekSection);
        });

        // Inicializar interatividade dos cards
        initializeFractionCards();

        // Show all fraction week sections stacked (one below another). Interactivity is handled per-week.
    }

    // Função para criar seção de fracionamento para perfil Curioso
    function createCuriosoFractionSection(weekNumber) {
        const section = document.createElement('div');
        section.className = 'mb-5';
        section.innerHTML = `
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-week me-2"></i>Semana ${weekNumber}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert" style="display: block !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Perfil Curioso:</strong> Neste perfil, você não fraciona sua cota. Você utilizará os 7 dias completos por período.
                        <hr>
                        <div class="small">
                            <i class="fas fa-gavel me-2"></i><strong>Leilão:</strong> 3 para aluguel, 3 para troca, 3 para compra, 3 para venda, por mês<br>
                            <i class="fas fa-bell me-2"></i><strong>Alerta:</strong> 0 para aluguel, 0 para troca, 0 para compra, 0 para venda, por mês<br>
                            <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> até o final do período da Cota no ano vigente<br>
                            <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> não pode fracionar a(s) semana(s) da cota
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-calendar-week fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold mb-2">Uso Integral</h5>
                            <p class="text-muted mb-0">{{ \App\Models\SuccessFee::formatFractionPrices('curioso', '7') }}</p>
                        </div>
                    </div>
                    <input type="hidden" name="fraction_type_week_${weekNumber}" value="7">
                </div>
            </div>
        `;

        // Garantir que listeners estão conectados e campo oculto atualizado
        setTimeout(() => {
            attachFractionDebugListeners();
            updateFractionDebugPanel();
        }, 0);

        return section;
    }

    /* ---------------------------
       Stepper for fraction sections (after profile selection)
    ---------------------------- */
    function initializeFractionStepper(authorizedWeeks) {
        const container = document.getElementById('fraction_weeks_container');
        if (!container) return;
        // New behaviour: show all fraction sections stacked (one below another).
        // Remove any previous stepper controls and ensure all week sections are visible.
        const sections = container.querySelectorAll('[id^="fraction_section_week_"]');
        sections.forEach((s) => s.classList.remove('d-none'));
        const existing = document.getElementById('fraction_weeks_stepper');
        if (existing) existing.remove();
    }

    function showFractionWeek(index) {
        const container = document.getElementById('fraction_weeks_container');
        if (!container) return;
        const sections = container.querySelectorAll('[id^="fraction_section_week_"]');
        sections.forEach(s => s.classList.add('d-none'));
        const target = document.getElementById(`fraction_section_week_${index}`);
        if (target) {
            target.classList.remove('d-none');
            target.classList.add('fade-in');
            setTimeout(() => { target.classList.remove('fade-in'); }, 360);
        }
        const indicator = document.getElementById('fraction_week_indicator');
        if (indicator) indicator.textContent = `Semana ${index} de ${window._fractionStepper.weeks.length}`;
        window._fractionStepper.current = index;
        updateFractionStepperButtons();
        // Show only the calendar section for the current week (hide others)
        const calendarContainers = document.getElementById('calendar-containers');
        if (calendarContainers) {
            const weekSections = calendarContainers.querySelectorAll('[id^="week_"][id$="_calendar_section"]');
            weekSections.forEach(ws => ws.classList.add('d-none'));
            const currentCalendar = document.getElementById(`week_${index}_calendar_section`);
            if (currentCalendar) currentCalendar.classList.remove('d-none');
        }
        // Update progress bar
        const total = window._fractionStepper.weeks.length;
        const progress = document.getElementById('fraction_progress_bar');
        if (progress) {
            const percent = Math.round((index / total) * 100);
            progress.style.width = percent + '%';
            progress.setAttribute('aria-valuenow', percent);
        }
    }

    function fractionPrev() {
        const cur = window._fractionStepper.current;
        if (cur > 1) showFractionWeek(cur - 1);
    }

    function fractionNext() {
        const cur = window._fractionStepper.current;
        const total = window._fractionStepper.weeks.length;
        if (!validateFractionWeek(cur)) return;
        if (cur < total) {
            showFractionWeek(cur + 1);
        } else {
            // last: keep existing behavior (allow submit)
            const err = document.getElementById('fraction_week_error');
            if (err) err.style.display = 'none';
        }
    }

    function updateFractionStepperButtons() {
        const cur = window._fractionStepper.current;
        const total = window._fractionStepper.weeks.length;
        const prev = document.getElementById('fraction_prev_week');
        const next = document.getElementById('fraction_next_week');
        if (prev) prev.disabled = cur <= 1;
        if (next) next.textContent = (cur >= total) ? 'Concluir' : 'Próxima';
        // update progress bar visually
        const progress = document.getElementById('fraction_progress_bar');
        if (progress) {
            const percent = Math.round((cur / total) * 100);
            progress.style.width = percent + '%';
            progress.setAttribute('aria-valuenow', percent);
        }
    }

    function validateFractionWeek(weekNumber) {
        // require a fraction type chosen for this week
        const radio = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
        const err = document.getElementById('fraction_week_error');
        if (!radio) {
            if (err) { err.textContent = 'Por favor selecione um tipo de fracionamento para esta semana.'; err.style.display = 'block'; }
            return false;
        }
        if (err) { err.style.display = 'none'; }
        return true;
    }

    /* Upload proof via AJAX to /api/uploads */
    async function uploadProofAjax(type, weekNumber, inputEl) {
        if (!inputEl || !inputEl.files || inputEl.files.length === 0) return;
        const file = inputEl.files[0];
        const statusId = `${type}_weeks_${weekNumber}_upload_status`;
        let status = document.getElementById(statusId);
        if (!status) {
            status = document.createElement('div');
            status.id = statusId;
            status.className = 'small text-muted mt-2';
            inputEl.parentNode.appendChild(status);
        }
        status.textContent = 'Enviando...';

        try {
            const fd = new FormData();
            fd.append('file', file);
            const res = await fetch('/api/uploads', { method: 'POST', body: fd });
            const json = await res.json();
            if (!res.ok || !json.success) {
                status.textContent = '';
                return;
            }
            // create or update hidden input with uploaded path
            const fieldName = `${type}_weeks[${weekNumber}][proof_uploaded]`;
            let hidden = document.querySelector(`input[name="${fieldName}"]`);
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = fieldName;
                inputEl.parentNode.appendChild(hidden);
            }
            hidden.value = json.path || '';
            status.innerHTML = `Enviado: <a href="${json.url}" target="_blank">${file.name}</a>`;
        } catch (e) {
            console.error('Upload failed', e);
            status.textContent = '';
        }
    }

    // Atualiza o campo oculto com os dados de fracionamento para envio ao back-end
    function updateFractionDebugPanel() {
        const fractionTypeRadio = document.querySelector('input[name="fraction_type"]:checked');
        const fractionType = fractionTypeRadio ? fractionTypeRadio.value : null;

        const allInputs = document.querySelectorAll('input[name^="fraction_weeks["], select[name^="fraction_weeks["]');
        const fractionWeeks = {};

        allInputs.forEach(input => {
            const name = input.name;
            const match = name.match(/^fraction_weeks\[(\d+)\]\[periods\]\[(\d+)\]\[(.+)\]$/);
            if (!match) return;

            const weekNumber = match[1];
            const periodNumber = match[2];
            const field = match[3];

            if (input.type === 'checkbox') {
                const checkedVal = input.checked ? (input.value || '1') : '0';
                if (!fractionWeeks[weekNumber]) {
                    fractionWeeks[weekNumber] = { periods: {} };
                }
                if (!fractionWeeks[weekNumber].periods[periodNumber]) {
                    fractionWeeks[weekNumber].periods[periodNumber] = {};
                }
                fractionWeeks[weekNumber].periods[periodNumber][field] = checkedVal;
                return;
            }

            const value = input.value;
            if (value === null || value === '') {
                return;
            }

            if (!fractionWeeks[weekNumber]) {
                fractionWeeks[weekNumber] = { periods: {} };
            }
            if (!fractionWeeks[weekNumber].periods[periodNumber]) {
                fractionWeeks[weekNumber].periods[periodNumber] = {};
            }

            fractionWeeks[weekNumber].periods[periodNumber][field] = value;
        });

        const debugData = {
            fraction_type: fractionType,
            fraction_weeks: fractionWeeks
        };

        const jsonText = JSON.stringify(debugData, null, 2);

        // Gravar o JSON em um campo oculto para garantir envio ao back-end
        const hiddenInput = document.getElementById('fraction_details_json');
        if (hiddenInput) {
            hiddenInput.value = jsonText;
        }
    }

    function attachFractionDebugListeners() {
        // Atualizar sempre que algo do fracionamento mudar
        const fractionInputs = document.querySelectorAll(
            'input[name="fraction_type"], ' +
            'input[name^="fraction_weeks["], ' +
            'select[name^="fraction_weeks["]'
        );

        fractionInputs.forEach(el => {
            if (!el.dataset._fractionDebugBound) {
                el.addEventListener('change', updateFractionDebugPanel);
                el.addEventListener('input', updateFractionDebugPanel);
                el.dataset._fractionDebugBound = '1';
            }
        });
    }

    // Função para criar seção de fracionamento para uma semana específica
    function createFractionSectionForWeek(weekNumber, profileType) {
        const section = document.createElement('div');
        section.className = 'mb-5';
        section.id = `fraction_section_week_${weekNumber}`;

        let fractionOptions = '';
        let alertMessage = '';
        let rowClass = '';

        if (profileType === 'inteligente') {
            alertMessage = `
                <div class="alert alert-info" role="alert" style="display: block !important;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Perfil Inteligente:</strong> Escolha como deseja fracionar seus 7 dias de cota para a Semana ${weekNumber}
                    <hr>
                    <div class="small">
                        <i class="fas fa-gavel me-2"></i><strong>Leilão:</strong> 2 para aluguel, 2 para troca, 2 para compra, 2 para venda, por mês<br>
                        <i class="fas fa-bell me-2"></i><strong>Alerta:</strong> 1 para aluguel, 1 para troca, 1 para compra, 1 para venda, por mês<br>
                        <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> até o final do período da Cota no ano vigente<br>
                        <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> pode fracionar ou não a(s) semana(s) da cota, e ofertar o que desejar delas
                    </div>
                </div>
            `;
            rowClass = 'row g-3 container';
            if (onlyVenderAllowed()) {
                fractionOptions = `
                <div class="col-md-4">
                    <div class="card fraction-card h-100" data-value="7" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_7" value="7" class="form-check-input" checked>
                            <label for="fraction_week_${weekNumber}_7" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-week fa-3x text-info mb-4"></i>
                                    <h5 class="fw-bold mb-3">Sem fracionar</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '7') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            } else {
                fractionOptions = `
                <div class="col-md-4">
                    <div class="card fraction-card h-100" data-value="7" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_7" value="7" class="form-check-input" checked>
                            <label for="fraction_week_${weekNumber}_7" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-week fa-3x text-info mb-4"></i>
                                    <h5 class="fw-bold mb-3">Sem fracionar</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '7') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card fraction-card h-100" data-value="3_4" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_3_4" value="3_4" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_3_4" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-alt fa-3x text-primary mb-4"></i>
                                    <h5 class="fw-bold mb-3">3 + 4 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '3_4') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card fraction-card h-100" data-value="4_3" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_4_3" value="4_3" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_4_3" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-alt fa-3x text-success mb-4"></i>
                                    <h5 class="fw-bold mb-3">4 + 3 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('inteligente', '4_3') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            }
        } else if (profileType === 'sabio') {
            alertMessage = `
                <div class="alert alert-info" role="alert" style="display: block !important;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Perfil Sábio:</strong> Escolha a melhor combinação de fracionamento para seus 7 dias da Semana ${weekNumber}
                    <hr>
                    <div class="small">
                        <i class="fas fa-gavel me-2"></i><strong>Leilão:</strong> 3 para aluguel, 3 para troca, 3 para compra, 3 para venda, por mês<br>
                        <i class="fas fa-bell me-2"></i><strong>Alerta:</strong> 3 para aluguel, 3 para troca, 3 para compra, 3 para venda, por mês<br>
                        <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> até o final do período da Cota no ano vigente<br>
                        <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> pode fracionar ou não a(s) semana(s) da cota, e ofertar o que desejar delas
                    </div>
                </div>
            `;
            rowClass = 'row g-3';
            if (onlyVenderAllowed()) {
                fractionOptions = `
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="7" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_7" value="7" class="form-check-input" checked>
                            <label for="fraction_week_${weekNumber}_7" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-week fa-3x text-info mb-4"></i>
                                    <h5 class="fw-bold mb-3">Sem fracionar</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '7') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            } else {
                fractionOptions = `
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="7" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_7" value="7" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_7" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-week fa-3x text-info mb-4"></i>
                                    <h5 class="fw-bold mb-3">Sem fracionar</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '7') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="2_2_3" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_2_2_3" value="2_2_3" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_2_2_3" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-check fa-3x text-primary mb-4"></i>
                                    <h5 class="fw-bold mb-3">2 + 2 + 3 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '2_2_3') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="3_4" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_3_4" value="3_4" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_3_4" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-check fa-3x text-success mb-4"></i>
                                    <h5 class="fw-bold mb-3">3 + 4 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '3_4') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="2_5" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_2_5" value="2_5" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_2_5" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-check fa-3x text-warning mb-4"></i>
                                    <h5 class="fw-bold mb-3">2 + 5 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '2_5') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="3_2_2" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_3_2_2" value="3_2_2" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_3_2_2" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-plus fa-3x text-primary mb-4"></i>
                                    <h5 class="fw-bold mb-3">3 + 2 + 2 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '3_2_2') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="4_3" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_4_3" value="4_3" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_4_3" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-plus fa-3x text-success mb-4"></i>
                                    <h5 class="fw-bold mb-3">4 + 3 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '4_3') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card fraction-card h-100" data-value="5_2" data-week="${weekNumber}">
                        <div class="card-body">
                            <input type="radio" name="fraction_type_week_${weekNumber}" id="fraction_week_${weekNumber}_5_2" value="5_2" class="form-check-input">
                            <label for="fraction_week_${weekNumber}_5_2" class="fraction-label w-100">
                                <div class="text-center">
                                    <i class="fas fa-calendar-plus fa-3x text-warning mb-4"></i>
                                    <h5 class="fw-bold mb-3">5 + 2 dias</h5>
                                    <hr class="my-3" style="border-color: #e0e0e0;">
                                    <p class="text-muted mb-3 small">{{ \App\Models\SuccessFee::formatFractionPrices('sabio', '5_2') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            }
        }

        section.innerHTML = `
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-week me-2"></i>Semana ${weekNumber}
                    </h5>
                </div>
                <div class="card-body">
                    ${alertMessage}
                    <div class="${rowClass}">
                        ${fractionOptions}
                    </div>
                </div>
            </div>
            <div id="fraction_week_${weekNumber}_calendar_wrapper" class="mt-3"></div>
        `;

        return section;
    }

    // Função para interação com cards de fracionamento
    function initializeFractionCards() {
        const fractionCards = document.querySelectorAll('.fraction-card');

        fractionCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            const weekNumber = card.getAttribute('data-week');
            
            if (radio && radio.checked) {
                card.classList.add('selected');
                // Se houver weekNumber, criar campos apenas para essa semana
                if (weekNumber) {
                    createCalendarFieldsForWeek(weekNumber, radio.value);
                } else {
                    // Comportamento antigo para cards sem data-week (backward compatibility)
                    updateAuthorizedWeeks();
                    createCalendarFields(radio.value);
                }
            }

            card.addEventListener('click', function() {
                const cardWeek = this.getAttribute('data-week');
                
                // Remover seleção apenas dos cards da mesma semana
                if (cardWeek) {
                    document.querySelectorAll(`.fraction-card[data-week="${cardWeek}"]`).forEach(c => c.classList.remove('selected'));
                } else {
                    document.querySelectorAll('.fraction-card').forEach(c => c.classList.remove('selected'));
                }
                
                this.classList.add('selected');

                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    if (cardWeek) {
                        // Criar campos apenas para essa semana específica
                        createCalendarFieldsForWeek(cardWeek, radio.value);
                    } else {
                        // Comportamento antigo
                        updateAuthorizedWeeks();
                        createCalendarFields(radio.value);
                    }
                }
            });

            const radioButton = card.querySelector('input[type="radio"]');
            if (radioButton) {
                radioButton.addEventListener('change', function() {
                    if (this.checked) {
                        const cardWeek = card.getAttribute('data-week');
                        
                        if (cardWeek) {
                            document.querySelectorAll(`.fraction-card[data-week="${cardWeek}"]`).forEach(c => c.classList.remove('selected'));
                            card.classList.add('selected');
                            createCalendarFieldsForWeek(cardWeek, this.value);
                        } else {
                            document.querySelectorAll('.fraction-card').forEach(c => c.classList.remove('selected'));
                            card.classList.add('selected');
                            updateAuthorizedWeeks();
                            createCalendarFields(this.value);
                        }
                    }
                });
            }
        });
        // Initialize tooltips for fraction cards (use inner small text if available)
        try {
            fractionCards.forEach(card => {
                // prefer the small description inside card
                const small = card.querySelector('.text-muted.small') || card.querySelector('p.text-muted');
                if (small) {
                    card.setAttribute('title', small.textContent.trim());
                    card.setAttribute('data-bs-toggle', 'tooltip');
                }
            });
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (el) {
                try { return new bootstrap.Tooltip(el); } catch (e) { return null; }
            });
        } catch (e) {
            // ignore if bootstrap tooltip not available
            console.warn('Tooltip init failed', e);
        }
    }

    // Função para criar campos de calendário para uma semana específica
    function createCalendarFieldsForWeek(weekNumber, fractionValue) {
        // Prefer per-week wrapper (under the fraction card) if present
        const perWeekWrapper = document.getElementById(`fraction_week_${weekNumber}_calendar_wrapper`);
        const calendarFields = document.getElementById('calendar-fields');
        const calendarContainers = document.getElementById('calendar-containers');

        // target container where week sections will be appended
        const targetContainer = perWeekWrapper || calendarContainers;
        if (!targetContainer) return;

        // show global header only if using global container
        if (calendarFields) {
            if (perWeekWrapper) calendarFields.classList.add('d-none');
            else calendarFields.classList.remove('d-none');
        }

        // Verificar se a semana está autorizada
        const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
        if (!authorizedWeeks.includes(parseInt(weekNumber))) {
            return;
        }

        // Remover seção existente para esta semana, se houver
        const existingSection = document.getElementById(`week_${weekNumber}_calendar_section`);
        if (existingSection) {
            existingSection.remove();
        }

        calendarFields.classList.remove('d-none');

        const weekType = getCurrentWeekType();
        const isIntegral = !fractionValue || fractionValue === 'integral' || fractionValue === '7';
        const parts = isIntegral ? [7] : fractionValue.split('_').map(n => parseInt(n, 10));

        const weekSection = document.createElement('div');
        weekSection.className = 'week-calendar-section mb-5';
        weekSection.id = `week_${weekNumber}_calendar_section`;
        weekSection.innerHTML = `
            <div class="d-flex align-items-center mb-3">
                <h5 class="fw-bold text-primary mb-0">Semana ${weekNumber}</h5>
            </div>
            <div class="row g-4" id="week_${weekNumber}_calendar_rows"></div>
        `;

        // Prefer inserting the calendar section immediately after the fraction section for this week
        const fractionSection = document.getElementById(`fraction_section_week_${weekNumber}`);
        if (fractionSection && fractionSection.parentNode) {
            fractionSection.parentNode.insertBefore(weekSection, fractionSection.nextSibling);
        } else {
            targetContainer.appendChild(weekSection);
        }

        const rowsContainer = weekSection.querySelector(`#week_${weekNumber}_calendar_rows`);
        if (!rowsContainer) {
            return;
        }

        const weekDetails = getWeekDetails(weekType, weekNumber);
        let currentStartDate = weekDetails.startDate ? new Date(weekDetails.startDate.getTime()) : null;

        parts.forEach((days, index) => {
            const periodNumber = index + 1;
            const periodIdBase = `week_${weekNumber}_period_${periodNumber}`;
            const showSellOption = onlyVenderAllowed() || (getCurrentWeekType() !== 'gestor' && isIntegral);
            const gestorPeriodActionOptions = '<option value="">Selecione uma opção</option><option value="rent">Alugar</option><option value="exchange">Trocar</option><option value="rent_exchange">Alugar e Trocar</option>';
            const ownerPeriodActionOptions = '<option value="">Selecione uma opção</option><option value="rent">Alugar</option><option value="exchange">Trocar</option><option value="rent_exchange">Alugar e Trocar</option>' + (showSellOption ? '<option value="sell">Vender</option>' : '');

            const startValue = currentStartDate ? formatDateInput(currentStartDate) : '';
            let computedEndDate = currentStartDate ? new Date(currentStartDate.getTime()) : null;
            if (computedEndDate) {
                // Regra: dia inicial + número de dias = dia final
                // Cota inteira (7 dias): dia inicial + 7
                // Fracionada: dia inicial + days
                const increment = days;
                computedEndDate.setDate(computedEndDate.getDate() + increment);
            }
            const endValue = computedEndDate ? formatDateInput(computedEndDate) : '';

            const periodCard = document.createElement('div');
            periodCard.className = 'col-12';
            periodCard.innerHTML = `
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 d-flex align-items-center">
                            <i class="fas fa-calendar-check me-2 text-success"></i>Período ${days} dias
                        </h6>
                        <div class="row g-4 mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input type="hidden" name="fraction_weeks[${weekNumber}][periods][${periodNumber}][enabled]" value="0">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="fraction_weeks[${weekNumber}][periods][${periodNumber}][enabled]"
                                           id="${periodIdBase}_enabled"
                                           value="1">
                                    <label class="form-check-label fw-semibold" for="${periodIdBase}_enabled">
                                        <i class="fas fa-toggle-on me-2 text-primary"></i>Desejo alugar ou trocar este período
                                    </label>
                                </div>
                                <div id="${periodIdBase}_action_div" class="d-none">
                                    <label class="form-label fw-semibold d-flex align-items-center mb-2">
                                        <i class="fas fa-hand-holding-usd me-2 text-success"></i>Ação para este período *
                                    </label>
                                    <select class="form-select" 
                                            name="fraction_weeks[${weekNumber}][periods][${periodNumber}][action]"
                                            id="${periodIdBase}_action_select">
                                        ${getCurrentWeekType() === 'gestor' ? gestorPeriodActionOptions : ownerPeriodActionOptions}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-flex align-items-center">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Data de Início *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="fraction_weeks[${weekNumber}][periods][${periodNumber}][start]"
                                       id="${periodIdBase}_start"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-flex align-items-center">
                                    <i class="fas fa-calendar-check me-2 text-success"></i>Data de Término *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="fraction_weeks[${weekNumber}][periods][${periodNumber}][end]"
                                       id="${periodIdBase}_end"
                                       required>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i>${isIntegral && periodNumber === 1 ? 'Será calculado automaticamente (7 pernoites + 1 Manhã para check-out)' : ``}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            rowsContainer.appendChild(periodCard);

            const onlyVender = onlyVenderAllowed();
            setTimeout(() => {
                const startInput = document.getElementById(`${periodIdBase}_start`);
                const endInput = document.getElementById(`${periodIdBase}_end`);
                const enabledCheckbox = document.getElementById(`${periodIdBase}_enabled`);
                const actionDiv = document.getElementById(`${periodIdBase}_action_div`);
                const actionSelect = document.getElementById(`${periodIdBase}_action_select`);

                const makeReadOnly = (input, value) => {
                    if (!input) return;
                    input.value = value;
                    input.setAttribute('readonly', 'readonly');
                    input.classList.add('bg-light');
                    input.setAttribute('tabindex', '-1');
                    input.style.pointerEvents = 'none';
                    input.addEventListener('keydown', event => event.preventDefault());
                    input.addEventListener('mousedown', event => event.preventDefault());
                    input.addEventListener('focus', event => event.target.blur());
                    if (!value) {
                        input.placeholder = 'Defina o período na seção de semanas';
                    }
                };

                makeReadOnly(startInput, startValue);
                makeReadOnly(endInput, endValue);

                if (enabledCheckbox && actionDiv && actionSelect) {
                    if (onlyVender) {
                        enabledCheckbox.checked = true;
                        actionDiv.classList.remove('d-none');
                        actionSelect.value = 'sell';
                        actionSelect.required = true;
                        actionSelect.disabled = true;
                        actionSelect.classList.add('bg-light');
                        const hiddenAction = document.createElement('input');
                        hiddenAction.type = 'hidden';
                        hiddenAction.name = actionSelect.name;
                        hiddenAction.value = 'sell';
                        actionSelect.parentNode.appendChild(hiddenAction);
                    }
                    enabledCheckbox.addEventListener('change', function() {
                        if (onlyVender) return;
                        const checked = this.checked;
                        actionDiv.classList.toggle('d-none', !checked);
                        actionSelect.required = checked;
                        if (!checked) {
                            actionSelect.value = '';
                            actionSelect.classList.remove('is-invalid');
                        }
                    });
                }
            }, 0);

            if (computedEndDate) {
                currentStartDate = new Date(computedEndDate.getTime());
            }
        });
    }

    // Função para criar campos de calendário baseado na seleção
    function createCalendarFields(fractionValue) {
        const calendarFields = document.getElementById('calendar-fields');
        const calendarContainers = document.getElementById('calendar-containers');

        if (!calendarFields || !calendarContainers) return;

        const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
        calendarContainers.innerHTML = '';

        if (!authorizedWeeks.length) {
            calendarFields.classList.add('d-none');
            return;
        }

        calendarFields.classList.remove('d-none');

        const weekType = getCurrentWeekType();
        const isIntegral = !fractionValue || fractionValue === 'integral' || fractionValue === '7';
        const parts = isIntegral ? [7] : fractionValue.split('_').map(n => parseInt(n, 10));

        authorizedWeeks.forEach(weekNumber => {
            const weekSection = document.createElement('div');
            weekSection.className = 'week-calendar-section mb-5';
            weekSection.innerHTML = `
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold text-primary mb-0">Semana ${weekNumber}</h5>
                        </div>
                        <div class="row g-4" id="week_${weekNumber}_calendar_rows"></div>
                    `;

            calendarContainers.appendChild(weekSection);

            const rowsContainer = weekSection.querySelector(`#week_${weekNumber}_calendar_rows`);
            if (!rowsContainer) {
                return;
            }

            const weekDetails = getWeekDetails(weekType, weekNumber);
            let currentStartDate = weekDetails.startDate ? new Date(weekDetails.startDate.getTime()) : null;

            parts.forEach((days, index) => {
                const periodNumber = index + 1;
                const periodIdBase = `week_${weekNumber}_period_${periodNumber}`;
                const showSellOptionCal = onlyVenderAllowed() || (getCurrentWeekType() !== 'gestor' && isIntegral);
                const gestorPeriodActionOptionsCal = '<option value="">Selecione uma opção</option><option value="rent">Alugar</option><option value="exchange">Trocar</option><option value="rent_exchange">Alugar e Trocar</option>';
                const ownerPeriodActionOptionsCal = '<option value="">Selecione uma opção</option><option value="rent">Alugar</option><option value="exchange">Trocar</option><option value="rent_exchange">Alugar e Trocar</option>' + (showSellOptionCal ? '<option value="sell">Vender</option>' : '');

                const startValue = currentStartDate ? formatDateInput(currentStartDate) : '';
                let computedEndDate = currentStartDate ? new Date(currentStartDate.getTime()) : null;
                if (computedEndDate) {
                    // Regra: dia inicial + número de dias = dia final
                    // Cota inteira (7 dias): dia inicial + 7
                    // Fracionada: dia inicial + days
                    const increment = days;
                    computedEndDate.setDate(computedEndDate.getDate() + increment);
                }
                const endValue = computedEndDate ? formatDateInput(computedEndDate) : '';

                const periodCard = document.createElement('div');
                periodCard.className = 'col-12';
                periodCard.innerHTML = `
                            <div class="card shadow-sm">
                            <div class="card-body p-4">
                                    <h6 class="fw-bold mb-4 d-flex align-items-center">
                                    <i class="fas fa-calendar-check me-2 text-success"></i>Período ${days} dias
                                </h6>
                                <div class="row g-4 mb-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch mb-3">
                                            <input type="hidden" name="fraction_weeks[${weekNumber}][periods][${periodNumber}][enabled]" value="0">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                       name="fraction_weeks[${weekNumber}][periods][${periodNumber}][enabled]"
                                                       id="${periodIdBase}_enabled"
                                                   value="1">
                                                <label class="form-check-label fw-semibold" for="${periodIdBase}_enabled">
                                                <i class="fas fa-toggle-on me-2 text-primary"></i>Desejo alugar ou trocar este período
                                            </label>
                                        </div>
                                            <div id="${periodIdBase}_action_div" class="d-none">
                                            <label class="form-label fw-semibold d-flex align-items-center mb-2">
                                                <i class="fas fa-hand-holding-usd me-2 text-success"></i>Ação para este período *
                                            </label>
                                            <select class="form-select" 
                                                        name="fraction_weeks[${weekNumber}][periods][${periodNumber}][action]"
                                                        id="${periodIdBase}_action_select">
                                                ${getCurrentWeekType() === 'gestor' ? gestorPeriodActionOptionsCal : ownerPeriodActionOptionsCal}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold d-flex align-items-center">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>Data de Início *
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                                   name="fraction_weeks[${weekNumber}][periods][${periodNumber}][start]"
                                                   id="${periodIdBase}_start"
                                               required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold d-flex align-items-center">
                                            <i class="fas fa-calendar-check me-2 text-success"></i>Data de Término *
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                                   name="fraction_weeks[${weekNumber}][periods][${periodNumber}][end]"
                                                   id="${periodIdBase}_end"
                                                   required>
                                        <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>${isIntegral && periodNumber === 1 ? 'Será calculado automaticamente (7 pernoites + 1 Manhã para check-out)' : ``}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                rowsContainer.appendChild(periodCard);

                const onlyVenderCal = onlyVenderAllowed();
                setTimeout(() => {
                    const startInput = document.getElementById(`${periodIdBase}_start`);
                    const endInput = document.getElementById(`${periodIdBase}_end`);
                    const enabledCheckbox = document.getElementById(`${periodIdBase}_enabled`);
                    const actionDiv = document.getElementById(`${periodIdBase}_action_div`);
                    const actionSelect = document.getElementById(`${periodIdBase}_action_select`);

                    const makeReadOnly = (input, value) => {
                        if (!input) return;
                        input.value = value;
                        input.setAttribute('readonly', 'readonly');
                        input.classList.add('bg-light');
                        input.setAttribute('tabindex', '-1');
                        input.style.pointerEvents = 'none';
                        input.addEventListener('keydown', event => event.preventDefault());
                        input.addEventListener('mousedown', event => event.preventDefault());
                        input.addEventListener('focus', event => event.target.blur());
                        if (!value) {
                            input.placeholder = 'Defina o período na seção de semanas';
                        }
                    };

                    makeReadOnly(startInput, startValue);
                    makeReadOnly(endInput, endValue);

                    if (enabledCheckbox && actionDiv && actionSelect) {
                        if (onlyVenderCal) {
                            enabledCheckbox.checked = true;
                            actionDiv.classList.remove('d-none');
                            actionSelect.value = 'sell';
                            actionSelect.required = true;
                            actionSelect.disabled = true;
                            actionSelect.classList.add('bg-light');
                            const hiddenAction = document.createElement('input');
                            hiddenAction.type = 'hidden';
                            hiddenAction.name = actionSelect.name;
                            hiddenAction.value = 'sell';
                            actionSelect.parentNode.appendChild(hiddenAction);
                        }
                        enabledCheckbox.addEventListener('change', function() {
                            if (onlyVenderCal) return;
                            const checked = this.checked;
                            actionDiv.classList.toggle('d-none', !checked);
                            actionSelect.required = checked;
                            if (!checked) {
                                actionSelect.value = '';
                                actionSelect.classList.remove('is-invalid');
                            }
                        });
                    }
                }, 0);

                if (computedEndDate) {
                    currentStartDate = new Date(computedEndDate.getTime());
                }
            });
        });

        calendarFields.classList.remove('d-none');
    }

    // Inicializar cards quando o Step 6 for exibido
    window.initialOwnerWeeks = <?php echo json_encode(old('owner_weeks', [])); ?>;
    window.initialGestorWeeks = <?php echo json_encode(old('gestor_weeks', [])); ?>;

    // Função para inicializar e remover required de campos gestor se a seção estiver oculta
    function initializeGestorRequiredFields() {
        const gestorSection = document.getElementById('gestor_section');
        const hasQuotaValue = document.querySelector('input[name="has_quota"]:checked');
        const isGestorSelected = hasQuotaValue && (hasQuotaValue.value === '2' || hasQuotaValue.value === '3');
        const isGestorVisible = gestorSection && !gestorSection.classList.contains('d-none');
        
        // Se gestor não está selecionado OU seção está oculta, remover required
        if (!isGestorSelected || !isGestorVisible) {
            const allGestorCheckboxes = document.querySelectorAll('input[name="gestor_allowed_uses[]"]');
            allGestorCheckboxes.forEach(checkbox => {
                checkbox.removeAttribute('required');
                checkbox.required = false;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeFractionCards();
        initializeRoomsConfiguration();
        initializeQuotaWeeks();
        updateProgressBar(); // Inicializar barra de progresso
        initializeGestorRequiredFields(); // Inicializar campos gestor
        // Toggle quota payment deadline visibility based on quota status
        (function(){
            function toggleDeadline(selectId, sectionId, inputId) {
                const sel = document.getElementById(selectId);
                const sec = document.getElementById(sectionId);
                const inp = document.getElementById(inputId);
                if (!sel || !sec || !inp) return;

                // prefer hiding only the input column/wrapper to avoid hiding the select itself
                const inpWrapper = inp.closest('.col-md-6') || inp.parentNode;

                function apply() {
                    if (sel.value === 'paid') {
                        if (inpWrapper) inpWrapper.classList.add('d-none'); else sec.classList.add('d-none');
                        inp.removeAttribute('required');
                        inp.required = false;
                        // clear value and validation classes
                        try { inp.value = ''; } catch(e){}
                        inp.classList.remove('is-invalid');
                        const fb = inpWrapper ? inpWrapper.querySelector('.invalid-feedback') : (sec ? sec.querySelector('.invalid-feedback') : null);
                        if (fb) fb.style.display = 'none';
                    } else {
                        if (inpWrapper) inpWrapper.classList.remove('d-none'); else sec.classList.remove('d-none');
                        inp.setAttribute('required','required');
                        inp.required = true;
                    }
                }

                sel.addEventListener('change', apply);
                // initial
                apply();
            }

            toggleDeadline('quota_status', 'quota_payment_deadline_section', 'quota_payment_deadline');
            toggleDeadline('gestor_quota_status', 'gestor_quota_payment_deadline_section', 'gestor_quota_payment_deadline');
        })();

        // Quando o usuário marcar/desmarcar algum "Usos permitidos", revalidar se pode prosseguir (vs. Autoriza... desta semana)
        document.addEventListener('change', function(e) {
            if (e.target && (e.target.name === 'allowed_uses[]' || e.target.name === 'gestor_allowed_uses[]')) {
                checkAllowedUsesVsAuthorize();
                // Atualizar visibilidade das opções de fracionamento (só "Sem fracionar" quando só Vender)
                toggleFractionCardsWhenOnlyVender('fraction_inteligente');
                toggleFractionCardsWhenOnlyVender('fraction_sabio');
            }
        });
    });

    // Função para inicializar configuração de quartos
    function initializeRoomsConfiguration() {
        // Event listener para owner_quota_rooms
        const ownerRoomsSelect = document.getElementById('owner_quota_rooms');
        if (ownerRoomsSelect) {
            ownerRoomsSelect.addEventListener('change', function() {
                updateRoomsConfiguration('owner', this.value);
            });
        }

        // Event listener para gestor_quota_rooms
        const gestorRoomsSelect = document.getElementById('gestor_quota_rooms');
        if (gestorRoomsSelect) {
            gestorRoomsSelect.addEventListener('change', function() {
                updateRoomsConfiguration('gestor', this.value);
            });
        }
    }

    // Função para atualizar configuração de quartos
    function updateRoomsConfiguration(type, roomCount) {
        const configDiv = document.getElementById(type === 'owner' ? 'rooms-configuration' : 'gestor-rooms-configuration');
        const container = document.getElementById(type === 'owner' ? 'rooms-container' : 'gestor-rooms-container');

        if (!configDiv || !container) return;

        // Limpar container
        container.innerHTML = '';

        if (roomCount && parseInt(roomCount) > 0) {
            // Mostrar seção de configuração
            configDiv.classList.remove('d-none');

            // Criar blocos para cada quarto
            for (let i = 1; i <= parseInt(roomCount); i++) {
                const roomBlock = createRoomBlock(type, i);
                container.appendChild(roomBlock);
            }
        } else {
            // Ocultar seção de configuração
            configDiv.classList.add('d-none');
        }
    }

    function initializeQuotaWeeks() {
        const weekData = {
            owner: window.initialOwnerWeeks || {},
            gestor: window.initialGestorWeeks || {},
        };

        document.querySelectorAll('input[name="has_quota"]').forEach(input => {
            input.addEventListener('change', refreshCalendarIfNeeded);
        });

        ['owner', 'gestor'].forEach(type => {
            const countSelect = document.getElementById(`${type}_quota_weeks_count`);
            const container = document.getElementById(`${type}_weeks_container`);

            if (!countSelect || !container) {
                return;
            }

            const initialData = weekData[type];
            const initialCount = countSelect.value || Object.keys(initialData || {}).length;

            if (!countSelect.value && initialCount) {
                countSelect.value = initialCount;
            }

            renderWeekBlocks(type, initialData);

            countSelect.addEventListener('change', () => {
                renderWeekBlocks(type, {});
                updateAuthorizedWeeks();
            });
        });
    }

    function renderWeekBlocks(type, initialData = {}) {
        const countSelect = document.getElementById(`${type}_quota_weeks_count`);
        const container = document.getElementById(`${type}_weeks_container`);

        if (!countSelect || !container) {
            return;
        }

        const count = parseInt(countSelect.value, 10);
        const sourceData = Object.keys(initialData).length ? initialData : (type === 'owner' ? (window.initialOwnerWeeks || {}) : (window.initialGestorWeeks || {}));

        container.innerHTML = '';

        if (Number.isNaN(count) || count <= 0) {
            updateAuthorizedWeeks();
            return;
        }

        for (let weekNumber = 1; weekNumber <= count; weekNumber++) {
            const weekData = sourceData && sourceData[weekNumber] ? sourceData[weekNumber] : {};
            const block = createWeekBlockElement(type, weekNumber, weekData);
            // mark block with data attribute to control visibility in stepper
            block.setAttribute('data-week-number', weekNumber);
            container.appendChild(block);
            setupWeekBlockInteractions(type, weekNumber, weekData);
        }

        // Render all week blocks stacked (one below another) — do not use a per-week stepper here
        // (keeps each week visible so user can authorize individually)

        updateAuthorizedWeeks();
        updateOwnerNextButton();
    }

    function validateWeeksPeriodAgainstCurrent(type) {
        const hasQuotaField = document.querySelector('input[name="has_quota"]:checked');
        const hasQuotaValue = hasQuotaField ? hasQuotaField.value : null;
        if ((type === 'owner' && hasQuotaValue !== '1') || (type === 'gestor' && hasQuotaValue !== '2' && hasQuotaValue !== '3')) {
            return true;
        }

        const container = document.getElementById(`${type}_weeks_container`);
        const nextButton = document.getElementById(type === 'owner' ? 'owner_next_button' : 'gestor_next_button');
        if (!container || !nextButton) return true;

        container.querySelectorAll('.week-period-date-error').forEach(box => {
            box.classList.add('d-none');
            const s = box.querySelector('span');
            if (s) s.textContent = '';
        });

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const currentMonth = today.getMonth() + 1;
        const currentYear = today.getFullYear();

        const periodRuleMessage = 'O período da Cota a ser cadastrada precisa ser posterior à data vigente.';
        let anyInvalid = false;
        const authorizeSelects = container.querySelectorAll('.week-authorize-select');
        authorizeSelects.forEach(select => {
            if (select.value !== 'yes') return;

            const weekNumber = select.dataset.weekNumber;
            const monthSelect = document.getElementById(`${type}_weeks_${weekNumber}_month`);
            const yearSelect = document.getElementById(`${type}_weeks_${weekNumber}_year`);
            const startDaySelect = document.getElementById(`${type}_weeks_${weekNumber}_start_day`);
            const alertBox = document.getElementById(`${type}_weeks_${weekNumber}_period_error`);

            const month = monthSelect ? parseInt(monthSelect.value, 10) : NaN;
            const year = yearSelect ? parseInt(yearSelect.value, 10) : NaN;
            const day = startDaySelect ? parseInt(startDaySelect.value, 10) : NaN;

            if (Number.isNaN(month) || Number.isNaN(year) || Number.isNaN(day)) {
                return;
            }

            let weekInvalid = false;
            if (year < currentYear || (year === currentYear && month < currentMonth)) {
                weekInvalid = true;
            } else {
                const startDate = new Date(year, month - 1, day);
                startDate.setHours(0, 0, 0, 0);
                if (Number.isNaN(startDate.getTime()) || startDate <= today) {
                    weekInvalid = true;
                }
            }

            if (weekInvalid && alertBox) {
                anyInvalid = true;
                const span = alertBox.querySelector('span');
                if (span) span.textContent = periodRuleMessage;
                alertBox.classList.remove('d-none');
            }
        });

        if (anyInvalid) {
            nextButton.disabled = true;
            return false;
        }

        return true;
    }

    function createWeekBlockElement(type, weekNumber, weekData = {}) {
        const block = document.createElement('div');
        block.className = 'card border mb-4 quota-week-block';

        const authorizeValue = weekData.authorize || '';
        const startDayValue = weekData.start_day || '';
        const endDayValue = weekData.end_day || '';
        const monthValue = weekData.month || '';
        const yearValue = weekData.year || '';

        const buildDayOptions = (selected) => {
            let options = '<option value=\"\">Selecione</option>';
            for (let day = 1; day <= 31; day++) {
                const value = day.toString().padStart(2, '0');
                const isSelected = selected === value ? 'selected' : '';
                options += `<option value=\"${value}\" ${isSelected}>${value}</option>`;
            }
            return options;
        };

        const buildMonthOptions = (selected) => {
            let options = '<option value=\"\">Selecione</option>';
            for (let month = 1; month <= 12; month++) {
                const value = month.toString().padStart(2, '0');
                const isSelected = selected === value ? 'selected' : '';
                options += `<option value=\"${value}\" ${isSelected}>${value}</option>`;
            }
            return options;
        };

        const buildYearOptions = (selected) => {
            const currentYear = new Date().getFullYear();
            const nextYear = currentYear + 1;
            const years = [currentYear.toString(), nextYear.toString()];
            let options = '<option value=\"\">Selecione</option>';
            years.forEach(year => {
                const isSelected = selected === year ? 'selected' : '';
                options += `<option value=\"${year}\" ${isSelected}>${year}</option>`;
            });
            return options;
        };

        block.innerHTML = `
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center mb-3">
                            <h2 class="h5 fw-bold text-primary mb-3 mb-md-0">Semana ${weekNumber}</h2>
                            <div class="ms-md-auto w-100 w-md-50">
                                <label class="form-label fw-semibold" for="${type}_weeks_${weekNumber}_authorize">
                                    <i class="fas fa-random me-1 text-success"></i>Autoriza o aluguel e/ou troca e ou compra/venda desta semana? *
                                </label>
                                <select class="form-select week-authorize-select"
                                    data-week-number="${weekNumber}"
                                    data-week-authorize="true"
                                    id="${type}_weeks_${weekNumber}_authorize"
                                    name="${type}_weeks[${weekNumber}][authorize]"
                                    required>
                                    <option value="">Selecione</option>
                                    <option value="yes" ${authorizeValue === 'yes' ? 'selected' : ''}>Sim</option>
                                    <option value="no" ${authorizeValue === 'no' ? 'selected' : ''}>Não</option>
                                </select>
                                <div class="authorize-error-message text-danger mt-2" 
                                     id="${type}_weeks_${weekNumber}_authorize_error" 
                                     style="display: none;">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    Você precisa autorizar as possibilidades de uso desta cota para usar o aplicativo na opção "Tenho Cota Hoteleira e gostaria de usá-la" e, também, na opção "Não, mas tenho autorização para ser gestor"
                                </div>
                                <div class="allowed-uses-required-message text-danger mt-2" 
                                     id="${type}_weeks_${weekNumber}_allowed_uses_required" 
                                     style="display: none;">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    Você precisa marcar algum uso permitido para conseguir prosseguir
                                </div>
                            </div>
                        </div>

                        <div class="week-fields" data-week-fields="${weekNumber}">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" for="${type}_weeks_${weekNumber}_start_day">
                                        <i class="fas fa-calendar-day me-1 text-success"></i>Dia de Início *
                                    </label>
                                    <select class="form-select week-start-day"
                                        id="${type}_weeks_${weekNumber}_start_day"
                                        name="${type}_weeks[${weekNumber}][start_day]"
                                        data-week-number="${weekNumber}">
                                        ${buildDayOptions(startDayValue)}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" for="${type}_weeks_${weekNumber}_end_day_display">
                                        <i class="fas fa-calendar-check me-1 text-success"></i>Dia de Término
                                    </label>
                                    <select class="form-select"
                                        id="${type}_weeks_${weekNumber}_end_day_display"
                                        disabled>
                                        ${buildDayOptions(endDayValue)}
                                    </select>
                                    <input type="hidden"
                                        id="${type}_weeks_${weekNumber}_end_day"
                                        name="${type}_weeks[${weekNumber}][end_day]"
                                        value="${endDayValue}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" for="${type}_weeks_${weekNumber}_month">
                                        <i class="fas fa-calendar-alt me-1 text-success"></i>Mês *
                                    </label>
                                    <select class="form-select"
                                        id="${type}_weeks_${weekNumber}_month"
                                        name="${type}_weeks[${weekNumber}][month]">
                                        ${buildMonthOptions(monthValue)}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" for="${type}_weeks_${weekNumber}_year">
                                        <i class="fas fa-calendar me-1 text-success"></i>Ano *
                                    </label>
                                    <select class="form-select"
                                        id="${type}_weeks_${weekNumber}_year"
                                        name="${type}_weeks[${weekNumber}][year]">
                                        ${buildYearOptions(yearValue)}
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-warning d-none week-period-date-error mb-3" id="${type}_weeks_${weekNumber}_period_error" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><span></span>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="${type}_weeks_${weekNumber}_proof">
                                        <i class="fas fa-file-upload me-1 text-success"></i>Comprovação de Período de Uso *
                                    </label>
                                    <input type="file"
                                        class="form-control"
                                        id="${type}_weeks_${weekNumber}_proof"
                                        name="${type}_weeks[${weekNumber}][proof]"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted d-block mt-2">
                                        Uma foto do documento  oficial de distribuição das semanas para uso no seu hotel
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

        return block;
    }

    function setupWeekBlockInteractions(type, weekNumber, weekData = {}) {
        const authorizeSelect = document.getElementById(`${type}_weeks_${weekNumber}_authorize`);
        const weekBlock = authorizeSelect ? authorizeSelect.closest('.quota-week-block') : null;
        const weekFields = weekBlock ? weekBlock.querySelector(`[data-week-fields="${weekNumber}"]`) : null;
        const startDaySelect = document.getElementById(`${type}_weeks_${weekNumber}_start_day`);
        const endDayDisplay = document.getElementById(`${type}_weeks_${weekNumber}_end_day_display`);
        const endDayHidden = document.getElementById(`${type}_weeks_${weekNumber}_end_day`);
        const monthSelect = document.getElementById(`${type}_weeks_${weekNumber}_month`);
        const yearSelect = document.getElementById(`${type}_weeks_${weekNumber}_year`);
        const proofInput = document.getElementById(`${type}_weeks_${weekNumber}_proof`);

        const toggleWeekFields = () => {
            const isAuthorized = authorizeSelect && authorizeSelect.value === 'yes';
            const isNotAuthorized = authorizeSelect && authorizeSelect.value === 'no';
            const errorMessage = document.getElementById(`${type}_weeks_${weekNumber}_authorize_error`);

            // Mostrar/ocultar mensagem de erro
            if (errorMessage) {
                errorMessage.style.display = isNotAuthorized ? 'block' : 'none';
            }

            if (weekFields) {
                weekFields.classList.toggle('d-none', !isAuthorized);
            }

            [startDaySelect, monthSelect, yearSelect, proofInput].forEach(input => {
                if (!input) return;
                if (isAuthorized) {
                    input.disabled = false;
                    input.setAttribute('required', 'required');
                } else {
                    input.disabled = true;
                    input.removeAttribute('required');
                }
            });

            // Atualizar estado do botão Próximo
            updateOwnerNextButton();
        };

        const updateEndDay = () => {
            if (!startDaySelect) {
                if (endDayDisplay) endDayDisplay.value = '';
                if (endDayHidden) endDayHidden.value = '';
                refreshCalendarIfNeeded();
                updateOwnerNextButton();
                return;
            }

            const startValue = startDaySelect.value;
            const monthValue = monthSelect ? monthSelect.value : '';
            const yearValue = yearSelect ? yearSelect.value : '';

            if (!startValue) {
                if (endDayDisplay) endDayDisplay.value = '';
                if (endDayHidden) endDayHidden.value = '';
                refreshCalendarIfNeeded();
                updateOwnerNextButton();
                return;
            }

            // Sempre calcular baseado na regra: dia inicial + 7 = dia final
            let endValue = '';
            
            if (startValue) {
                if (monthValue && yearValue) {
                    // Quando mês e ano estão definidos, usar cálculo de data completa
                    const startDate = new Date(`${yearValue}-${monthValue}-${startValue}T00:00:00`);
                    if (!Number.isNaN(startDate.getTime())) {
                        const endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + 7); // Dia inicial + 7 = dia final
                        endValue = String(endDate.getDate()).padStart(2, '0');
                    }
                } else {
                    // Quando mês/ano não estão definidos, usar cálculo simples (dia + 7)
                    const start = parseInt(startValue, 10);
                    if (!Number.isNaN(start)) {
                        let end = start + 7; // Dia inicial + 7 = dia final
                        if (end > 31) {
                            end = ((end - 1) % 31) + 1;
                        }
                        endValue = end.toString().padStart(2, '0');
                    }
                }
            }

            if (endDayDisplay) {
                endDayDisplay.value = endValue;
            }
            if (endDayHidden) {
                endDayHidden.value = endValue;
            }

            refreshCalendarIfNeeded();
            updateOwnerNextButton();
        };

        if (authorizeSelect) {
            authorizeSelect.addEventListener('change', () => {
                clearInvalid(authorizeSelect);
                toggleWeekFields();
                updateEndDay();
                updateAuthorizedWeeks();
                updateOwnerNextButton();
            });
        }

        if (startDaySelect) {
            startDaySelect.addEventListener('change', (e) => {
                clearInvalid(startDaySelect);
                updateEndDay();
            });
        }

        if (monthSelect) {
            monthSelect.addEventListener('change', (e) => {
                clearInvalid(monthSelect);
                updateEndDay();
            });
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', (e) => {
                clearInvalid(yearSelect);
                updateEndDay();
            });
        }

        if (proofInput) {
            proofInput.addEventListener('change', () => {
                clearInvalid(proofInput);
                if (proofInput.files && proofInput.files.length > 0) {
                    uploadProofAjax(type, weekNumber, proofInput);
                } else {
                    // remove any previously set hidden uploaded path
                    const hiddenName = `${type}_weeks[${weekNumber}][proof_uploaded]`;
                    const existingHidden = document.querySelector(`input[name="${hiddenName}"]`);
                    if (existingHidden) existingHidden.remove();
                }
            });
        }

        toggleWeekFields();
        updateEndDay();
        updateOwnerNextButton();
    }

    /* ---------------------------
       Stepper / per-week navigation
       - show one week block at a time when count > 1
    ---------------------------- */
    function initializeWeekStepper(type, count) {
        const container = document.getElementById(`${type}_weeks_container`);
        if (!container) return;
        // New behaviour: show all blocks (stacked). Remove any previous stepper controls.
        const blocks = container.querySelectorAll('.quota-week-block');
        blocks.forEach((b) => b.classList.remove('d-none'));

        const existing = document.getElementById(`${type}_weeks_stepper`);
        if (existing) existing.remove();
    }

    function showWeek(type, index, count) {
        const container = document.getElementById(`${type}_weeks_container`);
        if (!container) return;
        const blocks = container.querySelectorAll('.quota-week-block');
        blocks.forEach((b) => b.classList.add('d-none'));
        const target = container.querySelector(`.quota-week-block[data-week-number="${index}"]`);
        if (target) target.classList.remove('d-none');
        const indicator = document.getElementById(`${type}_week_indicator`);
        if (indicator) indicator.textContent = `Semana ${index} de ${count}`;
        window._weekStepper[type] = index;
        updateWeekStepperButtons(type, count);
    }

    function prevWeek(type, count) {
        const current = window._weekStepper && window._weekStepper[type] ? window._weekStepper[type] : 1;
        if (current > 1) {
            showWeek(type, current - 1, count);
        }
    }

    function nextWeek(type, count) {
        const current = window._weekStepper && window._weekStepper[type] ? window._weekStepper[type] : 1;
        const validate = validateWeek(type, current);
        if (!validate) return;
        if (current < count) {
            showWeek(type, current + 1, count);
        } else {
            // last week: optionally move forward in the flow; keep behavior to enable final submit
            // updateAuthorizedWeeks and next button state
            updateAuthorizedWeeks();
            updateOwnerNextButton();
        }
    }

    function updateWeekStepperButtons(type, count) {
        const current = window._weekStepper && window._weekStepper[type] ? window._weekStepper[type] : 1;
        const prevBtn = document.getElementById(`${type}_prev_week`);
        const nextBtn = document.getElementById(`${type}_next_week`);
        if (prevBtn) prevBtn.disabled = current <= 1;
        if (nextBtn) nextBtn.textContent = (current >= count) ? 'Concluir' : 'Próxima';
    }

    function validateWeek(type, weekNumber) {
        const authorizeSelect = document.getElementById(`${type}_weeks_${weekNumber}_authorize`);
        if (!authorizeSelect) return true;
        const authorizeValue = authorizeSelect.value;
        const errorEl = document.getElementById(`${type}_weeks_${weekNumber}_authorize_error`);
        if (!authorizeValue) {
            if (errorEl) errorEl.style.display = 'block';
            markInvalid(authorizeSelect, 'Por favor selecione Sim ou Não.');
            authorizeSelect.focus();
            return false;
        } else {
            if (errorEl) errorEl.style.display = 'none';
        }
        if (authorizeValue === 'yes') {
            const start = document.getElementById(`${type}_weeks_${weekNumber}_start_day`);
            const month = document.getElementById(`${type}_weeks_${weekNumber}_month`);
            const year = document.getElementById(`${type}_weeks_${weekNumber}_year`);
            const proof = document.getElementById(`${type}_weeks_${weekNumber}_proof`);
            if (!start || !start.value) { markInvalid(start, 'Selecione o dia de início.'); start && start.focus(); return false; }
            if (!month || !month.value) { markInvalid(month, 'Selecione o mês.'); month && month.focus(); return false; }
            if (!year || !year.value) { markInvalid(year, 'Selecione o ano.'); year && year.focus(); return false; }
            if (!proof || !proof.value) { markInvalid(proof, 'Anexe o comprovante de período de uso.'); proof && proof.focus(); return false; }
        }
        return true;
    }

    function markInvalid(el, message) {
        if (!el) return;
        el.classList.add('is-invalid');
        // find or create invalid-feedback
        let fb = el.parentNode.querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            el.parentNode.appendChild(fb);
        }
        fb.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + message;
        fb.style.display = 'block';
    }

    function clearInvalid(el) {
        if (!el) return;
        el.classList.remove('is-invalid');
        const fb = el.parentNode.querySelector('.invalid-feedback');
        if (fb) {
            fb.style.display = 'none';
        }
    }

    function calculateEndDay(startDay) {
        if (!startDay) {
            return '';
        }

        const start = parseInt(startDay, 10);
        if (Number.isNaN(start)) {
            return '';
        }

        // Regra: dia inicial + 7 = dia final (dia 1 → dia 8)
        let end = start + 7;
        if (end > 31) {
            end = ((end - 1) % 31) + 1;
        }

        return end.toString().padStart(2, '0');
    }

    function updateAuthorizedWeeks() {
        window.authorizedWeeksData = window.authorizedWeeksData || {
            owner: [],
            gestor: []
        };

        ['owner', 'gestor'].forEach(type => {
            const container = document.getElementById(`${type}_weeks_container`);
            if (!container) {
                window.authorizedWeeksData[type] = [];
                return;
            }

            const selects = container.querySelectorAll('select[data-week-authorize="true"]');
            const authorized = [];

            selects.forEach(select => {
                if (select.value === 'yes') {
                    const number = parseInt(select.dataset.weekNumber, 10);
                    if (!Number.isNaN(number)) {
                        authorized.push(number);
                    }
                }
            });

            window.authorizedWeeksData[type] = authorized;
        });

        // Sempre aplicar o tipo de fracionamento selecionado a todas as semanas autorizadas
        refreshCalendarIfNeeded();
    }

    function checkAllowedUsesVsAuthorize() {
        const hasQuotaField = document.querySelector('input[name="has_quota"]:checked');
        const hasQuotaValue = hasQuotaField ? hasQuotaField.value : null;
        const quotaOwnerSection = document.getElementById('quota_owner_section');
        const gestorSection = document.getElementById('gestor_section');

        let authorizeSelects = [];
        let allowedUseCheckboxes = [];
        let nextButton = null;
        let messageContainers = [];

        if (hasQuotaValue === '1' && quotaOwnerSection && !quotaOwnerSection.classList.contains('d-none')) {
            const container = document.getElementById('owner_weeks_container');
            if (container) {
                authorizeSelects = container.querySelectorAll('.week-authorize-select');
                allowedUseCheckboxes = Array.from(document.querySelectorAll('input[name="allowed_uses[]"]')).filter(cb => !cb.disabled && cb.checked);
            }
            nextButton = document.getElementById('owner_next_button');
            messageContainers = document.querySelectorAll('#owner_weeks_container .allowed-uses-required-message');
        } else if ((hasQuotaValue === '2' || hasQuotaValue === '3') && gestorSection && !gestorSection.classList.contains('d-none')) {
            const container = document.getElementById('gestor_weeks_container');
            if (container) {
                authorizeSelects = container.querySelectorAll('.week-authorize-select');
                allowedUseCheckboxes = Array.from(document.querySelectorAll('input[name="gestor_allowed_uses[]"]')).filter(cb => !cb.disabled && cb.checked);
            }
            nextButton = document.getElementById('gestor_next_button');
            messageContainers = document.querySelectorAll('#gestor_weeks_container .allowed-uses-required-message');
        }

        if (authorizeSelects.length === 0 || !nextButton) {
            if (nextButton) nextButton.disabled = false;
            document.querySelectorAll('.allowed-uses-required-message').forEach(el => { el.style.display = 'none'; });
            return true;
        }

        const anyAuthorizeSelected = Array.from(authorizeSelects).some(sel => sel.value === 'yes' || sel.value === 'no');
        const hasAllowedUse = allowedUseCheckboxes.length > 0;

        if (anyAuthorizeSelected && !hasAllowedUse) {
            nextButton.disabled = true;
            messageContainers.forEach(el => { el.style.display = 'block'; });
            return false;
        }

        nextButton.disabled = false;
        messageContainers.forEach(el => { el.style.display = 'none'; });
        return true;
    }

    function updateOwnerNextButton() {
        const allowedUsesOk = checkAllowedUsesVsAuthorize();
        const type = getCurrentWeekType();
        const periodOk = validateWeeksPeriodAgainstCurrent(type);
        const nextButton = document.getElementById(type === 'owner' ? 'owner_next_button' : 'gestor_next_button');
        if (nextButton) {
            nextButton.disabled = !(allowedUsesOk && periodOk);
        }
    }

    function getCurrentWeekType() {
        const hasQuotaField = document.querySelector('input[name="has_quota"]:checked');
        if (hasQuotaField && (hasQuotaField.value === '2' || hasQuotaField.value === '3')) {
            return 'gestor';
        }
        return 'owner';
    }

    // Usos permitidos: só Vender marcado (Alugar e Trocar não marcados) — para restringir fracionamento e fixar Ação = Vender
    function onlyVenderAllowed() {
        const hasQuotaField = document.querySelector('input[name="has_quota"]:checked');
        const name = (hasQuotaField && (hasQuotaField.value === '2' || hasQuotaField.value === '3')) ? 'gestor_allowed_uses[]' : 'allowed_uses[]';
        const checked = Array.from(document.querySelectorAll(`input[name="${name}"]:checked`)).map(cb => cb.value);
        const hasRent = checked.includes('rent');
        const hasExchange = checked.includes('exchange');
        const hasSell = checked.includes('sell');
        return hasSell && !hasRent && !hasExchange;
    }

    function getWeekDetails(weekType, weekNumber) {
        const startDaySelect = document.getElementById(`${weekType}_weeks_${weekNumber}_start_day`);
        const monthSelect = document.getElementById(`${weekType}_weeks_${weekNumber}_month`);
        const yearSelect = document.getElementById(`${weekType}_weeks_${weekNumber}_year`);
        const authorizeSelect = document.getElementById(`${weekType}_weeks_${weekNumber}_authorize`);

        const startDay = startDaySelect ? startDaySelect.value : '';
        const month = monthSelect ? monthSelect.value : '';
        const year = yearSelect ? yearSelect.value : '';
        const authorize = authorizeSelect ? authorizeSelect.value : '';

        let startDate = null;
        if (startDay && month && year) {
            const iso = `${year}-${month}-${startDay}T00:00:00`;
            const parsed = new Date(iso);
            if (!Number.isNaN(parsed.getTime())) {
                startDate = parsed;
            }
        }

        return {
            startDate,
            startDay,
            month,
            year,
            authorize,
        };
    }

    function formatDateInput(date) {
        if (!date) {
            return '';
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function getAuthorizedWeeksForCurrentProfile() {
        const hasQuotaField = document.querySelector('input[name="has_quota"]:checked');
        const hasQuotaValue = hasQuotaField ? hasQuotaField.value : '1';

        if (hasQuotaValue === '2' || hasQuotaValue === '3') {
            return window.authorizedWeeksData && window.authorizedWeeksData.gestor ? window.authorizedWeeksData.gestor : [];
        }

        return window.authorizedWeeksData && window.authorizedWeeksData.owner ? window.authorizedWeeksData.owner : [];
    }

    function refreshCalendarIfNeeded() {
        // Verificar se há fracionamento por semana (novo sistema)
        const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
        let hasAnyFraction = false;

        authorizedWeeks.forEach(weekNumber => {
            const fractionSelected = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
            if (fractionSelected) {
                hasAnyFraction = true;
                createCalendarFieldsForWeek(weekNumber, fractionSelected.value);
            }
        });

        // Backward compatibility: verificar também o campo antigo
        if (!hasAnyFraction) {
            const selectedFraction = document.querySelector('input[name="fraction_type"]:checked');
            if (selectedFraction) {
                // Aplicar o tipo de fracionamento selecionado a todas as semanas autorizadas
                createCalendarFields(selectedFraction.value);
            } else {
                // Se não há fracionamento selecionado, limpar os campos de calendário
                const calendarFields = document.getElementById('calendar-fields');
                const calendarContainers = document.getElementById('calendar-containers');
                if (calendarFields) {
                    calendarFields.classList.add('d-none');
                }
                if (calendarContainers) {
                    calendarContainers.innerHTML = '';
                }
            }
        }
    }

    function setupQuotaPeriodAutoFill() {
        const types = ['owner', 'gestor'];

        const calculateEndDay = (startDay) => {
            if (!startDay) {
                return '';
            }

            const start = parseInt(startDay, 10);
            if (Number.isNaN(start)) {
                return '';
            }

            // Regra: dia inicial + 7 = dia final (dia 1 → dia 8)
            let end = start + 7;
            if (end > 31) {
                end = ((end - 1) % 31) + 1;
            }

            return end.toString().padStart(2, '0');
        };

        types.forEach((type) => {
            const startSelect = document.getElementById(`${type}_quota_period_day`);
            const endSelect = document.getElementById(`${type}_quota_period_end_day`);
            const hiddenEnd = document.getElementById(`${type}_quota_period_end_day_hidden`);

            if (!startSelect || !endSelect) {
                return;
            }

            const updateEndDay = () => {
                const endValue = calculateEndDay(startSelect.value);
                endSelect.value = endValue;

                if (hiddenEnd) {
                    hiddenEnd.value = endValue;
                }
            };

            startSelect.addEventListener('change', updateEndDay);
            updateEndDay();
        });
    }

    // Função para criar um bloco de quarto
    function createRoomBlock(type, roomNumber) {
        const roomBlock = document.createElement('div');
        roomBlock.className = 'room-block mb-4 p-4 border rounded bg-light';
        roomBlock.innerHTML = `
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-0 text-primary">
                                <i class="fas fa-bed me-2"></i>Quarto ${roomNumber}
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Suíte</label>
                            <select class="form-select" name="${type}_room_${roomNumber}_suite" required>
                                <option value="">Selecione</option>
                                <option value="0">Não</option>
                                <option value="1">Sim</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bed me-1 text-success"></i>Cama de Casal
                            </label>
                            <select class="form-select" name="${type}_room_${roomNumber}_double_bed" required>
                                <option value="">Selecione</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bed me-1 text-success"></i>Cama de Solteiro
                            </label>
                            <select class="form-select" name="${type}_room_${roomNumber}_single_bed" required>
                                <option value="">Selecione</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-couch me-1 text-success"></i>Sofá Cama
                            </label>
                            <select class="form-select" name="${type}_room_${roomNumber}_sofa_bed" required>
                                <option value="">Selecione</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bed me-1 text-success"></i>Beliche
                            </label>
                            <select class="form-select" name="${type}_room_${roomNumber}_bunk_bed" required>
                                <option value="">Selecione</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-users me-1 text-success"></i>Pessoas
                            </label>
                            <select class="form-select" name="${type}_room_${roomNumber}_people" required>
                                <option value="">Selecione</option>
                                <option value="1">1 Pessoa</option>
                                <option value="2">2 Pessoas</option>
                                <option value="3">3 Pessoas</option>
                                <option value="4">4 Pessoas</option>
                                <option value="5">5 Pessoas</option>
                                <option value="6">6 Pessoas</option>
                                <option value="7">7 Pessoas</option>
                                <option value="8">8 Pessoas</option>
                                <option value="9">9 Pessoas</option>
                                <option value="10">10 Pessoas</option>
                            </select>
                            <div class="small fw-bold text-danger mt-2">Aviso: O número de pessoas deve ser igual ou menor que o número de leitos</div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body py-2">
                                    <h6 class="fw-semibold mb-2"><i class="fas fa-info-circle me-1 text-primary"></i>Capacidade de Leitos</h6>
                                    <ul class="mb-0" style="list-style:none; padding-left:0;">
                                        <li><strong>Cama de casal</strong> — 2 Leitos</li>
                                        <li><strong>Cama de solteiro</strong> — 1 Leito</li>
                                        <li><strong>Sofá cama</strong> — 2 Leitos</li>
                                        <li><strong>Beliche</strong> — 2 Leitos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

        return roomBlock;
    }

    function nextStep(event) {
        // Previne o submit do formulário
        if (event) {
            event.preventDefault();
        }

        console.log('nextStep called, currentStep:', currentStep);

        // Validação especial para o Step 4 (Possuo Cota Hoteleira)
        if (currentStep === 4) {
            const hasQuotaRadio = document.querySelector('input[name="has_quota"]:checked');

            if (!hasQuotaRadio) {
                // Mostrar erro visual em vez de alert
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção em "Possuo Cota Hoteleira?"';

                // Remover erro anterior se existir
                const existingError = document.querySelector('.quota-error-message');
                if (existingError) {
                    existingError.remove();
                }

                errorDiv.classList.add('quota-error-message');
                appendErrorToStep('step4', errorDiv);
                return false;
            }

            const existingError = document.querySelector('.quota-error-message');
            if (existingError) {
                existingError.remove();
            }

            if (!validateCurrentStep()) {
                return false;
            }

            const hasQuotaValue = hasQuotaRadio.value;

            // Se possui cota (proprietário ou gestor), exige ao menos um uso permitido marcado quando "Autoriza..." estiver preenchido
            if (hasQuotaValue === '1' || hasQuotaValue === '2' || hasQuotaValue === '3') {
                if (!checkAllowedUsesVsAuthorize()) {
                    return false;
                }
            }

            document.getElementById(`step${currentStep}`).classList.add('d-none');

            if (hasQuotaValue === '0' || hasQuotaValue === '3') {
                currentStep = 7;
            } else {
                currentStep++;
            }

            const nextStepElement = document.getElementById(`step${currentStep}`);

            if (nextStepElement) {
                nextStepElement.classList.remove('d-none');
            } else {
                console.error('ERRO: Step element not found!');
            }
            updateProgressBar();
            return false;
        }

        console.log('Calling validateCurrentStep for step:', currentStep);

        // Se estamos saindo do Step 6 para o Step 7, garantir que o campo oculto de fracionamento seja atualizado
        if (currentStep === 6 && typeof updateFractionDebugPanel === 'function') {
            try {
                updateFractionDebugPanel();
            } catch (e) {
                console.error('Erro ao atualizar dados de fracionamento ao sair do Step 6:', e);
            }
        }

        // Se estiver no Step 7 (último), não chamar nextStep - permitir submissão do formulário
        if (currentStep === 7) {
            // Validar fracionamento se necessário (apenas se o usuário tiver cota)
            const hasQuota = document.querySelector('input[name="has_quota"]:checked');
            if (hasQuota && (hasQuota.value === '1' || hasQuota.value === '2' || hasQuota.value === '3')) {
                const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
                if (selectedProfile && selectedProfile.value !== 'curioso') {
                    // Verificar fracionamento por semana (novo sistema)
                    const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
                    let allWeeksHaveFraction = true;
                    let missingWeeks = [];

                    authorizedWeeks.forEach(weekNumber => {
                        const fractionSelected = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
                        if (!fractionSelected) {
                            allWeeksHaveFraction = false;
                            missingWeeks.push(weekNumber);
                        }
                    });

                    // Backward compatibility: verificar também o campo antigo
                    const oldFractionSelected = document.querySelector('input[name="fraction_type"]:checked');
                    if (!allWeeksHaveFraction && !oldFractionSelected) {
                        // Mostrar erro visual
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3';
                        if (missingWeeks.length > 0) {
                            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento para ${missingWeeks.length > 1 ? 'as semanas' : 'a semana'} ${missingWeeks.join(', ')}`;
                        } else {
                            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento';
                        }

                        const existingError = document.querySelector('.final-fraction-error-message');
                        if (existingError) {
                            existingError.remove();
                        }

                        errorDiv.classList.add('final-fraction-error-message');
                        const step7 = document.getElementById('step7');
                        if (step7) {
                            step7.insertBefore(errorDiv, step7.firstChild);
                            errorDiv.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                        return false;
                    }
                }
            }

            // Permitir submissão do formulário - não fazer nada, deixar o submit natural acontecer
            return true;
        }

        if (validateCurrentStep()) {
            console.log('Validation passed for step:', currentStep);
            // Additional validation for passwords before proceeding
            if (currentStep === 1) {
                if (!validateUserName()) {
                    return false;
                }
                if (!validatePasswordMatch()) {
                    return false;
                }
            }
            // Validate email uniqueness before proceeding from Step 1
            if (currentStep === 1) {
                const emailInput = document.getElementById('email');
                const emailValue = emailInput ? emailInput.value.trim() : '';
                if (!emailValue) {
                    if (emailInput) emailInput.classList.add('is-invalid');
                    return false;
                }

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // remove previous ajax error if exists
                const existingEmailErr = document.querySelector('.email-ajax-error');
                if (existingEmailErr) existingEmailErr.remove();

                // perform AJAX check
                let emailProceed = false;
                fetch(registerAjaxCheckEmail, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: emailValue })
                }).then(res => res.json()).then(data => {
                    if (!data.valid) {
                        emailInput.classList.add('is-invalid');
                        const err = document.createElement('div');
                        err.className = 'invalid-feedback d-block email-ajax-error';
                        err.innerText = data.message || 'E-mail inválido.';
                        emailInput.parentNode.appendChild(err);
                        emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                    if (data.duplicate) {
                        emailInput.classList.add('is-invalid');
                        const err = document.createElement('div');
                        err.className = 'invalid-feedback d-block email-ajax-error';
                        err.innerText = data.message || 'E-mail já cadastrado no sistema.';
                        emailInput.parentNode.appendChild(err);
                        emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    // ok: advance to next step
                    document.getElementById(`step${currentStep}`).classList.add('d-none');
                    currentStep++;
                    const nextEl = document.getElementById(`step${currentStep}`);
                    if (nextEl) nextEl.classList.remove('d-none');
                    updateProgressBar();
                }).catch(err => {
                    console.error('Email check failed', err);
                    const errDiv = document.createElement('div');
                    errDiv.className = 'invalid-feedback d-block email-ajax-error';
                    errDiv.innerText = 'Erro ao validar e-mail. Tente novamente.';
                    if (emailInput && emailInput.parentNode) emailInput.parentNode.appendChild(errDiv);
                });

                // stop here; progression continues in AJAX callback
                return false;
            }

            // Additional validation for CPF, phone and CEP before proceeding
            if (currentStep === 2) {
                if (!areAllFieldsValid()) {
                    return false;
                }

                // Perform server-side CPF validation & uniqueness check via AJAX
                const cpfInput = document.getElementById('cpf');
                const cpfValue = cpfInput ? cpfInput.value.trim() : '';
                if (!cpfValue) {
                    cpfInput.classList.add('is-invalid');
                    return false;
                }

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(registerAjaxCheckCpf, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cpf: cpfValue })
                }).then(res => res.json()).then(data => {
                    // remove existing dynamic errors
                    const existing = document.querySelector('.cpf-ajax-error');
                    if (existing) existing.remove();

                    if (!data.valid) {
                        cpfInput.classList.add('is-invalid');
                        const err = document.createElement('div');
                        err.className = 'invalid-feedback d-block cpf-ajax-error';
                        err.innerText = data.message || 'CPF inválido.';
                        cpfInput.parentNode.appendChild(err);
                        cpfInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    if (data.duplicate) {
                        cpfInput.classList.add('is-invalid');
                        const err = document.createElement('div');
                        err.className = 'invalid-feedback d-block cpf-ajax-error';
                        err.innerText = data.message || 'CPF já cadastrado no sistema.';
                        cpfInput.parentNode.appendChild(err);
                        cpfInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    // CPF OK => proceed to next step (advance UI)
                    document.getElementById(`step${currentStep}`).classList.add('d-none');
                    currentStep++;
                    const nextEl = document.getElementById(`step${currentStep}`);
                    if (nextEl) nextEl.classList.remove('d-none');
                    updateProgressBar();
                }).catch(err => {
                    console.error('CPF check failed', err);
                    // show generic error
                    const existing = document.querySelector('.cpf-ajax-error');
                    if (existing) existing.remove();
                    const errDiv = document.createElement('div');
                    errDiv.className = 'invalid-feedback d-block cpf-ajax-error';
                    errDiv.innerText = 'Erro ao validar CPF. Tente novamente.';
                    if (cpfInput && cpfInput.parentNode) cpfInput.parentNode.appendChild(errDiv);
                });

                // Stop here — progression will continue in the AJAX callback
                return false;
            }

            // Lógica especial para Step 5 (Seleção de Perfil) - Detectar perfil e configurar Step 6
            if (currentStep === 5) {
                const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
                if (!selectedProfile) {
                    // Mostrar erro visual em vez de alert
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione um perfil de participação';

                    // Remover erro anterior se existir
                    const existingError = document.querySelector('.profile-error-message');
                    if (existingError) {
                        existingError.remove();
                    }

                    errorDiv.classList.add('profile-error-message');
                    appendErrorToStep('step5', errorDiv);
                    return false;
                }

                const profileValue = selectedProfile.value;
                console.log('Perfil selecionado:', profileValue);

                // Configurar Step 6 baseado no perfil selecionado
                configureFractionationStep(profileValue);
            }

            if (currentStep < totalSteps) {
                document.getElementById(`step${currentStep}`).classList.add('d-none');
                currentStep++;

                document.getElementById(`step${currentStep}`).classList.remove('d-none');

                // Sincronizar seleção visual dos cards de perfil ao exibir o step 5
                if (currentStep === 5 && typeof syncProfileCardSelection === 'function') {
                    syncProfileCardSelection();
                }

                // Atualizar barra de progresso
                updateProgressBar();

                // Reconfigurar Step 6 se necessário
                if (currentStep === 6) {
                    const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
                    if (selectedProfile) {
                        configureFractionationStep(selectedProfile.value);
                    }
                    // Garantir que as semanas autorizadas estão atualizadas
                    updateAuthorizedWeeks();
                }
            }
        } else {
            console.log('Validation failed for step:', currentStep);
        }

        // Se chegou até aqui e está no Step 7, permitir submissão
        if (currentStep === 7) {
            return true;
        }

        return false;
    }

    function prevStep() {
        if (currentStep > 1) {
            document.getElementById(`step${currentStep}`).classList.add('d-none');

            let targetStep = currentStep - 1;
            const hasQuotaRadio = document.querySelector('input[name="has_quota"]:checked');
            if (hasQuotaRadio && hasQuotaRadio.value === '0' && targetStep >= 5) {
                targetStep = 4;
            }

            currentStep = targetStep;
            document.getElementById(`step${currentStep}`).classList.remove('d-none');

            if (currentStep === 5 && typeof syncProfileCardSelection === 'function') {
                syncProfileCardSelection();
            }

            // Atualizar barra de progresso
            updateProgressBar();

            // Reconfigurar Step 6 se necessário
            if (currentStep === 6) {
                const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
                if (selectedProfile) {
                    configureFractionationStep(selectedProfile.value);
                }
                // Garantir que as semanas autorizadas estão atualizadas
                updateAuthorizedWeeks();
            }
        }
    }

    /** Step 3: foto de rosto, tipo de documento e foto do documento — realça áreas de upload + select. */
    function syncStep3DocumentsVisualState() {
        const userPhoto = document.getElementById('user_photo');
        const docPhoto = document.getElementById('document_photo');
        const docType = document.getElementById('document_type');
        const uDrop = document.getElementById('user_photo_drop');
        const dDrop = document.getElementById('document_photo_drop');
        const userOk = userPhoto && userPhoto.files && userPhoto.files.length > 0;
        const docOk = docPhoto && docPhoto.files && docPhoto.files.length > 0;
        const typeOk = docType && String(docType.value || '').trim() !== '';
        if (uDrop) {
            uDrop.classList.toggle('is-invalid-field', !userOk);
        }
        if (dDrop) {
            dDrop.classList.toggle('is-invalid-field', !docOk);
        }
        if (docType) {
            docType.classList.toggle('is-invalid', !typeOk);
        }
    }

    function validateCurrentStep() {
        const currentStepElement = document.getElementById(`step${currentStep}`);

        if (!currentStepElement) {
            return false;
        }

        // Validação especial para Step 6 (Fracionamento) - apenas quando estiver no Step 6
        if (currentStep === 6) {
            const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
            if (!selectedProfile) {
                return false;
            }

            // Se não for perfil curioso, deve ter selecionado uma opção de fracionamento para cada semana
            if (selectedProfile.value !== 'curioso') {
                const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
                let allWeeksHaveFraction = true;
                let missingWeeks = [];

                authorizedWeeks.forEach(weekNumber => {
                    const fractionSelected = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
                    if (!fractionSelected) {
                        allWeeksHaveFraction = false;
                        missingWeeks.push(weekNumber);
                    }
                });

                // Backward compatibility: verificar também o campo antigo
                const oldFractionSelected = document.querySelector('input[name="fraction_type"]:checked');
                if (!allWeeksHaveFraction && !oldFractionSelected) {
                    // Mostrar erro visual em vez de alert
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    if (missingWeeks.length > 0) {
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento para ${missingWeeks.length > 1 ? 'as semanas' : 'a semana'} ${missingWeeks.join(', ')}`;
                    } else {
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento para todas as semanas';
                    }

                    // Remover erro anterior se existir
                    const existingError = document.querySelector('.fraction-error-message');
                    if (existingError) {
                        existingError.remove();
                    }

                    errorDiv.classList.add('fraction-error-message');
                    appendErrorToStep('step6', errorDiv);
                    return false;
                }

                // Validar campos de ação dos períodos, exceto para fracionamento integral (7) onde não há períodos
                // Verificar cada semana autorizada
                for (let weekNumber of authorizedWeeks) {
                    const weekFractionSelected = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
                    const fractionValue = weekFractionSelected ? weekFractionSelected.value : (oldFractionSelected ? oldFractionSelected.value : null);
                    
                    if (fractionValue && fractionValue !== '7') {
                        // Verificar todos os checkboxes de períodos habilitados para esta semana
                        const enabledPeriods = currentStepElement.querySelectorAll(`input[id^="week_${weekNumber}_period_"][id$="_enabled"]:checked`);
                        for (let checkbox of enabledPeriods) {
                            const periodId = checkbox.id.replace('_enabled', '');
                            const periodNumber = periodId.replace(`week_${weekNumber}_period_`, '');
                            const actionSelect = document.getElementById(`${periodId}_action_select`);

                            if (actionSelect) {
                                if (!actionSelect.value || actionSelect.value.trim() === '') {
                                    actionSelect.classList.add('is-invalid');

                                    // Mostrar erro visual
                                    const existingError = document.querySelector('.period-action-error-message');
                                    if (existingError) {
                                        existingError.remove();
                                    }

                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'alert alert-danger mt-3 period-action-error-message';
                                    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma ação (Alugar ou Trocar) para o Período ${periodNumber} da Semana ${weekNumber}`;

                                    const calendarFields = document.getElementById('calendar-fields');
                                    if (calendarFields) {
                                        calendarFields.insertBefore(errorDiv, calendarFields.firstChild);
                                        errorDiv.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'center'
                                        });
                                    }

                                    return false;
                                } else {
                                    actionSelect.classList.remove('is-invalid');
                                }
                            }
                        }
                    }
                }
            }
            
            // Se chegou até aqui, a validação passou para o Step 6
            return true;
        }

        const ownerAdditionalFields = document.getElementById('owner_additional_fields');
        const gestorAdditionalFields = document.getElementById('gestor_additional_fields');

        let requiredFields = Array.from(currentStepElement.querySelectorAll('[required]'));

        if (ownerAdditionalFields && ownerAdditionalFields.classList.contains('d-none')) {
            requiredFields = requiredFields.filter(field => !ownerAdditionalFields.contains(field));
        }
        if (gestorAdditionalFields && gestorAdditionalFields.classList.contains('d-none')) {
            requiredFields = requiredFields.filter(field => !gestorAdditionalFields.contains(field));
        }

        let firstInvalidField = null;
        for (let field of requiredFields) {
            // Ignora campos dentro de um ancestral .d-none (ex.: seções colapsadas).
            // Não tratar o próprio campo como "oculto" só por ter classe d-none (ex.: file input estilizado).
            const hiddenAncestor = field.closest('.d-none');
            const parentHidden = hiddenAncestor && hiddenAncestor !== field;
            if (parentHidden || field.disabled) {
                continue;
            }

            let isValid = true;

            // Validação especial para radio buttons e checkboxes
            if (field.type === 'radio') {
                const radioName = field.name;
                const isChecked = document.querySelector(`input[name="${radioName}"]:checked`);
                if (!isChecked) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || field;
                }
            } else if (field.type === 'checkbox') {
                // Para checkboxes, verifica se pelo menos um está marcado no grupo
                const checkboxName = field.name;
                const visibleCheckboxes = Array.from(document.querySelectorAll(`input[name="${checkboxName}"]`))
                    .filter(cb => !cb.closest('.d-none') && !cb.disabled); // Ignora checkboxes desabilitados

                const anyChecked = visibleCheckboxes.some(cb => cb.checked);
                if (!anyChecked && field.hasAttribute('required')) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || (visibleCheckboxes.length > 0 ? visibleCheckboxes[0] : field);
                }
            } else if (field.type === 'file') {
                // Para campos de arquivo, verifica se há arquivo selecionado
                if (field.files.length === 0) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || field;
                } else {
                    // ok
                }
            } else {
                // Para campos de texto, select, etc.
                if (!field.value || !field.value.trim()) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || field;
                }
            }

            if (!isValid) {
                field.classList.add('is-invalid');
                // scroll/focus first invalid
                if (firstInvalidField && !firstInvalidField.scrolledIntoView) {
                    try { firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(e){}
                    try { firstInvalidField.focus(); } catch(e){}
                    firstInvalidField.scrolledIntoView = true;
                    setTimeout(() => { firstInvalidField.scrolledIntoView = false; }, 1000);
                }
            } else {
                field.classList.remove('is-invalid');
            }
        }

        if (currentStep === 3) {
            syncStep3DocumentsVisualState();
        }

        if (firstInvalidField) {
            if (currentStep === 3) {
                let scrollEl = null;
                const fid = firstInvalidField.id || '';
                if (fid === 'user_photo') {
                    scrollEl = document.getElementById('user_photo_drop');
                } else if (fid === 'document_photo') {
                    scrollEl = document.getElementById('document_photo_drop');
                } else if (fid === 'document_type') {
                    scrollEl = document.getElementById('document_type');
                }
                if (scrollEl) {
                    try {
                        scrollEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } catch (e) {}
                }
                showValidationError(currentStepElement, 'Selecione a foto de rosto, o tipo de documento (RG ou CNH) e a foto do documento para continuar.');
            } else {
                const fieldLabel = firstInvalidField.previousElementSibling?.textContent?.trim() ||
                                   firstInvalidField.closest('.form-group')?.querySelector('label')?.textContent?.trim() ||
                                   firstInvalidField.closest('.col-md-12')?.querySelector('label')?.textContent?.trim() ||
                                   firstInvalidField.name;
                showValidationError(currentStepElement, `Por favor, preencha o campo: ${fieldLabel}`);
            }
            return false;
        }

        // Additional validation for password fields
        if (currentStep === 1) { // Step 1 contains password fields
            if (!validateUserName()) {
                return false;
            }
            if (!validatePasswordMatch()) {
                return false;
            }
        }

        // Additional validation for CPF, phone and CEP fields
        if (currentStep === 2) { // Step 2 contains CPF, phone and CEP fields
            if (!areAllFieldsValid()) {
                return false;
            }
        }
        
        function showValidationError(container, message) {
            const existing = container.querySelector('.validation-error-message');
            if (existing) existing.remove();
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mt-3 validation-error-message';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
            container.insertBefore(errorDiv, container.firstChild);
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => { if (errorDiv.parentNode) errorDiv.remove(); }, 5000);
        }

        // Validação especial para Step 4 - verificar campos de configuração dos quartos
        if (currentStep === 4) {
            const hasQuotaForGestor = document.querySelector('input[name="has_quota"]:checked');
            if (hasQuotaForGestor && hasQuotaForGestor.value === '2') {
                const linkedIdInp = document.getElementById('gestor_linked_owner_user_id');
                if (!linkedIdInp || !String(linkedIdInp.value || '').trim()) {
                    const step4El = document.getElementById('step4');
                    showValidationError(step4El, 'Use o botão Verificar no CPF do proprietário para confirmar que você é o gestor autorizado antes de continuar.');
                    const ownerCpfEl = document.getElementById('gestor_claimed_owner_cpf');
                    if (ownerCpfEl) {
                        try {
                            ownerCpfEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } catch (e) {}
                    }
                    return false;
                }
            }
            // Verificar se a seção de configuração dos quartos está visível
            const ownerRoomsConfig = document.getElementById('rooms-configuration');
            const gestorRoomsConfig = document.getElementById('gestor-rooms-configuration');
            const ownerAdditionalFields = document.getElementById('owner_additional_fields');
            const gestorAdditionalFields = document.getElementById('gestor_additional_fields');

            // Validar quartos do proprietário
            if (ownerRoomsConfig && !ownerRoomsConfig.classList.contains('d-none')) {
                const ownerRoomFields = ownerRoomsConfig.querySelectorAll('select[required]');
                for (let field of ownerRoomFields) {
                    if (!field.value || field.value.trim() === '') {
                        field.classList.add('is-invalid');

                        // Mostrar erro visual
                        const existingError = document.querySelector('.rooms-validation-error');
                        if (existingError) {
                            existingError.remove();
                        }

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3 rooms-validation-error';
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, preencha todos os campos de configuração dos quartos';
                        ownerRoomsConfig.insertBefore(errorDiv, ownerRoomsConfig.firstChild);

                        // Scroll para o erro
                        errorDiv.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                }
            }

            // Validar quartos do gestor (apenas se os campos existirem e estiverem visíveis)
            if (gestorRoomsConfig && !gestorRoomsConfig.classList.contains('d-none')) {
                const gestorRoomFields = gestorRoomsConfig.querySelectorAll('select[required], input[required]');
                // Só valida se houver campos criados dinamicamente
                if (gestorRoomFields.length > 0) {
                    for (let field of gestorRoomFields) {
                        if (!field.value || field.value.trim() === '') {
                            field.classList.add('is-invalid');

                            // Mostrar erro visual
                            const existingError = document.querySelector('.rooms-validation-error');
                            if (existingError) {
                                existingError.remove();
                            }

                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-danger mt-3 rooms-validation-error';
                            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, preencha todos os campos de configuração dos quartos';
                            gestorRoomsConfig.insertBefore(errorDiv, gestorRoomsConfig.firstChild);

                            // Scroll para o erro
                            errorDiv.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            return false;
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    }
                }
                // Se não há campos criados, não bloqueia (campos são opcionais)
            }
        }

        // Validação especial para Step 7 (Finalização) - verificar fracionamento se necessário
        if (currentStep === 7) {
            // Validar fracionamento apenas se o usuário tiver cota
            const hasQuota = document.querySelector('input[name="has_quota"]:checked');
            if (hasQuota && (hasQuota.value === '1' || hasQuota.value === '2' || hasQuota.value === '3')) {
                const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
                if (selectedProfile && selectedProfile.value !== 'curioso') {
                    // Verificar fracionamento por semana (novo sistema)
                    const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
                    let allWeeksHaveFraction = true;
                    let missingWeeks = [];

                    authorizedWeeks.forEach(weekNumber => {
                        const fractionSelected = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
                        if (!fractionSelected) {
                            allWeeksHaveFraction = false;
                            missingWeeks.push(weekNumber);
                        }
                    });

                    // Backward compatibility: verificar também o campo antigo
                    const oldFractionSelected = document.querySelector('input[name="fraction_type"]:checked');
                    if (!allWeeksHaveFraction && !oldFractionSelected) {
                        // Mostrar erro visual em vez de alert
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3';
                        if (missingWeeks.length > 0) {
                            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento para ${missingWeeks.length > 1 ? 'as semanas' : 'a semana'} ${missingWeeks.join(', ')}`;
                        } else {
                            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento';
                        }

                        // Remover erro anterior se existir
                        const existingError = document.querySelector('.final-fraction-error-message');
                        if (existingError) {
                            existingError.remove();
                        }

                        errorDiv.classList.add('final-fraction-error-message');
                        const step7 = document.getElementById('step7');
                        if (step7) {
                            step7.insertBefore(errorDiv, step7.firstChild);
                            errorDiv.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                        return false;
                    }
                }
            }
        }

        return true;
    }

    // Toggle password visibility
    function togglePassword(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.getElementById('togglePassword1').addEventListener('click', () => togglePassword('password', 'togglePassword1'));
    document.getElementById('togglePassword2').addEventListener('click', () => togglePassword('password_confirmation', 'togglePassword2'));

    // Password validation
    const nameField = document.getElementById('name');
    if (nameField) {
        nameField.addEventListener('input', function() {
            updateStep1ValidationState();
        });
    }

    const fullNameField = document.getElementById('full_name');
    if (fullNameField) {
        fullNameField.addEventListener('input', function() {
            validateFullName(false);
        });
    }

    document.getElementById('password').addEventListener('input', function() {
        updateStep1ValidationState();
    });

    document.getElementById('password_confirmation').addEventListener('input', function() {
        updateStep1ValidationState();
    });

    updateStep1ValidationState();

    // CPF mask and validation
    document.getElementById('cpf').addEventListener('input', function(e) {
        if (typeof resetOwnerDelegatedVerifyUi === 'function') {
            resetOwnerDelegatedVerifyUi();
        }
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;

        // Validate CPF length
        validateFieldLength('cpf', value.replace(/\D/g, ''), 11, 'CPF deve ter 11 dígitos');

        const hasQuotaRadio = document.querySelector('input[name="has_quota"]:checked');
        if (hasQuotaRadio && hasQuotaRadio.value === '2' && typeof resetOwnerDelegatedVerifyUi === 'function') {
            resetOwnerDelegatedVerifyUi();
        }
    });

    const gestorDelegateCpfEl = document.getElementById('gestor_delegate_cpf');
    if (gestorDelegateCpfEl) {
        gestorDelegateCpfEl.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
            validateFieldLength('gestor_delegate_cpf', value.replace(/\D/g, ''), 11, 'CPF do gestor deve ter 11 dígitos');
        });
    }

    const gestorClaimedOwnerCpfEl = document.getElementById('gestor_claimed_owner_cpf');
    if (gestorClaimedOwnerCpfEl) {
        gestorClaimedOwnerCpfEl.addEventListener('input', function(e) {
            if (typeof resetOwnerDelegatedVerifyUi === 'function') {
                resetOwnerDelegatedVerifyUi();
            }
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
            validateFieldLength('gestor_claimed_owner_cpf', value.replace(/\D/g, ''), 11, 'CPF do proprietário deve ter 11 dígitos');
        });
    }
    // O clique em "Verificar" é tratado por delegação no #registerForm (DOMContentLoaded),
    // para funcionar mesmo com ícone interno no botão e após reexibir a seção gestor.

    // Phone mask and validation
    function applyPhoneMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;

            // Validate phone length
            validateFieldLength('phone', value, 11, 'Telefone deve ter 11 dígitos');
        });
    }

    // Phone mask will be applied in DOMContentLoaded


    // Toggle quota owner section based on has_quota selection
    function toggleQuotaSections(value) {
        const quotaOwnerSection = document.getElementById('quota_owner_section');
        const noQuotaSection = document.getElementById('no_quota_section');
        const gestorSection = document.getElementById('gestor_section');
        const noQuotaWarning = document.getElementById('no_quota_warning');
        const quotaContractInput = document.getElementById('quota_contract');
        const allowedUseSell = document.getElementById('use_sell');
        const allowedUseExchange = document.getElementById('use_exchange');
        const allowedUseRent = document.getElementById('use_rent');
        const allowedUseBuy = document.getElementById('use_buy');
        const profileRadios = document.querySelectorAll('input[name="profile_type"]');
        const fractionRadios = document.querySelectorAll('input[name="fraction_type"]');
        const ownerWeeksContainer = document.getElementById('owner_weeks_container');
        const gestorWeeksContainer = document.getElementById('gestor_weeks_container');

        // Se nenhum valor foi selecionado, oculta todos os formulários
        if (!value) {
            if (quotaOwnerSection) quotaOwnerSection.classList.add('d-none');
            if (noQuotaSection) noQuotaSection.classList.add('d-none');
            if (gestorSection) {
                gestorSection.classList.add('d-none');
                // Remover required dos campos gestor quando a seção é ocultada
                const gestorCheckboxes = document.querySelectorAll('input[name="gestor_allowed_uses[]"]');
                gestorCheckboxes.forEach(checkbox => {
                    checkbox.removeAttribute('required');
                    checkbox.required = false;
                });
                // Garantir remoção
                initializeGestorRequiredFields();
            }
            if (noQuotaWarning) noQuotaWarning.classList.add('d-none');
            profileRadios.forEach(radio => (radio.checked = false));
            fractionRadios.forEach(radio => (radio.checked = false));
            if (typeof updateGestorContextBanners === 'function') {
                updateGestorContextBanners('');
            }
            toggleOwnerDelegateHiddenSections('');
            return;
        }

        if (value === '1') {
            // Possui cota - Mostra todos os campos e funções
            if (quotaOwnerSection) quotaOwnerSection.classList.remove('d-none');
            if (noQuotaSection) noQuotaSection.classList.add('d-none');
            if (gestorSection) gestorSection.classList.add('d-none');
            if (noQuotaWarning) noQuotaWarning.classList.add('d-none');
            if (quotaContractInput) quotaContractInput.required = true;
            setSectionEnabled(quotaOwnerSection, true);
            setSectionEnabled(gestorSection, false);
            setSectionEnabled(noQuotaSection, false);
            if (gestorWeeksContainer) gestorWeeksContainer.innerHTML = '';
            if (typeof renderWeekBlocks === 'function') {
                renderWeekBlocks('owner', window.initialOwnerWeeks || {});
            }

            // Resetar escolhas de perfil/fracionamento para que o usuário selecione novamente
            profileRadios.forEach(radio => (radio.checked = false));
            fractionRadios.forEach(radio => (radio.checked = false));

            // Habilita todas as funções
            if (allowedUseSell) {
                allowedUseSell.disabled = false;
                const sellCard = allowedUseSell.closest('.use-option-card');
                if (sellCard) {
                    sellCard.classList.remove('disabled');
                }
            }
            if (allowedUseExchange) {
                allowedUseExchange.disabled = false;
                const exchangeCard = allowedUseExchange.closest('.use-option-card');
                if (exchangeCard) {
                    exchangeCard.classList.remove('disabled');
                }
            }
            if (allowedUseRent) {
                allowedUseRent.disabled = false;
                const rentCard = allowedUseRent.closest('.use-option-card');
                if (rentCard) {
                    rentCard.classList.remove('disabled');
                }
            }
            if (allowedUseBuy) {
                allowedUseBuy.disabled = false;
                const buyCard = allowedUseBuy.closest('.use-option-card');
                if (buyCard) {
                    buyCard.classList.remove('disabled');
                }
            }
            if (typeof updateGestorContextBanners === 'function') {
                updateGestorContextBanners(value);
            }
            toggleOwnerDelegateHiddenSections(value);

        } else if (value === '2' || value === '3') {
            // Gestor autorizado (2) ou titular que delega gestão (3) — mesmo formulário
            if (quotaOwnerSection) quotaOwnerSection.classList.add('d-none');
            if (noQuotaSection) noQuotaSection.classList.add('d-none');
            if (gestorSection) gestorSection.classList.remove('d-none');
            if (noQuotaWarning) noQuotaWarning.classList.add('d-none');
            if (quotaContractInput) quotaContractInput.required = false;
            setSectionEnabled(quotaOwnerSection, false);
            setSectionEnabled(gestorSection, true);
            setSectionEnabled(noQuotaSection, false);
            if (ownerWeeksContainer) ownerWeeksContainer.innerHTML = '';
            if (typeof renderWeekBlocks === 'function') {
                renderWeekBlocks('gestor', window.initialGestorWeeks || {});
            }
            
            // Atualizar campos required dos gestores após mostrar a seção
            setTimeout(() => {
                if (typeof toggleGestorAllowedUses === 'function') {
                    toggleGestorAllowedUses();
                }
            }, 100);
            
            // Garantir que required seja removido se ainda estiver oculto
            initializeGestorRequiredFields();

            // For gestor: allow only Alugar (rent) and Troca (exchange). Disable Compra and Venda (buy, sell).
            if (allowedUseSell) {
                allowedUseSell.checked = false;
                allowedUseSell.disabled = true;
                allowedUseSell.required = false;
                const sellCard = allowedUseSell.closest('.use-option-card');
                if (sellCard) {
                    sellCard.classList.add('disabled');
                    sellCard.classList.remove('selected');
                }
            }
            if (allowedUseBuy) {
                allowedUseBuy.checked = false;
                allowedUseBuy.disabled = true;
                allowedUseBuy.required = false;
                const buyCard = allowedUseBuy.closest('.use-option-card');
                if (buyCard) {
                    buyCard.classList.add('disabled');
                    buyCard.classList.remove('selected');
                }
            }
            if (allowedUseRent) {
                allowedUseRent.disabled = false;
                const rentCard = allowedUseRent.closest('.use-option-card');
                if (rentCard) {
                    rentCard.classList.remove('disabled');
                }
            }
            if (allowedUseExchange) {
                allowedUseExchange.disabled = false;
                const exchangeCard = allowedUseExchange.closest('.use-option-card');
                if (exchangeCard) {
                    exchangeCard.classList.remove('disabled');
                }
            }

            profileRadios.forEach(radio => {
                if (radio.value === 'curioso') {
                    radio.checked = true;
                } else {
                    radio.checked = false;
                }
            });
            fractionRadios.forEach(radio => (radio.checked = false));
            if (typeof updateGestorContextBanners === 'function') {
                updateGestorContextBanners(value);
            }
            toggleOwnerDelegateHiddenSections(value);

        } else {
            // Não possui cota - Oculta campos e desabilita funções de venda/troca
            if (quotaOwnerSection) {
                quotaOwnerSection.classList.add('d-none');
            }
            if (noQuotaSection) {
                noQuotaSection.classList.remove('d-none');
            }
            if (gestorSection) {
                gestorSection.classList.add('d-none');
                // Remover required dos campos gestor quando a seção é ocultada
                const gestorCheckboxes = gestorSection.querySelectorAll('input[name="gestor_allowed_uses[]"]');
                gestorCheckboxes.forEach(checkbox => {
                    checkbox.removeAttribute('required');
                });
            }
            if (noQuotaWarning) {
                noQuotaWarning.classList.remove('d-none');
            }
            if (quotaContractInput) quotaContractInput.required = false;
            setSectionEnabled(quotaOwnerSection, false);
            setSectionEnabled(gestorSection, false);
            setSectionEnabled(noQuotaSection, true);
            if (ownerWeeksContainer) ownerWeeksContainer.innerHTML = '';
            if (gestorWeeksContainer) gestorWeeksContainer.innerHTML = '';

            // Desabilita venda e troca, mantém apenas alugar e comprar
            if (allowedUseSell) {
                allowedUseSell.checked = false;
                allowedUseSell.disabled = true;
                allowedUseSell.required = false;
                const sellCard = allowedUseSell.closest('.use-option-card');
                if (sellCard) {
                    sellCard.classList.add('disabled');
                    sellCard.classList.remove('selected');
                }
            }
            if (allowedUseExchange) {
                allowedUseExchange.checked = false;
                allowedUseExchange.disabled = true;
                allowedUseExchange.required = false;
                const exchangeCard = allowedUseExchange.closest('.use-option-card');
                if (exchangeCard) {
                    exchangeCard.classList.add('disabled');
                    exchangeCard.classList.remove('selected');
                }
            }
            if (allowedUseRent) {
                allowedUseRent.disabled = false;
                const rentCard = allowedUseRent.closest('.use-option-card');
                if (rentCard) {
                    rentCard.classList.remove('disabled');
                }
            }
            if (allowedUseBuy) {
                allowedUseBuy.disabled = false;
                const buyCard = allowedUseBuy.closest('.use-option-card');
                if (buyCard) {
                    buyCard.classList.remove('disabled');
                }
            }

            profileRadios.forEach(radio => {
                if (radio.value === 'curioso') {
                    radio.checked = true;
                } else {
                    radio.checked = false;
                }
            });
            fractionRadios.forEach(radio => (radio.checked = false));
            if (typeof updateGestorContextBanners === 'function') {
                updateGestorContextBanners(value);
            }
            toggleOwnerDelegateHiddenSections(value);
        }

        if (typeof syncProfileCardSelection === 'function') {
            syncProfileCardSelection();
        }
    }

    function toggleGestorClassicAuthorizationUpload(hasQuotaValue) {
        const uploadWrap = document.getElementById('gestor_classic_authorization_upload_wrap');
        const fileInput = document.getElementById('gestor_authorization_document');
        const isOwnerDelegate = String(hasQuotaValue) === '3';
        if (uploadWrap) {
            uploadWrap.classList.toggle('d-none', !isOwnerDelegate);
        }
        if (fileInput) {
            fileInput.required = isOwnerDelegate;
            fileInput.disabled = !isOwnerDelegate;
            if (!isOwnerDelegate) {
                fileInput.value = '';
            }
        }
    }

    function resetOwnerDelegatedVerifyUi() {
        const ok = document.getElementById('gestor_owner_verify_success');
        const err = document.getElementById('gestor_owner_verify_error');
        const hid = document.getElementById('gestor_linked_owner_user_id');
        const docBtn = document.getElementById('gestor_owner_verify_doc_btn');
        if (ok) ok.classList.add('d-none');
        if (err) {
            err.classList.add('d-none');
            err.textContent = '';
            err.innerHTML = '';
        }
        if (hid) hid.value = '';
        if (docBtn) {
            docBtn.disabled = true;
            docBtn.removeAttribute('data-download-name');
        }
    }

    function parseDownloadFilename(contentDisposition, fallbackName) {
        if (!contentDisposition) {
            return fallbackName;
        }
        const utf8Match = contentDisposition.match(/filename\*=UTF-8''([^;]+)/i);
        if (utf8Match && utf8Match[1]) {
            try {
                return decodeURIComponent(utf8Match[1].trim());
            } catch (e) {
                return fallbackName;
            }
        }
        const asciiMatch = contentDisposition.match(/filename="?([^";]+)"?/i);
        if (asciiMatch && asciiMatch[1]) {
            return asciiMatch[1].trim();
        }
        return fallbackName;
    }

    function downloadDelegatedGestorDocument() {
        const ownerInp = document.getElementById('gestor_claimed_owner_cpf');
        const gestorInp = document.getElementById('cpf');
        const docBtn = document.getElementById('gestor_owner_verify_doc_btn');
        const errEl = document.getElementById('gestor_owner_verify_error');
        const linkedIdInp = document.getElementById('gestor_linked_owner_user_id');

        if (!ownerInp || !gestorInp || !docBtn) {
            return;
        }
        if (!String(linkedIdInp?.value || '').trim()) {
            if (errEl) {
                errEl.innerHTML = '<strong>Verifique o CPF do proprietário antes de baixar o documento.</strong>';
                errEl.classList.remove('d-none');
            }
            return;
        }

        const ownerDigits = ownerInp.value.replace(/\D/g, '');
        const gestorDigits = gestorInp.value.replace(/\D/g, '');
        if (ownerDigits.length !== 11 || gestorDigits.length !== 11) {
            if (errEl) {
                errEl.innerHTML = '<strong>Preencha os CPFs completos antes de baixar o documento.</strong>';
                errEl.classList.remove('d-none');
            }
            return;
        }

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
        const fallbackName = docBtn.getAttribute('data-download-name') || 'autorizacao-gestao-cota.pdf';

        docBtn.disabled = true;
        if (errEl) {
            errEl.classList.add('d-none');
            errEl.innerHTML = '';
        }

        fetch(registerAjaxDownloadDelegatedGestorDoc, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/octet-stream',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                owner_cpf: ownerInp.value.trim(),
                gestor_cpf: gestorInp.value.trim()
            })
        }).then(function (response) {
            if (!response.ok) {
                return response.text().then(function (text) {
                    let message = 'Não foi possível baixar o documento.';
                    try {
                        const data = text ? JSON.parse(text) : {};
                        if (data.message) {
                            message = data.message;
                        }
                    } catch (e) {}
                    throw new Error(message);
                });
            }
            const filename = parseDownloadFilename(
                response.headers.get('Content-Disposition'),
                fallbackName
            );
            return response.blob().then(function (blob) {
                return { blob: blob, filename: filename };
            });
        }).then(function (result) {
            const blobUrl = window.URL.createObjectURL(result.blob);
            const anchor = document.createElement('a');
            anchor.href = blobUrl;
            anchor.download = result.filename;
            anchor.style.display = 'none';
            document.body.appendChild(anchor);
            anchor.click();
            anchor.remove();
            window.URL.revokeObjectURL(blobUrl);
        }).catch(function (error) {
            if (errEl) {
                errEl.innerHTML = '<strong>' + (error.message || 'Erro ao baixar o documento.') + '</strong>';
                errEl.classList.remove('d-none');
            }
        }).finally(function () {
            docBtn.disabled = false;
        });
    }

    function verifyDelegatedGestorOwner() {
        const ownerInp = document.getElementById('gestor_claimed_owner_cpf');
        const gestorInp = document.getElementById('cpf');
        const errEl = document.getElementById('gestor_owner_verify_error');
        const okEl = document.getElementById('gestor_owner_verify_success');
        const btn = document.getElementById('gestor_verify_owner_btn');
        if (!ownerInp || !gestorInp) {
            if (errEl) {
                errEl.innerHTML = '<strong>Não foi possível carregar os campos de CPF. Atualize a página e tente novamente.</strong>';
                errEl.classList.remove('d-none');
            }
            return;
        }
        resetOwnerDelegatedVerifyUi();
        const ownerDigits = ownerInp.value.replace(/\D/g, '');
        const gestorDigits = gestorInp.value.replace(/\D/g, '');
        if (gestorDigits.length !== 11) {
            if (errEl) {
                errEl.innerHTML = '<strong>Preencha o seu CPF completo (passo anterior) antes de verificar o vínculo.</strong>';
                errEl.classList.remove('d-none');
            }
            gestorInp.classList.add('is-invalid');
            return;
        }
        if (ownerDigits.length !== 11) {
            if (errEl) {
                errEl.innerHTML = '<strong>Informe o CPF completo do proprietário (11 dígitos).</strong>';
                errEl.classList.remove('d-none');
            }
            ownerInp.classList.add('is-invalid');
            return;
        }
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
        if (btn) btn.disabled = true;
        const verifyUrl = registerAjaxCheckDelegatedGestor;
        fetch(verifyUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                owner_cpf: ownerInp.value.trim(),
                gestor_cpf: gestorInp.value.trim()
            })
        }).then(function (r) {
            return r.text().then(function (text) {
                let data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (e) {
                    data = { ok: false, message: 'Resposta inválida do servidor. Atualize a página e tente novamente.' };
                }
                return { okHttp: r.ok, data: data };
            });
        }).then(function (result) {
            if (btn) btn.disabled = false;
            const data = result.data || {};
            if (data.ok) {
                const hid = document.getElementById('gestor_linked_owner_user_id');
                if (hid) {
                    hid.value = String(data.owner_user_id || '');
                }
                const nameEl = document.getElementById('gestor_owner_verify_name');
                if (nameEl) {
                    nameEl.textContent = data.owner_name || '';
                }
                const docBtn = document.getElementById('gestor_owner_verify_doc_btn');
                if (docBtn) {
                    docBtn.disabled = false;
                    docBtn.setAttribute(
                        'data-download-name',
                        data.document_download_name || 'autorizacao-gestao-cota.pdf'
                    );
                }
                if (okEl) {
                    okEl.classList.remove('d-none');
                }
                ownerInp.classList.remove('is-invalid');
                gestorInp.classList.remove('is-invalid');
            } else {
                if (errEl) {
                    errEl.innerHTML = '<strong>' + (data.message || 'Não foi possível validar o vínculo.') + '</strong>';
                    errEl.classList.remove('d-none');
                }
                ownerInp.classList.add('is-invalid');
            }
        }).catch(function () {
            if (btn) btn.disabled = false;
            if (errEl) {
                errEl.innerHTML = '<strong>Erro de conexão. Tente novamente.</strong>';
                errEl.classList.remove('d-none');
            }
        });
    }

    function updateGestorContextBanners(hasQuotaValue) {
        const classic = document.getElementById('gestor_banner_classic');
        const ownerDel = document.getElementById('gestor_banner_owner_delegate');
        if (classic && ownerDel) {
            if (hasQuotaValue === '2') {
                classic.classList.remove('d-none');
                ownerDel.classList.add('d-none');
            } else if (hasQuotaValue === '3') {
                ownerDel.classList.remove('d-none');
                classic.classList.add('d-none');
            } else {
                classic.classList.add('d-none');
                ownerDel.classList.add('d-none');
            }
        }

        const helpClassic = document.getElementById('gestor_authorization_document_help_classic');
        const helpOwnerDelegate = document.getElementById('gestor_authorization_document_help_owner_delegate');
        const gestorInfoTitle = document.getElementById('gestor_information_title');
        if (gestorInfoTitle) {
            gestorInfoTitle.classList.toggle('d-none', String(hasQuotaValue) === '3');
        }
        const labelClassic = document.getElementById('gestor_authorization_document_label_classic');
        const labelOwnerDelegate = document.getElementById('gestor_authorization_document_label_owner_delegate');
        if (labelClassic && labelOwnerDelegate) {
            if (hasQuotaValue === '2') {
                labelClassic.classList.remove('d-none');
                labelOwnerDelegate.classList.add('d-none');
            } else if (hasQuotaValue === '3') {
                labelClassic.classList.add('d-none');
                labelOwnerDelegate.classList.remove('d-none');
            } else {
                labelClassic.classList.remove('d-none');
                labelOwnerDelegate.classList.add('d-none');
            }
        }
        if (helpClassic && helpOwnerDelegate) {
            if (hasQuotaValue === '2') {
                helpClassic.classList.remove('d-none');
                helpOwnerDelegate.classList.add('d-none');
            } else if (hasQuotaValue === '3') {
                helpClassic.classList.add('d-none');
                helpOwnerDelegate.classList.remove('d-none');
            } else {
                helpClassic.classList.add('d-none');
                helpOwnerDelegate.classList.add('d-none');
            }
        }

        const classicOwnerVerify = document.getElementById('classic_gestor_owner_verify_block');
        const gestorClaimedOwnerCpf = document.getElementById('gestor_claimed_owner_cpf');
        const gestorLinkedOwnerId = document.getElementById('gestor_linked_owner_user_id');
        if (classicOwnerVerify && gestorClaimedOwnerCpf && gestorLinkedOwnerId) {
            const showClassicOwner = String(hasQuotaValue) === '2';
            classicOwnerVerify.classList.toggle('d-none', !showClassicOwner);
            gestorClaimedOwnerCpf.disabled = !showClassicOwner;
            gestorClaimedOwnerCpf.required = showClassicOwner;
            gestorLinkedOwnerId.disabled = !showClassicOwner;
            const verifyOwnerBtn = document.getElementById('gestor_verify_owner_btn');
            if (verifyOwnerBtn) {
                verifyOwnerBtn.disabled = !showClassicOwner;
            }
            if (!showClassicOwner) {
                gestorClaimedOwnerCpf.value = '';
                gestorLinkedOwnerId.value = '';
                gestorClaimedOwnerCpf.classList.remove('is-invalid');
                if (typeof resetOwnerDelegatedVerifyUi === 'function') {
                    resetOwnerDelegatedVerifyUi();
                }
            }
        }

        if (typeof toggleGestorClassicAuthorizationUpload === 'function') {
            toggleGestorClassicAuthorizationUpload(hasQuotaValue);
        }
    }

    function toggleOwnerDelegateHiddenSections(hasQuotaValue) {
        const isOwnerDelegate = String(hasQuotaValue) === '3';
        const hiddenForOwnerDelegateSections = [
            document.getElementById('gestor_hotel_operational_section'),
            document.getElementById('gestor_quota_status_section'),
            document.getElementById('gestor_quota_facilities_section'),
            document.getElementById('gestor_quota_details_classic_only'),
        ].filter(Boolean);

        hiddenForOwnerDelegateSections.forEach(section => {
            section.classList.toggle('d-none', isOwnerDelegate);
            section.querySelectorAll('input, select, textarea').forEach(field => {
                if (isOwnerDelegate) {
                    if (field.required) {
                        field.dataset.ownerDelegateRequired = 'true';
                        field.required = false;
                    }
                    if (!field.disabled) {
                        field.dataset.ownerDelegateDisabled = 'true';
                        field.disabled = true;
                    }
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = false;
                    } else if (field.type !== 'file') {
                        field.value = '';
                    }
                    field.classList.remove('is-invalid');
                } else {
                    if (field.dataset.ownerDelegateDisabled === 'true') {
                        field.disabled = false;
                        delete field.dataset.ownerDelegateDisabled;
                    }
                    if (field.dataset.ownerDelegateRequired === 'true') {
                        field.required = true;
                        delete field.dataset.ownerDelegateRequired;
                    }
                }
            });
        });

        const possessionSection = document.getElementById('owner_delegate_possession_section');
        const possessionInput = document.getElementById('owner_delegate_possession_confirmation');
        if (possessionSection && possessionInput) {
            possessionSection.classList.toggle('d-none', !isOwnerDelegate);
            possessionInput.disabled = !isOwnerDelegate;
            possessionInput.required = isOwnerDelegate;
            if (!isOwnerDelegate) {
                possessionInput.value = '';
                possessionInput.classList.remove('is-invalid');
            }
        }

        const isClassicGestor = String(hasQuotaValue) === '2';
        const classicPossessionSection = document.getElementById('gestor_classic_possession_section');
        const classicPossessionInput = document.getElementById('gestor_quota_contracts');
        if (classicPossessionSection && classicPossessionInput) {
            classicPossessionSection.classList.toggle('d-none', !isClassicGestor);
            classicPossessionInput.disabled = !isClassicGestor;
            classicPossessionInput.required = isClassicGestor;
            if (!isClassicGestor) {
                classicPossessionInput.value = '';
                classicPossessionInput.classList.remove('is-invalid');
            }
        }

        const gestorDelegateCpfWrap = document.getElementById('owner_delegate_gestor_cpf_wrap');
        const gestorDelegateCpfInput = document.getElementById('gestor_delegate_cpf');
        if (gestorDelegateCpfWrap && gestorDelegateCpfInput) {
            gestorDelegateCpfWrap.classList.toggle('d-none', !isOwnerDelegate);
            gestorDelegateCpfInput.disabled = !isOwnerDelegate;
            gestorDelegateCpfInput.required = isOwnerDelegate;
            if (!isOwnerDelegate) {
                gestorDelegateCpfInput.value = '';
                gestorDelegateCpfInput.classList.remove('is-invalid');
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const registerFormElement = document.getElementById('registerForm');
        if (registerFormElement) {
            registerFormElement.querySelectorAll('[required]').forEach(field => {
                if (!field.dataset.originalRequired) {
                    field.dataset.originalRequired = 'true';
                }
            });
        }

        ownerAdditionalFieldsContainer = document.getElementById('owner_additional_fields');
        ownerAdditionalFieldsInputs = ownerAdditionalFieldsContainer ? Array.from(ownerAdditionalFieldsContainer.querySelectorAll('input, select, textarea')) : [];
        ownerAdditionalFieldsInputs.forEach(input => {
            if (input.required) {
                input.dataset.originalRequired = 'true';
            }
        });
        ownerHotelNotice = document.getElementById('hotel_not_operational_notice');
        ownerNextButton = document.getElementById('owner_next_button');

        gestorAdditionalFieldsContainer = document.getElementById('gestor_additional_fields');
        gestorAdditionalFieldsInputs = gestorAdditionalFieldsContainer ? Array.from(gestorAdditionalFieldsContainer.querySelectorAll('input, select, textarea')) : [];
        gestorAdditionalFieldsInputs.forEach(input => {
            if (input.required) {
                input.dataset.originalRequired = 'true';
            }
        });
        gestorHotelNotice = document.getElementById('gestor_hotel_not_operational_notice');
        gestorNextButton = document.getElementById('gestor_next_button');

        // Apply phone masks
        const phoneField = document.getElementById('phone');
        const whatsappField = document.getElementById('whatsapp');
        if (phoneField) applyPhoneMask(phoneField);
        if (whatsappField) applyPhoneMask(whatsappField);

        // Add event listeners to radio buttons
        const quotaRadios = document.querySelectorAll('input[name="has_quota"]');

        quotaRadios.forEach((radio, index) => {
            radio.addEventListener('change', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.quota-option-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selected class to current card
                const currentCard = this.closest('.quota-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }

                toggleQuotaSections(this.value);
            });
        });

        // Add click listeners to cards
        document.querySelectorAll('.quota-option-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });

        // Hotel operational selection
        const hotelRadios = document.querySelectorAll('input[name="hotel_operational"]');

        hotelRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selected class from all hotel cards
                document.querySelectorAll('.hotel-option-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selected class to current card
                const currentCard = this.closest('.hotel-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }

                // Executa a lógica de controle dos usos
                toggleOwnerFields(this.value);
            });
        });

        // Add click listeners to hotel cards
        document.querySelectorAll('.hotel-option-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });

        // Initialize hotel selection
        const selectedHotelRadio = document.querySelector('input[name="hotel_operational"]:checked');
        if (selectedHotelRadio) {
            const currentCard = selectedHotelRadio.closest('.hotel-option-card');
            if (currentCard) {
                currentCard.classList.add('selected');
            }
            // Executa a lógica de controle dos usos na inicialização
            toggleOwnerFields(selectedHotelRadio.value);
        }

        // Allowed uses selection
        const allowedUseCheckboxes = document.querySelectorAll('input[name="allowed_uses[]"]');

        allowedUseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const currentCard = this.closest('.use-option-card');
                if (currentCard) {
                    if (this.checked) {
                        currentCard.classList.add('selected');
                    } else {
                        currentCard.classList.remove('selected');
                    }
                }
            });
        });

        // Add click listeners to use option cards
        document.querySelectorAll('.use-option-card').forEach(card => {
            card.addEventListener('click', function() {
                if (!this.classList.contains('disabled')) {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });

        // Initialize allowed uses selection
        allowedUseCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const currentCard = checkbox.closest('.use-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }
            }
        });

        // Allowed uses selection for no quota section
        const noQuotaUseCheckboxes = document.querySelectorAll('#no_quota_section input[name="allowed_uses[]"]');

        noQuotaUseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const currentCard = this.closest('.use-option-card');
                if (currentCard) {
                    if (this.checked) {
                        currentCard.classList.add('selected');
                    } else {
                        currentCard.classList.remove('selected');
                    }
                }
            });
        });

        // Add click listeners to no quota use option cards
        document.querySelectorAll('#no_quota_section .use-option-card').forEach(card => {
            card.addEventListener('click', function() {
                if (!this.classList.contains('disabled')) {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });

        // Initialize no quota allowed uses selection
        noQuotaUseCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const currentCard = checkbox.closest('.use-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }
            }
        });

        // Initialize with current selection
        const selectedRadio = document.querySelector('input[name="has_quota"]:checked');
        if (selectedRadio) {
            // Add selected class to current card
            const currentCard = selectedRadio.closest('.quota-option-card');
            if (currentCard) {
                currentCard.classList.add('selected');
            }
            toggleQuotaSections(selectedRadio.value);
        } else {
            // Se nenhuma opção está selecionada, oculta todos os formulários
            toggleQuotaSections(null);
        }

        // Gestor hotel operational selection
        const gestorHotelRadios = document.querySelectorAll('input[name="gestor_hotel_operational"]');

        gestorHotelRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selected class from all gestor hotel cards
                document.querySelectorAll('#gestor_section .hotel-option-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selected class to current gestor hotel card
                const currentCard = this.closest('.hotel-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }

                // Toggle gestor fields based on hotel operational status
                toggleGestorFields(this.value);
            });
        });

        // Add click listeners to gestor hotel cards
        document.querySelectorAll('#gestor_section .hotel-option-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });

        // Initialize gestor hotel selection
        const selectedGestorRadio = document.querySelector('input[name="gestor_hotel_operational"]:checked');
        if (selectedGestorRadio) {
            const currentCard = selectedGestorRadio.closest('.hotel-option-card');
            if (currentCard) {
                currentCard.classList.add('selected');
            }
            toggleGestorFields(selectedGestorRadio.value);
        }

        // Gestor allowed uses selection
        const gestorAllowedUseCheckboxes = document.querySelectorAll('#gestor_section input[name="gestor_allowed_uses[]"]');

        gestorAllowedUseCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const currentCard = this.closest('.use-option-card');
                if (currentCard) {
                    if (this.checked) {
                        currentCard.classList.add('selected');
                    } else {
                        currentCard.classList.remove('selected');
                    }
                }
            });
        });

        // Add click listeners to gestor use option cards
        document.querySelectorAll('#gestor_section .use-option-card').forEach(card => {
            card.addEventListener('click', function() {
                if (!this.classList.contains('disabled')) {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });

        // Initialize gestor allowed uses selection
        gestorAllowedUseCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const currentCard = checkbox.closest('.use-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }
            }
        });

        const registerFormGestorVerify = document.getElementById('registerForm');
        if (registerFormGestorVerify) {
            registerFormGestorVerify.addEventListener('click', function(ev) {
                const verifyTrigger = ev.target && typeof ev.target.closest === 'function'
                    ? ev.target.closest('#gestor_verify_owner_btn')
                    : null;
                if (verifyTrigger && !verifyTrigger.disabled) {
                    ev.preventDefault();
                    if (typeof verifyDelegatedGestorOwner === 'function') {
                        verifyDelegatedGestorOwner();
                    }
                    return;
                }

                const downloadTrigger = ev.target && typeof ev.target.closest === 'function'
                    ? ev.target.closest('#gestor_owner_verify_doc_btn')
                    : null;
                if (downloadTrigger && !downloadTrigger.disabled) {
                    ev.preventDefault();
                    if (typeof downloadDelegatedGestorDocument === 'function') {
                        downloadDelegatedGestorDocument();
                    }
                }
            });
        }
    });

    // Toggle gestor fields based on hotel operational status
    function toggleGestorFields(value) {
        const quotaStatusSection = document.getElementById('gestor_quota_status_section');
        const quotaStatusSelect = document.getElementById('gestor_quota_status');
        const paymentDeadlineInput = document.getElementById('gestor_quota_payment_deadline');

        // Gestor allowed uses
        const gestorUseRent = document.getElementById('gestor_use_rent');
        const gestorUseExchange = document.getElementById('gestor_use_exchange');
        const gestorUseSell = document.getElementById('gestor_use_sell');
        const gestorUseBuy = document.getElementById('gestor_use_buy');

        if (value === '1') {
            if (gestorAdditionalFieldsContainer) {
                gestorAdditionalFieldsContainer.classList.remove('d-none');
            }
            if (gestorHotelNotice) {
                gestorHotelNotice.classList.add('d-none');
                gestorHotelNotice.style.display = 'none';
            }
            if (gestorNextButton) {
                gestorNextButton.disabled = false;
            }
            gestorAdditionalFieldsInputs.forEach(input => {
                if (input.dataset.gestorHotelDisabled === 'true') {
                    input.disabled = false;
                    delete input.dataset.gestorHotelDisabled;
                }
                if (input.dataset.originalRequired === 'true') {
                    input.required = true;
                }
            });

            // Hotel operacional - Mostra campos e habilita todos os usos
            if (quotaStatusSection) quotaStatusSection.style.display = 'block';
            if (quotaStatusSelect) quotaStatusSelect.required = true;
            if (paymentDeadlineInput) paymentDeadlineInput.required = false;

            // Habilita todos os usos permitidos para gestores
            [gestorUseRent, gestorUseExchange, gestorUseSell, gestorUseBuy].forEach(checkbox => {
                if (checkbox) {
                    checkbox.disabled = false;
                    checkbox.required = true; // Restaura obrigatoriedade quando hotel está em funcionamento
                    const card = checkbox.closest('.use-option-card');
                    if (card) {
                        card.classList.remove('disabled');
                    }
                }
            });

            // Aplica as regras baseadas no status da cota
            toggleGestorAllowedUses();
        } else {
            if (gestorAdditionalFieldsContainer) {
                gestorAdditionalFieldsContainer.classList.add('d-none');
            }
            if (gestorHotelNotice) {
                gestorHotelNotice.classList.remove('d-none');
                gestorHotelNotice.style.display = 'block';
            }
            if (gestorNextButton) {
                gestorNextButton.disabled = true;
            }

            gestorAdditionalFieldsInputs.forEach(input => {
                if (input.dataset.originalRequired === 'true') {
                    input.required = false;
                }
                if (input.type !== 'file') {
                    if (input.disabled === false) {
                        input.dataset.gestorHotelDisabled = 'true';
                    }
                    input.disabled = true;
                }
            });

            // Hotel não operacional - Oculta campos e bloqueia TODOS os usos
            if (quotaStatusSection) quotaStatusSection.style.display = 'none';
            if (quotaStatusSelect) {
                quotaStatusSelect.required = false;
                quotaStatusSelect.value = '';
            }
            if (paymentDeadlineInput) {
                paymentDeadlineInput.required = false;
                paymentDeadlineInput.value = '';
            }

            // Desabilita TODOS os usos permitidos quando hotel não está em funcionamento
            [gestorUseRent, gestorUseExchange, gestorUseSell, gestorUseBuy].forEach(checkbox => {
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                    checkbox.required = false; // Remove obrigatoriedade
                    const card = checkbox.closest('.use-option-card');
                    if (card) {
                        card.classList.add('disabled');
                        card.classList.remove('selected');
                    }
                }
            });
        }
    }

    // Função para controlar os usos permitidos baseado na situação da cota (gestor: só Alugar e Trocar)
    function toggleGestorAllowedUses() {
        const quotaStatus = document.getElementById('gestor_quota_status');
        const gestorUseRent = document.getElementById('gestor_use_rent');
        const gestorUseExchange = document.getElementById('gestor_use_exchange');
        const gestorUseSell = document.getElementById('gestor_use_sell');
        const gestorUseBuy = document.getElementById('gestor_use_buy');
        const gestorSection = document.getElementById('gestor_section');

        // Gestor: Vender e Comprar sempre desativados (não têm name para não serem enviados)
        if (gestorUseSell) {
            gestorUseSell.checked = false;
            gestorUseSell.disabled = true;
            gestorUseSell.removeAttribute('name');
            const sellCard = gestorUseSell.closest('.use-option-card');
            if (sellCard) {
                sellCard.classList.add('disabled');
                sellCard.classList.remove('selected');
            }
        }
        if (gestorUseBuy) {
            gestorUseBuy.checked = false;
            gestorUseBuy.disabled = true;
            gestorUseBuy.removeAttribute('name');
            const buyCard = gestorUseBuy.closest('.use-option-card');
            if (buyCard) {
                buyCard.classList.add('disabled');
                buyCard.classList.remove('selected');
            }
        }

        if (!quotaStatus) return;

        const hasQuotaSelected = document.querySelector('input[name="has_quota"]:checked');
        const isGestorSelected = hasQuotaSelected && (hasQuotaSelected.value === '2' || hasQuotaSelected.value === '3');
        const isGestorSectionVisible = gestorSection && !gestorSection.classList.contains('d-none');
        const selectedStatus = quotaStatus.value;

        // Apenas Alugar e Trocar são habilitados e podem ser obrigatórios para gestor
        const enabledForGestor = [gestorUseRent, gestorUseExchange];
        enabledForGestor.forEach(checkbox => {
            if (!checkbox) return;
            if (isGestorSectionVisible && (selectedStatus === 'unpaid' || selectedStatus === 'paid')) {
                checkbox.disabled = false;
                checkbox.required = true;
                if (!checkbox.hasAttribute('name')) {
                    checkbox.setAttribute('name', 'gestor_allowed_uses[]');
                }
                const card = checkbox.closest('.use-option-card');
                if (card) {
                    card.classList.remove('disabled');
                }
            } else {
                checkbox.removeAttribute('required');
                checkbox.required = false;
            }
        });
    }

    // Função para remover required de campos ocultos antes de enviar
    function removeRequiredFromHiddenFields() {
        // PRIMEIRO: Remover required de TODOS os checkboxes gestor_allowed_uses[] se a seção gestor estiver oculta
        const gestorSection = document.getElementById('gestor_section');
        const isGestorVisible = gestorSection && !gestorSection.classList.contains('d-none');
        const hasQuotaValue = document.querySelector('input[name="has_quota"]:checked');
        const isGestorSelected = hasQuotaValue && (hasQuotaValue.value === '2' || hasQuotaValue.value === '3');
        
        // Se a seção gestor estiver oculta OU gestor não está selecionado, remover required de TODOS os checkboxes gestor
        if (!isGestorVisible || !isGestorSelected) {
            // Buscar TODOS os checkboxes gestor_allowed_uses[] no documento inteiro
            const allGestorCheckboxes = document.querySelectorAll('input[name="gestor_allowed_uses[]"]');
            allGestorCheckboxes.forEach(checkbox => {
                // Múltiplas tentativas para garantir remoção
                checkbox.removeAttribute('required');
                checkbox.required = false;
                if (checkbox.hasAttribute('required')) {
                    checkbox.removeAttribute('required');
                }
                // Também verificar se está dentro de elemento oculto
                let parent = checkbox.parentElement;
                while (parent && parent !== document.body) {
                    if (parent.classList.contains('d-none') || 
                        window.getComputedStyle(parent).display === 'none' ||
                        parent.style.display === 'none') {
                        checkbox.removeAttribute('required');
                        checkbox.required = false;
                        break;
                    }
                    parent = parent.parentElement;
                }
            });
        }

        // Remover required de todos os campos dentro de seções ocultas
        const hiddenSections = document.querySelectorAll('.d-none');
        hiddenSections.forEach(section => {
            const requiredFields = section.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.removeAttribute('required');
                field.required = false;
            });
        });

        // Remover required de campos do proprietário se a seção estiver oculta
        const quotaOwnerSection = document.getElementById('quota_owner_section');
        if (quotaOwnerSection && quotaOwnerSection.classList.contains('d-none')) {
            const ownerRequiredFields = quotaOwnerSection.querySelectorAll('[required]');
            ownerRequiredFields.forEach(field => {
                field.removeAttribute('required');
                field.required = false;
            });
        }

        // Remover required de campos sem cota se a seção estiver oculta
        const noQuotaSection = document.getElementById('no_quota_section');
        if (noQuotaSection && noQuotaSection.classList.contains('d-none')) {
            const noQuotaRequiredFields = noQuotaSection.querySelectorAll('[required]');
            noQuotaRequiredFields.forEach(field => {
                field.removeAttribute('required');
                field.required = false;
            });
        }

        // Garantir que campos dentro de elementos com display:none também sejam limpos
        const allHiddenElements = document.querySelectorAll('[style*="display: none"], [style*="display:none"]');
        allHiddenElements.forEach(element => {
            const requiredFields = element.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.removeAttribute('required');
                field.required = false;
            });
        });
    }

    // Event listener para o campo Situação da Cota
    const gestorQuotaStatus = document.getElementById('gestor_quota_status');
    if (gestorQuotaStatus) {
        gestorQuotaStatus.addEventListener('change', toggleGestorAllowedUses);

        // Executa na inicialização para manter o estado correto
        toggleGestorAllowedUses();
    }

    // Função para controlar os usos permitidos baseado no status do hotel operacional (opção "Sim")
    function toggleOwnerFields(value) {
        const useRent = document.getElementById('use_rent');
        const useExchange = document.getElementById('use_exchange');
        const useSell = document.getElementById('use_sell');
        const useBuy = document.getElementById('use_buy');

        if (value === '1') {
            if (ownerAdditionalFieldsContainer) {
                ownerAdditionalFieldsContainer.classList.remove('d-none');
            }
            if (ownerHotelNotice) {
                ownerHotelNotice.classList.add('d-none');
                ownerHotelNotice.style.display = 'none';
            }
            if (ownerNextButton) {
                ownerNextButton.disabled = false;
            }
            ownerAdditionalFieldsInputs.forEach(input => {
                if (input.dataset.ownerHotelDisabled === 'true') {
                    input.disabled = false;
                    delete input.dataset.ownerHotelDisabled;
                }
                if (input.dataset.originalRequired === 'true') {
                    input.required = true;
                }
            });

            // Hotel operacional - Habilita todos os usos
            [useRent, useExchange, useSell, useBuy].forEach(checkbox => {
                if (checkbox) {
                    checkbox.disabled = false;
                    checkbox.required = true; // Restaura obrigatoriedade
                    const card = checkbox.closest('.use-option-card');
                    if (card) {
                        card.classList.remove('disabled');
                    }
                }
            });

            // Aplica as regras baseadas no status da cota
            toggleAllowedUses();
            // Do not show the "Termo de Intenção de Troca de Titularidade" here.
            // Keep the section hidden and non-mandatory.
            const intentionSection = document.getElementById('intention_transfer_section');
            if (intentionSection) {
                intentionSection.classList.add('d-none');
                const input = document.getElementById('intention_notary_address');
                if (input) {
                    input.required = false;
                }
            }
        } else {
            if (ownerAdditionalFieldsContainer) {
                ownerAdditionalFieldsContainer.classList.add('d-none');
            }
            if (ownerHotelNotice) {
                ownerHotelNotice.classList.remove('d-none');
                ownerHotelNotice.style.display = 'block';
            }
            if (ownerNextButton) {
                ownerNextButton.disabled = true;
            }

            ownerAdditionalFieldsInputs.forEach(input => {
                if (input.dataset.originalRequired === 'true') {
                    input.required = false;
                }
                if (input.type !== 'file') {
                    if (input.disabled === false) {
                        input.dataset.ownerHotelDisabled = 'true';
                    }
                    input.disabled = true;
                }
            });

            // Hotel não operacional - Desabilita TODOS os usos
            [useRent, useExchange, useSell, useBuy].forEach(checkbox => {
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.disabled = true;
                    checkbox.required = false; // Remove obrigatoriedade
                    const card = checkbox.closest('.use-option-card');
                    if (card) {
                        card.classList.add('disabled');
                        card.classList.remove('selected');
                    }
                }
            });
            // hide intention transfer section
            const intentionSection = document.getElementById('intention_transfer_section');
            if (intentionSection) intentionSection.classList.add('d-none');
        }
    }

    // Função para controlar os usos permitidos baseado na situação da cota (opção "Sim")
    function toggleAllowedUses() {
        const quotaStatus = document.getElementById('quota_status');
        const useRent = document.getElementById('use_rent');
        const useExchange = document.getElementById('use_exchange');
        const useSell = document.getElementById('use_sell');
        const useBuy = document.getElementById('use_buy');

        if (!quotaStatus) return;

        const selectedStatus = quotaStatus.value;

        if (selectedStatus === 'unpaid') {
            // Não Quitada - Habilita TODOS os usos e torna obrigatórios
            [useRent, useExchange, useSell, useBuy].forEach(checkbox => {
                if (checkbox) {
                    checkbox.disabled = false;
                    checkbox.required = true; // Torna obrigatório
                    const card = checkbox.closest('.use-option-card');
                    if (card) {
                        card.classList.remove('disabled');
                    }
                }
            });
        } else if (selectedStatus === 'paid') {
            // Quitada - Habilita todas as opções
            [useRent, useExchange, useSell, useBuy].forEach(checkbox => {
                if (checkbox) {
                    checkbox.disabled = false;
                    checkbox.required = true; // Mantém obrigatório
                    const card = checkbox.closest('.use-option-card');
                    if (card) {
                        card.classList.remove('disabled');
                    }
                }
            });
        }
    }

    // Event listener para o campo Status da Cota (opção "Sim")
    const quotaStatus = document.getElementById('quota_status');
    if (quotaStatus) {
        quotaStatus.addEventListener('change', toggleAllowedUses);

        // Executa na inicialização para manter o estado correto
        toggleAllowedUses();
    }



    // Enable submit button when both checkboxes are accepted
    function updateSubmitButtonState() {
        const acceptTermsField = document.getElementById('accept_terms');
        const acceptPromotionalField = document.getElementById('accept_promotional_periods');
        const finalSubmit = document.getElementById('finalSubmit');
        
        if (acceptTermsField && acceptPromotionalField && finalSubmit) {
            // Habilitar o botão apenas se ambos os checkboxes estiverem marcados
            finalSubmit.disabled = !(acceptTermsField.checked && acceptPromotionalField.checked);
        }
    }

    // Inicializar estado do botão
    const acceptTermsField = document.getElementById('accept_terms');
    const acceptPromotionalField = document.getElementById('accept_promotional_periods');
    
    if (acceptTermsField && acceptPromotionalField) {
        // Desabilitar o botão inicialmente
        updateSubmitButtonState();

        // Adicionar listeners para ambos os checkboxes
        acceptTermsField.addEventListener('change', updateSubmitButtonState);
        acceptPromotionalField.addEventListener('change', updateSubmitButtonState);
    }

    // Autocomplete de Hotéis
    const hotelInput = document.getElementById('hotel_autocomplete');
    const hotelIdInput = document.getElementById('hotel_id');
    const suggestionsBox = document.getElementById('hotel_suggestions');
    const hotelOperationalSelect = document.getElementById('hotel_operational');

    let hotelSearchTimeout = null;
    if (hotelInput) {
        hotelInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(hotelSearchTimeout);
            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                suggestionsBox.innerHTML = '';
                return;
            }
            hotelSearchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/hotels/search?query=${encodeURIComponent(query)}`);
                    const json = await res.json();
                    const items = json.data || [];
                    if (items.length === 0) {
                        suggestionsBox.style.display = 'none';
                        suggestionsBox.innerHTML = '';
                        return;
                    }
                    suggestionsBox.innerHTML = '';
                    items.forEach(item => {
                        const a = document.createElement('a');
                        a.href = '#';
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = item.label;
                        a.addEventListener('click', (e) => {
                            e.preventDefault();
                            hotelInput.value = item.label;
                            hotelIdInput.value = item.id;
                            suggestionsBox.style.display = 'none';
                            // Sincroniza "Hotel em funcionamento"
                            if (hotelOperationalSelect) {
                                hotelOperationalSelect.value = item.is_functioning ? '1' : '0';
                                hotelOperationalSelect.dispatchEvent(new Event('change'));
                            }
                            // Carrega info oficial
                            loadRegHotelInfo(item.id);
                        });
                        suggestionsBox.appendChild(a);
                    });
                    suggestionsBox.style.display = 'block';
                } catch (err) {
                    suggestionsBox.style.display = 'none';
                }
            }, 250);
        });
    }

    // Bloqueia TODOS os usos se hotel_operational = 0
    if (hotelOperationalSelect) {
        hotelOperationalSelect.addEventListener('change', function() {
            const useRent = document.getElementById('use_rent');
            const useExchange = document.getElementById('use_exchange');
            const useSell = document.getElementById('use_sell');
            const useBuy = document.getElementById('use_buy');

            if (this.value === '0') {
                // Hotel não operacional - Desabilita TODOS os usos
                [useRent, useExchange, useSell, useBuy].forEach(checkbox => {
                    if (checkbox) {
                        checkbox.checked = false;
                        checkbox.disabled = true;
                        checkbox.required = false; // Remove obrigatoriedade
                        const card = checkbox.closest('.use-option-card');
                        if (card) {
                            card.classList.add('disabled');
                            card.classList.remove('selected');
                        }
                    }
                });
            } else if (this.value === '1') {
                // Hotel operacional - Habilita todos os usos
                [useRent, useExchange, useSell, useBuy].forEach(checkbox => {
                    if (checkbox) {
                        checkbox.disabled = false;
                        checkbox.required = true; // Restaura obrigatoriedade
                        const card = checkbox.closest('.use-option-card');
                        if (card) {
                            card.classList.remove('disabled');
                        }
                    }
                });

                // Aplica as regras baseadas no status da cota
                toggleAllowedUses();
            }
        });
    }

    async function loadRegHotelInfo(hotelId) {
        try {
            const res = await fetch(`/api/hotels/${hotelId}`);
            const json = await res.json();
            const d = json.data;
            const box = document.getElementById('reg_hotel_official_info');
            if (!box) return;
            box.innerHTML = '';
            if (!d) {
                box.textContent = 'Informações indisponíveis.';
                return;
            }
            const desc = document.createElement('p');
            desc.textContent = d.description || 'Sem descrição disponível.';
            const website = document.createElement('a');
            if (d.website) {
                website.href = d.website;
                website.textContent = d.website;
                website.target = '_blank';
            }
            if (Array.isArray(d.amenities) && d.amenities.length) {
                const chips = document.createElement('div');
                chips.className = 'mt-2';
                d.amenities.forEach(am => {
                    const span = document.createElement('span');
                    span.className = 'badge bg-success me-1 mb-1';
                    span.textContent = am;
                    chips.appendChild(span);
                });
                box.appendChild(chips);
            }
            box.appendChild(desc);
            if (d.website) {
                box.appendChild(website);
            }
        } catch (e) {
            const box = document.getElementById('reg_hotel_official_info');
            if (box) box.textContent = 'Erro ao carregar informações do hotel.';
        }
    }

    // Autocomplete de Hotéis para Gestores
    const gestorHotelInput = document.getElementById('gestor_hotel_search');
    const gestorHotelIdInput = document.getElementById('gestor_hotel_id');
    const gestorSuggestionsBox = document.getElementById('gestor_hotel_suggestions');

    let gestorHotelSearchTimeout = null;
    if (gestorHotelInput) {
        gestorHotelInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(gestorHotelSearchTimeout);
            if (query.length < 2) {
                gestorSuggestionsBox.style.display = 'none';
                gestorSuggestionsBox.innerHTML = '';
                return;
            }
            gestorHotelSearchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/hotels/search?query=${encodeURIComponent(query)}`);
                    const json = await res.json();
                    const items = json.data || [];
                    if (items.length === 0) {
                        gestorSuggestionsBox.style.display = 'none';
                        gestorSuggestionsBox.innerHTML = '';
                        return;
                    }
                    gestorSuggestionsBox.innerHTML = '';
                    items.forEach(item => {
                        const a = document.createElement('a');
                        a.href = '#';
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = item.label;
                        a.addEventListener('click', (e) => {
                            e.preventDefault();
                            gestorHotelInput.value = item.label;
                            gestorHotelIdInput.value = item.id;
                            gestorSuggestionsBox.style.display = 'none';
                            // Carrega info oficial do hotel para gestor
                            loadGestorHotelInfo(item.id);
                        });
                        gestorSuggestionsBox.appendChild(a);
                    });
                    gestorSuggestionsBox.style.display = 'block';
                } catch (err) {
                    gestorSuggestionsBox.style.display = 'none';
                }
            }, 250);
        });
    }

    async function loadGestorHotelInfo(hotelId) {
        try {
            const res = await fetch(`/api/hotels/${hotelId}`);
            const json = await res.json();
            const d = json.data;
            const box = document.querySelector('#gestor_section .card-body p');
            if (!box) return;

            if (!d) {
                box.textContent = 'Informações indisponíveis.';
                return;
            }

            let content = '';
            if (d.description) {
                content += `<p class="mb-2">${d.description}</p>`;
            }
            if (d.website) {
                content += `<p class="mb-0"><strong>Site:</strong> <a href="${d.website}" target="_blank">${d.website}</a></p>`;
            }
            if (Array.isArray(d.amenities) && d.amenities.length) {
                content += '<div class="mt-2">';
                d.amenities.forEach(am => {
                    content += `<span class="badge bg-success me-1 mb-1">${am}</span>`;
                });
                content += '</div>';
            }

            box.innerHTML = content || 'Selecione um hotel para ver a descrição e site.';
        } catch (e) {
            const box = document.querySelector('#gestor_section .card-body p');
            if (box) box.textContent = 'Erro ao carregar informações do hotel.';
        }
    }

    // File preview functionality
    function previewFile(input, previewId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    }

    // Enhanced upload UX: drag/drop, preview, clear
    function setupUpload(fieldId, previewId, infoId, dropZoneId, clearBtnId) {
        const input = document.getElementById(fieldId);
        const preview = document.getElementById(previewId);
        const info = document.getElementById(infoId);
        const dropZone = document.getElementById(dropZoneId);
        const clearBtn = document.getElementById(clearBtnId);

        if (!input || !dropZone) return;

        function updateInfo(file) {
            if (!file) {
                info.textContent = 'Nenhum arquivo selecionado';
                preview.style.display = 'none';
                preview.src = '';
                return;
            }
            const sizeKb = (file.size / 1024).toFixed(0);
            info.textContent = `${file.name} — ${sizeKb} KB`;
        }

        // click on drop zone opens file selector
        dropZone.addEventListener('click', () => input.click());

        // handle file selection
        input.addEventListener('change', function() {
            const f = this.files[0];
            updateInfo(f);
            previewFile(this, previewId);
            if (typeof syncStep3DocumentsVisualState === 'function') {
                syncStep3DocumentsVisualState();
            }
        });

        // drag & drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', function(e) {
            this.classList.remove('dragover');
        });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                input.files = dt.files;
                const evt = new Event('change', { bubbles: true });
                input.dispatchEvent(evt);
            }
        });

        // clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                input.value = '';
                updateInfo(null);
                if (typeof syncStep3DocumentsVisualState === 'function') {
                    syncStep3DocumentsVisualState();
                }
            });
        }
    }

    // Initialize upload widgets
    setupUpload('user_photo', 'user_photo_preview', 'user_photo_info', 'user_photo_drop', 'clear_user_photo');
    setupUpload('document_photo', 'document_photo_preview', 'document_photo_info', 'document_photo_drop', 'clear_document_photo');
    document.getElementById('document_type')?.addEventListener('change', function() {
        if (typeof syncStep3DocumentsVisualState === 'function') {
            syncStep3DocumentsVisualState();
        }
    });

    // Profile card selection (delegação para funcionar mesmo com step oculto no carregamento)
    function syncProfileCardSelection() {
        const profileCards = document.querySelectorAll('.profile-card-horizontal');
        const checkedRadio = document.querySelector('input[name="profile_type"]:checked');
        profileCards.forEach(c => c.classList.remove('selected'));
        if (checkedRadio) {
            const card = checkedRadio.closest('.profile-card-horizontal');
            if (card) card.classList.add('selected');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Delegação: captura clique em qualquer lugar dentro de um card de perfil
        document.addEventListener('click', function(e) {
            const card = e.target.closest('.profile-card-horizontal');
            if (!card) return;
            const radio = card.querySelector('input[name="profile_type"]');
            if (radio) {
                radio.checked = true;
                syncProfileCardSelection();
            }
        });

        // Sincronizar quando o radio mudar (ex.: clique no label "Escolher")
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name="profile_type"]')) {
                syncProfileCardSelection();
            }
        });

        // Estado inicial e sempre que o step 5 for exibido
        syncProfileCardSelection();
    });

    // CEP mask and validation
    document.getElementById('cep').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;

        // Validate CEP length
        validateFieldLength('cep', value, 8, 'CEP deve ter 8 dígitos');
    });

    // Número da residência: apenas dígitos
    document.getElementById('house_number')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '');
    });

    // CEP automatic search
    document.getElementById('cep').addEventListener('blur', function(e) {
        const cep = e.target.value.replace(/\D/g, '');

        // Validate CEP length before proceeding
        if (cep.length > 0 && cep.length < 8) {
            validateFieldLength('cep', cep, 8, 'CEP deve ter 8 dígitos');
            return;
        }

        if (cep.length === 8) {
            // Show loading
            const streetField = document.getElementById('street');
            const neighborhoodField = document.getElementById('neighborhood');
            const cityField = document.getElementById('city');
            const stateField = document.getElementById('state');
            const houseNumberField = document.getElementById('house_number');

            // Disable fields while searching
            streetField.disabled = true;
            neighborhoodField.disabled = true;
            cityField.disabled = true;
            stateField.disabled = true;

            // Add loading text
            streetField.placeholder = 'Buscando...';
            neighborhoodField.placeholder = 'Buscando...';
            cityField.placeholder = 'Buscando...';
            stateField.placeholder = 'Buscando...';

            // Make API request to ViaCEP
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        // Preenche os campos automaticamente
                        streetField.value = data.logradouro || '';
                        neighborhoodField.value = data.bairro || '';
                        cityField.value = data.localidade || '';
                        stateField.value = data.uf || '';

                        // Focus on house number field
                        houseNumberField.focus();
                    } else {
                        // CEP not found - mostrar erro visual em vez de alert
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-warning mt-2';
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>CEP não encontrado. Por favor, preencha os dados manualmente.';

                        // Remover erro anterior se existir
                        const existingError = document.querySelector('.cep-error-message');
                        if (existingError) {
                            existingError.remove();
                        }

                        errorDiv.classList.add('cep-error-message');
                        document.querySelector('#cep').parentNode.appendChild(errorDiv);
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    // Mostrar erro visual em vez de alert
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-2';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Erro ao buscar CEP. Por favor, preencha os dados manualmente.';

                    // Remover erro anterior se existir
                    const existingError = document.querySelector('.cep-error-message');
                    if (existingError) {
                        existingError.remove();
                    }

                    errorDiv.classList.add('cep-error-message');
                    document.querySelector('#cep').parentNode.appendChild(errorDiv);
                })
                .finally(() => {
                    // Re-enable fields
                    streetField.disabled = false;
                    neighborhoodField.disabled = false;
                    cityField.disabled = false;
                    stateField.disabled = false;

                    // Reset placeholders
                    streetField.placeholder = 'Nome da rua';
                    neighborhoodField.placeholder = 'Nome do bairro';
                    cityField.placeholder = 'Nome da cidade';
                    stateField.placeholder = 'Nome do estado';
                });
        }
    });

    // ========================================================================
    // LÓGICA DE FRACIONAMENTO BASEADA NO PERFIL
    // ========================================================================

    // Detectar mudança de perfil e preparar step de fracionamento
    document.querySelectorAll('input[name="profile_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const profileType = this.value;

            // Ocultar todas as seções de fracionamento
            document.getElementById('fraction_curioso').classList.add('d-none');
            document.getElementById('fraction_inteligente').classList.add('d-none');
            document.getElementById('fraction_sabio').classList.add('d-none');

            // Mostrar a seção correspondente ao perfil
            if (profileType === 'curioso') {
                document.getElementById('fraction_curioso').classList.remove('d-none');
            } else if (profileType === 'inteligente') {
                document.getElementById('fraction_inteligente').classList.remove('d-none');
                toggleFractionCardsWhenOnlyVender('fraction_inteligente');
                // Marcar visualmente o card pré-selecionado (Sem fracionar)
                setTimeout(() => {
                    const checkedRadio = document.querySelector('#fraction_inteligente input[name="fraction_type"]:checked');
                    if (checkedRadio) {
                        const card = checkedRadio.closest('.fraction-card');
                        if (card) {
                            document.querySelectorAll('#fraction_inteligente .fraction-card').forEach(c => c.classList.remove('selected'));
                            card.classList.add('selected');
                        }
                    }
                }, 100);
            } else if (profileType === 'sabio') {
                document.getElementById('fraction_sabio').classList.remove('d-none');
                toggleFractionCardsWhenOnlyVender('fraction_sabio');
            }
        });
    });

    // Se só Vender está marcado nos usos permitidos, mostrar apenas "Sem fracionar" no step 6 (Inteligente/Sábio)
    function toggleFractionCardsWhenOnlyVender(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const onlyVender = onlyVenderAllowed();
        container.querySelectorAll('.fraction-card').forEach(card => {
            const radio = card.querySelector('input[name="fraction_type"]');
            if (!radio) return;
            if (onlyVender && radio.value !== '7') {
                card.style.display = 'none';
            } else {
                card.style.display = '';
            }
        });
        if (onlyVender) {
            const radio7 = container.querySelector('input[name="fraction_type"][value="7"]');
            if (radio7) {
                radio7.checked = true;
            }
        }
    }

    // Inicializar seleção visual dos cards de fracionamento pré-selecionados
    document.querySelectorAll('input[name="fraction_type"]:checked').forEach(radio => {
        const card = radio.closest('.fraction-card');
        if (card) {
            card.classList.add('selected');
        }
    });

    // Adicionar interatividade aos cards de fracionamento
    document.querySelectorAll('.fraction-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remover seleção de todos os cards
            document.querySelectorAll('.fraction-card').forEach(c => c.classList.remove('selected'));

            // Adicionar seleção ao card clicado
            this.classList.add('selected');

            // Marcar o radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Atualizar seleção visual quando o radio button mudar
    document.querySelectorAll('input[name="fraction_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Remover seleção de todos os cards
            document.querySelectorAll('.fraction-card').forEach(c => c.classList.remove('selected'));

            // Adicionar seleção ao card do radio selecionado
            const card = this.closest('.fraction-card');
            if (card) {
                card.classList.add('selected');
            }
        });
    });

    // Adicionar estilo ao label do fracionamento
    document.querySelectorAll('.fraction-label').forEach(label => {
        label.style.cursor = 'pointer';
    });

    // Form submission - INTERCEPTAR ANTES DA VALIDAÇÃO NATIVA
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
        console.log('Current step:', currentStep);

        // Atualizar campo oculto JSON de fracionamento imediatamente antes de enviar
        if (typeof updateFractionDebugPanel === 'function') {
            updateFractionDebugPanel();
        }
        
        // PRIMEIRO: Remover required de campos ocultos ANTES de qualquer validação
        // Isso evita que o navegador tente validar campos ocultos

        // PRIMEIRO: Remover required de TODOS os campos gestor_allowed_uses[] SEMPRE
        // Isso garante que não haverá problemas mesmo se houver duplicatas ou campos ocultos
        const allGestorCheckboxes = document.querySelectorAll('input[name="gestor_allowed_uses[]"]');
        const gestorSection = document.getElementById('gestor_section');
        const isGestorVisible = gestorSection && !gestorSection.classList.contains('d-none');
        const hasQuotaValue = document.querySelector('input[name="has_quota"]:checked');
        const isGestorSelected = hasQuotaValue && (hasQuotaValue.value === '2' || hasQuotaValue.value === '3');

        // REMOVER required de TODOS os checkboxes gestor se não estiver selecionado ou visível
        if (!isGestorSelected || !isGestorVisible) {
            allGestorCheckboxes.forEach(checkbox => {
                checkbox.removeAttribute('required');
                checkbox.required = false;
                // Forçar remoção múltiplas vezes
                if (checkbox.hasAttribute('required')) {
                    checkbox.removeAttribute('required');
                }
                // Também usar setAttribute com false
                checkbox.setAttribute('required', false);
                checkbox.removeAttribute('required');
            });
        }

        // Remover required de campos ocultos
        removeRequiredFromHiddenFields();

        // ÚLTIMA VERIFICAÇÃO: Remover required de TODOS os campos gestor_allowed_uses[] que estão ocultos
        document.querySelectorAll('input[name="gestor_allowed_uses[]"]').forEach(checkbox => {
            let isVisible = true;
            let element = checkbox;
            
            // Verificar se o checkbox ou algum parent está oculto
            while (element && element !== document.body) {
                const style = window.getComputedStyle(element);
                if (element.classList.contains('d-none') || 
                    style.display === 'none' ||
                    style.visibility === 'hidden' ||
                    element.style.display === 'none') {
                    isVisible = false;
                    break;
                }
                element = element.parentElement;
            }
            
            // Se não está visível, remover required FORÇADAMENTE
            if (!isVisible) {
                checkbox.removeAttribute('required');
                checkbox.required = false;
                checkbox.setAttribute('required', false);
                checkbox.removeAttribute('required');
            }
        });

        // Validar se está no Step 7 antes de submeter
        if (currentStep !== 7) {
            console.log('Not on step 7, preventing submit. Current step:', currentStep);
            e.preventDefault();
            return false;
        }

        // Validar termos aceitos
        const acceptTerms = document.getElementById('accept_terms');
        if (!acceptTerms || !acceptTerms.checked) {
            console.log('Terms not accepted, preventing submit');
            e.preventDefault();

            // Mostrar erro
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mt-3';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Você deve aceitar os termos e condições para concluir o cadastro';

            const existingError = document.querySelector('.terms-error-message');
            if (existingError) {
                existingError.remove();
            }

            errorDiv.classList.add('terms-error-message');
            const step7 = document.getElementById('step7');
            if (step7) {
                step7.insertBefore(errorDiv, step7.firstChild);
                errorDiv.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            return false;
        }

        // Validate passwords before submission
        if (!validateUserName()) {
            console.log('Name validation failed, preventing submit');
            e.preventDefault();
            return false;
        }

        if (!validatePasswordMatch()) {
            console.log('Password validation failed, preventing submit');
            e.preventDefault();
            return false;
        }

        // Validate CPF, phone and CEP before submission
        if (!areAllFieldsValid()) {
            console.log('Field validation failed, preventing submit');
            e.preventDefault();
            return false;
        }

        // Validar campos de quartos se existirem (não bloquear se não existirem)
        const gestorRoomsConfig = document.getElementById('gestor-rooms-configuration');
        if (gestorRoomsConfig && !gestorRoomsConfig.classList.contains('d-none')) {
            const gestorRoomFields = gestorRoomsConfig.querySelectorAll('select[required], input[required]');
            if (gestorRoomFields.length > 0) {
                for (let field of gestorRoomFields) {
                    if (!field.value || field.value.trim() === '') {
                        console.log('Gestor room field not filled:', field.name);
                        e.preventDefault();
                        field.classList.add('is-invalid');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3';
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, preencha todos os campos de configuração dos quartos';
                        gestorRoomsConfig.insertBefore(errorDiv, gestorRoomsConfig.firstChild);
                        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                }
            }
        }

        // Validar fracionamento se necessário (apenas se o usuário tiver cota)
        const hasQuota = document.querySelector('input[name="has_quota"]:checked');
        if (hasQuota && (hasQuota.value === '1' || hasQuota.value === '2' || hasQuota.value === '3')) {
            const selectedProfile = document.querySelector('input[name="profile_type"]:checked');
            if (selectedProfile && selectedProfile.value !== 'curioso') {
                // Verificar fracionamento por semana (novo sistema)
                const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
                let allWeeksHaveFraction = true;
                let missingWeeks = [];

                authorizedWeeks.forEach(weekNumber => {
                    const fractionSelected = document.querySelector(`input[name="fraction_type_week_${weekNumber}"]:checked`);
                    if (!fractionSelected) {
                        allWeeksHaveFraction = false;
                        missingWeeks.push(weekNumber);
                    }
                });

                // Backward compatibility: verificar também o campo antigo
                const oldFractionSelected = document.querySelector('input[name="fraction_type"]:checked');
                if (!allWeeksHaveFraction && !oldFractionSelected) {
                    console.log('Fraction not selected, preventing submit');
                    e.preventDefault();

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    if (missingWeeks.length > 0) {
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento para ${missingWeeks.length > 1 ? 'as semanas' : 'a semana'} ${missingWeeks.join(', ')}`;
                    } else {
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Por favor, selecione uma opção de fracionamento';
                    }

                    const existingError = document.querySelector('.final-fraction-error-message');
                    if (existingError) {
                        existingError.remove();
                    }

                    errorDiv.classList.add('final-fraction-error-message');
                    const step7 = document.getElementById('step7');
                    if (step7) {
                        step7.insertBefore(errorDiv, step7.firstChild);
                        errorDiv.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }

                    return false;
                }
            }
        }

        console.log('All validations passed, submitting form');

        // If all validations pass, proceed with submission
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando conta...';
            submitBtn.disabled = true;
        }

        // Permitir submissão natural do formulário
        // Como já removemos os campos required e temos novalidate, podemos submeter normalmente
        // Isso garante que o token CSRF seja incluído corretamente
        // O formulário será submetido naturalmente se não houver preventDefault() chamado
    });
</script>

<script>
    $(document).ready(function() {

        function limpa_formulário_cep() {
            // Limpa valores do formulário de cep.
            $("#street").val("");
            $("#neighborhood").val("");
            $("#city").val("");
            $("#state").val("");
        }

        //Quando o campo cep perde o foco.
        $("#cep").blur(function() {

            //Nova variável "cep" somente com dígitos.
            var cep = $(this).val().replace(/\D/g, '');

            //Verifica se campo cep possui valor informado.
            if (cep != "") {

                //Expressão regular para validar o CEP.
                var validacep = /^[0-9]{8}$/;

                //Valida o formato do CEP.
                if (validacep.test(cep)) {

                    //Preenche os campos com "..." enquanto consulta webservice.
                    $("#street").val("...");
                    $("#neighborhood").val("...");
                    $("#city").val("...");
                    $("#state").val("...");

                    //Consulta o webservice viacep.com.br/
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {

                        if (!("erro" in dados)) {
                            //Atualiza os campos com os valores da consulta.
                            $("#street").val(dados.logradouro);
                            $("#neighborhood").val(dados.bairro);
                            $("#city").val(dados.localidade);
                            $("#state").val(dados.uf);
                        } //end if.
                        else {
                            //CEP pesquisado não foi encontrado.
                            limpa_formulário_cep();
                            // Mostrar erro visual em vez de alert
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-warning mt-2';
                            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>CEP não encontrado.';

                            // Remover erro anterior se existir
                            const existingError = document.querySelector('.cep-jquery-error-message');
                            if (existingError) {
                                existingError.remove();
                            }

                            errorDiv.classList.add('cep-jquery-error-message');
                            document.querySelector('#cep').parentNode.appendChild(errorDiv);
                        }
                    });
                } //end if.
                else {
                    //cep é inválido.
                    limpa_formulário_cep();
                    // Mostrar erro visual em vez de alert
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-2';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Formato de CEP inválido.';

                    // Remover erro anterior se existir
                    const existingError = document.querySelector('.cep-format-error-message');
                    if (existingError) {
                        existingError.remove();
                    }

                    errorDiv.classList.add('cep-format-error-message');
                    document.querySelector('#cep').parentNode.appendChild(errorDiv);
                }
            } //end if.
            else {
                //cep sem valor, limpa formulário.
                limpa_formulário_cep();
            }
        });
    });
</script>

<style>
    /* Horizontal Profile Cards Styles */
    .profile-cards-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        max-width: 100%;
    }

    .profile-card-horizontal {
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e9ecef;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        cursor: pointer;
    }

    .profile-card-horizontal:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        border-color: #28a745;
    }

    .profile-card-horizontal.popular {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    }

    .profile-card-horizontal.popular:hover {
        border-color: #ffc107;
        box-shadow: 0 15px 35px rgba(255, 193, 7, 0.3);
    }

    .profile-card-content {
        display: flex;
        align-items: center;
        padding: 2rem;
        gap: 2rem;
    }

    .profile-icon {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .profile-card-horizontal[data-profile="curioso"] .profile-icon {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    }

    .profile-card-horizontal[data-profile="inteligente"] .profile-icon {
        background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
    }

    .profile-card-horizontal[data-profile="sabio"] .profile-icon {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .profile-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.5s ease;
    }

    .profile-card-horizontal:hover .profile-icon::before {
        animation: shine 0.8s ease;
    }

    @keyframes shine {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    .profile-info {
        flex: 1;
        min-width: 0;
    }

    .profile-header {
        margin-bottom: 1rem;
    }

    .profile-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
    }

    .profile-subtitle {
        font-size: 0.95rem;
        color: #6c757d;
        margin: 0;
        font-weight: 500;
    }

    .profile-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #495057;
    }

    .feature-item i {
        color: #28a745;
        font-size: 0.8rem;
        width: 16px;
        text-align: center;
    }

    .profile-pricing {
        flex-shrink: 0;
        text-align: center;
        min-width: 150px;
    }

    .price {
        margin-bottom: 1rem;
    }

    .price-amount {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        display: block;
    }

    .price-period {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }

    .profile-radio {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        position: relative;
    }

    /* Círculo à esquerda do "Escolher": vazio por padrão, preenchido quando o card está selecionado */
    .profile-radio::before {
        content: '';
        display: block;
        width: 22px;
        height: 22px;
        border: 2px solid #28a745;
        border-radius: 50%;
        background: #fff;
        flex-shrink: 0;
    }

    .profile-card-horizontal.selected .profile-radio::before {
        background: #28a745;
        box-shadow: inset 0 0 0 3px #fff;
    }

    .profile-radio input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
    }

    .profile-radio label {
        font-weight: 600;
        color: #495057;
        cursor: pointer;
        margin: 0;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .profile-card-horizontal:hover:not(.selected) .profile-radio label {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    /* Estado “escolhido”: só via .selected no card (sincronizado com o radio em JS), evita dois cards com aparência de selecionado */
    .profile-card-horizontal.selected {
        border-color: #28a745;
        background: linear-gradient(135deg, #e8f5e8 0%, #f8f9fa 100%);
        box-shadow: 0 15px 35px rgba(40, 167, 69, 0.2);
    }

    .profile-card-horizontal.selected .profile-icon {
        transform: scale(1.1);
    }

    .profile-card-horizontal.selected .profile-radio label {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    .popular-badge {
        position: absolute;
        top: -10px;
        right: 20px;
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        z-index: 10;
    }

    .popular-badge::before {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 5px solid #fd7e14;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .profile-card-content {
            flex-direction: column;
            text-align: center;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .profile-features {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .profile-pricing {
            min-width: auto;
        }

        .profile-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }

    @media (max-width: 576px) {
        .profile-cards-container {
            gap: 1rem;
        }

        .profile-card-content {
            padding: 1rem;
        }

        .profile-title {
            font-size: 1.25rem;
        }

        .price-amount {
            font-size: 1.5rem;
        }
    }

    /* Estilos para opções desabilitadas */
    .form-check-input:disabled+.form-check-label {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .form-check-input:disabled {
        cursor: not-allowed;
    }

    .disabled-feature {
        opacity: 0.5;
        text-decoration: line-through;
    }

    /* Estilos para seleção de cota */
    .quota-selection-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 1rem;
    }

    .quota-option-card {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 15px;
        background: #ffffff;
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .quota-option-card:hover {
        border-color: #007bff;
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }

    .quota-option-card.selected {
        border-color: #28a745;
        background: linear-gradient(135deg, #e8f5e8 0%, #f8f9fa 100%);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
    }

    .quota-option-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .quota-option-label {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        margin: 0;
        cursor: pointer;
        width: 100%;
        min-height: 80px;
    }

    .quota-option-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .quota-option-icon.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .quota-option-icon.danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }

    .quota-option-icon.info {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        color: white;
    }

    .quota-option-content {
        flex: 1;
        margin-right: 1rem;
    }

    .quota-option-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .quota-option-description {
        font-size: 0.9rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.4;
    }

    .quota-option-check {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .quota-option-card.selected .quota-option-check {
        background: #28a745;
        color: white;
        transform: scale(1.1);
    }

    .quota-option-card.selected .quota-option-icon {
        transform: scale(1.1);
    }

    .quota-option-card.selected .quota-option-title {
        color: #28a745;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .quota-option-label {
            padding: 1rem;
            min-height: 70px;
        }

        .quota-option-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .quota-option-title {
            font-size: 1rem;
        }

        .quota-option-description {
            font-size: 0.85rem;
        }
    }

    /* Estilos para seleção de hotel operacional */
    .hotel-operational-container {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .hotel-option-card {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 15px;
        background: #ffffff;
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
        flex: 1;
    }

    .hotel-option-card:hover {
        border-color: #007bff;
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }

    .hotel-option-card.selected {
        border-color: #28a745;
        background: linear-gradient(135deg, #e8f5e8 0%, #f8f9fa 100%);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
    }

    .hotel-option-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .hotel-option-label {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        margin: 0;
        cursor: pointer;
        width: 100%;
        min-height: 80px;
    }

    .hotel-option-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .hotel-option-icon.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .hotel-option-icon.danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }

    .hotel-option-content {
        flex: 1;
        margin-right: 1rem;
    }

    .hotel-option-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .hotel-option-description {
        font-size: 0.9rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.4;
    }

    .hotel-option-check {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .hotel-option-card.selected .hotel-option-check {
        background: #28a745;
        color: white;
        transform: scale(1.1);
    }

    .hotel-option-card.selected .hotel-option-icon {
        transform: scale(1.1);
    }

    .hotel-option-card.selected .hotel-option-title {
        color: #28a745;
    }

    /* Responsive para hotel */
    @media (max-width: 768px) {
        .hotel-operational-container {
            flex-direction: column;
        }

        .hotel-option-label {
            padding: 1rem;
            min-height: 70px;
        }

        .hotel-option-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .hotel-option-title {
            font-size: 1rem;
        }

        .hotel-option-description {
            font-size: 0.85rem;
        }
    }

    /* Estilos para usos permitidos */
    .allowed-uses-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .use-option-card {
        position: relative;
        border: 2px solid #e9ecef;
        border-radius: 15px;
        background: #ffffff;
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .use-option-card:hover {
        border-color: #007bff;
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }

    .use-option-card.selected {
        border-color: #28a745;
        background: linear-gradient(135deg, #e8f5e8 0%, #f8f9fa 100%);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
    }

    .use-option-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .use-option-card.disabled:hover {
        transform: none;
        box-shadow: none;
        border-color: #e9ecef;
    }

    .use-option-card input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .use-option-label {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        margin: 0;
        cursor: pointer;
        width: 100%;
        min-height: 80px;
    }

    .use-option-card.disabled .use-option-label {
        cursor: not-allowed;
    }

    .use-option-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .use-option-icon.rent {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        color: white;
    }

    .use-option-icon.sell {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .use-option-icon.exchange {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
    }

    .use-option-icon.buy {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
    }

    .use-option-content {
        flex: 1;
        margin-right: 1rem;
    }

    .use-option-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .use-option-description {
        font-size: 0.9rem;
        color: #6c757d;
        margin: 0;
        line-height: 1.4;
    }

    .use-option-check {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .use-option-card.selected .use-option-check {
        background: #28a745;
        color: white;
        transform: scale(1.1);
    }

    .use-option-card.selected .use-option-icon {
        transform: scale(1.1);
    }

    .use-option-card.selected .use-option-title {
        color: #28a745;
    }

    .use-option-card.disabled .use-option-title {
        text-decoration: line-through;
        color: #6c757d;
    }

    .use-option-card.disabled .use-option-description {
        color: #adb5bd;
    }

    /* Responsive para usos permitidos */
    @media (max-width: 768px) {
        .allowed-uses-container {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .use-option-label {
            padding: 1rem;
            min-height: 70px;
        }

        .use-option-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .use-option-title {
            font-size: 1rem;
        }

        .use-option-description {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .allowed-uses-container {
            grid-template-columns: 1fr;
        }
    }

    /* Estilos para erros de duplicação (laranja) */
    .alert-warning-custom {
        background-color: #fff3cd;
        border-color: #ffc107;
        color: #856404;
        padding: 1rem;
        border-radius: 0.375rem;
    }

    .alert-warning-custom i {
        color: #ff9800;
    }

    .is-warning {
        border-color: #ffc107 !important;
        background-color: #fff3cd;
    }

    .is-warning:focus {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
    }

    .warning-feedback {
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #856404;
        background-color: #fff3cd;
        padding: 0.5rem;
        border-radius: 0.25rem;
        border-left: 3px solid #ffc107;
    }
</style>

@endpush