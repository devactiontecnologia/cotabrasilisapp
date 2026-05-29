@extends('layouts.app')

@section('title', ($transactionType == 'buy' ? 'Iniciar Compra' : ($transactionType == 'exchange' ? 'Iniciar Troca' : 'Iniciar Aluguel')) . ' — ' . ($quota->hotel_name ?? 'Cota'))

@section('content')
<div class="row g-4 align-items-start negotiation-page">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">{{ $quota->hotel_name }}</h4>
                    <p class="text-muted">{{ $quota->location }}</p>

                    <h6 class="mt-4">Detalhes da Cota</h6>
                    <ul class="list-unstyled">
                        <li><strong>Quartos:</strong> {{ $quota->number_of_rooms ?? 'N/A' }}</li>
                        <li><strong>Hóspedes:</strong> {{ $quota->number_of_guests ?? 'N/A' }}</li>
                        @foreach($quota->getPeriodDisplayLines() as $periodLine)
                            <li><strong>{{ trim($periodLine['label']) }}</strong> {{ $periodLine['formatted'] }}</li>
                        @endforeach
                        @if($quota->getPeriodDisplayLines() === [])
                            <li><strong>Período:</strong> {{ $quota->start_date?->format('d/m/Y') }} a {{ $quota->end_date?->format('d/m/Y') }}</li>
                        @endif
                    </ul>

                    @php
                        $enabledActions = match ($transactionType) {
                            'exchange' => ['exchange', 'rent_exchange'],
                            'buy' => ['sell'],
                            default => ['rent', 'rent_exchange'],
                        };
                        $saleListPrice = $transactionType === 'buy' ? ($quota->getMarketplaceListPrice('buy') ?? 0) : 0;
                        $defaultTotal = $transactionType === 'buy' ? $saleListPrice : ($quota->rental_price ?? 0);
                        $hasEnabledPeriods = false;
                        if ($quota->is_fractioned && !empty($quota->fraction_details['fraction_weeks'])) {
                            foreach ($quota->fraction_details['fraction_weeks'] as $weekData) {
                                foreach ($weekData['periods'] ?? [] as $p) {
                                    if (is_array($p) && \App\Models\Quota::isPeriodEnabledWithAction($p) && in_array($p['action'] ?? '', $enabledActions, true)) {
                                        $hasEnabledPeriods = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                        $formAction = $transactionType === 'buy'
                            ? route('quotas.buy', $quota)
                            : ($transactionType === 'exchange' ? route('quotas.exchange', $quota) : route('quotas.rent', $quota));
                        $totalDays = ($quota->start_date && $quota->end_date)
                            ? \Carbon\Carbon::parse($quota->start_date)->diffInDays(\Carbon\Carbon::parse($quota->end_date)) + 1
                            : 1;
                        if ($transactionType === 'buy') {
                            $perDay = $saleListPrice > 0 && $totalDays ? ($saleListPrice / $totalDays) : $saleListPrice;
                        } else {
                            $perDay = $quota->rental_price && $totalDays ? ($quota->rental_price / $totalDays) : ($quota->rental_price ?? 0);
                        }
                    @endphp

                    <form id="negotiationForm" method="POST" action="{{ $formAction }}">
                        @csrf

                        @if($quota->is_fractioned && !empty($quota->fraction_details['fraction_weeks']) && $hasEnabledPeriods)
                            <h6 class="mt-4">Períodos de Fração disponíveis</h6>
                            <div class="list-group mb-3">
                                @foreach($quota->fraction_details['fraction_weeks'] as $weekData)
                                    @foreach($weekData['periods'] as $period)
                                        @if(!is_array($period) || !\App\Models\Quota::isPeriodEnabledWithAction($period) || !in_array($period['action'] ?? '', $enabledActions, true))
                                            @continue
                                        @endif
                                        @php
                                            $start = \Carbon\Carbon::parse($period['start']);
                                            $end = \Carbon\Carbon::parse($period['end']);
                                            $days = $start->diffInDays($end) + 1;
                                            $priceForPeriod = round($perDay * $days, 2);
                                            $label = $start->format('d/m/Y') . ' → ' . $end->format('d/m/Y') . " ({$days} dias)";
                                        @endphp
                                        <label class="list-group-item">
                                            <input type="radio" name="selected_period" value="{{ $start->toDateString() }}|{{ $end->toDateString() }}" data-price="{{ $priceForPeriod }}" required>
                                            <strong class="ms-2">{{ $label }}</strong>
                                            <span class="float-end fw-semibold">R$ {{ number_format($priceForPeriod, 2, ',', '.') }}</span>
                                        </label>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <input type="hidden" name="selected_period" value="">
                            <div class="mb-3">
                                <p class="mb-1"><strong>Preço total:</strong></p>
                                <div class="fs-4 fw-bold">R$ {{ number_format($defaultTotal, 2, ',', '.') }}</div>
                            </div>
                        @endif

                        <input type="hidden" name="total_amount" id="total_amount" value="{{ $defaultTotal }}">

                        @if($transactionType === 'exchange')
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Escolha sua cota ou fração para oferecer na troca</h5>
                                <select name="exchange_quota_id" class="form-select" required>
                                    <option value="">Selecione</option>
                                    @foreach(($myQuotas ?? collect()) as $myQuota)
                                        <option value="{{ $myQuota->id }}">{{ $myQuota->hotel_name }} — {{ $myQuota->location }}</option>
                                    @endforeach
                                </select>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_fair_exchange" name="is_fair_exchange">
                                    <label class="form-check-label" for="is_fair_exchange">
                                        Troca justa (pode gerar taxa de êxito)
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Seus dados</label>
                            <div class="border rounded-3 p-3">
                                <p class="mb-1"><strong>{{ auth()->user()->name }}</strong></p>
                                <p class="mb-0 text-muted">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        @if($transactionType === 'rent' || $transactionType === 'buy')
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Informe as pessoas que irão se hospedar juntamente com você</h5>
                                <div id="guests-container" class="border rounded-3 p-3 bg-light">
                                    <div class="guest-row d-flex flex-wrap gap-2 align-items-end mb-2" data-index="0">
                                        <div class="flex-grow-1" style="min-width: 140px;">
                                            <label class="form-label small mb-1">Nome completo</label>
                                            <input type="text" name="guests[0][name]" class="form-control form-control-sm" placeholder="Nome completo">
                                        </div>
                                        <div style="min-width: 120px; max-width: 160px;">
                                            <label class="form-label small mb-1">CPF</label>
                                            <input type="text" name="guests[0][cpf]" class="form-control form-control-sm guest-cpf" placeholder="000.000.000-00" maxlength="14">
                                        </div>
                                        <div class="flex-shrink-0">
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-guest" title="Remover" style="display: none;">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="btn-add-guest" class="btn btn-outline-success btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Adicionar pessoa
                                </button>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                @if($transactionType == 'buy')
                                    Quero comprar
                                @elseif($transactionType == 'exchange')
                                    Quero trocar
                                @else
                                    Quero alugar
                                @endif
                            </button>
                            <a href="{{ route('quotas.show', array_merge(['quota' => $quota], request()->only('transaction_type', 'hide_buttons'))) }}" class="btn btn-outline-secondary">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 sticky-lg-top" style="top: 110px;">
                <div class="card-body p-4">
                    <h6 class="fw-semibold">Informações do Hotel</h6>
                    <p class="mb-1"><strong>{{ $quota->hotel_name }}</strong></p>
                    <p class="text-muted mb-2">{{ $quota->location }}</p>
                    <hr>
                    @if($transactionType === 'buy')
                        <p class="mb-0 small text-muted">Ao confirmar você iniciará a negociação de compra. O proprietário enviará o documento para assinatura; em seguida seguem pagamento e taxa de êxito, no mesmo fluxo do aluguel.</p>
                    @else
                        <p class="mb-0 small text-muted">Ao confirmar você iniciará a negociação. Após a confirmação e pagamento será solicitado o termo de autorização de hospedagem.</p>
                    @endif
                </div>
            </div>
        </div>
</div>
@endsection

@push('styles')
<style>
    .negotiation-page > [class*="col-"] {
        min-width: 0;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="selected_period"][type="radio"]');
    const totalInput = document.getElementById('total_amount');
    radios.forEach(r => {
        r.addEventListener('change', function() {
            const price = parseFloat(this.dataset.price || 0);
            if (totalInput) totalInput.value = price;
        });
    });

    function maskCpf(input) {
        let v = input.value.replace(/\D/g, '');
        if (v.length > 11) v = v.slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = v;
    }
    document.querySelectorAll('.guest-cpf').forEach(function(inp) {
        inp.addEventListener('input', function() { maskCpf(this); });
    });
    document.addEventListener('input', function(e) {
        if (e.target.classList && e.target.classList.contains('guest-cpf')) maskCpf(e.target);
    });

    const container = document.getElementById('guests-container');
    const btnAdd = document.getElementById('btn-add-guest');
    if (container && btnAdd) {
        function getNextIndex() {
            const rows = container.querySelectorAll('.guest-row');
            let max = -1;
            rows.forEach(function(r) {
                const i = parseInt(r.getAttribute('data-index'), 10);
                if (!isNaN(i) && i > max) max = i;
            });
            return max + 1;
        }
        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.guest-row');
            rows.forEach(function(row) {
                const btn = row.querySelector('.btn-remove-guest');
                if (btn) btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
            });
        }
        function reindexRows() {
            const rows = container.querySelectorAll('.guest-row');
            rows.forEach(function(row, i) {
                row.setAttribute('data-index', i);
                const nameInp = row.querySelector('input[name*="[name]"]');
                const cpfInp = row.querySelector('input[name*="[cpf]"]');
                if (nameInp) nameInp.name = 'guests[' + i + '][name]';
                if (cpfInp) cpfInp.name = 'guests[' + i + '][cpf]';
            });
            updateRemoveButtons();
        }
        btnAdd.addEventListener('click', function() {
            const index = getNextIndex();
            const row = document.createElement('div');
            row.className = 'guest-row d-flex flex-wrap gap-2 align-items-end mb-2';
            row.setAttribute('data-index', index);
            row.innerHTML = '<div class="flex-grow-1" style="min-width:140px"><label class="form-label small mb-1">Nome completo</label><input type="text" name="guests[' + index + '][name]" class="form-control form-control-sm" placeholder="Nome completo"></div><div style="min-width:120px;max-width:160px"><label class="form-label small mb-1">CPF</label><input type="text" name="guests[' + index + '][cpf]" class="form-control form-control-sm guest-cpf" placeholder="000.000.000-00" maxlength="14"></div><div class="flex-shrink-0"><button type="button" class="btn btn-outline-danger btn-sm btn-remove-guest" title="Remover"><i class="fas fa-minus"></i></button></div>';
            container.appendChild(row);
            row.querySelector('.guest-cpf').addEventListener('input', function() { maskCpf(this); });
            row.querySelector('.btn-remove-guest').addEventListener('click', function() {
                row.remove();
                reindexRows();
            });
            updateRemoveButtons();
        });
        container.querySelectorAll('.btn-remove-guest').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const row = this.closest('.guest-row');
                if (row && container.querySelectorAll('.guest-row').length > 1) {
                    row.remove();
                    reindexRows();
                }
            });
        });
        updateRemoveButtons();
    }
});
</script>
@endpush
