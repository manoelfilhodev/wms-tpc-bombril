# Regras de Negocio

Este documento registra regras funcionais que orientam alteracoes no sistema. Ele deve ser atualizado quando uma decisao de negocio for descoberta, confirmada ou alterada.

## Principios

- Nenhuma regra existente deve ser alterada sem validacao explicita.
- Processos de estoque, recebimento, separacao, inventario e transferencia devem preservar rastreabilidade.
- Ajustes que afetem saldo, posicao, material, unidade ou pedido exigem revisao de impacto por ATHENA e GAIA.
- Operacoes offline, mobile ou por coletor devem manter reconciliacao segura quando aplicavel.

## Registro de regras

Cada nova regra deve conter:

- Nome da regra.
- Modulo afetado.
- Descricao objetiva.
- Excecoes conhecidas.
- Impacto em API, telas, banco de dados e relatorios.
- Evidencia de validacao com o responsavel de negocio.

## Cuidados operacionais

- Evitar atalhos que gerem divergencia entre interface administrativa e API.
- Nao remover validacoes de processo sem entender efeitos em estoque e auditoria.
- Preservar logs, historicos e referencias que ajudem a recompor eventos operacionais.
