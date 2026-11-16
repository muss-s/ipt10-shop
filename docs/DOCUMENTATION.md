# IPT10 Shop: A Custom PHP Framework and Ecommerce Application

**Integrative Programming and Technologies – Final Project**

Submitted by: [Team Member 1 Name] · [Team Member 2 Name]
Date: [Submission date]

> Draft. Before submitting: fill in names and dates, add screenshots, set the font to Times New Roman 12pt double-spaced, and export to PDF. Target length is 8–12 pages without the title page and references.

---

## Abstract

IPT10 Shop is an ecommerce web application built on a PHP framework we wrote from scratch, with no third-party packages. The framework follows the Model–View–Controller pattern. A single front controller (`public/index.php`) receives every request and hands it to a kernel. The kernel runs global middleware for logging, sessions and CSRF protection, then uses a router to match the URL to a controller action, and runs any route middleware such as authentication or admin checks. Its core classes are `Request`, `Response`, `Router`, `Database` (a PDO wrapper), `Model`, `View`, `Session`, `Validator`, `HttpClient` and `Config`, which reads settings from a `.env` file.

The application lets customers browse a catalog by category or search term, keep a cart in their session, register and log in, and check out with cash on delivery. Checkout validates stock inside a database transaction. Administrators get a dashboard with sales figures and low-stock alerts, product management (create, edit, delete) and order status updates; cancelling an order puts its items back in stock.

The app uses the Frankfurter exchange-rate API, which serves European Central Bank data, to show prices in Philippine pesos, US dollars, euros, Japanese yen or Singapore dollars. Rates are cached on disk for an hour. If the API cannot be reached, the app falls back to the last good rates, or to pesos only. Data is stored in a relational database with five tables (users, categories, products, orders, order_items). SQLite is the default and MySQL is supported by changing one setting.

---

## 1. Introduction

The course covers how the parts of a web system work together: HTTP, server-side programming, databases and third-party services. Frameworks like Laravel and Symfony hide most of this behind conventions and generated code. That is productive, but a student can build a whole app without ever seeing how a URL becomes a function call, or how a login survives from one request to the next.

The goal of this project was to build those parts ourselves and then prove they work by building a real application on them. Writing the router showed us how pattern matching and route parameters work. Writing the middleware pipeline showed why authentication and CSRF checks belong outside controllers. Writing the database layer made prepared statements and transactions concrete.

Our framework is much smaller than Laravel (about 1,000 lines) and leaves out things like a service container, an ORM with relationships, a templating language, queues and a CLI generator. In exchange, every line is code we wrote and can explain. It does use the same ideas as the larger frameworks: a front controller, MVC, middleware, configuration from the environment, and models that hide SQL from controllers.

---

## 2. Framework Design

### 2.1 Architecture and folder structure

```
public/index.php      Front controller (only public PHP file)
framework/            The framework, namespace Core
  App.php             Kernel: middleware pipeline, dispatch, error pages
  Router.php          Routes, groups, {param} matching (+ Route class)
  Request.php         Wraps $_SERVER/$_GET/$_POST/JSON body
  Response.php        Status, headers, body; html/json/redirect helpers
  Database.php        PDO connection, query helpers, transactions
  Model.php           Base table gateway (find, where, create, update, delete)
  View.php            PHP templates with layouts and partials
  Session.php         Session + flash data
  Validator.php       Rule strings like "required|email|max:150"
  HttpClient.php      cURL wrapper for JSON APIs
  Config.php          .env loader + config/*.php with dot notation
  Logger.php          File logger
  Middleware/Middleware.php   Middleware interface
  bootstrap.php       Autoloader, config, logger setup
  helpers.php         e(), url(), csrf_field(), old(), ...
app/                  The shop, namespace App
  Controllers/        HomeController, ProductController, CartController, ...
  Controllers/Admin/  DashboardController, ProductAdminController, OrderAdminController
  Models/             User, Category, Product, Order
  Middleware/         Auth, Admin, Guest, Csrf, Session, RequestLog
  Services/           CurrencyService (external API), Cart
  Views/              layouts/, partials/, one folder per section
config/               app.php, database.php, services.php
routes/web.php        All routes
database/migrations/  SQL schema (SQLite + MySQL)
```

**Naming conventions.** Classes use PascalCase and live in a file of the same name (PSR-4). Controllers end in `Controller`, middleware ends in `Middleware`, and models use the singular (`Product` → table `products`). Views are snake/kebab-case files grouped by feature (`products/show.php`). Database columns are snake_case, and money is stored as integer centavos (`price_cents`) so there are no rounding errors.

