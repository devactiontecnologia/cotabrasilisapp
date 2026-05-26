@extends('layouts.app')

@section('title', 'Transferência confirmada')

@section('content')
<div class="container py-5" style="max-width: 520px;">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5 text-center">
            <div class="rounded-circle bg-success bg-opacity-25 d-inline-flex align-items-center justify-content-center mb-4" style="width: 88px; height: 88px;">
                <i class="fas fa-check-circle fa-3x text-success"></i>
            </div>
            <h1 class="h4 fw-bold mb-2">Transferência confirmada</h1>
            <p class="text-muted mb-4">
                O valor de <strong>R$ {{ number_format($transfer->amount, 2, ',', '.') }}</strong> foi transferido com sucesso.
            </p>

            <ul class="list-unstyled text-start bg-light rounded-3 p-3 mb-4 small">
                <li class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Data</span>
                    <span>{{ $transfer->completed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span>
                </li>
                @if($transfer->asaas_transfer_id)
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Referência</span>
                        <span class="text-truncate ms-2" style="max-width: 200px;">{{ $transfer->asaas_transfer_id }}</span>
                    </li>
                @endif
                <li class="d-flex justify-content-between py-1">
                    <span class="text-muted">Saldo atual</span>
                    <strong>R$ {{ number_format($balance, 2, ',', '.') }}</strong>
                </li>
            </ul>

            <div class="d-grid gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg">Voltar ao painel</a>
                <a href="{{ route('wallet.transfer.form') }}" class="btn btn-outline-secondary">Nova transferência</a>
            </div>
        </div>
    </div>
</div>
@endsection
