@extends('layouts.app')

@section('title', 'Login - Cota Brasilis')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6 col-lg-5">
        @if(auth()->check())
            <div class="card border-0 shadow-lg" data-aos="fade-up">
                <div class="card-body p-5 text-center">
                    <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-check fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Você já está conectado!</h3>
                    <p class="text-muted mb-4">Continue navegando ou utilize a opção abaixo para sair da sua conta.</p>

                    <div class="d-flex flex-column gap-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Ir para o painel
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair da conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
        
        @if(!auth()->check())
            <div class="card border-0 shadow-lg" data-aos="fade-up">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="Cota Brasilis" class="mb-4" style="height: 180px; max-width: 600px; object-fit: contain;">
                        <h3 class="fw-bold mb-2">Bem-vindo de volta</h3>
                        <p class="text-muted">Entre na sua conta para continuar</p>
                    </div>

                    <p class="small text-muted mb-3">Se aparecer "Página expirada", atualize a página (F5) e tente novamente.</p>
                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-primary"></i>E-mail
                            </label>
                            <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="Digite seu e-mail" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-primary"></i>Senha
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Digite sua senha" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Lembrar de mim
                                </label>
                            </div>
                            <a href="{{ route('password.forgot') }}" class="text-decoration-none text-primary fw-semibold">
                                Esqueceu a senha?
                            </a>
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Entrar na Conta
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0 text-muted">Não tem uma conta? 
                                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-primary">
                                    Cadastre-se gratuitamente
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Additional Info Card -->
        <div class="card border-0 bg-light mt-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold mb-3">Por que escolher o Cota Brasilis?</h6>
                <div class="row g-3">
                    <div class="col-4">
                        <div class="text-center">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <small class="d-block fw-semibold">Seguro</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <i class="fas fa-clock fa-2x text-info mb-2"></i>
                            <small class="d-block fw-semibold">Rápido</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center">
                            <i class="fas fa-users fa-2x text-warning mb-2"></i>
                            <small class="d-block fw-semibold">Confiável</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const togglePasswordBtn = document.getElementById('togglePassword');
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

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Entrando...';
            submitBtn.disabled = true;
        });
    }
</script>
@endpush
@endsection