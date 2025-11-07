# Zestta Club Pass (MVP)

Corporate benefits platform with credits wallet, pass tiers (Basic/Premium/Elite), offers/vouchers, redemption, admin panel, and simple analytics.

**Stack:** PHP + CodeIgniter 3, MySQL  
**Goal:** Working MVP that an employee can log into, view wallet, browse tiered offers, redeem a voucher (static code pool), and an admin can manage employees, credits, vendors, and offers.

Run target: local dev on PHP 7.4+ with MySQL.

## Getting Started

1. Install PHP 7.4+ with PDO MySQL extension.
2. Serve the application from the `public/` webroot:
   ```bash
   php -S localhost:8080 -t public
   ```
3. Visit http://localhost:8080 in your browser.

Configuration lives under `application/config/` and has CSRF and global XSS protection enabled by default.

## Database Setup

1. Create a MySQL 8 database and user that match `application/config/database.php` (defaults: database `zestta`, host `127.0.0.1`, user `root`, empty password). Update that file if you prefer different credentials.
2. Run the migrations to create all required tables with UTF-8 support:
   ```bash
   php scripts/migrate.php
   ```
3. Seed demo data, including the Zestta corporate tenant and superadmin login (`admin@zestta.com` / `Admin@123`):
   ```bash
   php scripts/seed.php
   ```
4. Start the PHP development server and sign in with the seeded credentials to explore the app.
