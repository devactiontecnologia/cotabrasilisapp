<div class="row g-3 g-lg-4">
    <div class="col-md-4">
        <label for="hotel_name" class="form-label fw-semibold text-muted text-uppercase small">Hotel</label>
        <div class="hotel-autocomplete-wrapper position-relative">
            <div class="input-group">
                <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-hotel"></i></span>
                <input type="text" class="form-control border-0 shadow-sm fs-6" id="hotel_name" name="hotel_name" value="{{ request('hotel_name') }}" placeholder="Ex: Cora Paradise" autocomplete="off">
            </div>
            <div id="hotel-autocomplete" class="hotel-autocomplete-list"></div>
        </div>
    </div>
    <div class="col-md-4">
        <label for="month" class="form-label fw-semibold text-muted text-uppercase small">Mês</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-calendar"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="month" name="month">
                <option value="">Selecione um mês</option>
                <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>Janeiro</option>
                <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>Fevereiro</option>
                <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>Março</option>
                <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>Abril</option>
                <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>Maio</option>
                <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>Junho</option>
                <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>Julho</option>
                <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>Agosto</option>
                <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>Setembro</option>
                <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>Outubro</option>
                <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>Novembro</option>
                <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>Dezembro</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <label for="year" class="form-label fw-semibold text-muted text-uppercase small">Ano</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-calendar-alt"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="year" name="year">
                <option value="">Selecione um ano</option>
                @php
                    $currentYear = date('Y');
                    $nextYear = $currentYear + 1;
                @endphp
                <option value="{{ $currentYear }}" {{ request('year') == $currentYear ? 'selected' : '' }}>{{ $currentYear }}</option>
                <option value="{{ $nextYear }}" {{ request('year') == $nextYear ? 'selected' : '' }}>{{ $nextYear }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="city" class="form-label fw-semibold text-muted text-uppercase small">Cidade</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-location-dot"></i></span>
                <input type="text" class="form-control border-0 shadow-sm fs-6" id="city" name="city" value="{{ request('city') }}" placeholder="Ex: Florianópolis">
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="state" class="form-label fw-semibold text-muted text-uppercase small">Estado</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-map"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="state" name="state">
                <option value="">Todos</option>
                <option value="AC" {{ request('state') == 'AC' ? 'selected' : '' }}>Acre</option>
                <option value="AL" {{ request('state') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                <option value="AP" {{ request('state') == 'AP' ? 'selected' : '' }}>Amapá</option>
                <option value="AM" {{ request('state') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                <option value="BA" {{ request('state') == 'BA' ? 'selected' : '' }}>Bahia</option>
                <option value="CE" {{ request('state') == 'CE' ? 'selected' : '' }}>Ceará</option>
                <option value="DF" {{ request('state') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                <option value="ES" {{ request('state') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                <option value="GO" {{ request('state') == 'GO' ? 'selected' : '' }}>Goiás</option>
                <option value="MA" {{ request('state') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                <option value="MT" {{ request('state') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                <option value="MS" {{ request('state') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                <option value="MG" {{ request('state') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                <option value="PA" {{ request('state') == 'PA' ? 'selected' : '' }}>Pará</option>
                <option value="PB" {{ request('state') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                <option value="PR" {{ request('state') == 'PR' ? 'selected' : '' }}>Paraná</option>
                <option value="PE" {{ request('state') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                <option value="PI" {{ request('state') == 'PI' ? 'selected' : '' }}>Piauí</option>
                <option value="RJ" {{ request('state') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                <option value="RN" {{ request('state') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                <option value="RS" {{ request('state') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                <option value="RO" {{ request('state') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                <option value="RR" {{ request('state') == 'RR' ? 'selected' : '' }}>Roraima</option>
                <option value="SC" {{ request('state') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                <option value="SP" {{ request('state') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                <option value="SE" {{ request('state') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                <option value="TO" {{ request('state') == 'TO' ? 'selected' : '' }}>Tocantins</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="people" class="form-label fw-semibold text-muted text-uppercase small">Número de pessoas</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-user-friends"></i></span>
                <input type="number" min="1" class="form-control border-0 shadow-sm fs-6" id="people" name="people" value="{{ request('people') }}" placeholder="Quantos hóspedes?">
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="check_in" class="form-label fw-semibold text-muted text-uppercase small">Entrada</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-door-open"></i></span>
                <input type="date" class="form-control border-0 shadow-sm fs-6" id="check_in" name="check_in" value="{{ request('check_in') }}">
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="check_out" class="form-label fw-semibold text-muted text-uppercase small">Saída</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-door-closed"></i></span>
                <input type="date" class="form-control border-0 shadow-sm fs-6" id="check_out" name="check_out" value="{{ request('check_out') }}">
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="rooms" class="form-label fw-semibold text-muted text-uppercase small">Número de quartos</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-bed"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="rooms" name="rooms">
                <option value="">Qualquer</option>
                @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" {{ request('rooms') == $i ? 'selected' : '' }}>
                        {{ $i }} {{ $i === 1 ? 'quarto' : 'quartos' }}
                    </option>
                @endfor
                <option value="6" {{ request('rooms') == '6+' ? 'selected' : '' }}>6 quartos</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="stay_duration" class="form-label fw-semibold text-muted text-uppercase small">Pernoites</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-moon"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="stay_duration" name="stay_duration">
                <option value="">Selecione</option>
                @for($i = 1; $i <= 7; $i++)
                    <option value="{{ $i }}" {{ request('stay_duration') == (string) $i ? 'selected' : '' }}>
                        {{ $i }} {{ $i === 1 ? 'pernoite' : 'pernoites' }}
                    </option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="seasonality" class="form-label fw-semibold text-muted text-uppercase small">Sazonalidade</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-sun"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="seasonality" name="seasonality">
                <option value="">Todas</option>
                <option value="altissima" {{ request('seasonality') === 'altissima' ? 'selected' : '' }}>Altíssima</option>
                <option value="alta" {{ request('seasonality') === 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="media" {{ request('seasonality') === 'media' ? 'selected' : '' }}>Média</option>
                <option value="baixa" {{ request('seasonality') === 'baixa' ? 'selected' : '' }}>Baixa</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="quota_type" class="form-label fw-semibold text-muted text-uppercase small">Tipo de cota</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-layer-group"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="quota_type" name="quota_type">
                <option value="">Todas</option>
                <option value="fixa" {{ request('quota_type') === 'fixa' ? 'selected' : '' }}>Fixa</option>
                <option value="flexivel" {{ request('quota_type') === 'flexivel' ? 'selected' : '' }}>Flexível</option>
                <option value="fixa_flexivel" {{ request('quota_type') === 'fixa_flexivel' ? 'selected' : '' }}>Fixa + Flexível</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="hidromassagem" class="form-label fw-semibold text-muted text-uppercase small">Hidromassagem</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-hot-tub"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="hidromassagem" name="hidromassagem">
                <option value="">Qualquer</option>
                <option value="1" {{ request('hidromassagem') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('hidromassagem') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="academia" class="form-label fw-semibold text-muted text-uppercase small">Academia</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-dumbbell"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="academia" name="academia">
                <option value="">Qualquer</option>
                <option value="1" {{ request('academia') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('academia') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="estacionamento_gratuito" class="form-label fw-semibold text-muted text-uppercase small">Estacionamento Gratuito</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-parking"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="estacionamento_gratuito" name="estacionamento_gratuito">
                <option value="">Qualquer</option>
                <option value="1" {{ request('estacionamento_gratuito') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('estacionamento_gratuito') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="vista_mar" class="form-label fw-semibold text-muted text-uppercase small">Vista Mar</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-water"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="vista_mar" name="vista_mar">
                <option value="">Qualquer</option>
                <option value="1" {{ request('vista_mar') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('vista_mar') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="lareira" class="form-label fw-semibold text-muted text-uppercase small">Lareira</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-fire"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="lareira" name="lareira">
                <option value="">Qualquer</option>
                <option value="1" {{ request('lareira') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('lareira') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="adega" class="form-label fw-semibold text-muted text-uppercase small">Adega</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-wine-bottle"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="adega" name="adega">
                <option value="">Qualquer</option>
                <option value="1" {{ request('adega') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('adega') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="area_kids" class="form-label fw-semibold text-muted text-uppercase small">Área <i>Kids</i> </label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-child"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="area_kids" name="area_kids">
                <option value="">Qualquer</option>
                <option value="1" {{ request('area_kids') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('area_kids') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="area_trabalho" class="form-label fw-semibold text-muted text-uppercase small">Área de Trabalho</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-briefcase"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="area_trabalho" name="area_trabalho">
                <option value="">Qualquer</option>
                <option value="1" {{ request('area_trabalho') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('area_trabalho') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="spa" class="form-label fw-semibold text-muted text-uppercase small">Spa</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-spa"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="spa" name="spa">
                <option value="">Qualquer</option>
                <option value="1" {{ request('spa') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('spa') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="piscina" class="form-label fw-semibold text-muted text-uppercase small">Piscina</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-swimming-pool"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="piscina" name="piscina">
                <option value="">Qualquer</option>
                <option value="1" {{ request('piscina') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('piscina') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="wifi" class="form-label fw-semibold text-muted text-uppercase small">WiFi</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-wifi"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="wifi" name="wifi">
                <option value="">Qualquer</option>
                <option value="1" {{ request('wifi') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('wifi') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    @php
        $currentTransactionType = request('transaction_type', 'rent');
        $shouldShowPrice = $currentTransactionType != 'exchange';
    @endphp
    @if($shouldShowPrice)
    <div class="col-md-6 col-xl-3 price-filter-container" data-hide-on-tab="exchange">
        <label for="price_range" class="form-label fw-semibold text-muted text-uppercase small">Faixa de preço</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-tags"></i></span>
            <div class="w-100 px-3 py-2">
                @php
                    $priceSliderMax = 250000;
                    $priceMaxValue = (float) request('price_max', request('price_range', $priceSliderMax));
                    $priceMaxValue = min(max($priceMaxValue, 0), $priceSliderMax);
                    $priceMaxFormatted = number_format($priceMaxValue, 0, ',', '.');
                @endphp
                <input type="range" class="form-range price-range-slider" name="price_range" min="0" max="{{ $priceSliderMax }}" step="1" value="{{ $priceMaxValue }}" data-tab="{{ $currentTransactionType }}">
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">R$ <span class="price-min-display">0</span></small>
                    <small class="text-muted">R$ <span class="price-max-display">{{ $priceMaxFormatted }}</span></small>
                </div>
                <input type="hidden" name="price_min" value="{{ request('price_min', 0) }}">
                <input type="hidden" name="price_max" class="price-max-input" value="{{ $priceMaxValue }}">
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-6 col-xl-3">
        <label for="breakfast" class="form-label fw-semibold text-muted text-uppercase small">Café da Manhã Incluído</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-coffee"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="breakfast" name="breakfast">
                <option value="">Qualquer</option>
                <option value="1" {{ request('breakfast') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('breakfast') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <label for="sofa_mais" class="form-label fw-semibold text-muted text-uppercase small">Sofá mais</label>
        <div class="input-group input-group">
            <span class="input-group-text bg-success-subtle border-0 text-success"><i class="fas fa-couch"></i></span>
            <select class="form-select border-0 shadow-sm fs-6" id="sofa_mais" name="sofa_mais">
                <option value="">Qualquer</option>
                <option value="1" {{ request('sofa_mais') == '1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('sofa_mais') == '0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>
</div>
<div class="d-flex flex-column flex-md-row align-items-md-center gap-3 mt-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success px-4 py-2 js-refine-submit">
            <i class="fas fa-search me-2"></i>Buscar cota ou fração ideal
        </button>
        <a
            href="{{ route('quotas.index', array_filter([
                'transaction_type' => request('transaction_type', 'rent'),
                'exchange_refine' => request('exchange_refine') ? 1 : null,
                'purchase_refine' => request('purchase_refine') ? 1 : null,
            ], fn ($v) => $v !== null && $v !== '')) }}"
            class="btn btn-outline-secondary px-4 py-2 js-refine-clear"
        >
            <i class="fas fa-sync me-2"></i>Limpar filtros
        </a>
    </div>
    <div class="ms-md-auto">
        <span class="text-muted small">
            <i class="fas fa-lightbulb text-success me-1"></i>Dica: Para aumentar o resultado das buscas, use os campos Entrada e Saída com o primeiro e último dia do mês e ano desejados.
        </span>
    </div>
</div>
