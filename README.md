# Systex WMS

## Warehouse Management System

O **Systex WMS** é um sistema completo de gestão logística e armazenagem, desenvolvido para apoiar operações reais de Centros de Distribuição, plantas industriais, áreas de recebimento, armazenagem, separação, inventário, conferência, produtividade e gestão operacional.

A solução foi estruturada para entregar controle de ponta a ponta sobre fluxos críticos do armazém, conectando dados operacionais, usuários, posições, materiais, saldos, movimentações, relatórios e indicadores em uma plataforma web robusta, com base preparada para API REST, autenticação por token, documentação técnica e evolução futura para mobile, PWA, coletores e operação offline.

## Objetivo do Projeto

O objetivo do Systex WMS é atender operações logísticas robustas com foco em produtividade, rastreabilidade, controle operacional e tomada de decisão. O sistema centraliza processos essenciais do armazém, reduz dependência de controles paralelos e fornece uma base tecnológica para acompanhar a evolução da operação com segurança, padronização e capacidade de auditoria.

O projeto considera ambientes reais de operação, onde estabilidade, clareza de fluxo, permissões, logs, relatórios e compatibilidade com infraestrutura limitada são fatores decisivos.

## Stack Tecnológica

- Laravel 11
- PHP
- Blade
- MySQL
- Sanctum
- Scribe
- JavaScript
- CSS
- API REST
- Possível integração com Flutter/PWA
- Suporte futuro para coletores e operação offline

## Módulos Principais

- Recebimento
- Armazenagem
- Separação
- Inventário
- Conferência
- Gestão de usuários
- Relatórios operacionais
- Dashboards
- Controle de produtividade
- Logs e auditoria
- Multiunidades
- Controle de permissões

## Arquitetura

O projeto segue uma arquitetura Laravel orientada por responsabilidades, combinando interface web administrativa, API REST, camada de domínio operacional, persistência em MySQL e documentação técnica.

- **Web**: rotas e telas administrativas em Blade para operação, gestão e acompanhamento.
- **API**: endpoints REST para integração com clientes externos, mobile, PWA e coletores.
- **Controllers**: entrada das requisições, coordenação de fluxo e retorno de respostas.
- **Models**: representação das entidades persistidas, relacionamentos Eloquent e acesso ao banco.
- **Services**: camada recomendada para regras de aplicação, coordenação de processos e redução de lógica em controllers.
- **Requests**: validação formal de entrada, padronizando regras por caso de uso.
- **Middleware**: autenticação, autorização, logs, sessão, proteção de rotas e filtros operacionais.
- **Relatórios**: geração de consultas, indicadores, exportações e documentos operacionais.
- **Logs**: rastreabilidade de ações, auditoria, erros e eventos relevantes.
- **Integração futura mobile**: base preparada para Flutter, PWA, coletores, tokens Sanctum, sincronização e operação offline quando aplicável.

## Padrão Visual Systex

O padrão visual da Systex prioriza uma interface SaaS profissional, tecnológica e operacional, adequada a ambientes logísticos de uso diário.

- Tema dark/glass como identidade principal.
- Preto e cinza como base visual.
- Branco para contraste, leitura e hierarquia.
- Vermelho apenas como cor de destaque, alerta ou ação crítica.
- Cards escuros com bordas sutis.
- Botões modernos, objetivos e consistentes.
- Layout limpo, produtivo e voltado à leitura rápida de dados operacionais.

## Fluxo Oficial de Desenvolvimento

Este projeto segue o **Systex AI Engineering Framework**, descrito em [AGENTS.md](AGENTS.md).

Todo novo desenvolvimento deve respeitar o fluxo oficial de agentes:

```text
ATLAS -> ATHENA -> PROMETEU -> GAIA -> VULCAN -> ARES -> APOLLO -> HERMES -> ORION -> HADES
```

Esse fluxo garante que cada demanda passe por entendimento, regra de negócio, arquitetura, banco de dados, estrutura Laravel, backend/API, frontend/admin, mobile/offline quando aplicável, testes e segurança.

## Como Rodar Localmente

Ambiente recomendado para Windows:

- Windows
- Laragon
- PHP compatível com Laravel 11
- Composer
- MySQL local
- Node.js e NPM

### 1. Clonar o projeto

```bash
git clone https://github.com/manoelfilhodev/wms.git
cd wms
```

### 2. Instalar dependências PHP

```bash
composer install
```

### 3. Configurar o ambiente

Crie o arquivo `.env` a partir do exemplo:

```bash
copy .env.example .env
```

Configure as variáveis principais:

