@extends('layouts.app')

@section('title', 'Cadastro — Etapa 4')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Etapa 4 — Documentos</h4>

                <form method="POST" action="{{ route('register.step4.post') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tipo de documento</label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Selecione</option>
                            <option value="rg" {{ old('document_type', session('register.step4.document_type','')) == 'rg' ? 'selected' : '' }}>RG</option>
                            <option value="passport" {{ old('document_type', session('register.step4.document_type','')) == 'passport' ? 'selected' : '' }}>Passaporte</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número do documento</label>
                        <input type="text" name="document_number" class="form-control" value="{{ old('document_number', session('register.step4.document_number','')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Arquivo do documento (foto ou PDF)</label>
                        <input type="file" name="document_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Recomendado foto nítida do documento.</small>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('register.step3') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary">Próxima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

