@extends('layouts.app')

@section('title', 'Criar Oferta de Troca - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5 exchange-offer-form-card">
    <div class="card-body p-4 p-lg-5">
        <header class="exchange-create-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom">
            <div>
                <p class="exchange-create-eyebrow text-uppercase small fw-semibold mb-2">Troca de período</p>
                <h1 class="h4 fw-semibold text-body mb-1">Criar nova oferta de troca</h1>
                <p class="text-secondary small mb-0 exchange-create-subtitle">Preencha os critérios com calma. Você pode alternar o modo de troca abaixo sem perder o que já digitou.</p>
            </div>
            <a href="{{ route('exchanges.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-pill align-self-md-center flex-shrink-0">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </header>

        <form method="POST" action="{{ route('exchanges.store') }}">
            @csrf
            <input type="hidden" name="exchange_type" value="semana">

            @php $exchangeModeOld = old('exchange_mode', 'simples'); @endphp
            <input type="hidden" name="exchange_mode" id="exchange_mode_field" value="{{ $exchangeModeOld === 'mais' ? 'mais' : 'simples' }}">

            <div class="exchange-mode-shell border rounded-4 overflow-hidden mb-4 shadow-sm">
                <div class="exchange-mode-toolbar px-3 py-3 px-lg-4 border-bottom bg-body-secondary bg-opacity-25">
                    <div class="form-label small fw-semibold text-secondary mb-2">Modo de troca <span class="text-danger">*</span></div>
                    <ul class="nav nav-pills exchange-mode-seg flex-column flex-sm-row gap-2" id="exchangeModeTabs" role="tablist">
                        <li class="nav-item flex-sm-fill" role="presentation">
                            <button class="nav-link w-100 exchange-mode-seg-btn {{ $exchangeModeOld !== 'mais' ? 'active' : '' }}" id="tab-exchange-simples"
                                    type="button" role="tab" data-bs-toggle="tab" data-bs-target="#exchange-pane-simples"
                                    aria-controls="exchange-pane-simples" aria-selected="{{ $exchangeModeOld !== 'mais' ? 'true' : 'false' }}"
                                    data-exchange-mode="simples">
                                <span class="exchange-mode-seg-title"><i class="fas fa-exchange-alt me-2 opacity-75"></i>Troca Simples</span>
                                <span class="exchange-mode-seg-desc d-none d-md-block">Troca 1:1 por período equivalente</span>
                            </button>
                        </li>
                        <li class="nav-item flex-sm-fill" role="presentation">
                            <button class="nav-link w-100 exchange-mode-seg-btn {{ $exchangeModeOld === 'mais' ? 'active' : '' }}" id="tab-exchange-mais"
                                    type="button" role="tab" data-bs-toggle="tab" data-bs-target="#exchange-pane-mais"
                                    aria-controls="exchange-pane-mais" aria-selected="{{ $exchangeModeOld === 'mais' ? 'true' : 'false' }}"
                                    data-exchange-mode="mais">
                                <span class="exchange-mode-seg-title"><i class="fas fa-balance-scale me-2 opacity-75"></i>Troca Justa / Mais</span>
                                <span class="exchange-mode-seg-desc d-none d-md-block">Diárias diferentes ou complemento em valor</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content p-4 p-lg-4 bg-body" id="exchangeModeTabContent">
                <div class="tab-pane fade {{ $exchangeModeOld !== 'mais' ? 'show active' : '' }}" id="exchange-pane-simples" role="tabpanel" aria-labelledby="tab-exchange-simples" tabindex="0">
                    <div class="exchange-flow-card rounded-3 border bg-body p-4 p-lg-4 mb-0">
                        <div class="exchange-flow-card-head d-flex gap-3 mb-4 pb-3 border-bottom">
                            <div class="exchange-flow-icon rounded-3 flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div>
                                <h2 class="h6 fw-semibold mb-1">O que você oferece e o que busca</h2>
                                <p class="small text-secondary mb-0">Escolha a cota ou fração e defina estado, cidades, hotéis e período desejados.</p>
                            </div>
                        </div>
                        @include('exchanges.partials.exchange-source-and-criteria', [
                            'domIdPrefix' => '',
                            'quotas' => $quotas,
                            'fractionsFromQuotas' => $fractionsFromQuotas,
                            'limits' => $limits,
                            'usedCities' => $usedCities,
                            'usedHotels' => $usedHotels,
                        ])
                    </div>
                </div>
                <div class="tab-pane fade {{ $exchangeModeOld === 'mais' ? 'show active' : '' }}" id="exchange-pane-mais" role="tabpanel" aria-labelledby="tab-exchange-mais" tabindex="0">
                    <div class="exchange-flow-card rounded-3 border bg-body p-4 p-lg-4 mb-4">
                        <div class="exchange-flow-card-head d-flex gap-3 mb-4 pb-3 border-bottom">
                            <div class="exchange-flow-icon rounded-3 flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div>
                                <h2 class="h6 fw-semibold mb-1">Cota e critérios desejados</h2>
                                <p class="small text-secondary mb-0">Os mesmos campos da Troca Simples. Ao mudar de aba, os valores são copiados automaticamente.</p>
                            </div>
                        </div>
                        @include('exchanges.partials.exchange-source-and-criteria', [
                            'domIdPrefix' => 'mais_',
                            'quotas' => $quotas,
                            'fractionsFromQuotas' => $fractionsFromQuotas,
                            'limits' => $limits,
                            'usedCities' => $usedCities,
                            'usedHotels' => $usedHotels,
                        ])
                    </div>
                    <div class="exchange-flow-card rounded-3 border bg-body-secondary bg-opacity-10 p-4 p-lg-4 mb-0" id="mais_fields">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                            <div class="exchange-flow-icon exchange-flow-icon--sm rounded-2 flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <h3 class="h6 fw-semibold mb-0">Complemento da troca justa</h3>
                                <p class="small text-secondary mb-0">Escolha o tipo de complemento e preencha apenas o campo correspondente.</p>
                            </div>
                        </div>
                        <div class="row g-3 g-lg-4">
                    @php $complementOld = old('complement_trade_type', 'diarias'); @endphp
                    <div class="col-12 col-md-6">
                        <label for="complement_trade_type" class="form-label fw-semibold">Tipo de complemento de troca <span class="text-danger">*</span></label>
                        <select class="form-select @error('complement_trade_type') is-invalid @enderror"
                                id="complement_trade_type" name="complement_trade_type">
                            <option value="diarias" {{ $complementOld === 'diarias' ? 'selected' : '' }}>Diárias</option>
                            <option value="diarias_dinheiro" {{ $complementOld === 'diarias_dinheiro' ? 'selected' : '' }}>Diárias + dinheiro</option>
                        </select>
                        @error('complement_trade_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 {{ $complementOld === 'diarias' ? '' : 'd-none' }}" id="complement_row_diarias">
                        <label for="days_difference" class="form-label fw-semibold">Diferença de diárias</label>
                        <input type="text" class="form-control @error('days_difference') is-invalid @enderror"
                               id="days_difference" name="days_difference" value="{{ old('days_difference') }}"
                               placeholder="Ex: +2 ou -2" inputmode="text" autocomplete="off"
                               pattern="[+-][0-9]+" title="Use +N para solicitar ou -N para ofertar diárias extras">
                        <small class="form-hint text-secondary" style="font-size: 0.875rem;">Use <b>+N</b> para solicitar o período informado mais N diárias. Use <b>-N</b> para ofertar seu período mais N diárias na troca.</small>
                        <small id="days_difference_intent_hint" class="form-hint text-success d-none d-block mt-1"></small>
                        @error('days_difference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 {{ $complementOld === 'diarias_dinheiro' ? '' : 'd-none' }}" id="complement_row_diarias_dinheiro">
                        <label for="nights_plus_money" class="form-label fw-semibold">
                            <i class="fas fa-bed me-2 text-body-secondary"></i>Diárias + dinheiro
                        </label>
                        <input type="text" class="form-control @error('nights_plus_money') is-invalid @enderror"
                               id="nights_plus_money" name="nights_plus_money" maxlength="500"
                               value="{{ old('nights_plus_money') }}"
                               placeholder="Ex.: 2 diárias a mais + R$ 300">
                        <small class="form-hint text-secondary">Descreva diárias e valor em dinheiro que complementam a troca.</small>
                        @error('nights_plus_money')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $trocaJustaVideoUrl = (string) config('app.troca_justa_video_url', '');
                        $trocaJustaEmbed = null;
                        $trocaJustaExternalUrl = null;
                        if ($trocaJustaVideoUrl !== '') {
                            if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $trocaJustaVideoUrl, $m)) {
                                $trocaJustaEmbed = 'https://www.youtube.com/embed/'.$m[1].'?rel=0';
                            } elseif (preg_match('/youtu\.be\/([^?&]+)/', $trocaJustaVideoUrl, $m)) {
                                $trocaJustaEmbed = 'https://www.youtube.com/embed/'.$m[1].'?rel=0';
                            } elseif (preg_match('/youtube\.com\/embed\/([^?&]+)/', $trocaJustaVideoUrl, $m)) {
                                $trocaJustaEmbed = 'https://www.youtube.com/embed/'.$m[1].'?rel=0';
                            } elseif (filter_var($trocaJustaVideoUrl, FILTER_VALIDATE_URL)) {
                                $trocaJustaExternalUrl = $trocaJustaVideoUrl;
                            }
                        }
                    @endphp
                    <div class="col-12">
                        <div class="card border rounded-3 shadow-sm bg-body mt-1">
                            <div class="card-body p-4">
                                <p class="text-secondary mb-3">Assista o video sobre como funciona o troca justa</p>
                                <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#modalTrocaJustaVideo">
                                    <i class="fas fa-play-circle me-2"></i>Assistir video
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12" id="multiple_hotels_section">
                        <label class="form-label fw-semibold mb-3">
                            <i class="fas fa-hotel me-2 text-body-secondary"></i>Selecionar múltiplos hotéis (limite por perfil)
                        </label>
                        
                        @php
                            $maxHotels = $limits['max_hotels'] ?? 4;
                            $hotels = \App\Models\Hotel::where('is_functioning', true)->orderBy('name')->get();
                            $selectedHotels = old('mais_desired_hotel_ids', []);
                        @endphp
                        
                        <!-- Campo de busca -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-success-subtle border-0 text-success">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control border-0 shadow-sm" id="hotel_search" 
                                       placeholder="Buscar hotel por nome, cidade ou estado...">
                            </div>
                        </div>
                        
                        <!-- Contador de selecionados -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <span id="selected_count">0</span> de {{ $maxHotels }} hotéis selecionados 
                                <span class="badge bg-success-subtle text-success ms-2">
                                    Perfil: {{ ucfirst($profile->profile_type ?? 'curioso') }}
                                </span>
                            </small>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear_hotels">
                                <i class="fas fa-times me-1"></i>Limpar seleção
                            </button>
                        </div>
                        
                        <!-- Container de hotéis -->
                        <div class="border rounded-3 p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                            <div class="row g-2" id="hotels_container">
                                @foreach($hotels as $hotel)
                                    <div class="col-md-6 col-lg-4 hotel-item" 
                                         data-name="{{ strtolower($hotel->name) }}" 
                                         data-city="{{ strtolower($hotel->city) }}" 
                                         data-state="{{ strtolower($hotel->state) }}">
                                        <div class="card hotel-select-card h-100 border-2 {{ in_array($hotel->id, $selectedHotels) ? 'border-success bg-success-subtle' : 'border-light' }}" 
                                             data-hotel-id="{{ $hotel->id }}">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input hotel-checkbox" 
                                                           type="checkbox" 
                                                           name="mais_desired_hotel_ids[]" 
                                                           value="{{ $hotel->id }}" 
                                                           id="hotel_{{ $hotel->id }}"
                                                           {{ in_array($hotel->id, $selectedHotels) ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="hotel_{{ $hotel->id }}">
                                                        <div class="d-flex align-items-start">
                                                            <i class="fas fa-hotel text-body-secondary me-2 mt-1"></i>
                                                            <div class="flex-grow-1">
                                                                <strong class="d-block">{{ $hotel->name }}</strong>
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $hotel->city }}/{{ $hotel->state }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($hotels->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-hotel fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Nenhum hotel disponível no momento.</p>
                                </div>
                            @endif
                        </div>
                        
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Selecione até {{ $maxHotels }} hotéis. Use a busca para filtrar rapidamente.
                        </small>
                        
                    </div>
                </div>
                    </div>

                <div class="modal fade" id="modalTrocaJustaVideo" tabindex="-1" aria-labelledby="modalTrocaJustaVideoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-semibold" id="modalTrocaJustaVideoLabel">Como funciona a troca justa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body pt-3">
                                @if($trocaJustaEmbed)
                                    <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark">
                                        <iframe id="iframeTrocaJustaVideo"
                                                title="Vídeo sobre troca justa"
                                                class="border-0 w-100 h-100"
                                                data-src="{{ $trocaJustaEmbed }}"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen></iframe>
                                    </div>
                                @elseif($trocaJustaExternalUrl)
                                    <p class="text-secondary mb-3">Abra o vídeo em uma nova aba do navegador.</p>
                                    <a href="{{ $trocaJustaExternalUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                                        <i class="fas fa-external-link-alt me-2"></i>Assistir em nova aba
                                    </a>
                                @else
                                    <p class="text-secondary mb-3">O vídeo ainda não foi configurado. Defina <code class="small">TROCA_JUSTA_VIDEO_URL</code> no ambiente ou assista aos conteúdos da área educativa.</p>
                                    <a href="{{ route('educational.videos') }}" class="btn btn-outline-success">
                                        <i class="fas fa-graduation-cap me-2"></i>Ver vídeos educativos
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                </div>
            </div>

            @error('exchange_mode')
                <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror

            @if(($limits['max_alerts_per_month'] ?? 0) > 0 && ($maxCitiesAlerts ?? 0) > 0)
            <div class="mb-4">
                <div class="border border-success rounded-4 p-3 p-lg-4 bg-success-subtle bg-opacity-10">
                    <div class="form-check d-flex gap-3 align-items-start mb-0">
                        <input class="form-check-input mt-1" type="checkbox" name="city_promotion" id="exchange_city_promotion" value="1"
                               {{ old('city_promotion') ? 'checked' : '' }}
                               @if(($alertsRemaining ?? 0) <= 0) disabled @endif>
                        <div>
                            <label class="form-check-label fw-semibold d-block" for="exchange_city_promotion">
                                <i class="fas fa-bullhorn text-success me-2"></i>Informe de ofertas disponíveis por cidade
                            </label>
                            <p class="small text-secondary mb-0 mt-1">Avisar em certas cidades que sua oferta está disponível. Envio por <i>e-mail</i> e <i>WhatsApp</i>. <strong>Feito pelo usuário e de acordo com seu perfil.</strong></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-none" id="exchange_city_promotion_panel">
                    <label class="form-label fw-semibold mb-3">
                        <i class="fas fa-map-marker-alt text-success me-2"></i>Selecione as cidades
                        <span class="text-muted small">(Máximo: <span id="exchange_max_cities_limit">{{ $maxCitiesAlerts }}</span>
                            @if(($profileType ?? 'curioso') === 'curioso')
                                - Perfil Curioso
                            @elseif(($profileType ?? '') === 'inteligente')
                                - Perfil Inteligente
                            @elseif(($profileType ?? '') === 'sabio')
                                - Perfil Sábio
                            @endif
                        )</span>
                    </label>

                    <div id="exchange_city_limit_warning" class="alert alert-warning d-none mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="exchange_city_limit_message"></span>
                    </div>

                    <div class="mb-3">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="exchange_city_search"
                                   placeholder="Buscar cidade..." autocomplete="off">
                        </div>
                    </div>

                    <div id="exchange_selected_cities_tags" class="mb-3 d-flex flex-wrap gap-2" style="min-height: 40px;"></div>

                    <div id="exchange_cities_container" class="border rounded p-3" style="max-height: 400px; overflow-y: auto; background: #f8fafc;">
                        @php
                            $exchangeSelectedPromotion = array_map('strval', old('promotion_cities', []));
                        @endphp
                        <div class="row g-2" id="exchange_cities_grid">
                            @foreach(($informeCidades ?? collect()) as $cidade)
                                <div class="col-md-3 col-sm-4 col-6 exchange-city-item" data-city="{{ strtolower($cidade->nome.' '.$cidade->uf) }}">
                                    <div class="exchange-city-card {{ in_array((string) $cidade->codigo_ibge, $exchangeSelectedPromotion, true) ? 'selected' : '' }}"
                                         data-city-label="{{ $cidade->nome }}/{{ $cidade->uf }}"
                                         onclick="exchangeToggleCity('{{ $cidade->codigo_ibge }}')">
                                        <input type="checkbox"
                                               name="promotion_cities[]"
                                               value="{{ $cidade->codigo_ibge }}"
                                               id="exchange_city_cb_{{ $loop->index }}"
                                               {{ in_array((string) $cidade->codigo_ibge, $exchangeSelectedPromotion, true) ? 'checked' : '' }}
                                               class="d-none">
                                        <div class="exchange-city-card-content">
                                            <i class="fas fa-map-marker-alt mb-2"></i>
                                            <span class="exchange-city-name">{{ $cidade->nome }}</span>
                                            <span class="exchange-city-uf d-block small text-muted">{{ $cidade->uf }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="exchange_no_cities_found" class="text-center text-muted py-4 d-none">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p class="mb-0">Nenhuma cidade encontrada</p>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Clique nas cidades para selecioná-las. Limite baseado no seu perfil.
                    </small>
                </div>

                <div class="exchange-callout exchange-callout--neutral border rounded-3 p-3 mt-3">
                    <small class="form-hint text-secondary d-block">
                        @if(($alertsRemaining ?? 0) > 0)
                            Você pode enviar até <strong>{{ $limits['max_alerts_per_month'] ?? 0 }} alertas por mês</strong>.
                            Restam <strong>{{ $alertsRemaining ?? 0 }} alerta(s)</strong> este mês.
                        @else
                            <span class="text-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Você atingiu o limite de alertas deste mês. O informe por cidade ficará desativado até o próximo período.
                            </span>
                        @endif
                    </small>
                </div>
            </div>
            @else
            <div class="exchange-callout exchange-callout--warn border rounded-3 p-4 mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Seu perfil não permite envio de alertas por cidade.</strong> A oferta será apenas publicada na plataforma.
            </div>
            @endif

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3 pt-2 border-top">
                <a href="{{ route('exchanges.index') }}" class="btn btn-outline-secondary order-2 order-sm-1">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-success px-4 py-2 order-1 order-sm-2">
                    <i class="fas fa-check me-2"></i>Criar oferta de troca
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.exchange-offer-form-card {
    --exchange-accent: #198754;
    --exchange-accent-rgb: 25, 135, 84;
    --exchange-surface: rgba(var(--exchange-accent-rgb), 0.06);
}

.exchange-create-eyebrow {
    letter-spacing: 0.1em;
    color: rgba(var(--exchange-accent-rgb), 0.9);
}

.exchange-create-subtitle {
    max-width: 36rem;
    line-height: 1.5;
}

.exchange-mode-shell {
    overflow: visible;
}

.exchange-mode-toolbar {
    background: var(--exchange-surface);
}

.exchange-mode-seg {
    gap: 0.5rem;
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn {
    border: 1px solid transparent;
    color: var(--bs-secondary-color);
    background: rgba(var(--bs-secondary-rgb), 0.06);
    padding: 0.75rem 1rem;
    text-align: left;
    line-height: 1.25;
    transition: background 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn:hover {
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
    background: var(--bs-body-bg);
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn.active {
    color: #fff;
    background: var(--exchange-accent);
    border-color: var(--exchange-accent);
    box-shadow: 0 2px 10px rgba(var(--exchange-accent-rgb), 0.35);
    font-weight: 600;
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn.active:hover {
    color: #fff;
    background: #157347;
    border-color: #146c43;
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn.active .exchange-mode-seg-title,
.exchange-mode-seg .nav-link.exchange-mode-seg-btn.active .exchange-mode-seg-desc {
    color: #fff;
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn.active .exchange-mode-seg-desc {
    opacity: 0.92;
}

.exchange-mode-seg .nav-link.exchange-mode-seg-btn.active i {
    color: #fff !important;
    opacity: 1 !important;
}

.exchange-mode-seg-title {
    display: block;
    font-weight: 600;
}

.exchange-mode-seg-desc {
    display: block;
    font-size: 0.75rem;
    font-weight: 400;
    opacity: 0.85;
    margin-top: 0.2rem;
}

.exchange-flow-card {
    overflow: visible;
}

.exchange-flow-icon {
    width: 2.75rem;
    height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--exchange-surface);
    color: var(--exchange-accent);
    font-size: 1.1rem;
}

.exchange-flow-icon--sm {
    width: 2.25rem;
    height: 2.25rem;
    font-size: 0.95rem;
}

.form-hint {
    font-size: 0.8125rem;
    line-height: 1.45;
    display: block;
    margin-top: 0.35rem;
}

.exchange-callout {
    background: var(--bs-body-bg);
}

.exchange-callout--neutral {
    background: rgba(var(--bs-secondary-rgb), 0.06);
}

.exchange-callout--warn {
    background: rgba(var(--bs-warning-rgb), 0.12);
    border-color: rgba(var(--bs-warning-rgb), 0.45) !important;
}

.exchange-callout--info {
    background: rgba(var(--bs-info-rgb), 0.08);
    border-color: rgba(var(--bs-info-rgb), 0.35) !important;
}

.exchange-callout-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--bs-info-rgb), 0.15);
    color: var(--bs-info);
}

/* Listas absolutas de cidade/hotel não podem ser cortadas pelo card */
.exchange-offer-form-card .card-body {
    overflow: visible;
}

#criteria_fields,
#criteria_fields .row,
[id$="criteria_fields"],
[id$="criteria_fields"] .row,
#exchange_source_and_criteria,
#exchange_source_and_criteria .row,
#exchange_source_and_criteria_mais,
#exchange_source_and_criteria_mais .row {
    overflow: visible;
}

.exchange-criteria-panel {
    background: var(--bs-body-bg);
    overflow: visible;
}

.exchange-source-block {
    overflow: visible;
}

.exchange-label-dot {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 50%;
    background: var(--exchange-accent);
    flex-shrink: 0;
}

.exchange-note {
    border-left-width: 4px !important;
}

.exchange-badge-soft {
    font-weight: 500;
    padding: 0.4rem 0.75rem;
    background: rgba(var(--bs-secondary-rgb), 0.07);
    color: var(--bs-body-color);
    border: 1px solid var(--bs-border-color);
}

.exchange-badge-soft strong {
    font-weight: 600;
}

.hotel-select-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    cursor: pointer;
}

.hotel-select-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06) !important;
}

.hotel-select-card.border-success {
    background: linear-gradient(135deg, rgba(var(--exchange-accent-rgb), 0.12) 0%, rgba(var(--exchange-accent-rgb), 0.04) 100%);
}

.hotel-select-card .form-check-input:checked {
    background-color: var(--exchange-accent);
    border-color: var(--exchange-accent);
}

.hotel-select-card .form-check-label {
    cursor: pointer;
    user-select: none;
}

#hotels_container {
    scrollbar-width: thin;
    scrollbar-color: var(--exchange-accent) #f1f1f1;
}

#hotels_container::-webkit-scrollbar {
    width: 8px;
}

