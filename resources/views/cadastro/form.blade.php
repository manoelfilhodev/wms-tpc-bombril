@extends('layouts.auth')

@section('content')
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-lg-5">
                <div class="card">

                    <div class="card-header pt-1 pb-1 text-center bg-light">
                        <a href="{{ url('/') }}">
                            <span><img src="{{ asset('images/logo-sem-nome.png') }}" alt="" height="180"></span>
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <div class="text-center w-75 m-auto">
                            <h4 class="text-dark-50 text-center mt-0 fw-bold">Auto Cadastro</h4>
                            <small class="text-muted mb-4">Preencha os dados para criar seu acesso.</small><br>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('cadastro.salvar') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3 mt-2">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input class="form-control" type="text" name="nome" value="{{ old('nome') }}" required placeholder="Digite seu nome">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required placeholder="Digite seu e-mail">
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input class="form-control" type="password" name="senha" required placeholder="Digite sua senha">
                            </div>

                            <div class="mb-3">
                                <label for="senha_confirmation" class="form-label">Confirmar Senha</label>
                                <input class="form-control" type="password" name="senha_confirmation" required placeholder="Confirme sua senha">
                            </div>

                            <div class="mb-3 text-center">
                                <button class="btn btn-outline-primary w-100" type="submit">Cadastrar</button>
                            </div>
                        </form>
                    </div>

                    <div class="text-center mt-1">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/59/SAP_2011_logo.svg" alt="SAP S/4HANA Integration" style="max-width:50px;">
                        <p class="text-muted small mt-1">Compatível com integração via API SAP S/4HANA</p>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p class="text-muted">© {{ date('Y') }} SYSTEX Sistemas Inteligentes</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
