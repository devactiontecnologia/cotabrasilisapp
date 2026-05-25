@extends('admin.layout')

@section('title', 'Vídeos educativos')
@section('page-title', 'Vídeos educativos')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('admin.educational.index') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i>Voltar ao hub</a>
        <h2 class="h4 fw-bold mt-2 mb-0">Vídeos educativos</h2>
    </div>
    <a href="{{ route('admin.educational.videos.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Novo vídeo
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Título</th>
                    <th>URL</th>
                    <th>Público</th>
                    <th>Ordem</th>
                    <th>Ativo</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($videos as $video)
                    <tr>
                        <td class="fw-semibold">{{ $video->title }}</td>
                        <td><small class="text-break text-muted">{{ \Illuminate\Support\Str::limit($video->video_url, 40) }}</small></td>
                        <td>
                            @if($video->profile_type_required)
                                <span class="badge bg-info text-dark">{{ ucfirst($video->profile_type_required) }}</span>
                            @else
                                <span class="badge bg-success">Todos</span>
                            @endif
                        </td>
                        <td>{{ $video->order }}</td>
                        <td>
                            @if($video->is_active)
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                            @else
                                <span class="text-muted"><i class="bi bi-pause-circle"></i></span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.educational.videos.edit', $video) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form action="{{ route('admin.educational.videos.destroy', $video) }}" method="POST" class="d-inline" onsubmit="return confirm('Remover este vídeo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">Nenhum vídeo cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($videos->hasPages())
        <div class="card-footer">{{ $videos->links() }}</div>
    @endif
</div>
@endsection
