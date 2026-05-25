@extends('layouts.app')
@section('title', 'Interesses e negociações em andamento')
@section('content')
@php
    $hasOwner = $pendingAsOwner->isNotEmpty();
    $hasRenter = $pendingAsRenter->isNotEmpty();
@endphp
<div class="container py-5">
    <h4 class="mb-4"><i class="fas fa-bell me-2"></i>Interesses e negociações em andamento</h4>

    @if(!$hasOwner && !$hasRenter)
        <div class="alert alert-info">Nenhuma negociação em andamento no momento.</div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Voltar ao dashboard</a>
    @else
        @if($hasOwner)
            <h6 class="text-muted text-uppercase small mb-2">Como proprietário da cota</h6>
            <div class="list-group mb-4">
                @foreach($pendingAsOwner as $t)
                <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong>{{ $t->renter->name ?? 'Interessado' }}</strong> — {{ $t->quota->hotel_name ?? 'Cota' }}
                        <br><small class="text-muted">
                            @if(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_AWAITING_OWNER_DOC) Aguardando você enviar o documento
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_DOC_AVAILABLE) Documento enviado; aguardando assinatura do interessado
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_RENTER_SIGNED) Interessado enviou documento assinado; finalize quando possível
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_AWAITING_TAX_PAYMENT) Aguardando o interessado pagar a taxa de êxito
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_TAX_PAID) Taxa paga; aguardando comprovante final do interessado
                            @elseif($t->transaction_type === \App\Models\QuotaTransaction::TYPE_EXCHANGE) Troca em andamento — acompanhe e assine se necessário
                            @else Negociação em andamento
                            @endif
                        </small>
                    </div>
                    <a href="{{ route('transactions.owner-manage', $t) }}" class="btn btn-success btn-sm">Ver e gerenciar</a>
                </div>
                @endforeach
            </div>
        @endif

        @if($hasRenter)
            <h6 class="text-muted text-uppercase small mb-2">Como interessado (aluguel, troca ou compra)</h6>
            <div class="list-group mb-3">
                @foreach($pendingAsRenter as $t)
                <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong>{{ $t->quota->hotel_name ?? 'Cota' }}</strong>
                        <span class="text-muted"> — Proprietário: {{ $t->owner->name ?? '—' }}</span>
                        <br><small class="text-muted">
                            @if($t->transaction_type === \App\Models\QuotaTransaction::TYPE_EXCHANGE)
                                Troca em andamento
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_AWAITING_OWNER_DOC)
                                Aguardando o proprietário enviar o documento
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_DOC_AVAILABLE)
                                Documento disponível para você assinar
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_RENTER_SIGNED)
                                Aguardando o proprietário finalizar
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_AWAITING_TAX_PAYMENT)
                                É necessário pagar a taxa de êxito
                            @elseif(($t->workflow_step ?? '') === \App\Models\QuotaTransaction::WORKFLOW_TAX_PAID)
                                Envie o comprovante final para concluir
                            @else
                                Acompanhe os próximos passos na transação
                            @endif
                        </small>
                    </div>
                    <a href="{{ route('transactions.show', $t) }}" class="btn btn-primary btn-sm">Abrir transação</a>
                </div>
                @endforeach
            </div>
        @endif

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Voltar ao dashboard</a>
    @endif
</div>
@endsection
