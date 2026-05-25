@extends('layouts.app')

@section('title', 'Enquetes - Bora lá!')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('bora-la.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao menu
            </a>
            <h2 class="fw-bold mb-3">
                <i class="fas fa-poll me-2" style="color: #8b5cf6;"></i>Enquetes
            </h2>
            <p class="text-muted">Pesquisas e consultas do gestor do aplicativo</p>
        </div>
    </div>

    @if($isManager)
    <!-- Formulário para Gestor -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header text-white py-3" style="background: #8b5cf6;">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Criar Nova Enquete</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bora-la.enquetes.store') }}" id="enqueteForm">
                @csrf
                
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold">Título da Enquete *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="question" class="form-label fw-semibold">Pergunta *</label>
                        <textarea class="form-control @error('question') is-invalid @enderror" 
                                  id="question" name="question" rows="3" required>{{ old('question') }}</textarea>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Opções de Resposta * (mínimo 2)</label>
                        <div id="options_container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="options[]" placeholder="Opção 1" required>
                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="options[]" placeholder="Opção 2" required>
                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOption()">
                            <i class="fas fa-plus me-1"></i>Adicionar Opção
                        </button>
                        @error('options')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tipo de Conteúdo *</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="content_type" id="enquete_content_text" value="text" {{ old('content_type', 'text') == 'text' ? 'checked' : '' }} required>
                            <label class="btn btn-outline-primary" for="enquete_content_text" style="border-color: #8b5cf6; color: #8b5cf6;">Texto</label>
                            
                            <input type="radio" class="btn-check" name="content_type" id="enquete_content_video" value="video" {{ old('content_type') == 'video' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="enquete_content_video" style="border-color: #8b5cf6; color: #8b5cf6;">Vídeo</label>
                            
                            <input type="radio" class="btn-check" name="content_type" id="enquete_content_both" value="both" {{ old('content_type') == 'both' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="enquete_content_both" style="border-color: #8b5cf6; color: #8b5cf6;">Texto + Vídeo</label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="enquete_text_fields">
                    <div class="col-12">
                        <label for="content_text" class="form-label fw-semibold">Conteúdo em Texto</label>
                        <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                  id="content_text" name="content_text" rows="6">{{ old('content_text') }}</textarea>
                        @error('content_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3 d-none" id="enquete_video_fields">
                    <div class="col-12">
                        <label for="content_video" class="form-label fw-semibold">URL do Vídeo</label>
                        <input type="url" class="form-control @error('content_video') is-invalid @enderror" 
                               id="content_video" name="content_video" value="{{ old('content_video') }}" placeholder="https://...">
                        <small class="text-muted">Cole o link do vídeo (YouTube, Vimeo, etc.)</small>
                        @error('content_video')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @include('client.bora-la.partials.filters-and-send')

                <div class="mt-4">
                    <button type="submit" class="btn btn-lg text-white" style="background: #8b5cf6; border-color: #8b5cf6;">
                        <i class="fas fa-paper-plane me-2"></i>Criar e Enviar Enquete
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Lista de Enquetes Recebidas -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Enquetes publicadas</h5>
        </div>
        <div class="card-body p-4">
            @include('client.bora-la.partials.post-cards', ['posts' => $posts, 'emptyMessage' => 'Nenhuma enquete publicada no momento.'])
        </div>
    </div>
</div>

@push('scripts')
<script>
let optionCount = 2;

function addOption() {
    optionCount++;
    const container = document.getElementById('options_container');
    const newOption = document.createElement('div');
    newOption.className = 'input-group mb-2';
    newOption.innerHTML = `
        <input type="text" class="form-control" name="options[]" placeholder="Opção ${optionCount}" required>
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newOption);
    updateRemoveButtons();
}

function removeOption(button) {
    const container = document.getElementById('options_container');
    if (container.children.length > 2) {
        button.closest('.input-group').remove();
        updateRemoveButtons();
    }
}

function updateRemoveButtons() {
    const container = document.getElementById('options_container');
    const buttons = container.querySelectorAll('.btn-outline-danger');
    buttons.forEach(btn => {
        btn.style.display = container.children.length > 2 ? 'block' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
    
    const contentTypeRadios = document.querySelectorAll('input[name="content_type"]');
    const textFields = document.getElementById('enquete_text_fields');
    const videoFields = document.getElementById('enquete_video_fields');
    const textInput = document.getElementById('content_text');
    const videoInput = document.getElementById('content_video');
    
    function toggleContentFields() {
        const selected = document.querySelector('input[name="content_type"]:checked').value;
        
        if (selected === 'text') {
            textFields.classList.remove('d-none');
            videoFields.classList.add('d-none');
            textInput.required = true;
            videoInput.required = false;
        } else if (selected === 'video') {
            textFields.classList.add('d-none');
            videoFields.classList.remove('d-none');
            textInput.required = false;
            videoInput.required = true;
        } else {
            textFields.classList.remove('d-none');
            videoFields.classList.remove('d-none');
            textInput.required = true;
            videoInput.required = true;
        }
    }
    
    contentTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleContentFields);
    });
    
    toggleContentFields();
    
    // Validação do formulário
    document.getElementById('enqueteForm').addEventListener('submit', function(e) {
        const options = document.querySelectorAll('input[name="options[]"]');
        const filledOptions = Array.from(options).filter(opt => opt.value.trim() !== '');
        
        if (filledOptions.length < 2) {
            e.preventDefault();
            alert('Você precisa de pelo menos 2 opções preenchidas.');
            return false;
        }
    });
});
</script>
@endpush
@endsection









