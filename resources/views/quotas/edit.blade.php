@extends('layouts.app')

@section('title', 'Editar Cota')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-4">
                    <div class="text-center">
                        <h2 class="fw-bold mb-2">
                            <i class="fas fa-edit me-2"></i>Editar Cota
                        </h2>
                        <p class="mb-0">Atualize as informações da sua cota</p>
                    </div>
                </div>
                
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('quotas.update', $quota) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="hotel_name" class="form-label fw-semibold">
                                    <i class="fas fa-hotel me-2 text-primary"></i>Nome do Hotel *
                                </label>
                                <input type="text" class="form-control form-control-lg @error('hotel_name') is-invalid @enderror" 
                                       id="hotel_name" name="hotel_name" value="{{ old('hotel_name', $quota->hotel_name) }}" 
                                       placeholder="Ex: Hotel Copacabana Palace" required>
                                @error('hotel_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="location" class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Localização *
                                </label>
                                <input type="text" class="form-control form-control-lg @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location', $quota->location) }}" 
                                       placeholder="Ex: Copacabana, Rio de Janeiro - RJ" required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Data de Início *
                                </label>
                                <input type="date" class="form-control form-control-lg @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date', $quota->start_date ? \Carbon\Carbon::parse($quota->start_date)->format('Y-m-d') : '') }}" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Data de Fim *
                                </label>
                                <input type="date" class="form-control form-control-lg @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" value="{{ old('end_date', $quota->end_date ? \Carbon\Carbon::parse($quota->end_date)->format('Y-m-d') : '') }}" 
                                       min="{{ date('Y-m-d', strtotime('+2 days')) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="number_of_guests" class="form-label fw-semibold">
                                    <i class="fas fa-users me-2 text-primary"></i>Número de Hóspedes *
                                </label>
                                <select class="form-select form-select-lg @error('number_of_guests') is-invalid @enderror" 
                                        id="number_of_guests" name="number_of_guests" required>
                                    <option value="">Selecione</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('number_of_guests', $quota->number_of_guests) == $i ? 'selected' : '' }}>
                                            {{ $i }} {{ $i == 1 ? 'pessoa' : 'pessoas' }}
                                        </option>
                                    @endfor
                                </select>
                                @error('number_of_guests')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-exchange-alt me-2 text-primary"></i>Tipo de Cota
                                </label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_exchange" id="is_exchange" value="1" 
                                           {{ old('is_exchange', $quota->is_exchange) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_exchange">
                                        Disponível para troca
                                    </label>
                                </div>
                            </div>

                            <div class="col-12" id="rental_price_field" style="display: {{ old('is_exchange', $quota->is_exchange) ? 'none' : 'block' }};">
                                <label for="rental_price" class="form-label fw-semibold">
                                    <i class="fas fa-dollar-sign me-2 text-primary"></i>Preço de Aluguel (R$) *
                                </label>
                                <input type="number" class="form-control form-control-lg @error('rental_price') is-invalid @enderror" 
                                       id="rental_price" name="rental_price" value="{{ old('rental_price', $quota->rental_price) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('rental_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="observations" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-2 text-primary"></i>Observações
                                </label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" 
                                          id="observations" name="observations" rows="4" 
                                          placeholder="Informações adicionais sobre a cota...">{{ old('observations', $quota->observations) }}</textarea>
                                @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="contract_photo" class="form-label fw-semibold">
                                    <i class="fas fa-camera me-2 text-primary"></i>Foto do Contrato
                                </label>
                                <input type="file" class="form-control @error('contract_photo') is-invalid @enderror" 
                                       id="contract_photo" name="contract_photo" accept="image/jpeg,image/jpg,image/png">
                                <div class="form-text">Formatos aceitos: JPEG, JPG, PNG. Tamanho máximo: 2MB</div>
                                @if($quota->contract_photo_path)
                                    <div class="mt-2">
                                        <small class="text-muted">Foto atual:</small>
                                        <a href="{{ asset('storage/' . $quota->contract_photo_path) }}" target="_blank" class="ms-2">
                                            <i class="fas fa-eye me-1"></i>Visualizar foto atual
                                        </a>
                                    </div>
                                @endif
                                @error('contract_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5">
                            <a href="{{ route('quotas.show', $quota) }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle rental price field based on exchange checkbox
document.getElementById('is_exchange')?.addEventListener('change', function() {
    const rentalPriceField = document.getElementById('rental_price_field');
    const rentalPriceInput = document.getElementById('rental_price');
    
    if (this.checked) {
        rentalPriceField.style.display = 'none';
        rentalPriceInput.required = false;
        rentalPriceInput.value = '';
    } else {
        rentalPriceField.style.display = 'block';
        rentalPriceInput.required = true;
    }
});

// Update end date minimum when start date changes
document.getElementById('start_date')?.addEventListener('change', function() {
    const endDate = document.getElementById('end_date');
    if (this.value) {
        const startDate = new Date(this.value);
        startDate.setDate(startDate.getDate() + 1);
        endDate.min = startDate.toISOString().split('T')[0];
    }
});
</script>
@endpush
@endsection



