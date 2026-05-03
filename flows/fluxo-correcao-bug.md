# Fluxo de Correcao de Bug

## Diagnostico

- Reproduzir ou descrever claramente o erro.
- Identificar comportamento esperado e comportamento atual.
- Mapear arquivos, rotas, models, views ou queries envolvidos.

## Analise

- ATHENA valida se o bug envolve regra de negocio.
- PROMETEU avalia se a correcao afeta arquitetura.
- GAIA verifica impacto em dados ou migrations.
- HADES avalia risco de seguranca se houver permissao, token ou dado sensivel.

## Correcao

- Aplicar a menor alteracao suficiente.
- Evitar refatoracoes nao relacionadas.
- Preservar contratos de API e comportamento mobile.

## Validacao

- Executar testes relevantes.
- Rodar `php -l` em arquivos PHP alterados quando necessario.
- Registrar causa, correcao, risco residual e proximos passos.
