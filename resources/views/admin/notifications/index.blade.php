@extends('admin.layout')

@section('title', 'Notificações do Sistema')
@section('page-title', 'Notificações do Sistema')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Título</th>
                        <th>Mensagem</th>
                        <th>Tipo</th>
                        <th>Lida</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->id }}</td>
                        <td>{{ $notification->user->name }}</td>
                        <td>{{ $notification->title }}</td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $notification->message }}">
                                {{ Str::limit($notification->message, 50) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $notification->type === 'success' ? 'success' : ($notification->type === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($notification->type) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $notification->is_read ? 'success' : 'secondary' }}">
                                {{ $notification->is_read ? 'Sim' : 'Não' }}
                            </span>
                        </td>
                        <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-bell fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Nenhuma notificação encontrada</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($notifications->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection