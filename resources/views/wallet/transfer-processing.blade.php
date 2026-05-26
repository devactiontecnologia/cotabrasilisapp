@extends('layouts.app')

@section('title', 'Processando transferência')

@section('content')
<div class="container py-5" style="max-width: 520px;">
    <div class="card border-0 shadow-sm rounded-4 text-center">
        <div class="card-body p-5">
            <div class="spinner-border text-success mb-4" style="width: 3.5rem; height: 3.5rem;" role="status">
                <span class="visually-hidden">Processando...</span>
            </div>
            <h1 class="h4 fw-bold mb-2">Processando transferência</h1>
            <p class="text-muted mb-0">
                Estamos enviando <strong>R$ {{ number_format($transfer->amount, 2, ',', '.') }}</strong> para a conta Cota Brasilis.
                Aguarde alguns instantes…
            </p>
        </div>
    </div>
</div>
@push('scripts')
<script>
    setTimeout(function () {
        window.location.href = @json(route('wallet.transfer.processing', $transfer));
    }, 2000);
</script>
@endpush
@endsection
