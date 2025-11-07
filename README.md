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
