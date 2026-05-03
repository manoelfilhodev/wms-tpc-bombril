# API

Este documento define a governanca inicial para APIs REST do projeto.

## Stack

- Laravel 11.
- Rotas em `routes/api.php` ou arquivos segmentados quando justificado.
- Sanctum para autenticacao.
- Scribe para documentacao.
- Resources para formatacao de respostas.

## Principios

- Nao modificar contratos existentes sem documentar impacto.
- Manter respostas previsiveis, com status HTTP coerentes.
- Validar entrada com Form Requests sempre que possivel.
- Evitar expor dados sensiveis, campos internos ou informacoes de infraestrutura.

## Contratos

Toda nova rota deve documentar:

- Metodo e endpoint.
- Autenticacao exigida.
- Permissoes ou perfis necessarios.
- Payload de entrada.
- Resposta de sucesso.
- Respostas de erro esperadas.
- Impacto em mobile, PWA, coletor ou integracoes externas.

## Scribe

Endpoints novos ou alterados devem receber anotacoes suficientes para gerar documentacao util, incluindo exemplos seguros e sem credenciais reais.
