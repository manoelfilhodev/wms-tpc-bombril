# Padrao Laravel

## Estrutura

- Seguir convencoes do Laravel 11.
- Usar controllers enxutos e services para coordenacao de regras.
- Usar Form Requests para validacao de entrada.
- Usar Resources para respostas de API.
- Usar Policies, Gates ou middlewares para autorizacao.

## Codigo

- Preferir injecao de dependencias e recursos nativos do framework.
- Evitar consultas duplicadas ou logica repetida em controllers.
- Tratar erros de forma consistente, sem vazar detalhes internos.
- Manter nomes claros para classes, metodos e rotas.

## Validacao

Antes de finalizar alteracoes PHP, executar validacoes possiveis:

- `composer validate`
- `php artisan route:list`
- `php artisan test`
- `php -l` nos arquivos alterados quando artisan nao estiver disponivel.
