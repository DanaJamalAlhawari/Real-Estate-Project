# Makan | Real Estate Platform

Makan is a Laravel-based real estate platform for discovering rental properties, requesting bookings, and communicating with property owners. It supports Arabic and English, interactive property maps, real-time messaging, and local AI-powered assistance and chat moderation using Ollama.

## What you can do

- **Tenants:** Discover and save properties, request bookings, message property owners, and use mock checkout after a booking is accepted.
- **Agents:** Create and manage listings with property area, bilingual details, cover photos and image galleries, select a location on the map, submit ownership documents, review booking requests, and view commissions.
- **Admins:** Review ownership documents, approve listings, configure commission rates, and view platform activity.
- **AI features:** Use the Makan assistant and detect attempts to move tenant-agent conversations outside the platform. Messages that violate the communication policy are blocked before they are saved.

Mock checkout is for demonstration only. It does not charge or transfer real money.

## Project links

- [Figma - Makan wireframes and mockups](https://www.figma.com/design/GszRxU0gmeoi51sX3Mau4p/Makan-%E2%80%94-Wireframes---Mockups?node-id=3-475&t=9z44tSmBbVaUtbyl-1)
- [Trello - EstatePro Laravel project board](https://trello.com/b/54wEkdkv/estatepro-laravel-project)
- [GitHub repository](https://github.com/DanaJamalAlhawari/Real-Estate-Project)

## Requirements

- Git
- PHP 8.3 or newer (PHP 8.4 recommended for compatibility with the current dependency lock file)
- Composer 2
- Node.js 22.12+ or 24 LTS, with npm
- MySQL or MariaDB (XAMPP is suitable for Windows)
- Ollama, installed locally: [ollama.com/download](https://ollama.com/download)
- PHP extensions required by Laravel, including `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`, `dom`, `xml`, and `gd` for image tests. Enable `intl` and `pdo_sqlite` / `sqlite3` as well if needed for local development and automated tests.
- Internet access for installing dependencies, downloading AI models, and loading maps and geocoding results.

Laravel Reverb, Laravel Echo, MapLibre and other application dependencies are provided by the project; they do not need separate manual installation.

## Run on a fresh Windows machine

The following commands use PowerShell. On macOS or Linux, use `npm` instead of `npm.cmd` and your preferred database service.

### 1. Clone the project

Open PowerShell in the folder where you want to keep the project. For example, on Windows with XAMPP:

```powershell
cd C:\xampp\htdocs
git clone -b nahmad https://github.com/DanaJamalAlhawari/Real-Estate-Project.git Real-Estate-AI
cd Real-Estate-AI
```

These instructions use the `nahmad` development branch. If you already have the repository, switch to the appropriate branch and update it after saving any local changes.

Check that the required tools are available:

```powershell
git --version
php -v
composer --version
node -v
npm.cmd -v
ollama --version
```

### 2. Create the environment file and database

Create `.env` from the example if it does not already exist:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Start MySQL from the XAMPP Control Panel (or start your own MySQL/MariaDB service). Create a new empty database named `real_estate_ai` using phpMyAdmin or a database client.

Open `.env` and configure your local database:

```dotenv
APP_NAME=Makan
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=real_estate_ai
DB_USERNAME=root
DB_PASSWORD=
```

Use your actual database name, username, password and port. The empty password above is only an example for a default local XAMPP installation. Keep existing environment settings that are needed on your machine. If an existing `DB_URL` is set, ensure it points to the intended database or remove it when using the individual `DB_*` settings.

### 3. Install project dependencies

```powershell
composer install
npm.cmd install
```

If Composer reports that a locked Symfony dependency requires PHP 8.4, use PHP 8.4 or newer. [Laravel 13](https://laravel.com/framework/docs/13.x/deployment) supports PHP 8.3, but [Symfony 8.1](https://symfony.com/releases/8.1) requires PHP 8.4 and the committed dependency versions may require it. If you must stay on PHP 8.3, the following local workaround was used during development:

```powershell
composer update --minimal-changes
```

This can change `composer.lock`; do not commit that change without coordinating with the team. Do not bypass Composer's platform requirements.

If `APP_KEY` is empty in a new `.env`, generate it:

```powershell
php artisan key:generate
```

Do not regenerate an existing application key unnecessarily.

### 4. Configure Laravel Reverb (real-time chat)

Generate a local key and secret. Run the following command twice and use a different output for each value:

```powershell
php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"
```

Add or update these settings in `.env`:

```dotenv
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=makan-local
REVERB_APP_KEY=replace-with-generated-key
REVERB_APP_SECRET=replace-with-generated-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Replace the key and secret placeholders with the generated values. Do not commit `.env` or real secrets to GitHub.

### 5. Set up Ollama and AI models

Makan uses two independent Ollama configurations: one for the property assistant and another for chat moderation. Both can use the same local Ollama server.

Start the Ollama application. On a machine where the server is not already running, open a separate terminal and run:

```powershell
ollama serve
```

If Ollama is already running, you do not need to start a second server.

List the installed models:

```powershell
ollama list
```

If you want to use the project's default models, download any that are missing:

```powershell
ollama pull qwen2.5:3b
ollama pull qwen3.5:4b
```

Configure `.env`:

```dotenv
# Makan assistant
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:3b

# Tenant-agent message moderation
MODERATION_OLLAMA_URL=http://127.0.0.1:11434
MODERATION_OLLAMA_MODEL=qwen3.5:4b
MODERATION_OLLAMA_THINK=false
```

#### Using another model that is already installed

You do not need to download the default models if you have suitable alternatives. Run `ollama list`, copy the exact model name including its tag, and update the corresponding variable in `.env`.

For example, if `qwen3:8b` is already installed and you want to use it for moderation:

```dotenv
MODERATION_OLLAMA_MODEL=qwen3:8b
MODERATION_OLLAMA_THINK=false
```

To change the assistant model, update `OLLAMA_MODEL` instead. The two settings are independent. No PHP code changes are required. Choose a model that fits your machine's available memory and verify that it works for the intended task. Different models may produce different moderation results; the moderation client expects an `ALLOW` or `BLOCK` decision. If a model rejects the optional `think` parameter, set `MODERATION_OLLAMA_THINK=null` (without quotes) to omit it.

Verify that the local server responds:

```powershell
Invoke-RestMethod http://127.0.0.1:11434/api/tags
php artisan config:clear
```

If Ollama is unavailable or cannot return a valid moderation decision, tenant-agent messages that cannot be safely evaluated are not sent. The sender can retry after the service is restored. Admin support conversations are exempt from this moderation rule.

### 6. Prepare the database and storage

Before running migrations, confirm that `.env` points to your intended local database.

```powershell
php artisan config:clear
php artisan migrate
php artisan storage:link
```

The migrations create the application tables and apply changes for bookings, chat, photos, ownership verification, and property details. The storage link makes public property images accessible.

> **Existing databases:** Back up your database before applying the new migrations. The property changes add the required `area_sqm` field and remove the old geographic `area`, `area_ar`, and `area_en` columns. Existing listings need valid physical area values and a planned migration/backfill; do not assume an old database can be upgraded without data preparation. Never use `migrate:fresh` on a database containing data you need.

For a fresh local database, you can optionally seed demo data:

```powershell
php artisan db:seed
```

The default seeder provides these local demo accounts:

| Role | Email | Password |
| --- | --- | --- |
| Tenant | `tenant@example.com` | `password` |
| Agent | `agent@example.com` | `password` |

These credentials are for local development only. Do not seed an existing database without reviewing the seeders first. You can also register your own test accounts through the application.

If you need a local admin account and one does not already exist, run:

```powershell
php artisan tinker
```

Then enter the following PHP code, replacing the example email and password with your own local values:

```php
\App\Models\RealEstateUser::forceCreate([
    'email' => 'admin@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('replace-with-your-local-password'),
    'role' => 'admin',
]);
```

Type `exit` to leave Tinker. Do not use these example values as production credentials.

### 7. Run the application

Keep MySQL/MariaDB and Ollama running. Open three PowerShell terminals in the project directory and run one command in each:

**Terminal 1 - Laravel**

```powershell
php artisan serve
```

**Terminal 2 - Vite**

```powershell
npm.cmd run dev
```

**Terminal 3 - Reverb**

```powershell
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser, or use the URL printed by Laravel if a different port is selected. Update `APP_URL` accordingly.

Alternatively, use `npm.cmd run build` to compile frontend assets if you do not need the Vite development server. Laravel and Reverb must still be running, and Ollama is needed for AI features.

### 8. Try the main features

- Sign in as an agent, create a property with its area in square metres, bilingual details, a cover photo, and a location. Drag the map marker to update the address and verify the returned information. Missing or inaccurate address translations can be corrected manually.
- Sign in as an admin to review ownership verification and approve a listing when required.
- Sign in as a tenant to discover a listing, save it, request a booking, and open a conversation with the agent.
- Open the tenant and agent accounts in separate browser sessions (for example, normal and private windows). Send a normal message and confirm that it appears without refreshing. Try a message such as `Let's continue on WhatsApp` to check that moderation blocks attempts to move the conversation outside the platform.
- Try the Makan assistant to confirm that its configured Ollama model responds.

## Common issues

| Issue | What to check |
| --- | --- |
| Composer installation fails | Check `php -v`, enabled PHP extensions, and the locked dependencies. See step 3. |
| Database connection fails | Start MySQL/MariaDB and check the database credentials and port in `.env`. |
| Images are not displayed | Run `php artisan storage:link` if the public storage link is missing. |
| CSS or JavaScript is missing | Start Vite or run `npm.cmd run build`. |
| Chat is not real-time | Check that Reverb is running and its server and Vite settings match. Restart Vite after changing `VITE_REVERB_*` values. |
| Ollama or moderation fails | Check `ollama list`, the server URL, the exact model tag, and the `MODERATION_OLLAMA_THINK` setting. |
| Address lookup is unavailable | Check the internet connection. The map and geocoding service rely on external providers; enter missing address details manually. |

After changing `.env`, run `php artisan config:clear` and restart any affected long-running processes. Rebuild frontend assets if using a compiled build and Vite environment variables change.

## Run the automated tests (optional)

The project uses SQLite in memory for automated tests. Ensure the SQLite PHP extensions are enabled. To explicitly keep the tests separate from the local MySQL database, run:

```powershell
php artisan config:clear
$env:DB_CONNECTION = "sqlite"
$env:DB_DATABASE = ":memory:"
$env:DB_URL = ""
try {
    php artisan test
} finally {
    Remove-Item Env:DB_CONNECTION -ErrorAction SilentlyContinue
    Remove-Item Env:DB_DATABASE -ErrorAction SilentlyContinue
    Remove-Item Env:DB_URL -ErrorAction SilentlyContinue
}
```

The automated moderation tests mock the Ollama service; they do not require live model downloads or verify a model's real-world accuracy.
