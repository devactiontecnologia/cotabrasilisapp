@extends('admin.layout')

@section('title', 'Nova pergunta frequente')
@section('page-title', 'Nova pergunta frequente')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.faqs.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.faqs.store') }}" id="faq_form">
            @csrf
            @include('admin.faq._form', ['faq' => null])
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
