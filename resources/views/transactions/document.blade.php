@extends('layouts.app')

@section('title', 'Enviar Documento')

@section('content')
@php
    use Carbon\Carbon;
    $deadline = $transaction->document_upload_deadline;
    $hoursRemaining = $deadline ? max(0, now()->diffInHours($deadline, false)) : 0;
    $minutesRemaining = $deadline ? max(0, now()->diffInMinutes($deadline, false) % 60) : 0;
    $isOnTime = $hoursRemaining > 0;
@endphp

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Timer de Urgência -->
            @if($isOnTime)
            <div class="alert alert-warning mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 fw-bold">
                            <i class="fas fa-clock me-2"></i>Tempo Restante para Envio
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
                O prazo para envio do documento expirou. Por favor, envie o documento o quanto antes.
            </div>
            @endif

            <div class="card shadow-lg border-0 rounded-4 mb-4">
                <div class="card-header bg-success text-white py-4 rounded-top-4">
                    <h3 class="mb-0">
                        <i class="fas fa-file-upload me-2"></i>Enviar Documento
                    </h3>
                </div>
                <div class="card-body p-4">
                    <!-- Informações da Transação -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Informações da Transação</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Hotel</p>
                                <p class="fw-semibold">{{ $transaction->quota->hotel_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Localização</p>
                                <p class="fw-semibold">{{ $transaction->quota->location }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Período</p>
                                <p class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($transaction->quota->start_date)->format('d/m/Y') }} a 
                                    {{ \Carbon\Carbon::parse($transaction->quota->end_date)->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted">Cliente</p>
                                <p class="fw-semibold">{{ $transaction->renter->name }}</p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <p class="mb-1 text-muted">Valor Pago</p>
                                <h5 class="text-success fw-bold">R$ {{ number_format($transaction->total_amount, 2, ',', '.') }}</h5>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Instruções -->
                    <div class="alert alert-info mb-4">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-info-circle me-2"></i>Instruções
                        </h6>
                        <ul class="mb-0">
                            <li>Envie o documento de autorização de hospedagem</li>
                            <li>O documento deve estar legível e completo</li>
                            <li>Formatos aceitos: PDF, JPG, JPEG ou PNG</li>
                            <li>Tamanho máximo: 5MB</li>
                            <li>O documento deve ser enviado em até <strong>{{ $transaction->document_deadline_hours ?? 24 }} horas</strong> após o pagamento</li>
                        </ul>
                    </div>

                    <!-- Formulário de Upload -->
                    <form method="POST" action="{{ route('transactions.document.upload', $transaction) }}" enctype="multipart/form-data" id="documentForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="document" class="form-label fw-semibold">
                                Documento de Autorização <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('document') is-invalid @enderror" 
                                   id="document" 
                                   name="document" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formatos aceitos: PDF, JPG, JPEG, PNG (máx. 5MB)</small>
                        </div>

                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2">Pré-visualização</h6>
                                    <div id="preview" class="text-center">
                                        <p class="text-muted mb-0">Nenhum arquivo selecionado</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-upload me-2"></i>
                                Enviar Documento
                            </button>
                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($isOnTime)
<script>
    // Countdown timer
    let deadline = new Date('{{ $deadline->toIso8601String() }}');
    
    function updateCountdown() {
        let now = new Date();
        let diff = deadline - now;
        
        if (diff <= 0) {
            document.getElementById('countdown').textContent = '00:00';
            return;
        }
        
        let hours = Math.floor(diff / (1000 * 60 * 60));
        let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById('countdown').textContent = 
            String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }
    
    setInterval(updateCountdown, 60000);
    updateCountdown();
</script>
@endif

<script>
    // Preview do arquivo
    document.getElementById('document').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('preview');
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 300px;" alt="Preview">`;
                } else {
                    preview.innerHTML = `
                        <div class="p-3">
                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                            <p class="mb-0"><strong>${file.name}</strong></p>
                            <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                        </div>
                    `;
                }
            };
            
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<p class="text-muted mb-0">Nenhum arquivo selecionado</p>';
        }
    });
</script>
@endsection
