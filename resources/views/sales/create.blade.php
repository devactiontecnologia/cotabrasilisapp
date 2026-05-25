@extends('layouts.app')

@section('title', 'Criar Oferta de Venda - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Criar Nova Oferta de Venda</h4>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <form method="POST" action="{{ route('sales.store') }}">
            @csrf

            <!-- Informações Básicas -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Informações Básicas</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="quota_id" class="form-label fw-semibold">Cota (Opcional)</label>
                        <select class="form-select @error('quota_id') is-invalid @enderror" id="quota_id" name="quota_id">
                            <option value="">Não vinculada a uma cota específica</option>
                            @foreach($quotas as $quota)
                                <option value="{{ $quota->id }}" {{ old('quota_id') == $quota->id ? 'selected' : '' }}>
                                    {{ $quota->hotel_name }} - {{ $quota->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('quota_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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

                    <div class="col-md-4">
                        <label for="weeks" class="form-label fw-semibold">
                            <i class="fas fa-calendar-week me-2 text-success"></i>Semanas (1-4) *
                        </label>
                        <select class="form-select @error('weeks') is-invalid @enderror" id="weeks" name="weeks" required>
                            <option value="">Selecione</option>
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
                        <label for="number_of_rooms" class="form-label fw-semibold">
                            <i class="fas fa-door-open me-2 text-success"></i>Número de Quartos *
                        </label>
                        <input type="number" class="form-control @error('number_of_rooms') is-invalid @enderror" 
                               id="number_of_rooms" name="number_of_rooms" value="{{ old('number_of_rooms') }}" 
                               min="1" required>
                        @error('number_of_rooms')
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
                               id="city" name="city" value="{{ old('city') }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
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
                    
                    <div class="col-md-3">
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

                    <div class="col-md-12">
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

            <!-- Intermediação -->
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="professional_intermediation" 
                           id="professional_intermediation" value="1" {{ old('professional_intermediation') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="professional_intermediation">
                        Quero que um profissional de turismo intermedie minha venda
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

            <div class="d-flex justify-content-between">
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Criar Oferta de Venda
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Preencher campos automaticamente quando selecionar uma cota
document.getElementById('quota_id')?.addEventListener('change', function() {
    const quotaId = this.value;
    
    if (!quotaId) {
        // Limpar campos se não houver cota selecionada
        clearQuotaFields();
        return;
    }
    
    // Fazer requisição para buscar dados da cota
    const baseUrl = '{{ route("sales.quota.data", ["quotaId" => "PLACEHOLDER"]) }}';
    const url = baseUrl.replace('PLACEHOLDER', quotaId);
    
    console.log('Buscando dados da cota:', quotaId);
    console.log('URL:', url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.json().then(err => {
                console.error('Erro na resposta:', err);
                throw new Error(err.error || 'Erro ao buscar dados da cota');
            });
        }
        return response.json();
    })
    .then(data => {
        // Preencher campos com os dados retornados
        if (data.hotel_id) {
            const hotelSelect = document.getElementById('hotel_id');
            if (hotelSelect) {
                hotelSelect.value = data.hotel_id;
                // Disparar evento change para atualizar outros campos dependentes
                hotelSelect.dispatchEvent(new Event('change'));
            }
        }
        
        if (data.weeks) {
            const weeksSelect = document.getElementById('weeks');
            if (weeksSelect) {
                weeksSelect.value = data.weeks;
            }
        }
        
        if (data.number_of_rooms) {
            const roomsInput = document.getElementById('number_of_rooms');
            if (roomsInput) {
                roomsInput.value = data.number_of_rooms;
            }
        }
        
        if (data.state) {
            const stateSelect = document.getElementById('state');
            if (stateSelect) {
                stateSelect.value = data.state;
            }
        }
        
        if (data.city) {
            const cityInput = document.getElementById('city');
            if (cityInput) {
                cityInput.value = data.city;
                // Remover erro se existir
                cityInput.classList.remove('is-invalid');
                const errorDiv = cityInput.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        }
        
        if (data.quota_type) {
            const quotaTypeSelect = document.getElementById('quota_type');
            if (quotaTypeSelect) {
                quotaTypeSelect.value = data.quota_type;
            }
        }
        
        if (data.seasonality) {
            const seasonalitySelect = document.getElementById('seasonality');
            if (seasonalitySelect) {
                seasonalitySelect.value = data.seasonality;
            }
        }
    })
    .catch(error => {
        console.error('Erro ao buscar dados da cota:', error);
    });
});

// Função para limpar campos quando não houver cota selecionada
function clearQuotaFields() {
    const fieldsToClear = ['hotel_id', 'weeks', 'number_of_rooms', 'state', 'city', 'quota_type', 'seasonality'];
    fieldsToClear.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            if (field.tagName === 'SELECT') {
                field.value = '';
            } else {
                field.value = '';
            }
        }
    });
}

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
