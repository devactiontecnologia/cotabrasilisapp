@extends('layouts.app')

@section('title', 'Termos de Autorização - Cota Brasilis')

@section('content')
<section class="mb-5">
    <div class="p-5 p-lg-6 rounded-4 text-white" style="background: linear-gradient(135deg, rgba(17, 24, 39, 0.92), rgba(30, 64, 175, 0.85)); box-shadow: 0 26px 60px rgba(30, 64, 175, 0.35);">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
            <div>
                <span class="badge bg-light text-primary fw-semibold mb-3 px-3 py-2">
                    <i class="fas fa-file-signature me-2"></i>Termos de Autorização
                </span>
                <h1 class="display-6 fw-bold mb-3">Acompanhe as autorizações assinadas e pendentes</h1>
                <p class="lead mb-0" style="max-width: 640px;">
                    Visualize documentos, status de assinaturas e baixe comprovantes para manter o controle jurídico das suas operações com cotas hoteleiras.
                </p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn btn-light text-primary fw-semibold px-4 py-3 rounded-3">
                <i class="fas fa-folder-open me-2"></i>Ver transações
            </a>
        </div>
    </div>
</section>

@if(isset($platformAuthorizationDocuments) && $platformAuthorizationDocuments->isNotEmpty())
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4 p-lg-5">
        <h2 class="h4 fw-bold mb-2">Documentos padrão (Cota Brasilis)</h2>
        <p class="text-muted mb-4">Modelos e orientações fornecidos pela plataforma. Você pode visualizar ou baixar cada arquivo.</p>
        <div class="d-flex flex-column gap-4">
            @foreach($platformAuthorizationDocuments as $pad)
                <div class="row align-items-center g-3 border rounded-4 p-3 p-lg-4 bg-light bg-opacity-50">
                    <div class="col-lg-8">
                        <h3 class="h6 fw-bold mb-1">{{ $pad->title }}</h3>
                        @if($pad->file_path)
                            <p class="text-muted small mb-0">Documento oficial para consulta e download.</p>
                        @else
                            <p class="text-muted small mb-0">Arquivo ainda não foi publicado pela equipe Cota Brasilis.</p>
                        @endif
                    </div>
                    <div class="col-lg-4">
                        @if($url = $pad->publicAssetUrl())
                            <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2">
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success w-100">
                                    <i class="fas fa-external-link-alt me-2"></i>Visualizar
                                </a>
                                <a href="{{ $url }}" download="{{ $pad->suggestedDownloadFilename() }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-download me-2"></i>Download
                                </a>
                            </div>
                        @else
                            <span class="badge bg-secondary">Em breve</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5">
        <h2 class="h4 fw-bold mb-3">Seu termo no cadastro</h2>
        @if(!empty($hasTerm) && !empty($termUrl))
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-3 h5">Termo de Autorização de Hospedagem para Terceiros</h3>
                    <p class="text-muted mb-0">
                        Documento enviado por você no cadastro. Utilize os botões ao lado para abrir em nova aba ou salvar uma cópia no seu dispositivo.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2">
                        <a href="{{ $termUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-external-link-alt me-2"></i>Visualizar
                        </a>
                        <a href="{{ $termUrl }}" download="{{ $downloadName }}" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 88px; height: 88px; background: rgba(59, 130, 246, 0.12);">
                    <i class="fas fa-file-contract fa-2x text-primary"></i>
                </div>
                <h3 class="fw-bold mb-2 h5">Termo de hospedagem não encontrado</h3>
                <p class="text-muted mb-0" style="max-width: 560px; margin: 0 auto;">
                    O <strong>Termo de Autorização de Hospedagem para Terceiros</strong> enviado no seu cadastro aparecerá aqui quando estiver disponível. Se você cadastrou uma cota como proprietário ou gestor e anexou esse documento, verifique se o cadastro foi concluído ou atualize seus dados no perfil.
                </p>
            </div>
        @endif
    </div>
</div>

@if(
    (empty($hasTerm) || empty($termUrl))
    && (!isset($platformAuthorizationDocuments) || $platformAuthorizationDocuments->whereNotNull('file_path')->isEmpty())
)
<div class="card border-0 shadow-sm rounded-4 mb-5">
    <div class="card-body p-4 p-lg-5 text-center">
        <p class="text-muted mb-3 mb-lg-0">Quando houver documentos publicados ou termo anexado no cadastro, eles aparecerão nas seções acima.</p>
        <a href="{{ route('quotas.index') }}" class="btn btn-primary btn-lg px-4">
            <i class="fas fa-search me-2"></i>Explorar novas oportunidades
        </a>
    </div>
</div>
@endif
@endsection
