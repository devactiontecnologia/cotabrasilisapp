@extends('admin.layout')

@section('title', 'Detalhes da Taxa de Êxito')
@section('page-title', 'Detalhes da Taxa de Êxito')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-eye text-primary me-2"></i>
                    Taxa de Êxito #{{ $successFee->id }}
                </h5>
                <div>
                    <a href="{{ route('admin.success-fees.edit', $successFee) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil me-1"></i>
                        Editar
                    </a>
                    <a href="{{ route('admin.success-fees.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Tipo de Perfil</label>
                        <div>
                            <span class="badge bg-info fs-6">{{ $successFee->profile_type_name }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Número de Dias</label>
                        <div>
                            <strong class="fs-5">{{ $successFee->days }}</strong> dia(s)
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Valor da Taxa</label>
                        <div>
                            <strong class="text-success fs-4">{{ $successFee->formatted_fee }}</strong>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Ordem de Exibição</label>
                        <div>
                            <strong class="fs-5">{{ $successFee->order }}</strong>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            @if($successFee->is_active)
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Ativo
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6">
                                    <i class="bi bi-pause-circle me-1"></i>
                                    Inativo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Descrição</label>
                        <div class="p-3 bg-light rounded">
                            {{ $successFee->description ?? 'Sem descrição' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Criado em</label>
                        <div>
                            {{ $successFee->created_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Última atualização</label>
                        <div>
                            {{ $successFee->updated_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Ações
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.success-fees.edit', $successFee) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>
                        Editar Taxa
                    </a>
                    
                    <form action="{{ route('admin.success-fees.toggle-active', $successFee) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-{{ $successFee->is_active ? 'warning' : 'success' }} w-100">
                            <i class="bi bi-{{ $successFee->is_active ? 'pause' : 'play' }}-fill me-2"></i>
                            {{ $successFee->is_active ? 'Desativar' : 'Ativar' }} Taxa
                        </button>
                    </form>

                    <form action="{{ route('admin.success-fees.destroy', $successFee) }}" 
                          method="POST" 
                          onsubmit="return confirm('Tem certeza que deseja excluir esta taxa? Esta ação não pode ser desfeita.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-2"></i>
                            Excluir Taxa
                        </button>
                    </form>

                    <a href="{{ route('admin.success-fees.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar para Lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>ID:</strong> {{ $successFee->id }}
                </p>
                <p class="small mb-2">
                    <strong>Perfil:</strong> {{ $successFee->profile_type_name }}
                </p>
                <p class="small mb-2">
                    <strong>Dias:</strong> {{ $successFee->days }}
                </p>
                <p class="small mb-0">
                    <strong>Taxa:</strong> {{ $successFee->formatted_fee }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
