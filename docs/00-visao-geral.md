# Visao Geral do Projeto

Este repositorio segue o Systex AI Engineering Framework para organizar demandas, decisoes tecnicas e entregas em um fluxo auditavel de agentes.

## Contexto

O projeto e uma aplicacao Laravel 11 voltada a operacoes WMS, com interface administrativa em Blade, API REST, autenticacao via Sanctum, documentacao de API com Scribe e persistencia em MySQL. O ambiente de deploy pode incluir hospedagem compartilhada, portanto as decisoes tecnicas devem considerar restricoes de terminal, permissoes de arquivos e compatibilidade operacional.

## Objetivos de governanca

- Centralizar regras, arquitetura, dados, API, seguranca e deploy em documentos versionados.
- Evitar alteracoes funcionais sem avaliacao previa de negocio, arquitetura e dados.
- Manter rastreabilidade entre demanda, implementacao, testes e riscos.
- Preservar compatibilidade com integracoes mobile, PWA, coletores e operacao offline quando aplicavel.

## Agentes principais

- ATLAS coordena a demanda e divide etapas.
- ATHENA valida regras de negocio.
- PROMETEU valida arquitetura e limites tecnicos.
- GAIA avalia impacto em banco de dados.
- VULCAN garante estrutura Laravel e padroes de base.
- ARES implementa backend e API.
- APOLLO implementa frontend/admin.
- HERMES valida integracoes mobile.
- ORION valida testes e regressao.
- HADES revisa seguranca.

## Criterio de qualidade

Toda entrega deve deixar claro o agente responsavel, arquivos alterados, o que foi feito, riscos, comandos executados e proximos passos recomendados.
