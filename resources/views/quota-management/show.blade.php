@extends('layouts.app')

@section('title', 'Detalhes da Cota')

@section('content')
<!-- Botão Voltar - Canto Superior Direito -->
<button onclick="window.history.back();" class="btn btn-outline-primary btn-lg position-fixed" style="top: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ $quota->hotel_name }}</h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt me-1"></i>{{ $quota->location }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('quota-management.edit', $quota) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <a href="{{ route('quota-management.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Voltar
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Information -->
                <div class="col-lg-8">
                    <!-- Status Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-eye fa-2x {{ $quota->is_published ? 'text-success' : 'text-muted' }} mb-2"></i>
                                    <h6 class="card-title">Status</h6>
                                    <span class="badge bg-{{ $quota->is_published ? 'success' : 'secondary' }}">
                                        {{ $quota->is_published ? 'Publicada' : 'Não Publicada' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-credit-card fa-2x {{ $quota->payment_status == 'paid' ? 'text-success' : 'text-warning' }} mb-2"></i>
                                    <h6 class="card-title">Pagamento</h6>
                                    <span class="badge bg-{{ $quota->payment_status == 'paid' ? 'success' : 'warning' }}">
                                        {{ $quota->getPaymentStatusLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-tags fa-2x text-info mb-2"></i>
                                    <h6 class="card-title">Ofertas Ativas</h6>
                                    <h4 class="mb-0">{{ $quota->activeRentalOffers->count() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-calendar fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">Sazonalidade</h6>
                                    <span class="badge bg-primary">{{ $quota->getSeasonalityLabel() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Photo -->
                    @if($quota->contract_photo_path)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-image me-2"></i>Foto do Contrato
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset('storage/' . $quota->contract_photo_path) }}" 
                                 alt="Foto do Contrato" 
                                 class="img-fluid rounded" 
                                 style="max-height: 400px;">
                        </div>
                    </div>
                    @endif

                    <!-- Quota Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Detalhes da Cota
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Semanas:</td>
                                            <td>{{ $quota->weeks }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Número de Quartos:</td>
                                            <td>{{ $quota->number_of_rooms }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Número de Hóspedes:</td>
                                            <td>{{ $quota->number_of_guests }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Proprietário:</td>
                                            <td>
                                                @if($quota->is_owner)
                                                <span class="badge bg-success">Sim</span>
                                                @else
                                                <span class="badge bg-warning">Não</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Data de Início:</td>
                                            <td>{{ \Carbon\Carbon::parse($quota->start_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Data de Fim:</td>
                                            <td>{{ \Carbon\Carbon::parse($quota->end_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Duração:</td>
                                            <td>{{ \Carbon\Carbon::parse($quota->start_date)->diffInDays(\Carbon\Carbon::parse($quota->end_date)) }} {{ \Carbon\Carbon::parse($quota->start_date)->diffInDays(\Carbon\Carbon::parse($quota->end_date)) == 1 ? 'pernoite' : 'pernoites' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status da Cota:</td>
                                            <td>
                                                <span class="badge bg-{{ $quota->quota_status == 'active' ? 'success' : ($quota->quota_status == 'inactive' ? 'secondary' : 'warning') }}">
                                                    {{ $quota->getQuotaStatusLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($quota->rental_price)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-dollar-sign me-1"></i>Preço de Aluguel
                                        </h6>
                                        <h4 class="mb-0 text-primary">R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($quota->observations)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Observações:</h6>
                                    <p class="text-muted">{{ $quota->observations }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                @foreach($quota->getRegistrationDetailsForDisplay() as $detail)
                                                <tr>
                                                    <td class="fw-bold">
                                                        <i class="fas {{ $detail['icon'] ?? 'fa-circle-info' }} text-success me-2"></i>
                                                        {{ $detail['label'] }}:
                                                    </td>
                                                    <td>{{ $detail['value'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Authorizations -->
                    @if($quota->authorizations && count($quota->authorizations) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>Autorizações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($quota->authorizations as $index => $authorization)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <h6 class="card-title">Autorização {{ $index + 1 }}</h6>
                                            <a href="{{ asset('storage/' . $authorization) }}" 
                                               target="_blank" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-download me-1"></i>Visualizar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Active Offers -->
                    @if($quota->activeRentalOffers->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tags me-2"></i>Ofertas Ativas ({{ $quota->activeRentalOffers->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($quota->activeRentalOffers as $offer)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $offer->display_title }}</h6>
                                            <p class="card-text text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') }} - 
                                                {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                            </p>
                                            <p class="card-text">
                                                <strong>R$ {{ number_format($offer->price, 2, ',', '.') }}</strong>
                                            </p>
                                            <a href="{{ route('rental-offers.show', $offer) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>Ver Oferta
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar Actions -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>Ações Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($quota->canBePublished())
                                <form method="POST" action="{{ route('quota-management.publish', $quota) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-eye me-1"></i>Publicar Cota
                                    </button>
                                </form>
                                @elseif($quota->is_published)
                                <form method="POST" action="{{ route('quota-management.unpublish', $quota) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-eye-slash me-1"></i>Despublicar Cota
                                    </button>
                                </form>
                                @endif

                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#createOfferModal">
                                    <i class="fas fa-plus me-1"></i>Criar Oferta
                                </button>

                                <button type="button" class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#transferModal">
                                    <i class="fas fa-exchange-alt me-1"></i>Transferir Titularidade
                                </button>

                                @if($quota->payment_status == 'unpaid')
                                <form method="POST" action="{{ route('quota-management.mark-paid', $quota) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fas fa-check me-1"></i>Marcar como Quitada
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('quota-management.mark-unpaid', $quota) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-times me-1"></i>Marcar como Não Quitada
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quota Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info me-2"></i>Informações
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Criada em:</strong><br>
                                {{ $quota->created_at->format('d/m/Y H:i') }}
                            </p>
                            <p class="mb-2">
                                <strong>Última atualização:</strong><br>
                                {{ $quota->updated_at->format('d/m/Y H:i') }}
                            </p>
                            @if($quota->published_at)
                            <p class="mb-0">
                                <strong>Publicada em:</strong><br>
                                {{ \Carbon\Carbon::parse($quota->published_at)->format('d/m/Y H:i') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Offer Modal -->
<div class="modal fade" id="createOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Nova Oferta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('quota-management.create-offer', $quota) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="offer_type" class="form-label">Tipo de Oferta</label>
                            <select name="offer_type" id="offer_type" class="form-select" required>
                                <option value="rent">Alugar</option>
                                <option value="exchange">Trocar</option>
                                <option value="sell">Vender</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Preço original (R$)</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">Título</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Oferta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transferir Titularidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('quota-management.transfer', $quota) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="new_owner_email" class="form-label">E-mail do Novo Proprietário</label>
                        <input type="email" name="new_owner_email" id="new_owner_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="transfer_reason" class="form-label">Motivo da Transferência</label>
                        <textarea name="transfer_reason" id="transfer_reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Transferir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Botão Voltar - Canto Inferior Direito -->
<button onclick="window.history.back();" class="btn btn-success btn-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 1050; border-radius: 50px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-left me-2"></i>Voltar
</button>
@endsection