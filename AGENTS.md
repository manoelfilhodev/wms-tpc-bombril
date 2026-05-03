# Systex AI Engineering Framework — Project Instructions

Este projeto deve seguir o fluxo oficial de agentes da Systex.

## Agentes

| Agente | Função |
|---|---|
| 👑 ATLAS | Orquestração e direção do projeto |
| 🧠 ATHENA | Regras de negócio |
| 🏗️ PROMETEU | Arquitetura |
| 🗄️ GAIA | Banco de dados |
| ⚙️ VULCAN | Estrutura base |
| 🔴 ARES | Backend/API |
| 🎨 APOLLO | Frontend/Admin |
| 📱 HERMES | Mobile/Flutter |
| 🧪 ORION | Testes |
| 🛡️ HADES | Segurança |

## Fluxo obrigatório

Todo novo desenvolvimento neste projeto deve seguir:

1. ATLAS → entender a demanda e dividir em etapas
2. ATHENA → validar regras de negócio
3. PROMETEU → validar arquitetura
4. GAIA → avaliar impacto em banco de dados, migrations, models e API
5. VULCAN → garantir estrutura base e padrões do Laravel
6. ARES → implementar backend, controllers, services, requests e API
7. APOLLO → implementar frontend/admin em Blade, componentes e layout Systex
8. HERMES → implementar ou validar integrações mobile/Flutter quando aplicável
9. ORION → validar testes, build e regressões
10. HADES → revisar segurança, permissões, tokens e dados sensíveis

## Regras críticas

- Não iniciar código antes de entender regra de negócio, arquitetura e impacto em dados.
- Não alterar estrutura de pastas sem justificar.
- Não modificar contratos de API sem documentar impacto.
- Não recriar tabelas existentes sem validação.
- Não alterar migrations críticas sem avaliar impacto em produção.
- Não remover suporte offline/mobile sem validação.
- Não hardcodar tokens, senhas, URLs sensíveis ou credenciais.
- Usar `.env`, arquivos de configuração seguros ou constantes apropriadas.
- Considerar que o deploy pode ocorrer em hospedagem compartilhada, com acesso limitado a terminal.
- Antes de finalizar, rodar validações possíveis:
  - `composer validate`
  - `php artisan route:list` quando disponível
  - `php artisan test` quando houver testes aplicáveis
  - `npm run build` quando houver alteração no frontend
  - `php -l` nos arquivos PHP alterados quando artisan não estiver disponível

## Stack

- Laravel 11
- PHP
- Blade
- MySQL
- API REST
- Sanctum
- Scribe
- JavaScript/CSS
- Possível integração Flutter/PWA/coletor
- Possível operação offline/local storage no mobile

## Padrão visual Systex

- Visual SaaS profissional.
- Tema dark/glass.
- Base em preto, cinza e branco.
- Vermelho apenas como cor de destaque.
- Evitar excesso de vermelho.
- Cards escuros, bordas sutis e botões modernos.
- Interface limpa, tecnológica e operacional.

## Padrão de resposta do Codex

Ao executar qualquer tarefa, responder sempre com:

1. Agente responsável pela etapa
2. Arquivos alterados
3. O que foi feito
4. Riscos ou impactos
5. Comandos executados
6. Próximos passos recomendados
