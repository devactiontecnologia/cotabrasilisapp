@extends('layouts.app')

@section('title', 'Oferta Única - Bora lá!')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('bora-la.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao menu
            </a>
            <h2 class="fw-bold mb-3">
                <i class="fas fa-star me-2 text-warning"></i>Oferta Única
            </h2>
        </div>
    </div>

    @if($isManager)
    <!-- Formulário para Gestor -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-warning text-white py-3">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Criar Nova Oferta Única</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bora-la.oferta-unica.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold">Título da Oferta *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">Descrição *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label fw-semibold">Data de Início *</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="start_time" class="form-label fw-semibold">Horário de Início *</label>
                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                               id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_date" class="form-label fw-semibold">Data de Término *</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                               id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_time" class="form-label fw-semibold">Horário de Término *</label>
                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                               id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tipo de Conteúdo *</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="content_type" id="content_text" value="text" {{ old('content_type', 'text') == 'text' ? 'checked' : '' }} required>
                            <label class="btn btn-outline-primary" for="content_text">Texto</label>
                            
                            <input type="radio" class="btn-check" name="content_type" id="content_video" value="video" {{ old('content_type') == 'video' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="content_video">Vídeo</label>
                            
                            <input type="radio" class="btn-check" name="content_type" id="content_both" value="both" {{ old('content_type') == 'both' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="content_both">Texto + Vídeo</label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="text_content_fields">
                    <div class="col-12">
                        <label for="content_text" class="form-label fw-semibold">Conteúdo em Texto</label>
                        <textarea class="form-control @error('content_text') is-invalid @enderror" 
                                  id="content_text" name="content_text" rows="6">{{ old('content_text') }}</textarea>
                        @error('content_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3 d-none" id="video_content_fields">
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
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Criar e Enviar Oferta
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Lista de Ofertas Recebidas -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Ofertas publicadas</h5>
        </div>
        <div class="card-body p-4">
            @include('client.bora-la.partials.post-cards', ['posts' => $posts, 'emptyMessage' => 'Nenhuma oferta única publicada no momento.'])
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentTypeRadios = document.querySelectorAll('input[name="content_type"]');
    const textFields = document.getElementById('text_content_fields');
    const videoFields = document.getElementById('video_content_fields');
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
});
</script>
@endpush
@endsection









