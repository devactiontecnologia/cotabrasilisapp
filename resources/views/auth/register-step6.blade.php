@extends('layouts.app')

@section('title', 'Cadastro — Etapa 6')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Etapa 6 — Fracionamento e Termos</h4>

                <form method="POST" action="{{ route('register.step6.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tipo de fracionamento</label>
                        <select name="fraction_type" class="form-select" required>
                            <option value="">Selecione</option>
                            <option value="fixed" {{ old('fraction_type', session('register.step6.fraction_type','')) == 'fixed' ? 'selected' : '' }}>Fixo</option>
                            <option value="rotative" {{ old('fraction_type', session('register.step6.fraction_type','')) == 'rotative' ? 'selected' : '' }}>Rotativo</option>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="agree_terms" id="agree_terms" class="form-check-input" {{ old('agree_terms', session('register.step6.agree_terms',false)) ? 'checked' : '' }} required>
                        <label for="agree_terms" class="form-check-label">Li e concordo com os Termos de Uso</label>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('register.step5') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-success">Concluir e continuar cadastro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

