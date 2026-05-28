@extends('layouts.app')

@section('title', 'Detalhes da Troca')

@section('content')
<style>
    .exchange-tx-hero h1,
    .exchange-tx-hero h1 i {
        color: #ffffff !important;
    }
</style>
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $isRental = $transaction->transaction_type === 'rental';
    $startDate = $transaction->quota->start_date ? Carbon::parse($transaction->quota->start_date) : null;
    $endDate = $transaction->quota->end_date ? Carbon::parse($transaction->quota->end_date) : null;
    $durationDays = ($startDate && $endDate) ? $startDate->diffInDays($endDate) + 1 : null;
    $statusColors = [
        'pending' => 'warning',
        'negotiating' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'expired' => 'secondary',
    ];
    $statusIcons = [
        'pending' => 'clock',
        'negotiating' => 'hourglass-half',
        'completed' => 'check',
        'cancelled' => 'times',
        'expired' => 'ban',
    ];
    $statusLabels = [
        'pending' => 'Pendente',
        'negotiating' => 'Em negociação',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado',
        'expired' => 'Expirado',
        'payment_pending' => 'Pagamento pendente',
        'document_pending' => 'Documento pendente',
    ];
    $paymentStatusLabels = [
        'pending' => 'Pendente',
        'completed' => 'Concluído',
        'failed' => 'Falhou',
    ];
    $transactionStatusLabel = $statusLabels[$transaction->status] ?? ucfirst($transaction->status);
    $paymentMethods = [
        'credit_card' => 'Cartão de Crédito',
        'debit_card' => 'Cartão de Débito',
        'pix' => 'Pix',
        'bank_transfer' => 'Transferência bancária',
        'exchange' => 'Troca de cotas'
    ];
@endphp

