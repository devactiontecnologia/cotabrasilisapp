@extends('layouts.app')

@section('title', 'Detalhes da Troca - Cota Brasilis')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Detalhes da Oferta de Troca</h4>
            <div class="d-flex gap-2">
                @if($exchangeOffer->status === 'active')
                <a href="{{ route('exchanges.edit', $exchangeOffer) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                @endif
                <a href="{{ route('exchanges.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="border rounded-4 p-4 bg-light">
                    <h5 class="fw-bold mb-4">Informações da Oferta</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small">Tipo de Troca</label>
                            <p class="fw-semibold">{{ $exchangeOffer->exchange_type === 'semana' ? 'Trocar Semana para Uso' : 'Trocar Titularidade' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $exchangeOffer->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($exchangeOffer->status) }}
                                </span>
                            </p>
                        </div>
                        @if($exchangeOffer->quota)
                        <div class="col-md-12">
                            <label class="text-muted small">Cota Oferecida</label>
                            <p class="fw-semibold">{{ $exchangeOffer->quota->hotel_name }} - {{ $exchangeOffer->quota->location }}</p>
                            <p class="text-muted small">
                                {{ $exchangeOffer->quota->start_date->format('d/m/Y') }} até {{ $exchangeOffer->quota->end_date->format('d/m/Y') }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <h6 class="fw-bold mb-3">Critérios Desejados</h6>
                    <div class="row g-3">
                        @if($exchangeOffer->desired_cities_labels !== '')
                        <div class="col-md-6">
                            <label class="text-muted small">Cidade(s)</label>
                            <p class="fw-semibold">{{ $exchangeOffer->desired_cities_labels }}</p>
                        </div>
                        @endif
                        @if($exchangeOffer->desired_hotels_labels !== '')
                        <div class="col-md-6">
                            <label class="text-muted small">Hotel(es)</label>
                            <p class="fw-semibold">{{ $exchangeOffer->desired_hotels_labels }}</p>
                        </div>
                        @endif
                        @if($periodLabel = $exchangeOffer->getDesiredPeriodLabel())
                        <div class="col-md-6">
                            <label class="text-muted small">Período</label>
                            <p class="fw-semibold">{{ $periodLabel }}</p>
                        </div>
                        @endif
                        @if($exchangeOffer->desired_people)
                        <div class="col-md-3">
                            <label class="text-muted small">Pessoas</label>
                            <p class="fw-semibold">{{ $exchangeOffer->desired_people }}</p>
                        </div>
                        @endif
                        @if($exchangeOffer->desired_rooms)
                        <div class="col-md-3">
                            <label class="text-muted small">Quartos</label>
                            <p class="fw-semibold">{{ $exchangeOffer->desired_rooms }}</p>
                        </div>
                        @endif
                        @if($exchangeOffer->price_range_min || $exchangeOffer->price_range_max)
                        <div class="col-md-12">
                            <label class="text-muted small">Faixa de Preço</label>
                            <p class="fw-semibold">
                                R$ {{ number_format($exchangeOffer->price_range_min ?? 0, 2, ',', '.') }} 
                                até 
                                R$ {{ number_format($exchangeOffer->price_range_max ?? 0, 2, ',', '.') }}
                            </p>
                        </div>
                        @endif
                    </div>

                    @if($exchangeOffer->exchange_mode === 'mais')
                    <div class="mt-4 p-3 bg-warning-subtle rounded">
                        <h6 class="fw-bold text-warning">Troca MAIS</h6>
                        @if($exchangeOffer->complement_trade_type === 'diarias')
                            <p class="mb-0"><strong>Tipo de complemento:</strong> Diárias</p>
                        @elseif($exchangeOffer->complement_trade_type === 'diarias_dinheiro')
                            <p class="mb-0"><strong>Tipo de complemento:</strong> Diárias + dinheiro</p>
                        @endif
                        @if($exchangeOffer->additional_value)
                        <p class="mb-1"><strong>Valor Adicional (legado):</strong> R$ {{ number_format($exchangeOffer->additional_value, 2, ',', '.') }}</p>
                        @endif
                        @if($daysDiffLabel = $exchangeOffer->getDaysDifferenceLabel())
                        <p class="mb-0"><strong>Diferença de diárias:</strong> {{ $daysDiffLabel }}</p>
                        @endif
                    </div>
                    @endif

                    @php
                        $nightsPlusMoneyDisplay = trim((string) ($exchangeOffer->nights_plus_money ?? ''));
                        if ($nightsPlusMoneyDisplay === '' && ! empty($exchangeOffer->observations)) {
                            $nightsPlusMoneyDisplay = trim((string) $exchangeOffer->observations);
                        }
                    @endphp
                    @if($exchangeOffer->exchange_mode === 'mais' && $exchangeOffer->complement_trade_type === 'diarias_dinheiro' && $nightsPlusMoneyDisplay !== '')
                    <div class="mt-4">
                        <label class="text-muted small">Diárias + dinheiro</label>
                        <p class="fw-semibold">{{ $nightsPlusMoneyDisplay }}</p>
                    </div>
                    @elseif($exchangeOffer->exchange_mode !== 'mais' && $nightsPlusMoneyDisplay !== '')
                    <div class="mt-4">
                        <label class="text-muted small">Diárias + dinheiro</label>
                        <p class="fw-semibold">{{ $nightsPlusMoneyDisplay }}</p>
                    </div>
                    @endif

                    @if($exchangeOffer->validity_until)
                    <div class="mt-4">
                        <label class="text-muted small">Validade</label>
                        <p class="fw-semibold">
                            Até {{ $exchangeOffer->validity_until->format('d/m/Y H:i') }}
                            @if($exchangeOffer->validity_until > now())
                                <span class="badge bg-success ms-2">{{ $exchangeOffer->validity_until->diffForHumans() }}</span>
                            @else
                                <span class="badge bg-danger ms-2">Expirada</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-4 p-4 bg-light">
                    <h6 class="fw-bold mb-3">Opções Selecionadas</h6>
                    @if($exchangeOffer->selected_options && count($exchangeOffer->selected_options) > 0)
                        <ul class="list-unstyled">
                            @foreach($exchangeOffer->selected_options as $index => $option)
                                <li class="mb-2">
                                    <span class="badge bg-primary">Opção {{ $index + 1 }}</span>
                                    <p class="small mb-0 mt-1">{{ json_encode($option) }}</p>
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-muted small">
                            {{ count($exchangeOffer->selected_options) }} de {{ $exchangeOffer->max_options }} opções utilizadas
                        </p>
                    @else
                        <p class="text-muted">Nenhuma opção selecionada ainda.</p>
                    @endif
                </div>

                @if($matches && $matches->count() > 0)
                <div class="border rounded-4 p-4 bg-light mt-4">
                    <h6 class="fw-bold mb-3">Matches Encontrados</h6>
                    <p class="text-muted small mb-3">{{ $matches->count() }} cotas correspondem aos seus critérios</p>
                    <a href="#" class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-eye me-2"></i>Ver Matches
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection
