# Seguranca

Este documento define controles minimos para seguranca, permissao e dados sensiveis.

## Principios

- Nunca hardcodar tokens, senhas, URLs sensiveis ou credenciais.
- Usar `.env`, arquivos de configuracao seguros ou constantes apropriadas.
- Validar autorizacao alem da autenticacao.
- Minimizar exposicao de dados em logs, respostas de API e telas.

## Sanctum e acesso

- Proteger rotas sensiveis com middleware adequado.
- Revisar escopos, tokens e expiracao quando houver consumo externo.
- Invalidar ou rotacionar tokens comprometidos.
- Nao exibir tokens completos em interfaces ou logs.

## Dados sensiveis

- Mascarar documentos, chaves, segredos e identificadores sensiveis quando necessario.
- Evitar salvar arquivos sensiveis em diretorios publicos.
- Revisar uploads, extensoes permitidas e tamanho maximo.

## Revisao obrigatoria

Alteracoes em login, permissao, tokens, upload, exportacao, relatorios ou dados pessoais devem passar por HADES.
