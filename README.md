# IPT10 Shop

An ecommerce web app built on a custom PHP framework (no Laravel or Symfony, no Composer packages).
IPT10 final project.

- **Framework** (`framework/`): router with route parameters and groups, middleware pipeline, Request/Response objects, PDO database layer, base Model, views with layouts, sessions with flash messages, validator, HTTP client, logger, `.env` config.
- **App** (`app/`): product catalog with search and categories, session cart, sign-up/login, checkout with stock checks, order history, and an admin area for products, orders and stock.
- **External API**: [Frankfurter](https://frankfurter.dev) exchange rates (ECB data, no key needed). Prices can be shown in PHP, USD, EUR, JPY or SGD.
- **Database**: SQLite by default; MySQL works by changing `.env`.

## Requirements

PHP 8.1+ with `pdo_sqlite` (or `pdo_mysql`) and `curl` extensions.

## Run it

```bash
cp .env.example .env
```

```bash
php bin/migrate.php --fresh --seed
```

```bash
php -S localhost:8000 -t public public/index.php
```

Then open http://localhost:8000.

Test accounts created by the seeder (local only, set in `.env`):

| Role | Email | Password |
|---|---|---|
| Admin | admin@shop.test | admin123 |
| Customer | juan@shop.test | customer123 |

### Using XAMPP / MySQL instead

1. Create a database, e.g. `ipt10_shop`.
2. In `.env` set `DB_DRIVER=mysql` and fill in `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
3. Run `php bin/migrate.php --seed`. It uses `database/migrations/001_create_tables.mysql.sql`.
4. Point Apache at `public/` (the included `.htaccess` sends all requests to `index.php`). If the app is in a subfolder, set `APP_URL` to that full URL, e.g. `http://localhost/ipt10-shop/public`.

## Tests

```bash
php tests/run.php
```

The tests cover the router, request, validator, user login, placing an order, and rollback when stock runs out. They use an in-memory SQLite database.

## Folder layout

```
framework/     the framework (namespace Core)
app/           the shop: Controllers, Models, Middleware, Services, Views (namespace App)
config/        app, database and API settings (read from .env)
routes/web.php all URLs
database/      SQL migrations
bin/migrate.php  creates tables + sample data
public/        web root: index.php (front controller), css
storage/       SQLite file, logs, API cache
tests/run.php  test runner
docs/          project documentation draft
```

## Main URLs

| URL | What |
|---|---|
| `/` | Home |
| `/products`, `/products/{slug}` | Catalog and product page |
| `/cart`, `/checkout` | Cart and checkout (checkout needs login) |
| `/orders`, `/orders/{id}` | Customer order history |
| `/admin` | Dashboard, products, orders (admin only) |
| `/currency/{code}` | Switch display currency |
| `/api/products?currency=USD` | JSON catalog |
