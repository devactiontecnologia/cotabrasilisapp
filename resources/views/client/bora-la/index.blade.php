@extends('layouts.app')

@section('title', 'Bora lá! Cota Brasilis')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-3">
                    <i class="fas fa-gift me-3"></i>Bora lá! Cota Brasilis
                </h1>
                <p class="lead text-muted">Comunicações e ofertas especiais para você</p>
            </div>
        </div>
    </div>

    <!-- Options Grid -->
    <div class="row g-4">
        <!-- Oferta Única -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-star fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Oferta Única</h5>
                    <p class="text-muted small mb-4">Ofertas especiais com data e horário de início e término</p>
                    <a href="{{ route('bora-la.oferta-unica') }}" class="btn btn-warning w-100">
                        <i class="fas fa-arrow-right me-2"></i>Acessar
                    </a>
                </div>
            </div>
        </div>

        <!-- Atualizações -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        <i class="fas fa-sync-alt fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Atualizações</h5>
                    <p class="text-muted small mb-4">Novos campos e filtros do aplicativo</p>
                    <a href="{{ route('bora-la.atualizacoes') }}" class="btn btn-primary w-100">
                        <i class="fas fa-arrow-right me-2"></i>Acessar
                    </a>
                </div>
            </div>
        </div>

        <!-- Avisos -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-bullhorn fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Avisos</h5>
                    <p class="text-muted small mb-4">Comunicações importantes do gestor</p>
                    <a href="{{ route('bora-la.avisos') }}" class="btn btn-success w-100">
                        <i class="fas fa-arrow-right me-2"></i>Acessar
                    </a>
                </div>
            </div>
        </div>

        <!-- Enquetes -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="fas fa-poll fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Enquetes</h5>
                    <p class="text-muted small mb-4">Pesquisas e consultas do gestor</p>
                    <a href="{{ route('bora-la.enquetes') }}" class="btn btn-purple w-100" style="background: #8b5cf6; border-color: #8b5cf6; color: white;">
                        <i class="fas fa-arrow-right me-2"></i>Acessar
                    </a>
                </div>
            </div>
        </div>

        <!-- Dicas -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-lightbulb fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Dicas</h5>
                    <p class="text-muted small mb-4">Dicas e orientações úteis</p>
                    <a href="{{ route('bora-la.dicas') }}" class="btn btn-warning w-100">
                        <i class="fas fa-arrow-right me-2"></i>Acessar
                    </a>
                </div>
            </div>
        </div>
    </div>

    @php
        $feedRecent = $feedRecent ?? collect();
    @endphp
    @if($feedRecent->isNotEmpty())
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4 text-center">Últimas publicações</h3>
            <div class="row g-3">
                @foreach($feedRecent as $item)
                    @php
                        $itemLink = match ($item->type) {
                            \App\Models\BoraLaPost::TYPE_OFERTA_UNICA => route('bora-la.oferta-unica'),
                            \App\Models\BoraLaPost::TYPE_ATUALIZACAO => route('bora-la.atualizacoes'),
                            \App\Models\BoraLaPost::TYPE_AVISO => route('bora-la.avisos'),
                            \App\Models\BoraLaPost::TYPE_ENQUETE => route('bora-la.enquetes'),
                            \App\Models\BoraLaPost::TYPE_DICA => route('bora-la.dicas'),
                            default => route('bora-la.index'),
                        };
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <span class="badge bg-secondary-subtle text-secondary mb-2">{{ \App\Models\BoraLaPost::TYPES[$item->type] ?? $item->type }}</span>
                                <h6 class="fw-bold">{{ \Illuminate\Support\Str::limit($item->title, 70) }}</h6>
                                <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($item->body ?? '')), 120) }}</p>
                                <a href="{{ $itemLink }}" class="btn btn-sm btn-outline-primary">Ver na seção</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
}
</style>
@endsection

