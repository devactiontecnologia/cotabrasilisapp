@extends('layouts.app')

@section('title', 'Cadastro concluído')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-5 text-center">
                <h3 class="fw-bold mb-3">Cadastro concluído com sucesso</h3>
                <p class="mb-4">Obrigado por se cadastrar. Em alguns minutos você receberá um e-mail de confirmação (verifique a caixa de spam).</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Ir para login</a>
            </div>
        </div>
    </div>
</div>
@endsection

