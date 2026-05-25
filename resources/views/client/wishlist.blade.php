@extends('layouts.app')

@section('title', 'Desejados - Cota Brasilis')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp
<section class="mb-5">
    <div class="p-5 p-lg-6 rounded-4 text-white" style="background: linear-gradient(135deg, rgba(147, 51, 234, 0.9), rgba(91, 33, 182, 0.85)); box-shadow: 0 26px 60px rgba(109, 40, 217, 0.36);">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
            <div>
                <span class="badge bg-light text-purple fw-semibold mb-3 px-3 py-2" style="color: #6d28d9 !important;">
                    <i class="fas fa-star me-2"></i>Desejados
                </span>
                <h1 class="display-6 fw-bold mb-3">Seu desejo, nossa prioridade</h1>
                <p class="lead mb-0" style="max-width: 620px;">
                    Quando não encontrar o que procura, salve sua busca.<br>
                    Você será avisado por e-mail e WhatsApp assim que ela estiver disponível.
                </p>
            </div>
            <a href="{{ route('quotas.index') }}" class="btn btn-light text-purple fw-semibold px-4 py-3 rounded-3" style="color: #6d28d9 !important;">
                <i class="fas fa-search me-2"></i>Nova busca
            </a>
        </div>
    </div>
</section>

<div class="container">
    @if($wishlistSearches->isEmpty() && $wishlistQuotas->isEmpty())
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(147, 51, 234, 0.15);">
                        <i class="fas fa-star fa-3x" style="color: #6d28d9;"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Nada salvo nos Desejados ainda</h3>
                    <p class="text-muted mb-4" style="max-width: 540px; margin: 0 auto;">
                        Use a <strong>estrela</strong> nos resultados da busca para marcar uma cota como desejada, ou salve uma <strong>busca inteira</strong> quando não houver resultados.
                        Você será avisado por e-mail e WhatsApp quando houver novidades.
                    </p>
                    <a href="{{ route('quotas.index') }}" class="btn btn-primary btn-lg px-4" style="background: #6d28d9; border-color: #6d28d9;">
                        <i class="fas fa-search me-2"></i>Fazer uma busca
                    </a>
                </div>
            </div>
        </div>
    @else
        @if($wishlistQuotas->isNotEmpty())
            <h2 class="h5 fw-bold mb-3">
                <i class="fas fa-star me-2" style="color: #6d28d9;"></i>Cotas marcadas como desejadas
            </h2>
            <div class="row g-4 mb-5">
                @foreach($wishlistQuotas as $quota)
                    <div class="col-lg-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $quota->hotel_name }}</h6>
                                        <span class="badge bg-success-subtle text-success">{{ Str::title($quota->location ?? '—') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('client.wishlist.toggle', $quota) }}" class="flex-shrink-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning text-white" title="Remover dos desejados">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                </div>
                                <ul class="list-unstyled small text-muted mb-3">
                                    <li class="mb-1">
                                        <i class="fas fa-calendar-alt me-1" style="color: #6d28d9;"></i>
                                        {{ $quota->start_date ? $quota->start_date->format('d/m/Y') : '—' }}
                                        a {{ $quota->end_date ? $quota->end_date->format('d/m/Y') : '—' }}
                                    </li>
                                    <li>
                                        <i class="fas fa-users me-1" style="color: #6d28d9;"></i>
                                        {{ $quota->number_of_guests }} {{ Str::plural('hóspede', (int) $quota->number_of_guests) }}
                                    </li>
                                </ul>
                                @if($quota->status !== \App\Models\Quota::STATUS_AVAILABLE)
                                    <p class="small text-warning mb-2 mb-0"><i class="fas fa-info-circle me-1"></i>Esta oferta pode não estar mais disponível.</p>
                                @endif
                                <a href="{{ route('quotas.show', $quota) }}" class="btn btn-outline-primary btn-sm w-100" style="border-color: #6d28d9; color: #6d28d9;">
                                    <i class="fas fa-eye me-2"></i>Ver detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($wishlistSearches->isNotEmpty())
            <h2 class="h5 fw-bold mb-3">
                <i class="fas fa-search me-2" style="color: #6d28d9;"></i>Buscas salvas (sem resultado na época)
            </h2>
            <div class="row g-4">
                @foreach($wishlistSearches as $search)
                    <div class="col-lg-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-header bg-light py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-search me-2" style="color: #6d28d9;"></i>Busca #{{ $search->id }}
                                    </h6>
                                    @if($search->notified)
                                        <span class="badge bg-success">
                                            <i class="fas fa-bell me-1"></i>Avisado
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Aguardando
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-3">
                                    @if($search->hotel_name)
                                        <li class="mb-2"><i class="fas fa-hotel me-2" style="color: #6d28d9;"></i><strong>Hotel:</strong> {{ $search->hotel_name }}</li>
                                    @endif
                                    @if($search->city)
                                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2" style="color: #6d28d9;"></i><strong>Cidade:</strong> {{ $search->city }}</li>
                                    @endif
                                    @if($search->state)
                                        <li class="mb-2"><i class="fas fa-map me-2" style="color: #6d28d9;"></i><strong>Estado:</strong> {{ $search->state }}</li>
                                    @endif
                                    @if($search->start_date && $search->end_date)
                                        <li class="mb-2"><i class="fas fa-calendar-alt me-2" style="color: #6d28d9;"></i><strong>Período:</strong> {{ $search->start_date->format('d/m/Y') }} a {{ $search->end_date->format('d/m/Y') }}</li>
                                    @endif
                                    @if($search->number_of_guests)
                                        <li class="mb-2"><i class="fas fa-users me-2" style="color: #6d28d9;"></i><strong>Hóspedes:</strong> {{ $search->number_of_guests }}</li>
                                    @endif
                                    @if($search->price_min || $search->price_max)
                                        <li class="mb-2"><i class="fas fa-dollar-sign me-2" style="color: #6d28d9;"></i><strong>Preço:</strong>
                                            R$ {{ $search->price_min ? number_format($search->price_min, 2, ',', '.') : '0,00' }}
                                            até R$ {{ $search->price_max ? number_format($search->price_max, 2, ',', '.') : '10.000,00' }}
                                        </li>
                                    @endif
                                </ul>
                                <small class="text-muted d-block mb-3">
                                    <i class="fas fa-calendar-plus me-1"></i>Salva em {{ $search->created_at->format('d/m/Y H:i') }}
                                </small>
                                <form method="POST" action="{{ route('client.wishlist.remove', $search) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Tem certeza que deseja remover esta busca?')">
                                        <i class="fas fa-trash me-2"></i>Remover busca
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
