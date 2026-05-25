@extends('layouts.app')

@section('title', $page->title . ' - Cota Brasilis')

@section('content')
<section class="site-page-institutional py-5">
    <div class="container site-page-container">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
            <nav aria-label="Navegação estrutural" class="site-page-breadcrumb mb-0">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>
            @include('partials.site-back-to-home')
        </div>

        <article class="card site-page-card border-0">
            <div class="card-body p-4 p-lg-5">
                <header class="mb-0">
                    <h1 class="site-page-title fw-bold mb-0">{{ $page->title }}</h1>
                </header>
                <div class="site-page-body pt-4">
                    @if($page->body)
                        {!! $page->body !!}
                    @else
                        <p class="text-muted mb-0">Conteúdo disponível em breve.</p>
                    @endif
                </div>
            </div>
        </article>
    </div>
</section>
@endsection
