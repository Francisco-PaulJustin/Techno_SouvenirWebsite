# MemoCraft Repository Analysis

> **Status (2026-09-26):** Items 1 and 2, and the MySQL part of item 11, are fixed in
> [PR #1](https://github.com/Francisco-PaulJustin/Techno_SouvenirWebsite/pull/1).
> For item 1, the database password still has to be changed in Supabase.

## What it is

MemoCraft, a souvenir shop written in plain PHP with PDO, vanilla JS and CSS. It has about 74 files and 13k lines. It covers customer pages (products, cart, checkout, orders, profile), a JSON API in `api/`, and an admin panel in `admin/` for products, categories, orders and users. You're partway through moving the database from MySQL to Supabase (Postgres).

## Critical

1. **Your live Supabase password is committed** in `includes/config.php:7` and is in the git history. Change it in Supabase now, then load it from environment variables or a gitignored file.
2. **The Postgres move is incomplete, so some pages will crash:**
   - `DATE_SUB(NOW(), INTERVAL 5 SECOND)` in `checkout.php:97` and `api/process_checkout.php:125` is MySQL syntax. Postgres needs `NOW() - INTERVAL '5 seconds'`.
   - `GROUP_CONCAT` in `orders.php:35` doesn't exist in Postgres. Use `STRING_AGG`.
   - `lastInsertId()` without a sequence name is unreliable on Postgres. Use `INSERT ... RETURNING id` instead.
   - `sql/souvenir_shop.sql` is still a MySQL dump (`ENGINE=InnoDB`).

## Security

3. **No CSRF protection anywhere.** `admin/delete_product.php` deletes a product on a plain GET, so a malicious link can do it for a logged-in admin.
4. **Uploads in `admin/add_product.php` and `edit_product.php` are only checked by file extension**, not by content.
5. **Database errors are shown to users:** `config.php` prints connection errors with `die()`, and checkout puts exception messages into error text and URLs.
6. **Open redirects** from `HTTP_REFERER` and `?redirect=`.

## Bugs

7. **Checkout can sell more than you have in stock.** Stock is checked before the transaction without locking the rows, so two orders at once can oversell.
8. **Negative quantities pass straight through:** `add_to_cart` accepts `quantity=-5`, and direct checkout doesn't cast quantity to a number.
9. **Direct checkout saves empty addresses:** `api/process_direct_checkout.php` fills shipping details from session values that are never set.
10. **Checkout logic is duplicated** across `checkout.php` and two API files.
11. **The README is out of date.** It still says MySQL and lists an `admin/login.php` that doesn't exist.

## Good

Every query uses prepared statements (no SQL injection), passwords are hashed with `password_hash`, the session ID is regenerated on login, and order cancellation checks that the user owns the order.

## Fix first

Change the database password, then finish the Postgres query fixes (item 2).
