@extends('layouts.app')

@section('title', 'Cadastro — Etapa 1')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Etapa 1 — Informações de Login</h4>

                <form method="POST" action="{{ route('register.step1.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nome de usuário</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', session('register.step1.name', '')) }}" required minlength="6">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', session('register.step1.email', '')) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Senha</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary">Próxima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

