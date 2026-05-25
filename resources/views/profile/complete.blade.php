@extends('layouts.app')

@section('title', 'Completar Perfil - Cota Brasilis')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-user-check me-2"></i>Completar Perfil
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.complete') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Document Uploads -->
                    <h5 class="mb-3">Documentos</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cnh_photo" class="form-label">Foto da CNH</label>
                                <input type="file" class="form-control @error('cnh_photo') is-invalid @enderror" 
                                       id="cnh_photo" name="cnh_photo" accept="image/*">
                                <div class="form-text">Upload da CNH válida (opcional)</div>
                                @error('cnh_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rg_photo" class="form-label">Foto do RG</label>
                                <input type="file" class="form-control @error('rg_photo') is-invalid @enderror" 
                                       id="rg_photo" name="rg_photo" accept="image/*">
                                <div class="form-text">Upload do RG (opcional)</div>
                                @error('rg_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_photo" class="form-label">Foto do Usuário</label>
                                <input type="file" class="form-control @error('user_photo') is-invalid @enderror" 
                                       id="user_photo" name="user_photo" accept="image/*">
                                <div class="form-text">Sua foto de perfil (opcional)</div>
                                @error('user_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quota_contract_photo" class="form-label">Contrato da Cota</label>
                                <input type="file" class="form-control @error('quota_contract_photo') is-invalid @enderror" 
                                       id="quota_contract_photo" name="quota_contract_photo" accept="image/*">
                                <div class="form-text">Primeira página do contrato (se possuir cota)</div>
                                @error('quota_contract_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Quota Information -->
                    <h5 class="mb-3 mt-4">Informações da Cota</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="quota_paid_off" 
                                       name="quota_paid_off" value="1" {{ old('quota_paid_off') ? 'checked' : '' }}>
                                <label class="form-check-label" for="quota_paid_off">
                                    Cota está quitada
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="hotel_operational" 
                                       name="hotel_operational" value="1" {{ old('hotel_operational', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="hotel_operational">
                                    Hotel está em funcionamento
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <h5 class="mb-3">Termos e Condições</h5>
                        <div class="card">
                            <div class="card-body">
                                <h6>Termo de Autorização de Hospedagem</h6>
                                <div class="small text-muted mb-3">
                                    <p>Eu, <strong>{{ $profile->full_name ?? auth()->user()->name }}</strong>, 
                                    portador do CPF <strong>{{ $profile->cpf ?? 'N/A' }}</strong>, 
                                    declaro estar ciente e de acordo com os seguintes termos:</p>
                                    
                                    <ul>
                                        <li>Autorizo o uso de minha cota hoteleira por terceiros através da plataforma Cota Brasilis</li>
                                        <li>Declaro que possuo os direitos legais sobre a cota mencionada</li>
                                        <li>Comprometo-me a cumprir todas as obrigações contratuais</li>
                                        <li>Estou ciente das taxas e comissões aplicáveis</li>
                                        <li>Autorizo o processamento dos meus dados pessoais conforme a LGPD</li>
                                    </ul>
                                    
                                    <p>Este termo é válido a partir da data de aceite e permanece em vigor até que seja revogado.</p>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                                           type="checkbox" id="terms_accepted" name="terms_accepted" 
                                           value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="terms_accepted">
                                        <strong>Li e aceito os termos de autorização de hospedagem *</strong>
                                    </label>
                                    @error('terms_accepted')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check me-2"></i>Completar Perfil
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <p class="mb-0">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Voltar ao Dashboard
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // File preview functionality
    function previewFile(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById(previewId);
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = previewId;
                    preview.className = 'img-thumbnail mt-2';
                    preview.style.maxWidth = '200px';
                    input.parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Add preview for all file inputs
    document.getElementById('cnh_photo').addEventListener('change', function() {
        previewFile(this, 'cnh_preview');
    });

    document.getElementById('rg_photo').addEventListener('change', function() {
        previewFile(this, 'rg_preview');
    });

    document.getElementById('user_photo').addEventListener('change', function() {
        previewFile(this, 'user_preview');
    });

    document.getElementById('quota_contract_photo').addEventListener('change', function() {
        previewFile(this, 'contract_preview');
    });
</script>
@endpush