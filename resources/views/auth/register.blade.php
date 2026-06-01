@extends('layouts.auth')

@section('content')
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h4 class="text-dark text-center mt-0 fw-bold">Cadastro controlado</h4>

                            <form method="POST" action="{{ route('register') }}" class="mt-3">
                                @csrf

                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input id="nome" name="nome" class="form-control" value="{{ old('nome') }}" required>
                                    @error('nome') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required>
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="unidade_id" class="form-label">Unidade</label>
                                    <input id="unidade_id" name="unidade_id" type="number" class="form-control" value="{{ old('unidade_id') }}" required>
                                    @error('unidade_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input id="password" name="password" type="password" class="form-control" required>
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar senha</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                                </div>

                                <button class="btn btn-primary w-100" type="submit">Cadastrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
