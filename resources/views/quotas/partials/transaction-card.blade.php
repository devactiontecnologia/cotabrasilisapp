<div class="col-lg-6 col-xl-4 mb-4">
    <div class="card h-100 shadow-sm border-0 rounded-4">
        <div class="card-header quota-card__header text-white border-0" style="background: linear-gradient(135deg, #0a8f3f 0%, #046143 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-hotel me-2 text-white"></i>{{ $transaction->quota->hotel_name }}
                </h5>
                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>
        </div>
        
        <div class="card-body">
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                    <span class="fw-medium">{{ $transaction->quota->location }}</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-calendar-alt text-muted me-2"></i>
                    <span>{{ \Carbon\Carbon::parse($transaction->quota->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($transaction->quota->end_date)->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-users text-muted me-2"></i>
                    <span>{{ $transaction->quota->number_of_guests }} {{ $transaction->quota->number_of_guests == 1 ? 'hóspede' : 'hóspedes' }}</span>
                </div>
                @if($transaction->transaction_type === 'rental' && $transaction->total_amount)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-dollar-sign text-muted me-2"></i>
                        <span class="fw-bold text-success">R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-user me-2 text-muted"></i>
                    <span class="small">Proprietário: {{ $transaction->owner->name }}</span>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Criada em {{ $transaction->created_at->format('d/m/Y') }}
                </small>
                <span class="badge bg-{{ $transaction->transaction_type === 'rental' ? 'success' : 'warning' }}">
                    <i class="fas fa-{{ $transaction->transaction_type === 'rental' ? 'dollar-sign' : 'exchange-alt' }} me-1"></i>
                    {{ $transaction->transaction_type === 'rental' ? 'Aluguel' : 'Troca' }}
                </span>
            </div>
        </div>

        <div class="card-footer bg-light border-0">
            <div class="d-flex gap-2">
                <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-eye me-1"></i>Ver detalhes
                </a>
            </div>
        </div>
    </div>
</div>









