@extends('layouts.app')

@section('title', 'Otimização de Êxito - Cota Brasilis')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-success text-white rounded-top-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-chart-line me-2"></i>Otimização de Êxito
                        </h4>
                        <span class="badge bg-light text-success">{{ $rentalOffers->count() + $saleOffers->count() + $exchangeOffers->count() }} Ofertas</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Gerencie todas as suas ofertas de aluguel, venda e troca em um só lugar. 
                        Você pode editar ofertas que ainda não têm negociações em andamento.
                    </p>

                    <!-- Tabs para navegação entre tipos de ofertas -->
                    <ul class="nav nav-tabs mb-4" id="offerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental" type="button" role="tab">
                                <i class="fas fa-bed me-2"></i>Ofertas de Aluguel
                                <span class="badge bg-primary ms-2">{{ $rentalOffers->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sale-tab" data-bs-toggle="tab" data-bs-target="#sale" type="button" role="tab">
                                <i class="fas fa-dollar-sign me-2"></i>Ofertas de Venda
                                <span class="badge bg-success ms-2">{{ $saleOffers->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="exchange-tab" data-bs-toggle="tab" data-bs-target="#exchange" type="button" role="tab">
                                <i class="fas fa-exchange-alt me-2"></i>Ofertas de Troca
                                <span class="badge bg-info ms-2">{{ $exchangeOffers->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="offerTabsContent">
                        <!-- Ofertas de Aluguel -->
                        <div class="tab-pane fade show active" id="rental" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Ofertas de Aluguel</h5>
                                <a href="{{ route('rental-offers.create') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-2"></i>Nova Oferta
                                </a>
                            </div>
                            
                            @if($rentalOffers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Hotel</th>
                                                <th>Período</th>
                                                <th>Preço</th>
                                                <th>Status</th>
                                                <th>Visualizações</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rentalOffers as $offer)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $offer->hotel->name ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">{{ $offer->city }}, {{ $offer->state }}</small>
                                                    </td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') }} - 
                                                        {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                                        <br>
                                                        <small class="text-muted">{{ $offer->number_of_days }} dias</small>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">R$ {{ number_format($offer->price, 2, ',', '.') }}</strong>
                                                        @if($offer->is_auction)
                                                            <br><span class="badge bg-warning text-dark">Leilão</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($offer->status === 'active')
                                                            <span class="badge bg-success">Ativa</span>
                                                        @elseif($offer->status === 'negotiated')
                                                            <span class="badge bg-primary">Negociada</span>
                                                        @elseif($offer->status === 'cancelled')
                                                            <span class="badge bg-danger">Cancelada</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($offer->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-eye me-1"></i>{{ $offer->views_count ?? 0 }}
                                                        <br>
                                                        <i class="fas fa-heart me-1 text-danger"></i>{{ $offer->favorites_count ?? 0 }}
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('rental-offers.show', $offer->id) }}" class="btn btn-outline-primary" title="Ver detalhes">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($offer->can_edit)
                                                                <a href="{{ route('rental-offers.edit', $offer->id) }}" class="btn btn-outline-success" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @else
                                                                <button class="btn btn-outline-secondary" disabled title="Não é possível editar - há negociação em andamento">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Você ainda não criou nenhuma oferta de aluguel.
                                    <a href="{{ route('rental-offers.create') }}" class="alert-link">Criar primeira oferta</a>
                                </div>
                            @endif
                        </div>

                        <!-- Ofertas de Venda -->
                        <div class="tab-pane fade" id="sale" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Ofertas de Venda</h5>
                                <a href="{{ route('sales.create') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-2"></i>Nova Oferta
                                </a>
                            </div>
                            
                            @if($saleOffers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Hotel</th>
                                                <th>Semanas</th>
                                                <th>Quartos</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($saleOffers as $offer)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $offer->hotel->name ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">{{ $offer->city }}, {{ $offer->state ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>{{ $offer->weeks }} semana(s)</td>
                                                    <td>{{ $offer->number_of_rooms }} quarto(s)</td>
                                                    <td>
                                                        @if($offer->status === 'pending')
                                                            <span class="badge bg-warning">Pendente</span>
                                                        @elseif($offer->status === 'negotiating')
                                                            <span class="badge bg-primary">Em Negociação</span>
                                                        @elseif($offer->status === 'sold')
                                                            <span class="badge bg-success">Vendida</span>
                                                        @elseif($offer->status === 'cancelled')
                                                            <span class="badge bg-danger">Cancelada</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($offer->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('sales.show', $offer->id) }}" class="btn btn-outline-primary" title="Ver detalhes">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($offer->can_edit)
                                                                <a href="{{ route('sales.edit', $offer->id) }}" class="btn btn-outline-success" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @else
                                                                <button class="btn btn-outline-secondary" disabled title="Não é possível editar - há negociação em andamento">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Você ainda não criou nenhuma oferta de venda.
                                    <a href="{{ route('sales.create') }}" class="alert-link">Criar primeira oferta</a>
                                </div>
                            @endif
                        </div>

                        <!-- Ofertas de Troca -->
                        <div class="tab-pane fade" id="exchange" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Ofertas de Troca</h5>
                                <a href="{{ route('exchanges.create') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-2"></i>Nova Oferta
                                </a>
                            </div>
                            
                            @if($exchangeOffers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Cidade Desejada</th>
                                                <th>Período</th>
                                                <th>Status</th>
                                                <th>Validade</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($exchangeOffers as $offer)
                                                <tr>
                                                    <td>
                                                        @if($offer->exchange_type === 'semana')
                                                            <span class="badge bg-info">Semana</span>
                                                        @else
                                                            <span class="badge bg-primary">Titularidade</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $offer->desired_cities_labels !== '' ? $offer->desired_cities_labels : ($offer->desired_city ?? 'N/A') }}</strong>
                                                        @if($offer->desired_hotels_labels !== '')
                                                            <br><small class="text-muted">{{ $offer->desired_hotels_labels }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($offer->desired_period_start && $offer->desired_period_end)
                                                            {{ \Carbon\Carbon::parse($offer->desired_period_start)->format('d/m/Y') }} - 
                                                            {{ \Carbon\Carbon::parse($offer->desired_period_end)->format('d/m/Y') }}
                                                        @else
                                                            <span class="text-muted">Não especificado</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($offer->status === 'active')
                                                            <span class="badge bg-success">Ativa</span>
                                                        @elseif($offer->status === 'negotiating')
                                                            <span class="badge bg-primary">Em Negociação</span>
                                                        @elseif($offer->status === 'completed')
                                                            <span class="badge bg-success">Completada</span>
                                                        @elseif($offer->status === 'cancelled')
                                                            <span class="badge bg-danger">Cancelada</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ ucfirst($offer->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($offer->validity_until)
                                                            {{ \Carbon\Carbon::parse($offer->validity_until)->format('d/m/Y H:i') }}
                                                            @if($offer->validity_until < now())
                                                                <br><span class="badge bg-danger">Expirada</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Sem validade</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('exchanges.show', $offer->id) }}" class="btn btn-outline-primary" title="Ver detalhes">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($offer->can_edit)
                                                                <a href="{{ route('exchanges.edit', $offer->id) }}" class="btn btn-outline-success" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @else
                                                                <button class="btn btn-outline-secondary" disabled title="Não é possível editar - há negociação em andamento">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>Você ainda não criou nenhuma oferta de troca.
                                    <a href="{{ route('exchanges.create') }}" class="alert-link">Criar primeira oferta</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
