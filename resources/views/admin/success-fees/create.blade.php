@extends('admin.layout')

@section('title', 'Nova Taxa de Êxito')
@section('page-title', 'Nova Taxa de Êxito')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle text-primary me-2"></i>
                    Cadastrar Nova Taxa de Êxito
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.success-fees.store') }}">
                    @csrf

                    <!-- Tipo de Perfil -->
                    <div class="mb-3">
                        <label for="profile_type" class="form-label">
                            Tipo de Perfil <span class="text-danger">*</span>
                        </label>
                        <select name="profile_type" 
                                id="profile_type" 
                                class="form-select @error('profile_type') is-invalid @enderror" 
                                required>
                            <option value="">Selecione o tipo de perfil</option>
                            @foreach($profileTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('profile_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('profile_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Número de Dias -->
                    <div class="mb-3">
                        <label for="days" class="form-label">
                            Número de Dias <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               name="days" 
                               id="days" 
                               class="form-control @error('days') is-invalid @enderror" 
                               value="{{ old('days') }}" 
                               min="1" 
                               max="30" 
                               required>
                        @error('days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Número de dias do fracionamento (ex: 2, 3, 4, 5, 7 dias)
                        </small>
                    </div>

                    <!-- Valor da Taxa -->
                    <div class="mb-3">
                        <label for="fee_amount" class="form-label">
                            Valor da Taxa (R$) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" 
                                   name="fee_amount" 
                                   id="fee_amount" 
                                   class="form-control @error('fee_amount') is-invalid @enderror" 
                                   value="{{ old('fee_amount') }}" 
                                   step="0.01" 
                                   min="0" 
                                   max="999999.99" 
                                   required>
                        </div>
                        @error('fee_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Valor em reais (ex: 65.00, 90.00, 100.00)
                        </small>
                    </div>

                    <!-- Ordem -->
                    <div class="mb-3">
                        <label for="order" class="form-label">
                            Ordem de Exibição
                        </label>
                        <input type="number" 
                               name="order" 
                               id="order" 
                               class="form-control @error('order') is-invalid @enderror" 
                               value="{{ old('order', 0) }}" 
                               min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Ordem de exibição (quanto menor o número, maior a prioridade)
                        </small>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Descrição
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" 
                                  maxlength="500">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Descrição opcional da taxa (máximo 500 caracteres)
                        </small>
                    </div>

                    <!-- Status Ativo -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Taxa ativa
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Se desmarcado, a taxa não será exibida no sistema
                        </small>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.success-fees.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Criar Taxa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <h6>Tipos de Perfil:</h6>
                <ul class="small">
                    <li><strong>Curioso:</strong> Perfil básico</li>
                    <li><strong>Inteligente:</strong> Perfil intermediário</li>
                    <li><strong>Sábio:</strong> Perfil avançado</li>
                </ul>
                
                <hr>
                
                <h6>Exemplos de Taxas:</h6>
                <ul class="small">
                    <li>2 dias: R$ 65,00</li>
                    <li>3 dias: R$ 90,00</li>
                    <li>4 dias: R$ 100,00</li>
                    <li>5 dias: R$ 125,00</li>
                    <li>7 dias: R$ 160,00</li>
                </ul>

                <hr>

                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção:</strong> Não é possível cadastrar duas taxas com o mesmo perfil e número de dias.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
