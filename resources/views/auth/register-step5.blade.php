@extends('layouts.app')

@section('title', 'Cadastro — Etapa 5')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Etapa 5 — Informações da Cota</h4>

                <form method="POST" action="{{ route('register.step5.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Status da Cota</label>
                        <select name="quota_status" class="form-select" required>
                            <option value="">Selecione</option>
                            <option value="paid" {{ old('quota_status', session('register.step5.quota_status','')) == 'paid' ? 'selected' : '' }}>Quitada</option>
                            <option value="unpaid" {{ old('quota_status', session('register.step5.quota_status','')) == 'unpaid' ? 'selected' : '' }}>Em aberto</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantas semanas</label>
                        <select name="weeks_count" class="form-select" required>
                            <option value="">Selecione</option>
                            @for($i=1;$i<=8;$i++)
                            <option value="{{ $i }}" {{ old('weeks_count', session('register.step5.weeks_count','')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('register.step4') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary">Próxima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

