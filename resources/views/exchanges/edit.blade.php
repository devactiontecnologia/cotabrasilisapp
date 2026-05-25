@extends('layouts.app')

@section('title', 'Editar Oferta de Troca - Cota Brasilis')

@push('styles')
<style>
.exchange-offer-form-card .card-body {
    overflow: visible;
}

.exchange-edit-criteria,
.exchange-edit-criteria .row {
    overflow: visible;
}
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5 exchange-offer-form-card">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Editar Oferta de Troca</h4>
            <a href="{{ route('exchanges.show', $exchangeOffer) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <form method="POST" action="{{ route('exchanges.update', $exchangeOffer) }}">
            @csrf
            @method('PUT')
            <input type="hidden" id="exchange_mode_field" value="{{ $exchangeOffer->exchange_mode }}">

            <!-- Critérios Desejados -->
            <div class="mb-4 exchange-edit-criteria">
                <h5 class="fw-bold mb-3">Critérios Desejados</h5>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="desired_state" class="form-label fw-semibold">Estado (filtro da busca)</label>
                        <select class="form-select @error('desired_state') is-invalid @enderror" id="desired_state" name="desired_state">
                            <option value="">Todos</option>
                            <option value="AC" {{ old('desired_state') == 'AC' ? 'selected' : '' }}>Acre</option>
                            <option value="AL" {{ old('desired_state') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                            <option value="AP" {{ old('desired_state') == 'AP' ? 'selected' : '' }}>Amapá</option>
                            <option value="AM" {{ old('desired_state') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                            <option value="BA" {{ old('desired_state') == 'BA' ? 'selected' : '' }}>Bahia</option>
                            <option value="CE" {{ old('desired_state') == 'CE' ? 'selected' : '' }}>Ceará</option>
                            <option value="DF" {{ old('desired_state') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                            <option value="ES" {{ old('desired_state') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                            <option value="GO" {{ old('desired_state') == 'GO' ? 'selected' : '' }}>Goiás</option>
                            <option value="MA" {{ old('desired_state') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                            <option value="MT" {{ old('desired_state') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                            <option value="MS" {{ old('desired_state') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                            <option value="MG" {{ old('desired_state') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                            <option value="PA" {{ old('desired_state') == 'PA' ? 'selected' : '' }}>Pará</option>
                            <option value="PB" {{ old('desired_state') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                            <option value="PR" {{ old('desired_state') == 'PR' ? 'selected' : '' }}>Paraná</option>
                            <option value="PE" {{ old('desired_state') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                            <option value="PI" {{ old('desired_state') == 'PI' ? 'selected' : '' }}>Piauí</option>
                            <option value="RJ" {{ old('desired_state') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                            <option value="RN" {{ old('desired_state') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                            <option value="RS" {{ old('desired_state') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                            <option value="RO" {{ old('desired_state') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                            <option value="RR" {{ old('desired_state') == 'RR' ? 'selected' : '' }}>Roraima</option>
                            <option value="SC" {{ old('desired_state') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                            <option value="SP" {{ old('desired_state') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                            <option value="SE" {{ old('desired_state') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                            <option value="TO" {{ old('desired_state') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                        </select>
                        @error('desired_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        @include('exchanges.partials.city-multi-field', [
                            'initialCities' => old('desired_cities', $exchangeOffer->getDesiredCitiesList()),
                            'citiesRemaining' => $citiesRemaining,
                            'limits' => $limits,
                        ])
                    </div>

                    <div class="col-md-4">
                        @include('exchanges.partials.hotel-multi-field', [
                            'initialHotels' => old('desired_hotels', $exchangeOffer->getDesiredHotelsList()),
                            'hotelsRemaining' => $hotelsRemaining,
                            'limits' => $limits,
                        ])
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                        @include('exchanges.partials.desired-period-fields', [
                            'domIdPrefix' => '',
                            'exchangeOffer' => $exchangeOffer,
                        ])
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="desired_people" class="form-label fw-semibold">Pessoas</label>
                        <input type="number" class="form-control @error('desired_people') is-invalid @enderror" 
                               id="desired_people" name="desired_people" 
                               value="{{ old('desired_people', $exchangeOffer->desired_people) }}" 
                               min="1" max="10">
                        @error('desired_people')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label for="desired_rooms" class="form-label fw-semibold">Quartos</label>
                        <input type="number" class="form-control @error('desired_rooms') is-invalid @enderror" 
                               id="desired_rooms" name="desired_rooms" 
                               value="{{ old('desired_rooms', $exchangeOffer->desired_rooms) }}" 
                               min="1">
                        @error('desired_rooms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="price_range_min" class="form-label fw-semibold">Faixa de Preço - Mínimo (R$)</label>
                        <input type="number" class="form-control @error('price_range_min') is-invalid @enderror" 
                               id="price_range_min" name="price_range_min" 
                               value="{{ old('price_range_min', $exchangeOffer->price_range_min) }}" 
                               step="0.01" min="0" placeholder="0.00">
                        @error('price_range_min')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="price_range_max" class="form-label fw-semibold">Faixa de Preço - Máximo (R$)</label>
                        <input type="number" class="form-control @error('price_range_max') is-invalid @enderror" 
                               id="price_range_max" name="price_range_max" 
                               value="{{ old('price_range_max', $exchangeOffer->price_range_max) }}" 
                               step="0.01" min="0" placeholder="0.00">
                        @error('price_range_max')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if($exchangeOffer->exchange_mode === 'mais')
                @php
                    $complementEdit = old('complement_trade_type', $exchangeOffer->complement_trade_type);
                    if ($complementEdit === null || $complementEdit === '') {
                        $nm = trim((string) ($exchangeOffer->nights_plus_money ?? ''));
                        $complementEdit = $nm !== '' ? 'diarias_dinheiro' : 'diarias';
                    }
                @endphp
                <div class="mb-4 border rounded-3 p-3 bg-light">
                    <h6 class="fw-semibold mb-3">Complemento da troca (Troca Justa / Mais)</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="complement_trade_type" class="form-label fw-semibold">Tipo de complemento de troca <span class="text-danger">*</span></label>
                            <select class="form-select @error('complement_trade_type') is-invalid @enderror" id="complement_trade_type" name="complement_trade_type" required>
                                <option value="diarias" {{ $complementEdit === 'diarias' ? 'selected' : '' }}>Diárias</option>
                                <option value="diarias_dinheiro" {{ $complementEdit === 'diarias_dinheiro' ? 'selected' : '' }}>Diárias + dinheiro</option>
                            </select>
                            @error('complement_trade_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 {{ $complementEdit === 'diarias' ? '' : 'd-none' }}" id="edit_complement_row_diarias">
                            <label for="days_difference" class="form-label fw-semibold">Diferença de diárias</label>
                            @php
                                $daysDiffDisplay = old('days_difference');
                                if ($daysDiffDisplay === null && $exchangeOffer->days_difference !== null) {
                                    $diffVal = (int) $exchangeOffer->days_difference;
                                    $daysDiffDisplay = $diffVal > 0 ? '+' . $diffVal : (string) $diffVal;
                                }
                            @endphp
                            <input type="text" class="form-control @error('days_difference') is-invalid @enderror"
                                   id="days_difference" name="days_difference"
                                   value="{{ $daysDiffDisplay }}"
                                   placeholder="Ex: +2 ou -2" inputmode="text" autocomplete="off"
                                   pattern="[+-][0-9]+" title="Use +N para solicitar ou -N para ofertar diárias extras">
                            <small class="text-muted">Use <b>+N</b> para solicitar ou <b>-N</b> para ofertar diárias extras além do período.</small>
                            <small id="days_difference_intent_hint" class="form-hint text-success d-none d-block mt-1"></small>
                            @error('days_difference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 {{ $complementEdit === 'diarias_dinheiro' ? '' : 'd-none' }}" id="edit_complement_row_diarias_dinheiro">
                            <label for="nights_plus_money" class="form-label fw-semibold">
                                <i class="fas fa-bed me-2 text-success"></i>Diárias + dinheiro
                            </label>
                            <input type="text" class="form-control @error('nights_plus_money') is-invalid @enderror"
                                   id="nights_plus_money" name="nights_plus_money" maxlength="500"
                                   value="{{ old('nights_plus_money', $exchangeOffer->nights_plus_money ?? ($exchangeOffer->observations ?? '')) }}"
                                   placeholder="Ex.: 2 diárias a mais + R$ 300">
                            <small class="text-muted">Descreva diárias e valor em dinheiro que complementam a troca.</small>
                            @error('nights_plus_money')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @else
            <!-- Diárias + dinheiro (ofertas modo simples / legado) -->
            <div class="mb-4">
                <label for="nights_plus_money" class="form-label fw-semibold">
                    <i class="fas fa-bed me-2 text-success"></i>Diárias + dinheiro
                </label>
                <input type="text" class="form-control @error('nights_plus_money') is-invalid @enderror"
                       id="nights_plus_money" name="nights_plus_money" maxlength="500"
                       value="{{ old('nights_plus_money', $exchangeOffer->nights_plus_money ?? ($exchangeOffer->observations ?? '')) }}"
                       placeholder="Ex.: 2 diárias a mais + R$ 300">
                <small class="text-muted">Opcional. Descreva a combinação de diárias e valor em dinheiro que complementa a troca.</small>
                @error('nights_plus_money')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <div class="d-flex justify-content-between">
                <a href="{{ route('exchanges.show', $exchangeOffer) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function syncEditComplementTradeTypeFields() {
        const sel = document.getElementById('complement_trade_type');
        const rowD = document.getElementById('edit_complement_row_diarias');
        const rowM = document.getElementById('edit_complement_row_diarias_dinheiro');
        const daysInput = document.getElementById('days_difference');
        const nightsInput = document.getElementById('nights_plus_money');
        if (!sel || !rowD || !rowM) {
            return;
        }
        const v = sel.value;
        const isDiarias = v === 'diarias';
        const isMix = v === 'diarias_dinheiro';
        rowD.classList.toggle('d-none', !isDiarias);
        rowM.classList.toggle('d-none', !isMix);
        if (daysInput) {
            daysInput.disabled = !isDiarias;
            if (!isDiarias) {
                daysInput.value = '';
            }
        }
        if (nightsInput) {
            nightsInput.disabled = !isMix;
            if (!isMix) {
                nightsInput.value = '';
            }
        }
    }
    document.getElementById('complement_trade_type')?.addEventListener('change', syncEditComplementTradeTypeFields);
    syncEditComplementTradeTypeFields();
});
</script>
@include('exchanges.partials.desired-period-scripts')
@endpush

