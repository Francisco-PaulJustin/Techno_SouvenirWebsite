# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

MemoCraft is a souvenir shop in plain PHP (no framework, no Composer, no npm), PDO, vanilla JS and CSS. The database is Supabase PostgreSQL. It runs locally under XAMPP and is deployed to Render as a Docker web service.

There is no build step, test suite or linter.

## Commands

```sh
# Syntax-check PHP (XAMPP's PHP 8.2; `php` is not on PATH)
/c/xampp/php/php.exe -l path/to/file.php

# Local site: start Apache in XAMPP, then open
#   http://localhost/Techno_SouvenirWebsite/

# Run the production container locally (same as Render)
docker build -t memocraft .
docker run --rm -p 10000:10000 -e PORT=10000 -e DB_HOST=... -e DB_USER=... -e DB_PASS=... memocraft
# then http://localhost:10000/  and  http://localhost:10000/health
```

To check a query against the real database, write a small PHP script that does `require 'includes/config.php';` and uses `$pdo`. Run it from the repo root with `php.exe`. Wrap writes in `beginTransaction()` / `rollBack()`: there is only one database, and it is the live one.

## Configuration

`includes/config.php` reads `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` and optionally `DB_SSLMODE` (default `require`) from environment variables first. If they aren't set, it falls back to the array returned by `includes/config.local.php`, which is gitignored and copied from `config.local.example.php`. Every link in the site is relative.

Every page includes `config.php`, which does three things in order:
- It starts the session with hardened cookies (HttpOnly, SameSite=Lax, and Secure behind HTTPS, including Render's `X-Forwarded-Proto`). **Pages must not call `session_start()` themselves.** Scripts that don't want a session, such as `health.php`, define `SKIP_SESSION` before including it.
- It protects against CSRF: any POST whose `Origin` (or `Referer`) host differs from `HTTP_HOST` gets a 403. There are no per-form tokens, so every state-changing action must be a POST, never a GET link.
- It opens the database connection, creating the global `$pdo`. If the connection fails, it logs the error and `die()`s with a generic message.

## Database (PostgreSQL, not MySQL)

The code was migrated from MySQL. The live Supabase schema is the source of truth, and `sql/souvenir_shop.sql` mirrors it. Things that have broken before:

- `orders.status` is the enum `order_status` (`pending`, `processing`, `shipped`, `completed`, `cancelled`). `users.role` is the enum `user_role` (`customer`, `admin`). Always use lowercase values, because Postgres rejects any other literal and even `WHERE status = 'Processing'` throws.
- `products.featured` is `BOOLEAN`: compare with `= TRUE`, not `= 1`.
- Get new ids with `INSERT ... RETURNING id` and `fetchColumn()`, not `lastInsertId()`.
- Don't use MySQL syntax: use `STRING_AGG` (not `GROUP_CONCAT`), `NOW() - INTERVAL '5 seconds'` (not `DATE_SUB`), and `ILIKE` for case-insensitive search.
- PDO runs with `ATTR_EMULATE_PREPARES => false`, `ERRMODE_EXCEPTION` and `FETCH_ASSOC` as the default fetch mode.

## Page structure

Every page is a standalone script that follows the same pattern:

```php
require_once 'includes/config.php';
require_once 'includes/auth.php';
redirectAdminIfLoggedIn();   // public pages; requireCustomer() / requireLogin() on protected ones
$page_title = '...';
$additional_js = ['cart.js']; // optional, loaded by footer from assets/js/
require_once 'includes/header.php';
require_once 'includes/navbar.php';
// ... page body ...
require_once 'includes/footer.php';
```

Include paths are relative, so scripts in `admin/` and `api/` use `../includes/...`.

**Roles:** customers and admins share `login.php`. Admins are kept out of the customer pages: `redirectAdminIfLoggedIn()` sends them to `admin/index.php`, unless `?admin_view=1` sets the `$_SESSION['admin_viewing_site']` flag. Admin pages include `admin/includes/admin_auth.php`, which enforces the admin role and clears that flag. The admin pages use their own header, sidebar and footer from `admin/includes/`.

**Cart:** the cart lives only in the session, as `$_SESSION['cart']` (`[product_id => quantity]`), managed by `cart/cart_session.php`. It is not stored in the database.

**AJAX:** scripts in `assets/js/` call endpoints in `api/` with `fetch`, and the endpoints return JSON. `api/add_to_cart.php` works both ways: it returns JSON when the request is `Content-Type: application/json`, and otherwise handles a normal form POST from `product_view.php`.

**Checkout:** `checkout.php` does the whole checkout on the server: it validates, inserts `orders` and `order_items`, and decrements stock in a transaction. It handles both the cart and "Buy Now", which arrives as `product_id` and `quantity` via POST or GET. The cart page also POSTs to `checkout.php` (with `selected_products[]`), so an order is only placed when the form's hidden `place_order` field is present. Stock is decremented with `UPDATE ... WHERE stock >= ?` and `rowCount()` is checked, so simultaneous orders can't oversell. Cancelling an order (the customer via `api/cancel_order.php`, or an admin in `manage_orders.php`) returns its items to stock, and a cancelled order can't be reopened.

## Uploads

- Product images are saved to `admin/uploads/`. `products.image` stores only the filename, and pages build the path as `admin/uploads/<image>`.
- Profile images are saved to `uploads/profiles/`.
- `order_items.product_id` cascades on delete, so `admin/delete_product.php` refuses to delete products that have been ordered (that would erase lines from customers' order history).
- Existing images are committed to git. Render's filesystem is temporary, so images uploaded on the live site are lost on every redeploy or restart.

## Deployment (Render)

- `Dockerfile`: `php:8.2-apache` with `pdo_pgsql` and the production `php.ini`.
  - Apache is rewritten at startup to listen on `$PORT`.
  - `/health` is an Apache `Alias` to `health.php`, which returns `200 OK` or `503` if the database is down. UptimeRobot pings it to keep the free instance awake.
  - On XAMPP, the health check is at `/health.php`.
- Render builds from `main` on GitHub and redeploys on every push.
- Database credentials are set as Render environment variables. `.dockerignore` keeps `config.local.php` out of the image.
- Production is Linux, so file and path references are case-sensitive, even though XAMPP on Windows isn't.

## Conventions

- IDs from `$_GET` and `$_POST` must be validated (`filter_var(..., FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])`) before they reach a query. Unlike MySQL, Postgres throws on `'abc'` or `''` for a numeric column.
- Pure-PHP include files must not output anything after `?>`, because pages send `header('Location: ...')` after including them.
- Most files use CRLF line endings. Keep them when editing, so diffs don't touch every line.
