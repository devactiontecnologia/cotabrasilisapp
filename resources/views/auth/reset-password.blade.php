@extends('layouts.app')

@section('title', 'Redefinir Senha - Cota Brasilis')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg" data-aos="fade-up">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-lock fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Redefinir Senha</h3>
                    <p class="text-muted">Digite sua nova senha abaixo</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="reset-password-form">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2 text-primary"></i>Nova Senha
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Digite sua nova senha" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Mínimo de 8 caracteres</small>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2 text-primary"></i>Confirmar Nova Senha
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Confirme sua nova senha" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-success btn-lg py-3">
                            <i class="fas fa-check me-2"></i>Redefinir Senha
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0 text-muted">
                            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">
                                <i class="fas fa-arrow-left me-1"></i>Voltar para o login
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Requirements -->
        <div class="card border-0 bg-light mt-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-center">Requisitos da Senha</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Mínimo de 8 caracteres</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        <small>Use uma combinação de letras, números e símbolos</small>
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-shield-alt text-warning me-2"></i>
                        <small>Não compartilhe sua senha com ninguém</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const togglePasswordConfirmationBtn = document.getElementById('togglePasswordConfirmation');
    
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');

            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    if (togglePasswordConfirmationBtn) {
        togglePasswordConfirmationBtn.addEventListener('click', function() {
            const passwordConfirmation = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');

            if (passwordConfirmation.type === 'password') {
                passwordConfirmation.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordConfirmation.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    const resetPasswordForm = document.getElementById('reset-password-form');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redefinindo...';
            submitBtn.disabled = true;
        });
    }
</script>
@endpush
@endsection
