@extends('layouts.app')

@section('title', 'Nova Cota')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus me-2"></i>Nova Cota
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quota-management.store') }}" enctype="multipart/form-data" id="quotaForm">
                        @csrf
                        
                        <!-- Step 1: Hotel Information -->
                        <div class="step-content" id="step1">
                            <h5 class="mb-3">
                                <i class="fas fa-hotel me-2"></i>Informações do Hotel
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="hotel_id" class="form-label">Hotel <span class="text-danger">*</span></label>
                                    <select name="hotel_id" id="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
                                        <option value="">Selecione um hotel</option>
                                        @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }} - {{ $hotel->city }}/{{ $hotel->state }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('hotel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Quota Details -->
                        <div class="step-content d-none" id="step2">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>Detalhes da Cota
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="weeks" class="form-label">Número de Semanas <span class="text-danger">*</span></label>
                                    <select name="weeks" id="weeks" class="form-select @error('weeks') is-invalid @enderror" required>
                                        <option value="">Selecione</option>
                                        <option value="1" {{ old('weeks') == '1' ? 'selected' : '' }}>1 Semana</option>
                                        <option value="2" {{ old('weeks') == '2' ? 'selected' : '' }}>2 Semanas</option>
                                        <option value="3" {{ old('weeks') == '3' ? 'selected' : '' }}>3 Semanas</option>
                                        <option value="4" {{ old('weeks') == '4' ? 'selected' : '' }}>4 Semanas</option>
                                    </select>
                                    @error('weeks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="number_of_rooms" class="form-label">Número de Quartos <span class="text-danger">*</span></label>
                                    <select name="number_of_rooms" id="number_of_rooms" class="form-select @error('number_of_rooms') is-invalid @enderror" required>
                                        <option value="">Selecione</option>
                                        @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('number_of_rooms') == $i ? 'selected' : '' }}>{{ $i }} Quarto{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                    @error('number_of_rooms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="seasonality" class="form-label">Sazonalidade <span class="text-danger">*</span></label>
                                    <select name="seasonality" id="seasonality" class="form-select @error('seasonality') is-invalid @enderror" required>
                                        <option value="">Selecione</option>
                                        <option value="low" {{ old('seasonality') == 'low' ? 'selected' : '' }}>Baixa</option>
                                        <option value="medium" {{ old('seasonality') == 'medium' ? 'selected' : '' }}>Média</option>
                                        <option value="high" {{ old('seasonality') == 'high' ? 'selected' : '' }}>Alta</option>
                                        <option value="peak" {{ old('seasonality') == 'peak' ? 'selected' : '' }}>Altíssima</option>
                                    </select>
                                    @error('seasonality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Dates and Pricing -->
                        <div class="step-content d-none" id="step3">
                            <h5 class="mb-3">
                                <i class="fas fa-calendar me-2"></i>Período e Preços
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Data de Início <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ old('start_date') }}" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">Data de Fim <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="number_of_guests" class="form-label">Número de Hóspedes <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_guests" id="number_of_guests" 
                                           class="form-control @error('number_of_guests') is-invalid @enderror" 
                                           value="{{ old('number_of_guests', 1) }}" 
                                           min="1" max="20" required>
                                    @error('number_of_guests')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="rental_price" class="form-label">Preço de Aluguel (R$)</label>
                                    <input type="number" name="rental_price" id="rental_price" 
                                           class="form-control @error('rental_price') is-invalid @enderror" 
                                           value="{{ old('rental_price') }}" 
                                           min="0" step="0.01">
                                    @error('rental_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Documents -->
                        <div class="step-content d-none" id="step4">
                            <h5 class="mb-3">
                                <i class="fas fa-file-upload me-2"></i>Documentos
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="contract_photo" class="form-label">Foto do Contrato <span class="text-danger">*</span></label>
                                    <input type="file" name="contract_photo" id="contract_photo" 
                                           class="form-control @error('contract_photo') is-invalid @enderror" 
                                           accept="image/*" required>
                                    <div class="form-text">Formatos aceitos: JPG, PNG. Tamanho máximo: 5MB</div>
                                    @error('contract_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <!-- Image Preview -->
                                    <div id="contract-preview" class="mt-2 d-none">
                                        <img id="contract-preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="authorizations" class="form-label">Autorizações (Opcional)</label>
                                    <input type="file" name="authorizations[]" id="authorizations" 
                                           class="form-control @error('authorizations') is-invalid @enderror" 
                                           accept=".pdf,image/*" multiple>
                                    <div class="form-text">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 10MB por arquivo</div>
                                    @error('authorizations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Additional Information -->
                        <div class="step-content d-none" id="step5">
                            <h5 class="mb-3">
                                <i class="fas fa-comment me-2"></i>Informações Adicionais
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="observations" class="form-label">Observações</label>
                                    <textarea name="observations" id="observations" rows="4" 
                                              class="form-control @error('observations') is-invalid @enderror" 
                                              placeholder="Informações adicionais sobre a cota...">{{ old('observations') }}</textarea>
                                    @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="prevBtn" class="btn btn-outline-secondary" onclick="changeStep(-1)" style="display: none;">
                                <i class="fas fa-arrow-left me-1"></i>Anterior
                            </button>
                            
                            <div class="ms-auto">
                                <button type="button" id="nextBtn" class="btn btn-primary" onclick="changeStep(1)">
                                    Próximo <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                                
                                <button type="submit" id="submitBtn" class="btn btn-success d-none">
                                    <i class="fas fa-save me-1"></i>Salvar Cota
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 5;

function changeStep(direction) {
    const currentStepElement = document.getElementById(`step${currentStep}`);
    const nextStep = currentStep + direction;
    
    if (nextStep < 1 || nextStep > totalSteps) return;
    
    // Hide current step
    currentStepElement.classList.add('d-none');
    
    // Show next step
    currentStep = nextStep;
    const nextStepElement = document.getElementById(`step${currentStep}`);
    nextStepElement.classList.remove('d-none');
    
    // Update buttons
    updateButtons();
}

function updateButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Previous button
    if (currentStep === 1) {
        prevBtn.style.display = 'none';
    } else {
        prevBtn.style.display = 'inline-block';
    }
    
    // Next/Submit button
    if (currentStep === totalSteps) {
        nextBtn.classList.add('d-none');
        submitBtn.classList.remove('d-none');
    } else {
        nextBtn.classList.remove('d-none');
        submitBtn.classList.add('d-none');
    }
}

// Image preview for contract photo
document.getElementById('contract_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('contract-preview');
            const previewImg = document.getElementById('contract-preview-img');
            previewImg.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});

// Date validation
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('end_date');
    const minEndDate = new Date(startDate);
    minEndDate.setDate(minEndDate.getDate() + 1);
    endDateInput.min = minEndDate.toISOString().split('T')[0];
});

// Form validation
document.getElementById('quotaForm').addEventListener('submit', function(e) {
    if (currentStep !== totalSteps) {
        e.preventDefault();
        return false;
    }
});
</script>
@endpush
@endsection