@extends('layouts.app')
@section('title', 'Enviar comprovante')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-success text-white py-3 rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Anexar comprovante de pagamento</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-4">O pagamento da taxa de êxito foi processado. Envie o comprovante para o proprietário da cota finalizar o processo. <strong>É obrigatório.</strong></p>
                    @if($transaction->payment_receipt_path)
                        <div class="alert alert-success">
                            <i class="fas fa-check me-2"></i>Comprovante já enviado. Processo em conclusão.
                        </div>
                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">Ver transação</a>
                    @else
                    <form method="POST" action="{{ route('transactions.receipt.upload', $transaction) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label fw-semibold">Comprovante de pagamento (PDF ou imagem)</label>
                        <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('receipt')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <button type="submit" class="btn btn-success mt-3"><i class="fas fa-upload me-1"></i>Enviar comprovante</button>
                    </form>
                    @endif
                    <hr>
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
