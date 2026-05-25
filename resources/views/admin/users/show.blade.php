@extends('admin.layout')

@section('title', 'Detalhes do Usuário')
@section('page-title', 'Detalhes do Usuário')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- User Info Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-circle"></i>
                    {{ $user->name }}
                </h5>
                <div class="btn-group">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informações Pessoais</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nome:</strong></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>WhatsApp:</strong></td>
                                <td>{{ $user->whatsapp ?? 'Não informado' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Status e Permissões</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Função:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'moderator' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                        {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Bloqueado:</strong></td>
                                <td>
                                    @if($user->is_blocked)
                                        <span class="badge bg-warning">Sim</span>
                                    @else
                                        <span class="badge bg-success">Não</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Administrador:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $user->is_admin ? 'danger' : 'secondary' }}">
                                        {{ $user->is_admin ? 'Sim' : 'Não' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Datas</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Cadastrado em:</strong></td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Última atualização:</strong></td>
                                <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($user->blocked_until)
                            <tr>
                                <td><strong>Bloqueado até:</strong></td>
                                <td>{{ $user->blocked_until->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Estatísticas</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Cotas:</strong></td>
                                <td><span class="badge bg-info">{{ $user->quotas_count }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Locações:</strong></td>
                                <td><span class="badge bg-primary">{{ $user->rental_transactions_count }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Propriedades:</strong></td>
                                <td><span class="badge bg-success">{{ $user->owned_transactions_count }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Quotas -->
        @if($user->quotas->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calendar-check"></i>
                    Cotas do Usuário ({{ $user->quotas->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Hotel</th>
                                <th>Localização</th>
                                <th>Período</th>
                                <th>Status</th>
                                <th>Preço</th>
                                <th>Criada em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->quotas as $quota)
                            <tr>
                                <td>{{ $quota->hotel_name }}</td>
                                <td>{{ $quota->location }}</td>
                                <td>{{ $quota->start_date->format('d/m/Y') }} - {{ $quota->end_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $quota->status === 'available' ? 'success' : ($quota->status === 'rented' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($quota->status) }}
                                    </span>
                                </td>
                                <td>R$ {{ number_format($quota->rental_price, 2, ',', '.') }}</td>
                                <td>{{ $quota->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- User Transactions -->
        @if($user->rentalTransactions->count() > 0 || $user->ownedTransactions->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-arrow-left-right"></i>
                    Transações do Usuário
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Hotel</th>
                                <th>Período</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->rentalTransactions as $transaction)
                            <tr>
                                <td><span class="badge bg-primary">Locação</span></td>
                                <td>{{ $transaction->quota->hotel_name }}</td>
                                <td>{{ $transaction->quota->start_date->format('d/m/Y') }} - {{ $transaction->quota->end_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                                <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                            
                            @foreach($user->ownedTransactions as $transaction)
                            <tr>
                                <td><span class="badge bg-success">Propriedade</span></td>
                                <td>{{ $transaction->quota->hotel_name }}</td>
                                <td>{{ $transaction->quota->start_date->format('d/m/Y') }} - {{ $transaction->quota->end_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>R$ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                                <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightning"></i>
                    Ações Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-{{ $user->is_active ? 'danger' : 'success' }} w-100">
                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                            {{ $user->is_active ? 'Desativar Usuário' : 'Ativar Usuário' }}
                        </button>
                    </form>
                    
                    <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-{{ $user->is_blocked ? 'success' : 'warning' }} w-100">
                            <i class="bi bi-{{ $user->is_blocked ? 'unlock' : 'lock' }}"></i>
                            {{ $user->is_blocked ? 'Desbloquear Usuário' : 'Bloquear Usuário' }}
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning w-100">
                        <i class="bi bi-pencil"></i>
                        Editar Usuário
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        @if($user->profile)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-person-badge"></i>
                    Perfil do Usuário
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>{{ ucfirst($user->profile->profile_type) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pode Fracionar:</strong></td>
                        <td>
                            <span class="badge bg-{{ $user->profile->can_fraction ? 'success' : 'secondary' }}">
                                {{ $user->profile->can_fraction ? 'Sim' : 'Não' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Max Frações:</strong></td>
                        <td>{{ $user->profile->max_fractions }}</td>
                    </tr>
                    <tr>
                        <td><strong>Verificado:</strong></td>
                        <td>
                            <span class="badge bg-{{ $user->profile->is_verified ? 'success' : 'warning' }}">
                                {{ $user->profile->is_verified ? 'Sim' : 'Não' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent Notifications -->
        @if($user->notifications->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bell"></i>
                    Notificações Recentes
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($user->notifications->take(5) as $notification)
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $notification->title }}</h6>
                            <small>{{ $notification->created_at->format('d/m H:i') }}</small>
                        </div>
                        <p class="mb-1 small">{{ Str::limit($notification->message, 50) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection