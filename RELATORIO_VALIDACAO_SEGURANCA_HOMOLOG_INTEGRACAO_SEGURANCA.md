# Relatorio de Validacao de Seguranca - homolog/integracao-seguranca

Data: 2026-06-02  
Branch: `homolog/integracao-seguranca`  
Escopo: Laravel 11, Web, API, Sanctum, Scribe, RBAC, DevSecOps, OWASP

## A. Resultado geral

**Aprovado com ressalvas para merge e homologacao.**  
**Nao aprovado para producao ate saneamento dos bloqueadores tecnicos abaixo.**

As correcoes criticas de seguranca da branch foram validadas: `APP_KEY` sem fallback inseguro, docs protegidos por flag e RBAC admin, APIs operacionais com `auth:sanctum`, rate limits ativos, headers de seguranca com CSP nonce, uploads perigosos bloqueados, auditoria e correlacao presentes.

## B. Score

- Seguranca: **8,0 / 10**
- Risco: **4,0 / 10**

O risco principal remanescente nao esta em controle de acesso de API, mas em qualidade/operacao: PHPStan ainda falha e ha rotas web legadas operacionais protegidas por middleware global de defesa, sem `auth` explicito em todas as definicoes.

## C. Bloqueadores para producao

1. `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` falhou com **113 erros**.
   - Principais areas: `DemandaController`, controllers/servicos de expedicao, exports e tipagem de collections.
   - Existe `phpstan-baseline.neon` e `PHPSTAN_BASELINE_REDUCTION_PLAN.md`, mas a execucao atual ainda falha.

2. `composer audit` falhou por advisory conhecido:
   - Pacote: `firebase/php-jwt`
   - CVE: `CVE-2025-45769`
   - Severidade: baixa
   - Status: risco aceito temporariamente em `SECURITY_RISK_ACCEPTANCE.md`.

3. Rotas web legadas operacionais ainda aparecem sem `auth` explicito no route definition.
   - Ha protecao global por `EnsureOperationalRouteIsAuthenticated`.
   - Para producao, recomenda-se migrar as rotas operacionais para grupos explicitos `auth`/permissao, reduzindo dependencia de padroes globais por path.

## D. Riscos remanescentes aceitos

- `firebase/php-jwt` CVE-2025-45769: aceito temporariamente por baixa severidade, dependencia transitive e controles mitigadores com Sanctum, rate limit e auditoria.
- `style-src 'unsafe-inline'`: presente somente para estilos. `script-src` nao usa `unsafe-inline` nem `unsafe-eval`; scripts inline recebem nonce.
- Rotas legadas web: protegidas por middleware global, mas com recomendacao de explicitar `auth` em cada grupo operacional.

## E. Arquivos alterados/corrigidos

- `.github/workflows/security.yml`
- `.github/workflows/gitleaks.yml`
- `.github/workflows/phpstan.yml`
- `.github/workflows/zap-baseline.yml`
- `.github/workflows/dependency-monitoring.yml`
- `config/app.php`
- `app/Http/Middleware/ApiDocsAccess.php`
- `app/Http/Middleware/SecurityHeaders.php`
- `app/Http/Controllers/DemandaController.php`
- `resources/views/dashboard/tv.blade.php`
- `resources/views/demandas/report_gerencial.blade.php`
- `resources/views/expedicao/dashboard.blade.php`
- `resources/views/relatorios/armazenagem.blade.php`
- `resources/views/relatorios/separacoes.blade.php`
- `RELATORIO_VALIDACAO_SEGURANCA_HOMOLOG_INTEGRACAO_SEGURANCA.md`

## F. Evidencias

### Comandos executados

- `php artisan route:list`
- `php artisan route:list --path=api -vv`
- `php artisan route:list --path=docs -vv`
- `composer validate`
- `composer audit`
- `npm audit`
- `npm run build`
- `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test`
- `APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test tests/Feature/SecurityOperationsTest.php tests/Feature/SecurityPhase2Test.php`
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
- `php artisan config:cache`
- `php artisan optimize:clear`
- `git diff --check`

