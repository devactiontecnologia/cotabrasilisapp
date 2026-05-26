@extends('layouts.app')

@section('title', 'Transferência - Carteira Digital')

@section('content')
<div class="container py-5" style="max-width: 520px;">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="fas fa-wallet text-success fa-lg"></i>
                </div>
                <div>
                    <h1 class="h4 fw-bold mb-0">Transferir valor</h1>
                    <p class="text-muted small mb-0">Carteira digital Cota Brasilis</p>
                </div>
            </div>

            <p class="text-muted mb-4">
                Saldo disponível: <strong class="text-dark">R$ {{ number_format($balance, 2, ',', '.') }}</strong>
            </p>

            @if(!($walletActive ?? false))
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-info-circle me-1"></i>{{ $walletMessage }}
                </div>
            @endif

            <form method="POST" action="{{ route('wallet.transfer.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="amount" class="form-label fw-semibold">Valor da transferência (R$)</label>
                    <input type="text"
                           name="amount"
                           id="amount"
                           class="form-control form-control-lg @error('amount') is-invalid @enderror"
                           inputmode="decimal"
                           placeholder="0,00"
                           value="{{ old('amount') }}"
                           {{ ($walletActive ?? false) ? 'required autofocus' : 'disabled' }}>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">O valor será transferido para a conta principal Cota Brasilis (custódia da negociação).</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg" {{ ($walletActive ?? false) ? '' : 'disabled' }}>
                        <i class="fas fa-paper-plane me-2"></i>Continuar
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Voltar ao painel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
