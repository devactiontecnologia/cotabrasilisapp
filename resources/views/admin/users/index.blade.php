@extends('admin.layout')

@section('title', 'Gerenciar Usuários')
@section('page-title', 'Gerenciar Usuários')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">
        <i class="bi bi-people me-2"></i>
        Usuários
    </h2>
    <a href="{{ route('admin.users.create') }}" class="btn btn-admin btn-admin-primary">
        <i class="bi bi-plus-circle me-2"></i>
        Novo Usuário
    </a>
</div>

<div class="admin-card" data-aos="fade-up">
    <div class="card-body">
        <div class="admin-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>WhatsApp</th>
                        <th>Função</th>
                        <th>Status</th>
                        <th>Cotas</th>
                        <th>Transações</th>
                        <th>Cadastrado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->whatsapp ?? '-' }}</td>
                        <td>
                            <span class="badge-admin bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'moderator' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="badge-admin bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                @if($user->is_blocked)
                                <span class="badge-admin bg-warning">
                                    Bloqueado
                                </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge-admin bg-info">{{ $user->quotas_count }}</span>
                        </td>
                        <td>
                            <span class="badge-admin bg-primary">{{ $user->rental_transactions_count + $user->owned_transactions_count }}</span>
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}" 
                                            title="{{ $user->is_active ? 'Desativar' : 'Ativar' }}">
                                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_blocked ? 'success' : 'warning' }}" 
                                            title="{{ $user->is_blocked ? 'Desbloquear' : 'Bloquear' }}">
                                        <i class="bi bi-{{ $user->is_blocked ? 'unlock' : 'lock' }}"></i>
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Tem certeza que deseja remover este usuário?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="bi bi-people fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Nenhum usuário encontrado</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>
@endsection