@extends('admin.layout')

@section('title', 'Aprovação de perfis')
@section('page-title', 'Aprovação de perfis')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="bi bi-person-check me-2"></i>
        Aprovação de perfis
    </h2>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<ul class="nav nav-tabs mb-4" id="approvalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}" href="{{ route('admin.profile-approvals.index', ['tab' => 'pending']) }}" role="tab">
            <i class="bi bi-hourglass-split me-1"></i> Pendentes de aprovação
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $tab === 'approved' ? 'active' : '' }}" href="{{ route('admin.profile-approvals.index', ['tab' => 'approved']) }}" role="tab">
            <i class="bi bi-check-circle me-1"></i> Aprovados
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $tab === 'rejected' ? 'active' : '' }}" href="{{ route('admin.profile-approvals.index', ['tab' => 'rejected']) }}" role="tab">
            <i class="bi bi-x-circle me-1"></i> Reprovados
        </a>
    </li>
</ul>

<div class="admin-card" data-aos="fade-up">
    <div class="card-body">
        <div class="admin-table">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>WhatsApp</th>
                        <th>Perfil</th>
                        <th>Cadastrado em</th>
                        @if($tab === 'pending')
                            <th>Ações</th>
                        @elseif($tab === 'approved')
                            <th>Aprovado em</th>
                        @else
                            <th>Reprovado em</th>
                        @endif
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
                            @if($user->profile)
                                <span class="badge bg-secondary">{{ ucfirst($user->profile->profile_type ?? '-') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        @if($tab === 'pending')
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <form method="POST" action="{{ route('admin.profile-approvals.approve', $user) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Aprovar">
                                            <i class="bi bi-check-lg me-1"></i> Aprovar
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.profile-approvals.reject', $user) }}" class="d-inline" onsubmit="return confirm('Tem certeza que deseja reprovar este perfil?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Reprovar">
                                            <i class="bi bi-x-lg me-1"></i> Reprovar
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary" title="Ver detalhes">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        @elseif($tab === 'approved')
                            <td>{{ $user->profile_approved_at ? $user->profile_approved_at->format('d/m/Y H:i') : '-' }}</td>
                        @else
                            <td>{{ $user->profile_rejected_at ? $user->profile_rejected_at->format('d/m/Y H:i') : '-' }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $tab === 'pending' ? 8 : 7 }}" class="text-center text-muted py-5">
                            @if($tab === 'pending')
                                Nenhum perfil pendente de aprovação.
                            @elseif($tab === 'approved')
                                Nenhum perfil aprovado ainda.
                            @else
                                Nenhum perfil reprovado.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
