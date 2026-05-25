@extends('admin.layout')

@section('title', 'Editar texto educativo')
@section('page-title', 'Editar texto educativo')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.educational.contents.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.educational.contents.update', $content) }}">
            @csrf
            @method('PUT')
            @include('admin.educational.contents._form', ['content' => $content])
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