#hotels_container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#hotels_container::-webkit-scrollbar-thumb {
    background: var(--exchange-accent);
    border-radius: 10px;
}

#hotels_container::-webkit-scrollbar-thumb:hover {
    filter: brightness(0.92);
}

.exchange-city-card {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
.exchange-city-card:hover {
    border-color: var(--exchange-accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--exchange-accent-rgb), 0.15);
}
.exchange-city-card.selected {
    background: var(--exchange-accent);
    border-color: var(--exchange-accent);
    box-shadow: 0 4px 12px rgba(var(--exchange-accent-rgb), 0.35);
}
.exchange-city-card-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}
.exchange-city-card i,
.exchange-city-name {
    color: #64748b;
    transition: color 0.2s ease;
}
.exchange-city-card.selected i,
.exchange-city-card.selected .exchange-city-name {
    color: #fff !important;
}
.exchange-city-item.hidden {
    display: none !important;
}
#exchange_cities_container {
    scrollbar-width: thin;
    scrollbar-color: var(--exchange-accent) #e2e8f0;
}
@keyframes exchangeShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    75% { transform: translateX(6px); }
}
@keyframes exchangeFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
function exchangeFindPromotionCheckbox(ibge) {
    const code = String(ibge);
    const boxes = document.querySelectorAll('#exchange_cities_grid input[name="promotion_cities[]"]');
    for (let i = 0; i < boxes.length; i++) {
        if (boxes[i].value === code) {
            return boxes[i];
        }
    }
    return null;
}

