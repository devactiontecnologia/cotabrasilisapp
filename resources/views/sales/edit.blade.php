@extends('layouts.app')

@section('title', 'Editar Oferta de Venda - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Editar Oferta de Venda</h4>
            <a href="{{ route('sales.show', $saleOffer) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('sales.update', $saleOffer) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <h5 class="fw-bold mb-3">Informações Básicas</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="quota_id" class="form-label fw-semibold">Cota (Opcional)</label>
                        <select class="form-select @error('quota_id') is-invalid @enderror" id="quota_id" name="quota_id">
                            <option value="">Não vinculada a uma cota específica</option>
                            @foreach($quotas as $quota)
                                <option value="{{ $quota->id }}" {{ (string) old('quota_id', $saleOffer->quota_id) === (string) $quota->id ? 'selected' : '' }}>
                                    {{ $quota->hotel_name }} - {{ $quota->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('quota_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="hotel_id" class="form-label fw-semibold">Hotel *</label>
                        <select class="form-select @error('hotel_id') is-invalid @enderror" id="hotel_id" name="hotel_id" required>
                            <option value="">Selecione um hotel</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}" {{ (string) old('hotel_id', $saleOffer->hotel_id) === (string) $hotel->id ? 'selected' : '' }}>
                                    {{ $hotel->name }} - {{ $hotel->city }}, {{ $hotel->state }}
                                </option>
                            @endforeach
                        </select>
                        @error('hotel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="weeks" class="form-label fw-semibold">Semanas (1-4) *</label>
                        <select class="form-select @error('weeks') is-invalid @enderror" id="weeks" name="weeks" required>
                            @for($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ (int) old('weeks', $saleOffer->weeks) === $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'semana' : 'semanas' }}
                                </option>
                            @endfor
                        </select>
                        @error('weeks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="number_of_rooms" class="form-label fw-semibold">Número de Quartos *</label>
                        <input type="number" class="form-control @error('number_of_rooms') is-invalid @enderror"
                               id="number_of_rooms" name="number_of_rooms"
                               value="{{ old('number_of_rooms', $saleOffer->number_of_rooms) }}" min="1" required>
                        @error('number_of_rooms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="city" class="form-label fw-semibold">Cidade *</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror"
                               id="city" name="city" value="{{ old('city', $saleOffer->city) }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label for="company" class="form-label fw-semibold">Empresa</label>
                        <input type="text" class="form-control @error('company') is-invalid @enderror"
                               id="company" name="company" value="{{ old('company', $saleOffer->company) }}"
                               placeholder="Nome da empresa (opcional)">
                        @error('company')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold mb-3">Preços</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="minimum_price" class="form-label fw-semibold">Preço mínimo *</label>
                        <input type="number" class="form-control @error('minimum_price') is-invalid @enderror"
                               id="minimum_price" name="minimum_price"
                               value="{{ old('minimum_price', $saleOffer->minimum_price) }}" step="0.01" min="0" required>
                        @error('minimum_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="acceptable_price" class="form-label fw-semibold">Preço aceitável *</label>
                        <input type="number" class="form-control @error('acceptable_price') is-invalid @enderror"
                               id="acceptable_price" name="acceptable_price"
                               value="{{ old('acceptable_price', $saleOffer->acceptable_price) }}" step="0.01" min="0" required>
                        @error('acceptable_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="desired_price" class="form-label fw-semibold">Preço desejado *</label>
                        <input type="number" class="form-control @error('desired_price') is-invalid @enderror"
                               id="desired_price" name="desired_price"
                               value="{{ old('desired_price', $saleOffer->desired_price) }}" step="0.01" min="0" required>
                        @error('desired_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('sales.show', $saleOffer) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
