@extends('layouts.tv')

@section('content')
<style>
  body {
    background-color: #1c1c1c;
    color: white;
    font-family: sans-serif;
  }

  .grid-containers {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    padding: 30px;
  }

  .container-card {
    background-color: #2a2a2a;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 0 10px #000;
    text-align: center;
  }

  .container-image {
    width: 100%;
    max-height: 100px;
    object-fit: contain;
    margin-bottom: 15px;
  }

  .progress-bar {
    height: 30px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #fff;
    margin-top: 5px;
  }

  .bar-fill {
    height: 100%;
    width: 0%;
    background-color: rgba(0, 128, 0, 0.4);
    font-weight: bold;
    font-size: 18px;
    color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.4s ease;
  }

  .title {
    font-size: 24px;
    margin-bottom: 10px;
    font-weight: bold;
  }
</style>

<div class="grid-containers">
  @foreach ($containers as $container)
    <div class="container-card">
      <div class="title">Contêiner {{ $container->codigo ?? $container->id }}</div>
      <img src="{{ asset('images/container_fechado_compacto.png') }}" class="container-image" alt="Contêiner">
      <div class="progress-bar">
        <div class="bar-fill" style="width: {{ $container->progresso }}%;
             background-color:
               {{ $container->progresso <= 25 ? 'rgba(255,0,0,0.4)' :
                  ($container->progresso <= 50 ? 'rgba(255,165,0,0.4)' :
                  ($container->progresso <= 75 ? 'rgba(255,255,0,0.4)' :
                   'rgba(0,128,0,0.4)') ) }};">
          {{ $container->progresso }}%
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection
