@extends('layouts.app')

@section('title', 'Detalhes da Cota')

@section('content')
<style>
    .quota-show-hero h1 {
        color: #ffffff !important;
    }
    .quota-show-hero__price-label,
    .quota-show-hero__price-value,
    .quota-show-hero__price-note {
        color: #E1AD01 !important;
    }
</style>
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $startDate = $quota->start_date ? Carbon::parse($quota->start_date) : null;
    $endDate = $quota->end_date ? Carbon::parse($quota->end_date) : null;
    $daysCount = ($startDate && $endDate) ? $startDate->diffInDays($endDate) + 1 : null;
    $seasonalityLabel = $quota->seasonality ? $quota->getSeasonalityLabel() : 'Não informada';
    $paymentLabel = $quota->payment_status ? $quota->getPaymentStatusLabel() : 'Não informado';
    $quotaStatusLabel = $quota->quota_status ? $quota->getQuotaStatusLabel() : 'Não informado';
    $statusLabels = [
        'available' => 'Disponível',
        'rented' => 'Alugada',
        'exchanged' => 'Trocada',
        'cancelled' => 'Cancelada',
    ];
    $statusLabel = $statusLabels[$quota->status] ?? Str::title($quota->status ?? 'Indefinido');
    $allowedUses = collect($quota->allowed_uses ?? []);
    $authorizations = collect($quota->authorizations ?? []);
    $fractionDetails = collect($quota->fraction_details ?? []);
    $periodBreakdown = $quota->getPeriodNightsBreakdown();
@endphp

