@extends('layouts.app')

@section('title', 'Minhas Ofertas - Cota Brasilis')

@section('content')
@php
    use Illuminate\Support\Str;

    $user = auth()->user();
    $offers = $user->quotas()->latest()->take(8)->get();
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        @if($offers->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(4, 64, 52, 0.12);">
                    <i class="fas fa-gift fa-3x text-success"></i>
                </div>
                <h3 class="fw-bold mb-3">Você ainda não publicou uma oferta</h3>
                <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                    Publique suas cotas com fotos, documentação e condições especiais. Nosso time valida os dados e conecta você com viajantes qualificados.
                </p>
                @if($profile && data_get($profile->getProfileConfig(), 'can_publish'))
                <a href="{{ route('quotas.create') }}" class="btn btn-success btn-lg px-4">
                    <i class="fas fa-rocket me-2"></i>Começar agora
                </a>
                @endif
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-semibold mb-0">Minhas Cotas ou Frações Publicadas</h4>
                <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2">
                    <i class="fas fa-chart-line me-2"></i>{{ $offers->count() }} ofertas em destaque
                </span>
            </div>
            <div class="row g-4">
                @foreach($offers as $offer)
                    <div class="col-xl-6">
                        <div class="border rounded-4 h-100 p-4 shadow-sm bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $offer->hotel_name }}</h5>
                                    <span class="badge bg-success-subtle text-success fw-semibold">{{ Str::title($offer->location ?? 'Destino reservado') }}</span>
                                </div>
                                <a href="{{ route('quotas.edit', $offer) }}" class="btn btn-outline-success btn-sm fw-semibold">
                                    <i class="fas fa-edit me-1"></i>Editar
                                </a>
                            </div>
                            <ul class="list-unstyled text-muted small mb-4">
                                <li class="mb-2"><i class="fas fa-calendar-alt me-2 text-success"></i>{{ optional($offer->start_date)->format('d/m/Y') }} até {{ optional($offer->end_date)->format('d/m/Y') }}</li>
                                <li class="mb-2"><i class="fas fa-users me-2 text-success"></i>{{ $offer->number_of_guests }} {{ Str::plural('hóspede', $offer->number_of_guests) }}</li>
                                <li><i class="fas fa-dollar-sign me-2 text-success"></i>Valor sugerido: R$ {{ number_format($offer->rental_price ?? 0, 2, ',', '.') }}</li>
                            </ul>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('quotas.show', $offer) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Visualizar detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

