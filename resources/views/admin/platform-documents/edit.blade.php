@extends('admin.layout')

@section('title', 'Documentos padrão (termos)')
@section('page-title', 'Documentos padrão (termos)')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Voltar ao painel
        </a>
        <h1 class="h3 fw-bold mt-2 mb-2">Documentos e autorizações — modelos Cota Brasilis</h1>
        <p class="text-muted mb-0">
            Envie um arquivo padrão (PDF, JPG ou PNG) para cada termo. Os arquivos ficam disponíveis para todos os usuários em
            <strong>Termos de autorização</strong> e na seção <strong>Documentos e autorizações</strong> do perfil.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.platform-documents.update') }}" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-4">
        @csrf
        @method('PUT')

        <div class="card-body p-4 p-lg-5">
            @foreach($documents as $doc)
                <div class="mb-4 pb-4 border-bottom">
                    <h2 class="h6 fw-bold text-dark mb-2">{{ $doc->title }}</h2>
                    <p class="small text-muted mb-2">Identificador interno: <code>{{ $doc->slug }}</code></p>
                    @if($doc->file_path)
                        <p class="small mb-2">
                            <span class="text-success"><i class="bi bi-check-circle me-1"></i>Arquivo publicado.</span>
                            <a href="{{ $doc->publicAssetUrl() }}" target="_blank" rel="noopener" class="ms-2">Abrir arquivo atual</a>
                        </p>
                    @else
                        <p class="small text-warning mb-2"><i class="bi bi-exclamation-circle me-1"></i>Nenhum arquivo enviado ainda.</p>
                    @endif
                    <label class="form-label fw-semibold small">Substituir ou enviar arquivo</label>
                    <input type="file"
                           name="documents[{{ $doc->slug }}]"
                           id="doc_{{ $doc->slug }}"
                           class="form-control @error('documents.' . $doc->slug) is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png">
                    @error('documents.' . $doc->slug)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach

            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-cloud-upload me-2"></i>Salvar alterações
            </button>
        </div>
    </form>
</div>
@endsection
