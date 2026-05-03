# Padrao de Seguranca

## Segredos

- Segredos devem ficar em `.env` ou cofres/configuracoes seguras.
- Nunca commitar credenciais reais.
- Rotacionar segredos expostos imediatamente.

## Entrada e saida

- Validar toda entrada externa.
- Escapar saidas em Blade usando recursos padrao.
- Sanitizar uploads e restringir extensoes.
- Evitar logs com payloads sensiveis.

## Permissoes

- Confirmar autorizacao em acoes administrativas.
- Aplicar menor privilegio.
- Revisar alteracoes em usuarios, perfis, tokens e exportacoes.
