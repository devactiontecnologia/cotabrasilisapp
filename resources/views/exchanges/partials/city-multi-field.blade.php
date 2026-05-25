@php
    $initialCities = isset($initialCities) && is_array($initialCities) ? array_values(array_filter($initialCities)) : [];
    $citiesRemaining = $citiesRemaining ?? 0;
    $maxCitiesProfile = $limits['max_cities'] ?? 2;
    $idPrefix = isset($idPrefix) ? (string) $idPrefix : '';
    $reqBase = rtrim((string) request()->getBasePath(), '/');
    if ($reqBase !== '') {
        $apiHotelsSearchPath = $reqBase . '/web-autocomplete/hotels';
    } else {
        $apiHotelsSearchPath = parse_url(route('web.autocomplete.hotels', [], true), PHP_URL_PATH) ?: '/web-autocomplete/hotels';
    }
@endphp
<div class="exchange-city-multi w-100" style="overflow: visible; z-index: 3;">
    <label for="{{ $idPrefix }}desired_city_search" class="form-label fw-semibold">
        Cidade
        @if($citiesRemaining <= 0)
            <span class="badge bg-danger">Limite atingido</span>
        @else
            <span class="badge rounded-pill bg-body-secondary border text-secondary fw-medium">{{ $citiesRemaining }} restante(s)</span>
        @endif
    </label>
    @if($citiesRemaining <= 0)
        <small class="text-warning d-block mb-2">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Você atingiu o limite de {{ $maxCitiesProfile }} cidades distintas em ofertas ativas.
            Você pode reutilizar cidades já usadas em outras ofertas.
        </small>
    @endif
    <div id="{{ $idPrefix }}desired_city_chips" class="d-flex flex-wrap gap-2 mb-2"></div>
    <div class="position-relative">
        <input type="text"
               id="{{ $idPrefix }}desired_city_search"
               class="form-control @error('desired_cities') is-invalid @enderror"
               autocomplete="off"
               placeholder="Digite para buscar cidades cadastradas (hotéis ativos)…">
        <div id="{{ $idPrefix }}desired_city_dropdown"
             class="list-group position-absolute w-100 shadow border rounded mt-1 d-none"
             style="z-index: 1050; max-height: 240px; overflow-y: auto;"></div>
    </div>
    <div id="{{ $idPrefix }}desired_city_hidden"></div>
    @error('desired_cities')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-secondary d-block mt-1 lh-base">Selecione uma ou mais cidades da lista. Filtre pelo estado ao lado, se quiser.</small>
</div>

