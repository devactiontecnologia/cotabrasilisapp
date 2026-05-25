@extends('admin.layout')

@section('title', 'Editar: '.$sitePage->title)
@section('page-title', $sitePage->title)

@php
    $bodyValue = old('body', $sitePage->body ?? '');
    $bodyValue = str_ireplace('</textarea>', '<\/textarea>', $bodyValue);
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('admin.site-information.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Voltar à lista
        </a>
    </div>

    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">{{ $sitePage->title }}</h1>
        <p class="text-muted small mb-0">Conteúdo exibido na página institucional do rodapé. Slug: <code>{{ $sitePage->slug }}</code></p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.site-information.update', $sitePage) }}" id="site_info_form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Título público</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $sitePage->title) }}" required maxlength="255">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-1">
                    <label class="form-label fw-semibold">Conteúdo da página</label>
                </div>
                <textarea id="site_page_body" name="body" class="form-control @error('body') is-invalid @enderror" rows="12">{!! $bodyValue !!}</textarea>
                @error('body')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <p class="form-text small text-muted mt-2 mb-4">Edite no modo visual (igual em Perguntas frequentes). O conteúdo é salvo em HTML.</p>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Salvar alterações
                    </button>
                    <a href="{{ route('site.page', $sitePage->slug) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Ver página pública
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.partials.tinymce-init', ['editorId' => 'site_page_body', 'formId' => 'site_info_form'])
@endsection
