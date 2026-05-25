@extends('layouts.app')

@section('title', 'Pagamento')

@section('content')
@php
    use Carbon\Carbon;
    $deadline = $transaction->negotiation_deadline;
    $hoursRemaining = $deadline ? max(0, now()->diffInHours($deadline, false)) : 0;
    $minutesRemaining = $deadline ? max(0, now()->diffInMinutes($deadline, false) % 60) : 0;
@endphp

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Timer de Urgência -->
            @if($hoursRemaining > 0)
            <div class="alert alert-warning mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 fw-bold">
                            <i class="fas fa-clock me-2"></i>Tempo Restante para Pagamento
                        </h6>
                        <p class="mb-0">
                            <span id="countdown" class="fw-bold fs-4">
                                {{ str_pad($hoursRemaining, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutesRemaining, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Prazo final:</small>
                        <strong>{{ $deadline->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                O prazo para pagamento expirou. Esta negociação será cancelada automaticamente.
            </div>
            @endif

            <div class="card shadow-lg border-0 rounded-4 mb-4">
                <div class="card-header bg-success text-white py-4 rounded-top-4">
                    <h3 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Realizar Pagamento
                    </h3>
                </div>
                <div class="card-body p-4">
                    <!-- Resumo da Transação -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Resumo da Transação</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Hotel</p>
                                <p class="fw-semibold">{{ $transaction->quota->hotel_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Localização</p>
                                <p class="fw-semibold">{{ $transaction->quota->location }}</p>
                            </div>
                            @foreach($transaction->quota->getPeriodDisplayLines() as $periodLine)
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">{{ trim($periodLine['label']) }}</p>
                                <p class="fw-semibold">{{ $periodLine['formatted'] }}</p>
                            </div>
                            @endforeach
                            @if($transaction->quota->getPeriodDisplayLines() === [])
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Período</p>
                                <p class="fw-semibold">
                                    {{ $transaction->quota->start_date ? \Carbon\Carbon::parse($transaction->quota->start_date)->format('d/m/Y') : '-' }} a
                                    {{ $transaction->quota->end_date ? \Carbon\Carbon::parse($transaction->quota->end_date)->format('d/m/Y') : '-' }}
                                </p>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Proprietário</p>
                                <p class="fw-semibold">{{ $transaction->owner->name }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Valores -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Valores</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Valor da Cota:</span>
                            <strong>R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Taxa da Plataforma (5%):</span>
                            <strong>R$ {{ number_format($transaction->platform_fee, 2, ',', '.') }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fs-5 fw-bold">Total:</span>
                            <span class="fs-4 fw-bold text-success">R$ {{ number_format($transaction->total_amount + $transaction->platform_fee, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Formulário de Pagamento -->
                    @if($hoursRemaining > 0)
                    <form method="POST" action="{{ route('transactions.payment.process', $transaction) }}" id="paymentForm">
                        @csrf
                        
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Método de Pagamento</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pix" value="pix" checked>
                                        <label class="form-check-label w-100 p-3 border rounded" for="pix">
                                            <i class="fab fa-pix fa-2x text-primary mb-2 d-block"></i>
                                            <strong>PIX</strong>
                                            <small class="d-block text-muted">Aprovação imediata</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card">
                                        <label class="form-check-label w-100 p-3 border rounded" for="credit_card">
                                            <i class="fas fa-credit-card fa-2x text-primary mb-2 d-block"></i>
                                            <strong>Cartão de Crédito</strong>
                                            <small class="d-block text-muted">Parcelamento disponível</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="debit_card" value="debit_card">
                                        <label class="form-check-label w-100 p-3 border rounded" for="debit_card">
                                            <i class="fas fa-credit-card fa-2x text-primary mb-2 d-block"></i>
                                            <strong>Cartão de Débito</strong>
                                            <small class="d-block text-muted">Débito automático</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check payment-method-card">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                        <label class="form-check-label w-100 p-3 border rounded" for="bank_transfer">
                                            <i class="fas fa-university fa-2x text-primary mb-2 d-block"></i>
                                            <strong>Transferência Bancária</strong>
                                            <small class="d-block text-muted">Até 2 dias úteis</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Importante:</strong> Após o pagamento, o proprietário terá {{ $transaction->document_deadline_hours ?? 24 }} horas para enviar o documento necessário.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Pagar taxa de êxito
                            </button>
                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </a>
                        </div>
                    </form>
                    @else
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        O prazo para pagamento expirou. Esta negociação será cancelada automaticamente.
                    </div>
                    <a href="{{ route('quotas.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar para Cotas
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .payment-method-card {
        cursor: pointer;
    }
    .payment-method-card input[type="radio"] {
        display: none;
    }
    .payment-method-card input[type="radio"]:checked + label {
        border-color: #009739 !important;
        background-color: #f0f9f4;
    }
    .payment-method-card label {
        cursor: pointer;
        transition: all 0.3s;
    }
    .payment-method-card label:hover {
        border-color: #009739;
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('scripts')
@if($hoursRemaining > 0)
<script>
    // Countdown timer
    let deadline = new Date('{{ $deadline->toIso8601String() }}');
    
    function updateCountdown() {
        let now = new Date();
        let diff = deadline - now;
        
        if (diff <= 0) {
            document.getElementById('countdown').textContent = '00:00';
            location.reload();
            return;
        }
        
        let hours = Math.floor(diff / (1000 * 60 * 60));
        let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById('countdown').textContent = 
            String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }
    
    setInterval(updateCountdown, 60000); // Atualizar a cada minuto
    updateCountdown();
</script>
@endif
@endsection
