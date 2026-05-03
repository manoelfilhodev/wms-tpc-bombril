# Padrao de Banco de Dados

## Migrations

- Criar migrations incrementais.
- Nao editar migrations ja aplicadas em producao sem justificativa.
- Definir indices e constraints de forma explicita quando necessario.
- Planejar rollback antes de aplicar alteracoes criticas.

## Models

- Manter relacionamentos Eloquent claros.
- Declarar casts quando houver datas, booleanos, arrays ou valores monetarios.
- Evitar expor campos sensiveis por serializacao acidental.

## MySQL

- Avaliar impacto de queries em tabelas grandes.
- Evitar alteracoes bloqueantes sem janela operacional.
- Usar tipos de dados compativeis com o dominio.
