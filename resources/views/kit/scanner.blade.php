@extends('layouts.app')

@section('title', 'Scanner de Paletes (Kits)')

@section('content')
<div class="container-fluid">
  <h4 class="mb-3">Scanner de Paletes — Produção de Kits</h4>

  <div class="card shadow-sm">
    <div class="card-body">
      <form id="form-scan" class="d-flex gap-2">
        @csrf
        <input type="text" id="palete_uid" class="form-control" placeholder="Bipe/cole o UID (ex.: KP-20250825-ABC123)" autofocus>
        <button class="btn btn-success" type="submit">Apontar</button>
      </form>

      <div id="retorno" class="mt-3"></div>

      <hr class="my-4">

      <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Dica: leitores a laser funcionam como teclado; basta mirar no código e pressionar Enter.</span>
        <a href="{{ route('kit.programar') }}" class="ms-auto btn btn-link">← Voltar à Programação</a>
      </div>
    </div>
  </div>
</div>

<script>
const form = document.getElementById('form-scan');
const inp  = document.getElementById('palete_uid');
const out  = document.getElementById('retorno');

form.addEventListener('submit', async (ev) => {
  ev.preventDefault();
  const uid = inp.value.trim();
  if(!uid){ return; }

  try{
    const resp = await fetch('{{ route('kits.apontar_por_etiqueta') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ palete_uid: uid })
    });

    const j = await resp.json();
    if(j.ok){
      out.innerHTML = `<div class="alert alert-success mb-0">✅ ${j.msg} • UID: ${uid}</div>`;
    }else{
      out.innerHTML = `<div class="alert alert-warning mb-0">⚠️ ${j.msg}</div>`;
    }
  }catch(e){
    out.innerHTML = `<div class="alert alert-danger mb-0">Erro de comunicação: ${e.message}</div>`;
  }
  inp.value = '';
  inp.focus();
});
</script>
@endsection
