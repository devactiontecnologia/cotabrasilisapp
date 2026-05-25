@extends('layouts.app')

@php
    $isQuotaEdit = isset($quota) && $quota;
    $editHotelId = null;
    $initialOwnerWeeksForEdit = [];
    $editWeeksCount = null;
    $fractionDetailsJsonForEdit = '';
    $seasonFromQuota = '';
    if ($isQuotaEdit) {
        $editHotelId = $hotels->firstWhere('name', $quota->hotel_name)?->id
            ?? $profile->owner_hotel_id
            ?? $profile->gestor_hotel_id;
        if ($quota->seasonality ?? null) {
            $seasonFromQuota = match ($quota->seasonality) {
                'low' => 'baixa',
                'medium' => 'media',
                'high' => 'alta',
                'peak' => 'pico',
                default => '',
            };
        }
        $fw = ($quota->fraction_details ?? [])['fraction_weeks'] ?? [];
        if (is_array($fw) && count($fw) > 0) {
            foreach ($fw as $k => $weekData) {
                $wn = (int) (is_numeric($k) ? $k : 1);
                if ($wn < 1) {
                    $wn = 1;
                }
                $entry = ['authorize' => 'yes'];
                $periods = is_array($weekData) ? ($weekData['periods'] ?? []) : [];
                $firstPeriod = is_array($periods) ? reset($periods) : null;
                if (is_array($firstPeriod) && ! empty($firstPeriod['start'])) {
                    try {
                        $d = \Carbon\Carbon::parse($firstPeriod['start']);
                        $entry['start_day'] = $d->format('d');
                        $entry['month'] = $d->format('m');
                        $entry['year'] = $d->format('Y');
                    } catch (\Throwable $e) {
                        // ignorar data inválida no JSON
                    }
                }
                $initialOwnerWeeksForEdit[$wn] = $entry;
            }
        }
        $fromCol = (int) ($quota->weeks ?? 0);
        $fromFrac = count($initialOwnerWeeksForEdit);
        $editWeeksCount = max(1, min(4, $fromCol ?: $fromFrac ?: 1));
        if ($isQuotaEdit && is_array($quota->fraction_details)) {
            $fractionDetailsJsonForEdit = json_encode($quota->fraction_details, JSON_UNESCAPED_UNICODE);
        }
    }
    $allowedUsesDefault = old('allowed_uses');
    if ($allowedUsesDefault === null) {
        $allowedUsesDefault = $isQuotaEdit
            ? ($quota->allowed_uses ?? $profile->allowed_uses ?? [])
            : [];
    }
    if (! is_array($allowedUsesDefault)) {
        $allowedUsesDefault = [];
    }
    $gestorAllowedUsesDefault = old('gestor_allowed_uses');
    if ($gestorAllowedUsesDefault === null) {
        $gestorAllowedUsesDefault = $isQuotaEdit
            ? ($quota->allowed_uses ?? $profile->gestor_allowed_uses ?? [])
            : [];
    }
    if (! is_array($gestorAllowedUsesDefault)) {
        $gestorAllowedUsesDefault = [];
    }

    $quotaDetailsForEdit = ($isQuotaEdit && is_array($profile->quota_details ?? null))
        ? $profile->quota_details
        : [];

    /** Sim/Não no cadastro: colunas do perfil e, se faltar, chaves em quota_details (finalizeRegistration). */
    $amenitySelectDefault = function (string $formField, ?string $profileColumn = null) use ($isQuotaEdit, $profile, $quotaDetailsForEdit): string {
        if (! $isQuotaEdit) {
            return '';
        }
        $col = $profileColumn ?? $formField;
        $v = $profile->getAttribute($col);
        if ($v !== null && $v !== '') {
            return (string) (int) (bool) $v;
        }
        if (array_key_exists($formField, $quotaDetailsForEdit)) {
            return (string) (int) (bool) $quotaDetailsForEdit[$formField];
        }

        return '';
    };

    /** Edição: gestor = perfil has_quota 2 ou 3 (cadastro completo usa 3 em alguns fluxos). */
    $profileHasQuotaIsGestor = in_array((int) ($profile->has_quota ?? 1), [2, 3], true);
    $hasQuotaLockedValue = old('has_quota', $isQuotaEdit ? ($profileHasQuotaIsGestor ? '2' : '1') : '');
@endphp

@section('title', $isQuotaEdit ? 'Editar Cota' : 'Cadastrar Nova Cota')

@section('content')
@if($isQuotaEdit)
<style>
    .quota-edit-back-btn {
        min-width: 132px;
        padding: 0.4rem 1.25rem !important;
        font-size: 0.875rem;
        line-height: 1.25;
    }