<div class="container py-5">
    <section class="mb-4">
        <div class="quota-show-hero p-4 p-lg-5 rounded-4 text-white position-relative overflow-hidden" style="background: @if($hotelFirstImage) linear-gradient(135deg, rgba(0, 151, 57, 0.85), rgba(4, 64, 52, 0.85)), url('{{ $hotelFirstImage }}') center center / cover; @else linear-gradient(135deg, rgba(0, 151, 57, 0.92), rgba(4, 64, 52, 0.88)); @endif box-shadow: 0 30px 70px rgba(5, 74, 40, 0.25);">
            @if($hotelFirstImage)
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.3); z-index: 1;"></div>
            @endif
            <div class="position-relative" style="z-index: 2;">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-4">
                <div>
                    <span class="badge bg-light text-success fw-semibold mb-3 px-3 py-2">
                        <i class="fas {{ $quota->is_exchange ? 'fa-exchange-alt' : 'fa-dollar-sign' }} me-2"></i>{{ $quota->is_exchange ? 'Disponível para troca' : 'Disponível para aluguel' }}
                    </span>
                    <h1 class="display-6 fw-bold mb-2 text-white">{{ $quota->hotel_name }}</h1>
                    <div class="d-flex flex-wrap gap-3 text-white-75">
                        <span><i class="fas fa-map-marker-alt me-1"></i>{{ $quota->location }}</span>
                        <span><i class="fas fa-info-circle me-1"></i>{{ $statusLabel }}</span>
                        <span><i class="fas fa-sun me-1"></i>{{ $seasonalityLabel }}</span>
                    </div>
                </div>
                <div class="ms-lg-auto w-100 w-lg-auto">
                    <div class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded-4 px-4 py-3 text-lg-end">
                        <small class="text-uppercase fw-semibold d-block quota-show-hero__price-label">Preço</small>
                        @php
                            $txShow = $transactionType ?? 'rent';
                            if ($txShow === 'rental') {
                                $txShow = 'rent';
                            }
                            if ($txShow === 'purchase') {
                                $txShow = 'buy';
                            }
                            $listShow = $quota->getMarketplaceListPrice($txShow);
                        @endphp
                        @if($txShow === 'exchange')
                            <div class="fs-3 fw-bold quota-show-hero__price-value">R$ {{ number_format(0, 2, ',', '.') }}</div>
                            <p class="mb-0 small quota-show-hero__price-note">Troca — período conforme oferta</p>
                        @elseif($listShow !== null)
                            <div class="fs-3 fw-bold quota-show-hero__price-value">R$ {{ number_format($listShow, 2, ',', '.') }}</div>
                        @else
                            <p class="mb-0 fw-semibold quota-show-hero__price-value">Negociação sob consulta</p>
                        @endif
                    </div>
                </div>
            </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4"><i class="fas fa-suitcase-rolling text-success me-2"></i>Resumo da Oferta Cadastrada</h5>
                    <div class="row g-3">
                        @foreach($periodBreakdown as $periodItem)
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-calendar-alt"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">{{ trim($periodItem['label']) }}</small>
                                    <p class="mb-0 fw-semibold">{{ $periodItem['formatted'] }}</p>
                                    <small class="text-muted">{{ $periodItem['nights'] }} {{ $periodItem['nights'] == 1 ? 'pernoite' : 'pernoites' }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @if($periodBreakdown === [])
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-calendar-alt"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Período</small>
                                    <p class="mb-0 fw-semibold">{{ $startDate?->format('d/m/Y') }} a {{ $endDate?->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-users"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Hóspedes</small>
                                    <p class="mb-0 fw-semibold">{{ $quota->number_of_guests }} {{ Str::plural('pessoa', $quota->number_of_guests) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-door-closed"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Quartos</small>
                                    <p class="mb-0 fw-semibold">{{ $quota->number_of_rooms ?? 'Não informado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4"><i class="fas fa-info-circle text-success me-2"></i>Detalhes da Cota e Facilidades</h5>
                    <div class="row g-3">
                        @php
                            $profile = $quota->user->profile ?? null;
                            $isOwner = $profile && ($profile->is_quota_owner ?? false);
                            $isGestor = $profile && ($profile->is_authorized_user ?? false);
                        @endphp
                        
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-door-open text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">Quartos</h6>
                                </div>
                                <p class="mb-0 fw-semibold fs-6">{{ $quota->number_of_rooms ?? ($profile ? ($isOwner ? ($profile->owner_quota_rooms ?? $profile->gestor_quota_rooms ?? 1) : ($profile->gestor_quota_rooms ?? 1)) : 1) }} {{ ($quota->number_of_rooms ?? 1) == 1 ? 'quarto' : 'quartos' }}</p>
                                @foreach($quota->getRoomDetailsForDisplay() as $roomDetail)
                                <small class="text-muted d-block mt-2">
                                    <strong>{{ $roomDetail['title'] }}:</strong> {{ $roomDetail['description'] }}
                                </small>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-users text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">Hóspedes</h6>
                                </div>
                                <p class="mb-0 fw-semibold fs-6">{{ $quota->number_of_guests ?? ($profile ? ($isOwner ? ($profile->owner_quota_people ?? $profile->gestor_quota_people ?? 4) : ($profile->gestor_quota_people ?? 4)) : 4) }} {{ ($quota->number_of_guests ?? 4) == 1 ? 'pessoa' : 'pessoas' }}</p>
                            </div>
                        </div>

                        @foreach($quota->getRegistrationDetailsForDisplay() as $detail)
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas {{ $detail['icon'] ?? 'fa-circle-info' }} text-success"></i>
                                    <h6 class="fw-bold text-dark mb-0 small text-uppercase">{{ $detail['label'] }}</h6>
                                </div>
                                <p class="mb-0 fw-semibold fs-6">{{ $detail['value'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    {{-- Bloco de "Comodidades do Hotel" ocultado conforme solicitação do cliente --}}
                </div>
            </div>

            @if($quota->observations)
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><i class="fas fa-sticky-note text-success me-2"></i>Observações da cota</h5>
                        <p class="mb-0 text-muted">{{ $quota->observations }}</p>
                    </div>
                </div>
            @endif

            @if($quota->contract_photo_path || $quota->transferred_at || $quota->previousOwner)
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-4"><i class="fas fa-file-contract text-success me-2"></i>Termo de autorização de hospedagem/<em>voucher</em></h5>
                        <div class="row g-3">
                            @if($quota->contract_photo_path)
                                <div class="col-12">
                                    <a href="{{ asset('storage/' . $quota->contract_photo_path) }}" target="_blank" class="btn btn-outline-success w-100">
                                        <i class="fas fa-eye me-2"></i>Visualizar contrato digital
                                    </a>
                                </div>
                            @endif
                            @if($quota->previousOwner)
                                <div class="col-sm-6">
                                    <small class="text-muted text-uppercase fw-semibold">Último proprietário</small>
                                    <p class="mb-0 fw-semibold">{{ $quota->previousOwner->name }}</p>
                                </div>
                            @endif
                            @if($quota->transferred_at)
                                <div class="col-sm-6">
                                    <small class="text-muted text-uppercase fw-semibold">Transferida em</small>
                                    <p class="mb-0 fw-semibold">{{ $quota->transferred_at->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 88px; height: 88px; background: rgba(0, 151, 57, 0.14);">
                        <i class="fas fa-user text-success fs-2"></i>
                    </div>
                    <h6 class="fw-semibold mb-1">{{ $quota->user->name }}</h6>
                    @if($quota->user->profile)
                        <span class="badge bg-success-subtle text-success fw-semibold mb-3">
                            <i class="fas fa-crown me-1"></i>
                            @switch($quota->user->profile->profile_type)
                                @case('curioso') Curioso @break
                                @case('inteligente') Inteligente @break
                                @case('sabio') Sábio @break
                            @endswitch
                        </span>
                    @endif
                    <div class="mt-3">
                        <small class="text-muted d-block">
                            <i class="fas fa-clock me-1"></i>Última atualização: {{ $quota->updated_at?->format('d/m/Y \à\s H:i') }}
                        </small>
                    </div>
                </div>
            </div>


            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-hand-holding-usd text-success me-2"></i>Ações</h5>
                    @if(isset($hotelInoperant) && $hotelInoperant)
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Hotel temporariamente inoperante: aluguel e trocas indisponíveis.
                        </div>
                        @auth
                            @if($quota->user_id == auth()->id())
                                <a href="{{ route('quotas.transfer', $quota) }}" class="btn btn-outline-success w-100 mb-3">
                                    <i class="fas fa-user-shield me-2"></i>Transferir titularidade
                                </a>
                            @endif
                        @endauth
                    @endif

                    @if($quota->status == 'available' && $quota->user_id != auth()->id())
                        @php
                            $currentTransactionType = $transactionType ?? request('transaction_type', 'rent');
                        @endphp
                        @if($currentTransactionType == 'exchange' || $quota->is_exchange)
                            <a href="{{ route('quotas.negotiate', ['quota' => $quota, 'type' => 'exchange']) }}" class="btn btn-warning btn-lg w-100 text-white mb-3">
                                <i class="fas fa-exchange-alt me-2"></i>Iniciar Troca
                            </a>
                            <p class="text-muted small mb-0 text-center">
                                <i class="fas fa-info-circle me-1"></i>Prepare uma cota equivalente para a negociação (fração ou cota de troca, sem valores monetários).
                            </p>
                        @elseif(in_array($currentTransactionType, ['buy', 'purchase'], true))
                            <a href="{{ route('quotas.negotiate', ['quota' => $quota, 'type' => 'buy']) }}" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Iniciar Compra
                            </a>
                            <p class="text-muted small mb-0 text-center">
                                <i class="fas fa-shield-alt me-1"></i>Pagamento protegido com contrato digital.
                            </p>
                        @else
                            <a href="{{ route('quotas.negotiate', ['quota' => $quota, 'type' => 'rent']) }}" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Alugar
                            </a>
                            <p class="text-muted small mb-0 text-center">
                                <i class="fas fa-shield-alt me-1"></i>Pagamento protegido com contrato digital.
                            </p>
                        @endif
                    @elseif($quota->user_id == auth()->id())
                        <div class="d-grid gap-2">
                            <a href="{{ route('quotas.edit', $quota) }}" class="btn btn-success">
                                <i class="fas fa-edit me-2"></i>Editar informações
                            </a>
                            <button type="button" class="btn btn-outline-danger delete-quota-btn" data-quota-id="{{ $quota->id }}">
                                <i class="fas fa-trash me-2"></i>Excluir cota
                            </button>
                        </div>
                    @else
                        <p class="text-muted small mb-0 text-center">
                            <i class="fas fa-lock me-1"></i>Esta cota não está mais disponível para negociação.
                        </p>
                    @endif
                </div>
            </div>

            <a href="{{ route('quotas.my') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-2"></i>Voltar a minhas cotas
            </a>
        </div>
    </div>
</div>

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.delete-quota-btn').forEach(button => {
        button.addEventListener('click', () => {
            const quotaId = button.getAttribute('data-quota-id');
            if (confirm('Tem certeza que deseja excluir esta cota?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/quotas/${quotaId}`;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = '{{ csrf_token() }}';

                form.appendChild(methodInput);
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
@endsection