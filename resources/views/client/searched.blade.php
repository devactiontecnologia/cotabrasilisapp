@extends('layouts.app')

@section('title', 'Pesquisados - Cota Brasilis')

@section('content')
<section class="mb-5">
    <div class="p-5 p-lg-6 rounded-4 text-white" style="background: linear-gradient(135deg, rgba(14, 116, 144, 0.92), rgba(7, 89, 133, 0.85)); box-shadow: 0 26px 60px rgba(15, 118, 128, 0.32);">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
            <div>
                <span class="badge bg-light text-primary fw-semibold mb-3 px-3 py-2">
                    <i class="fas fa-search me-2"></i>Pesquisados
                </span>
                <h1 class="display-6 fw-bold mb-3">Suas buscas favoritas sempre à mão</h1>
                <p class="lead mb-0" style="max-width: 620px;">
                    Salve filtros estratégicos e receba sugestões personalizadas conforme o destino, período e perfil de viagem que você procura.
                </p>
            </div>
            <a href="{{ route('quotas.index') }}" class="btn btn-light text-primary fw-semibold px-4 py-3 rounded-3">
                <i class="fas fa-plus me-2"></i>Criar nova pesquisa
            </a>
        </div>
    </div>
</section>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-lg-5">
        <div class="text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 110px; height: 110px; background: rgba(59, 130, 246, 0.1);">
                <i class="fas fa-search-location fa-3x text-primary"></i>
            </div>
            <h3 class="fw-bold mb-3">Nenhuma pesquisa salva por enquanto</h3>
            <p class="text-muted mb-4" style="max-width: 540px; margin: 0 auto;">
                Ao realizar buscas com filtros relevantes, salve-as com um nome fácil para reutilizar quando quiser e receber alertas assim que surgirem novas oportunidades.
            </p>
            <a href="{{ route('quotas.index') }}" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-search me-2"></i>Iniciar minha pesquisa
            </a>
        </div>
    </div>
</div>
@endsection

