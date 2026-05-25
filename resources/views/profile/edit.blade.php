@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
@php
    use Illuminate\Support\Str;
    $profilePhotoUrl = $profile->userPhotoDisplayUrl();
@endphp

<div class="container py-5">
    <!-- Hero -->
    <section class="mb-4">
        <div class="p-4 p-lg-5 rounded-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.94), rgba(16, 185, 129, 0.9)); box-shadow: 0 32px 80px rgba(79, 70, 229, 0.2);">
            <div class="row g-4 align-items-center">
                <div class="col-auto">
                    <div class="position-relative">
                        <img id="profilePhotoPreview" src="{{ $profilePhotoUrl }}" alt="Foto do usuário"
                             class="rounded-4 border border-4 border-white shadow-lg"
                             style="width: 132px; height: 132px; object-fit: cover;">
                        <label for="user_photo" class="btn btn-light btn-sm rounded-circle position-absolute bottom-0 end-0 shadow" style="cursor:pointer;">
                            <i class="fas fa-camera text-primary"></i>
                        </label>
                    </div>
                </div>
                <div class="col-lg-6">
                    <span class="badge bg-white text-primary fw-semibold mb-3 px-3 py-2">
                        <i class="fas fa-user-edit me-2"></i>Atualize seus dados
                    </span>
                    <h1 class="display-6 fw-bold mb-2">{{ $profile->full_name }}</h1>
                    <p class="text-white-75 mb-0">Mantenha suas informações sempre atualizadas para agilizar negociações e garantir segurança nas transações.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-light fw-semibold px-4 py-2 rounded-3">
                        <i class="fas fa-arrow-left me-2"></i>Voltar ao perfil
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-5">
                @csrf
                @method('PUT')

                <!-- Seção Dados Pessoais -->
                <section>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0"><i class="fas fa-id-card text-primary me-2"></i>Dados pessoais</h5>
                        <span class="text-muted small">Campos marcados com * são obrigatórios</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label fw-semibold">Nome completo *</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $profile->full_name) }}" required>
                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Telefone *</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="whatsapp" class="form-label fw-semibold">WhatsApp</label>
                            <input type="text" class="form-control @error('whatsapp') is-invalid @enderror" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $profile->user->whatsapp) }}">
                            @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">E-mail (somente leitura)</label>
                            <input type="email" class="form-control" value="{{ $profile->user->email }}" disabled>
                        </div>
                    </div>
                </section>

                <!-- Perfil de uso -->
                <section>
                    <h5 class="fw-semibold mb-3"><i class="fas fa-crown text-primary me-2"></i>Perfil de uso na plataforma</h5>
                    <p class="text-muted small mb-3">Este é o plano de uso (Curioso, Inteligente ou Sábio). Ele afeta taxas, publicação de cotas e limites. A mesma alteração pode ser feita em <a href="{{ route('profile.show') }}#perfil-uso">Meu perfil</a>.</p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="profile_type" class="form-label fw-semibold">Tipo de perfil *</label>
                            <select name="profile_type" id="profile_type" class="form-select @error('profile_type') is-invalid @enderror" required>
                                <option value="curioso" @selected(old('profile_type', $profile->profile_type ?? 'curioso') === 'curioso')>Curioso</option>
                                <option value="inteligente" @selected(old('profile_type', $profile->profile_type ?? 'curioso') === 'inteligente')>Inteligente</option>
                                <option value="sabio" @selected(old('profile_type', $profile->profile_type ?? 'curioso') === 'sabio')>Sábio</option>
                            </select>
                            @error('profile_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </section>

                <!-- Seção Endereço -->
                <section>
                    <h5 class="fw-semibold mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i>Endereço</h5>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <label for="cep" class="form-label fw-semibold">CEP *</label>
                            <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep" name="cep" value="{{ old('cep', $profile->cep) }}" maxlength="9" required>
                            @error('cep')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label for="street" class="form-label fw-semibold">Rua *</label>
                            <input type="text" class="form-control @error('street') is-invalid @enderror" id="street" name="street" value="{{ old('street', $profile->street) }}" required>
                            @error('street')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="neighborhood" class="form-label fw-semibold">Bairro *</label>
                            <input type="text" class="form-control @error('neighborhood') is-invalid @enderror" id="neighborhood" name="neighborhood" value="{{ old('neighborhood', $profile->neighborhood) }}" required>
                            @error('neighborhood')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label for="city" class="form-label fw-semibold">Cidade *</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $profile->city) }}" required>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="state" class="form-label fw-semibold">Estado *</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $profile->state) }}" required>
                            @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label for="house_number" class="form-label fw-semibold">Número *</label>
                            <input type="text" class="form-control @error('house_number') is-invalid @enderror" id="house_number" name="house_number" value="{{ old('house_number', $profile->house_number) }}" required>
                            @error('house_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label for="complement" class="form-label fw-semibold">Compl.</label>
                            <input type="text" class="form-control" id="complement" name="complement" value="{{ old('complement', $profile->complement) }}">
                        </div>
                    </div>
                </section>

                <!-- Seção Documentos -->
                <section>
                    <h5 class="fw-semibold mb-3"><i class="fas fa-folder-open text-primary me-2"></i>Documentos e foto de perfil</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="user_photo" class="form-label fw-semibold">Foto de perfil</label>
                            <input type="file" class="form-control @error('user_photo') is-invalid @enderror" id="user_photo" name="user_photo" accept="image/*">
                            @error('user_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Formatos aceitos: JPG ou PNG até 5MB.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="cnh_photo" class="form-label fw-semibold">Foto da CNH</label>
                            <input type="file" class="form-control @error('cnh_photo') is-invalid @enderror" id="cnh_photo" name="cnh_photo" accept="image/*">
                            @error('cnh_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($profile->cnh_photo_path)
                                <small class="d-block text-muted mt-1"><a href="{{ asset('storage/' . $profile->cnh_photo_path) }}" target="_blank">Visualizar documento atual</a></small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="rg_photo" class="form-label fw-semibold">Foto do RG</label>
                            <input type="file" class="form-control @error('rg_photo') is-invalid @enderror" id="rg_photo" name="rg_photo" accept="image/*">
                            @error('rg_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($profile->rg_photo_path)
                                <small class="d-block text-muted mt-1"><a href="{{ asset('storage/' . $profile->rg_photo_path) }}" target="_blank">Visualizar documento atual</a></small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="quota_contract_photo" class="form-label fw-semibold">Contrato da cota</label>
                            <input type="file" class="form-control @error('quota_contract_photo') is-invalid @enderror" id="quota_contract_photo" name="quota_contract_photo" accept="application/pdf,image/*">
                            @error('quota_contract_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($profile->quota_contract_photo_path)
                                <small class="d-block text-muted mt-1"><a href="{{ asset('storage/' . $profile->quota_contract_photo_path) }}" target="_blank">Visualizar contrato atual</a></small>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Botões -->
                <section class="d-flex flex-column flex-md-row gap-3">
                    <button type="submit" class="btn btn-primary btn-lg flex-fill">
                        <i class="fas fa-save me-2"></i>Salvar alterações
                    </button>
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary btn-lg flex-fill">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </section>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cepInput = document.getElementById('cep');
        const phoneInput = document.getElementById('phone');
        const whatsappInput = document.getElementById('whatsapp');
        const photoInput = document.getElementById('user_photo');
        const photoPreview = document.getElementById('profilePhotoPreview');

        function maskPhone(input) {
            input.addEventListener('input', () => {
                let v = input.value.replace(/\D/g, '');
                if (v.length > 11) v = v.slice(0, 11);
                v = v.replace(/(\d{2})(\d)/, '($1) $2');
                v = v.replace(/(\d{4,5})(\d{4})$/, '$1-$2');
                input.value = v;
            });
        }

        [phoneInput, whatsappInput].forEach(field => field && maskPhone(field));

        if (cepInput) {
            cepInput.addEventListener('input', () => {
                let v = cepInput.value.replace(/\D/g, '');
                if (v.length > 8) v = v.slice(0, 8);
                v = v.replace(/(\d{5})(\d)/, '$1-$2');
                cepInput.value = v;
            });

            cepInput.addEventListener('blur', () => {
                const cep = cepInput.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                document.getElementById('street').value = data.logradouro || '';
                                document.getElementById('neighborhood').value = data.bairro || '';
                                document.getElementById('city').value = data.localidade || '';
                                document.getElementById('state').value = data.uf || '';
                                document.getElementById('house_number').focus();
                            }
                        })
                        .catch(() => console.warn('Não foi possível buscar o CEP automaticamente.'));
                }
            });
        }

        if (photoInput && photoPreview) {
            photoInput.addEventListener('change', event => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => photoPreview.src = e.target.result;
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection