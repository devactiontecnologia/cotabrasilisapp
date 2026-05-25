@extends('layouts.app')

@section('title', 'Vídeos Educativos - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <h4 class="fw-semibold mb-4">Vídeos Educativos</h4>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($videos->isEmpty())
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(0, 151, 57, 0.12);">
                    <i class="fas fa-video fa-3x text-success"></i>
                </div>
                <h3 class="fw-bold mb-3">Nenhum vídeo disponível</h3>
                <p class="text-muted mb-4" style="max-width: 520px; margin: 0 auto;">
                    Em breve teremos vídeos educativos disponíveis para você.
                </p>
            </div>
        @else
            <div class="row g-4">
                @foreach($videos as $video)
                    <div class="col-md-4">
                        <div class="border rounded-4 h-100 p-4 shadow-sm bg-light">
                            <h5 class="fw-bold mb-3">{{ $video->title }}</h5>
                            <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($video->description ?? '', 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info">{{ $video->category ?? 'Geral' }}</span>
                                <a href="{{ route('educational.video.show', $video) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-play me-2"></i>Assistir
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $videos->links('vendor.pagination.modern') }}
            </div>
        @endif
    </div>
</div>
@endsection

