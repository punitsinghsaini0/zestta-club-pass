<?php
require __DIR__ . '/../application/config/database.php';

$config = $config['database'] ?? null;

if (!$config) {
    fwrite(STDERR, "Database configuration not found.\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['hostname'], $config['database'], $config['char_set']);
$options = $config['options'] ?? [];
$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
$options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
$options[PDO::ATTR_EMULATE_PREPARES] = false;
$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci';

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
} catch (PDOException $e) {
    fwrite(STDERR, 'Connection failed: ' . $e->getMessage() . "\n");
    exit(1);
}

$pdo->exec('SET FOREIGN_KEY_CHECKS=0');

$queries = [
    'CREATE TABLE IF NOT EXISTS corporates (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        domain VARCHAR(191) NOT NULL UNIQUE,
        sso_enabled TINYINT(1) NOT NULL DEFAULT 0,
        brand_primary VARCHAR(7) DEFAULT NULL,
        brand_accent VARCHAR(7) DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        corporate_id INT UNSIGNED NOT NULL,
        email VARCHAR(191) NOT NULL UNIQUE,
        phone VARCHAR(20) DEFAULT NULL,
        full_name VARCHAR(191) NOT NULL,
        role ENUM("employee", "admin", "superadmin") NOT NULL DEFAULT "employee",
        pass_tier ENUM("basic", "premium", "elite") NOT NULL DEFAULT "basic",
        password_hash VARCHAR(255) NOT NULL,
        otp_code VARCHAR(10) DEFAULT NULL,
        otp_expires_at DATETIME DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_users_corporate FOREIGN KEY (corporate_id) REFERENCES corporates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS wallets (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL UNIQUE,
        balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        points INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS wallet_txns (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        type ENUM("credit_alloc", "debit_redeem", "adjust") NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        meta JSON DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_wallet_txns_user_id (user_id),
        CONSTRAINT fk_wallet_txns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS vendors (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        code VARCHAR(64) NOT NULL UNIQUE,
        integration ENUM("static", "api") NOT NULL DEFAULT "static",
        status ENUM("active", "inactive") NOT NULL DEFAULT "active"
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS offers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT UNSIGNED NOT NULL,
        title VARCHAR(191) NOT NULL,
        description TEXT,
        category ENUM("health", "gifting", "dining", "travel", "concierge", "finance", "learning") NOT NULL,
        tier_access SET("basic", "premium", "elite") NOT NULL,
        price_credits INT UNSIGNED NOT NULL,
        redemption_type ENUM("voucher", "link", "code", "api") NOT NULL,
        redemption_meta JSON DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_offers_vendor FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS redemptions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        offer_id INT UNSIGNED NOT NULL,
        status ENUM("pending", "approved", "fulfilled", "failed", "cancelled") NOT NULL DEFAULT "pending",
        partner_ref VARCHAR(191) DEFAULT NULL,
        voucher_code VARCHAR(191) DEFAULT NULL,
        amount_credits INT UNSIGNED NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_redemptions_user_id (user_id),
        INDEX idx_redemptions_offer_id (offer_id),
        CONSTRAINT fk_redemptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_redemptions_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS campaigns (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        corporate_id INT UNSIGNED NOT NULL,
        name VARCHAR(191) NOT NULL,
        kind ENUM("festival", "birthday", "anniversary", "milestone") NOT NULL,
        message TEXT,
        budget_per_user DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_campaigns_corporate FOREIGN KEY (corporate_id) REFERENCES corporates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS campaign_recipients (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        status ENUM("queued", "sent", "redeemed") NOT NULL DEFAULT "queued",
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_campaign_user (campaign_id, user_id),
        CONSTRAINT fk_campaign_recipients_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
        CONSTRAINT fk_campaign_recipients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

    'CREATE TABLE IF NOT EXISTS analytics_daily (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        corporate_id INT UNSIGNED NOT NULL,
        total_redemptions INT UNSIGNED NOT NULL DEFAULT 0,
        total_credits_spent DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        active_users INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_analytics_date_corp (date, corporate_id),
        CONSTRAINT fk_analytics_corporate FOREIGN KEY (corporate_id) REFERENCES corporates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
];

foreach ($queries as $sql) {
    $pdo->exec($sql);
}

$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

echo "Migration completed successfully.\n";
