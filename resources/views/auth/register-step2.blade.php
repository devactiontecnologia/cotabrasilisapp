@extends('layouts.app')

@section('title', 'Cadastro — Etapa 2')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Etapa 2 — Dados Pessoais</h4>

                <form method="POST" action="{{ route('register.step2.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name', session('register.step2.full_name', '')) }}" required>
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CPF</label>
                        <input type="text" name="cpf" class="form-control @error('cpf') is-invalid @enderror"
                               value="{{ old('cpf', session('register.step2.cpf', '')) }}" required>
                        @error('cpf') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', session('register.step2.phone', '')) }}">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('register.step1') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary">Próxima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

