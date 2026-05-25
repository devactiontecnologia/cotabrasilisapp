@extends('layouts.app')

@section('title', 'Cadastro Exitoso')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body text-center p-5">
                    <!-- Ícone de Sucesso -->
                    <div class="mb-4">
                        <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
                            <i class="fas fa-check fa-4x text-white"></i>
                        </div>
                    </div>

                    <!-- Título -->
                    <h1 class="display-5 fw-bold text-success mb-3">
                    Cadastro Exitoso
                    </h1>

                    <!-- Mensagem -->
                    <p class="lead text-muted mb-4">
                    Parabéns. Sua conta foi criada com êxito. Agora, você é membro da plataforma Cota Brasilis.

Seja muito bem vindo ao Mundo da Multipropriedade Hoteleira de desfrute e partilha conscientes.
                    </p>

                    <!-- Informações Adicionais -->
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>Próximos Passos
                        </h5>
                        <hr>
                        <p class="mb-2">
                            <strong>1.</strong> Verifique seu e-mail para confirmar sua conta (se aplicável)
                        </p>
                        <p class="mb-0">
                            <strong>2.</strong> Explore as funcionalidades disponíveis no seu perfil
                        </p>
                        <p>
                            <strong>3.</strong> Você receberá um email com as instruções para acessar sua conta.
                        </p>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-tachometer-alt me-2"></i>Ir para o Painel de controle
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-sign-in-alt me-2"></i>Fazer Login
                            </a>
                        @endauth
                    </div>

                    <!-- Informação de Login -->
                    <div class="mt-4 pt-4 border-top">
                        <p class="text-muted mb-0">
                            <small>
                                <i class="fas fa-envelope me-1"></i>
                                Você receberá um e-mail com as instruções para acessar sua conta.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bg-success {
        animation: scaleIn 0.5s ease-out 0.2s both;
    }

    @keyframes scaleIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }
</style>
@endsection
