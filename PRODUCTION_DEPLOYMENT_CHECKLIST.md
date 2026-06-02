# Production Deployment Checklist

Branch base: `homolog/integracao-seguranca`

Use este checklist antes de qualquer subida controlada para producao.

## Requisitos de runtime

- PHP **8.2+** obrigatorio.
- Composer compativel com o `composer.lock` do projeto.
- Node.js/NPM compativeis com o build Vite; recomendado Node 20.
- Extensoes PHP minimas: `mbstring`, `dom`, `fileinfo`, `pdo_mysql`, `openssl`, `json`, `ctype`, `tokenizer`, `xml`, `curl`.

## Variaveis obrigatorias de producao

Configure o `.env` real do servidor antes de cachear config:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:CHAVE_REAL_GERADA_POR_AMBIENTE
APP_URL=https://seu-dominio
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
API_DOCS_ENABLED=false
```

`APP_KEY` e obrigatoria e nao possui fallback no codigo. Gere uma chave real com:

```bash
php artisan key:generate --show
```

Nao reutilize chave de desenvolvimento/homologacao em producao.

## Permissoes de arquivos

Garanta permissao de escrita para o usuario do PHP/web server em:

```bash
storage/
bootstrap/cache/
```

Evite permissao ampla como `777` quando houver alternativa com dono/grupo correto.

## Ordem segura de deploy

1. Publicar codigo da branch aprovada.
2. Instalar dependencias PHP sem dev.
3. Instalar/buildar assets.
4. Configurar `.env` de producao.
5. Rodar migrations antes de caches.
6. Validar RBAC/admin.
7. Gerar caches de config, rotas e views.
8. Testar login, dashboard, rotas operacionais e headers.

## Validacao de pre-producao

Execute no servidor, ajustando conforme restricoes do ambiente:

```bash
php -v
composer validate
composer install --no-dev --optimize-autoloader
npm audit
npm run build
php artisan key:generate --show
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan test
```

Observacoes:

- `php artisan key:generate --show` apenas imprime uma chave; nao altera o `.env`.
- `php artisan migrate --force` altera banco e deve ser executado somente depois de backup e janela aprovada.
- Se o servidor nao tiver Node/NPM, gere `public/build` no pipeline e publique o artefato.

## RBAC/admin

Antes de liberar producao, garanta pelo menos um usuario com:

- role `admin`;
- permissao `admin.access`;
- status ativo.

Exemplo de verificacao via Tinker:

```bash
php artisan tinker
```

```php
use App\Models\User;

$user = User::where('email', 'admin@empresa.com')->first();
[
    'id' => $user?->id_user,
    'email' => $user?->email,
    'tipo' => $user?->tipo,
    'nivel' => $user?->nivel,
    'roles' => $user?->roles()->pluck('name')->all(),
    'admin_access' => $user?->hasPermission('admin.access'),
];
```

Resultado esperado:

```php
'roles' => ['admin']
'admin_access' => true
```

Se o usuario existir como admin legado, mas sem vinculo RBAC, rode a migration/seed de RBAC e valide novamente:

```bash
php artisan migrate --force
php artisan db:seed --class=RbacSeeder --force
php artisan optimize:clear
```

## Docs/API

Em producao:

```dotenv
API_DOCS_ENABLED=false
```

Status esperado:

- `/docs`: `404`
- `/docs.openapi`: `404`
- `/docs.postman`: `404`

Se houver necessidade temporaria de documentacao:

- habilitar somente em janela controlada;
- exigir login;
- exigir usuario com `admin.access`;
- desabilitar novamente apos uso.

## Pos-deploy

Validar manualmente:

- login web e Microsoft;
- acesso admin;
- demandas;
- recebimento;
- conferencia;
- kits;
- etiquetas;
- relatorios;
- expedicao;
- dashboards/graficos;
- headers de seguranca;
- logs de auditoria;
- bloqueio de upload perigoso.

## Criterio de liberacao

Producao deve ser liberada somente se:

- `APP_KEY` real estiver configurada;
- `APP_DEBUG=false`;
- HTTPS forcado;
- cookies seguros;
- migrations executadas;
- caches gerados sem erro;
- usuario admin validado;
- testes e build aprovados ou excecoes formalmente aceitas;
- riscos de dependencia/PHPStan documentados e aprovados.