</style>
@endif
<div class="row justify-content-center py-5">
    <div class="col-lg-10">
        @if($isQuotaEdit)
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('quotas.show', $quota) }}" class="btn btn-outline-primary btn-sm quota-edit-back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        @endif
        <div class="card border-0 shadow-lg" data-aos="fade-up">
            <div class="card-body p-5">
            <div class="text-center mb-5">
                    <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-hotel fa-2x text-white"></i>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $isQuotaEdit ? 'Editar Cota' : 'Cadastrar Nova Cota' }}</h2>
                    <p class="text-muted">{{ $isQuotaEdit ? 'Revise as informações e atualize sua cota na plataforma' : 'Cadastre uma nova cota ou fração para usar na plataforma' }}</p>
            </div>

                <form method="POST" action="{{ $isQuotaEdit ? route('quotas.update', $quota) : route('quotas.store') }}" id="quotaForm" enctype="multipart/form-data" onsubmit="return ensureRoomFieldsEnabled()">
                    @csrf
                    @if($isQuotaEdit)
                        @method('PUT')
                        <input type="hidden" name="has_quota" value="{{ $hasQuotaLockedValue }}">
                        @if (! $profileHasQuotaIsGestor)
                            @foreach ($allowedUsesDefault as $lockedUse)
                                <input type="hidden" name="allowed_uses[]" value="{{ $lockedUse }}">
                            @endforeach
                            <input type="hidden" name="quota_status" value="{{ old('quota_status', $profile->quota_status ?? '') }}">
                        @else
                            @foreach ($gestorAllowedUsesDefault as $lockedGestorUse)
                                <input type="hidden" name="gestor_allowed_uses[]" value="{{ $lockedGestorUse }}">
                            @endforeach
                            <input type="hidden" name="gestor_quota_status" value="{{ old('gestor_quota_status', $profile->gestor_quota_status ?? '') }}">
                        @endif
                    @endif

                    <!-- Step 1: Informações da Cota -->
                    <div class="step" id="step1">
                        <h5 class="fw-bold mb-4 text-primary">
                        <i class="fas fa-hotel me-2"></i>Informações da Cota
                        </h5>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> A escolha de posse de cota define quais funcionalidades estarão disponíveis para você na plataforma.
                </div>

                        @if($isQuotaEdit)
                            <div class="alert alert-secondary border mb-4" role="status">
                                <i class="fas fa-lock me-2"></i>
                                <strong>Edição:</strong> tipo de posse da cota, documentos de posse/autorização, status da cota e usos permitidos não podem ser alterados aqui. Para mudanças nesses dados, entre em contato com o suporte.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-4" style="font-size: 25px;">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>Possuo Cota Hoteleira? *
                                </label>

                                <!-- Botões de seleção estilizados -->
                                <div class="quota-selection-container @if($isQuotaEdit) quota-immutable-on-edit pe-none user-select-none @endif">
                                    <div class="quota-option-card" data-value="1">
                                        <input type="radio" id="has_quota_yes" value="1"
                                            @if($isQuotaEdit) disabled @else name="has_quota" required @endif
                                            {{ old('has_quota', $isQuotaEdit ? ($profileHasQuotaIsGestor ? '2' : '1') : '') == '1' ? 'checked' : '' }}>
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

                                    <div class="quota-option-card" data-value="2">
                                        <input type="radio" id="has_quota_manager" value="2"
                                            @if($isQuotaEdit) disabled @else name="has_quota" required @endif
                                            {{ old('has_quota', $isQuotaEdit ? ($profileHasQuotaIsGestor ? '2' : '1') : '') == '2' ? 'checked' : '' }}>
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
                                </div>

                                @error('has_quota')
                                <div class="text-danger small mt-3">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                function toggleDeadline(selectId, sectionId, inputId) {
                                    const sel = document.getElementById(selectId);
                                    const sec = document.getElementById(sectionId);
                                    const inp = document.getElementById(inputId);
                                    if (!sel || !sec || !inp) return;
                                    const inpWrapper = inp.closest('.col-md-6') || inp.parentNode;
                                    function apply() {
                                        if (sel.value === 'paid') {
                                            if (inpWrapper) inpWrapper.classList.add('d-none'); else sec.classList.add('d-none');
                                            inp.removeAttribute('required');
                                            inp.required = false;
                                            try { inp.value = ''; } catch (e) {}
                                            inp.classList.remove('is-invalid');
                                            const fb = inpWrapper ? inpWrapper.querySelector('.invalid-feedback') : (sec ? sec.querySelector('.invalid-feedback') : null);
                                            if (fb) fb.style.display = 'none';
                                        } else {
                                            if (inpWrapper) inpWrapper.classList.remove('d-none'); else sec.classList.remove('d-none');
                                            inp.setAttribute('required', 'required');
                                            inp.required = true;
                                        }
                                    }
                                    sel.addEventListener('change', apply);
                                    apply();
                                }

                                toggleDeadline('quota_status', 'quota_payment_deadline_section', 'quota_payment_deadline');
                                toggleDeadline('gestor_quota_status', 'gestor_quota_payment_deadline_section', 'gestor_quota_payment_deadline');
                            });
                        </script>

                        <!-- Mensagem de aviso para quem não possui cota -->
                        <div class="mt-4 d-none" id="no_quota_warning">
                            <div class="alert alert-info border-0 shadow-sm">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle me-3 mt-1 text-info"></i>
                                    <div>
                                        <h6 class="alert-heading mb-2">Informação Importante</h6>
                                        <p class="mb-0">
                                            Como você não possui Cotas, você terá acesso somente às principais funções da plataforma que são <strong>COMPRAR</strong> ou
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
                                                {{ old('hotel_operational', $isQuotaEdit ? (($profile->hotel_operational ?? true) ? '1' : '0') : '1') == '1' ? 'checked' : '' }} required>
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
                                                {{ old('hotel_operational', $isQuotaEdit ? (($profile->hotel_operational ?? true) ? '1' : '0') : '1') === '0' ? 'checked' : '' }} required>
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

                                <div class="@if($isQuotaEdit && ! $profileHasQuotaIsGestor) quota-immutable-on-edit @endif">
                                <!-- 2. Documento de confirmação da Posse da Cota -->
                                <div class="mt-3">
                                    <label for="quota_contract" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Documento de confirmação da Posse da Cota *
                                </label>
                                    <input type="file" class="form-control form-control-lg @error('quota_contracts') is-invalid @enderror"
                                        id="quota_contract" name="quota_contracts[]" accept=".pdf,.jpg,.jpeg,.png" multiple
                                        @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @endif>
                                    <div class="form-text">
                                        <strong>Obrigatório:</strong> Foto da primeira folha do contrato de compra e venda da Cota, ou outra folha do contrato, que contenha informações do nome do hotel, nome do titular, CPF, endereço, telefone, email, número da cota, do bloco e do apartamento. Formatos de documentos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB
                                    </div>
                                    @error('quota_contracts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                                <!-- Termo de Autorização de Hospedagem para Terceiros -->
                                <div class="mt-3">
                                    <label for="hospitality_authorization_term" class="form-label fw-semibold">
                                        <i class="fas fa-file-pdf me-2 text-primary"></i>Termo de Autorização de Hospedagem para Terceiros
                                    </label>
                                    <input type="file" class="form-control form-control-lg @error('hospitality_authorization_term') is-invalid @enderror"
                                        id="hospitality_authorization_term" name="hospitality_authorization_term" accept=".pdf"
                                        @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @endif>
                                    <div class="form-text">
                                        Anexe o termo oficial do hotel onde você cadastrará cotas aqui. Cada hotel tem o seu próprio termo em pdf editável além do sistema digital.
                                    </div>
                                    @error('hospitality_authorization_term')
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
                                            id="quota_status"
                                            @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @else name="quota_status" required @endif>
                                            <option value="">Selecione o status</option>
                                            <option value="paid" {{ old('quota_status', $isQuotaEdit ? ($profile->quota_status ?? '') : '') == 'paid' ? 'selected' : '' }}>Quitada</option>
                                            <option value="unpaid" {{ old('quota_status', $isQuotaEdit ? ($profile->quota_status ?? '') : '') == 'unpaid' ? 'selected' : '' }}>Não Quitada</option>
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
                                            id="quota_payment_deadline" name="quota_payment_deadline" value="{{ old('quota_payment_deadline', $isQuotaEdit && $profile->quota_payment_deadline ? \Carbon\Carbon::parse($profile->quota_payment_deadline)->format('Y-m-d') : '') }}">
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
                                                <input type="checkbox" id="use_rent" value="rent"
                                                    @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @else name="allowed_uses[]" @endif
                                                    {{ is_array($allowedUsesDefault) && in_array('rent', $allowedUsesDefault) ? 'checked' : '' }}>
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
                                                <input type="checkbox" id="use_exchange" value="exchange"
                                                    @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @else name="allowed_uses[]" @endif
                                                    {{ is_array($allowedUsesDefault) && in_array('exchange', $allowedUsesDefault) ? 'checked' : '' }}>
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
                                                <input type="checkbox" id="use_sell" value="sell"
                                                    @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @else name="allowed_uses[]" @endif
                                                    {{ is_array($allowedUsesDefault) && in_array('sell', $allowedUsesDefault) ? 'checked' : '' }}>
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
                                                <input type="checkbox" id="use_buy" value="buy"
                                                    @if($isQuotaEdit && ! $profileHasQuotaIsGestor) disabled @else name="allowed_uses[]" @endif
                                                    {{ is_array($allowedUsesDefault) && in_array('buy', $allowedUsesDefault) ? 'checked' : '' }}>
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
                                </div>

                                <!-- Informações da Cota -->
                                <div class="mb-4 mt-4">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-hotel me-2"></i>Informações da Cota
                                    </h6>

                                    <!-- Primeira linha: Hotel -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="owner_hotel_id" class="form-label fw-semibold mb-1">
                                                <i class="fas fa-hotel me-1 text-success"></i>Hotel *
                                            </label>
                                            <div class="alert alert-warning hotel-choice-warning-static small mb-2 py-2 shadow-sm" role="alert">
                                                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                                                Atenção! No caso de escolha errônea do nome do hotel, você só poderá corrigir com o apoio da equipe de suporte. Isso pode demorar!
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text bg-success text-white">
                                                    <i class="fas fa-hotel"></i>
                                                </span>
                                                <select 
                                                    id="owner_hotel_id"
                                                    name="owner_hotel_id"
                                                    class="form-select @error('owner_hotel_id') is-invalid @enderror"
                                                    required
                                                >
                                                    <option value="">Selecione o hotel</option>
                                                    @if(isset($hotels) && $hotels->count())
                                                        @foreach($hotels as $hotel)
                                                            <option value="{{ $hotel->id }}" {{ (string) old('owner_hotel_id', $editHotelId ?? '') === (string) $hotel->id ? 'selected' : '' }}>
                                                                {{ $hotel->name }}@if($hotel->city || $hotel->state) - {{ implode(', ', array_filter([$hotel->city, $hotel->state])) }}@endif
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            @error('owner_hotel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_rooms" class="form-label fw-semibold">
                                                <i class="fas fa-bed me-1 text-success"></i>Quartos *
                                </label>
                                            <select class="form-select @error('owner_quota_rooms') is-invalid @enderror"
                                                id="owner_quota_rooms" name="owner_quota_rooms" required>
                                    <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_rooms', $isQuotaEdit ? (string) ($profile->owner_quota_rooms ?? ($quota->number_of_rooms ?? '')) : '') == '1' ? 'selected' : '' }}>1 Quarto</option>
                                                <option value="2" {{ old('owner_quota_rooms', $isQuotaEdit ? (string) ($profile->owner_quota_rooms ?? ($quota->number_of_rooms ?? '')) : '') == '2' ? 'selected' : '' }}>2 Quartos</option>
                                                <option value="3" {{ old('owner_quota_rooms', $isQuotaEdit ? (string) ($profile->owner_quota_rooms ?? ($quota->number_of_rooms ?? '')) : '') == '3' ? 'selected' : '' }}>3 Quartos</option>
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
                                            <div id="rooms-container">
                                                <!-- Os blocos de quartos serão inseridos aqui dinamicamente -->
                            </div>
                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_balcony" class="form-label fw-semibold">
                                                <i class="fas fa-door-open me-1 text-success"></i>Varanda *
                                            </label>
                                            <select class="form-select @error('owner_quota_balcony') is-invalid @enderror"
                                                id="owner_quota_balcony" name="owner_quota_balcony" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_balcony', $amenitySelectDefault('owner_quota_balcony')) == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_balcony', $amenitySelectDefault('owner_quota_balcony')) == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_balcony')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                            </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_size" class="form-label fw-semibold">
                                                <i class="fas fa-expand-arrows-alt me-1 text-success"></i>Tamanho (m²) *
                                        </label>
                                            <input type="text" class="form-control @error('owner_quota_size') is-invalid @enderror"
                                                id="owner_quota_size" name="owner_quota_size"
                                                placeholder="Ex: 45, 50-60, 70+"
                                                value="{{ old('owner_quota_size', $isQuotaEdit ? ($profile->owner_quota_size ?? '') : '') }}" required>
                                            @error('owner_quota_size')
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
                                                <option value="1" {{ old('owner_quota_jacuzzi', $amenitySelectDefault('owner_quota_jacuzzi')) == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_jacuzzi', $amenitySelectDefault('owner_quota_jacuzzi')) == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_jacuzzi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_kitchen" class="form-label fw-semibold">
                                                <i class="fas fa-utensils me-1 text-success"></i>Cozinha Completa *
                                            </label>
                                            <select class="form-select @error('owner_quota_kitchen') is-invalid @enderror"
                                                id="owner_quota_kitchen" name="owner_quota_kitchen" required>
                                                <option value="">Selecione</option>
                                                <option value="1" {{ old('owner_quota_kitchen', $amenitySelectDefault('owner_quota_kitchen')) == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_kitchen', $amenitySelectDefault('owner_quota_kitchen')) == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_kitchen')
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
                                                <option value="1" {{ old('owner_quota_parking', $amenitySelectDefault('owner_quota_parking')) == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_parking', $amenitySelectDefault('owner_quota_parking')) == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_parking')
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
                                                <option value="1" {{ old('owner_quota_breakfast', $amenitySelectDefault('owner_quota_breakfast')) == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_breakfast', $amenitySelectDefault('owner_quota_breakfast')) == '0' ? 'selected' : '' }}>Não</option>
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
                                                <option value="1" {{ old('owner_quota_sofa_mais', $amenitySelectDefault('owner_quota_sofa_mais')) == '1' ? 'selected' : '' }}>Sim</option>
                                                <option value="0" {{ old('owner_quota_sofa_mais', $amenitySelectDefault('owner_quota_sofa_mais')) == '0' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('owner_quota_sofa_mais')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="owner_quota_seasonality" class="form-label fw-semibold">
                                                <i class="fas fa-calendar-alt me-1 text-success"></i>Sazonalidade *
                                            </label>
                                            <select class="form-select @error('owner_quota_seasonality') is-invalid @enderror"
                                                id="owner_quota_seasonality" name="owner_quota_seasonality" required>
                                                <option value="">Selecione</option>
                                                <option value="baixa" {{ old('owner_quota_seasonality', $isQuotaEdit ? ($profile->owner_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                                <option value="media" {{ old('owner_quota_seasonality', $isQuotaEdit ? ($profile->owner_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'media' ? 'selected' : '' }}>Média</option>
                                                <option value="alta" {{ old('owner_quota_seasonality', $isQuotaEdit ? ($profile->owner_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'alta' ? 'selected' : '' }}>Alta</option>
                                                <option value="pico" {{ old('owner_quota_seasonality', $isQuotaEdit ? ($profile->owner_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'pico' ? 'selected' : '' }}>Altíssima</option>
                                            </select>
                                            <div class="form-text">Ex: Baixa, Média, Alta, Altíssima</div>
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
                                                <option value="fixa" {{ old('owner_quota_type', $isQuotaEdit ? ($profile->owner_quota_type ?? ($quota->quota_type ?? '')) : '') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                                                <option value="flexivel" {{ old('owner_quota_type', $isQuotaEdit ? ($profile->owner_quota_type ?? ($quota->quota_type ?? '')) : '') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                                                <option value="fix_flexivel" {{ old('owner_quota_type', $isQuotaEdit ? ($profile->owner_quota_type ?? ($quota->quota_type ?? '')) : '') == 'fix_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                                            </select>
                                            <div class="form-text">Informe como o uso da cota está definido no contrato.</div>
                                            @error('owner_quota_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Campos de Comodidades -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="owner_quota_hidromassagem" class="form-label fw-semibold">
                                    <i class="fas fa-hot-tub me-1 text-success"></i>Hidromassagem *
                                </label>
                                <select class="form-select @error('owner_quota_hidromassagem') is-invalid @enderror"
                                    id="owner_quota_hidromassagem" name="owner_quota_hidromassagem" required>
                                    <option value="">Selecione</option>
                                    <option value="1" {{ old('owner_quota_hidromassagem', $amenitySelectDefault('owner_quota_hidromassagem', 'owner_quota_jacuzzi')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_hidromassagem', $amenitySelectDefault('owner_quota_hidromassagem', 'owner_quota_jacuzzi')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_hidromassagem')
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
                                    <option value="1" {{ old('owner_quota_academia', $amenitySelectDefault('owner_quota_academia')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_academia', $amenitySelectDefault('owner_quota_academia')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_academia')
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
                                    <option value="1" {{ old('owner_quota_vista_mar', $amenitySelectDefault('owner_quota_vista_mar')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_vista_mar', $amenitySelectDefault('owner_quota_vista_mar')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_vista_mar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="owner_quota_lareira" class="form-label fw-semibold">
                                    <i class="fas fa-fire me-1 text-success"></i>Lareira *
                                </label>
                                <select class="form-select @error('owner_quota_lareira') is-invalid @enderror"
                                    id="owner_quota_lareira" name="owner_quota_lareira" required>
                                    <option value="">Selecione</option>
                                    <option value="1" {{ old('owner_quota_lareira', $amenitySelectDefault('owner_quota_lareira')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_lareira', $amenitySelectDefault('owner_quota_lareira')) == '0' ? 'selected' : '' }}>Não</option>
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
                                    <option value="1" {{ old('owner_quota_adega', $amenitySelectDefault('owner_quota_adega')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_adega', $amenitySelectDefault('owner_quota_adega')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_adega')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="owner_quota_area_kids" class="form-label fw-semibold">
                                    <i class="fas fa-child me-1 text-success"></i>Área Kids *
                                </label>
                                <select class="form-select @error('owner_quota_area_kids') is-invalid @enderror"
                                    id="owner_quota_area_kids" name="owner_quota_area_kids" required>
                                    <option value="">Selecione</option>
                                    <option value="1" {{ old('owner_quota_area_kids', $amenitySelectDefault('owner_quota_area_kids')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_area_kids', $amenitySelectDefault('owner_quota_area_kids')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_area_kids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="owner_quota_area_trabalho" class="form-label fw-semibold">
                                    <i class="fas fa-briefcase me-1 text-success"></i>Área de Trabalho *
                                </label>
                                <select class="form-select @error('owner_quota_area_trabalho') is-invalid @enderror"
                                    id="owner_quota_area_trabalho" name="owner_quota_area_trabalho" required>
                                    <option value="">Selecione</option>
                                    <option value="1" {{ old('owner_quota_area_trabalho', $amenitySelectDefault('owner_quota_area_trabalho')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_area_trabalho', $amenitySelectDefault('owner_quota_area_trabalho')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_area_trabalho')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="owner_quota_spa" class="form-label fw-semibold">
                                    <i class="fas fa-spa me-1 text-success"></i>Spa *
                                </label>
                                <select class="form-select @error('owner_quota_spa') is-invalid @enderror"
                                    id="owner_quota_spa" name="owner_quota_spa" required>
                                    <option value="">Selecione</option>
                                    <option value="1" {{ old('owner_quota_spa', $amenitySelectDefault('owner_quota_spa')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_spa', $amenitySelectDefault('owner_quota_spa')) == '0' ? 'selected' : '' }}>Não</option>
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
                                    <option value="1" {{ old('owner_quota_piscina', $amenitySelectDefault('owner_quota_piscina')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_piscina', $amenitySelectDefault('owner_quota_piscina')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_piscina')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="owner_quota_wifi" class="form-label fw-semibold">
                                    <i class="fas fa-wifi me-1 text-success"></i>WiFi *
                                </label>
                                <select class="form-select @error('owner_quota_wifi') is-invalid @enderror"
                                    id="owner_quota_wifi" name="owner_quota_wifi" required>
                                    <option value="">Selecione</option>
                                    <option value="1" {{ old('owner_quota_wifi', $amenitySelectDefault('owner_quota_wifi')) == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('owner_quota_wifi', $amenitySelectDefault('owner_quota_wifi')) == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                                @error('owner_quota_wifi')
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
                                                    <option value="{{ $week }}" {{ (string) old('owner_quota_weeks_count', $editWeeksCount !== null ? $editWeeksCount : '') === (string) $week ? 'selected' : '' }}>{{ $week }}</option>
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
                                                <i class="fas fa-sticky-note me-1 text-success"></i>Observações da cota *
                                </label>
                                            <textarea class="form-control @error('owner_quota_observations') is-invalid @enderror"
                                                id="owner_quota_observations" name="owner_quota_observations"
                                                rows="4" placeholder='Escreva em detalhes o que diferencia a sua Cota para melhor atrair o público.
Ex: Vista mar ou do vale, acesso facilitado à piscina, à academia, se tem lareira ou adega, etc.
Possibilidade de serviços extras gratuitos, ou com desconto.
Seja o mais detalhista possivel'>{{ old('owner_quota_observations', $isQuotaEdit ? ($quota->observations ?? '') : '') }}</textarea>
                                            @error('owner_quota_observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                        </div>
                            </div>
                        </div>

                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </button>
                                <button type="button" class="btn btn-primary btn-lg px-4" id="owner_next_button" onclick="nextStep()">
                                    Próximo <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                            <!-- Seção para gestor (fora de quota_owner_section para não ser ocultada ao escolher gestor) -->
                            <div class="mt-4 d-none" id="gestor_section">
                                <h6 class="fw-bold text-warning mb-3">
                                    <i class="fas fa-user-tie me-2"></i>Informações do Gestor
                                </h6>

                                <!-- Campo Hotel Operacional para Gestores -->
                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold mb-4">
                                            <i class="fas fa-hotel me-2 text-primary"></i>O Hotel onde você é(será) Gestor da Cota está em funcionamento? *
                                        </label>

                                        <!-- Botões de seleção estilizados lado a lado -->
                                        <div class="hotel-operational-container">
                                            <div class="hotel-option-card" data-value="1">
                                                <input type="radio" name="gestor_hotel_operational" id="gestor_hotel_operational_yes" value="1"
                                                    {{ old('gestor_hotel_operational', $isQuotaEdit ? (($profile->gestor_hotel_operational ?? true) ? '1' : '0') : '1') == '1' ? 'checked' : '' }} required>
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
                                                    {{ old('gestor_hotel_operational', $isQuotaEdit ? (($profile->gestor_hotel_operational ?? true) ? '1' : '0') : '1') === '0' ? 'checked' : '' }} required>
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
                                </div>

                                <div class="alert alert-warning mt-3 d-none" id="gestor_hotel_not_operational_notice">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Aviso!</strong> Não é possível cadastrar cotas quando o hotel não está em funcionamento. Retorne para o cadastro quando ele estiver em funcionamento.
                                </div>

                                <div id="gestor_additional_fields">
                                    <div class="@if($isQuotaEdit && $profileHasQuotaIsGestor) quota-immutable-on-edit @endif">
                                    <!-- Documento de Autorização -->
                                    <div class="mt-3">
                                        <label for="gestor_authorization_document" class="form-label fw-semibold">
                                            <i class="fas fa-file-signature me-2 text-primary"></i>Documento de Autorização *
                                        </label>
                                        <input type="file" class="form-control form-control-lg @error('gestor_authorization_document') is-invalid @enderror"
                                            id="gestor_authorization_document" name="gestor_authorization_document"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @else required @endif>
                                        <div class="form-text">
                                            <strong>Obrigatório:</strong> Documento de autorização do dono da Cota para você, Gestor da Cota, mencionando nome do hotel, números da cota, do bloco e do apartamento, os nomes completos do dono e do gestor com seus CPFs, endereços residenciais, números telefônicos, emails, permitindo ao Gestor usá-lo em seu nome no aplicativo Cota Brasilis para as funções de Aluguel e Troca. Datar e assinar digitalmente o documento pelo Gov.br. Formatos de documento aceitos são PDF, JPG, JPEG, PNG. Tamanho máximo 10MB
                                        </div>
                                        @error('gestor_authorization_document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Termo de Autorização de Hospedagem para Terceiros -->
                                    <div class="mt-3">
                                        <label for="gestor_hospitality_authorization_term" class="form-label fw-semibold">
                                            <i class="fas fa-file-pdf me-2 text-primary"></i>Termo de Autorização de Hospedagem para Terceiros
                                        </label>
                                        <input type="file" class="form-control form-control-lg @error('gestor_hospitality_authorization_term') is-invalid @enderror"
                                            id="gestor_hospitality_authorization_term" name="gestor_hospitality_authorization_term" accept=".pdf"
                                            @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @endif>
                                        <div class="form-text">
                                            Anexe o termo oficial do hotel onde você cadastrará cotas aqui. Cada hotel tem o seu próprio termo em pdf editável além do sistema digital.
                                        </div>
                                        @error('gestor_hospitality_authorization_term')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status da Cota e Prazo de Quitação (id na coluna do prazo = toggleDeadline / cadastro) -->
                                    <div class="mb-4" id="gestor_quota_status_section">
                                        <div class="row g-3 mt-3">
                                            <div class="col-md-6">
                                                <label for="gestor_quota_status" class="form-label fw-semibold">
                                                    <i class="fas fa-credit-card me-2 text-primary"></i>Status da Cota *
                                                </label>
                                                <select class="form-select form-select-lg @error('gestor_quota_status') is-invalid @enderror"
                                                    id="gestor_quota_status"
                                                    @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @else name="gestor_quota_status" required @endif>
                                                    <option value="">Selecione o status</option>
                                                    <option value="paid" {{ old('gestor_quota_status', $isQuotaEdit ? ($profile->gestor_quota_status ?? '') : '') == 'paid' ? 'selected' : '' }}>Quitada</option>
                                                    <option value="unpaid" {{ old('gestor_quota_status', $isQuotaEdit ? ($profile->gestor_quota_status ?? '') : '') == 'unpaid' ? 'selected' : '' }}>Não Quitada</option>
                                                </select>
                                                @error('gestor_quota_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6" id="gestor_quota_payment_deadline_section">
                                                <label for="gestor_quota_payment_deadline" class="form-label fw-semibold">
                                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Prazo para Quitação
                                                </label>
                                                <input type="date" class="form-control form-control-lg @error('gestor_quota_payment_deadline') is-invalid @enderror"
                                                    id="gestor_quota_payment_deadline" name="gestor_quota_payment_deadline" value="{{ old('gestor_quota_payment_deadline', $isQuotaEdit && $profile->gestor_quota_payment_deadline ? \Carbon\Carbon::parse($profile->gestor_quota_payment_deadline)->format('Y-m-d') : '') }}">
                                                <div class="form-text">Informe o prazo para quitação da Cota</div>
                                                @error('gestor_quota_payment_deadline')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Usos permitidos para gestores -->
                                    <div class="row g-3 mt-2">
                                        <div class="col-12"><br><br>
                                            <label class="form-label fw-semibold mb-4" style="font-size: 18px;">
                                                <i class="fas fa-sliders-h me-2 text-primary"></i>Usos permitidos *
                                            </label>
                                            <small>Escolha quais funcionalidades você quer usar</small>

                                            <!-- Cards de usos permitidos lado a lado -->
                                            <div class="allowed-uses-container">
                                                <div class="use-option-card" data-value="rent">
                                                    <input type="checkbox" id="gestor_use_rent" value="rent"
                                                        @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @else name="gestor_allowed_uses[]" @endif
                                                        {{ is_array($gestorAllowedUsesDefault) && in_array('rent', $gestorAllowedUsesDefault) ? 'checked' : '' }}>
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
                                                    <input type="checkbox" id="gestor_use_exchange" value="exchange"
                                                        @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @else name="gestor_allowed_uses[]" @endif
                                                        {{ is_array($gestorAllowedUsesDefault) && in_array('exchange', $gestorAllowedUsesDefault) ? 'checked' : '' }}>
                                                    <label for="gestor_use_exchange" class="use-option-label">
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
                                                    <input type="checkbox" id="gestor_use_sell" value="sell"
                                                        @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @else name="gestor_allowed_uses[]" @endif
                                                        {{ is_array($gestorAllowedUsesDefault) && in_array('sell', $gestorAllowedUsesDefault) ? 'checked' : '' }}>
                                                    <label for="gestor_use_sell" class="use-option-label">
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
                                                    <input type="checkbox" id="gestor_use_buy" value="buy"
                                                        @if($isQuotaEdit && $profileHasQuotaIsGestor) disabled @else name="gestor_allowed_uses[]" @endif
                                                        {{ is_array($gestorAllowedUsesDefault) && in_array('buy', $gestorAllowedUsesDefault) ? 'checked' : '' }}>
                                                    <label for="gestor_use_buy" class="use-option-label">
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

                                            @error('gestor_allowed_uses')
                                            <div class="text-danger small mt-3">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    </div>

                                    <!-- Informações da Cota para Gestor -->
                                    <div class="mb-4 mt-4">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="fas fa-hotel me-2"></i>Informações da Cota
                                        </h6>

                                        <!-- Hotel -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-12">
                                                <label for="gestor_hotel_id" class="form-label fw-semibold mb-1">
                                                    <i class="fas fa-hotel me-1 text-success"></i>Hotel *
                                                </label>
                                                <div class="alert alert-warning hotel-choice-warning-static small mb-2 py-2 shadow-sm" role="alert">
                                                    <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                                                    Atenção! No caso de escolha errônea do nome do hotel, você só poderá corrigir com o apoio da equipe de suporte. Isso pode demorar!
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white">
                                                        <i class="fas fa-hotel"></i>
                                                    </span>
                                                    <select
                                                        id="gestor_hotel_id"
                                                        name="gestor_hotel_id"
                                                        class="form-select @error('gestor_hotel_id') is-invalid @enderror"
                                                        required
                                                    >
                                                        <option value="">Selecione o hotel</option>
                                                        @if(isset($hotels) && $hotels->count())
                                                            @foreach($hotels as $hotel)
                                                                <option value="{{ $hotel->id }}" {{ (string) old('gestor_hotel_id', $editHotelId ?? '') === (string) $hotel->id ? 'selected' : '' }}>
                                                                    {{ $hotel->name }}@if($hotel->city || $hotel->state) - {{ implode(', ', array_filter([$hotel->city, $hotel->state])) }}@endif
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                @error('gestor_hotel_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gestor_quota_rooms" class="form-label fw-semibold">
                                                    <i class="fas fa-bed me-1 text-success"></i>Quartos *
                                                </label>
                                                <select class="form-select @error('gestor_quota_rooms') is-invalid @enderror"
                                                    id="gestor_quota_rooms" name="gestor_quota_rooms" required>
                                                    <option value="">Selecione</option>
                                                    <option value="1" {{ old('gestor_quota_rooms', $isQuotaEdit ? (string) ($profile->gestor_quota_rooms ?? ($quota->number_of_rooms ?? '')) : '') == '1' ? 'selected' : '' }}>1 Quarto</option>
                                                    <option value="2" {{ old('gestor_quota_rooms', $isQuotaEdit ? (string) ($profile->gestor_quota_rooms ?? ($quota->number_of_rooms ?? '')) : '') == '2' ? 'selected' : '' }}>2 Quartos</option>
                                                    <option value="3" {{ old('gestor_quota_rooms', $isQuotaEdit ? (string) ($profile->gestor_quota_rooms ?? ($quota->number_of_rooms ?? '')) : '') == '3' ? 'selected' : '' }}>3 Quartos</option>
                                                </select>
                                                @error('gestor_quota_rooms')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gestor_quota_balcony" class="form-label fw-semibold">
                                                    <i class="fas fa-door-open me-1 text-success"></i>Varanda *
                                                </label>
                                                <select class="form-select @error('gestor_quota_balcony') is-invalid @enderror"
                                                    id="gestor_quota_balcony" name="gestor_quota_balcony" required>
                                                    <option value="">Selecione</option>
                                                    <option value="1" {{ old('gestor_quota_balcony', $amenitySelectDefault('gestor_quota_balcony')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_balcony', $amenitySelectDefault('gestor_quota_balcony')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_balcony')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gestor_quota_size" class="form-label fw-semibold">
                                                    <i class="fas fa-expand-arrows-alt me-1 text-success"></i>Tamanho (m²) *
                                                </label>
                                                <input type="text" class="form-control @error('gestor_quota_size') is-invalid @enderror"
                                                    id="gestor_quota_size" name="gestor_quota_size"
                                                    placeholder="Ex: 45, 50-60, 70+"
                                                    value="{{ old('gestor_quota_size', $isQuotaEdit ? ($profile->gestor_quota_size ?? '') : '') }}" required>
                                                @error('gestor_quota_size')
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
                                                    <option value="1" {{ old('gestor_quota_jacuzzi', $amenitySelectDefault('gestor_quota_jacuzzi')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_jacuzzi', $amenitySelectDefault('gestor_quota_jacuzzi')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_jacuzzi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gestor_quota_kitchen" class="form-label fw-semibold">
                                                    <i class="fas fa-utensils me-1 text-success"></i>Cozinha Completa *
                                                </label>
                                                <select class="form-select @error('gestor_quota_kitchen') is-invalid @enderror"
                                                    id="gestor_quota_kitchen" name="gestor_quota_kitchen" required>
                                                    <option value="">Selecione</option>
                                                    <option value="1" {{ old('gestor_quota_kitchen', $amenitySelectDefault('gestor_quota_kitchen')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_kitchen', $amenitySelectDefault('gestor_quota_kitchen')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_kitchen')
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
                                                    <option value="1" {{ old('gestor_quota_parking', $amenitySelectDefault('gestor_quota_parking')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_parking', $amenitySelectDefault('gestor_quota_parking')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_parking')
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
                                                    <option value="1" {{ old('gestor_quota_breakfast', $amenitySelectDefault('gestor_quota_breakfast')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_breakfast', $amenitySelectDefault('gestor_quota_breakfast')) == '0' ? 'selected' : '' }}>Não</option>
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
                                                    <option value="1" {{ old('gestor_quota_sofa_mais', $amenitySelectDefault('gestor_quota_sofa_mais')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_sofa_mais', $amenitySelectDefault('gestor_quota_sofa_mais')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_sofa_mais')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gestor_quota_seasonality" class="form-label fw-semibold">
                                                    <i class="fas fa-calendar-alt me-1 text-success"></i>Sazonalidade *
                                                </label>
                                                <select class="form-select @error('gestor_quota_seasonality') is-invalid @enderror"
                                                    id="gestor_quota_seasonality" name="gestor_quota_seasonality" required>
                                                    <option value="">Selecione</option>
                                                    <option value="baixa" {{ old('gestor_quota_seasonality', $isQuotaEdit ? ($profile->gestor_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                                    <option value="media" {{ old('gestor_quota_seasonality', $isQuotaEdit ? ($profile->gestor_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'media' ? 'selected' : '' }}>Média</option>
                                                    <option value="alta" {{ old('gestor_quota_seasonality', $isQuotaEdit ? ($profile->gestor_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'alta' ? 'selected' : '' }}>Alta</option>
                                                    <option value="pico" {{ old('gestor_quota_seasonality', $isQuotaEdit ? ($profile->gestor_quota_seasonality ?? $seasonFromQuota ?? '') : '') == 'pico' ? 'selected' : '' }}>Altíssima</option>
                                                </select>
                                                <div class="form-text">Ex: Baixa, Média, Alta, Altíssima</div>
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
                                                    <option value="fixa" {{ old('gestor_quota_type', $isQuotaEdit ? ($profile->gestor_quota_type ?? ($quota->quota_type ?? '')) : '') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                                                    <option value="flexivel" {{ old('gestor_quota_type', $isQuotaEdit ? ($profile->gestor_quota_type ?? ($quota->quota_type ?? '')) : '') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                                                    <option value="fix_flexivel" {{ old('gestor_quota_type', $isQuotaEdit ? ($profile->gestor_quota_type ?? ($quota->quota_type ?? '')) : '') == 'fix_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                                                </select>
                                                <div class="form-text">Informe como o uso da cota está definido no contrato.</div>
                                                @error('gestor_quota_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Campos de Comodidades para Gestor -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label for="gestor_quota_hidromassagem" class="form-label fw-semibold">
                                                    <i class="fas fa-hot-tub me-1 text-success"></i>Hidromassagem *
                                                </label>
                                                <select class="form-select @error('gestor_quota_hidromassagem') is-invalid @enderror"
                                                    id="gestor_quota_hidromassagem" name="gestor_quota_hidromassagem" required>
                                                    <option value="">Selecione</option>
                                                    <option value="1" {{ old('gestor_quota_hidromassagem', $amenitySelectDefault('gestor_quota_hidromassagem', 'gestor_quota_jacuzzi')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_hidromassagem', $amenitySelectDefault('gestor_quota_hidromassagem', 'gestor_quota_jacuzzi')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_hidromassagem')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="gestor_quota_spa" class="form-label fw-semibold">
                                                    <i class="fas fa-spa me-1 text-success"></i>Spa *
                                                </label>
                                                <select class="form-select @error('gestor_quota_spa') is-invalid @enderror"
                                                    id="gestor_quota_spa" name="gestor_quota_spa" required>
                                                    <option value="">Selecione</option>
                                                    <option value="1" {{ old('gestor_quota_spa', $amenitySelectDefault('gestor_quota_spa')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_spa', $amenitySelectDefault('gestor_quota_spa')) == '0' ? 'selected' : '' }}>Não</option>
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
                                                    <option value="1" {{ old('gestor_quota_piscina', $amenitySelectDefault('gestor_quota_piscina')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_piscina', $amenitySelectDefault('gestor_quota_piscina')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_piscina')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label for="gestor_quota_wifi" class="form-label fw-semibold">
                                                    <i class="fas fa-wifi me-1 text-success"></i>WiFi *
                                                </label>
                                                <select class="form-select @error('gestor_quota_wifi') is-invalid @enderror"
                                                    id="gestor_quota_wifi" name="gestor_quota_wifi" required>
                                                    <option value="">Selecione</option>
                                                    <option value="1" {{ old('gestor_quota_wifi', $amenitySelectDefault('gestor_quota_wifi')) == '1' ? 'selected' : '' }}>Sim</option>
                                                    <option value="0" {{ old('gestor_quota_wifi', $amenitySelectDefault('gestor_quota_wifi')) == '0' ? 'selected' : '' }}>Não</option>
                                                </select>
                                                @error('gestor_quota_wifi')
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
                                                        <option value="{{ $week }}" {{ (string) old('gestor_quota_weeks_count', $editWeeksCount !== null ? $editWeeksCount : '') === (string) $week ? 'selected' : '' }}>{{ $week }}</option>
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
                                                    <i class="fas fa-sticky-note me-1 text-success"></i>Observações da cota *
                                                </label>
                                                <textarea class="form-control @error('gestor_quota_observations') is-invalid @enderror"
                                                    id="gestor_quota_observations" name="gestor_quota_observations"
                                                    rows="4" placeholder='Escreva em detalhes o que diferencia a sua Cota para melhor atrair o público.
Ex: Vista mar ou do vale, acesso facilitado à piscina, à academia, se tem lareira ou adega, etc.
Possibilidade de serviços extras gratuitos, ou com desconto.
Seja o mais detalhista possivel'>{{ old('gestor_quota_observations', $isQuotaEdit ? ($quota->observations ?? '') : '') }}</textarea>
                                                @error('gestor_quota_observations')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()" style="display: none;">
                                        <i class="fas fa-arrow-left me-2"></i>Voltar
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg px-4" id="gestor_next_button" onclick="nextStep()">
                                        Próximo <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                    </div>

                    <!-- Step 2: Fracionamento da Cota -->
                    <div class="step d-none" id="step2">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-cut me-2"></i>Fracionamento da Cota
                                </h5>

                        @php
                            $userProfile = auth()->user()->profile;
                            $profileType = $userProfile->profile_type ?? 'curioso';
                        @endphp

                        <!-- Mensagem para Curioso -->
                        <div id="fraction_curioso" class="{{ $profileType === 'curioso' ? '' : 'd-none' }}">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Perfil Curioso:</strong> Neste perfil, você não fraciona sua cota. Você utilizará os 7 dias completos por período.
                                <hr>
                                <div class="small">
                                    <i class="fas fa-ban me-2"></i><strong>Fracionamento:</strong> Não permitido<br>
                                    <i class="fas fa-gavel me-2"></i><strong>Leilões:</strong> 3 no total durante a validade da cota<br>
                                    <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> 7 dias - até o fim do ano corrente<br>
                                    <i class="fas fa-simplicity me-2"></i><strong>Vantagem:</strong> Gestão simples e direta
                </div>
            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4 text-center">
                                    <i class="fas fa-calendar-week fa-3x text-primary mb-3"></i>
                                    <h5 class="fw-bold mb-2">Uso Integral</h5>
                                    <p class="text-muted mb-0">{{ \App\Models\SuccessFee::formatFractionPrices('curioso', '7') }}</p>
        </div>
    </div>
</div>

                        <!-- Opções para Inteligente -->
                        <div id="fraction_inteligente" class="{{ $profileType === 'inteligente' ? '' : 'd-none' }}">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Perfil Inteligente:</strong> Escolha como deseja fracionar seus 7 dias de cota
                                <hr>
                                <div class="small">
                                    <i class="fas fa-gavel me-2"></i><strong>Leilões:</strong> 2 por mês por tipo de uso<br>
                                    <i class="fas fa-map-marker-alt me-2"></i><strong>Alertas:</strong> 1 cidade por mês<br>
                                    <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> Até o fim do ano corrente
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
                                                </div>
                                </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opções para Sábio -->
                        <div id="fraction_sabio" class="{{ $profileType === 'sabio' ? '' : 'd-none' }}">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Perfil Sábio:</strong> Escolha a melhor combinação de fracionamento para seus 7 dias
                                <hr>
                                <div class="small">
                                    <i class="fas fa-gavel me-2"></i><strong>Leilões:</strong> 3 por mês por tipo de uso<br>
                                    <i class="fas fa-map-marker-alt me-2"></i><strong>Alertas:</strong> 3 cidades diferentes<br>
                                    <i class="fas fa-calendar-check me-2"></i><strong>Validade:</strong> Até o fim do ano corrente<br>
                                    <i class="fas fa-shuffle me-2"></i><strong>Flexibilidade:</strong> Pode alternar entre fracionar e não fracionar
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
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card fraction-card h-100" data-value="4_3">
                                        <div class="card-body">
                                            <input type="radio" name="fraction_type" id="fraction_4_3_sabio" value="4_3" class="form-check-input">
                                            <label for="fraction_4_3_sabio" class="fraction-label w-100">
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
                                    <div class="card fraction-card h-100" data-value="5_2">
                                        <div class="card-body">
                                            <input type="radio" name="fraction_type" id="fraction_5_2" value="5_2" class="form-check-input">
                                            <label for="fraction_5_2" class="fraction-label w-100">
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

                        <!-- Navigation Buttons for Step 2 -->
                        <div class="d-flex justify-content-between mt-4" id="step2-nav">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-primary btn-lg px-5" onclick="nextStep()">
                                <i class="fas fa-arrow-right me-2"></i>Próximo
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: conclusão (dados da oferta vêm do fluxo; backend completa com hotel/períodos do passo 1–2) -->
                    <div class="step d-none" id="step3">
                        <h5 class="fw-bold mb-4 text-primary">
                            <i class="fas fa-flag-checkered me-2"></i>Finalizar cadastro
                        </h5>

                        <input type="hidden" id="step3_hotel_name" name="hotel_name" value="{{ $isQuotaEdit ? e($quota->hotel_name) : '' }}">
                        <input type="hidden" id="fraction_details_json" name="fraction_details_json" value="{{ $isQuotaEdit ? e($fractionDetailsJsonForEdit) : '' }}">

                        <div class="alert alert-info border-0 shadow-sm mb-4 py-4 px-4" role="alert">
                            <p class="mb-0 fw-semibold text-center" style="font-size: 1.05rem;">
                                @if($isQuotaEdit)
                                    Clique em atualizar informações da cota para salvar as alterações.
                                @else
                                    Clique em concluir cadastro para efetuar o cadastro da nova cota.
                                @endif
                            </p>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="prevStep()">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </button>
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check me-2"></i>{{ $isQuotaEdit ? 'Atualizar informações da cota' : 'Concluir cadastro de nova cota' }}
                            </button>
                        </div>
                    </div>
                    </form>
            @if($isQuotaEdit)
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <a href="{{ route('quotas.show', $quota) }}" class="btn btn-outline-primary btn-sm quota-edit-back-btn">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

<style>
    .step {
        min-height: 400px;
        animation: fadeIn 0.5s ease-in-out;
    }

    .hotel-choice-warning-static {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        animation: none !important;
        position: relative;
        z-index: 2;
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
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
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

    /* Estilos para usos permitidos */
    .allowed-uses-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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

    /* Responsive */
    @media (max-width: 768px) {
        .quota-option-label,
        .hotel-option-label,
        .use-option-label {
            padding: 1rem;
            min-height: 70px;
        }

        .quota-option-icon,
        .hotel-option-icon,
        .use-option-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .allowed-uses-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .allowed-uses-container {
            grid-template-columns: 1fr;
        }
    }

    /* Estilos para autocomplete de hotel */
    .hotel-autocomplete-wrapper {
        width: 100%;
        position: relative;
    }

    .hotel-autocomplete-list {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
    }

    .hotel-autocomplete-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
        transition: background-color 0.2s ease;
        display: flex;
        align-items: center;
    }

    .hotel-autocomplete-item:last-child {
        border-bottom: none;
    }

    .hotel-autocomplete-item:hover,
    .hotel-autocomplete-item.active {
        background-color: rgba(0, 151, 57, 0.1);
        color: #009739;
    }

    .hotel-autocomplete-item i {
        color: #009739;
        width: 20px;
    }

    .hotel-name {
        font-weight: 600;
        margin-right: 0.5rem;
    }

    .hotel-location {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .hotel-autocomplete-no-results {
        color: #6c757d;
        font-style: italic;
        cursor: default;
    }

    .hotel-autocomplete-no-results:hover {
        background-color: transparent;
        color: #6c757d;
    }
</style>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let currentStep = 1;
    const totalSteps = 3;

    function getHasQuotaRadioValue() {
        if (window.isQuotaEdit) {
            const hidden = document.querySelector('input[type="hidden"][name="has_quota"]');
            if (hidden && hidden.value) {
                return hidden.value;
            }
        }
        const r = document.querySelector('input[name="has_quota"]:checked');
        return r ? r.value : '';
    }

    function setSectionEnabled(section, enabled) {
        if (!section) {
            return;
        }

        const fields = section.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            if (field.closest('.quota-immutable-on-edit')) {
                return;
            }
            // Ignorar campos hidden e radio buttons que não devem ser habilitados/desabilitados diretamente
            if (field.type === 'hidden') {
                return;
            }
            
            if (enabled) {
                // Remover atributo disabled e garantir que o campo esteja habilitado
                field.removeAttribute('disabled');
                field.disabled = false;
                
                if (field.dataset.disabledByToggle === 'true') {
                    delete field.dataset.disabledByToggle;
                }
                if (field.dataset.originalRequired === 'true') {
                    field.required = true;
                    delete field.dataset.originalRequired;
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

                // Na edição de cota, não limpar valores já renderizados (evita apagar hotel, quartos, etc. ao abrir a tela).
                if (window.isQuotaEdit) {
                    return;
                }

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

    function toggleQuotaSections(value) {
        const quotaOwnerSection = document.getElementById('quota_owner_section');
        const gestorSection = document.getElementById('gestor_section');
        const noQuotaWarning = document.getElementById('no_quota_warning');
        const ownerAdditionalFields = document.getElementById('owner_additional_fields');
        const gestorAdditionalFields = document.getElementById('gestor_additional_fields');

        // Se nenhum valor foi selecionado, oculta todos os formulários
        if (!value) {
            if (quotaOwnerSection) quotaOwnerSection.classList.add('d-none');
            if (gestorSection) gestorSection.classList.add('d-none');
            if (noQuotaWarning) noQuotaWarning.classList.add('d-none');
            if (ownerAdditionalFields) setSectionEnabled(ownerAdditionalFields, false);
            if (gestorAdditionalFields) setSectionEnabled(gestorAdditionalFields, false);
            return;
        }

        if (value === '1') {
            // Possui cota - Mostra seção do proprietário
            if (quotaOwnerSection) quotaOwnerSection.classList.remove('d-none');
            if (gestorSection) gestorSection.classList.add('d-none');
            if (noQuotaWarning) noQuotaWarning.classList.add('d-none');
            if (ownerAdditionalFields) {
                // Inicialmente desabilita até o hotel estar operacional
                setSectionEnabled(ownerAdditionalFields, false);
            }
            if (gestorAdditionalFields) setSectionEnabled(gestorAdditionalFields, false);

            // Se o rádio de hotel operacional já estiver marcado (padrão "Sim"),
            // aplicar imediatamente a lógica para habilitar os campos adicionais.
            const selectedHotelRadio = document.querySelector('input[name="hotel_operational"]:checked');
            if (selectedHotelRadio) {
                toggleOwnerFields(selectedHotelRadio.value);
            }
            // Inicializar semanas quando tiver cota
            if (typeof initializeQuotaWeeks === 'function') {
                setTimeout(() => {
                    initializeQuotaWeeks();
                }, 100);
            }
        } else if (value === '2') {
            // Gestor - Mostra seção do gestor
            if (quotaOwnerSection) quotaOwnerSection.classList.add('d-none');
            if (gestorSection) gestorSection.classList.remove('d-none');
            if (noQuotaWarning) noQuotaWarning.classList.add('d-none');
            if (ownerAdditionalFields) setSectionEnabled(ownerAdditionalFields, false);
            if (gestorAdditionalFields) {
                // Verificar se o hotel já está marcado como operacional
                const gestorHotelOperational = document.querySelector('input[name="gestor_hotel_operational"]:checked');
                if (gestorHotelOperational && gestorHotelOperational.value === '1') {
                    // Se já está operacional, habilita os campos
                    gestorAdditionalFields.classList.remove('d-none');
                    setSectionEnabled(gestorAdditionalFields, true);
                } else {
                    // Caso contrário, inicialmente desabilita até o hotel estar operacional
                    setSectionEnabled(gestorAdditionalFields, false);
                }
            }
            // Inicializar semanas quando for gestor
            if (typeof initializeQuotaWeeks === 'function') {
                setTimeout(() => {
                    initializeQuotaWeeks();
                }, 100);
            }
            // Gestor: só Alugar/Trocar — desabilita Vender/Comprar (ids do bloco gestor)
            (function enforceManagerUses() {
                const useSell = document.getElementById('gestor_use_sell');
                const useBuy = document.getElementById('gestor_use_buy');
                if (useSell) {
                    useSell.checked = false;
                    useSell.disabled = true;
                    useSell.required = false;
                    useSell.removeAttribute('name');
                    const card = useSell.closest('.use-option-card');
                    if (card) { card.classList.add('disabled'); card.classList.remove('selected'); }
                }
                if (useBuy) {
                    useBuy.checked = false;
                    useBuy.disabled = true;
                    useBuy.required = false;
                    useBuy.removeAttribute('name');
                    const card = useBuy.closest('.use-option-card');
                    if (card) { card.classList.add('disabled'); card.classList.remove('selected'); }
                }
                if (typeof toggleGestorAllowedUses === 'function') {
                    toggleGestorAllowedUses();
                }
            })();
        } else {
            // Não possui cota (fallback)
            if (quotaOwnerSection) quotaOwnerSection.classList.add('d-none');
            if (gestorSection) gestorSection.classList.add('d-none');
            if (noQuotaWarning) noQuotaWarning.classList.remove('d-none');
            if (ownerAdditionalFields) setSectionEnabled(ownerAdditionalFields, false);
            if (gestorAdditionalFields) setSectionEnabled(gestorAdditionalFields, false);
        }
    }

    function toggleOwnerFields(value) {
        const ownerAdditionalFields = document.getElementById('owner_additional_fields');
        const hotelNotOperationalNotice = document.getElementById('hotel_not_operational_notice');
        const ownerNextButton = document.getElementById('owner_next_button');

        if (value === '1') {
            // Hotel operacional - Habilita todos os campos
            if (ownerAdditionalFields) {
                ownerAdditionalFields.classList.remove('d-none');
                setSectionEnabled(ownerAdditionalFields, true);
            }
            if (hotelNotOperationalNotice) {
                hotelNotOperationalNotice.classList.add('d-none');
            }
            if (ownerNextButton) {
                ownerNextButton.disabled = false;
            }
            // Renderizar blocos de semanas quando hotel estiver operacional
            if (typeof renderWeekBlocks === 'function') {
                setTimeout(() => {
                    renderWeekBlocks('owner', window.initialOwnerWeeks || {});
                }, 100);
            }
        } else {
            // Hotel não operacional - Desabilita campos e mostra aviso
            if (ownerAdditionalFields) {
                ownerAdditionalFields.classList.add('d-none');
                setSectionEnabled(ownerAdditionalFields, false);
            }
            if (hotelNotOperationalNotice) {
                hotelNotOperationalNotice.classList.remove('d-none');
            }
            if (ownerNextButton) {
                ownerNextButton.disabled = true;
            }
        }
    }

    /** Igual ao cadastro: usos permitidos do gestor conforme status da cota + bloqueio vender/comprar */
    function toggleGestorAllowedUses() {
        if (window.isQuotaEdit) {
            return;
        }
        const quotaStatus = document.getElementById('gestor_quota_status');
        const gestorUseRent = document.getElementById('gestor_use_rent');
        const gestorUseExchange = document.getElementById('gestor_use_exchange');
        const gestorUseSell = document.getElementById('gestor_use_sell');
        const gestorUseBuy = document.getElementById('gestor_use_buy');
        const gestorSection = document.getElementById('gestor_section');

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

        const hasQuotaSelected = getHasQuotaRadioValue();
        const isGestorSelected = hasQuotaSelected === '2';
        const isGestorSectionVisible = gestorSection && !gestorSection.classList.contains('d-none');
        const selectedStatus = quotaStatus.value;

        [gestorUseRent, gestorUseExchange].forEach(checkbox => {
            if (!checkbox) return;
            if (isGestorSelected && isGestorSectionVisible && (selectedStatus === 'unpaid' || selectedStatus === 'paid')) {
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

    function toggleGestorFields(value) {
        const gestorAdditionalFields = document.getElementById('gestor_additional_fields');
        const gestorHotelNotOperationalNotice = document.getElementById('gestor_hotel_not_operational_notice');
        const gestorNextButton = document.getElementById('gestor_next_button');

        if (value === '1') {
            // Hotel operacional - Habilita todos os campos
            if (gestorAdditionalFields) {
                gestorAdditionalFields.classList.remove('d-none');
                
                // Primeiro, habilitar usando a função setSectionEnabled
                setSectionEnabled(gestorAdditionalFields, true);
                
                // Depois, forçar habilitação de todos os campos (garantir que nenhum fique desabilitado)
                const enableFields = () => {
                    const fields = gestorAdditionalFields.querySelectorAll('input, select, textarea, button');
                    fields.forEach(field => {
                        if (field.closest('.quota-immutable-on-edit')) {
                            return;
                        }
                        // Ignorar campos hidden e radio buttons
                        if (field.type === 'hidden' || field.type === 'radio') {
                            return;
                        }
                        
                        // Remover todos os atributos que podem desabilitar o campo
                        field.disabled = false;
                        field.removeAttribute('disabled');
                        field.removeAttribute('readonly');
                        field.classList.remove('disabled');
                        
                        // Garantir que campos required mantenham o atributo required
                        if (field.hasAttribute('data-original-required') || field.hasAttribute('required')) {
                            field.required = true;
                        }
                    });
                };
                
                // Executar imediatamente e depois com um pequeno delay para garantir
                enableFields();
                setTimeout(enableFields, 50);
                setTimeout(enableFields, 150);
                setTimeout(enableFields, 300);
            }
            if (gestorHotelNotOperationalNotice) {
                gestorHotelNotOperationalNotice.classList.add('d-none');
            }
            if (gestorNextButton) {
                gestorNextButton.disabled = false;
            }
            // Renderizar blocos de semanas quando hotel estiver operacional
            if (typeof renderWeekBlocks === 'function') {
                setTimeout(() => {
                    renderWeekBlocks('gestor', window.initialGestorWeeks || {});
                }, 100);
            }
            // Reaplica regra Quitada → oculta prazo (enableFields pode ter rodado depois do toggle inicial)
            const gst = document.getElementById('gestor_quota_status');
            if (gst) {
                gst.dispatchEvent(new Event('change'));
            }
            setTimeout(function() {
                toggleGestorAllowedUses();
            }, 350);
        } else {
            // Hotel não operacional - Desabilita campos e mostra aviso
            if (gestorAdditionalFields) {
                gestorAdditionalFields.classList.add('d-none');
                setSectionEnabled(gestorAdditionalFields, false);
            }
            if (gestorHotelNotOperationalNotice) {
                gestorHotelNotOperationalNotice.classList.remove('d-none');
            }
            if (gestorNextButton) {
                gestorNextButton.disabled = true;
            }
        }
    }

    function syncStep3HotelNameFromStep1() {
        const step3HotelName = document.getElementById('step3_hotel_name');
        if (!step3HotelName) {
            return;
        }
        const mode = getHasQuotaRadioValue() || '1';
        const sel = mode === '2'
            ? document.getElementById('gestor_hotel_id')
            : document.getElementById('owner_hotel_id');
        if (!sel || !sel.value) {
            step3HotelName.value = '';
            return;
        }
        const opt = sel.options[sel.selectedIndex];
        const text = (opt && (opt.textContent || '')).trim();
        step3HotelName.value = text.split(' - ')[0].trim();
    }

    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                document.getElementById(`step${currentStep}`).classList.add('d-none');
                currentStep++;
                document.getElementById(`step${currentStep}`).classList.remove('d-none');
                
                // Se for Step 2, inicializar os cards de fracionamento
                if (currentStep === 2) {
                    initializeFractionCards();
                }
                
                // Se for Step 3, ajustar navegação e copiar hotel_name do passo 1
                if (currentStep === 3) {
                    const step2Nav = document.getElementById('step2-nav');
                    if (step2Nav) {
                        step2Nav.classList.add('d-none');
                    }
                    syncStep3HotelNameFromStep1();
                    if (typeof updateFractionDebugPanel === 'function') {
                        updateFractionDebugPanel();
                    }
                } else {
                    const step2Nav = document.getElementById('step2-nav');
                    if (step2Nav) {
                        step2Nav.classList.remove('d-none');
                    }
                }
            }
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            document.getElementById(`step${currentStep}`).classList.add('d-none');
            currentStep--;
            document.getElementById(`step${currentStep}`).classList.remove('d-none');
            
            // Ajustar navegação
            if (currentStep === 2) {
                const step2Nav = document.getElementById('step2-nav');
                if (step2Nav) {
                    step2Nav.classList.remove('d-none');
                }
            }
        }
    }

    function validateCurrentStep() {
        const currentStepElement = document.getElementById(`step${currentStep}`);
        if (!currentStepElement) {
            console.error('Step element not found:', `step${currentStep}`);
            return false;
        }

        // Remover mensagens de erro anteriores
        const existingErrors = currentStepElement.querySelectorAll('.validation-error-message');
        existingErrors.forEach(err => err.remove());

        // Validação especial para Step 1 - verificar se tem cota foi selecionado
        if (currentStep === 1) {
            const hasQuotaVal = getHasQuotaRadioValue();
            if (!hasQuotaVal) {
                showValidationError(currentStepElement, 'Por favor, selecione uma opção em "Possuo Cota Hoteleira?"');
                return false;
            }

            if (window.isQuotaEdit) {
                const hotelSel = hasQuotaVal === '2'
                    ? document.getElementById('gestor_hotel_id')
                    : document.getElementById('owner_hotel_id');
                if (!hotelSel || !hotelSel.value) {
                    showValidationError(currentStepElement, 'Por favor, selecione um hotel.');
                    return false;
                }
                const opSel = hasQuotaVal === '2'
                    ? document.querySelector('input[name="gestor_hotel_operational"]:checked')
                    : document.querySelector('input[name="hotel_operational"]:checked');
                if (!opSel || opSel.value !== '1') {
                    showValidationError(currentStepElement, 'Indique que o hotel está em funcionamento para continuar.');
                    return false;
                }
                return true;
            }

            // Se tem cota, verificar se hotel operacional foi selecionado
            if (hasQuotaVal === '1') {
                const hotelOperationalRadio = document.querySelector('input[name="hotel_operational"]:checked');
                if (!hotelOperationalRadio) {
                    showValidationError(currentStepElement, 'Por favor, selecione se o hotel está em funcionamento');
                    return false;
                }

                // Se hotel não está operacional, não permitir avançar
                if (hotelOperationalRadio.value === '0') {
                    showValidationError(currentStepElement, 'Não é possível cadastrar cotas quando o hotel não está em funcionamento.');
                    return false;
                }

                // Verificar se owner_additional_fields está visível e habilitado
                const ownerAdditionalFields = document.getElementById('owner_additional_fields');
                if (ownerAdditionalFields && ownerAdditionalFields.classList.contains('d-none')) {
                    showValidationError(currentStepElement, 'Os campos adicionais não estão disponíveis. Tente novamente.');
                    return false;
                }
            }

            // Se é gestor, verificar se hotel operacional foi selecionado
            if (hasQuotaVal === '2') {
                const gestorHotelOperationalRadio = document.querySelector('input[name="gestor_hotel_operational"]:checked');
                if (!gestorHotelOperationalRadio) {
                    showValidationError(currentStepElement, 'Por favor, selecione se o hotel está em funcionamento');
                    return false;
                }

                // Se hotel não está operacional, não permitir avançar
                if (gestorHotelOperationalRadio.value === '0') {
                    showValidationError(currentStepElement, 'Não é possível cadastrar cotas quando o hotel não está em funcionamento.');
                    return false;
                }

                // Verificar se gestor_additional_fields está visível e habilitado
                const gestorAdditionalFields = document.getElementById('gestor_additional_fields');
                if (gestorAdditionalFields && gestorAdditionalFields.classList.contains('d-none')) {
                    showValidationError(currentStepElement, 'Os campos adicionais não estão disponíveis. Tente novamente.');
                    return false;
                }
            }
        }

        // Step 3: apenas hotel (nome) vindo do passo 1 e JSON de fracionamento atualizado
        if (currentStep === 3) {
            syncStep3HotelNameFromStep1();
            if (typeof updateFractionDebugPanel === 'function') {
                updateFractionDebugPanel();
            }
            const step3HotelName = document.getElementById('step3_hotel_name');
            if (!step3HotelName || !step3HotelName.value.trim()) {
                showValidationError(currentStepElement, 'Por favor, selecione um hotel no primeiro passo.');
                return false;
            }
        }

        // Buscar todos os campos obrigatórios, mas filtrar apenas os visíveis e habilitados
        let requiredFields = Array.from(currentStepElement.querySelectorAll('[required]'));
        
        // Filtrar campos que estão ocultos ou desabilitados
        requiredFields = requiredFields.filter(field => {
            // Ignorar campos dentro de elementos ocultos
            const parentHidden = field.closest('.d-none');
            if (parentHidden) {
                return false;
            }
            
            // Ignorar campos desabilitados
            if (field.disabled) {
                return false;
            }
            
            // Ignorar campos hidden (a menos que sejam necessários)
            if (field.type === 'hidden' && !field.hasAttribute('data-required')) {
                return false;
            }
            
            // Verificar se o campo está realmente visível (checking display style)
            const style = window.getComputedStyle(field);
            if (style.display === 'none' || style.visibility === 'hidden') {
                return false;
            }
            
            // Verificar se o campo dos quartos está visível
            // Se owner_quota_rooms está vazio ou 0, não validar campos dos quartos
            if (field.name && field.name.startsWith('owner_room_')) {
                const ownerQuotaRooms = document.getElementById('owner_quota_rooms');
                if (!ownerQuotaRooms || !ownerQuotaRooms.value || ownerQuotaRooms.value === '' || ownerQuotaRooms.value === '0') {
                    return false;
                }
                
                // Verificar se a seção de quartos está visível
                const roomsConfig = document.getElementById('rooms-configuration');
                if (!roomsConfig || roomsConfig.classList.contains('d-none')) {
                    return false;
                }
            }
            
            return true;
        });
        
        let firstInvalidField = null;
        
        for (let field of requiredFields) {
            let isValid = true;

            if (field.type === 'radio') {
                const radioName = field.name;
                const isChecked = document.querySelector(`input[name="${radioName}"]:checked`);
                if (!isChecked) {
                    isValid = false;
                    firstInvalidField = field;
                }
            } else if (field.type === 'checkbox') {
                const checkboxName = field.name;
                const visibleCheckboxes = Array.from(document.querySelectorAll(`input[name="${checkboxName}"]`))
                    .filter(cb => !cb.closest('.d-none') && !cb.disabled);
                const anyChecked = visibleCheckboxes.some(cb => cb.checked);
                if (!anyChecked) {
                    isValid = false;
                    firstInvalidField = visibleCheckboxes.length > 0 ? visibleCheckboxes[0] : field;
                }
            } else if (field.type === 'file') {
                if (field.files.length === 0) {
                    isValid = false;
                    firstInvalidField = field;
                }
            } else if (field.tagName === 'SELECT') {
                if (!field.value || field.value === '') {
                    isValid = false;
                    firstInvalidField = field;
                }
            } else if (field.tagName === 'TEXTAREA') {
                if (!field.value.trim()) {
                    isValid = false;
                    firstInvalidField = field;
                }
            } else {
                if (!field.value || !field.value.trim()) {
                    isValid = false;
                    firstInvalidField = field;
                }
            }

            // Marcar campo como inválido ou válido
            if (!isValid) {
                field.classList.add('is-invalid');
                // Scroll para o primeiro campo inválido
                if (firstInvalidField && !firstInvalidField.scrolledIntoView) {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidField.focus();
                    firstInvalidField.scrolledIntoView = true;
                    setTimeout(() => {
                        firstInvalidField.scrolledIntoView = false;
                    }, 1000);
                }
            } else {
                field.classList.remove('is-invalid');
            }
        }

        if (firstInvalidField) {
            const fieldLabel = firstInvalidField.previousElementSibling?.textContent?.trim() || 
                              firstInvalidField.closest('.form-group')?.querySelector('label')?.textContent?.trim() ||
                              firstInvalidField.closest('.col-md-12')?.querySelector('label')?.textContent?.trim() ||
                              firstInvalidField.name;
            showValidationError(currentStepElement, `Por favor, preencha o campo: ${fieldLabel}`);
            return false;
        }

        return true;
    }

    function showValidationError(container, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-3 validation-error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
        container.insertBefore(errorDiv, container.firstChild);
        
        // Scroll para a mensagem de erro
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Remover após 5 segundos
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }

    // Função para interação com cards de fracionamento
    function initializeFractionCards() {
        const fractionCards = document.querySelectorAll('.fraction-card');

        fractionCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('selected');
            }

            card.addEventListener('click', function() {
                fractionCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    // Aplicar o tipo de fracionamento selecionado a todas as semanas autorizadas
                    updateAuthorizedWeeks();
                    // Atualizar calendários quando um tipo de fracionamento é selecionado
                    refreshCalendarIfNeeded();
                }
            });

            const radioButton = card.querySelector('input[type="radio"]');
            if (radioButton) {
                radioButton.addEventListener('change', function() {
                    if (this.checked) {
                        fractionCards.forEach(c => c.classList.remove('selected'));
                        card.classList.add('selected');
                        // Aplicar o tipo de fracionamento selecionado a todas as semanas autorizadas
                        updateAuthorizedWeeks();
                        // Atualizar calendários quando um tipo de fracionamento é selecionado
                        refreshCalendarIfNeeded();
                    }
                });
            }
        });
    }

    // Função para inicializar configuração de quartos
    function initializeRoomsConfiguration() {
        const ownerRoomsSelect = document.getElementById('owner_quota_rooms');
        if (ownerRoomsSelect) {
            // Atualizar quando o valor mudar
            ownerRoomsSelect.addEventListener('change', function() {
                updateRoomsConfiguration('owner', this.value);
            });
            
            // Inicializar com o valor atual (se houver old value)
            if (ownerRoomsSelect.value) {
                updateRoomsConfiguration('owner', ownerRoomsSelect.value);
            }
        }
    }

    // Inicializar semanas
    window.initialOwnerWeeks = <?php echo json_encode(old('owner_weeks', $isQuotaEdit ? $initialOwnerWeeksForEdit : [])); ?>;
    window.initialGestorWeeks = <?php echo json_encode(old('gestor_weeks', $isQuotaEdit ? $initialOwnerWeeksForEdit : [])); ?>;
    window.isQuotaEdit = <?php echo json_encode((bool) $isQuotaEdit); ?>;
    window.quotaEditFractionType = <?php echo json_encode($isQuotaEdit ? (($quota->fraction_details ?? [])['fraction_type'] ?? null) : null); ?>;

    function initializeQuotaWeeks() {
        const weekData = {
            owner: window.initialOwnerWeeks || {},
        };

        ['owner'].forEach(type => {
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
        const sourceData = Object.keys(initialData).length ? initialData : (window.initialOwnerWeeks || {});

        container.innerHTML = '';

        if (Number.isNaN(count) || count <= 0) {
            updateAuthorizedWeeks();
            return;
        }

        for (let weekNumber = 1; weekNumber <= count; weekNumber++) {
            const weekData = sourceData && sourceData[weekNumber] ? sourceData[weekNumber] : {};
            const block = createWeekBlockElement(type, weekNumber, weekData);
            container.appendChild(block);
            setupWeekBlockInteractions(type, weekNumber, weekData);
        }

        updateAuthorizedWeeks();
        updateOwnerNextButton();
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
            let options = '<option value="">Selecione</option>';
            for (let day = 1; day <= 31; day++) {
                const value = day.toString().padStart(2, '0');
                const isSelected = selected === value ? 'selected' : '';
                options += `<option value="${value}" ${isSelected}>${value}</option>`;
            }
            return options;
        };

        const buildMonthOptions = (selected) => {
            let options = '<option value="">Selecione</option>';
            for (let month = 1; month <= 12; month++) {
                const value = month.toString().padStart(2, '0');
                const isSelected = selected === value ? 'selected' : '';
                options += `<option value="${value}" ${isSelected}>${value}</option>`;
            }
            return options;
        };

        const buildYearOptions = (selected) => {
            const currentYear = new Date().getFullYear();
            const nextYear = currentYear + 1;
            const years = [currentYear.toString(), nextYear.toString()];
            let options = '<option value="">Selecione</option>';
            years.forEach(year => {
                const isSelected = selected === year ? 'selected' : '';
                options += `<option value="${year}" ${isSelected}>${year}</option>`;
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
                                Uma foto do documento oficial de distribuição das semanas para uso no seu hotel
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
        const weekFields = document.querySelector(`[data-week-fields="${weekNumber}"]`);
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
                return;
            }

            const startValue = startDaySelect.value;
            const monthValue = monthSelect ? monthSelect.value : '';
            const yearValue = yearSelect ? yearSelect.value : '';

            if (!startValue) {
                if (endDayDisplay) endDayDisplay.value = '';
                if (endDayHidden) endDayHidden.value = '';
                return;
            }

            let endValue = calculateEndDay(startValue);

            if (monthValue && yearValue) {
                const startDate = new Date(`${yearValue}-${monthValue}-${startValue}T00:00:00`);
                if (!Number.isNaN(startDate.getTime())) {
                    const endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6); // 7 dias = adiciona 6 dias (dia inicial + 6 dias seguintes)
                    endValue = String(endDate.getDate()).padStart(2, '0');
                }
            }

            if (endDayDisplay) {
                endDayDisplay.value = endValue;
            }
            if (endDayHidden) {
                endDayHidden.value = endValue;
            }
        };

        if (authorizeSelect) {
            authorizeSelect.addEventListener('change', () => {
                toggleWeekFields();
                updateEndDay();
                updateAuthorizedWeeks();
                updateOwnerNextButton();
            });
        }

        if (startDaySelect) {
            startDaySelect.addEventListener('change', updateEndDay);
        }

        if (monthSelect) {
            monthSelect.addEventListener('change', updateEndDay);
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', updateEndDay);
        }

        toggleWeekFields();
        updateEndDay();
        updateOwnerNextButton();
    }

    function calculateEndDay(startDay) {
        if (!startDay) {
            return '';
        }

        const start = parseInt(startDay, 10);
        if (Number.isNaN(start)) {
            return '';
        }

        let end = start + 7;
        if (end > 31) {
            end = ((end - 1) % 31) + 1;
        }

        return end.toString().padStart(2, '0');
    }

    function updateAuthorizedWeeks() {
        window.authorizedWeeksData = window.authorizedWeeksData || {
            owner: []
        };

        const container = document.getElementById('owner_weeks_container');
        if (!container) {
            window.authorizedWeeksData.owner = [];
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

        window.authorizedWeeksData.owner = authorized;
        
        // Sempre aplicar o tipo de fracionamento selecionado a todas as semanas autorizadas
        refreshCalendarIfNeeded();
    }

    function updateOwnerNextButton() {
        const ownerNextButton = document.getElementById('owner_next_button');
        if (!ownerNextButton) {
            return;
        }

        const container = document.getElementById('owner_weeks_container');
        if (!container) {
            ownerNextButton.disabled = false;
            return;
        }

        const selects = container.querySelectorAll('select[data-week-authorize="true"]');
        let hasUnauthorizedWeek = false;

        selects.forEach(select => {
            if (select.value === 'no') {
                hasUnauthorizedWeek = true;
            }
        });

        // Bloqueia o botão se houver alguma semana com "Não"
        ownerNextButton.disabled = hasUnauthorizedWeek;
    }

    function getCurrentWeekType() {
        return 'owner';
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
        return window.authorizedWeeksData && window.authorizedWeeksData.owner ? window.authorizedWeeksData.owner : [];
    }

    function refreshCalendarIfNeeded() {
        const selectedFraction = document.querySelector('input[name="fraction_type"]:checked');
        const calendarFields = document.getElementById('calendar-fields');
        const calendarContainers = document.getElementById('calendar-containers');
        
        if (!calendarFields || !calendarContainers) return;
        
        if (selectedFraction && selectedFraction.value) {
            // Qualquer tipo selecionado (inclui "7" = sem fracionar: um período de 7 dias)
            calendarFields.classList.remove('d-none');
            
            const authorizedWeeks = getAuthorizedWeeksForCurrentProfile();
            if (authorizedWeeks.length === 0) {
                calendarContainers.innerHTML = '<div class="alert alert-warning">Nenhuma semana autorizada encontrada. Por favor, autorize pelo menos uma semana no passo anterior.</div>';
                return;
            }
            
            calendarContainers.innerHTML = '';
            
            authorizedWeeks.forEach(weekNumber => {
                createCalendarFieldsForWeek(weekNumber, selectedFraction.value);
            });
            
            attachFractionDebugListeners();
        } else {
            calendarFields.classList.add('d-none');
            calendarContainers.innerHTML = '';
        }
    }
    
    // Função para criar campos de calendário para uma semana específica
    function createCalendarFieldsForWeek(weekNumber, fractionValue) {
        const perWeekWrapper = document.getElementById(`fraction_week_${weekNumber}_calendar_wrapper`);
        const calendarFields = document.getElementById('calendar-fields');
        const calendarContainers = document.getElementById('calendar-containers');
        const targetContainer = perWeekWrapper || calendarContainers;
        if (!targetContainer) return;
        if (calendarFields) {
            if (perWeekWrapper) calendarFields.classList.add('d-none'); else calendarFields.classList.remove('d-none');
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
            const showSellOptionCreate = getCurrentWeekType() === 'owner' && isIntegral;
            const gestorPeriodActionOptionsCreate = '<option value="">Selecione uma opção</option><option value="rent">Alugar</option><option value="exchange">Trocar</option><option value="rent_exchange">Alugar e Trocar</option>';
            const ownerPeriodActionOptionsCreate = '<option value="">Selecione uma opção</option><option value="rent">Alugar</option><option value="exchange">Trocar</option><option value="rent_exchange">Alugar e Trocar</option>' + (showSellOptionCreate ? '<option value="sell">Vender</option>' : '');
            
            const startValue = currentStartDate ? formatDateInput(currentStartDate) : '';
            let computedEndDate = currentStartDate ? new Date(currentStartDate.getTime()) : null;
            if (computedEndDate) {
                // Regra: dia inicial + número de dias = dia final
                const increment = days;
                computedEndDate.setDate(computedEndDate.getDate() + increment);
            }
            const endValue = computedEndDate ? formatDateInput(computedEndDate) : '';
            
            const periodCard = document.createElement('div');
            periodCard.className = 'col-md-6';
            periodCard.innerHTML = `
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>Período ${periodNumber} (${days} ${days === 1 ? 'dia' : 'dias'})
                        </h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="fraction_weeks[${weekNumber}][periods][${periodNumber}][enabled]" value="0">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       name="fraction_weeks[${weekNumber}][periods][${periodNumber}][enabled]"
                                       id="${periodIdBase}_enabled"
                                       value="1">
                                <label class="form-check-label fw-semibold" for="${periodIdBase}_enabled">
                                    <i class="fas fa-toggle-on me-2 text-primary"></i>Desejo alugar ou trocar este período
                                </label>
                            </div>
                            <div id="${periodIdBase}_action_div" class="d-none mt-3">
                                <label class="form-label fw-semibold d-flex align-items-center mb-2">
                                    <i class="fas fa-hand-holding-usd me-2 text-success"></i>Ação para este período *
                                </label>
                                <select class="form-select" 
                                        name="fraction_weeks[${weekNumber}][periods][${periodNumber}][action]"
                                        id="${periodIdBase}_action_select">
                                    ${getCurrentWeekType() === 'gestor' ? gestorPeriodActionOptionsCreate : ownerPeriodActionOptionsCreate}
                                </select>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-flex align-items-center">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Data de Início *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="fraction_weeks[${weekNumber}][periods][${periodNumber}][start]"
                                       id="${periodIdBase}_start"
                                       value="${startValue}"
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
                                       value="${endValue}"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            rowsContainer.appendChild(periodCard);
            
            // Adicionar listeners para o checkbox enabled
            setTimeout(() => {
                const enabledCheckbox = document.getElementById(`${periodIdBase}_enabled`);
                const actionDiv = document.getElementById(`${periodIdBase}_action_div`);
                const actionSelect = document.getElementById(`${periodIdBase}_action_select`);
                
                if (enabledCheckbox && actionDiv) {
                    enabledCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            actionDiv.classList.remove('d-none');
                            if (actionSelect) {
                                actionSelect.required = true;
                            }
                        } else {
                            actionDiv.classList.add('d-none');
                            if (actionSelect) {
                                actionSelect.required = false;
                                actionSelect.value = '';
                            }
                        }
                        updateFractionDebugPanel();
                    });
                }
            }, 100);
            
            // Atualizar data inicial para o próximo período
            if (computedEndDate) {
                currentStartDate = new Date(computedEndDate.getTime());
            }
        });
        
        // Atualizar listeners
        attachFractionDebugListeners();
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
                el.addEventListener('change', function() {
                    updateFractionDebugPanel();
                    // Se for mudança no fraction_type, atualizar calendários
                    if (el.name === 'fraction_type') {
                        refreshCalendarIfNeeded();
                    }
                });
                el.addEventListener('input', updateFractionDebugPanel);
                el.dataset._fractionDebugBound = '1';
            }
        });
    }
    
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

    function updateRoomsConfiguration(type, roomCount) {
        const configDiv = document.getElementById('rooms-configuration');
        const container = document.getElementById('rooms-container');
        const ownerAdditionalFields = document.getElementById('owner_additional_fields');

        if (!configDiv || !container) return;

        container.innerHTML = '';

        if (roomCount && parseInt(roomCount) > 0) {
            // Garantir que a seção owner_additional_fields esteja habilitada
            if (ownerAdditionalFields) {
                setSectionEnabled(ownerAdditionalFields, true);
            }
            
            configDiv.classList.remove('d-none');
            for (let i = 1; i <= parseInt(roomCount); i++) {
                const roomBlock = createRoomBlock(type, i);
                container.appendChild(roomBlock);
            }
            
            // Garantir que todos os campos dos quartos estejam habilitados e requeridos
            const roomFields = configDiv.querySelectorAll('input, select, textarea');
            roomFields.forEach(field => {
                field.disabled = false;
                field.removeAttribute('readonly');
                // Remover qualquer atributo que impeça o envio
                if (field.type === 'hidden' || field.type === 'file') {
                    // Manter como está
                } else {
                    // Garantir que campos requeridos estejam marcados
                    if (field.hasAttribute('data-original-required') || field.hasAttribute('required')) {
                        field.required = true;
                    }
                }
            });
        } else {
            configDiv.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeRoomsConfiguration();
        initializeQuotaWeeks();

        if (window.isQuotaEdit) {
            const hqVal = getHasQuotaRadioValue();
            if (hqVal && typeof toggleQuotaSections === 'function') {
                toggleQuotaSections(hqVal);
            }
            setTimeout(function() {
                if (hqVal === '1') {
                    const op1 = document.querySelector('input[name="hotel_operational"][value="1"]');
                    if (op1) {
                        op1.checked = true;
                        op1.dispatchEvent(new Event('change'));
                    }
                } else if (hqVal === '2') {
                    const gop = document.querySelector('input[name="gestor_hotel_operational"][value="1"]');
                    if (gop) {
                        gop.checked = true;
                        gop.dispatchEvent(new Event('change'));
                    }
                }
            }, 80);
            setTimeout(function() {
                const ft = window.quotaEditFractionType;
                if (ft) {
                    const r = document.querySelector('input[name="fraction_type"][value="' + String(ft).replace(/"/g, '') + '"]');
                    if (r) {
                        r.checked = true;
                        r.dispatchEvent(new Event('change'));
                        document.querySelectorAll('.fraction-card').forEach(function(c) { c.classList.remove('selected'); });
                        const card = r.closest('.fraction-card');
                        if (card) {
                            card.classList.add('selected');
                        }
                    }
                }
                if (typeof initializeFractionCards === 'function') {
                    initializeFractionCards();
                }
                if (typeof refreshCalendarIfNeeded === 'function') {
                    refreshCalendarIfNeeded();
                }
            }, 450);
        }

        // enforce manager allowed uses on load (in case "gestor" preselected)
        (function enforceManagerUsesOnLoad(){
            const selectedQuotaVal = getHasQuotaRadioValue();
            if (selectedQuotaVal === '2') {
                const useSell = document.getElementById('gestor_use_sell');
                const useBuy = document.getElementById('gestor_use_buy');
                if (useSell) {
                    useSell.checked = false;
                    useSell.disabled = true;
                    useSell.required = false;
                    useSell.removeAttribute('name');
                    const card = useSell.closest('.use-option-card');
                    if (card) { card.classList.add('disabled'); card.classList.remove('selected'); }
                }
                if (useBuy) {
                    useBuy.checked = false;
                    useBuy.disabled = true;
                    useBuy.required = false;
                    useBuy.removeAttribute('name');
                    const card = useBuy.closest('.use-option-card');
                    if (card) { card.classList.add('disabled'); card.classList.remove('selected'); }
                }
                if (typeof toggleGestorAllowedUses === 'function') {
                    toggleGestorAllowedUses();
                }
            }
        })();

        const gestorQuotaStatusEl = document.getElementById('gestor_quota_status');
        if (gestorQuotaStatusEl) {
            gestorQuotaStatusEl.addEventListener('change', toggleGestorAllowedUses);
        }

        // Quota selection
        const quotaRadios = document.querySelectorAll(window.isQuotaEdit ? '#step1 .quota-selection-container input[type="radio"]' : 'input[name="has_quota"]');
        quotaRadios.forEach((radio) => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.quota-option-card').forEach(card => {
                    card.classList.remove('selected');
                });

                const currentCard = this.closest('.quota-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }

                toggleQuotaSections(this.value);
            });
        });

        // Add click listeners to quota cards
        document.querySelectorAll('.quota-option-card').forEach(card => {
            card.addEventListener('click', function() {
                if (window.isQuotaEdit) {
                    return;
                }
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
                document.querySelectorAll('.hotel-option-card').forEach(card => {
                    card.classList.remove('selected');
                });

                const currentCard = this.closest('.hotel-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }

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
            toggleOwnerFields(selectedHotelRadio.value);
        }

        // Gestor hotel operational selection
        const gestorHotelRadios = document.querySelectorAll('input[name="gestor_hotel_operational"]');
        gestorHotelRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('#gestor_section .hotel-option-card').forEach(card => {
                    card.classList.remove('selected');
                });

                const currentCard = this.closest('.hotel-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }

                // Forçar habilitação dos campos quando hotel estiver operacional
                if (this.value === '1') {
                    toggleGestorFields(this.value);
                    // Garantir que os campos sejam habilitados mesmo após um pequeno delay
                    setTimeout(() => {
                        const gestorAdditionalFields = document.getElementById('gestor_additional_fields');
                        if (gestorAdditionalFields) {
                            const fields = gestorAdditionalFields.querySelectorAll('input, select, textarea');
                            fields.forEach(field => {
                                if (field.closest('.quota-immutable-on-edit')) {
                                    return;
                                }
                                if (field.type !== 'hidden' && field.type !== 'radio') {
                                    field.disabled = false;
                                    field.removeAttribute('disabled');
                                    field.removeAttribute('readonly');
                                }
                            });
                        }
                    }, 150);
                } else {
                    toggleGestorFields(this.value);
                }
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
        const selectedGestorHotelRadio = document.querySelector('input[name="gestor_hotel_operational"]:checked');
        if (selectedGestorHotelRadio) {
            const currentCard = selectedGestorHotelRadio.closest('.hotel-option-card');
            if (currentCard) {
                currentCard.classList.add('selected');
            }
            // Aguardar um pouco para garantir que a seção do gestor esteja visível
            setTimeout(() => {
                toggleGestorFields(selectedGestorHotelRadio.value);
            }, 100);
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

        // Gestor allowed uses selection
        const gestorAllowedUseCheckboxes = document.querySelectorAll('input[name="gestor_allowed_uses[]"]');
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

        // Initialize allowed uses selection
        allowedUseCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const currentCard = checkbox.closest('.use-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }
            }
        });

        gestorAllowedUseCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const currentCard = checkbox.closest('.use-option-card');
                if (currentCard) {
                    currentCard.classList.add('selected');
                }
            }
        });

        // Initialize with current selection
        const selectedRadioVal = getHasQuotaRadioValue();
        if (selectedRadioVal) {
            const selectedRadio = document.querySelector('#step1 .quota-selection-container input[type="radio"]:checked')
                || document.querySelector('input[name="has_quota"]:checked');
            const currentCard = selectedRadio ? selectedRadio.closest('.quota-option-card') : null;
            if (currentCard) {
                currentCard.classList.add('selected');
            }
            if (!currentCard && window.isQuotaEdit) {
                document.querySelectorAll('#step1 .quota-selection-container input[type="radio"]').forEach(function(r) {
                    if (r.value === selectedRadioVal) {
                        const c = r.closest('.quota-option-card');
                        if (c) {
                            c.classList.add('selected');
                        }
                    }
                });
            }
            toggleQuotaSections(selectedRadioVal);
            
            // Se já tem cota selecionada, verificar hotel operacional
            if (selectedRadioVal === '1') {
                const selectedHotelRadio = document.querySelector('input[name="hotel_operational"]:checked');
                if (selectedHotelRadio) {
                    const currentHotelCard = selectedHotelRadio.closest('.hotel-option-card');
                    if (currentHotelCard) {
                        currentHotelCard.classList.add('selected');
                    }
                    toggleOwnerFields(selectedHotelRadio.value);
                }
            } else if (selectedRadioVal === '2') {
                // Se é gestor, verificar hotel operacional do gestor
                setTimeout(() => {
                    const selectedGestorHotelRadio = document.querySelector('input[name="gestor_hotel_operational"]:checked');
                    if (selectedGestorHotelRadio) {
                        const currentGestorHotelCard = selectedGestorHotelRadio.closest('.hotel-option-card');
                        if (currentGestorHotelCard) {
                            currentGestorHotelCard.classList.add('selected');
                        }
                        toggleGestorFields(selectedGestorHotelRadio.value);
                    }
                }, 200);
            }
        } else {
            toggleQuotaSections(null);
        }

        // Autocomplete de hotel desativado: seleção passa a ser por <select>.
        
        // Observer para garantir que os campos sejam habilitados quando a seção do gestor for exibida
        const gestorSection = document.getElementById('gestor_section');
        if (gestorSection) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const gestorAdditionalFields = document.getElementById('gestor_additional_fields');
                        const selectedGestorHotelRadio = document.querySelector('input[name="gestor_hotel_operational"]:checked');
                        
                        // Se a seção do gestor está visível e o hotel está operacional, garantir que os campos estejam habilitados
                        if (!gestorSection.classList.contains('d-none') && 
                            selectedGestorHotelRadio && 
                            selectedGestorHotelRadio.value === '1' &&
                            gestorAdditionalFields && 
                            !gestorAdditionalFields.classList.contains('d-none')) {
                            
                            setTimeout(() => {
                                const fields = gestorAdditionalFields.querySelectorAll('input, select, textarea');
                                fields.forEach(field => {
                                    if (field.type !== 'hidden' && field.type !== 'radio') {
                                        field.disabled = false;
                                        field.removeAttribute('disabled');
                                        field.removeAttribute('readonly');
                                    }
                                });
                            }, 100);
                        }
                    }
                });
            });
            
            observer.observe(gestorSection, {
                attributes: true,
                attributeFilter: ['class']
            });
        }
    });

    // Função para inicializar autocomplete de hotel
    function initHotelAutocomplete() {
        const hotelInput = document.getElementById('hotel_name');
        const hotelIdInput = document.getElementById('owner_hotel_id');
        const hotelCheckIcon = document.getElementById('hotel_check_icon');
        const wrapper = document.querySelector('.hotel-autocomplete-wrapper');
        
        if (!hotelInput || !wrapper) return;
        
        let autocompleteList = document.getElementById('hotel-autocomplete-create');
        if (!autocompleteList) {
            autocompleteList = document.createElement('div');
            autocompleteList.className = 'hotel-autocomplete-list';
            autocompleteList.id = 'hotel-autocomplete-create';
            wrapper.appendChild(autocompleteList);
        }
        
        let searchTimeout;
        let selectedIndex = -1;
        
        // Função para buscar hotéis
        function searchHotels(query) {
            if (query.length < 1) {
                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
                hotelIdInput.value = '';
                if (hotelCheckIcon) hotelCheckIcon.classList.add('d-none');
                return;
            }

            fetch(`{{ route('api.hotels.search') }}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        autocompleteList.innerHTML = '';
                        data.data.forEach((hotel, index) => {
                            const item = document.createElement('div');
                            item.className = 'hotel-autocomplete-item';
                            item.dataset.index = index;
                            item.innerHTML = `
                                <i class="fas fa-hotel me-2"></i>
                                <span class="hotel-name">${hotel.name}</span>
                                ${hotel.city || hotel.state ? `<span class="hotel-location"> - ${[hotel.city, hotel.state].filter(Boolean).join(', ')}</span>` : ''}
                            `;
                            item.addEventListener('click', function() {
                                hotelInput.value = hotel.name;
                                hotelIdInput.value = hotel.id;
                                autocompleteList.innerHTML = '';
                                autocompleteList.style.display = 'none';
                                selectedIndex = -1;
                                if (hotelCheckIcon) hotelCheckIcon.classList.remove('d-none');
                            });
                            item.addEventListener('mouseenter', function() {
                                document.querySelectorAll('.hotel-autocomplete-item').forEach(i => i.classList.remove('active'));
                                this.classList.add('active');
                                selectedIndex = parseInt(this.dataset.index);
                            });
                            autocompleteList.appendChild(item);
                        });
                        autocompleteList.style.display = 'block';
                    } else {
                        autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Nenhum hotel encontrado</div>';
                        autocompleteList.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar hotéis:', error);
                });
        }

        // Event listener para input - busca a cada letra digitada
        hotelInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            // Busca imediatamente se tiver pelo menos 1 caractere
            if (query.length >= 1) {
                searchTimeout = setTimeout(() => {
                    searchHotels(query);
                }, 100); // Pequeno delay para evitar muitas requisições
            } else {
                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
                hotelIdInput.value = '';
                if (hotelCheckIcon) hotelCheckIcon.classList.add('d-none');
            }
        });

        // Fechar autocomplete ao clicar fora
        document.addEventListener('click', function(e) {
            if (!hotelInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.style.display = 'none';
            }
        });

        // Navegação com teclado
        hotelInput.addEventListener('keydown', function(e) {
            const items = autocompleteList.querySelectorAll('.hotel-autocomplete-item:not(.hotel-autocomplete-no-results)');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = (selectedIndex + 1) % items.length;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    items[selectedIndex].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    items[selectedIndex].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                autocompleteList.style.display = 'none';
                selectedIndex = -1;
            }
        });

        // Focar no campo ao clicar no autocomplete
        hotelInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 1) {
                searchHotels(this.value.trim());
            }
        });

        // Se já tem valor selecionado (old value), mostrar check
        if (hotelIdInput.value && hotelInput.value.trim()) {
            if (hotelCheckIcon) hotelCheckIcon.classList.remove('d-none');
        }
    }

    // Função para inicializar autocomplete de hotel do gestor
    function initGestorHotelAutocomplete() {
        const gestorHotelInput = document.getElementById('gestor_hotel_name');
        const gestorHotelIdInput = document.getElementById('gestor_hotel_id');
        const gestorHotelCheckIcon = document.getElementById('gestor_hotel_check_icon');
        const gestorWrapper = document.querySelector('#gestor_section .hotel-autocomplete-wrapper');
        
        if (!gestorHotelInput || !gestorWrapper) return;
        
        let autocompleteList = document.getElementById('gestor-hotel-autocomplete-create');
        if (!autocompleteList) {
            autocompleteList = document.createElement('div');
            autocompleteList.className = 'hotel-autocomplete-list';
            autocompleteList.id = 'gestor-hotel-autocomplete-create';
            gestorWrapper.appendChild(autocompleteList);
        }
        
        let searchTimeout;
        let selectedIndex = -1;
        
        // Função para buscar hotéis
        function searchHotels(query) {
            if (query.length < 1) {
                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
                gestorHotelIdInput.value = '';
                if (gestorHotelCheckIcon) gestorHotelCheckIcon.classList.add('d-none');
                return;
            }

            fetch(`{{ route('api.hotels.search') }}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        autocompleteList.innerHTML = '';
                        data.data.forEach((hotel, index) => {
                            const item = document.createElement('div');
                            item.className = 'hotel-autocomplete-item';
                            item.dataset.index = index;
                            item.innerHTML = `
                                <i class="fas fa-hotel me-2"></i>
                                <span class="hotel-name">${hotel.name}</span>
                                ${hotel.city || hotel.state ? `<span class="hotel-location"> - ${[hotel.city, hotel.state].filter(Boolean).join(', ')}</span>` : ''}
                            `;
                            item.addEventListener('click', function() {
                                gestorHotelInput.value = hotel.name;
                                gestorHotelIdInput.value = hotel.id;
                                autocompleteList.innerHTML = '';
                                autocompleteList.style.display = 'none';
                                selectedIndex = -1;
                                if (gestorHotelCheckIcon) gestorHotelCheckIcon.classList.remove('d-none');
                            });
                            item.addEventListener('mouseenter', function() {
                                document.querySelectorAll('#gestor-hotel-autocomplete-create .hotel-autocomplete-item').forEach(i => i.classList.remove('active'));
                                this.classList.add('active');
                                selectedIndex = parseInt(this.dataset.index);
                            });
                            autocompleteList.appendChild(item);
                        });
                        autocompleteList.style.display = 'block';
                    } else {
                        autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Nenhum hotel encontrado</div>';
                        autocompleteList.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar hotéis:', error);
                });
        }

        // Event listener para input - busca a cada letra digitada
        gestorHotelInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            // Busca imediatamente se tiver pelo menos 1 caractere
            if (query.length >= 1) {
                searchTimeout = setTimeout(() => {
                    searchHotels(query);
                }, 100); // Pequeno delay para evitar muitas requisições
            } else {
                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
                gestorHotelIdInput.value = '';
                if (gestorHotelCheckIcon) gestorHotelCheckIcon.classList.add('d-none');
            }
        });

        // Fechar autocomplete ao clicar fora
        document.addEventListener('click', function(e) {
            if (!gestorHotelInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.style.display = 'none';
            }
        });

        // Navegação com teclado
        gestorHotelInput.addEventListener('keydown', function(e) {
            const items = autocompleteList.querySelectorAll('.hotel-autocomplete-item:not(.hotel-autocomplete-no-results)');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = (selectedIndex + 1) % items.length;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    items[selectedIndex].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    items[selectedIndex].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                autocompleteList.style.display = 'none';
                selectedIndex = -1;
            }
        });

        // Focar no campo ao clicar no autocomplete
        gestorHotelInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 1) {
                searchHotels(this.value.trim());
            }
        });

        // Se já tem valor selecionado (old value), mostrar check
        if (gestorHotelIdInput.value && gestorHotelInput.value.trim()) {
            if (gestorHotelCheckIcon) gestorHotelCheckIcon.classList.remove('d-none');
        }
    }

    // Função para garantir que os campos dos quartos estejam habilitados antes do submit
    function ensureRoomFieldsEnabled() {
        const roomsConfigDiv = document.getElementById('rooms-configuration');
        if (roomsConfigDiv && !roomsConfigDiv.classList.contains('d-none')) {
            const roomFields = roomsConfigDiv.querySelectorAll('input, select, textarea');
            roomFields.forEach(field => {
                // Se o campo está visível, garantir que está habilitado
                if (!field.closest('.d-none')) {
                    field.disabled = false;
                }
            });
        }
        syncStep3HotelNameFromStep1();
        if (typeof updateFractionDebugPanel === 'function') {
            updateFractionDebugPanel();
        }
        return true;
    }
</script>
@endpush
@endsection
