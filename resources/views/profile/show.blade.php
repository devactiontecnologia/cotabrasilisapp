@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $profilePhotoUrl = $profile->userPhotoDisplayUrl();
    $profileTypes = [
        'curioso' => 'Curioso',
        'inteligente' => 'Inteligente',
        'sabio' => 'Sábio',
    ];
    $profileLabel = $profileTypes[$profile->profile_type] ?? Str::title($profile->profile_type);
    $alertCities = $profile->alert_cities ? json_decode($profile->alert_cities, true) : [];
@endphp

<div class="container py-5">
    <!-- Hero -->
    <section class="mb-4">
        <div class="p-4 p-lg-5 rounded-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.95), rgba(5, 150, 105, 0.92)); box-shadow: 0 32px 80px rgba(59, 130, 246, 0.22);">
            <div class="row g-4 align-items-center">
                <div class="col-auto">
                    <div class="position-relative">
                        <img id="profilePhotoPreview" src="{{ $profilePhotoUrl }}" alt="Foto do usuário"
                             class="rounded-4 border border-4 border-white shadow-lg"
                             style="width: 132px; height: 132px; object-fit: cover;"
                             onerror="this.onerror=null; this.src='{{ asset('images/placeholders/user-avatar.svg') }}';">
                        <button class="btn btn-light btn-sm rounded-circle position-absolute bottom-0 end-0 shadow"
                                type="button" data-bs-toggle="modal" data-bs-target="#profilePhotoModal">
                            <i class="fas fa-camera text-primary"></i>
                        </button>
                    </div>
                </div>
                <div class="col-lg-6">
                    <span class="badge bg-white text-primary fw-semibold mb-3 px-3 py-2">
                        <i class="fas fa-crown me-2"></i>{{ $profileLabel }}
                    </span>
                    <h1 class="display-6 fw-bold mb-2">{{ $profile->full_name }}</h1>
                    <div class="d-flex flex-wrap gap-3 text-white-75">
                        <span><i class="fas fa-envelope me-1"></i>{{ $profile->user->email }}</span>
                        <span><i class="fas fa-phone me-1"></i>{{ $profile->phone ?? 'Telefone não informado' }}</span>
                        <span><i class="fas fa-map-marker-alt me-1"></i>{{ $profile->city ? $profile->city . '/' . $profile->state : 'Localização não informada' }}</span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="bg-white bg-opacity-10 rounded-4 p-4">
                        <p class="text-white-50 mb-1"><i class="fas fa-clock me-1"></i>Atualizado em {{ Carbon::now()->format('d/m/Y \à\s H:i') }}</p>
                        <h4 class="fw-bold mb-1">{{ $profile->quota_paid_off ? 'Cota quitada' : 'Cota pendente' }}</h4>
                        <div class="text-white-75 small">
                            <div><i class="fas fa-hotel me-1"></i>{{ $profile->hotel_operational ? 'Hotel operacional' : 'Hotel inoperante' }}</div>
                            <div><i class="fas fa-bell me-1"></i>{{ count($alertCities) }} cidades monitoradas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-8 d-flex flex-column gap-4">
            <!-- Informações pessoais -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0"><i class="fas fa-id-card text-primary me-2"></i>Informações pessoais</h5>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-2"></i>Editar dados
                        </a>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-semibold">E-mail</small>
                            <p class="fw-semibold mb-0">{{ $profile->user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-semibold">Telefone</small>
                            <p class="fw-semibold mb-0">{{ $profile->phone ?? 'Não informado' }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-semibold">WhatsApp</small>
                            <p class="fw-semibold mb-0">{{ $profile->user->whatsapp ?? 'Não informado' }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-semibold">CPF</small>
                            <p class="fw-semibold mb-0">{{ $profile->cpf ?? 'Não informado' }}</p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted text-uppercase fw-semibold">Endereço</small>
                            <p class="fw-semibold mb-0">
                                {{ $profile->street ? $profile->street . ', ' . $profile->house_number . ($profile->complement ? ' - ' . $profile->complement : '') : 'Endereço não informado' }}<br>
                                {{ $profile->neighborhood ? $profile->neighborhood . ' - ' : '' }}{{ $profile->city ? $profile->city . '/' . $profile->state : '' }}<br>
                                {{ $profile->cep ? 'CEP: ' . $profile->cep : '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Perfil de uso na plataforma -->
            <div class="card border-0 shadow-sm rounded-4" id="perfil-uso">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-crown text-primary me-2"></i>Perfil de uso na plataforma</h5>
                    <p class="text-muted small mb-3">
                        O perfil define taxas, se você pode <strong>publicar cotas</strong>, opções de <strong>fracionamento</strong> e limites de busca e alertas. Você pode trocar entre <strong>Curioso</strong>, <strong>Inteligente</strong> e <strong>Sábio</strong> quando quiser.
                    </p>
                    <ul class="small text-muted mb-4 ps-3">
                        <li class="mb-1"><strong>Curioso</strong> — alugar e comprar; sem publicar cotas na plataforma.</li>
                        <li class="mb-1"><strong>Inteligente</strong> — publicar, fracionar períodos (ex.: 3+4 dias) e usar funções de aluguel/troca/compra/venda conforme as regras do sistema.</li>
                        <li><strong>Sábio</strong> — mais combinações de diárias, mais alertas por cidade e maior flexibilidade nas ofertas.</li>
                    </ul>
                    <form action="{{ route('profile.update-type') }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label for="profile_type_quick" class="form-label fw-semibold">Alterar tipo de perfil</label>
                            <select name="profile_type" id="profile_type_quick" class="form-select @error('profile_type') is-invalid @enderror" required>
                                <option value="curioso" @selected(old('profile_type', $profile->profile_type ?? 'curioso') === 'curioso')>Curioso</option>
                                <option value="inteligente" @selected(old('profile_type', $profile->profile_type ?? 'curioso') === 'inteligente')>Inteligente</option>
                                <option value="sabio" @selected(old('profile_type', $profile->profile_type ?? 'curioso') === 'sabio')>Sábio</option>
                            </select>
                            @error('profile_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Salvar tipo de perfil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Documentos e autorizações -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-folder-open text-primary me-2"></i>Documentos e autorizações</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-2">CNH</small>
                                @if($profile->cnh_photo_path)
                                    <a href="{{ asset('storage/' . $profile->cnh_photo_path) }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                        <i class="fas fa-eye me-1"></i>Visualizar documento
                                    </a>
                                @else
                                    <span class="text-muted">Documento não enviado</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-2">RG</small>
                                @if($profile->rg_photo_path)
                                    <a href="{{ asset('storage/' . $profile->rg_photo_path) }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                        <i class="fas fa-eye me-1"></i>Visualizar documento
                                    </a>
                                @else
                                    <span class="text-muted">Documento não enviado</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-2">Contrato da cota</small>
                                @if($profile->quota_contract_photo_path)
                                    <a href="{{ asset('storage/' . $profile->quota_contract_photo_path) }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                        <i class="fas fa-eye me-1"></i>Visualizar contrato
                                    </a>
                                @else
                                    <span class="text-muted">Documento não enviado</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100 d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold d-block">Cota quitada</small>
                                    <span class="badge bg-{{ $profile->quota_paid_off ? 'success' : 'warning' }}">{{ $profile->quota_paid_off ? 'Sim' : 'Não' }}</span>
                                </div>
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100 d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold d-block">Hotel operacional</small>
                                    <span class="badge bg-{{ $profile->hotel_operational ? 'success' : 'danger' }}">{{ $profile->hotel_operational ? 'Sim' : 'Não' }}</span>
                                </div>
                                <i class="fas fa-building text-primary fs-4"></i>
                            </div>
                        </div>
                        @isset($platformAuthorizationDocuments)
                            <div class="col-12">
                                <p class="small text-muted fw-semibold text-uppercase mb-3">Modelos Cota Brasilis</p>
                            </div>
                            @foreach($platformAuthorizationDocuments as $pad)
                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100">
                                        <small class="text-muted fw-semibold d-block mb-2" style="line-height: 1.35;">{{ $pad->title }}</small>
                                        @if($url = $pad->publicAssetUrl())
                                            <div class="d-flex flex-column gap-2">
                                                <a href="{{ $url }}" class="btn btn-outline-primary btn-sm w-100" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-eye me-1"></i>Visualizar
                                                </a>
                                                <a href="{{ $url }}" class="btn btn-outline-secondary btn-sm w-100" download="{{ $pad->suggestedDownloadFilename() }}">
                                                    <i class="fas fa-download me-1"></i>Baixar
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted small">Disponível em breve. Acompanhe em <a href="{{ route('client.authorization-terms') }}">Termos de autorização</a>.</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endisset
                        <div class="col-12">
                            <div class="border rounded-4 p-3">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-2">Termo de autorização de hospedagem</small>
                                @if($profile->terms_accepted)
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Aceito em {{ optional($profile->terms_accepted_at)->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Não aceito</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 d-flex flex-column gap-4">
            <!-- Estatísticas -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-chart-bar text-primary me-2"></i>Estatísticas</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Leilões utilizados</span>
                            <span class="fw-semibold">{{ $profile->auctions_used }}</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Visualizações de busca</span>
                            <span class="fw-semibold">{{ $profile->search_views_used }}</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted">Cidades em alerta</span>
                            <span class="fw-semibold">{{ count($alertCities) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Ações rápidas -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-bolt text-primary me-2"></i>Ações rápidas</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar perfil completo
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Voltar ao painel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Preferências -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-sliders-h text-primary me-2"></i>Preferências</h5>
                    <p class="text-muted small mb-2">Notificações configuradas:</p>
                    <ul class="text-muted small mb-0">
                        @forelse($alertCities as $city)
                            <li><i class="fas fa-bell me-1 text-primary"></i>{{ $city }}</li>
                        @empty
                            <li>Você ainda não adicionou cidades para alerta.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de atualização de foto -->
<div class="modal fade" id="profilePhotoModal" tabindex="-1" aria-labelledby="profilePhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePhotoModalLabel">Atualizar foto de perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(Route::has('profile.photo.update'))
                    <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Selecione uma nova imagem (JPG ou PNG)</label>
                            <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-upload me-2"></i>Enviar nova foto
                        </button>
                    </form>
                @else
                    <p class="text-muted mb-0">Para alterar sua foto, acesse a página de edição do perfil.</p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-user-edit me-2"></i>Ir para edição de perfil
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection