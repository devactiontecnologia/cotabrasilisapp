@extends('admin.layout')

@section('title', 'Bora lá — publicações')
@section('page-title', 'Bora lá — publicações')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <p class="text-muted mb-0 small">
        Conteúdo exibido em <strong>Bora lá! Cota Brasilis</strong> e no <strong>dashboard</strong> do cotista.
    </p>
    <a href="{{ route('admin.bora-la-posts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nova publicação
    </a>
</div>

<form method="get" action="{{ route('admin.bora-la-posts.index') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label class="form-label small mb-0">Filtrar por tipo</label>
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Todos</option>
            @foreach(\App\Models\BoraLaPost::TYPES as $value => $label)
                <option value="{{ $value }}" {{ ($type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th style="width:7rem">Ordem</th>
                    <th style="width:7rem">Publicado</th>
                    <th style="width:11rem">Data</th>
                    <th style="width:10rem" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td><span class="badge text-bg-light text-dark">{{ \App\Models\BoraLaPost::TYPES[$post->type] ?? $post->type }}</span></td>
                        <td class="fw-medium">{{ \Illuminate\Support\Str::limit($post->title, 80) }}</td>
                        <td class="text-muted">{{ $post->sort_order }}</td>
                        <td>
                            @if($post->is_published)
                                <span class="badge text-bg-success">Sim</span>
                            @else
                                <span class="badge text-bg-secondary">Não</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $post->published_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.bora-la-posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('admin.bora-la-posts.destroy', $post) }}" class="d-inline" onsubmit="return confirm('Excluir esta publicação?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">Nenhuma publicação. Clique em <strong>Nova publicação</strong>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($posts->hasPages())
        <div class="card-body border-top">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
