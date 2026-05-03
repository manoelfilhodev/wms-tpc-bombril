@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Registrar Produto</h4>

        <form id="meu-formulario" onsubmit="return false;">
            @csrf
            <div class="mb-3">
                <label for="produto" class="form-label">Produto</label>
                <input type="text" class="form-control" name="produto" id="produto" required>
            </div>

            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" name="quantidade" id="quantidade" required>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            
            <button id="btn-sync" class="btn btn-outline-primary mt-3">
                Forçar Sincronização
            </button>
        </form>

        <div id="mensagem" class="mt-3"></div>
    </div>
</div>
@endsection

@section('scripts')

<script>
    document.getElementById('btn-sync').addEventListener('click', async () => {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            const reg = await navigator.serviceWorker.ready;
            await reg.sync.register('sync-formularios');
            alert('🔄 Sincronização forçada enviada ao Service Worker!');
        } else {
            alert('❌ Seu navegador não suporta Background Sync.');
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('meu-formulario');
    const msg = document.getElementById('mensagem');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const dados = {
            produto: form.produto.value,
            quantidade: form.quantidade.value,
            criado_em: new Date().toISOString()
        };

        try {
            const res = await fetch('https://systex.com.br/wms/public/formulario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(dados)
            });

            if (!response.ok) throw new Error();

            msg.innerHTML = `<div class="alert alert-success">Enviado com sucesso!</div>`;
            form.reset();

        } catch (error) {
            const db = await openDB();
            const tx = db.transaction('formularios', 'readwrite');
            await tx.store.add(dados);
            await tx.done;

            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register('sync-formularios');
            }

            msg.innerHTML = `<div class="alert alert-warning">Você está offline. Dados salvos localmente e serão enviados depois.</div>`;
            form.reset();
        }
    });
});
</script>


@endsection
