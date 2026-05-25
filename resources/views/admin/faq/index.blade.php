@extends('admin.layout')

@section('title', 'Perguntas frequentes')
@section('page-title', 'Perguntas frequentes')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <p class="text-muted mb-0 small">Gerencie as perguntas exibidas em <a href="{{ route('faq') }}" target="_blank" rel="noopener">/perguntas-frequentes</a>.</p>
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Cadastrar nova
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:5rem">Ordem</th>
                    <th>Pergunta</th>
                    <th style="width:6rem">Ativa</th>
                    <th style="width:10rem" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faqs as $faq)
                    <tr>
                        <td class="text-muted">{{ $faq->sort_order }}</td>
                        <td class="fw-medium">{{ Str::limit($faq->question, 120) }}</td>
                        <td>
                            @if($faq->is_active)
                                <span class="badge text-bg-success">Sim</span>
                            @else
                                <span class="badge text-bg-secondary">Não</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="d-inline" onsubmit="return confirm('Remover esta pergunta?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">Nenhuma pergunta cadastrada. Clique em <strong>Cadastrar nova</strong>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($faqs->hasPages())
        <div class="card-body border-top">{{ $faqs->links() }}</div>
    @endif
</div>
@endsection