function exchangeTagId(ibge) {
    return 'exchange_tag_' + String(ibge);
}

function exchangeToggleCity(ibge) {
    const checkbox = exchangeFindPromotionCheckbox(ibge);
    if (!checkbox) {
        return;
    }
    const cityCard = checkbox.closest('.exchange-city-card');
    const isSelected = checkbox.checked;
    const maxCities = parseInt(document.getElementById('exchange_max_cities_limit')?.textContent || '0', 10);

    if (isSelected) {
        checkbox.checked = false;
        cityCard?.classList.remove('selected');
        exchangeRemoveCityTag(ibge);
        exchangeHideCityLimitWarning();
    } else {
        const selectedCount = document.querySelectorAll('#exchange_cities_grid input[name="promotion_cities[]"]:checked').length;
        if (maxCities > 0 && selectedCount >= maxCities) {
            exchangeShowCityLimitWarning(maxCities);
            return;
        }
        checkbox.checked = true;
        cityCard?.classList.add('selected');
        exchangeAddCityTag(ibge);
        exchangeHideCityLimitWarning();
    }
}

function exchangeAddCityTag(ibge) {
    const tagsContainer = document.getElementById('exchange_selected_cities_tags');
    if (!tagsContainer) {
        return;
    }
    const code = String(ibge);
    const safeId = exchangeTagId(code);
    if (document.getElementById(safeId)) {
        return;
    }
    const checkbox = exchangeFindPromotionCheckbox(code);
    const label = checkbox?.closest('.exchange-city-card')?.dataset?.cityLabel || code;
    const tag = document.createElement('span');
    tag.id = safeId;
    tag.className = 'badge bg-success px-3 py-2 d-flex align-items-center gap-2';
    tag.style.cssText = 'font-size: 0.9rem; animation: exchangeFadeIn 0.3s ease;';
    const icon = document.createElement('i');
    icon.className = 'fas fa-map-marker-alt';
    const labelSpan = document.createElement('span');
    labelSpan.textContent = label;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close btn-close-white';
    btn.style.fontSize = '0.7rem';
    btn.addEventListener('click', function () { exchangeRemoveCity(code); });
    tag.appendChild(icon);
    tag.appendChild(labelSpan);
    tag.appendChild(btn);
    tagsContainer.appendChild(tag);
}