```dotenv
APP_NAME="Systex WMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wms
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Gerar chave da aplicação

```bash
php artisan key:generate
```

### 5. Preparar banco de dados

Crie o banco no MySQL local pelo Laragon ou ferramenta equivalente e execute:

```bash
php artisan migrate
```

Quando houver seeders aplicáveis ao ambiente:

```bash
php artisan db:seed
```

### 6. Instalar dependências frontend

```bash
npm install
```

### 7. Gerar assets

```bash
npm run build
```

Para desenvolvimento com Vite:

```bash
npm run dev
```

### 8. Subir aplicação local

```bash
php artisan serve
```

Acesse:

```text
http://127.0.0.1:8000
```

## MVP Atual: Módulo Separação (Picking)

Este repositório está com foco no **MVP de Separação/Picking**.

### Escopo implementado

- Importação de DTs (base SAP) com itens.
- Filtro automático para DTs com sobra (picking).
- Bloqueio de SKUs não separáveis.
- Distribuição da DT por separador (nome livre).
- Finalização por separador dentro do modal (Parcial/Completa).
- Status operacional:
  - A separar
  - Separando
  - Separado parcial
  - Separado completo
- Dashboard Operacional Picking.
- Painel TV de Separação (visão geral + slides de detalhe).
- Tela base de Relatórios com roadmap dos modelos.

### Fluxo operacional (resumo)

1. ADM Sala importa as DTs.
2. Sistema filtra somente DTs com sobra para o ADM Operacional.
3. ADM Operacional distribui peças por separador na DT.
4. Cada separador é finalizado no modal (Parcial/Completa), com tempo individual.
5. Indicadores são atualizados no Dashboard/Painel TV.

### SKUs bloqueados

`1101, 1163, 1112, 1312, 22291, 22298, 22307, 22308, 21842, 40285, 22297`

### Rotas principais do módulo

- `/demandas/operacional`
- `/demandas/dashboard-operacional`
- `/demandas/relatorios`
- `/painel-tv`

### Checklist rápido de validação (in loco)

1. Importar base de DTs.
2. Confirmar lista apenas com DTs picking.
3. Abrir uma DT, distribuir para 2 separadores.
4. Finalizar separadores em horários diferentes.
5. Conferir tempo individual no modal.
6. Conferir status da DT e dashboard.
7. Abrir painel TV em tela cheia.

## API e Documentação Técnica

A API utiliza Sanctum para autenticação por token e Scribe para documentação técnica.

Base recomendada para endpoints versionados:

```text
/api/v1
```

Endpoints de referência:

- `POST /api/v1/auth/login`
- `GET /api/v1/me`
- `GET /api/v1/saldo-estoque`
- `GET /api/v1/saldo-estoque/{id}`
- `PUT/PATCH /api/v1/saldo-estoque/{id}`

Autenticação:

```http
Authorization: Bearer <token>
```

Gerar documentação Scribe quando necessário:

```bash
php artisan config:clear
php artisan scribe:generate
```

Rotas comuns de documentação:

- `/api/docs`
- `/api/docs.postman`
- `/api/docs.openapi`

## Deploy em Hospedagem Compartilhada

O projeto deve considerar cenários de deploy com acesso limitado a terminal, comuns em hospedagem compartilhada. Nesses ambientes, alguns comandos podem precisar ser executados localmente antes do envio dos arquivos, ou manualmente pelo painel da hospedagem.

Cuidados principais:

- Configurar `.env` de produção com credenciais reais e seguras.
- Nunca enviar `.env` local para produção.
- Garantir `APP_ENV=production` e `APP_DEBUG=false`.
- Confirmar versão do PHP compatível com Laravel 11.
- Apontar o document root para `public/` quando a hospedagem permitir.
- Ajustar permissões de `storage/` e `bootstrap/cache/`.
- Criar `storage link` manual quando `php artisan storage:link` não estiver disponível.
- Gerar assets com `npm run build` antes do deploy quando não houver Node.js no servidor.
- Limpar e recriar caches manualmente ou via Artisan quando houver terminal.
- Validar uploads, logs, sessões, rotas principais, login, API e permissões após a publicação.

Comandos úteis quando disponíveis:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan migrate --force
```

Em hospedagem compartilhada, qualquer migration em produção deve ter plano de rollback e validação prévia do impacto em dados.

## Padrões de Contribuição

Nenhuma feature deve começar sem validação mínima do fluxo Systex:

- Regra de negócio validada.
- Arquitetura validada.
- Impacto em banco de dados validado.
- Segurança revisada.
- Contratos de API avaliados quando houver integração.
- Impacto mobile/offline analisado quando aplicável.
- Testes e validações planejados antes da entrega.

Antes de finalizar alterações, executar as validações possíveis:

```bash
composer validate
php artisan route:list
php artisan test
npm run build
```

Quando `artisan` não estiver disponível, validar sintaxe dos arquivos PHP alterados:

```bash
php -l caminho/do/arquivo.php
```

## Documentação do Projeto

Documentos de governança e validação:

- [AGENTS.md](AGENTS.md)
- [docs/00-visao-geral.md](docs/00-visao-geral.md)
- [docs/02-arquitetura.md](docs/02-arquitetura.md)
- [docs/04-api.md](docs/04-api.md)
- [docs/06-seguranca.md](docs/06-seguranca.md)
- [docs/RELATORIO_VALIDACAO_AGENTES.md](docs/RELATORIO_VALIDACAO_AGENTES.md)

## Assinatura

**SYSTEX Sistemas Inteligentes**

Engineering + Logistics + Technology
