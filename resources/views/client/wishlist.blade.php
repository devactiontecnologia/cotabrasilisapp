@extends('layouts.app')

@section('title', 'Desejados - Cota Brasilis')

@section('content')
@php
    use Illuminate\Support\Str;
    $hasAny = ($groupedQuotas->isNotEmpty() || $groupedSearches->isNotEmpty());
    $txOrder = ['rental', 'exchange', 'purchase'];
    $listOrder = ['state', 'city', 'hotel'];
@endphp

<section class="mb-5">
    <div class="p-5 p-lg-6 rounded-4 text-white" style="background: linear-gradient(135deg, rgba(147, 51, 234, 0.9), rgba(91, 33, 182, 0.85)); box-shadow: 0 26px 60px rgba(109, 40, 217, 0.36);">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
            <div>
                <span class="badge bg-light fw-semibold mb-3 px-3 py-2" style="color: #6d28d9 !important;">
                    <i class="fas fa-star me-2"></i>Desejados
                </span>
                <h1 class="display-6 fw-bold mb-3">Seu desejo, nossa prioridade</h1>
                <p class="lead mb-0" style="max-width: 620px;">
                    Organizado por <strong>Alugar</strong>, <strong>Troca</strong> e <strong>Comprar</strong>, depois por estado, cidade ou hotel.
                    Você será avisado quando houver ofertas; proprietários com semana compatível recebem alerta para publicar.
                </p>
            </div>
            <a href="{{ route('quotas.index') }}" class="btn btn-light fw-semibold px-4 py-3 rounded-3" style="color: #6d28d9 !important;">
                <i class="fas fa-search me-2"></i>Nova busca
            </a>
        </div>
    </div>
</section>

<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    @if(!$hasAny)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(147, 51, 234, 0.15);">
                    <i class="fas fa-star fa-3x" style="color: #6d28d9;"></i>
                </div>
                <h3 class="fw-bold mb-3">Nada salvo nos Desejados ainda</h3>
                <p class="text-muted mb-4">Use a estrela na busca ou salve uma busca sem resultado.</p>
                <a href="{{ route('quotas.index') }}" class="btn btn-lg px-4 text-white" style="background: #6d28d9; border-color: #6d28d9;">
                    <i class="fas fa-search me-2"></i>Fazer uma busca
                </a>
            </div>
        </div>
    @else
        @foreach($txOrder as $transType)
            @php
                $transInfo = $transactionTypes[$transType];
                $quotaTx = $groupedQuotas->get($transType, collect());
                $searchTx = $groupedSearches->get($transType, collect());
                $hasTx = $quotaTx->isNotEmpty() || $searchTx->isNotEmpty();
            @endphp
            @if($hasTx)
                <div class="mb-5">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-{{ $transInfo['color'] }}-subtle text-{{ $transInfo['color'] }}" style="width: 56px; height: 56px;">
                            <i class="fas {{ $transInfo['icon'] }} fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="h4 fw-bold mb-0">{{ $transInfo['title'] }}</h2>
                            <p class="text-muted mb-0 small">Desejados de {{ strtolower($transInfo['title']) }}</p>
                        </div>
                    </div>

                    @foreach($listOrder as $listType)
                        @php
                            $listInfo = $listTypes[$listType];
                            $quotaGroups = $quotaTx->get($listType, collect());
                            $searchGroups = $searchTx->get($listType, collect());
                        @endphp
                        @if($quotaGroups->isNotEmpty() || $searchGroups->isNotEmpty())
                            <div class="mb-4">
                                <h3 class="h6 fw-bold text-muted text-uppercase mb-3">
                                    <i class="fas {{ $listInfo['icon'] }} me-2"></i>{{ $listInfo['title'] }}
                                </h3>

                                @foreach($searchGroups as $listName => $searches)
                                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                                        <div class="card-header bg-light py-3">
                                            <strong><i class="fas fa-search me-2" style="color: #6d28d9;"></i>{{ $listName }}</strong>
                                            <span class="badge bg-secondary ms-2">Buscas salvas</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                @foreach($searches as $search)
                                                    <div class="col-lg-6">
                                                        <div class="border rounded-3 p-3 h-100">
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="small fw-semibold">Busca #{{ $search->id }}</span>
                                                                @if($search->notified)
                                                                    <span class="badge bg-success">Avisado</span>
                                                                @else
                                                                    <span class="badge bg-warning text-dark">Aguardando</span>
                                                                @endif
                                                            </div>
                                                            <ul class="list-unstyled small mb-3">
                                                                @if($search->start_date && $search->end_date)
                                                                    <li><i class="fas fa-calendar-alt me-1 text-purple"></i>{{ $search->start_date->format('d/m/Y') }} a {{ $search->end_date->format('d/m/Y') }}</li>
                                                                @endif
                                                                @if($search->number_of_guests)
                                                                    <li><i class="fas fa-users me-1 text-purple"></i>{{ $search->number_of_guests }} hóspede(s)</li>
                                                                @endif
                                                            </ul>
                                                            <a href="{{ route('quotas.index', array_filter(['search' => 1, 'transaction_type' => $transType === 'purchase' ? 'purchase' : $transType, 'hotel_name' => $search->hotel_name, 'city' => $search->city, 'state' => $search->state, 'check_in' => $search->start_date?->format('Y-m-d'), 'check_out' => $search->end_date?->format('Y-m-d'), 'people' => $search->number_of_guests])) }}" class="btn btn-sm btn-outline-primary w-100 mb-2" style="border-color: #6d28d9; color: #6d28d9;">Buscar de novo</a>
                                                            <form method="POST" action="{{ route('client.wishlist.remove', $search) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Remover esta busca?')">Remover</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach($quotaGroups as $listName => $quotas)
                                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                                        <div class="card-header bg-light py-3">
                                            <strong>{{ $listName }}</strong>
                                            <span class="badge ms-2" style="background: #6d28d9;">{{ $quotas->count() }} {{ Str::plural('cota', $quotas->count()) }}</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                @foreach($quotas as $quota)
                                                    <div class="col-lg-4">
                                                        <div class="border rounded-3 p-3 h-100">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="fw-bold mb-0">{{ $quota->hotel_name }}</h6>
                                                                <form method="POST" action="{{ route('client.wishlist.toggle', $quota) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="transaction_type" value="{{ $transType }}">
                                                                    <input type="hidden" name="list_type" value="{{ $listType }}">
                                                                    <button type="submit" class="btn btn-sm btn-warning text-white" title="Remover"><i class="fas fa-star"></i></button>
                                                                </form>
                                                            </div>
                                                            <p class="small text-muted mb-2">{{ $quota->location }}</p>
                                                            <p class="small mb-2">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                {{ $quota->start_date?->format('d/m/Y') }} a {{ $quota->end_date?->format('d/m/Y') }}
                                                            </p>
                                                            <a href="{{ route('quotas.show', ['quota' => $quota, 'transaction_type' => $transType]) }}" class="btn btn-sm btn-outline-primary w-100" style="border-color: #6d28d9; color: #6d28d9;">Ver detalhes</a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach
    @endif
</div>
@endsection