**Autoloading.** We don't use Composer, so `bootstrap.php` registers an autoloader that maps `Core\` to `framework/` and `App\` to `app/`:

```php
spl_autoload_register(function (string $class) use ($root): void {
    $map = ['Core\\' => '/framework/', 'App\\' => '/app/'];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $root . $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});
```

### 2.2 Request lifecycle

1. The web server sends every request to `public/index.php`: Apache through `.htaccess`, PHP's built-in server through the router script.
2. `bootstrap.php` loads the autoloader, `.env` and config.
3. Routes are registered from `routes/web.php`.
4. `App::run()` builds a `Request` and passes it through global middleware: `RequestLogMiddleware` → `SessionMiddleware` → `CsrfMiddleware`.
5. The router matches the method and path, and route middleware runs (for example `auth`, then `admin`).
6. The controller action receives the `Request` and returns a `Response`.
7. The response travels back out through the middleware (the logger records the status and time) and is sent.
8. Any `HttpException` (404, 403, 405, 419) becomes an error page. Other exceptions are logged and shown as a 500, with the stack trace only when `APP_DEBUG=true`.

### 2.3 Routing

Routes connect an HTTP method and a URL pattern to a `[ControllerClass, 'method']` pair. Patterns can contain `{name}` parameters, which the `Route` class turns into named regex groups. Groups add a URL prefix and middleware to every route inside them.

```php
// routes/web.php
$router->get('/products/{slug}', [ProductController::class, 'show']);
$router->post('/cart/add', [CartController::class, 'add']);

$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($router) {
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/products/{id}/edit', [ProductAdminController::class, 'edit']);
    $router->delete('/products/{id}', [ProductAdminController::class, 'destroy']);
});
```

```php
// framework/Router.php – turning a pattern into a regex and matching it
$this->regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';

public function match(string $method, string $path): array
{
    $allowed = [];
    foreach ($this->routes as $route) {
        $params = $route->matches($path);
        if ($params === null) continue;
        if ($route->method === $method) return [$route, $params];
        $allowed[] = $route->method;
    }
    throw $allowed ? new HttpException(405, 'Method Not Allowed')
                   : new HttpException(404, 'Page Not Found');
}
```

HTML forms can only send GET and POST, so `Request::capture()` reads a hidden `_method` field. That is how the delete button reaches the `DELETE /admin/products/{id}` route.

### 2.4 Middleware

Middleware classes implement one method, `handle(Request $request, callable $next): Response`. A middleware can return its own response, for example a redirect to the login page, or call `$next` to pass the request on. The kernel wraps the controller in each middleware, from the inside out:

```php
// framework/App.php
private function runPipeline(array $middleware, Request $request, callable $destination): Response
{
    $next = $destination;
    foreach (array_reverse($middleware) as $name) {
        $class = $this->middlewareAliases[$name] ?? $name;
        $next = fn (Request $req) => (new $class())->handle($req, $next);
    }
    return $next($request);
}
```

| Middleware | Kind | Purpose |
|---|---|---|
| `RequestLogMiddleware` | global | Logs method, path, status, time in ms, IP to `storage/logs/app.log` |
| `SessionMiddleware` | global | Starts the session and rotates flash messages |
| `CsrfMiddleware` | global | Rejects POST/PUT/DELETE without a valid token (HTTP 419) |
| `AuthMiddleware` (`auth`) | route | Redirects guests to `/login` and remembers the page they wanted |
| `AdminMiddleware` (`admin`) | route | 403 unless the user's role is `admin` |
| `GuestMiddleware` (`guest`) | route | Keeps logged-in users away from login/register |

```php
// app/Middleware/AuthMiddleware.php
public function handle(Request $request, callable $next): Response
{
    if (!Session::get('user')) {
        Session::set('intended', $request->method() === 'GET' ? $request->path() : '/');
        Session::flash('error', 'Please log in first.');
        return Response::redirect('/login');
    }
    return $next($request);
}
```

### 2.5 Core classes

- **Request**: method, normalized path (subfolder base path removed), `input()`, `only()`, `query()`, and route parameters through `param()`. It also decodes JSON request bodies.
- **Response**: holds status, headers and body. The static constructors `html()`, `json()` and `redirect()` keep controllers short. `send()` writes the response out.
- **Router / Route**: described above.
- **Database**: one shared PDO connection built from config, with a SQLite or MySQL DSN, exception error mode and associative fetches. The helpers `query`, `fetch`, `fetchAll`, `insert`, `update` and `transaction` always use prepared statements.
- **Model**: a base class with `find`, `findBy`, `where`, `all`, `create`, `update`, `delete` and `count`. `$fillable` limits mass assignment. Column names are checked against a whitelist because they cannot be bound as parameters.
- **View**: renders a PHP template into a layout. Output is escaped with the `e()` helper.
- **Session / Validator / HttpClient / Logger / Config**: supporting services.

```php
// framework/Database.php – transactions used by checkout
public function transaction(callable $callback): mixed
{
    $this->pdo->beginTransaction();
    try {
        $result = $callback($this);
        $this->pdo->commit();
        return $result;
    } catch (\Throwable $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}
```

### 2.6 Configuration

Settings live in a `.env` file, which is kept out of version control; `.env.example` is the template. `Config::loadEnv()` reads it, and `config/*.php` files map it into named arrays read with dot notation, such as `Config::get('services.currency.base_url')`.

```
APP_NAME="IPT10 Shop"
APP_URL=http://localhost:8000      # base URL; a subfolder path here is respected by url()
APP_DEBUG=true
DB_DRIVER=sqlite                   # or mysql
DB_DATABASE=storage/database/shop.sqlite
CURRENCY_API_URL=https://api.frankfurter.dev/v1
CURRENCY_CACHE_TTL=3600
```

---

## 3. Database Design

### 3.1 Entity–relationship overview

```
users 1 ──< orders 1 ──< order_items >── 1 products >── 1 categories
```

- A **user** places many **orders**. `role` is either `customer` or `admin`.
- An **order** has many **order_items**. Each item stores its own `unit_price_cents`, so later price changes do not alter past orders.
- An **order_item** points to one **product**.
- A **product** belongs to one **category** (optional; set to NULL if the category is deleted).

*(Insert ERD image here, drawn in draw.io or dbdiagram.io from the tables below.)*

### 3.2 Tables

**users**

| Column | Type | Key / constraint |
|---|---|---|
| id | INTEGER | PK, auto-increment |
| name | VARCHAR(100) | NOT NULL |
| email | VARCHAR(150) | UNIQUE, NOT NULL |
| password_hash | VARCHAR(255) | bcrypt from `password_hash()` |
| role | VARCHAR(20) | `customer` (default) or `admin` |
| created_at, updated_at | DATETIME | |

**categories**: id (PK), name VARCHAR(100), slug VARCHAR(120) UNIQUE

**products**

| Column | Type | Key / constraint |
|---|---|---|
| id | INTEGER | PK |
| category_id | INTEGER | FK → categories.id, ON DELETE SET NULL |
| name | VARCHAR(150) | NOT NULL |
| slug | VARCHAR(170) | UNIQUE (used in URLs) |
| description | TEXT | |
| price_cents | INTEGER | ≥ 0, price in centavos |
| stock | INTEGER | ≥ 0 |
| image_url | VARCHAR(500) | |
| created_at, updated_at | DATETIME | |

**orders**: id (PK), user_id (FK → users.id), status (`pending`/`paid`/`shipped`/`completed`/`cancelled`), total_cents, shipping_name, shipping_address, phone, created_at, updated_at

**order_items**: id (PK), order_id (FK → orders.id, ON DELETE CASCADE), product_id (FK → products.id), quantity (> 0), unit_price_cents

Indexes: `products(category_id)`, `orders(user_id)`, `order_items(order_id)`.

### 3.3 Migration scripts

The schema lives in `database/migrations/001_create_tables.sql` (SQLite) and `001_create_tables.mysql.sql` (MySQL, InnoDB, utf8mb4). `php bin/migrate.php --fresh --seed` runs the file that matches the configured driver, then adds two test users, four categories and nine products.

```sql
CREATE TABLE IF NOT EXISTS products (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name        VARCHAR(150) NOT NULL,
    slug        VARCHAR(170) NOT NULL UNIQUE,
    description TEXT,
    price_cents INTEGER NOT NULL CHECK (price_cents >= 0),
    stock       INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
    image_url   VARCHAR(500),
    created_at  DATETIME NOT NULL,
    updated_at  DATETIME NOT NULL
);
```

### 3.4 Connection configuration

`config/database.php` reads the driver, host, port, database, username and password from `.env`. For SQLite only `DB_DATABASE` (a file path) is used, and foreign keys are switched on with `PRAGMA foreign_keys = ON`. For MySQL, for example with XAMPP:

```
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipt10_shop
DB_USERNAME=root
DB_PASSWORD=
```

---

## 4. Web Application Development

**Type:** ecommerce system.

### 4.1 Features

| Feature | Where | Framework parts used |
|---|---|---|
| Catalog with search and category filter | `ProductController@index`, `Product::search()` | Router, Request query, Model with prepared LIKE query, View partials |
| Product page with prices in five currencies | `ProductController@show` | Route param `{slug}`, CurrencyService, HttpException 404 |
| Display currency switcher | `HomeController@setCurrency` | Session |
| Session cart (add, update, remove) with stock limits | `CartController`, `Services/Cart` | Session, flash messages, CSRF |
| Register / log in / log out | `AuthController`, `User` model | Validator, `password_hash`/`password_verify`, session regeneration, `guest` middleware |
| Checkout and order placement | `CheckoutController`, `Order::place()` | `auth` middleware, Validator, DB transaction |
| Order history | `OrderController` | `auth` middleware; customers can only see their own orders |
| Admin dashboard (stats, low stock, rates) | `Admin/DashboardController` | Route group with `auth` + `admin` |
| Product management | `Admin/ProductAdminController` | Validator, `_method=DELETE`, unique slugs |
| Order status management, restock on cancel | `Admin/OrderAdminController` | Transaction |
| JSON catalog API | `GET /api/products?currency=USD` | `Response::json` |

### 4.2 Example: placing an order

`Order::place()` runs inside one transaction. It checks every cart line against current stock, inserts the order and its items, and lowers stock with `UPDATE ... WHERE stock >= ?`. If two customers try to buy the last item at the same moment, the second update changes no rows, an exception is thrown, and the whole order is rolled back.

```php
$updated = $db->query(
    'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?',
    [$qty, $productId, $qty],
)->rowCount();
if ($updated === 0) {
    throw new \DomainException("{$products[$productId]['name']} just sold out.");
}
```

### 4.3 Security measures

- Every query uses prepared statements.
- All template output is escaped with `e()` (`htmlspecialchars`), which prevents XSS.
- Every form carries a CSRF token, checked with `hash_equals`.
- Passwords are hashed with bcrypt, the session ID is regenerated at login, and cookies are HttpOnly with SameSite=Lax.
- Model `$fillable` lists block mass assignment.
- Access is checked on the server: admin routes go through middleware, and order pages check ownership.

### 4.4 Screenshots

*(Add screenshots: home, product list, product page with currency table, cart, checkout, order page, admin dashboard, product form, admin orders.)*

---

## 5. API Integration

### 5.1 Purpose

