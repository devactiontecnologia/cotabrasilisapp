@extends('layouts.app')

@section('title', 'Criar Solicitação de Compra - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Criar Nova Solicitação de Compra</h4>
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            <!-- Informações Básicas -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Informações da Compra Desejada</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="quota_type" class="form-label fw-semibold">
                            <i class="fas fa-layer-group me-2 text-success"></i>Tipo de cota *
                        </label>
                        <select class="form-select @error('quota_type') is-invalid @enderror" id="quota_type" name="quota_type" required>
                            <option value="">Selecione</option>
                            <option value="fixa" {{ old('quota_type') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                            <option value="flexivel" {{ old('quota_type') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                            <option value="fixa_flexivel" {{ old('quota_type') == 'fixa_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
                        </select>
                        @error('quota_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="seasonality" class="form-label fw-semibold">
                            <i class="fas fa-sun me-2 text-success"></i>Sazonalidade *
                        </label>
                        <select class="form-select @error('seasonality') is-invalid @enderror" id="seasonality" name="seasonality" required>
                            <option value="">Selecione</option>
                            <option value="altissima" {{ old('seasonality') == 'altissima' ? 'selected' : '' }}>Altíssima</option>
                            <option value="alta" {{ old('seasonality') == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="media" {{ old('seasonality') == 'media' ? 'selected' : '' }}>Média</option>
                            <option value="baixa" {{ old('seasonality') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                        </select>
                        @error('seasonality')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="number_of_rooms" class="form-label fw-semibold">
                            <i class="fas fa-bed me-2 text-success"></i>Quartos *
                        </label>
                        <input type="number" class="form-control @error('number_of_rooms') is-invalid @enderror" 
                               id="number_of_rooms" name="number_of_rooms" value="{{ old('number_of_rooms') }}" 
                               min="1" required>
                        @error('number_of_rooms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="price_range_min" class="form-label fw-semibold">Preço mínimo *</label>
                        <input type="number" class="form-control @error('price_range_min') is-invalid @enderror" 
                               id="price_range_min" name="price_range_min" value="{{ old('price_range_min') }}" 
                               step="0.01" min="0" placeholder="0.00" required>
                        @error('price_range_min')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="price_range_max" class="form-label fw-semibold">Preço máximo *</label>
                        <input type="number" class="form-control @error('price_range_max') is-invalid @enderror" 
                               id="price_range_max" name="price_range_max" value="{{ old('price_range_max') }}" 
                               step="0.01" min="0" placeholder="0.00" required>
                        @error('price_range_max')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="hotel_id" class="form-label fw-semibold">
                            <i class="fas fa-hotel me-2 text-success"></i>Hotel *
                        </label>
                        <select class="form-select @error('hotel_id') is-invalid @enderror" id="hotel_id" name="hotel_id" required>
                            <option value="">Selecione um hotel</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                    {{ $hotel->name }} - {{ $hotel->city }}, {{ $hotel->state }}
                                </option>
                            @endforeach
                        </select>
                        @error('hotel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="state" class="form-label fw-semibold">
                            <i class="fas fa-map me-2 text-success"></i>Estado *
                        </label>
                        <select class="form-select @error('state') is-invalid @enderror" id="state" name="state" required>
                            <option value="">Selecione</option>
                            <option value="AC" {{ old('state') == 'AC' ? 'selected' : '' }}>Acre</option>
                            <option value="AL" {{ old('state') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                            <option value="AP" {{ old('state') == 'AP' ? 'selected' : '' }}>Amapá</option>
                            <option value="AM" {{ old('state') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                            <option value="BA" {{ old('state') == 'BA' ? 'selected' : '' }}>Bahia</option>
                            <option value="CE" {{ old('state') == 'CE' ? 'selected' : '' }}>Ceará</option>
                            <option value="DF" {{ old('state') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                            <option value="ES" {{ old('state') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                            <option value="GO" {{ old('state') == 'GO' ? 'selected' : '' }}>Goiás</option>
                            <option value="MA" {{ old('state') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                            <option value="MT" {{ old('state') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                            <option value="MS" {{ old('state') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                            <option value="MG" {{ old('state') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                            <option value="PA" {{ old('state') == 'PA' ? 'selected' : '' }}>Pará</option>
                            <option value="PB" {{ old('state') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                            <option value="PR" {{ old('state') == 'PR' ? 'selected' : '' }}>Paraná</option>
                            <option value="PE" {{ old('state') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                            <option value="PI" {{ old('state') == 'PI' ? 'selected' : '' }}>Piauí</option>
                            <option value="RJ" {{ old('state') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                            <option value="RN" {{ old('state') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                            <option value="RS" {{ old('state') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                            <option value="RO" {{ old('state') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                            <option value="RR" {{ old('state') == 'RR' ? 'selected' : '' }}>Roraima</option>
                            <option value="SC" {{ old('state') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                            <option value="SP" {{ old('state') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                            <option value="SE" {{ old('state') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                            <option value="TO" {{ old('state') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                        </select>
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="city" class="form-label fw-semibold">
                            <i class="fas fa-map-marker-alt me-2 text-success"></i>Cidade *
                        </label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                               id="city" name="city" value="{{ old('city') }}" 
                               placeholder="Ex: Rio de Janeiro" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="weeks" class="form-label fw-semibold">
                            <i class="fas fa-calendar-week me-2 text-success"></i>Semanas
                        </label>
                        <select class="form-select @error('weeks') is-invalid @enderror" id="weeks" name="weeks">
                            <option value="">Qualquer</option>
                            @for($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ old('weeks') == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'semana' : 'semanas' }}
                                </option>
                            @endfor
                        </select>
                        @error('weeks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="month" class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-2 text-success"></i>Mês
                        </label>
                        <select class="form-select @error('month') is-invalid @enderror" id="month" name="month">
                            <option value="">Qualquer mês</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('month') == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->locale('pt_BR')->monthName }}
                                </option>
                            @endfor
                        </select>
                        @error('month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-check me-2 text-success"></i>Tipo de Período *
                        </label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="period_type" id="period_fixo" value="fixo" {{ old('period_type', 'fixo') == 'fixo' ? 'checked' : '' }} required>
                            <label class="btn btn-outline-success" for="period_fixo">
                                <i class="fas fa-calendar-day me-2"></i>Fixo
                            </label>
                            
                            <input type="radio" class="btn-check" name="period_type" id="period_flexivel" value="flexivel" {{ old('period_type') == 'flexivel' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="period_flexivel">
                                <i class="fas fa-calendar-week me-2"></i>Flexível
                            </label>
                        </div>
                        @error('period_type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="company" class="form-label fw-semibold">Empresa</label>
                        <input type="text" class="form-control @error('company') is-invalid @enderror" 
                               id="company" name="company" value="{{ old('company') }}" 
                               placeholder="Nome da empresa (opcional)">
                        @error('company')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Observações -->
            <div class="mb-4">
                <label for="observations" class="form-label fw-semibold">
                    <i class="fas fa-sticky-note me-2 text-success"></i>Observações
                </label>
                <textarea class="form-control @error('observations') is-invalid @enderror" 
                          id="observations" name="observations" rows="4" 
                          placeholder="Descreva detalhes sobre a compra desejada...">{{ old('observations') }}</textarea>
                @error('observations')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Intermediação -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="professional_intermediation" 
                           id="professional_intermediation" value="1" {{ old('professional_intermediation') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="professional_intermediation">
                        Quero que um profissional de turismo intermedie minha compra
                    </label>
                </div>
            </div>

            <!-- Campos para Intermediação -->
            <div class="mb-4 d-none" id="intermediation_prices">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="price_min_acceptable" class="form-label fw-semibold">Preço mínimo</label>
                        <input type="number" class="form-control @error('price_min_acceptable') is-invalid @enderror" 
                               id="price_min_acceptable" name="price_min_acceptable" value="{{ old('price_min_acceptable') }}" 
                               step="0.01" min="0" placeholder="0.00">
                        @error('price_min_acceptable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="price_acceptable" class="form-label fw-semibold">Preço aceitável</label>
                        <input type="number" class="form-control @error('price_acceptable') is-invalid @enderror" 
                               id="price_acceptable" name="price_acceptable" value="{{ old('price_acceptable') }}" 
                               step="0.01" min="0" placeholder="0.00">
                        @error('price_acceptable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="price_desired" class="form-label fw-semibold">Preço desejável</label>
                        <input type="number" class="form-control @error('price_desired') is-invalid @enderror" 
                               id="price_desired" name="price_desired" value="{{ old('price_desired') }}" 
                               step="0.01" min="0" placeholder="0.00">
                        @error('price_desired')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Informações sobre Taxas -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Taxa de Compra:</strong> A taxa inicial é de 10% sobre o valor da compra.
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Criar Solicitação de Compra
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Toggle campos de intermediação
document.getElementById('professional_intermediation')?.addEventListener('change', function() {
    const pricesDiv = document.getElementById('intermediation_prices');
    if (pricesDiv) {
        pricesDiv.classList.toggle('d-none', !this.checked);
    }
});
</script>
@endpush
@endsection