function exchangeRemoveCityTag(ibge) {
    const tag = document.getElementById(exchangeTagId(ibge));
    if (tag) {
        tag.remove();
    }
}

function exchangeRemoveCity(ibge) {
    const checkbox = exchangeFindPromotionCheckbox(ibge);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.closest('.exchange-city-card')?.classList.remove('selected');
    }
    exchangeRemoveCityTag(ibge);
    exchangeHideCityLimitWarning();
}

function exchangeShowCityLimitWarning(maxCities) {
    const warningDiv = document.getElementById('exchange_city_limit_warning');
    const messageSpan = document.getElementById('exchange_city_limit_message');
    if (!warningDiv || !messageSpan) {
        return;
    }
    const profileType = '{{ $profileType ?? "curioso" }}';
    const profileName = profileType === 'inteligente' ? 'Inteligente' : (profileType === 'sabio' ? 'Sábio' : 'Curioso');
    const cityText = maxCities === 1 ? 'cidade' : 'cidades';
    messageSpan.textContent = 'Você atingiu o limite máximo de ' + maxCities + ' ' + cityText + ' permitido para o seu perfil (' + profileName + '). Remova uma cidade antes de selecionar outra.';
    warningDiv.classList.remove('d-none');
    warningDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    warningDiv.style.animation = 'exchangeShake 0.5s';
    setTimeout(function () { warningDiv.style.animation = ''; }, 500);
}

