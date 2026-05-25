@extends('layouts.app')

@section('title', 'Recuperar Senha - Cota Brasilis')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg" data-aos="fade-up">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-key fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Esqueceu sua senha?</h3>
                    <p class="text-muted">Não se preocupe! Digite seu e-mail e enviaremos um link para redefinir sua senha.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgot-password-form">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-2 text-primary"></i>E-mail
                        </label>
                        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="Digite seu e-mail cadastrado" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg py-3">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Link de Recuperação
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

        <!-- Additional Info Card -->
        <div class="card border-0 bg-light mt-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold mb-3">Dicas de Segurança</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="text-start">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <small class="d-block">O link de recuperação expira em 60 minutos</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-start">
                            <i class="fas fa-lock text-info me-2"></i>
                            <small class="d-block">Verifique sua caixa de spam se não receber o e-mail</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-start">
                            <i class="fas fa-user-shield text-warning me-2"></i>
                            <small class="d-block">Se você não solicitou esta recuperação, ignore o e-mail</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const forgotPasswordForm = document.getElementById('forgot-password-form');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
            submitBtn.disabled = true;
        });
    }
</script>
@endpush
@endsection
