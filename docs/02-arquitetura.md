# Arquitetura

Este documento define a direcao arquitetural inicial para evolucao do projeto no padrao Systex.

## Stack base

- Laravel 11 como framework principal.
- PHP para backend, services, requests, policies e jobs.
- Blade para frontend administrativo.
- MySQL como banco relacional.
- Sanctum para autenticacao de API.
- Scribe para documentacao de API.
- JavaScript e CSS para interacoes administrativas.

## Diretrizes

- Priorizar padroes nativos do Laravel antes de criar abstracoes proprias.
- Separar responsabilidades entre controllers, form requests, services, models, resources e policies.
- Evitar logica de negocio extensa diretamente em views ou controllers.
- Manter contratos de API versionaveis quando houver consumo mobile ou externo.
- Considerar hospedagem compartilhada: comandos artisan, filas, cron e storage links podem ter limitacoes.

## Organizacao esperada

- Controllers tratam entrada, autorizacao inicial e resposta.
- Form Requests concentram validacao.
- Services concentram regras de aplicacao e coordenacao de operacoes.
- Models representam entidades e relacionamentos Eloquent.
- Resources padronizam respostas de API.
- Policies e middlewares controlam acesso.

## Decisoes arquiteturais

Toda mudanca estrutural deve registrar motivacao, alternativas consideradas, impacto em deploy e plano de regressao.