function exchangeHideCityLimitWarning() {
    const warningDiv = document.getElementById('exchange_city_limit_warning');
    if (!warningDiv) {
        return;
    }
    const selectedCount = document.querySelectorAll('#exchange_cities_grid input[name="promotion_cities[]"]:checked').length;
    const maxCities = parseInt(document.getElementById('exchange_max_cities_limit')?.textContent || '0', 10);
    if (selectedCount < maxCities || maxCities === 0) {
        warningDiv.classList.add('d-none');
    }
}

function syncComplementTradeTypeFields() {
    const modeInput = document.getElementById('exchange_mode_field');
    if (!modeInput || modeInput.value !== 'mais') {
        return;
    }
    const sel = document.getElementById('complement_trade_type');
    const rowD = document.getElementById('complement_row_diarias');
    const rowM = document.getElementById('complement_row_diarias_dinheiro');
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

// Ajusta seções internas da aba Troca Justa/Mais conforme o modo (campo oculto + abas)
function toggleExchangeMode() {
    const modeInput = document.getElementById('exchange_mode_field');
    if (!modeInput) return;

    const complementSelect = document.getElementById('complement_trade_type');
    const multipleHotelsSection = document.getElementById('multiple_hotels_section');

    if (modeInput.value === 'mais') {
        if (complementSelect) {
            complementSelect.disabled = false;
            complementSelect.required = true;
        }
        syncComplementTradeTypeFields();
        if (multipleHotelsSection) {
            multipleHotelsSection.style.display = 'none';
            multipleHotelsSection.classList.add('d-none');
        }
    } else {
        if (complementSelect) {
            complementSelect.disabled = true;
            complementSelect.required = false;
        }
        const rowD = document.getElementById('complement_row_diarias');
        const rowM = document.getElementById('complement_row_diarias_dinheiro');
        if (rowD) {
            rowD.classList.add('d-none');
        }
        if (rowM) {
            rowM.classList.add('d-none');
        }
        const daysInput = document.getElementById('days_difference');
        const nightsInput = document.getElementById('nights_plus_money');
        if (daysInput) {
            daysInput.disabled = true;
            daysInput.value = '';
        }
        if (nightsInput) {
            nightsInput.disabled = true;
            nightsInput.value = '';
        }
        if (multipleHotelsSection) {
            multipleHotelsSection.style.display = '';
            multipleHotelsSection.classList.remove('d-none');
        }
    }
}

function syncExchangeCriteriaBetweenPanels(fromPrefix, toPrefix) {
    const keys = [
        'quota_id',
        'desired_state',
        'desired_period_day_start',
        'desired_period_day_end',
        'desired_period_month',
        'desired_period_year',
        'desired_people',
        'desired_rooms',
    ];
    keys.forEach(function (k) {
        const a = document.getElementById(fromPrefix + k);
        const b = document.getElementById(toPrefix + k);
        if (a && b) {
            b.value = a.value;
        }
    });
    try {
        let cities = [];
        if (window.exchangeCityMultiRead && typeof window.exchangeCityMultiRead[fromPrefix] === 'function') {
            cities = window.exchangeCityMultiRead[fromPrefix]() || [];
        }
        if (window.exchangeCityMultiApply && typeof window.exchangeCityMultiApply[toPrefix] === 'function') {
            window.exchangeCityMultiApply[toPrefix](cities);
        }
        let hotels = [];
        if (window.exchangeHotelMultiRead && typeof window.exchangeHotelMultiRead[fromPrefix] === 'function') {
            hotels = window.exchangeHotelMultiRead[fromPrefix]() || [];
        }
        if (window.exchangeHotelMultiApply && typeof window.exchangeHotelMultiApply[toPrefix] === 'function') {
            window.exchangeHotelMultiApply[toPrefix](hotels);
        }
    } catch (e) { /* ignore */ }
}

function setExchangeCriteriaPanelDisabledState(maisPanelActive) {
    const simplesRoot = document.getElementById('exchange_source_and_criteria');
    const maisRoot = document.getElementById('exchange_source_and_criteria_mais');
    if (!simplesRoot || !maisRoot) {
        return;
    }
    const active = maisPanelActive ? maisRoot : simplesRoot;
    const passive = maisPanelActive ? simplesRoot : maisRoot;
    passive.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.disabled = true;
        if (el.tagName === 'SELECT' && el.getAttribute('name') === 'quota_id') {
            el.required = false;
        }
    });
    active.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.disabled = false;
    });
    const q = active.querySelector('select[name="quota_id"]');
    if (q) {
        q.required = true;
    }
}

