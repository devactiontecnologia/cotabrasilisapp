@extends('admin.layout')

@section('title', 'Exportação e importação de dados')
@section('page-title', 'Exportação e importação de dados')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-2">Backup e restauração (CSV no ZIP)</h1>
        <p class="text-muted mb-0">
            A <strong>exportação</strong> gera um arquivo ZIP com um CSV (separador <code>;</code>, UTF-8) por tabela de negócio, mais um <code>manifest.json</code> com metadados.
            A <strong>importação</strong> espera o mesmo formato (pasta <code>csv/</code> dentro do ZIP). Antes de importar, faça backup do banco atual — a operação <span class="text-danger fw-semibold">apaga e recarrega</span> todas as tabelas listadas abaixo; tabelas sem CSV no arquivo ficam vazias.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-download me-2 text-success"></i>Exportar todos os dados</h2>
                    <p class="text-muted small mb-4">
                        Baixa um ZIP pronto para arquivo ou para importar em outro ambiente. Não inclui filas, cache, sessões nem histórico de migrações.
                    </p>
                    <form method="POST" action="{{ route('admin.data-exchange.export') }}">
                        @csrf
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-file-zip me-2"></i>Baixar ZIP com CSVs
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-danger border-opacity-25">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-danger"><i class="bi bi-upload me-2"></i>Importar (substituir dados)</h2>
                    <p class="text-muted small mb-3">
                        Envie um ZIP gerado por esta mesma tela (ou com a mesma estrutura: <code>csv/nome_tabela.csv</code>). Senhas de usuários permanecem como estavam no backup (hashes).
                    </p>
                    <form method="POST" action="{{ route('admin.data-exchange.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Arquivo ZIP</label>
                            <input type="file" name="archive" accept=".zip,application/zip" class="form-control @error('archive') is-invalid @enderror" required>
                            @error('archive')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Máx. 500 MB (ajuste <code>upload_max_filesize</code> no PHP se necessário).</div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input @error('confirm_replace') is-invalid @enderror" type="checkbox" name="confirm_replace" id="confirm_replace" value="1" required>
                            <label class="form-check-label small" for="confirm_replace">
                                Entendo que <strong>todas as tabelas abaixo serão esvaziadas</strong> e preenchidas conforme o ZIP; isso não pode ser desfeito pelo sistema.
                            </label>
                            @error('confirm_replace')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-database-exclamation me-2"></i>Importar ZIP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-4">
            <h2 class="h6 fw-bold mb-3">Tabelas incluídas na exportação / afetadas na importação</h2>
            <p class="small text-muted mb-3">Excluídas de propósito: <code>migrations</code>, <code>failed_jobs</code>, <code>jobs</code>, <code>job_batches</code>, <code>cache</code>, <code>cache_locks</code>, <code>password_reset_tokens</code>, <code>sessions</code>.</p>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-2">
                @foreach($tables as $t)
                    <div class="col"><code class="small">{{ $t }}</code></div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
