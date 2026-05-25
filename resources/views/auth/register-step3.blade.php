@extends('layouts.app')

@section('title', 'Cadastro — Etapa 3')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Etapa 3 — Endereço</h4>

                <form method="POST" action="{{ route('register.step3.post') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Logradouro</label>
                            <input type="text" name="street" class="form-control" value="{{ old('street', session('register.step3.street','')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número</label>
                            <input type="text" name="number" class="form-control" value="{{ old('number', session('register.step3.number','')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', session('register.step3.city','')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', session('register.step3.state','')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CEP</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', session('register.step3.postal_code','')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">País</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', session('register.step3.country','BR')) }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('register.step2') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary">Próxima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

