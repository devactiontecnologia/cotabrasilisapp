<div class="col-lg-6 col-xl-4 mb-4">
    <div class="card h-100 border-0 rounded-4 quota-card" style="box-shadow: 0 12px 28px rgba(15, 23, 42, .08);">
        <div class="card-header quota-card__header text-white border-0" style="background: linear-gradient(135deg, #0a8f3f 0%, #046143 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-hotel me-2 text-white"></i>{{ $quota->hotel_name }}
                </h5>
            </div>
        </div>
        
        <div class="card-body">
            @php
                $periodLines = $quota->getPeriodNightsBreakdown();
            @endphp
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                    <span class="fw-medium">{{ $quota->location }}</span>
                </div>
                @foreach($periodLines as $line)
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-alt text-muted me-2"></i>
                    <span>
                        <strong>{{ $line['label'] }}</strong> {{ $line['formatted'] }}
                        @if(isset($line['nights']))
                            ({{ $line['nights'] }} {{ $line['nights'] == 1 ? 'pernoite' : 'pernoites' }})
                        @endif
                    </span>
                </div>
                @endforeach
                @if(empty($periodLines))
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-alt text-muted me-2"></i>
                    <span class="text-muted">—</span>
                </div>
                @endif
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-users text-muted me-2"></i>
                    <span>{{ $quota->number_of_guests }} {{ $quota->number_of_guests == 1 ? 'hóspede' : 'hóspedes' }}</span>
                </div>
                @if(!$quota->is_exchange && $quota->rental_price)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-dollar-sign text-muted me-2"></i>
                        <span class="fw-bold text-success">R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</span>
                    </div>
                @elseif($quota->is_exchange)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exchange-alt text-muted me-2"></i>
                        <span class="fw-bold text-warning">Troca</span>
                    </div>
                @endif
                @if(isset($isOffer) && $isOffer)
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info">
                            <i class="fas fa-tag me-1"></i>Oferta de Aluguel
                        </span>
                    </div>
                @endif
            </div>

            @if($quota->observations)
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ Str::limit($quota->observations, 80) }}
                    </small>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Criada em {{ $quota->created_at->format('d/m/Y') }}
                </small>
                @php
                    $allowedUses = collect($quota->allowed_uses ?? []);
                    $useLabels = [
                        'rent' => ['label' => 'Alugar', 'class' => 'bg-success', 'icon' => 'fa-key'],
                        'exchange' => ['label' => 'Trocar', 'class' => 'bg-warning', 'icon' => 'fa-exchange-alt'],
                        'sell' => ['label' => 'Vender', 'class' => 'bg-danger', 'icon' => 'fa-dollar-sign'],
                        'buy' => ['label' => 'Comprar', 'class' => 'bg-primary', 'icon' => 'fa-shopping-cart'],
                    ];
                @endphp
                <div class="d-flex flex-wrap gap-1 justify-content-end">
                    @foreach($allowedUses as $use)
                        @if(isset($useLabels[$use]))
                            <span class="badge {{ $useLabels[$use]['class'] }}">
                                <i class="fas {{ $useLabels[$use]['icon'] }} me-1"></i>{{ $useLabels[$use]['label'] }}
                            </span>
                        @endif
                    @endforeach
                    @if($allowedUses->isEmpty())
                        <span class="badge bg-secondary">Não definido</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-0" style="background: #f8fbfa !important;">
            <div class="d-flex gap-2">
                <a href="{{ route('quotas.show', $quota) }}" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-eye me-1"></i>Ver
                </a>
                <a href="{{ route('quotas.edit', $quota) }}" class="btn btn-outline-primary btn-sm flex-fill">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
                @if($quota->status == 'available')
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteQuota({{ $quota->id }})">
                        <i class="fas fa-trash me-1"></i>Excluir
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>