<script>
(function () {
    const idP = @json($idPrefix);
    const INITIAL = {!! json_encode($initialCities) !!};
    const apiPath = @json($apiHotelsSearchPath);
    let selected = Array.isArray(INITIAL) ? INITIAL.slice() : [];
    let debounceTimer = null;

    const chipsEl = document.getElementById(idP + 'desired_city_chips');
    const hiddenEl = document.getElementById(idP + 'desired_city_hidden');
    const searchEl = document.getElementById(idP + 'desired_city_search');
    const dropdownEl = document.getElementById(idP + 'desired_city_dropdown');
    const stateEl = document.getElementById(idP + 'desired_state');

    if (!chipsEl || !hiddenEl || !searchEl || !dropdownEl) return;

    function renderHidden() {
        hiddenEl.innerHTML = '';
        selected.forEach(function (city) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'desired_cities[]';
            inp.value = city;
            hiddenEl.appendChild(inp);
        });
    }

    function renderChips() {
        chipsEl.innerHTML = '';
        selected.forEach(function (city) {
            const span = document.createElement('span');
            span.className = 'badge rounded-pill bg-success-subtle text-success border border-success d-inline-flex align-items-center gap-2 py-2 px-3';
            span.textContent = city;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-close btn-close-sm';
            btn.style.fontSize = '0.65rem';
            btn.setAttribute('aria-label', 'Remover');
            btn.addEventListener('click', function () {
                selected = selected.filter(function (c) { return c !== city; });
                renderChips();
                renderHidden();
            });
            span.appendChild(btn);
            chipsEl.appendChild(span);
        });
        renderHidden();
    }

    function hideDropdown() {
        dropdownEl.classList.add('d-none');
        dropdownEl.innerHTML = '';
    }

    function showDropdown(items) {
        dropdownEl.innerHTML = '';
        if (!items.length) {
            hideDropdown();
            return;
        }
        items.forEach(function (row) {
            const cityName = row.city || row.name || '';
            if (!cityName) return;
            const a = document.createElement('button');
            a.type = 'button';
            a.className = 'list-group-item list-group-item-action py-2';
            a.textContent = row.label || (cityName + (row.state ? ', ' + row.state : ''));
            a.addEventListener('click', function () {
                if (selected.indexOf(cityName) === -1) {
                    selected.push(cityName);
                    renderChips();
                }
                searchEl.value = '';
                hideDropdown();
            });
            dropdownEl.appendChild(a);
        });
        dropdownEl.classList.remove('d-none');
    }

    async function runSearch(q) {
        const query = (q || '').trim();
        if (query.length < 1) {
            hideDropdown();
            return;
        }
        try {
            const params = new URLSearchParams();
            params.set('type', 'city');
            params.set('query', query);
            if (stateEl && stateEl.value) {
                params.set('state', stateEl.value);
            }
            const path = apiPath.startsWith('/') ? apiPath : '/' + apiPath;
            const fullUrl = new URL(path, window.location.origin).href + '?' + params.toString();
            const res = await fetch(fullUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const ct = (res.headers.get('content-type') || '').toLowerCase();
            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }
            if (!ct.includes('application/json')) {
                throw new Error('not-json');
            }
            const json = await res.json();
            const data = json.data || [];
            const filtered = data.filter(function (row) {
                const name = row.city || row.name || '';
                return name && selected.indexOf(name) === -1;
            });
            if (!filtered.length) {
                dropdownEl.innerHTML = '';
                const hint = document.createElement('div');
                hint.className = 'list-group-item text-muted small py-2';
                hint.textContent = 'Nenhuma cidade encontrada com esse termo (apenas cidades de hotéis ativos no cadastro).';
                dropdownEl.appendChild(hint);
                dropdownEl.classList.remove('d-none');
                return;
            }
            showDropdown(filtered);
        } catch (e) {
            dropdownEl.innerHTML = '';
            const err = document.createElement('div');
            err.className = 'list-group-item text-danger small py-2';
            const detail = (e && e.message) ? (' (' + e.message + ')') : '';
            err.innerHTML = 'Não foi possível carregar cidades.' + detail +
                '<br><span class="text-muted">Se o erro for HTTP 404, o caminho da API não bate com a pasta da aplicação (confira <code>APP_URL</code> e acesse pelo mesmo host da barra de endereço).</span>';
            dropdownEl.appendChild(err);
            dropdownEl.classList.remove('d-none');
        }
    }

    searchEl.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const v = this.value;
        debounceTimer = setTimeout(function () { runSearch(v); }, 280);
    });

    searchEl.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideDropdown();
    });

    document.addEventListener('click', function (e) {
        if (!dropdownEl.contains(e.target) && e.target !== searchEl) {
            hideDropdown();
        }
    });

    if (stateEl) {
        stateEl.addEventListener('change', function () {
            hideDropdown();
            if (searchEl.value.trim().length >= 1) {
                runSearch(searchEl.value);
            }
        });
    }

    renderChips();

    window.exchangeCityMultiApply = window.exchangeCityMultiApply || {};
    window.exchangeCityMultiApply[idP] = function (cityNames) {
        selected = Array.isArray(cityNames) ? cityNames.slice() : [];
        renderChips();
    };
    window.exchangeCityMultiRead = window.exchangeCityMultiRead || {};
    window.exchangeCityMultiRead[idP] = function () {
        return selected.slice();
    };
})();
</script>
