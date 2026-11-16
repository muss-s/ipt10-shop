# LIVE STATE: IPT10 Shop
Updated: 2026-11-05 · lean mode: ultra, profile: code

## Goal
IPT10 final project: a custom PHP framework (framework/, namespace Core) plus an ecommerce app (app/) using the Frankfurter currency API and SQLite (MySQL optional). Guidelines: /Users/mus/Documents/skills for proj/IPT10-Final_project_guidelines.pdf

## Done
- Framework: Router (params, groups), App kernel (middleware pipeline, error pages), Request/Response, Database (PDO, transactions), Model, View, Session/flash, Validator, HttpClient, Config/.env, Logger.
- App: catalog/search, product page with multi-currency prices, session cart, auth, checkout (transactional stock), order history, admin dashboard / product CRUD / order status (cancel restocks), /api/products JSON.
- bin/migrate.php (--fresh --seed), tests/run.php (10 passing), README.md, docs/DOCUMENTATION.md draft.

- 2026-11-05: rethemed as "KAGE 影 SUPPLY", a Japanese street style shop. New seed products, SVG poster art (product_art() in app/helpers.php), dark neon CSS theme, public/js/app.js (intro, page wipe, reveal, custom cursor, tilt, magnetic buttons, parallax, toasts).

- 2026-11-16: pushed to GitHub (private repo muss-s/ipt10-shop). Full handoff: handoffs/HANDOFF.md.

## Run
cp .env.example .env; php bin/migrate.php --fresh --seed; php -S localhost:8000 -t public public/index.php
Test accounts: admin@shop.test/admin123, juan@shop.test/customer123

## Next
- User: fill in names, screenshots, ERD image, real issues table; export the doc to PDF (TNR 12pt, double-spaced); record the video.
- Not yet tested: UI visuals, admin create/edit/delete via the browser, API fallback, MySQL path.
