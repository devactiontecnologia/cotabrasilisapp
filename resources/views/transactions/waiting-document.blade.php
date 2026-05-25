@extends('layouts.app')
@section('title', 'Aguardando documento')
@section('content')
<style>
    .waiting-document-banner {
        background-color: #cff4fc;
        border: 1px solid #9eeaf9;
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        color: #055160;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
    }
    /* Blocos fixos: não usam .alert para não serem escondidos pelo script global */
    .doc-available-fixed {
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        color: #0f5132;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
    }
    .owner-pix-fixed {
        background-color: #cff4fc;
        border: 1px solid #9eeaf9;
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        color: #055160;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
    }
    .go-payment-fixed {
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        color: #0f5132;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
    }
    .signed-sent-fixed {
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        color: #0f5132;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
    }
</style>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-success text-white py-3 rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Aguardando documento do proprietário</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-1"><strong>Hotel:</strong> {{ $transaction->quota->hotel_name }} — {{ $transaction->quota->location }}</p>
                    <div class="mb-3">
                        @foreach($transaction->quota->getPeriodDisplayLines() as $periodLine)
                        <p class="text-muted mb-1"><strong>{{ $periodLine['label'] }}</strong> {{ $periodLine['formatted'] }}</p>
                        @endforeach
                        @if($transaction->quota->getPeriodDisplayLines() === [])
                        <p class="text-muted mb-0"><strong>Período:</strong> {{ $transaction->quota->start_date ? \Carbon\Carbon::parse($transaction->quota->start_date)->format('d/m/Y') : '-' }} a {{ $transaction->quota->end_date ? \Carbon\Carbon::parse($transaction->quota->end_date)->format('d/m/Y') : '-' }}</p>
                        @endif
                    </div>
                    <p class="mb-4">O prazo para essa transação expira em 12 horas. O proprietário da Cota e você precisam cumprir os prazos de suas tarefas. Aguarde ele enviar o Termo de Autorização de Hospedagem para Terceiros/<i>Voucher</i> para você assinar com gov.br e devolver.<br>Você receberá alertas por <em>email</em> e <em>Whatsapp</em>.</p>

                    {{-- Aviso fixo: exibido até o proprietário anexar o documento; só esconde quando workflow_step for doc_available --}}
                    @php
                        $step = $transaction->workflow_step ?? 'awaiting_owner_doc';
                        $hideWaiting = ($step === \App\Models\QuotaTransaction::WORKFLOW_DOC_AVAILABLE);
                    @endphp
                    <div id="waiting-box" class="waiting-document-banner {{ $hideWaiting ? 'd-none' : '' }}">
                        <i class="fas fa-clock me-2"></i>
                        <span id="waiting-text">Aguardando documento...</span>
                        <small class="d-block mt-1 text-muted">Prazo: {{ $transaction->document_upload_deadline?->format('d/m/Y H:i') }}</small>
                    </div>

                    <div id="doc-available-box" class="{{ ($transaction->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_DOC_AVAILABLE ? '' : 'd-none' }}">
                        <div id="doc-available-banner" class="doc-available-fixed">
                            <i class="fas fa-check-circle me-2"></i><strong>Documento disponível</strong>
                        </div>
                        <div id="owner-pix-container" class="owner-pix-fixed {{ !empty($transaction->owner_pix) ? '' : 'd-none' }}">
                            <strong>O PIX do proprietário para pagamento do aluguel da cota é:</strong><br>
                            <span class="fs-6" id="owner-pix-value">{{ $transaction->owner_pix ?? '' }}</span>
                        </div>
                        @if($transaction->document_path)
                        <p class="mb-3"><a href="{{ route('transactions.download-document', $transaction) }}" class="btn btn-outline-primary"><i class="fas fa-download me-1"></i>Baixar documento enviado pelo proprietário</a></p>
                        @endif
                        <form method="POST" action="{{ route('transactions.renter-signed.upload', $transaction) }}" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <label class="form-label fw-semibold">Documento assinado com gov.br <span class="text-danger">*</span></label>
                            <input type="file" name="document" class="form-control mb-3" accept=".pdf,.jpg,.jpeg,.png" required>
                            @error('document')<div class="text-danger small">{{ $message }}</div>@enderror
                            <label class="form-label fw-semibold">Anexe o comprovante de pagamento do valor do aluguel <span class="text-danger">*</span></label>
                            <input type="file" name="payment_receipt" class="form-control mb-2" accept=".pdf,.jpg,.jpeg,.png" required>
                            <p class="small text-muted">Comprovante da transferência PIX para o proprietário.</p>
                            @error('payment_receipt')<div class="text-danger small">{{ $message }}</div>@enderror
                            <button type="submit" class="btn btn-success mt-2"><i class="fas fa-upload me-1"></i>Enviar documento assinado e comprovante</button>
                        </form>
                    </div>

                    <div id="signed-sent-box" class="{{ ($transaction->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_RENTER_SIGNED ? '' : 'd-none' }}">
                        <div class="signed-sent-fixed">
                            <i class="fas fa-check me-2"></i>Documento assinado enviado. Aguarde o proprietário finalizar o processo.
                        </div>
                    </div>

                    <div id="go-payment-box" class="{{ ($transaction->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_AWAITING_TAX_PAYMENT ? '' : 'd-none' }}">
                        <div class="go-payment-fixed">
                            <i class="fas fa-arrow-right me-2"></i>Proprietário finalizou. Você já possue o termo de autorização de hospedagem/<i>Voucher</i> assinado pelo Gov.br por ambas as partes. <a href="{{ route('transactions.payment', $transaction) }}" class="fw-semibold text-decoration-underline" style="color: #0a3622;">Pague a taxa de êxito para conclusão da transação</a>
                        </div>
                    </div>
                    <hr>
                    <p>
                        <h4>Muito Grato, Boralá Brasil! Cota Brasilis</h4>
                        <img src="{{ asset('images/logo/logo.png') }}" alt="Cota Brasilis" class="mt-2" style="max-height: 120px; width: auto; display: block;">
                    </p>

                    <hr>
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function() {
    const step = '{{ $transaction->workflow_step ?? "awaiting_owner_doc" }}';
    if (step === 'awaiting_tax_payment' || step === 'tax_paid' || step === 'completed') return;
    const url = '{{ route("transactions.status", $transaction) }}';
    function poll() {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.workflow_step === 'doc_available') {
                    document.getElementById('waiting-box').classList.add('d-none');
                    document.getElementById('doc-available-box').classList.remove('d-none');
                    if (data.document_path) {
                        const box = document.getElementById('doc-available-box');
                        if (!box.querySelector('a[href][target="_blank"]')) {
                            const p = document.createElement('p');
                            p.className = 'mb-3';
                            const a = document.createElement('a');
                            a.href = data.document_path;
                            a.target = '_blank';
                            a.className = 'btn btn-outline-primary';
                            a.innerHTML = '<i class="fas fa-download me-1"></i>Baixar documento enviado pelo proprietário';
                            p.appendChild(a);
                            const form = box.querySelector('form');
                            if (form) form.parentNode.insertBefore(p, form);
                            else box.appendChild(p);
                        }
                    }
                    if (data.owner_pix) {
                        const pixEl = document.getElementById('owner-pix-value');
                        const container = document.getElementById('owner-pix-container');
                        if (pixEl) pixEl.textContent = data.owner_pix;
                        if (container) container.classList.remove('d-none');
                    }
                } else if (data.workflow_step === 'renter_signed_uploaded') {
                    document.getElementById('doc-available-box').classList.add('d-none');
                    document.getElementById('signed-sent-box').classList.remove('d-none');
                } else if (data.workflow_step === 'awaiting_tax_payment') {
                    document.getElementById('doc-available-box').classList.add('d-none');
                    document.getElementById('signed-sent-box').classList.add('d-none');
                    document.getElementById('go-payment-box').classList.remove('d-none');
                }
            }).catch(() => {});
    }
    setInterval(poll, 5000);
})();
</script>
@endpush
@endsection
