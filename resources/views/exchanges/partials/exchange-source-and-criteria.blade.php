@php
    $domIdPrefix = isset($domIdPrefix) ? (string) $domIdPrefix : '';
    $criteriaWrapperId = $criteriaWrapperId ?? ($domIdPrefix === '' ? 'exchange_source_and_criteria' : 'exchange_source_and_criteria_mais');
    $citiesCount = count($usedCities ?? []);
    $hotelsCount = count($usedHotels ?? []);
    $citiesRemaining = ($limits['max_cities'] ?? 2) - $citiesCount;
    $hotelsRemaining = ($limits['max_hotels'] ?? 4) - $hotelsCount;
    $noSelectable = $quotas->isEmpty() && (!isset($fractionsFromQuotas) || $fractionsFromQuotas->count() === 0);
@endphp

<div class="mb-0 exchange-criteria-root" id="{{ $criteriaWrapperId }}" data-exchange-criteria-prefix="{{ $domIdPrefix }}">
    <section class="exchange-source-block rounded-3 border p-3 p-md-4 mb-4 bg-body-secondary bg-opacity-10">
        <label for="{{ $domIdPrefix }}quota_id" class="form-label fw-semibold d-flex align-items-center gap-2 mb-2">
            <span class="exchange-label-dot" aria-hidden="true"></span>
            Minhas cotas ou frações cadastradas <span class="text-danger">*</span>
        </label>
        <select class="form-select @error('quota_id') is-invalid @enderror" id="{{ $domIdPrefix }}quota_id" name="quota_id" required>
            <option value="">Selecione uma cota ou fração</option>

            @foreach($quotas as $quota)
                @php
                    $locationParts = explode(',', $quota->location);
                    $city = trim($locationParts[0] ?? $quota->location);
                    $state = trim($locationParts[1] ?? '');
                    $period = $quota->start_date?->format('d/m/Y') . ' até ' . $quota->end_date?->format('d/m/Y');
                    $quotaDays = ($quota->start_date && $quota->end_date)
                        ? \Carbon\Carbon::parse($quota->start_date)->diffInDays(\Carbon\Carbon::parse($quota->end_date)) + 1
                        : 0;
                    $quotaNights = $quotaDays > 0 ? $quotaDays - 1 : 0;
                @endphp
                <option value="{{ $quota->id }}" data-pernoites="{{ $quotaNights }}" {{ old('quota_id') == $quota->id ? 'selected' : '' }}>
                    {{ $quota->hotel_name }} - {{ $city }}{{ $state ? ', ' . $state : '' }} - Período: {{ $period }}
                </option>
            @endforeach

            @if(isset($fractionsFromQuotas) && $fractionsFromQuotas->count() > 0)
                @foreach($fractionsFromQuotas as $fraction)
                    @php
                        $fractionStart = \Carbon\Carbon::parse($fraction->start_date)->format('d/m/Y');
                        $fractionEnd = \Carbon\Carbon::parse($fraction->end_date)->format('d/m/Y');
                        $days = $fraction->number_of_days ?? 0;
                        $nights = $days > 0 ? $days - 1 : 0;
                        $weekLabel = isset($fraction->week_number) ? 'Semana ' . $fraction->week_number . ' - ' : '';
                        $hotelName = $fraction->hotel->name ?? ($fraction->quota->hotel_name ?? 'Hotel não informado');
                    @endphp
                    <option value="{{ $fraction->quota_id }}" data-pernoites="{{ $nights }}" {{ old('quota_id') == $fraction->quota_id ? 'selected' : '' }}>
                        {{ $weekLabel }}[Fração] {{ $hotelName }} - {{ $fractionStart }} a {{ $fractionEnd }} ({{ $nights }} {{ $nights == 1 ? 'pernoite' : 'pernoites' }})
                    </option>
                @endforeach
            @endif
        </select>
        @error('quota_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if($noSelectable)
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Você não possui cotas disponíveis para troca. <a href="{{ route('quotas.create') }}">Cadastre uma cota</a> primeiro.
            </div>
        @endif
    </section>

    <section class="mb-4">
        <h2 class="h6 fw-semibold text-body mb-2">Trocar cota ou fração para uso <span class="text-danger">*</span></h2>
        <div class="exchange-note border-start border-success rounded-end py-2 px-3 bg-body-secondary bg-opacity-25">
            <p class="small text-secondary mb-0">
                <i class="fas fa-info-circle me-2 text-success"></i>
                <strong class="text-body">Troca para uso:</strong>
                Troca de períodos para uso. Vale para Troca simples e Troca justa/mais.
            </p>
        </div>
        @error('exchange_type')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </section>

    <section class="exchange-criteria-panel border rounded-3 p-3 p-md-4 mb-0" id="{{ $domIdPrefix }}criteria_fields">
        <div class="d-flex flex-column flex-lg-row flex-lg-wrap justify-content-between align-items-start gap-3 mb-3 pb-3 border-bottom">
            <div class="flex-grow-1">
                <h3 class="h6 fw-semibold text-body mb-1">Critérios desejados</h3>
                <p class="form-hint text-secondary mb-0">Local, hotéis e período da estadia que você busca na troca.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge rounded-pill exchange-badge-soft">
                    Até <strong>{{ $limits['max_cities'] ?? 2 }}</strong> cidades
                </span>
                <span class="badge rounded-pill exchange-badge-soft">
                    Até <strong>{{ $limits['max_hotels'] ?? 4 }}</strong> hotéis
                </span>
            </div>
        </div>
        @if($citiesCount > 0 || $hotelsCount > 0)
            <p class="form-hint text-secondary mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                Uso nas ofertas ativas:
                cidades <strong>{{ $citiesCount }}/{{ $limits['max_cities'] ?? 2 }}</strong>
                · hotéis <strong>{{ $hotelsCount }}/{{ $limits['max_hotels'] ?? 4 }}</strong>
            </p>
        @endif

        <div class="row g-3 g-lg-4">
            <div class="col-12 col-md-6 col-lg-4">
                <label for="{{ $domIdPrefix }}desired_state" class="form-label fw-semibold">Estado</label>
                <select class="form-select @error('desired_state') is-invalid @enderror" id="{{ $domIdPrefix }}desired_state" name="desired_state">
                    <option value="">Selecione</option>
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
            <div class="col-12 col-md-6 col-lg-4">
                @include('exchanges.partials.city-multi-field', [
                    'initialCities' => old('desired_cities', []),
                    'citiesRemaining' => $citiesRemaining,
                    'limits' => $limits,
                    'idPrefix' => $domIdPrefix,
                ])
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                @include('exchanges.partials.hotel-multi-field', [
                    'initialHotels' => old('desired_hotels', []),
                    'hotelsRemaining' => $hotelsRemaining,
                    'limits' => $limits,
                    'idPrefix' => $domIdPrefix,
                ])
            </div>

            @include('exchanges.partials.desired-period-fields', [
                'domIdPrefix' => $domIdPrefix,
                'exchangeOffer' => $exchangeOffer ?? null,
            ])

            <div class="col-12 col-sm-6 col-xl-3">
                <label for="{{ $domIdPrefix }}desired_people" class="form-label fw-semibold">Pessoas</label>
                <input type="number" class="form-control @error('desired_people') is-invalid @enderror"
                       id="{{ $domIdPrefix }}desired_people" name="desired_people" value="{{ old('desired_people') }}"
                       min="1" max="10">
                @error('desired_people')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <label for="{{ $domIdPrefix }}desired_rooms" class="form-label fw-semibold">Quartos</label>
                <input type="number" class="form-control @error('desired_rooms') is-invalid @enderror"
                       id="{{ $domIdPrefix }}desired_rooms" name="desired_rooms" value="{{ old('desired_rooms') }}"
                       min="1">
                @error('desired_rooms')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>
</div>
