@extends('layouts.app')

@section('title', 'Conteúdos Educativos - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-semibold mb-0">Conteúdos Educativos</h4>
            <a href="{{ route('educational.videos') }}" class="btn btn-success">
                <i class="fas fa-video me-2"></i>Ver Vídeos
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($contents->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                    <i class="fas fa-graduation-cap fa-3x text-success"></i>
                </div>
                <h3 class="fw-bold mb-3">Nenhum conteúdo disponível</h3>
                <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                    Em breve teremos conteúdos educativos disponíveis para você.
                </p>
            </div>
        @else
            <div class="row g-4">
                @foreach($contents as $content)
                    <div class="col-md-6">
                        <div class="border rounded-4 h-100 p-4 shadow-sm bg-light d-flex flex-column">
                            <h5 class="fw-bold mb-3">{{ $content->title }}</h5>
                            <p class="text-muted mb-3 flex-grow-1">{{ \Illuminate\Support\Str::limit($content->description ?? '', 150) }}</p>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge bg-info">{{ ucfirst($content->content_type) }}</span>
                                @if($content->profile_type_required)
                                    <span class="badge bg-secondary">{{ ucfirst($content->profile_type_required) }}</span>
                                @else
                                    <span class="badge bg-success">Todos</span>
                                @endif
                                <a href="{{ route('educational.content.show', $content) }}" class="btn btn-sm btn-outline-success ms-auto">
                                    <i class="fas fa-book-open me-1"></i>Ler
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

