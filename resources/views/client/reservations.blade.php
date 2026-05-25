@extends('layouts.app')

@section('title', 'Minhas Reservas - Cota Brasilis')

@section('content')
@php
    $user = auth()->user();
    $reservations = $user->rentalTransactions()->latest()->take(6)->get();
    if ($reservations->isNotEmpty()) {
        $reservations->load('quota');
    }
@endphp

<section class="mb-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            @if($reservations->isEmpty())
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                        <i class="fas fa-calendar-day fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Você ainda não possui reservas ativas</h3>
                    <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                        Explore cotas disponíveis para aluguel e confirme sua próxima experiência com poucos cliques. Tudo com contratos digitais e garantia Cota Brasilis.
                    </p>
                    <a href="{{ route('quotas.index') }}" class="btn btn-success btn-lg px-4">
                        <i class="fas fa-compass me-2"></i>Explorar cotas disponíveis
                    </a>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-semibold mb-0">Histórico de Reservas</h4>
                    <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2">
                        <i class="fas fa-clock me-2"></i>Atualizado em {{ now()->format('d/m/Y H:i') }}
                    </span>
                </div>
                <div class="row g-4">
                    @foreach($reservations as $reservation)
                        <div class="col-md-6">
                            <div class="border rounded-4 h-100 p-4 shadow-sm bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">{{ $reservation->quota->hotel_name ?? 'Cota Reservada' }}</h5>
                                    <span class="badge bg-success text-uppercase">Confirmada</span>
                                </div>
                                <ul class="list-unstyled mb-4 text-muted small">
                                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-success"></i>{{ $reservation->quota->location ?? 'Destino reservado' }}</li>
                                    <li class="mb-2"><i class="fas fa-calendar-check me-2 text-success"></i>{{ optional($reservation->start_date)->format('d/m/Y') }} até {{ optional($reservation->end_date)->format('d/m/Y') }}</li>
                                    <li><i class="fas fa-users me-2 text-success"></i>{{ $reservation->quota->number_of_guests ?? 'Sob consulta' }} hóspedes</li>
                                </ul>
                                <a href="{{ route('transactions.show', $reservation) }}" class="btn btn-outline-success btn-sm fw-semibold">
                                    <i class="fas fa-file-alt me-2"></i>Ver detalhes completos
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
