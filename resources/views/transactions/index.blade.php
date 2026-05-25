@extends('layouts.app')

@section('title', 'Minhas Transações')

@section('content')
@php
    use Illuminate\Support\Str;

    $totalTransactions = $transactions->count();
    $completedCount = $transactions->where('status', 'completed')->count();
    $pendingCount = $transactions->where('status', 'pending')->count();
    $completedAmount = $transactions->where('status', 'completed')->sum('total_amount');
    $exchangeCount = $transactions->where('transaction_type', 'exchange')->count();
    $statusLabels = [
        'pending' => 'Pendente',
        'completed' => 'Concluída',
        'cancelled' => 'Cancelada'
    ];
    $paymentLabels = [
        'pending' => 'Pagamento pendente',
        'completed' => 'Pagamento concluído'
    ];
@endphp

<div class="container py-5">
    <section class="mb-5">
        <div class="p-4 p-lg-5 rounded-4 text-white" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.92), rgba(79, 70, 229, 0.9)); box-shadow: 0 30px 70px rgba(79, 70, 229, 0.25); position: relative; overflow: hidden;">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 text-lg-start text-center">
                    <span class="badge bg-white text-primary fw-semibold mb-3 px-3 py-2">
                        <i class="fas fa-sync-alt me-2"></i>Central de transações
                    </span>
                    <h1 class="display-5 fw-bold mb-2">
                        Minhas transações
                    </h1>
                    <p class="lead text-white-75 mb-0" style="max-width: 580px;">
                        Acompanhe contratos, pagamentos e progresso das negociações de aluguel ou troca. Transparência total para proprietários e locatários.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end text-center">
                    <a href="{{ route('quotas.index') }}" class="btn btn-light text-primary fw-semibold px-4 py-2 rounded-3">
                        <i class="fas fa-search me-2"></i>Buscar novas cotas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-3 g-lg-4">
            <div class="col-md-3 col-sm-6">
                <div class="rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); box-shadow: 0 16px 40px rgba(37, 99, 235, 0.2);">
                    <i class="fas fa-list fs-4 mb-3"></i>
                    <p class="text-uppercase small mb-1 text-white-75 fw-semibold">Total de transações</p>
                    <h3 class="fw-bold mb-0">{{ $totalTransactions }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 16px 40px rgba(5, 150, 105, 0.2);">
                    <i class="fas fa-check-circle fs-4 mb-3"></i>
                    <p class="text-uppercase small mb-1 text-white-75 fw-semibold">Concluídas</p>
                    <h3 class="fw-bold mb-0">{{ $completedCount }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 16px 40px rgba(245, 158, 11, 0.2);">
                    <i class="fas fa-hourglass-half fs-4 mb-3"></i>
                    <p class="text-uppercase small mb-1 text-white-75 fw-semibold">Pendentes</p>
                    <h3 class="fw-bold mb-0">{{ $pendingCount }}</h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 16px 40px rgba(14, 165, 233, 0.2);">
                    <i class="fas fa-dollar-sign fs-4 mb-3"></i>
                    <p class="text-uppercase small mb-1 text-white-75 fw-semibold">Valor concluído</p>
                    <h3 class="fw-bold mb-1">R$ {{ number_format($completedAmount, 2, ',', '.') }}</h3>
                    <span class="badge bg-white text-primary fw-semibold">{{ $exchangeCount }} trocas registradas</span>
                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-history me-2"></i>Histórico de Transações
                </h4>
                <a href="{{ route('quotas.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-search me-2"></i>Buscar Cotas
                </a>
            </div>

            @if($transactions->count() > 0)
                <div class="row row-cols-1 row-cols-lg-2 g-4">
                    @foreach($transactions as $transaction)
                        <div class="col">
                            <div class="card h-100 border-0 rounded-4 shadow-sm transaction-card" style="overflow: hidden;">
                                <div class="px-4 pt-4 pb-3" style="background: linear-gradient(135deg, #047857, #065f46); color: #fff;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="fw-bold mb-1"><i class="fas fa-hotel me-2"></i>{{ $transaction->quota->hotel_name }}</h5>
                                                <span class="text-white-50 small"><i class="fas fa-map-marker-alt me-1"></i>{{ $transaction->quota->location }}</span>
                                            </div>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }} text-uppercase fw-semibold">
                                                {{ $statusLabels[$transaction->status] ?? 'Em análise' }}
                                            </span>
                                        </div>
                                </div>

                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <div class="d-flex align-items-center gap-2 text-muted">
                                            <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                            <div>
                                                <small class="text-uppercase fw-semibold">Período</small>
                                                <p class="mb-0 fw-semibold">{{ optional($transaction->quota->start_date)->format('d/m/Y') }} · {{ optional($transaction->quota->end_date)->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 text-muted">
                                            <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="fas fa-user-friends"></i>
                                            </span>
                                            <div>
                                                <small class="text-uppercase fw-semibold">Hóspedes</small>
                                                <p class="mb-0 fw-semibold">{{ $transaction->quota->number_of_guests }} {{ Str::plural('pessoa', $transaction->quota->number_of_guests) }}</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 text-muted">
                                            <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="fas fa-tag"></i>
                                            </span>
                                            <div>
                                                <small class="text-uppercase fw-semibold">Operação</small>
                                            <span class="badge bg-{{ $transaction->transaction_type === 'rental' ? 'success' : 'warning' }} fw-semibold">
                                                <i class="fas fa-{{ $transaction->transaction_type === 'rental' ? 'dollar-sign' : 'exchange-alt' }} me-1"></i>{{ $transaction->transaction_type === 'rental' ? 'Aluguel' : 'Troca' }}
                                                </span>
                                            </div>
                                        </div>
                                        @if($transaction->transaction_type === 'rental')
                                            <div class="d-flex align-items-center gap-2 text-muted">
                                                <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </span>
                                                <div>
                                                    <small class="text-uppercase fw-semibold">Valor total</small>
                                                    <p class="mb-0 fw-bold text-success">R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-sm-6">
                                            <small class="text-muted text-uppercase fw-semibold d-block">Você é</small>
                                            <p class="mb-0 fw-semibold text-primary">{{ $transaction->owner_id == auth()->id() ? 'Proprietário da cota' : ($transaction->transaction_type === 'rental' ? 'Locatário' : 'Participante') }}</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted text-uppercase fw-semibold d-block">Outra parte</small>
                                            <p class="mb-0 fw-semibold">{{ $transaction->owner_id == auth()->id() ? $transaction->renter->name : $transaction->owner->name }}</p>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-plus me-1"></i>{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}
                                        </small>
                                        @if($transaction->payment_status)
                                            <span class="badge bg-{{ $transaction->payment_status === 'completed' ? 'success' : 'warning' }} fw-semibold">
                                                <i class="fas fa-credit-card me-1"></i>{{ $paymentLabels[$transaction->payment_status] ?? 'Status do pagamento' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-success-subtle bg-opacity-10 border-0 p-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-success flex-fill">
                                            <i class="fas fa-eye me-2"></i>Ver detalhes
                                        </a>
                                        @if($transaction->status === 'pending' && $transaction->transaction_type === 'rental' && $transaction->renter_id == auth()->id() && $transaction->payment_status == 'pending')
                                            <form method="POST" action="{{ route('transactions.payment', $transaction) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success">
                                                    <i class="fas fa-credit-card me-1"></i>Pagar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="mt-4">
                        {{ $transactions->links('vendor.pagination.modern') }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: rgba(37, 99, 235, 0.12);">
                        <i class="fas fa-exchange-alt text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-semibold mb-3">Nenhuma transação registrada</h4>
                    <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                        Quando você realizar aluguéis ou trocas, o histórico completo aparecerá aqui. Explore oportunidades ou publique sua cota para iniciar uma negociação.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('quotas.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Explorar cotas
                        </a>
                        <a href="{{ route('quotas.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Publicar minha cota
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection