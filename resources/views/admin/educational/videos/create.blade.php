@extends('admin.layout')

@section('title', 'Novo vídeo educativo')
@section('page-title', 'Novo vídeo educativo')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.educational.videos.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.educational.videos.store') }}">
            @csrf
            @include('admin.educational.videos._form', ['video' => null, 'contents' => $contents])
            <div class="mt-4">
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
