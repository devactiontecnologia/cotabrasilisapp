@extends('admin.layout')

@section('title', 'Informações do site')
@section('page-title', 'Informações do site')

@php
    use App\Models\SitePage;
@endphp

@push('styles')
<style>
    :root {
        --site-info-green: #4c8435;
        --site-info-green-light: #5fa848;
    }
    .site-info-hero {
        max-width: 720px;
    }
    .site-info-card {
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .site-info-card .card-body {
        padding: 1.5rem 1.5rem 1.75rem;
    }
    .site-info-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: 0.02em;
        margin-bottom: 1.25rem;
        padding-bottom: 0.65rem;
        border-bottom: 2px solid rgba(76, 132, 53, 0.2);
    }
    .site-info-btn {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.2rem;
        border: none;
        border-radius: 14px;
        background: linear-gradient(180deg, var(--site-info-green-light) 0%, var(--site-info-green) 100%);
        color: #fff !important;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none !important;
        text-align: left;
        line-height: 1.35;
        box-shadow: 0 3px 10px rgba(76, 132, 53, 0.28);
        transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        min-height: 78px;
    }
    .site-info-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(76, 132, 53, 0.38);
        filter: brightness(1.06);
        color: #fff !important;
    }
    .site-info-btn:active {
        transform: translateY(-1px);
    }
    .site-info-btn .bi {
        font-size: 1.75rem;
        flex-shrink: 0;
        opacity: 0.95;
        line-height: 1;
    }
    .site-info-btn span {
        flex: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="site-info-hero mb-4">
        <h1 class="h3 fw-bold mb-2">Informações do site</h1>
        <p class="text-muted mb-0">Escolha uma seção e depois a página para editar o texto exibido nas páginas institucionais do rodapé. O editor é o mesmo das Perguntas frequentes (formatação visual, HTML no salvamento).</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if($pages->isEmpty())
        <div class="alert alert-warning rounded-3">Nenhuma página cadastrada. Execute <code>php artisan db:seed --class=SitePageSeeder</code>.</div>
    @else
        <div class="d-flex flex-column gap-4">
            @foreach(SitePage::categories() as $catKey => $catLabel)
                @php $catPages = $pagesByCategory->get($catKey, collect()); @endphp
                @if($catPages->isNotEmpty())
                    <div class="card site-info-card shadow-sm border-0 bg-white">
                        <div class="card-body">
                            <h2 class="site-info-card-title mb-0">{{ $catLabel }}</h2>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mt-2">
                                @foreach($catPages as $p)
                                    <div class="col">
                                        <a href="{{ route('admin.site-information.edit', $p) }}" class="site-info-btn w-100 h-100 d-flex">
                                            <i class="bi {{ SitePage::adminIconForSlug($p->slug) }}" aria-hidden="true"></i>
                                            <span>{{ $p->title }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
