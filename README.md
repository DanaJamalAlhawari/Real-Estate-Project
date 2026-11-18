# Makan

Makan is a real estate browsing prototype built with Laravel 12, Blade, Bootstrap 5, and JavaScript. It includes public pages and demo interfaces for seekers, owners, and administrators.

## Run locally

Requires PHP 8.2 or newer and Composer.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

On Windows, use `copy .env.example .env` in Command Prompt or `Copy-Item .env.example .env` in PowerShell. Open `http://127.0.0.1:8000`.

The demo uses a local listing catalog and file-based sessions, so it runs without database migrations or an npm build. To run the starter Laravel migrations for future backend development, create `database/database.sqlite` first or configure your database connection in `.env`.

## Project structure

- `routes/web.php` defines named page routes and redirects old `.html` URLs.
- `app/Http/Controllers/PageController.php` renders the Blade views and supplies listing data.
- `app/Models/demo-homes.json` contains the six demo listings.
- `resources/views/layouts/app.blade.php` is the shared Blade layout.
- `resources/views/components/property-card.blade.php` renders the cards used on the landing page and in Discover.
- `public/assets/` contains Bootstrap, local fonts, illustrative Unsplash photography, and JavaScript for filters, galleries, and browser-local favorites.

Login, registration, booking, chat, and role dashboards are demo screens. Demo forms do not save changes or provide real authentication. Use sample details only.

Run the Laravel test suite with `php artisan test`.
