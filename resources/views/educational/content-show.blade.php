@extends('layouts.app')

@section('title', $content->title . ' - Cota Brasilis')

@section('content')
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <span class="badge bg-secondary mb-2">{{ ucfirst($content->content_type) }}</span>
                @if($content->profile_type_required)
                    <span class="badge bg-info text-dark mb-2">{{ ucfirst($content->profile_type_required) }}</span>
                @else
                    <span class="badge bg-success mb-2">Todos os perfis</span>
                @endif
                <h1 class="h3 fw-bold mb-0">{{ $content->title }}</h1>
                @if($content->description)
                    <p class="text-muted mt-3 mb-0">{{ $content->description }}</p>
                @endif
            </div>
            <a href="{{ route('educational.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>

        @if($content->body)
            <div class="educational-body border-top pt-4 mt-2">
                {!! $content->body !!}
            </div>
        @else
            <p class="text-muted">Este conteúdo ainda não possui texto completo cadastrado.</p>
        @endif
    </div>
</div>
@endsection
