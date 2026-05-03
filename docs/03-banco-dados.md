# Banco de Dados

Este documento orienta evolucoes de schema, migrations, models e consultas MySQL.

## Principios

- Nao recriar tabelas existentes sem validacao de impacto.
- Nao alterar migrations criticas ja aplicadas em producao sem plano de migracao.
- Preferir novas migrations incrementais para alteracoes de schema.
- Preservar integridade referencial, historico operacional e rastreabilidade.

## Checklist para alteracoes

- Identificar tabelas, colunas, indices e relacionamentos afetados.
- Avaliar volume de dados e custo de migracao.
- Definir rollback realista.
- Verificar compatibilidade com hospedagem compartilhada.
- Atualizar models, factories, seeders e documentacao quando aplicavel.

## Padroes

- Usar nomes claros e consistentes com o dominio.
- Criar indices para chaves estrangeiras, buscas frequentes e filtros operacionais.
- Evitar campos sensiveis sem criptografia, mascaramento ou justificativa.
- Registrar impacto em API quando a estrutura de dados afetar contratos.