<div class="container py-5">
    <section class="mb-4">
        <div class="p-4 p-lg-5 rounded-4 text-white exchange-tx-hero" style="background: linear-gradient(135deg, rgba(0, 151, 57, 0.93), rgba(4, 64, 52, 0.9)); box-shadow: 0 30px 60px rgba(5, 74, 40, 0.28); position: relative; overflow: hidden;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-light text-success fw-semibold mb-3 px-3 py-2">
                        <i class="fas fa-file-invoice me-2"></i>Transação #{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}
                    </span>
                    <h1 class="display-6 fw-bold mb-2 text-white">
                        <i class="fas fa-hotel me-2"></i>{{ $transaction->quota->hotel_name }}
                    </h1>
                    <div class="d-flex flex-wrap gap-3 text-white-50">
                        <span><i class="fas fa-map-marker-alt me-1"></i>{{ $transaction->quota->location }}</span>
                        @foreach($transaction->quota->getPeriodDisplayLines() as $periodLine)
                        <span><i class="fas fa-calendar-alt me-1"></i>{{ trim($periodLine['label']) }} {{ $periodLine['formatted'] }}</span>
                        @endforeach
                        @if($transaction->quota->getPeriodDisplayLines() === [])
                        <span><i class="fas fa-calendar-alt me-1"></i>{{ $startDate?->format('d/m/Y') }} a {{ $endDate?->format('d/m/Y') }}</span>
                        @endif
                        <span><i class="fas fa-user-friends me-1"></i>{{ $transaction->quota->number_of_guests }} {{ Str::plural('hóspede', $transaction->quota->number_of_guests) }}</span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end text-center mt-4 mt-lg-0">
                    <span class="badge bg-{{ $statusColors[$transaction->status] ?? 'secondary' }} fs-6 px-4 py-2">
                        <i class="fas fa-{{ $statusIcons[$transaction->status] ?? 'info-circle' }} me-1"></i>{{ $transactionStatusLabel }}
                    </span>
                    <div class="mt-3 text-white-50">
                        <i class="fas fa-clock me-1"></i> Criada em {{ $transaction->created_at->format('d/m/Y \\à\\s H:i') }}<br>
                        <i class="fas fa-redo me-1"></i> Atualizada em {{ $transaction->updated_at->format('d/m/Y \\à\\s H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4"><i class="fas fa-layer-group text-success me-2"></i>Resumo da operação</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-sync-alt"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Tipo</small>
                                    <p class="mb-0 fw-semibold">{{ $isRental ? 'Aluguel de cota' : 'Troca de cotas' }}</p>
                                    <span class="text-muted small">{{ $isRental ? 'Processo com pagamento e contrato digital' : 'Negociação baseada em troca de titularidade' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-sun"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Duração</small>
                                    <p class="mb-0 fw-semibold">{{ $durationDays ? $durationDays . ' ' . Str::plural('dia', $durationDays) : 'Período não informado' }}</p>
                                    @if($transaction->quota->seasonality)
                                        <span class="badge bg-success-subtle text-success fw-semibold mt-1">{{ $transaction->quota->getSeasonalityLabel() }} temporada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-bed"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Capacidade</small>
                                    <p class="mb-0 fw-semibold">{{ $transaction->quota->number_of_guests }} {{ Str::plural('pessoa', $transaction->quota->number_of_guests) }}</p>
                                    <span class="text-muted small">Quartos: {{ $transaction->quota->number_of_rooms ?? 'Não informado' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-success-subtle text-success rounded-3 p-3"><i class="fas fa-tag"></i></span>
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Status da cota</small>
                                    <p class="mb-0 fw-semibold">{{ $transaction->quota->getQuotaStatusLabel() }}</p>
                                    <span class="text-muted small">Pagamento: {{ $transaction->quota->getPaymentStatusLabel() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($transaction->exchangeQuota)
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><i class="fas fa-exchange-alt text-warning me-2"></i>Cotas na troca</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Cota desejada</p>
                                <p class="fw-semibold mb-0">{{ $transaction->quota->hotel_name }}</p>
                                <span class="text-muted small">{{ $transaction->quota->location }}</span>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Cota oferecida pelo interessado</p>
                                <p class="fw-semibold mb-0">{{ $transaction->exchangeQuota->hotel_name }}</p>
                                <span class="text-muted small">{{ $transaction->exchangeQuota->location }}</span>
                            </div>
                        </div>
                        <p class="small text-muted mb-0 mt-3"><i class="fas fa-info-circle me-1"></i>Troca de periodo — sem valores monetários entre as partes.</p>
                    </div>
                </div>
            @endif

            @if($isRental)
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-4"><i class="fas fa-dollar-sign text-success me-2"></i>Fluxo financeiro</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="rounded-4 p-4 text-center" style="background: rgba(0, 151, 57, 0.08);">
                                    <i class="fas fa-wallet text-success fs-3 mb-2"></i>
                                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Valor total</p>
                                    <p class="fs-4 fw-bold text-success mb-0">R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="rounded-4 p-4 text-center" style="background: rgba(4, 64, 52, 0.08);">
                                    <i class="fas fa-user-tie text-success fs-3 mb-2"></i>
                                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Repasse ao proprietário</p>
                                    <p class="fs-5 fw-bold text-success mb-0">R$ {{ number_format($transaction->owner_amount, 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="rounded-4 p-4 text-center" style="background: rgba(250, 204, 21, 0.12);">
                                    <i class="fas fa-percentage text-warning fs-3 mb-2"></i>
                                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Taxa plataforma</p>
                                    <p class="fs-5 fw-bold text-warning mb-0">R$ {{ number_format($transaction->platform_fee, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>Valores considerados na data da transação. Conferir recibo para detalhes fiscais.
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4"><i class="fas fa-file-contract text-success me-2"></i>Contrato digital</h5>
                    @if($transaction->digitalContract)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="fw-semibold mb-1">{{ $isRental ? 'Contrato de aluguel' : 'Contrato de troca' }}</p>
                                <span class="badge bg-{{ $transaction->digitalContract->is_signed ? 'success' : 'warning' }} fw-semibold">
                                    <i class="fas fa-{{ $transaction->digitalContract->is_signed ? 'check' : 'clock' }} me-1"></i>
                                    {{ $transaction->digitalContract->is_signed ? 'Assinado por ambas as partes' : 'Assinatura pendente' }}
                                </span>
                            </div>
                            <button class="btn btn-outline-success" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Emitir comprovante
                            </button>
                        </div>

                        @if(!$transaction->digitalContract->is_signed)
                            <div class="bg-success-subtle bg-opacity-10 rounded-4 p-3">
                                <p class="text-muted small mb-2"><i class="fas fa-pen-nib me-1"></i>Digite sua assinatura para concluir o contrato:</p>
                                <form method="POST" action="{{ route('transactions.sign', $transaction) }}" class="input-group">
                                    @csrf
                                    <input type="text" class="form-control" name="signature" placeholder="Ex: Maria Oliveira Costa" minlength="10" required>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-signature me-2"></i>Assinar
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>Contrato finalizado em {{ $transaction->digitalContract->updated_at->format('d/m/Y \\à\\s H:i') }}. Acesse a versão completa nos documentos da plataforma.
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Nenhum contrato digital gerado para esta transação.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="fas fa-user-shield text-success me-2"></i>Partes envolvidas</h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-crown"></i>
                            </span>
                            <div>
                                <small class="text-muted text-uppercase fw-semibold">Proprietário</small>
                                <p class="mb-0 fw-semibold">{{ $transaction->owner->name }}</p>
                                <span class="text-muted small">{{ $transaction->owner->email }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-user"></i>
                            </span>
                            <div>
                                <small class="text-muted text-uppercase fw-semibold">{{ $isRental ? 'Locatário' : 'Interessado' }}</small>
                                <p class="mb-0 fw-semibold">{{ $transaction->renter->name }}</p>
                                <span class="text-muted small">{{ $transaction->renter->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-4 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Você está visualizando esta transação como <strong>{{ $transaction->owner_id == auth()->id() ? 'proprietário' : ($isRental ? 'locatário' : 'participante da troca') }}</strong>.
                    </div>
                </div>
            </div>

            @if($isRental)
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3"><i class="fas fa-credit-card text-success me-2"></i>Status do pagamento</h5>
                        <p class="mb-1"><strong>Método:</strong> {{ $paymentMethods[$transaction->payment_method] ?? Str::title(str_replace('_', ' ', $transaction->payment_method)) }}</p>
                        <p class="mb-3"><strong>Status:</strong> <span class="badge bg-{{ $transaction->payment_status === 'completed' ? 'success' : 'warning' }}">{{ $paymentStatusLabels[$transaction->payment_status] ?? ucfirst($transaction->payment_status) }}</span></p>

                        @if($transaction->status == 'pending')
                            @if($transaction->renter_id == auth()->id() && $transaction->payment_status == 'pending')
                                <form method="POST" action="{{ route('transactions.payment', $transaction) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 mb-3">
                                        <i class="fas fa-credit-card me-2"></i>Processar pagamento
                                    </button>
                                </form>
                                <p class="text-muted small mb-0 text-center">
                                    <i class="fas fa-shield-alt me-1"></i>Ao concluir o pagamento, você terá acesso imediato ao contrato assinado.
                                </p>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-hourglass-half me-2"></i>Aguardando a confirmação do pagamento pela outra parte.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check me-2"></i>Pagamento validado e transação concluída.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-2"></i>Voltar para minhas transações
            </a>
        </div>
    </div>
</div>

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection

@section('scripts')
@endsection