### Saidas resumidas

- `composer validate`: aprovado.
- `npm audit`: aprovado, 0 vulnerabilidades.
- `npm run build`: aprovado, Vite build concluido.
- `php artisan test`: aprovado, **99 testes**, **401 assertions**.
- Testes focados de seguranca: aprovado, **17 testes**, **61 assertions**.
- `php artisan config:cache`: aprovado; compatibilidade confirmada.
- `git diff --check`: aprovado.
- `composer audit`: reprovado por `firebase/php-jwt` CVE-2025-45769, baixa severidade, risco aceito.
- `phpstan`: reprovado com 113 erros.

### Rotas publicas encontradas

Publicas justificadas:

- `GET /`
- `GET /up`
- `GET login`
- `POST login` com `throttle:login`
- `GET login/microsoft`
- `GET login/microsoft/callback`
- `POST api/login` com `throttle:login`
- `POST api/v1/auth/login` com `throttle:login`
- `POST api/login-microsoft` com `throttle:login`
- `POST api/auth/microsoft` com `throttle:login`
- `GET sanctum/csrf-cookie`
- `GET cadastro`, `POST cadastro`, `GET cadastro/sucesso` com token/convite
- `GET storage/{path}` conforme configuracao local

Rotas operacionais sem `auth` explicito no route definition, mitigadas pelo middleware global `EnsureOperationalRouteIsAuthenticated`:

- `kit*` e `kits*`
- `etiquetas*`
- `relatorios/producao`
- `setores/recebimento/*` em algumas rotas legadas
- `setores/conferencia/*` em algumas rotas legadas
- `separacoes*` em algumas rotas legadas
- `transferencias*`
- `inventario*`
- `contagem*`
- `painel-tv*`

### Rotas autenticadas/protegidas confirmadas

- `demandas*`: `auth`, `permission:demandas.view`, `permission:demandas.edit` e/ou `demanda.perfil:operacional`.
- `expedicao/apontamentos-operacionais`, `expedicao/timeline-dts`, `expedicao/saida-veiculos`: `auth` e `demanda.perfil:operacional`.
- `relatorios/separacoes`, `relatorios/armazenagem`: `auth`.
- `admin/logs`, `admin/security`: `auth` e `admin`.
- `docs`, `docs.openapi`, `docs.postman`: middleware `ApiDocsAccess`.

### API

`php artisan route:list --path=api -vv` confirmou:

- Endpoints operacionais com `auth:sanctum` e `throttle:api`.
- Endpoints criticos de demandas:
  - `PUT api/demandas/{id}`: `auth:sanctum`, `throttle:api`, `throttle:critical`, `permission:demandas.edit`.
  - `POST api/demandas/{id}/status`: `auth:sanctum`, `throttle:api`, `throttle:critical`, `permission:demandas.edit`.
- Login API/Microsoft publico somente para autenticacao, com `throttle:login`.

### Scribe / Docs

Middleware validado: `ApiDocsAccess`.

Status esperado:

- `API_DOCS_ENABLED=false`: `404`.
- `API_DOCS_ENABLED=true` sem login: `302` para `/login`.
- `API_DOCS_ENABLED=true` com usuario sem admin: `403`.
- `API_DOCS_ENABLED=true` com usuario admin: `200`.

Correcao aplicada: removido fallback que habilitava docs automaticamente em ambiente `local`; a flag `API_DOCS_ENABLED` passa a controlar o acesso.

### Configuracao de producao

Validado:

- `config/app.php`: `key => env('APP_KEY')`, sem fallback hardcoded.
- `APP_ENV`: fallback `production`.
- `APP_DEBUG`: fallback `false`.
- `FORCE_HTTPS`: fallback seguro em producao.
- `SESSION_SECURE_COOKIE`: fallback seguro em producao.
- `API_DOCS_ENABLED`: fallback `false`.
- `php artisan config:cache`: aprovado.

