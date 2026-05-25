@extends('layouts.app')

@section('title', 'Pagamento - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <h4 class="fw-semibold mb-4">Processar Pagamento</h4>
        <p class="text-muted">Funcionalidade em desenvolvimento. Em breve você poderá processar pagamentos aqui.</p>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>
@endsection

