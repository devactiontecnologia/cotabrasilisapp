@extends('layouts.app')

@section('title', 'Ofertas Cota Brasilis - Cota Brasilis')

@section('content')
<section class="mb-5">
    <div class="p-5 p-lg-6 rounded-4 text-white" style="background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(17, 94, 67, 0.85)); box-shadow: 0 30px 70px rgba(5, 74, 40, 0.38);">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
            <div>
                <span class="badge bg-light text-success fw-semibold mb-3 px-3 py-2">
                    <i class="fas fa-gift me-2"></i>Ofertas Cota Brasilis
                </span>
                <h1 class="display-6 fw-bold mb-3">Curadoria especial com vantagens exclusivas</h1>
                <p class="lead mb-0" style="max-width: 640px;">
                    Descubra oportunidades selecionadas pela nossa equipe com condições diferenciadas, atendimento concierge e prazos flexíveis para negociação.
                </p>
            </div>
            <a href="{{ \Illuminate\Support\Facades\Route::has('public.quotas.featured') ? route('public.quotas.featured') : route('quotas.index') }}" class="btn btn-light text-success fw-semibold px-4 py-3 rounded-3">
                <i class="fas fa-star me-2"></i>Ver ofertas em destaque
            </a>
        </div>
    </div>
</section>

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(16, 185, 129, 0.12);">
                <i class="fas fa-gift fa-3x text-success"></i>
            </div>
            <h3 class="fw-bold mb-3">Nenhuma oferta personalizada disponível agora</h3>
            <p class="text-muted mb-4" style="max-width: 560px; margin: 0 auto;">
                Assim que tivermos novas oportunidades alinhadas ao seu perfil {{ auth()->user()->profile?->profile_type ?? 'cliente' }}, você receberá um aviso aqui e por e-mail.
            </p>
            <a href="{{ route('quotas.index') }}" class="btn btn-success btn-lg px-4">
                <i class="fas fa-compass me-2"></i>Explorar todas as cotas
            </a>
        </div>
    </div>
</div>
@endsection
