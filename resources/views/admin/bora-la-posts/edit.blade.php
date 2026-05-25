@extends('admin.layout')

@section('title', 'Editar publicação Bora lá')
@section('page-title', 'Editar publicação Bora lá')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.bora-la-posts.index', ['type' => $post->type]) }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.bora-la-posts.update', $post) }}">
            @csrf
            @method('PUT')
            @include('admin.bora-la-posts._form', ['post' => $post])
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
