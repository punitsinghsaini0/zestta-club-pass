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

try {
    $pdo->beginTransaction();

    // Ensure demo corporate exists
    $domain = 'zestta.com';
    $stmt = $pdo->prepare('SELECT id FROM corporates WHERE domain = :domain');
    $stmt->execute(['domain' => $domain]);
    $corporateId = $stmt->fetchColumn();

    if (!$corporateId) {
        $stmt = $pdo->prepare('INSERT INTO corporates (name, domain, sso_enabled, brand_primary, brand_accent) VALUES (:name, :domain, :sso, :primary, :accent)');
        $stmt->execute([
            'name'    => 'Zestta Demo Inc.',
            'domain'  => $domain,
            'sso'     => 0,
            'primary' => '#1F6FEB',
            'accent'  => '#FF7B72',
        ]);
        $corporateId = (int) $pdo->lastInsertId();
    } else {
        $corporateId = (int) $corporateId;
    }

    // Ensure superadmin user exists
    $email = 'admin@zestta.com';
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        $passwordHash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (corporate_id, email, phone, full_name, role, pass_tier, password_hash, is_active) VALUES (:corporate_id, :email, :phone, :full_name, :role, :pass_tier, :password_hash, :is_active)');
        $stmt->execute([
            'corporate_id' => $corporateId,
            'email'        => $email,
            'phone'        => '+1-202-555-0188',
            'full_name'    => 'Zestta Super Admin',
            'role'         => 'superadmin',
            'pass_tier'    => 'elite',
            'password_hash'=> $passwordHash,
            'is_active'    => 1,
        ]);
        $userId = (int) $pdo->lastInsertId();
    } else {
        $userId = (int) $userId;
    }

    // Ensure wallet exists and seed balance
    $stmt = $pdo->prepare('SELECT id, balance, points FROM wallets WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    $wallet = $stmt->fetch();

    $targetBalance = 500.00;
    $targetPoints = 500;

    if ($wallet) {
        $stmt = $pdo->prepare('UPDATE wallets SET balance = :balance, points = :points WHERE user_id = :user_id');
        $stmt->execute([
            'balance' => $targetBalance,
            'points'  => $targetPoints,
            'user_id' => $userId,
        ]);
        $walletId = (int) $wallet['id'];
    } else {
        $stmt = $pdo->prepare('INSERT INTO wallets (user_id, balance, points) VALUES (:user_id, :balance, :points)');
        $stmt->execute([
            'user_id' => $userId,
            'balance' => $targetBalance,
            'points'  => $targetPoints,
        ]);
        $walletId = (int) $pdo->lastInsertId();
    }

    // Seed wallet transactions (idempotent based on meta keys)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wallet_txns WHERE user_id = :user_id AND type = :type AND JSON_EXTRACT(meta, "$.seed") = :seed');
    $stmt->execute([
        'user_id' => $userId,
        'type'    => 'credit_alloc',
        'seed'    => 'initial_credit',
    ]);
    $hasCredit = (int) $stmt->fetchColumn() > 0;

    if (!$hasCredit) {
        $stmt = $pdo->prepare('INSERT INTO wallet_txns (user_id, type, amount, meta) VALUES (:user_id, :type, :amount, :meta)');
        $stmt->execute([
            'user_id' => $userId,
            'type'    => 'credit_alloc',
            'amount'  => 1000.00,
            'meta'    => json_encode(['seed' => 'initial_credit', 'note' => 'Initial credit allocation']),
        ]);
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM wallet_txns WHERE user_id = :user_id AND type = :type AND JSON_EXTRACT(meta, "$.seed") = :seed');
    $stmt->execute([
        'user_id' => $userId,
        'type'    => 'debit_redeem',
        'seed'    => 'sample_redemption',
    ]);
    $hasDebit = (int) $stmt->fetchColumn() > 0;

    // Ensure vendor
    $stmt = $pdo->prepare('SELECT id FROM vendors WHERE code = :code');
    $stmt->execute(['code' => 'WELLNESS']);
    $vendorId = $stmt->fetchColumn();

    if (!$vendorId) {
        $stmt = $pdo->prepare('INSERT INTO vendors (name, code, integration, status) VALUES (:name, :code, :integration, :status)');
        $stmt->execute([
            'name'        => 'Zestta Wellness Partners',
            'code'        => 'WELLNESS',
            'integration' => 'static',
            'status'      => 'active',
        ]);
        $vendorId = (int) $pdo->lastInsertId();
    } else {
        $vendorId = (int) $vendorId;
    }

    // Ensure offer
    $stmt = $pdo->prepare('SELECT id FROM offers WHERE title = :title AND vendor_id = :vendor_id');
    $stmt->execute([
        'title'     => 'Premium Spa Day',
        'vendor_id' => $vendorId,
    ]);
    $offerId = $stmt->fetchColumn();

    if (!$offerId) {
        $stmt = $pdo->prepare('INSERT INTO offers (vendor_id, title, description, category, tier_access, price_credits, redemption_type, redemption_meta, is_active) VALUES (:vendor_id, :title, :description, :category, :tier_access, :price_credits, :redemption_type, :redemption_meta, :is_active)');
        $stmt->execute([
            'vendor_id'       => $vendorId,
            'title'           => 'Premium Spa Day',
            'description'     => 'Relaxing spa experience redeemable across partner locations.',
            'category'        => 'health',
            'tier_access'     => 'basic,premium,elite',
            'price_credits'   => 500,
            'redemption_type' => 'voucher',
            'redemption_meta' => json_encode(['instructions' => 'Show voucher code at the spa reception.']),
            'is_active'       => 1,
        ]);
        $offerId = (int) $pdo->lastInsertId();
    } else {
        $offerId = (int) $offerId;
    }

    if (!$hasDebit) {
        $stmt = $pdo->prepare('INSERT INTO wallet_txns (user_id, type, amount, meta) VALUES (:user_id, :type, :amount, :meta)');
        $stmt->execute([
            'user_id' => $userId,
            'type'    => 'debit_redeem',
            'amount'  => 500.00,
            'meta'    => json_encode(['seed' => 'sample_redemption', 'offer_id' => $offerId]),
        ]);
    }

    // Ensure redemption record
    $stmt = $pdo->prepare('SELECT id FROM redemptions WHERE user_id = :user_id AND offer_id = :offer_id AND status = :status');
    $stmt->execute([
        'user_id' => $userId,
        'offer_id'=> $offerId,
        'status'  => 'fulfilled',
    ]);
    $redemptionId = $stmt->fetchColumn();

    if (!$redemptionId) {
        $stmt = $pdo->prepare('INSERT INTO redemptions (user_id, offer_id, status, partner_ref, voucher_code, amount_credits) VALUES (:user_id, :offer_id, :status, :partner_ref, :voucher_code, :amount_credits)');
        $stmt->execute([
            'user_id'       => $userId,
            'offer_id'      => $offerId,
            'status'        => 'fulfilled',
            'partner_ref'   => 'SEED-REF-001',
            'voucher_code'  => 'ZEST-SPA-2024',
            'amount_credits'=> 500,
        ]);
        $redemptionId = (int) $pdo->lastInsertId();
    }

    // Ensure campaign exists
    $stmt = $pdo->prepare('SELECT id FROM campaigns WHERE corporate_id = :corporate_id AND name = :name');
    $stmt->execute([
        'corporate_id' => $corporateId,
        'name'         => 'Welcome Festival',
    ]);
    $campaignId = $stmt->fetchColumn();

    if (!$campaignId) {
        $stmt = $pdo->prepare('INSERT INTO campaigns (corporate_id, name, kind, message, budget_per_user, active) VALUES (:corporate_id, :name, :kind, :message, :budget_per_user, :active)');
        $stmt->execute([
            'corporate_id'    => $corporateId,
            'name'            => 'Welcome Festival',
            'kind'            => 'festival',
            'message'         => 'Celebrate with a complimentary spa day.',
            'budget_per_user' => 500.00,
            'active'          => 1,
        ]);
        $campaignId = (int) $pdo->lastInsertId();
    } else {
        $campaignId = (int) $campaignId;
    }

    // Ensure campaign recipient includes the superadmin for demo
    $stmt = $pdo->prepare('SELECT id FROM campaign_recipients WHERE campaign_id = :campaign_id AND user_id = :user_id');
    $stmt->execute([
        'campaign_id' => $campaignId,
        'user_id'     => $userId,
    ]);
    $recipientId = $stmt->fetchColumn();

    if (!$recipientId) {
        $stmt = $pdo->prepare('INSERT INTO campaign_recipients (campaign_id, user_id, status) VALUES (:campaign_id, :user_id, :status)');
        $stmt->execute([
            'campaign_id' => $campaignId,
            'user_id'     => $userId,
            'status'      => 'sent',
        ]);
    }

    // Ensure analytics snapshot for today
    $today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
    $stmt = $pdo->prepare('SELECT id FROM analytics_daily WHERE date = :date AND corporate_id = :corporate_id');
    $stmt->execute([
        'date'         => $today,
        'corporate_id' => $corporateId,
    ]);
    $analyticsId = $stmt->fetchColumn();

    if (!$analyticsId) {
        $stmt = $pdo->prepare('INSERT INTO analytics_daily (date, corporate_id, total_redemptions, total_credits_spent, active_users) VALUES (:date, :corporate_id, :total_redemptions, :total_credits_spent, :active_users)');
        $stmt->execute([
            'date'                => $today,
            'corporate_id'        => $corporateId,
            'total_redemptions'   => 1,
            'total_credits_spent' => 500.00,
            'active_users'        => 1,
        ]);
    }

    $pdo->commit();
    echo "Seed data inserted successfully.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Seeding failed: ' . $e->getMessage() . "\n");
    exit(1);
}
