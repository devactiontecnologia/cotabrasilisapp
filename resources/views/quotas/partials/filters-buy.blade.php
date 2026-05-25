<div class="row g-3 g-lg-4">
    <div class="col-md-3">
        <label for="compra_quota_type" class="form-label fw-semibold text-muted text-uppercase small">Tipo da Cota *</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-layer-group"></i></span>
            <select class="form-select border-0 shadow-sm" id="compra_quota_type" name="compra_quota_type" required>
                <option value="">Selecione</option>
                <option value="fixa" {{ request('compra_quota_type') == 'fixa' ? 'selected' : '' }}>Fixa</option>
                <option value="flexivel" {{ request('compra_quota_type') == 'flexivel' ? 'selected' : '' }}>Flexível</option>
                <option value="fixa_flexivel" {{ request('compra_quota_type') == 'fixa_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
            </select>
        </div>
    </div>
    <div class="col-md-3 compra-fixa-fields" style="display: {{ in_array(request('compra_quota_type'), ['fixa', 'fixa_flexivel']) ? 'block' : 'none' }};">
        <label for="compra_fixed_date" class="form-label fw-semibold text-muted text-uppercase small">
            Cota fixa - Dia e Mês <span class="compra-fixed-date-required" style="display: {{ request('compra_quota_type') == 'fixa' ? 'inline' : 'none' }};">*</span>
        </label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-calendar-day"></i></span>
            <input type="text" class="form-control border-0 shadow-sm" id="compra_fixed_date" name="compra_fixed_date" value="{{ request('compra_fixed_date') }}" placeholder="DD/MM">
        </div>
        <small class="text-muted">Exibir apenas dia e mês</small>
    </div>
    <div class="col-md-3">
        <label for="compra_hotel" class="form-label fw-semibold text-muted text-uppercase small">Hotel</label>
        <div class="hotel-autocomplete-wrapper position-relative">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-hotel"></i></span>
                <input type="text" class="form-control border-0 shadow-sm" id="compra_hotel" name="compra_hotel" value="{{ request('compra_hotel') }}" placeholder="Ex: Cora Paradise" autocomplete="off">
            </div>
            <div id="compra-hotel-autocomplete" class="hotel-autocomplete-list"></div>
        </div>
    </div>
    <div class="col-md-3">
        <label for="compra_state" class="form-label fw-semibold text-muted text-uppercase small">Estado</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-map"></i></span>
            <select class="form-select border-0 shadow-sm" id="compra_state" name="compra_state">
                <option value="">Selecione</option>
                <option value="AC" {{ request('compra_state') == 'AC' ? 'selected' : '' }}>Acre</option>
                <option value="AL" {{ request('compra_state') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                <option value="AP" {{ request('compra_state') == 'AP' ? 'selected' : '' }}>Amapá</option>
                <option value="AM" {{ request('compra_state') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                <option value="BA" {{ request('compra_state') == 'BA' ? 'selected' : '' }}>Bahia</option>
                <option value="CE" {{ request('compra_state') == 'CE' ? 'selected' : '' }}>Ceará</option>
                <option value="DF" {{ request('compra_state') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                <option value="ES" {{ request('compra_state') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                <option value="GO" {{ request('compra_state') == 'GO' ? 'selected' : '' }}>Goiás</option>
                <option value="MA" {{ request('compra_state') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                <option value="MT" {{ request('compra_state') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                <option value="MS" {{ request('compra_state') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                <option value="MG" {{ request('compra_state') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                <option value="PA" {{ request('compra_state') == 'PA' ? 'selected' : '' }}>Pará</option>
                <option value="PB" {{ request('compra_state') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                <option value="PR" {{ request('compra_state') == 'PR' ? 'selected' : '' }}>Paraná</option>
                <option value="PE" {{ request('compra_state') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                <option value="PI" {{ request('compra_state') == 'PI' ? 'selected' : '' }}>Piauí</option>
                <option value="RJ" {{ request('compra_state') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                <option value="RN" {{ request('compra_state') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                <option value="RS" {{ request('compra_state') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                <option value="RO" {{ request('compra_state') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                <option value="RR" {{ request('compra_state') == 'RR' ? 'selected' : '' }}>Roraima</option>
                <option value="SC" {{ request('compra_state') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                <option value="SP" {{ request('compra_state') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                <option value="SE" {{ request('compra_state') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                <option value="TO" {{ request('compra_state') == 'TO' ? 'selected' : '' }}>Tocantins</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <label for="compra_city" class="form-label fw-semibold text-muted text-uppercase small">Cidade</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-location-dot"></i></span>
            <input type="text" class="form-control border-0 shadow-sm" id="compra_city" name="compra_city" value="{{ request('compra_city') }}" placeholder="Ex: Florianópolis">
        </div>
    </div>
    <div class="col-md-3">
        <label for="compra_seasonality" class="form-label fw-semibold text-muted text-uppercase small">Sazonalidade</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-sun"></i></span>
            <select class="form-select border-0 shadow-sm" id="compra_seasonality" name="compra_seasonality">
                <option value="">Selecione</option>
                <option value="altissima" {{ request('compra_seasonality') === 'altissima' ? 'selected' : '' }}>Altíssima</option>
                <option value="alta" {{ request('compra_seasonality') === 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="media" {{ request('compra_seasonality') === 'media' ? 'selected' : '' }}>Média</option>
                <option value="baixa" {{ request('compra_seasonality') === 'baixa' ? 'selected' : '' }}>Baixa</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <label for="compra_rooms" class="form-label fw-semibold text-muted text-uppercase small">Quartos</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-bed"></i></span>
            <input type="number" min="1" class="form-control border-0 shadow-sm" id="compra_rooms" name="compra_rooms" value="{{ request('compra_rooms') }}" placeholder="Ex: 2">
        </div>
    </div>
    <div class="col-md-3">
        <label for="compra_price_max" class="form-label fw-semibold text-muted text-uppercase small">Preço máximo</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-tags"></i></span>
            <input type="number" min="0" step="0.01" class="form-control border-0 shadow-sm" id="compra_price_max" name="compra_price_max" value="{{ request('compra_price_max') }}" placeholder="R$ 10.000,00">
        </div>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="compra_professional_intermediation" name="compra_professional_intermediation" value="1" {{ request('compra_professional_intermediation') == '1' ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="compra_professional_intermediation">
                Quero que um profissional de Turismo do Cota Brasilis intermedie minha compra
            </label>
        </div>
        <div id="compra_professional_prices" style="display: {{ request('compra_professional_intermediation') == '1' ? 'block' : 'none' }};" class="mt-3 row g-3">
            <div class="col-md-6">
                <label for="compra_price_acceptable" class="form-label fw-semibold text-muted text-uppercase small">Preço aceitável</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-dollar-sign"></i></span>
                    <input type="number" min="0" step="0.01" class="form-control border-0 shadow-sm" id="compra_price_acceptable" name="compra_price_acceptable" value="{{ request('compra_price_acceptable') }}" placeholder="R$ 0,00">
                </div>
            </div>
            <div class="col-md-6">
                <label for="compra_price_desired" class="form-label fw-semibold text-muted text-uppercase small">Preço desejável</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-dollar-sign"></i></span>
                    <input type="number" min="0" step="0.01" class="form-control border-0 shadow-sm" id="compra_price_desired" name="compra_price_desired" value="{{ request('compra_price_desired') }}" placeholder="R$ 0,00">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="d-flex flex-column flex-md-row align-items-md-center gap-3 mt-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success px-4 py-2">
            <i class="fas fa-search me-2"></i>Buscar cota ou fração ideal
        </button>
        <a href="{{ route('quotas.index', ['transaction_type' => request('transaction_type', 'rent')]) }}" class="btn btn-outline-secondary px-4 py-2">
            <i class="fas fa-sync me-2"></i>Limpar filtros
        </a>
    </div>
    <div class="ms-md-auto">
        <span class="text-muted small">
            <i class="fas fa-lightbulb text-success me-1"></i>Dica: Use os filtros para refinar sua busca e encontrar a cota ideal.
        </span>
    </div>
</div>

<script>
// Toggle intermediação profissional
document.getElementById('compra_professional_intermediation')?.addEventListener('change', function() {
    const pricesDiv = document.getElementById('compra_professional_prices');
    if (pricesDiv) {
        pricesDiv.style.display = this.checked ? 'block' : 'none';
    }
});

// Mostrar/ocultar campo cota fixa baseado no tipo de cota e tornar obrigatório apenas se "Fixa"
document.getElementById('compra_quota_type')?.addEventListener('change', function() {
    const fixedDateDiv = document.querySelector('.compra-fixa-fields');
    const fixedDateInput = document.getElementById('compra_fixed_date');
    const requiredIndicator = document.querySelector('.compra-fixed-date-required');
    
    if (fixedDateDiv) {
        const shouldShow = (this.value === 'fixa' || this.value === 'fixa_flexivel');
        fixedDateDiv.style.display = shouldShow ? 'block' : 'none';
        
        // Tornar obrigatório apenas se for "Fixa"
        if (fixedDateInput) {
            if (this.value === 'fixa') {
                fixedDateInput.setAttribute('required', 'required');
                fixedDateInput.required = true;
                if (requiredIndicator) {
                    requiredIndicator.style.display = 'inline';
                }
            } else {
                fixedDateInput.removeAttribute('required');
                fixedDateInput.required = false;
                if (requiredIndicator) {
                    requiredIndicator.style.display = 'none';
                }
            }
        }
    }
});

// Verificar ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const quotaType = document.getElementById('compra_quota_type');
    const fixedDateDiv = document.querySelector('.compra-fixa-fields');
    const fixedDateInput = document.getElementById('compra_fixed_date');
    const requiredIndicator = document.querySelector('.compra-fixed-date-required');
    
    if (quotaType && fixedDateDiv && fixedDateInput) {
        const value = quotaType.value;
        const shouldShow = ['fixa', 'fixa_flexivel'].includes(value);
        fixedDateDiv.style.display = shouldShow ? 'block' : 'none';
        
        // Tornar obrigatório apenas se for "Fixa"
        if (value === 'fixa') {
            fixedDateInput.setAttribute('required', 'required');
            fixedDateInput.required = true;
            if (requiredIndicator) {
                requiredIndicator.style.display = 'inline';
    }
        } else {
            fixedDateInput.removeAttribute('required');
            fixedDateInput.required = false;
            if (requiredIndicator) {
                requiredIndicator.style.display = 'none';
            }
        }
    }
});
</script>

