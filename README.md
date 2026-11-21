# Makan | Real Estate Platform

Makan is a Laravel application for discovering rental properties, requesting bookings, and communicating with property owners.

## What you can do

- **Tenants** can discover and save properties, select a booking date range, message property owners, and use mock checkout after a booking is accepted.
- **Agents** can manage listings, submit ownership documents, review booking requests, and view their commission breakdown.
- **Admins** can review ownership documents, approve listings, set commission rates, and view platform activity.

Mock checkout is for demonstration. It does not charge or transfer money.

## Project links

- [Figma · Makan wireframes and mockups](https://www.figma.com/design/GszRxU0gmeoi51sX3Mau4p/Makan-%E2%80%94-Wireframes---Mockups?node-id=3-475&t=9z44tSmBbVaUtbyl-1)
- [Trello · EstatePro Laravel project board](https://trello.com/b/54wEkdkv/estatepro-laravel-project)

## Run on a fresh Windows machine

### Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL (or another database configured for the project)

Clone or switch to the branch that contains the chat work, then open PowerShell in the project directory.

### 1. Create `.env` if needed

Keep an existing `.env` file and its database credentials. For a fresh checkout, create it from the example:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Configure the database connection in `.env`. For MySQL, create a local database first, then set values such as:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=makan
DB_USERNAME=root
DB_PASSWORD=your-local-password
```

Use the database name, username, and password configured on your machine.

### 2. Install dependencies

```powershell
composer install
npm.cmd install
```

Composer installs Laravel Reverb. npm installs Laravel Echo and `pusher-js` from the project dependencies.

If this is a new `.env` and `APP_KEY` is empty, generate the application key:

```powershell
php artisan key:generate
```

### 3. Configure local Reverb

Generate two different local values for the Reverb key and secret. Run this command twice and use a different result each time:

```powershell
php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"
```

Add or update these values in `.env`, replacing the example key and secret with those generated values:

```dotenv
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=makan-local
REVERB_APP_KEY=replace-with-local-key
REVERB_APP_SECRET=replace-with-local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Do not commit your local `.env` or real secrets.

### 4. Apply configuration and database migrations

```powershell
php artisan config:clear
php artisan migrate
```

`migrate` applies pending database changes, including the chat reply and reaction migrations.

To create local sample users, properties, and a favorite, you can also run:

```powershell
php artisan db:seed
```

The default seeder creates `tenant@example.com` and `agent@example.com`, both with password `password`. These are local demo accounts; change or remove them outside local development.

The additional agent login listed below is not created by the default seeder. It works only with a database that already contains that account.

### 5. Start Laravel, Vite, and Reverb

Open three PowerShell terminals in the project directory and keep all three running.

**Terminal 1 — Laravel**

```powershell
php artisan serve
```

**Terminal 2 — Vite**

```powershell
npm.cmd run dev
```

**Terminal 3 — Reverb**

```powershell
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

### 6. Try the chat

Use a tenant account and an agent account that have a conversation in your local database. You can use the sample accounts created by `php artisan db:seed`, then create a conversation from a property listing. Sign in to each account in a separate browser session, such as a regular window and a private window. Send a message from one account and check that it appears for the other without refreshing.

Provided agent login (for a database that contains this account):

- Email: `makan-demo-agent-76198e8019@example.com`
- Password: `03cc1144dfd748e03277562e1cb06e27`

## Run the automated tests

```powershell
php artisan test
```
