@extends('layouts.app')

@section('title', 'Offline')

@section('content')
<div class="text-center py-5">
    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="64">
    <h2 class="mt-4">🛑 Você está offline</h2>
    <p class="text-muted">Esta funcionalidade não está disponível no momento.<br>
    Verifique sua conexão com a internet e tente novamente.</p>
</div>
@endsection