Product prices are stored in Philippine pesos. The **Frankfurter API** (https://frankfurter.dev) returns daily reference rates from the European Central Bank, which the shop uses to show prices in USD, EUR, JPY and SGD. Shoppers pick a currency in the header. Orders are still charged in pesos, and the cart says so when another currency is selected.

### 5.2 Endpoint and formats

Request:

```
GET https://api.frankfurter.dev/v1/latest?base=PHP&symbols=USD,EUR,JPY,SGD
Accept: application/json
```

Response (JSON):

```json
{
  "amount": 1.0,
  "base": "PHP",
  "date": "2026-11-04",
  "rates": { "EUR": 0.013971, "JPY": 2.491, "SGD": 0.020115, "USD": 0.015741 }
}
```

### 5.3 Authentication

Frankfurter is free and needs no API key. We still put the base URL in `.env` (`CURRENCY_API_URL`), so the provider can be changed without editing code. A keyed provider would also add its key to `.env` and pass it in a header through `HttpClient::getJson($url, $query, $headers)`.

### 5.4 Error handling and rate limiting

- **Timeouts:** cURL connect and total timeouts are 5 seconds, so a slow API cannot hang the page.
- **Failures:** `HttpClient` throws `HttpClientException` on network errors, non-2xx statuses (with a separate message for HTTP 429) and invalid JSON. `CurrencyService` also checks that `rates` is present.
- **Caching:** successful responses are saved to `storage/cache/rates.json` with a timestamp and reused for `CURRENCY_CACHE_TTL` seconds (one hour by default). The page makes at most one API call per hour, which keeps us well within fair use. ECB rates only change once per working day anyway.
- **Fallback:** if the API fails, the last cached rates are used and marked "cached, API unreachable" in the footer. With no cache at all, the currency switcher hides the other currencies and prices show in pesos. Every failure is written to the log.

### 5.5 Code

```php
// app/Services/CurrencyService.php (simplified)
public function rates(): ?array
{
    $cached = $this->readCache();
    if ($cached && time() - $cached['fetched_at'] < $this->config['cache_ttl']) {
        return $cached + ['stale' => false];
    }
    try {
        $data = $this->http->getJson($this->config['base_url'] . '/latest', [
            'base' => 'PHP',
            'symbols' => implode(',', $this->config['symbols']),
        ]);
        $fresh = ['base' => $data['base'], 'date' => $data['date'],
                  'rates' => array_map('floatval', $data['rates']), 'fetched_at' => time()];
        $this->writeCache($fresh);
        return $fresh + ['stale' => false];
    } catch (HttpClientException $e) {
        Logger::error('Currency API: ' . $e->getMessage());
        return $cached ? $cached + ['stale' => true] : null;
    }
}
```

The app also has its own small JSON endpoint built on the framework: `GET /api/products?currency=USD` returns the catalog with converted prices, and returns HTTP 422 for an unsupported currency.

---

## 6. Testing and Debugging

### 6.1 Unit tests

We wrote a small test runner (`tests/run.php`) instead of installing PHPUnit, to keep the no-dependencies rule. It uses an in-memory SQLite database built from the real migration file. Run it with `php tests/run.php`. There are 10 tests:

- Router: route parameters, group prefix and middleware, 404 and 405.
- Request: path normalization and trimmed input.
- Validator: rule errors.
- Models: register and log in a user, placing an order lowers stock, an order with too little stock rolls back completely, unique slug generation.

### 6.2 Manual testing

We ran the app with PHP's built-in server and checked each flow by hand: browsing and filtering, switching currencies, adding to the cart beyond stock, registering with bad data, checking out, and viewing another user's order by URL (returns 404). As admin we created, edited and deleted products, changed order statuses and cancelled an order to confirm the stock came back. We also checked HTTP status codes with curl, for example a form POST without a CSRF token returns 419 and a customer opening `/admin` gets 403. We tested the API fallback by pointing `CURRENCY_API_URL` at an invalid host.

### 6.3 Issues encountered and fixes

| Issue | Cause | Fix |
|---|---|---|
| Valid phone numbers failed checkout validation | The validator's `max:20` compared numeric strings like `09171234567` as numbers | `min`/`max` compare numbers only when the field also has a `numeric`/`integer` rule; otherwise they check text length |
| Local server returned errors on every page | Another program was already using the chosen port | Started `php -S` on a free port |
| Flash messages appeared twice or not at all | Messages were cleared at the wrong point in the request | Two-slot flash storage (`_flash_next` → `_flash_now`) rotated once per request in `Session::start()` |
| Customer could open someone else's order by changing the ID | No ownership check | `OrderController@show` compares `user_id` with the logged-in user (admins excepted) |
| Price rounding errors | Floats for money | Prices stored as integer centavos |
| Deleting a product that had been ordered broke order history | Foreign key from order_items | Deletion is blocked with a message; admins set stock to 0 instead |

*(Edit this table to match the problems your team actually hit.)*

Debugging tools: `APP_DEBUG=true` shows exception messages and stack traces on the error page, `storage/logs/app.log` records every request and error, and `php -l` checks syntax.

---

## 7. Conclusion and Future Work

Building the framework first made the application easier to reason about. Each feature uses the same request → middleware → controller → model → view path, and every security rule sits in one place. The main lessons were how much a framework does for you (routing, sessions, CSRF, escaping) and why transactions matter when money and stock are involved.

Future work: online payments (for example the PayMongo test mode), product image uploads, pagination, reusable model relationships, a migration version table, and PHPUnit with CI.

---

## References

- PHP Manual: PDO, Sessions, password_hash. https://www.php.net/manual/
- Frankfurter API documentation. https://frankfurter.dev
- European Central Bank euro foreign exchange reference rates. https://www.ecb.europa.eu
- OWASP Cheat Sheet Series: CSRF Prevention, XSS Prevention. https://cheatsheetseries.owasp.org

## Appendix: Source Code

Full source: [repository link or "included on USB drive"]. Main entry points: `public/index.php`, `framework/App.php`, `routes/web.php`.

---

## Presentation outline (10–15 min, MP4/WebM, ≥720p)

1. **Overview (2–3 min):** problem, features, tech stack, folder layout.
2. **Framework demo (3–4 min):** walk through `index.php` → `App` → `Router` → middleware → controller; show adding a route; show a 404 and a 419.
3. **Features + API (4–5 min):** browse, switch currency, cart, register, checkout, order history; admin dashboard, product CRUD, cancel order → stock restored; `/api/products?currency=USD`; show the cache file and the fallback when the API URL is broken.
4. **Database + challenges (1–2 min):** ERD, transaction in `Order::place`, issues table.
5. **Conclusion (1 min):** lessons and future work.
