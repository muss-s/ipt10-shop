# Handoff: IPT10 Final Project (KAGE 影 SUPPLY)

Updated: 2026-11-16 · lean mode: ultra, profile: code

Read this first when you continue on another computer. `handoffs/LIVE-STATE.md` is the short version.

## What this is

IPT10 final project: a PHP web framework written from scratch, plus an ecommerce app built on it.
- Guidelines PDF (not in the repo): `IPT10-Final_project_guidelines.pdf`. Required: custom framework, CMS or ecommerce app, at least one external API, a database, an 8–12 page PDF write-up and a 10–15 min video.
- The shop is **KAGE 影 SUPPLY**, a Japanese street style clothing store with a dark neon theme and heavy animation.
- External API: Frankfurter exchange rates (ECB data, no key). Prices show in PHP, USD, EUR, JPY, SGD.
- Database: SQLite by default; MySQL supported (`DB_DRIVER=mysql`).

## Set up on a new computer

Needs PHP 8.1+ with `pdo_sqlite` and `curl` (check with `php -m`).

```bash
git clone https://github.com/muss-s/ipt10-shop.git
```

```bash
cd ipt10-shop && cp .env.example .env
```

```bash
php bin/migrate.php --fresh --seed
```

```bash
php -S localhost:8000 -t public public/index.php
```

Open http://localhost:8000. Test logins: admin `admin@shop.test` / `admin123`, customer `juan@shop.test` / `customer123`.

Tests: `php tests/run.php` (10 should pass).

`.env`, the SQLite file, logs and the rate cache are not in git. The commands above recreate them.

## Where things are

| Path | What |
|---|---|
| `framework/` | The framework (namespace `Core`): App, Router, Request, Response, Database, Model, View, Session, Validator, HttpClient, Config, Logger |
| `app/` | The shop: Controllers (+ Admin), Models, Middleware, Services (CurrencyService, Cart), Views |
| `app/helpers.php` | `product_art()` draws the SVG kanji poster for products without an image URL |
| `routes/web.php` | All URLs |
| `public/css/app.css`, `public/js/app.js` | Theme and animations (intro, page wipe, scroll reveal, custom cursor, 3D tilt, magnetic buttons, parallax, toasts) |
| `bin/migrate.php` | Creates tables and seeds 9 streetwear products |
| `database/migrations/` | SQLite and MySQL schema |
| `docs/DOCUMENTATION.md` | Documentation draft, all sections from the guidelines |

## Done

- Framework and app complete. Customer flow (browse, cart, register/login, checkout, order history) and admin (dashboard, product CRUD, order status, cancel restocks) work.
- Live currency API call works, with a one-hour cache and fallback.
- Retheme to KAGE 影 SUPPLY with poster art per product.
- Bug fixed: validator rejected valid phone numbers at checkout.

## Not done / not checked

- **Docs are out of date with the redesign**: they still say "IPT10 Shop" and don't mention the streetwear theme. The testing section claims the API fallback was tested; it wasn't.
- Docs still need: names, dates, screenshots, an ERD image, your team's own "issues encountered", then export to PDF (Times New Roman 12pt, double-spaced).
- Not checked by eye: full-width desktop layout, mobile menu, and the cart / checkout / login / admin pages in the new theme.
- Not tested: admin create/edit/delete through the browser, cancel-to-restock, API fallback, MySQL/XAMPP setup.
- Product photos: optional. Posters are generated now. Real images could be made with an image generator (the `/imagegen` skill was available in the last session) and set as each product's image URL.
- Video presentation not recorded. Outline is at the end of `docs/DOCUMENTATION.md`.

## Decisions made

- Ecommerce, not CMS. Frankfurter API (keyless). SQLite default.
- No Composer packages, to keep everything as our own framework code. Tests use a small custom runner, not PHPUnit.
- Deployment: decided against it. Not required by the guidelines, and Vercel doesn't suit a PHP app that saves files and sessions. If ever wanted: Render/Railway, or a free PHP host with MySQL.
- Repo is private on GitHub. Make it public only if the instructor needs a public link.

## Next steps (suggested order)

1. Update `docs/DOCUMENTATION.md` for the KAGE theme and fill in the missing parts.
2. Click through every page once (especially admin) to check the new styling.
3. Optional: product images.
4. Export the PDF, record the video, submit with the repo link.
