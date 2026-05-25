@extends('layouts.app')

@section('title', 'Perguntas Frequentes - Cota Brasilis')

@push('styles')
<style>
    .faq-accordion-cb .accordion-item {
        border: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .faq-accordion-cb .accordion-item:last-child { border-bottom: none; }
    .faq-accordion-cb .accordion-button {
        font-weight: 600;
        font-size: 1.05rem;
        padding: 1.15rem 1.25rem;
        background: #fff;
        color: #1a1a1a;
        box-shadow: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .faq-accordion-cb .accordion-button:not(.collapsed) {
        background: var(--primary-green, #009739);
        color: #fff;
        box-shadow: none;
    }
    .faq-accordion-cb .accordion-button:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 151, 57, 0.25);
        border-color: transparent;
    }
    .faq-accordion-cb .accordion-button::after {
        filter: brightness(0) saturate(100%);
        opacity: 0.6;
    }
    .faq-accordion-cb .accordion-button:not(.collapsed)::after {
        filter: brightness(0) invert(1);
        opacity: 1;
    }
    .faq-accordion-cb .accordion-body {
        background: #fafbfc;
        padding: 1.25rem 1.5rem 1.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.04);
    }
    /*
     * Padrão único para todo o conteúdo das respostas (HTML vindo da BD pode trazer font-size inline).
     * 2rem (~32px) é grande demais para texto corrido; usamos 1.125rem (18px) em todo o bloco.
     */
    .faq-accordion-cb .accordion-body.faq-answer-content {
        font-size: 1.125rem !important;
        line-height: 1.55 !important;
        font-weight: 400;
        color: #495057;
        margin: 0;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content * {
        font-size: inherit !important;
        line-height: inherit !important;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content strong,
    .faq-accordion-cb .accordion-body.faq-answer-content b {
        font-weight: 600 !important;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content p {
        margin-top: 0;
        margin-bottom: 0;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content p + p {
        margin-top: 0.5rem;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content ul,
    .faq-accordion-cb .accordion-body.faq-answer-content ol {
        margin: 0.35rem 0 0;
        padding-left: 1.25rem;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content li + li {
        margin-top: 0.2rem;
    }
    .faq-accordion-cb .accordion-body.faq-answer-content a {
        color: var(--primary-green, #009739);
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="faq-public-page py-5">
    <div class="container">
        <div class="mb-4">
            @include('partials.site-back-to-home')
        </div>
        <div class="text-center mb-5" data-aos="fade-up">
            <h1 class="display-5 fw-bold mb-2" style="color: var(--primary-green, #009739);">Cota Brasilis</h1>
            <p class="h5 text-muted mb-0">Perguntas e respostas de suporte ao cadastrado</p>
        </div>

        @if($faqs->isEmpty())
            <div class="alert alert-light border text-center py-5 rounded-4 shadow-sm">
                <p class="text-muted mb-0">Em breve publicaremos as perguntas frequentes. Volte mais tarde.</p>
            </div>
        @else
            <div class="accordion faq-accordion-cb shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
                @foreach($faqs as $faq)
                    @include('faq.partials.item', ['faq' => $faq])
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
