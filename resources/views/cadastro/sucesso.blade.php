@extends('layouts.auth')

@section('content')
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-lg-5">
                <div class="card">

                    <div class="card-header pt-1 pb-1 text-center bg-light">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('images/logo-sem-nome.png') }}" alt="" height="180">
                        </a>
                    </div>

                    <div class="card-body p-4 text-center">
                        <h4 class="text-success fw-bold mb-3">Cadastro realizado com sucesso!</h4>
                        <p class="text-muted">Aguarde liberação de acesso ou entre em contato com o administrador do sistema.</p>

                        <a href="{{ route('login') }}" class="btn btn-outline-primary mt-3 w-100">Ir para o Login</a>
                    </div>

                </div>

                <div class="text-center mt-3">
                    <p class="text-muted">© {{ date('Y') }} SYSTEX Sistemas Inteligentes</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
