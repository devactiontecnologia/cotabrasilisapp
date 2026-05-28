@extends('layouts.app')
@section('title', 'Gerenciar troca')
@section('content')
<div class="container py-5">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-success text-white py-3 rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Troca — dados do interessado</h5>
        </div>
        <div class="card-body p-4">
            <p class="mb-4">
                <strong>Troca de período</strong> — sem valores monetários entre as partes.
                O prazo para essa transação expira em 06 horas.
                Envie o Termo de Autorização de Hospedagem para Terceiros/<em>Voucher</em> preenchido com os nomes e CPFs dos interessados, e assinado pelo Gov.br.<br>
                Você receberá alertas por <em>email</em> e <em>Whatsapp</em>.
            </p>

            @php $transaction->loadMissing('exchangeQuota'); @endphp
            @if($transaction->exchangeQuota)
            <div class="alert alert-warning border-0 mb-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-exchange-alt me-2"></i>Cota oferecida pelo interessado</h6>
                <p class="mb-1"><strong>{{ $transaction->exchangeQuota->hotel_name }}</strong> — {{ $transaction->exchangeQuota->location }}</p>
                <p class="mb-0 small">{{ $transaction->exchangeQuota->start_date?->format('d/m/Y') }} a {{ $transaction->exchangeQuota->end_date?->format('d/m/Y') }}</p>
            </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-6">
                    @php
                        $renterCpf = optional($transaction->renter->profile)->cpf ?? null;
                        if ($renterCpf) {
                            $renterCpf = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', preg_replace('/\D/', '', $renterCpf));
                        }
                    @endphp
                    <p class="mb-1 text-muted">Nome / CPF</p>
                    <p class="fw-semibold">
                        {{ $transaction->renter->name }}
                        @if($renterCpf)
                            — CPF: {{ $renterCpf }}
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1 text-muted">Hotel / Cota</p>
                    <p class="fw-semibold">{{ $transaction->quota->hotel_name }} — {{ $transaction->quota->location }}</p>
                </div>
                <div class="col-md-6">
                    @foreach($transaction->quota->getPeriodDisplayLines() as $periodLine)
                    <p class="mb-1 text-muted" style="color:#000 !important;">{{ trim($periodLine['label']) }}</p>
                    <p class="fw-semibold mb-2">{{ $periodLine['formatted'] }}</p>
                    @endforeach
                    @if($transaction->quota->getPeriodDisplayLines() === [])
                    <p class="mb-1 text-muted" style="color:#000 !important;">Período</p>
                    <p class="fw-semibold">{{ $transaction->quota->start_date?->format('d/m/Y') }} a {{ $transaction->quota->end_date?->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>

            @php
                $guests = $transaction->guest_names ?? [];
                $guests = is_array($guests) ? $guests : [];
            @endphp
            @if(!empty($guests))
            <div class="mb-4">
                <h6 class="fw-bold mb-2"><i class="fas fa-users me-2"></i>Pessoas que irão se hospedar (para você preencher no documento abaixo)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nome completo</th>
                                <th>CPF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($guests as $guest)
                            <tr>
                                <td>{{ $guest['name'] ?? '—' }}</td>
                                <td>{{ isset($guest['cpf']) && (string)$guest['cpf'] !== '' ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $guest['cpf']) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(empty($transaction->document_path))
            <hr>
            <div class="upload-term-section rounded-3 border bg-light border-success border-opacity-25 p-4">
                <h6 class="fw-bold mb-1 text-success">
                    <i class="fas fa-file-signature me-2"></i>Termo de Autorização de Hospedagem para Terceiros
                </h6>
                <p class="text-muted small mb-3">Anexe o documento devidamente preenchido e envie para o interessado assinar com gov.br.</p>
                <form method="POST" action="{{ route('transactions.owner-document.upload', $transaction) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="d-block mb-2 fw-semibold small text-secondary">Selecione o arquivo do documento</label>
                    <div class="file-upload-zone rounded-3 border border-2 border-dashed border-success border-opacity-50 bg-white p-4 text-center mb-3">
                        <input type="file" name="document" id="term-document-input" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <p class="small text-muted mb-0 mt-2"><i class="fas fa-file-pdf text-danger me-1"></i> PDF, JPG ou PNG</p>
                    </div>
                    @error('document')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-upload me-2"></i>Enviar para o interessado
                    </button>
                </form>
            </div>
            @else
            <p class="text-success mb-2"><i class="fas fa-check me-1"></i>Documento anexado. O interessado pode assinar com gov.br.</p>
            <a href="{{ route('transactions.download-document', $transaction) }}" class="btn btn-outline-primary btn-sm">Baixar documento</a>
            @endif

            @if(!empty($transaction->renter_signed_document_path))
            <hr>
            <h6 class="fw-bold mb-2"><i class="fas fa-file-signature me-2"></i>Documento assinado pelo interessado (gov.br)</h6>
            <a href="{{ route('transactions.download-renter-signed', $transaction) }}" class="btn btn-outline-primary btn-sm me-2">Ver documento</a>
            @if(empty($transaction->owner_signed_document_path))
            <div class="upload-term-section rounded-3 border bg-light border-success border-opacity-25 p-4 mt-3">
                <p class="text-muted small mb-3">Anexe o termo devidamente preenchido e assinado por você para finalizar o processo.</p>
                <form method="POST" action="{{ route('transactions.owner-finalize', $transaction) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="d-block mb-2 fw-semibold small text-secondary">Selecione o arquivo assinado</label>
                    <div class="rounded-3 border border-2 border-dashed border-success border-opacity-50 bg-white p-3 mb-3">
                        <input type="file" name="owner_signed_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <p class="small text-muted mb-0 mt-2">PDF, JPG ou PNG</p>
                    </div>
                    @error('owner_signed_document')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check me-2"></i>Finalizar processo</button>
                </form>
            </div>
            @else
            <p class="text-success mt-2"><i class="fas fa-check me-1"></i>Processo finalizado.@if($transaction->is_fair_exchange) Interessado pode pagar a taxa de êxito (troca justa).@else Troca concluída.@endif</p>
            @endif
            @endif

            @if(($transaction->is_fair_exchange ?? false) && !empty($transaction->payment_receipt_path))
            <hr>
            <h6 class="fw-bold mb-2">Comprovante da taxa de êxito</h6>
            <a href="{{ route('transactions.download-payment-receipt', $transaction) }}" class="btn btn-outline-primary btn-sm">Ver comprovante</a>
            @endif

            <hr>
            <a href="{{ route('transactions.owner-pending') }}" class="btn btn-outline-secondary">Voltar aos interesses</a>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('term-document-input');
    if (input) {
        input.addEventListener('change', function() {
            var zone = this.closest('.file-upload-zone');
            var hint = zone && zone.querySelector('.file-name-hint');
            if (hint) hint.remove();
            if (this.files.length && zone) {
                var p = document.createElement('p');
                p.className = 'small text-success mb-0 mt-2 file-name-hint';
                p.innerHTML = '<i class="fas fa-file me-1"></i> ' + this.files[0].name;
                zone.appendChild(p);
            }
        });
    }
});
</script>
@endpush
@endsection
