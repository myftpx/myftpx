<?php
namespace App\Core;

class Schema
{
    public static function install(\PDO $db): void
    {
        $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $autoinc = $driver === 'mysql' ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $tables = [];

        $tables['settings'] = "CREATE TABLE IF NOT EXISTS settings (name VARCHAR(100) PRIMARY KEY, value TEXT)";
        $tables['admins'] = "CREATE TABLE IF NOT EXISTS admins (id {$autoinc}, name VARCHAR(150) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, role VARCHAR(50) DEFAULT 'admin', last_login DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['users'] = "CREATE TABLE IF NOT EXISTS users (id {$autoinc}, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT '', company VARCHAR(150) DEFAULT '', address TEXT, city VARCHAR(100) DEFAULT '', country VARCHAR(100) DEFAULT '', postal_code VARCHAR(30) DEFAULT '', balance DECIMAL(15,2) DEFAULT 0, currency VARCHAR(10) DEFAULT 'TRY', status VARCHAR(20) DEFAULT 'active', email_verified TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['products'] = "CREATE TABLE IF NOT EXISTS products (id {$autoinc}, name VARCHAR(190) NOT NULL, slug VARCHAR(190) NOT NULL UNIQUE, description TEXT, category VARCHAR(100) DEFAULT '', type VARCHAR(50) DEFAULT 'hosting', price DECIMAL(15,2) DEFAULT 0, setup_fee DECIMAL(15,2) DEFAULT 0, billing_cycle VARCHAR(20) DEFAULT 'monthly', status VARCHAR(20) DEFAULT 'active', module VARCHAR(50) DEFAULT 'none', config TEXT, sort_order INT DEFAULT 0, featured TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['product_config_options'] = "CREATE TABLE IF NOT EXISTS product_config_options (id {$autoinc}, product_id INT NOT NULL, name VARCHAR(190) NOT NULL, type VARCHAR(50) DEFAULT 'dropdown', options TEXT, required TINYINT DEFAULT 0, sort_order INT DEFAULT 0)";
        $tables['orders'] = "CREATE TABLE IF NOT EXISTS orders (id {$autoinc}, order_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, product_id INT NOT NULL, billing_cycle VARCHAR(20) DEFAULT 'monthly', domain VARCHAR(190) DEFAULT '', config TEXT, amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'pending', payment_method VARCHAR(50) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['services'] = "CREATE TABLE IF NOT EXISTS services (id {$autoinc}, user_id INT NOT NULL, product_id INT NOT NULL, domain VARCHAR(190) DEFAULT '', username VARCHAR(190) DEFAULT '', password TEXT, config TEXT, amount DECIMAL(15,2) DEFAULT 0, billing_cycle VARCHAR(20) DEFAULT 'monthly', status VARCHAR(20) DEFAULT 'pending', next_due_date DATE NULL, auto_renew TINYINT DEFAULT 0, card_id INT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['domains'] = "CREATE TABLE IF NOT EXISTS domains (id {$autoinc}, user_id INT NOT NULL, domain VARCHAR(190) NOT NULL, registrar VARCHAR(100) DEFAULT '', tld VARCHAR(20) DEFAULT '', registration_period INT DEFAULT 1, status VARCHAR(20) DEFAULT 'active', expiry_date DATE NULL, dns TEXT, nameservers TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['invoices'] = "CREATE TABLE IF NOT EXISTS invoices (id {$autoinc}, invoice_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, amount DECIMAL(15,2) DEFAULT 0, tax DECIMAL(15,2) DEFAULT 0, total DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'unpaid', due_date DATE NULL, notes TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, paid_at DATETIME NULL)";
        $tables['invoice_items'] = "CREATE TABLE IF NOT EXISTS invoice_items (id {$autoinc}, invoice_id INT NOT NULL, description VARCHAR(255) NOT NULL, amount DECIMAL(15,2) DEFAULT 0)";
        $tables['transactions'] = "CREATE TABLE IF NOT EXISTS transactions (id {$autoinc}, invoice_id INT NULL, user_id INT NOT NULL, amount DECIMAL(15,2) DEFAULT 0, fee DECIMAL(15,2) DEFAULT 0, gateway VARCHAR(50) DEFAULT '', transaction_id VARCHAR(190) DEFAULT '', status VARCHAR(20) DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['tickets'] = "CREATE TABLE IF NOT EXISTS tickets (id {$autoinc}, ticket_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, subject VARCHAR(255) NOT NULL, department VARCHAR(100) DEFAULT 'Destek', priority VARCHAR(20) DEFAULT 'medium', status VARCHAR(20) DEFAULT 'open', last_reply_at DATETIME DEFAULT CURRENT_TIMESTAMP, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['ticket_replies'] = "CREATE TABLE IF NOT EXISTS ticket_replies (id {$autoinc}, ticket_id INT NOT NULL, user_id INT NULL, is_admin TINYINT DEFAULT 0, message TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['api_keys'] = "CREATE TABLE IF NOT EXISTS api_keys (id {$autoinc}, user_id INT NOT NULL, name VARCHAR(190) NOT NULL, api_key VARCHAR(255) NOT NULL UNIQUE, auth_key VARCHAR(255) NOT NULL, allowed_ips TEXT, permissions TEXT, status VARCHAR(20) DEFAULT 'active', last_used_at DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['payment_gateways'] = "CREATE TABLE IF NOT EXISTS payment_gateways (id {$autoinc}, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL UNIQUE, enabled TINYINT DEFAULT 0, config TEXT, sort_order INT DEFAULT 0)";
        $tables['email_templates'] = "CREATE TABLE IF NOT EXISTS email_templates (id {$autoinc}, code VARCHAR(100) NOT NULL UNIQUE, subject VARCHAR(255) DEFAULT '', body TEXT, enabled TINYINT DEFAULT 1)";
        $tables['activity_log'] = "CREATE TABLE IF NOT EXISTS activity_log (id {$autoinc}, user_id INT NULL, admin_id INT NULL, action VARCHAR(255) NOT NULL, description TEXT, ip VARCHAR(60) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['modules'] = "CREATE TABLE IF NOT EXISTS modules (id {$autoinc}, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL UNIQUE, type VARCHAR(50) DEFAULT 'addon', enabled TINYINT DEFAULT 0, config TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['saved_cards'] = "CREATE TABLE IF NOT EXISTS saved_cards (id {$autoinc}, user_id INT NOT NULL, gateway VARCHAR(50) NOT NULL, card_token VARCHAR(255) NOT NULL, card_user_key VARCHAR(255) DEFAULT '', last4 VARCHAR(8) DEFAULT '', brand VARCHAR(30) DEFAULT '', holder_name VARCHAR(190) DEFAULT '', expiry_month VARCHAR(2) DEFAULT '', expiry_year VARCHAR(4) DEFAULT '', is_default TINYINT DEFAULT 0, status VARCHAR(20) DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['payment_logs'] = "CREATE TABLE IF NOT EXISTS payment_logs (id {$autoinc}, user_id INT NULL, invoice_id INT NULL, gateway VARCHAR(50) DEFAULT '', action VARCHAR(100) NOT NULL, reference VARCHAR(190) DEFAULT '', amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'info', message TEXT, ip VARCHAR(60) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['bank_accounts'] = "CREATE TABLE IF NOT EXISTS bank_accounts (id {$autoinc}, bank_name VARCHAR(150) NOT NULL, account_holder VARCHAR(190) DEFAULT '', iban VARCHAR(40) DEFAULT '', account_no VARCHAR(40) DEFAULT '', branch_code VARCHAR(40) DEFAULT '', is_active TINYINT DEFAULT 1, sort_order INT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";

        foreach ($tables as $sql) {
            $db->exec($sql);
        }
    }
    public static function seed(\PDO $db, array $data): void
    {
        $stmt = $db->prepare('INSERT INTO settings (name, value) VALUES (?, ?)');
        $settings = [
            ['site_name', $data['site_name'] ?? 'RCVXTR'],
            ['theme', 'rcvxtrwhite'],
            ['currency', 'TRY'],
            ['admin_email', $data['email'] ?? ''],
            ['tax_rate', '20'],
            ['invoice_prefix', 'RCVXTR-'],
            ['default_language', 'tr'],
            ['api_enabled', '1'],
            ['allow_registration', '1'],
            ['maintenance_mode', '0'],
            ['cron_secret', bin2hex(random_bytes(16))],
        ];
        foreach ($settings as $s) {
            $stmt->execute($s);
        }

        $stmt = $db->prepare('INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['name'] ?? 'Admin',
            $data['email'] ?? 'admin@example.com',
            password_hash($data['password'] ?? 'admin123', PASSWORD_DEFAULT),
            'admin',
        ]);

        $stmt = $db->prepare('INSERT INTO payment_gateways (name, code, enabled, config) VALUES (?, ?, ?, ?)');
        $gateways = [
            ['PayTR', 'paytr', 0, json_encode(['merchant_id' => '', 'merchant_key' => '', 'merchant_salt' => '', 'api_mode' => 'test', 'currency' => 'TL'])],
            ['iyzico', 'iyzico', 0, json_encode(['api_key' => '', 'secret_key' => '', 'api_mode' => 'sandbox', 'currency' => 'TRY'])],
            ['Havale / EFT (Banka Transferi)', 'bank_transfer', 1, json_encode(['instruction' => 'Ödemenizi aşağıdaki hesaba yaptıktan sonra "Ödeme Yaptım" butonuna tıklayın.'])],
        ];
        foreach ($gateways as $g) {
            $stmt->execute($g);
        }

        $stmt = $db->prepare('INSERT INTO modules (name, code, type, enabled, config) VALUES (?, ?, ?, ?, ?)');
        $modules = [
            ['cPanel / WHM', 'cpanel', 'server', 0, '{}'],
            ['Plesk', 'plesk', 'server', 0, '{}'],
            ['Domain Registrar', 'domain_registrar', 'registrar', 1, '{}'],
            ['Mail / SMTP', 'smtp', 'addon', 0, '{}'],
        ];
        foreach ($modules as $m) {
            $stmt->execute($m);
        }
    }

    /**
     * Idempotent migration: adds any tables/columns/gateways missing in existing installs.
     */
    public static function migrate(\PDO $db): void
    {
        $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $autoinc = $driver === 'mysql' ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';

        // New tables
        $tables = [
            'saved_cards' => "CREATE TABLE IF NOT EXISTS saved_cards (id {$autoinc}, user_id INT NOT NULL, gateway VARCHAR(50) NOT NULL, card_token VARCHAR(255) NOT NULL, card_user_key VARCHAR(255) DEFAULT '', last4 VARCHAR(8) DEFAULT '', brand VARCHAR(30) DEFAULT '', holder_name VARCHAR(190) DEFAULT '', expiry_month VARCHAR(2) DEFAULT '', expiry_year VARCHAR(4) DEFAULT '', is_default TINYINT DEFAULT 0, status VARCHAR(20) DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'payment_logs' => "CREATE TABLE IF NOT EXISTS payment_logs (id {$autoinc}, user_id INT NULL, invoice_id INT NULL, gateway VARCHAR(50) DEFAULT '', action VARCHAR(100) NOT NULL, reference VARCHAR(190) DEFAULT '', amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'info', message TEXT, ip VARCHAR(60) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'bank_accounts' => "CREATE TABLE IF NOT EXISTS bank_accounts (id {$autoinc}, bank_name VARCHAR(150) NOT NULL, account_holder VARCHAR(190) DEFAULT '', iban VARCHAR(40) DEFAULT '', account_no VARCHAR(40) DEFAULT '', branch_code VARCHAR(40) DEFAULT '', is_active TINYINT DEFAULT 1, sort_order INT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        ];
        foreach ($tables as $sql) {
            $db->exec($sql);
        }

        // Missing columns on services
        $cols = self::columns($db, 'services');
        if (!in_array('auto_renew', $cols, true)) {
            $db->exec('ALTER TABLE services ADD COLUMN auto_renew TINYINT DEFAULT 0');
        }
        if (!in_array('card_id', $cols, true)) {
            $db->exec('ALTER TABLE services ADD COLUMN card_id INT NULL');
        }

        // Missing gateways
        $existing = $db->query('SELECT code FROM payment_gateways')->fetchAll(\PDO::FETCH_COLUMN);
        $gateways = [
            ['PayTR', 'paytr', 0, json_encode(['merchant_id' => '', 'merchant_key' => '', 'merchant_salt' => '', 'api_mode' => 'test', 'currency' => 'TL'])],
            ['iyzico', 'iyzico', 0, json_encode(['api_key' => '', 'secret_key' => '', 'api_mode' => 'sandbox', 'currency' => 'TRY'])],
        ];
        $stmt = $db->prepare('INSERT INTO payment_gateways (name, code, enabled, config) VALUES (?, ?, ?, ?)');
        foreach ($gateways as $g) {
            if (!in_array($g[1], $existing, true)) {
                $stmt->execute($g);
            }
        }

        // Missing cron_secret setting
        $stmt = $db->prepare('SELECT COUNT(*) FROM settings WHERE name = ?');
        $stmt->execute(['cron_secret']);
        if ((int)$stmt->fetchColumn() === 0) {
            $db->prepare('INSERT INTO settings (name, value) VALUES (?, ?)')->execute(['cron_secret', bin2hex(random_bytes(16))]);
        }
    }

    private static function columns(\PDO $db, string $table): array
    {
        $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $rows = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
            return array_column($rows, 'Field');
        }
        $rows = $db->query("PRAGMA table_info(`{$table}`)")->fetchAll();
        return array_column($rows, 'name');
    }
}
