@extends('admin.layout')

@section('title', 'Novo texto educativo')
@section('page-title', 'Novo texto educativo')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.educational.contents.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.educational.contents.store') }}">
            @csrf
            @include('admin.educational.contents._form', ['content' => null])
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
