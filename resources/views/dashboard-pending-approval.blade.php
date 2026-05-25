@extends('layouts.app')

@section('title', 'Aguardando aprovação - Cota Brasilis')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="rounded-circle bg-warning bg-opacity-25 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-hourglass-half fa-4x text-warning"></i>
                        </div>
                    </div>
                    <h1 class="h4 fw-bold text-dark mb-3">Sua conta ainda não foi aprovada</h1>
                    <p class="text-muted mb-4 fs-6">
                        Sua conta ainda não foi aprovada pela equipe do Cota Brasilis. Aguarde: suas informações e documentos estão em processo de verificação.
                    </p>
                    <p class="text-muted small mb-0">
                        Assim que sua conta for aprovada, você poderá acessar todas as funcionalidades do painel. Em caso de dúvidas, entre em contato com a equipe.
                    </p>
                    <p class="text-muted small mt-3 mb-0" id="approvalPollHint" role="status">
                        <i class="fas fa-arrows-rotate me-1 text-success" aria-hidden="true"></i>Esta página verifica automaticamente se sua conta já foi analisada; não é necessário atualizar manualmente.
                    </p>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair da conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var url = @json(route('dashboard.approval-status'));
    var intervalMs = 12000;

    function checkApproval() {
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && (data.approved || data.rejected)) {
                    window.location.reload();
                }
            })
            .catch(function () {});
    }

    setInterval(checkApproval, intervalMs);
    setTimeout(checkApproval, 3000);
})();
</script>
@endpush
