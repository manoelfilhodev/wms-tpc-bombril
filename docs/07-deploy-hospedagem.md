# Deploy e Hospedagem

Este documento registra cuidados para publicacao do projeto, especialmente em hospedagem compartilhada.

## Premissas

- O ambiente pode ter acesso limitado a terminal.
- O `public/` pode exigir configuracao manual do document root.
- Comandos artisan, filas, cron e symlinks podem depender do painel da hospedagem.
- Permissoes de `storage/` e `bootstrap/cache/` devem ser verificadas.

## Checklist de deploy

- Confirmar versao de PHP compativel com Laravel 11.
- Configurar `.env` sem expor credenciais.
- Executar migrations com plano de rollback.
- Validar cache de config, rotas e views quando aplicavel.
- Verificar escrita em `storage/` e `bootstrap/cache/`.
- Confirmar HTTPS e URLs de aplicacao.
- Testar login, rotas principais, API e uploads.

## Build frontend

Quando houver alteracao em assets, executar `npm run build` em ambiente adequado e publicar os artefatos esperados pelo Vite.

## Riscos comuns

- Caminho incorreto do document root.
- Falta de extensoes PHP.
- Permissoes insuficientes.
- Limite de memoria ou tempo de execucao.
- Rotinas agendadas nao configuradas.
