@extends('admin.layout')

@section('title', 'Editar pergunta frequente')
@section('page-title', 'Editar pergunta frequente')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.faqs.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" id="faq_form">
            @csrf
            @method('PUT')
            @include('admin.faq._form', ['faq' => $faq])
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>
@endsection
