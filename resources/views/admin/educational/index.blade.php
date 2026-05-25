@extends('admin.layout')

@section('title', 'Conteúdo educativo')
@section('page-title', 'Conteúdo educativo')
@section('body-class', 'admin-educational-hub')

@push('styles')
<style>
    body.admin-educational-hub .admin-header .page-title {
        color: #009739 !important;
        letter-spacing: -0.03em;
    }

    .edu-hub-wrap {
        max-width: 1100px;
        margin: 0 auto;
    }

    .edu-hub-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.08) 0%, rgba(0, 184, 74, 0.06) 50%, #f8fafc 100%);
        border: 1px solid rgba(0, 151, 57, 0.12);
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
    }

    .edu-hub-hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(180deg, #009739, #00b84a);
        border-radius: 1.25rem 0 0 1.25rem;
    }

    .edu-hub-eyebrow {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #009739;
        margin-bottom: 0.5rem;
    }

    .edu-hub-hero-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .edu-hub-hero-text {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.55;
        margin: 0;
        max-width: 52rem;
    }

    .edu-hub-card {
        position: relative;
        border: 1px solid rgba(15, 23, 42, 0.06) !important;
        border-radius: 1.15rem !important;
        background: #fff;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        overflow: hidden;
        height: 100%;
    }

    .edu-hub-card::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        opacity: 0;
        transition: opacity 0.22s ease;
    }

    .edu-hub-card--texts::after {
        background: linear-gradient(90deg, #0ea5e9, #0284c7);
    }

    .edu-hub-card--videos::after {
        background: linear-gradient(90deg, #009739, #00b84a);
    }

    .edu-hub-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.1) !important;
    }

    .edu-hub-card:hover::after {
        opacity: 1;
    }

    .edu-hub-card .card-body {
        padding: 1.75rem !important;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .edu-hub-icon-wrap {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .edu-hub-icon-wrap--texts {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(2, 132, 199, 0.1));
        color: #0284c7;
    }

    .edu-hub-icon-wrap--videos {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.18), rgba(0, 184, 74, 0.12));
        color: #009739;
    }

    .edu-hub-icon-wrap i {
        font-size: 1.65rem;
    }

    .edu-hub-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .edu-hub-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 2rem;
    }

    .edu-hub-badge--texts {
        background: rgba(14, 165, 233, 0.12);
        color: #0369a1;
    }

    .edu-hub-badge--videos {
        background: rgba(0, 151, 57, 0.12);
        color: #007a2f;
    }

    .edu-hub-desc {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        flex-grow: 1;
    }

    .edu-hub-btn {
        border-radius: 0.65rem;
        font-weight: 600;
        padding: 0.65rem 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .edu-hub-btn:hover {
        transform: translateY(-1px);
    }

    .edu-hub-btn--texts {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border: none;
        color: #fff;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
    }

    .edu-hub-btn--texts:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45);
    }

    .edu-hub-btn--videos {
        background: linear-gradient(135deg, #009739, #00b84a);
        border: none;
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 151, 57, 0.35);
    }

    .edu-hub-btn--videos:hover {
        color: #fff;
        box-shadow: 0 6px 20px rgba(0, 151, 57, 0.45);
    }
</style>
@endpush

@section('content')
<div class="edu-hub-wrap">
    <div class="edu-hub-hero" data-aos="fade-up">
        <p class="edu-hub-eyebrow mb-0">Material educativo</p>
        <h2 class="edu-hub-hero-title">Textos e vídeos para a área do cliente</h2>
        <p class="edu-hub-hero-text">
            Publique artigos, guias e vídeos alinhados a cada perfil de uso. Conteúdo claro reduz dúvidas e melhora a experiência na plataforma.
        </p>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="50">
            <div class="card edu-hub-card edu-hub-card--texts border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="edu-hub-icon-wrap edu-hub-icon-wrap--texts">
                            <i class="bi bi-journal-text" aria-hidden="true"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h3 class="edu-hub-card-title mb-0">Textos e artigos</h3>
                                <span class="edu-hub-badge edu-hub-badge--texts">{{ $textCount }} cadastrado(s)</span>
                            </div>
                        </div>
                    </div>
                    <p class="edu-hub-desc mb-4">
                        Crie conteúdos em texto (artigos, guias, FAQ, tutoriais), defina tipo e público por perfil ou “todos”.
                    </p>
                    <a href="{{ route('admin.educational.contents.index') }}" class="btn edu-hub-btn edu-hub-btn--texts w-100 mt-auto">
                        <i class="bi bi-list-ul" aria-hidden="true"></i>
                        Gerenciar textos
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="card edu-hub-card edu-hub-card--videos border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="edu-hub-icon-wrap edu-hub-icon-wrap--videos">
                            <i class="bi bi-play-btn-fill" aria-hidden="true"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h3 class="edu-hub-card-title mb-0">Vídeos</h3>
                                <span class="edu-hub-badge edu-hub-badge--videos">{{ $videoCount }} cadastrado(s)</span>
                            </div>
                        </div>
                    </div>
                    <p class="edu-hub-desc mb-4">
                        Cadastre URLs de vídeo (YouTube e outros) e o público-alvo por perfil de uso.
                    </p>
                    <a href="{{ route('admin.educational.videos.index') }}" class="btn edu-hub-btn edu-hub-btn--videos w-100 mt-auto">
                        <i class="bi bi-film" aria-hidden="true"></i>
                        Gerenciar vídeos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
