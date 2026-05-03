# Padrao de API

## REST

- Usar metodos HTTP coerentes: GET, POST, PUT/PATCH e DELETE.
- Retornar status codes adequados.
- Padronizar mensagens de erro e validacao.
- Versionar ou documentar quebras de contrato.

## Autenticacao

- Usar Sanctum para rotas autenticadas.
- Aplicar middlewares de permissao quando necessario.
- Nao retornar tokens ou dados sensiveis sem necessidade.

## Documentacao

- Atualizar Scribe para endpoints novos ou alterados.
- Incluir exemplos sem credenciais reais.
- Documentar parametros, filtros, paginacao e respostas de erro.
