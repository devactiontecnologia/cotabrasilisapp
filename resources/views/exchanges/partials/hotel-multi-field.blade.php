@php
    $initialHotels = isset($initialHotels) && is_array($initialHotels) ? array_values(array_filter($initialHotels)) : [];
    $hotelsRemaining = $hotelsRemaining ?? 0;
    $maxHotelsProfile = $limits['max_hotels'] ?? 4;
    $idPrefix = isset($idPrefix) ? (string) $idPrefix : '';
    $reqBase = rtrim((string) request()->getBasePath(), '/');
    if ($reqBase !== '') {
        $apiHotelsSearchPath = $reqBase . '/web-autocomplete/hotels';
    } else {
        $apiHotelsSearchPath = parse_url(route('web.autocomplete.hotels', [], true), PHP_URL_PATH) ?: '/web-autocomplete/hotels';
    }
@endphp
<div class="exchange-hotel-multi w-100" style="overflow: visible; z-index: 4;">
    <label for="{{ $idPrefix }}desired_hotel_search" class="form-label fw-semibold">
        Hotel
        @if($hotelsRemaining <= 0)
            <span class="badge bg-danger">Limite atingido</span>
        @else
            <span class="badge rounded-pill bg-body-secondary border text-secondary fw-medium">{{ $hotelsRemaining }} restante(s)</span>
        @endif
    </label>
    @if($hotelsRemaining <= 0)
        <small class="text-warning d-block mb-2">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Você atingiu o limite de {{ $maxHotelsProfile }} hotéis distintos em ofertas ativas.
            Você pode reutilizar hotéis já usados em outras ofertas.
        </small>
    @endif
    <div id="{{ $idPrefix }}desired_hotel_chips" class="d-flex flex-wrap gap-2 mb-2"></div>
    <div class="position-relative">
        <input type="text"
               id="{{ $idPrefix }}desired_hotel_search"
               class="form-control @error('desired_hotels') is-invalid @enderror"
               autocomplete="off"
               placeholder="Digite para buscar hotéis cadastrados…">
        <div id="{{ $idPrefix }}desired_hotel_dropdown"
             class="list-group position-absolute w-100 shadow border rounded mt-1 d-none"
             style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
    </div>
    <div id="{{ $idPrefix }}desired_hotel_hidden"></div>
    @error('desired_hotels')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-secondary d-block mt-1 lh-base">Selecione um ou mais hotéis da lista (mesmo critério do limite do perfil).</small>
</div>

<script>
(function () {
    const idP = @json($idPrefix);
    const INITIAL = {!! json_encode($initialHotels) !!};
    const apiPath = @json($apiHotelsSearchPath);
    let selected = Array.isArray(INITIAL) ? INITIAL.slice() : [];
    let debounceTimer = null;

    const chipsEl = document.getElementById(idP + 'desired_hotel_chips');
    const hiddenEl = document.getElementById(idP + 'desired_hotel_hidden');
    const searchEl = document.getElementById(idP + 'desired_hotel_search');
    const dropdownEl = document.getElementById(idP + 'desired_hotel_dropdown');
    const stateEl = document.getElementById(idP + 'desired_state');

    if (!chipsEl || !hiddenEl || !searchEl || !dropdownEl) return;

    function renderHidden() {
        hiddenEl.innerHTML = '';
        selected.forEach(function (name) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'desired_hotels[]';
            inp.value = name;
            hiddenEl.appendChild(inp);
        });
    }

    function renderChips() {
        chipsEl.innerHTML = '';
        selected.forEach(function (name) {
            const span = document.createElement('span');
            span.className = 'badge rounded-pill bg-success-subtle text-success border border-success d-inline-flex align-items-center gap-2 py-2 px-3';
            span.textContent = name;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-close btn-close-sm';
            btn.style.fontSize = '0.65rem';
            btn.setAttribute('aria-label', 'Remover');
            btn.addEventListener('click', function () {
                selected = selected.filter(function (h) { return h !== name; });
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
            const hotelName = row.name || '';
            if (!hotelName) return;
            const a = document.createElement('button');
            a.type = 'button';
            a.className = 'list-group-item list-group-item-action py-2';
            a.textContent = row.label || (hotelName + (row.city ? ' — ' + row.city : '') + (row.state ? '/' + row.state : ''));
            a.addEventListener('click', function () {
                if (selected.indexOf(hotelName) === -1) {
                    selected.push(hotelName);
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
            params.set('type', 'hotel');
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
                const name = row.name || '';
                return name && selected.indexOf(name) === -1;
            });
            if (!filtered.length) {
                dropdownEl.innerHTML = '';
                const hint = document.createElement('div');
                hint.className = 'list-group-item text-muted small py-2';
                hint.textContent = 'Nenhum hotel encontrado com esse termo (apenas hotéis ativos no cadastro).';
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
            err.innerHTML = 'Não foi possível carregar hotéis.' + detail +
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

    window.exchangeHotelMultiApply = window.exchangeHotelMultiApply || {};
    window.exchangeHotelMultiApply[idP] = function (hotelNames) {
        selected = Array.isArray(hotelNames) ? hotelNames.slice() : [];
        renderChips();
    };
    window.exchangeHotelMultiRead = window.exchangeHotelMultiRead || {};
    window.exchangeHotelMultiRead[idP] = function () {
        return selected.slice();
    };
})();
</script>