### GitHub Actions / CI

Workflows validados com triggers:

- `main`
- `security/**`
- `feature/**`
- `homolog/**`

Arquivos:

- `security.yml`
- `gitleaks.yml`
- `phpstan.yml`
- `zap-baseline.yml`
- `dependency-monitoring.yml`

### Secrets e arquivos sensiveis

- `.env` nao esta versionado.
- `.env.example` usa placeholders para Microsoft/OAuth e chaves externas.
- `.gitignore` cobre `.env`, caches, logs, temporarios, `vendor`, `node_modules`, build e arquivos locais.
- Gitleaks esta presente no CI.

### RBAC

Validado:

- Migration `2026_06_01_000001_create_rbac_tables.php`.
- Seeder `RbacSeeder`.
- `EnsureModulePermission`.
- `AdminMiddleware`.
- `DemandaPerfilMiddleware`.
- Testes confirmam:
  - sem login: 401/302 conforme web/API;
  - login sem permissao: 403;
  - login com permissao: 200.

### Auditoria e alertas

Validado:

- `audit_logs`.
- `security_alerts`.
- `correlation_id` e `request_id`.
- Registro de login com sucesso, login com falha, logout, 403, rate limit, upload bloqueado e mutacoes autenticadas.
- Sanitizacao de `password`, `token`, `access_token`, `refresh_token`, `cookie`, `secret`, `client_secret`, `remember_token` e `_token`.

### Security Headers / CSP

Validado:

- `Strict-Transport-Security` em producao/HTTPS.
- `X-Frame-Options`.
- `X-Content-Type-Options`.
- `Referrer-Policy`.
- `Permissions-Policy`.
- CSP com nonce.
- Ausencia de `unsafe-eval`.
- `unsafe-inline` existe apenas em `style-src`; justificativa: compatibilidade com estilos inline/Blade/Bootstrap. `script-src` permanece protegido por nonce.

### Upload hardening

Validado:

- Middleware `BlockMaliciousUploads`.
- Bloqueio de `php`, `phtml`, `phar`, `exe`, `bat`, `cmd`, `sh`, `ps1`, `com`, `scr`, `js`, `jar`.
- Validacoes locais de MIME/extensao/tamanho em endpoints de foto, XML, XLS/XLSX e importacoes.
- Upload bloqueado gera auditoria `blocked_malicious_upload`.

### Vulnerabilidades conhecidas

- `composer audit` aponta `firebase/php-jwt` CVE-2025-45769.
- `SECURITY_RISK_ACCEPTANCE.md` documenta aceitacao temporaria, mitigacoes e revisao mensal.

### Divida tecnica

- `phpstan-baseline.neon` presente.
- `PHPSTAN_BASELINE_REDUCTION_PLAN.md` presente.
- CI possui workflow PHPStan para `homolog/**`.
- Execucao atual do PHPStan falhou; deve ser tratada antes de producao.

## G. Recomendacoes finais

1. Antes de producao, corrigir os 113 erros do PHPStan sem ampliar baseline.
2. Manter acompanhamento mensal do `firebase/php-jwt` e atualizar/remover assim que houver caminho compativel.
3. Migrar rotas operacionais legadas para grupos explicitos `auth` e permissao por modulo.
4. Reduzir gradualmente `style-src 'unsafe-inline'` quando o frontend estiver pronto para nonce/hash de estilos.
5. Manter `API_DOCS_ENABLED=false` em producao por padrao e habilitar apenas em janela controlada para usuario admin.
6. Rodar ZAP baseline contra ambiente de homologacao antes do deploy produtivo.

## H. Veredito final

- Pode mergear? **Sim, com ressalvas.**
- Pode ir para homologacao? **Sim, com monitoramento e validacao funcional.**
- Pode ir para producao? **Nao ainda.**

Condicao minima para producao: PHPStan aprovado ou plano formal de excecao assinado, advisory revisado, e rotas operacionais legadas endurecidas com `auth` explicito por grupo/modulo.