// Melhorar seleção de múltiplos hotéis e verificar modo inicial
document.addEventListener('DOMContentLoaded', function() {
    const modeField = document.getElementById('exchange_mode_field');
    const tabList = document.getElementById('exchangeModeTabs');
    if (tabList && modeField) {
        tabList.addEventListener('shown.bs.tab', function () {
            const activeBtn = tabList.querySelector('button.nav-link.active[data-exchange-mode]');
            const mode = activeBtn ? activeBtn.getAttribute('data-exchange-mode') : null;
            if (mode) {
                modeField.value = mode;
                if (mode === 'mais') {
                    syncExchangeCriteriaBetweenPanels('', 'mais_');
                } else {
                    syncExchangeCriteriaBetweenPanels('mais_', '');
                }
                setExchangeCriteriaPanelDisabledState(mode === 'mais');
                toggleExchangeMode();
                syncComplementTradeTypeFields();
                if (typeof window.refreshActiveExchangePeriod === 'function') {
                    window.refreshActiveExchangePeriod();
                }
            }
        });
    }

    toggleExchangeMode();
    setExchangeCriteriaPanelDisabledState(modeField && modeField.value === 'mais');

    document.getElementById('complement_trade_type')?.addEventListener('change', function () {
        syncComplementTradeTypeFields();
        if (typeof window.refreshActiveExchangePeriod === 'function') {
            window.refreshActiveExchangePeriod();
        }
    });
    syncComplementTradeTypeFields();

    const exchangeCityPromo = document.getElementById('exchange_city_promotion');
    const exchangeCityPanel = document.getElementById('exchange_city_promotion_panel');
    if (exchangeCityPromo && exchangeCityPanel) {
        exchangeCityPromo.addEventListener('change', function() {
            exchangeCityPanel.classList.toggle('d-none', !this.checked);
            if (this.checked) {
                setTimeout(function () {
                    document.getElementById('exchange_city_search')?.focus();
                }, 100);
            }
        });
        if (exchangeCityPromo.checked) {
            exchangeCityPanel.classList.remove('d-none');
        }
    }

    document.querySelectorAll('#exchange_cities_grid input[name="promotion_cities[]"]:checked').forEach(function (checkbox) {
        exchangeAddCityTag(checkbox.value);
    });

    document.getElementById('exchange_city_search')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const cityItems = document.querySelectorAll('.exchange-city-item');
        const noCitiesFound = document.getElementById('exchange_no_cities_found');
        let visibleCount = 0;
        cityItems.forEach(function (item) {
            const cityName = item.getAttribute('data-city') || '';
            const cityCard = item.querySelector('.exchange-city-card');
            const nameEl = cityCard ? cityCard.querySelector('.exchange-city-name') : null;
            const label = nameEl ? nameEl.textContent.toLowerCase() : '';
            if (searchTerm === '' || cityName.includes(searchTerm) || label.includes(searchTerm)) {
                item.classList.remove('hidden');
                item.style.display = '';
                visibleCount++;
            } else {
                item.classList.add('hidden');
                item.style.display = 'none';
            }
        });
        if (noCitiesFound) {
            if (visibleCount === 0 && searchTerm !== '') {
                noCitiesFound.classList.remove('d-none');
            } else {
                noCitiesFound.classList.add('d-none');
            }
        }
    });

    const maxHotels = {{ $limits['max_hotels'] ?? 4 }};
    const hotelSearch = document.getElementById('hotel_search');
    const hotelsContainer = document.getElementById('hotels_container');
    const hotelCheckboxes = document.querySelectorAll('.hotel-checkbox');
    const clearHotelsBtn = document.getElementById('clear_hotels');
    const selectedCountSpan = document.getElementById('selected_count');

    if (hotelsContainer && selectedCountSpan) {
    // Função para atualizar contador
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.hotel-checkbox:checked').length;
        if (selectedCountSpan) {
            selectedCountSpan.textContent = selected;
        }
        
        // Atualizar estilo dos cards
        hotelCheckboxes.forEach(checkbox => {
            const card = checkbox.closest('.hotel-select-card');
            if (!card) return;
            
            if (checkbox.checked) {
                card.classList.add('border-success', 'bg-success-subtle');
                card.classList.remove('border-light');
            } else {
                card.classList.remove('border-success', 'bg-success-subtle');
                card.classList.add('border-light');
            }
        });
        
        // Desabilitar checkboxes se limite atingido
        if (selected >= maxHotels) {
            hotelCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.disabled = true;
                    const card = checkbox.closest('.hotel-select-card');
                    if (card) card.style.opacity = '0.5';
                }
            });
        } else {
            hotelCheckboxes.forEach(checkbox => {
                checkbox.disabled = false;
                const card = checkbox.closest('.hotel-select-card');
                if (card) card.style.opacity = '1';
            });
        }
    }
    
    // Busca de hotéis
    if (hotelSearch && hotelsContainer) {
        hotelSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const hotelItems = hotelsContainer.querySelectorAll('.hotel-item');
            
            hotelItems.forEach(item => {
                const name = item.dataset.name || '';
                const city = item.dataset.city || '';
                const state = item.dataset.state || '';
                
                if (name.includes(searchTerm) || city.includes(searchTerm) || state.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Event listeners para checkboxes
    hotelCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selected = document.querySelectorAll('.hotel-checkbox:checked').length;
            
            if (this.checked && selected > maxHotels) {
                alert(`Você pode selecionar no máximo ${maxHotels} hotéis (limite do seu perfil).`);
                this.checked = false;
            }
            
            updateSelectedCount();
        });
        
        // Adicionar efeito hover nos cards
        const card = checkbox.closest('.hotel-select-card');
        if (!card) return;
        
        card.addEventListener('click', function(e) {
            if (e.target !== checkbox && !checkbox.disabled) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
        
        card.style.cursor = 'pointer';
        card.style.transition = 'all 0.3s ease';
        
        card.addEventListener('mouseenter', function() {
            if (!checkbox.disabled && !checkbox.checked) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!checkbox.checked) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
    
    // Botão limpar seleção
    if (clearHotelsBtn) {
        clearHotelsBtn.addEventListener('click', function() {
            hotelCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedCount();
            if (hotelSearch) {
                hotelSearch.value = '';
                hotelSearch.dispatchEvent(new Event('input'));
            }
        });
    }
    
    // Inicializar contador
    updateSelectedCount();
    }

    const trocaJustaModal = document.getElementById('modalTrocaJustaVideo');
    const trocaJustaIframe = document.getElementById('iframeTrocaJustaVideo');
    if (trocaJustaModal && trocaJustaIframe) {
        const embedSrc = trocaJustaIframe.getAttribute('data-src');
        trocaJustaModal.addEventListener('shown.bs.modal', function () {
            if (embedSrc) {
                trocaJustaIframe.setAttribute('src', embedSrc);
            }
        });
        trocaJustaModal.addEventListener('hidden.bs.modal', function () {
            trocaJustaIframe.removeAttribute('src');
        });
    }
});
</script>
@include('exchanges.partials.desired-period-scripts')
@endpush
@endsection
