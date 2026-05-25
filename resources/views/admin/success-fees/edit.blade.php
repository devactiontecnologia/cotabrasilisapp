@extends('admin.layout')

@section('title', 'Editar Taxa de Êxito')
@section('page-title', 'Editar Taxa de Êxito')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-pencil text-primary me-2"></i>
                    Editar Taxa de Êxito #{{ $successFee->id }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.success-fees.update', $successFee) }}">
                    @csrf
                    @method('PUT')

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
                                <option value="{{ $key }}" {{ old('profile_type', $successFee->profile_type) == $key ? 'selected' : '' }}>
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
                               value="{{ old('days', $successFee->days) }}" 
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
                                   value="{{ old('fee_amount', $successFee->fee_amount) }}" 
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
                               value="{{ old('order', $successFee->order) }}" 
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
                                  maxlength="500">{{ old('description', $successFee->description) }}</textarea>
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
                                   {{ old('is_active', $successFee->is_active) ? 'checked' : '' }}>
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
                            Atualizar Taxa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>Criado em:</strong><br>
                    {{ $successFee->created_at->format('d/m/Y H:i') }}
                </p>
                <p class="small mb-0">
                    <strong>Última atualização:</strong><br>
                    {{ $successFee->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Aviso
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção:</strong> Ao alterar o perfil ou número de dias, certifique-se de que não existe outra taxa com a mesma combinação.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
