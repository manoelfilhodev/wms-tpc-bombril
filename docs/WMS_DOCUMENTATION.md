# 🏭 Systex WMS — Documentação Técnica

## 📘 Visão Geral

Systex WMS é um **sistema de gerenciamento de estoque e logística** (Warehouse Management System) composto por:

- **Backend e Frontend Web:** Desenvolvido em **Laravel PHP**, utilizando o tema **Hyper Bootstrap** para a interface administrativa.
- **Aplicativo Mobile:** Desenvolvido em **Flutter**, voltado para operadores e gestão de campo.
- **API RESTful:** Fornecida pelo Laravel, consumida tanto pelo painel web quanto pelo app mobile.

A solução foi projetada para integrar os fluxos de **recebimento, estoque, expedição e rastreabilidade** de forma centralizada e segura.

---

## 🧱 Arquitetura do Sistema

O sistema é dividido em três camadas principais:

| Camada | Repositório | Função |
|--------|--------------|--------|
| **wms (Laravel)** | https://github.com/manoelfilhodev/wms | API REST + Painel Administrativo |
| **wms_app (Flutter)** | https://github.com/manoelfilhodev/wms_app | Aplicativo Mobile (Operacional) |
| **Database** | MySQL | Armazena dados operacionais e de usuários |

O Laravel fornece API REST para o Flutter via `routes/api.php`, enquanto mantém o painel administrativo via `routes/web.php`.

---

## ⚙️ Tecnologias Utilizadas

### Backend / Web
- Laravel 10 +
- PHP 8.2 +
- MySQL 8
- Bootstrap 5 (Hyper Theme)
- Laravel Sanctum (autenticação)
- Git + Git LFS

### Mobile
- Flutter 3 +
- Dart >= 3 .0
- Dio (HTTP Client)
- Provider / Riverpod (Gerência de estado)
- SharedPreferences / Hive (armazenamento local)

---

## 🗂️ Estrutura de Pastas

### Laravel (Backend / Web)
wms/
├── app/                   # Lógica de negócio e Controllers
├── bootstrap/             # Inicialização e autoload
├── config/                # Configurações de ambiente
├── database/              # Migrations, Seeders, Factories
├── microsoft/             # Integração com Microsoft Login
├── public/                # Assets públicos e downloads
├── resources/
│   ├── views/             # Páginas Blade (tema Hyper)
│   └── js, sass, assets/  # Recursos de front-end
├── routes/
│   ├── web.php            # Rotas do painel web
│   └── api.php            # Endpoints para o Flutter
└── storage/               # Logs, cache e uploads

### Flutter (Mobile)
wms_app/
├── lib/
│   ├── core/              # Core do app (config e temas)
│   ├── modules/           # Módulos funcionais
│   ├── utils/             # Funções e helpers
│   └── main.dart          # Ponto de entrada
├── assets/                # Ícones, imagens, fontes
└── pubspec.yaml           # Dependências e metadata

---

## 🌐 Fluxo de Autenticação

Laravel usa **Sanctum** para autenticação mista — sessões web e tokens API.

### Login no Laravel (Web)
- Usuário autentica via formulário Blade.
- Sessão gerenciada por cookies.

### Login no Flutter (Mobile)
- App envia POST para `/api/login`.
- Laravel retorna token.
- App armazena token e envia Bearer em headers subsequentes.

### Exemplo de Request (Flutter)
final response = await Dio().post(
  '$baseUrl/login',
  data: {'email': email, 'password': password},
);
final token = response.data['token'];

### Rotas protegidas Laravel
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/recebimentos', [RecebimentoController::class, 'index']);
    Route::post('/expedicoes', [ExpedicaoController::class, 'store']);
});

---

## 🚚 Principais Módulos

| Módulo | Descrição | Responsável |
|:--------|:------------|:--------------|
| **Autenticação** | Login / logout, controle de acesso e tokens API | Laravel + Flutter |
| **Dashboard Web** | Indicadores em tempo real (Hyper Bootstrap) | Laravel |
| **Recebimentos / Conferência** | Registro e conferência de materiais | Flutter |
| **Expedições** | Saída de produtos, ordens, QR Code | Flutter + Laravel |
| **Administração** | Usuários, permissões, logs e configurações | Laravel |

---

## 🧩 API Inicial: Endpoints Base

| Endpoint | Método | Descrição |
|-----------|--------|-----------|
| `/api/login` | POST | Autenticação de usuário e geração de token |
| `/api/logout` | POST | Revoga o token ativo |
| `/api/dashboard` | GET | Indicadores e status do operador |
| `/api/recebimentos` | GET / POST | Consulta e registro de recebimentos |
| `/api/expedicoes` | GET / POST | Consulta e atualização de expedições |
| `/api/usuarios/me` | GET | Retorna dados do usuário autenticado |

### Exemplo de resposta
{
  "status": "success",
  "user": {
    "id": 1,
    "name": "Operador WMS",
    "email": "operador@systex.com"
  },
  "token": "abc123xyz"
}

---

## 🧠 Configuração de Ambiente

### `.env` (Laravel)
APP_NAME=Systex WMS
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=https://systex.com.br/wms

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=systex
DB_USERNAME=usuario
DB_PASSWORD=senha

SANCTUM_STATEFUL_DOMAINS=systex.com.br,localhost
SESSION_DOMAIN=.systex.com.br

### Flutter `.env` (ou config constants)
const String baseUrl = "https://systex.com.br/wms/api";

---

## 🧪 Instalação e Deploy

### Laravel
git clone https://github.com/manoelfilhodev/wms.git
cd wms
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve

### Flutter
git clone https://github.com/manoelfilhodev/wms_app.git
cd wms_app
flutter pub get
flutter run

---

## 🔧 Manutenção e Build

### Limpeza de cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

### Build Flutter App
flutter build apk --release

Os APKs podem ser armazenados em:
wms/public/app-download/

---

## 📘 Diretrizes de Contribuição (CONTRIBUTING)

### 🧰 Requisitos
- PHP 8.2 +
- Composer 2 +
- Node / NPM (Laravel Mix ou Vite)
- Flutter 3 +
- Git + SSH configurado (com Git LFS)

### 🧑‍💻 Fluxo de trabalho
1. Crie um fork do repositório.  
2. Crie uma branch nova:
   git checkout -b feature/nova-funcionalidade
3. Faça commits claros:
   git commit -m "feat: adiciona módulo de inventário"
4. Envie para o fork:
   git push origin feature/nova-funcionalidade
5. Abra um Pull Request detalhado para `main`.

### 🧼 Boas práticas
- Nunca commitar `.env` ou chaves.  
- Utilizar `.gitignore` ajustado (excluindo `vendor/`, `node_modules/`, `storage/`).  
- Manter o código seguindo **PSR‑12** (PHP) e **Effective Dart**.  
- Usar commits semânticos (`feat`, `fix`, `chore`, `docs`, `refactor`).

---

## 🗺️ Próximos Passos

- Implementar documentação Swagger (OpenAPI) dos endpoints.
- Configurar CI/CD via GitHub Actions (build + deploy).
- Integrar logs centralizados (Laravel Log + Sentry).
- Adicionar controle de permissões por perfil (Role / Permission).
- Expandir dashboard com métricas em tempo real.

---

## 🧾 Autoria

**Sistema:** Systex WMS  
**Backend:** Laravel + Hyper Bootstrap  
**App Mobile:** Flutter  
**Repositórios:**  
- https://github.com/manoelfilhodev/wms  
- https://github.com/manoelfilhodev/wms_app

