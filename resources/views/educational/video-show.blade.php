@extends('layouts.app')

@section('title', $video->title . ' - Cota Brasilis')

@section('content')
@php
    $embedUrl = null;
    $url = $video->video_url ?? '';
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $url, $m)) {
        $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (preg_match('/youtu\.be\/([^?&]+)/', $url, $m)) {
        $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (preg_match('/youtube\.com\/embed\/([^?&]+)/', $url, $m)) {
        $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
    }
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="fw-semibold mb-2">{{ $video->title }}</h4>
                @if($video->description)
                    <p class="text-muted mb-0">{{ $video->description }}</p>
                @endif
            </div>
            <a href="{{ route('educational.videos') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        @if($embedUrl)
            <div class="ratio ratio-16x9 rounded overflow-hidden bg-secondary bg-opacity-10 border">
                <iframe src="{{ $embedUrl }}" title="{{ $video->title }}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        @else
            <p class="mb-2">Abra o vídeo no link abaixo:</p>
            <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                <i class="fas fa-external-link-alt me-2"></i>Assistir em nova aba
            </a>
        @endif
    </div>
</div>
@endsection
