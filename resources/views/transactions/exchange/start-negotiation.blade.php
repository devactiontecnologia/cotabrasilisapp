@extends('layouts.app')

@section('title', 'Iniciar Troca — ' . ($quota->hotel_name ?? 'Cota'))

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">{{ $quota->hotel_name }}</h4>
                    <p class="text-muted">{{ $quota->location }}</p>

                    <h6 class="mt-4">Detalhes da cota desejada</h6>
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
                        $enabledActions = ['exchange', 'rent_exchange'];
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
                    @endphp

                    <form id="negotiationForm" method="POST" action="{{ route('quotas.exchange', $quota) }}">
                        @csrf

                        @if($quota->is_fractioned && !empty($quota->fraction_details['fraction_weeks']) && $hasEnabledPeriods)
                            <h6 class="mt-4">Cota ou Fração disponível para troca</h6>
                            <div class="list-group mb-3">
                                @foreach($quota->fraction_details['fraction_weeks'] as $weekNumber => $weekData)
                                    @foreach($weekData['periods'] as $periodIndex => $period)
                                        @if(!is_array($period) || !\App\Models\Quota::isPeriodEnabledWithAction($period) || !in_array($period['action'] ?? '', $enabledActions, true))
                                            @continue
                                        @endif
                                        @php
                                            $start = \Carbon\Carbon::parse($period['start']);
                                            $end = \Carbon\Carbon::parse($period['end']);
                                            $days = $start->diffInDays($end) + 1;
                                            $label = $start->format('d/m/Y') . ' → ' . $end->format('d/m/Y') . " ({$days} dias)";
                                        @endphp
                                        <label class="list-group-item">
                                            <input type="radio" name="selected_period" value="{{ $start->toDateString() }}|{{ $end->toDateString() }}" required>
                                            <strong class="ms-2">{{ $label }}</strong>
                                        </label>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <input type="hidden" name="selected_period" value="">
                        @endif

                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Escolha sua cota ou fração para oferecer na troca</h5>
                            <select name="exchange_quota_id" class="form-select @error('exchange_quota_id') is-invalid @enderror" required>
                                <option value="">Selecione a fração/cota que você oferece</option>
                                @foreach(($myQuotas ?? collect()) as $myQuota)
                                    <option value="{{ $myQuota->id }}" @selected(old('exchange_quota_id') == $myQuota->id)>
                                        {{ $myQuota->hotel_name }} — {{ $myQuota->location }}
                                        @if($myQuota->start_date && $myQuota->end_date)
                                            ({{ $myQuota->start_date->format('d/m/Y') }} a {{ $myQuota->end_date->format('d/m/Y') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('exchange_quota_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if(($myQuotas ?? collect())->isEmpty())
                                <p class="small text-warning mt-2 mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Você precisa ter uma cota disponível cadastrada para propor a troca.
                                </p>
                            @endif
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="is_fair_exchange" name="is_fair_exchange" @checked(old('is_fair_exchange'))>
                                <label class="form-check-label" for="is_fair_exchange">
                                    Troca justa (pode exigir taxa de êxito da plataforma)
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-bold ">Informe nome completo e CPF.</h5>
                            <small class="text-muted mb-5">Autorização para se hospedar no hotel demanda nomes completos e CPFs de todos que irão se hospedar com essa troca.</small><br>
                            <div id="guests-container mt-2" class="border rounded-3 p-3 bg-light">
                                <div class="guest-row row g-2 align-items-end mb-2" data-index="0">
                                    <div class="col-md-5">
                                        <label class="form-label small mb-1">Nome completo</label>
                                        <input type="text" name="guests[0][name]" class="form-control form-control-sm" placeholder="Nome completo">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">CPF</label>
                                        <input type="text" name="guests[0][cpf]" class="form-control form-control-sm guest-cpf" placeholder="000.000.000-00" maxlength="14">
                                    </div>
                                    <div class="col-md-3 text-md-end">
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

                        <div class="mb-3">
                            <label class="form-label">Seus dados</label>
                            <div class="border rounded-3 p-3">
                                <p class="mb-1"><strong>{{ auth()->user()->name }}</strong></p>
                                <p class="mb-0 text-muted">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning text-white" @if(($myQuotas ?? collect())->isEmpty()) disabled @endif>
                                <i class="fas fa-exchange-alt me-1"></i>Quero trocar
                            </button>
                            <a href="{{ route('quotas.show', ['quota' => $quota, 'transaction_type' => 'exchange']) }}" class="btn btn-outline-secondary">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold">Troca de frações</h6>
                    <p class="mb-2 small text-muted">Este processo não envolve pagamento de valores entre as partes — apenas a troca de períodos/frações equivalentes.</p>
                    <hr>
                    <p class="mb-0 small text-muted">Ao confirmar, você inicia a negociação. O proprietário enviará o documento para assinatura com gov.br, no mesmo fluxo do aluguel.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            row.className = 'guest-row row g-2 align-items-end mb-2';
            row.setAttribute('data-index', index);
            row.innerHTML = '<div class="col-md-5"><label class="form-label small mb-1">Nome completo</label><input type="text" name="guests[' + index + '][name]" class="form-control form-control-sm" placeholder="Nome completo"></div><div class="col-md-4"><label class="form-label small mb-1">CPF</label><input type="text" name="guests[' + index + '][cpf]" class="form-control form-control-sm guest-cpf" placeholder="000.000.000-00" maxlength="14"></div><div class="col-md-3 text-md-end"><button type="button" class="btn btn-outline-danger btn-sm btn-remove-guest" title="Remover"><i class="fas fa-minus"></i></button></div>';
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
@endsection
