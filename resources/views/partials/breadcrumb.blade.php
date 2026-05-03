@php
    $homeUrl = Auth::check() && Auth::user()->tipo === 'operador'
        ? route('painel.operador')
        : url('/dashboard');
@endphp

<div class="page-title-box">
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="{{ $homeUrl }}">Início</a></li>
            @foreach($items as $key => $value)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $value }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ url($key) }}">{{ $value }}</a></li>
                @endif
            @endforeach
        </ol>
    </div>
    <h4 class="page-title">{{ $title }}</h4>
</div>
