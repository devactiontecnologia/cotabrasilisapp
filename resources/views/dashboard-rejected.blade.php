@extends('layouts.app')

@section('title', 'Conta reprovada - Cota Brasilis')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="rounded-circle bg-danger bg-opacity-25 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-times-circle fa-4x text-danger"></i>
                        </div>
                    </div>
                    <h1 class="h4 fw-bold text-dark mb-3">Sua conta foi reprovada</h1>
                    <p class="text-muted mb-4 fs-6">
                        Sua conta foi reprovada pela equipe do Cota Brasilis. Entre em contato com a equipe para mais informações.
                    </p>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair da conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
