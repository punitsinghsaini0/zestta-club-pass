# AGENTS.md — Build Instructions for Codex

## Tech
- PHP 7.4+, MySQL 8
- Framework: CodeIgniter 3 (CI3)
- Webroot: `/public`
- CSRF enabled; use CI Query Builder

## Must Deliver (MVP)
1) Auth (email/password for now), roles: employee, admin, superadmin
2) Wallet: balance + transactions (credit/debit)
3) Offers/Benefits: category, tier_access (basic,premium,elite), price_credits, redemption_meta JSON
4) Redemption flow: debit wallet → issue voucher from `static_codes` → success page
5) Admin: employees CRUD (assign tier, allocate credits), vendors CRUD, offers CRUD
6) Basic analytics rollup script (daily totals)

## Data Model
- corporates, users, wallets, wallet_txns, vendors, offers, redemptions, campaigns, campaign_recipients, analytics_daily

## Dev Commands
- Serve: `php -S localhost:8080 -t public`
- Migrate/seed (PHP scripts in `/scripts`):  
  `php scripts/migrate.php` then `php scripts/seed.php`
- Lint: `php -l $(git ls-files '*.php')`

## Test User (seed)
- admin@zestta.com / Admin@123

## Acceptance
- Employee can log in, see wallet, view tiered offers, redeem one to get a code
- Admin can add vendor, create offer with `static_codes`, create employee, allocate credits
- Wallet history updates, redemption recorded
