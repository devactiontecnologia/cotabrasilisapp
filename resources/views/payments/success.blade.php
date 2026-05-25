@extends('layouts.app')

@section('title', 'Pagamento Realizado - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5 text-center">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
            <i class="fas fa-check-circle fa-3x text-success"></i>
        </div>
        <h3 class="fw-bold mb-3">Pagamento Realizado com Sucesso!</h3>
        <p class="text-muted mb-4">Sua transação foi processada com sucesso.</p>
        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-success">
            <i class="fas fa-file-alt me-2"></i>Ver Detalhes da Transação
        </a>
    </div>
</div>
@endsection

