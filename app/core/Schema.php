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
        $tables['users'] = "CREATE TABLE IF NOT EXISTS users (id {$autoinc}, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT '', company VARCHAR(150) DEFAULT '', account_type VARCHAR(20) DEFAULT 'individual', tc_no VARCHAR(20) DEFAULT '', tax_no VARCHAR(20) DEFAULT '', address TEXT, city VARCHAR(100) DEFAULT '', country VARCHAR(100) DEFAULT '', postal_code VARCHAR(30) DEFAULT '', balance DECIMAL(15,2) DEFAULT 0, currency VARCHAR(10) DEFAULT 'TRY', status VARCHAR(20) DEFAULT 'active', email_verified TINYINT DEFAULT 0, verified TINYINT DEFAULT 0, referral_code VARCHAR(20) DEFAULT '', referred_by INT NULL, affiliate TINYINT DEFAULT 0, affiliate_balance DECIMAL(15,2) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['products'] = "CREATE TABLE IF NOT EXISTS products (id {$autoinc}, name VARCHAR(190) NOT NULL, slug VARCHAR(190) NOT NULL UNIQUE, description TEXT, category VARCHAR(100) DEFAULT '', type VARCHAR(50) DEFAULT 'hosting', price DECIMAL(15,2) DEFAULT 0, setup_fee DECIMAL(15,2) DEFAULT 0, billing_cycle VARCHAR(20) DEFAULT 'monthly', status VARCHAR(20) DEFAULT 'active', module VARCHAR(50) DEFAULT 'none', config TEXT, sort_order INT DEFAULT 0, featured TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['product_config_options'] = "CREATE TABLE IF NOT EXISTS product_config_options (id {$autoinc}, product_id INT NOT NULL, name VARCHAR(190) NOT NULL, type VARCHAR(50) DEFAULT 'dropdown', options TEXT, required TINYINT DEFAULT 0, sort_order INT DEFAULT 0)";
        $tables['orders'] = "CREATE TABLE IF NOT EXISTS orders (id {$autoinc}, order_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, product_id INT NOT NULL, billing_cycle VARCHAR(20) DEFAULT 'monthly', domain VARCHAR(190) DEFAULT '', config TEXT, amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'pending', payment_method VARCHAR(50) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['services'] = "CREATE TABLE IF NOT EXISTS services (id {$autoinc}, user_id INT NOT NULL, product_id INT NOT NULL, domain VARCHAR(190) DEFAULT '', username VARCHAR(190) DEFAULT '', password TEXT, config TEXT, amount DECIMAL(15,2) DEFAULT 0, billing_cycle VARCHAR(20) DEFAULT 'monthly', status VARCHAR(20) DEFAULT 'pending', next_due_date DATE NULL, auto_renew TINYINT DEFAULT 0, card_id INT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['domains'] = "CREATE TABLE IF NOT EXISTS domains (id {$autoinc}, user_id INT NOT NULL, domain VARCHAR(190) NOT NULL, registrar VARCHAR(100) DEFAULT '', tld VARCHAR(20) DEFAULT '', registration_period INT DEFAULT 1, status VARCHAR(20) DEFAULT 'active', expiry_date DATE NULL, dns TEXT, nameservers TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['invoices'] = "CREATE TABLE IF NOT EXISTS invoices (id {$autoinc}, invoice_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, amount DECIMAL(15,2) DEFAULT 0, tax DECIMAL(15,2) DEFAULT 0, discount DECIMAL(15,2) DEFAULT 0, promo_code VARCHAR(50) DEFAULT '', total DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'unpaid', due_date DATE NULL, notes TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, paid_at DATETIME NULL)";
        $tables['invoice_items'] = "CREATE TABLE IF NOT EXISTS invoice_items (id {$autoinc}, invoice_id INT NOT NULL, description VARCHAR(255) NOT NULL, amount DECIMAL(15,2) DEFAULT 0)";
        $tables['transactions'] = "CREATE TABLE IF NOT EXISTS transactions (id {$autoinc}, invoice_id INT NULL, user_id INT NOT NULL, amount DECIMAL(15,2) DEFAULT 0, fee DECIMAL(15,2) DEFAULT 0, gateway VARCHAR(50) DEFAULT '', transaction_id VARCHAR(190) DEFAULT '', status VARCHAR(20) DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['tickets'] = "CREATE TABLE IF NOT EXISTS tickets (id {$autoinc}, ticket_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, subject VARCHAR(255) NOT NULL, department VARCHAR(100) DEFAULT 'Destek', priority VARCHAR(20) DEFAULT 'medium', status VARCHAR(20) DEFAULT 'open', rating TINYINT DEFAULT 0, rating_comment TEXT, last_reply_at DATETIME DEFAULT CURRENT_TIMESTAMP, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['ticket_replies'] = "CREATE TABLE IF NOT EXISTS ticket_replies (id {$autoinc}, ticket_id INT NOT NULL, user_id INT NULL, is_admin TINYINT DEFAULT 0, message TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['api_keys'] = "CREATE TABLE IF NOT EXISTS api_keys (id {$autoinc}, user_id INT NOT NULL, name VARCHAR(190) NOT NULL, api_key VARCHAR(255) NOT NULL UNIQUE, auth_key VARCHAR(255) NOT NULL, allowed_ips TEXT, permissions TEXT, status VARCHAR(20) DEFAULT 'active', last_used_at DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['payment_gateways'] = "CREATE TABLE IF NOT EXISTS payment_gateways (id {$autoinc}, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL UNIQUE, enabled TINYINT DEFAULT 0, config TEXT, sort_order INT DEFAULT 0)";
        $tables['email_templates'] = "CREATE TABLE IF NOT EXISTS email_templates (id {$autoinc}, code VARCHAR(100) NOT NULL UNIQUE, subject VARCHAR(255) DEFAULT '', body TEXT, enabled TINYINT DEFAULT 1)";
        $tables['activity_log'] = "CREATE TABLE IF NOT EXISTS activity_log (id {$autoinc}, user_id INT NULL, admin_id INT NULL, action VARCHAR(255) NOT NULL, description TEXT, ip VARCHAR(60) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['modules'] = "CREATE TABLE IF NOT EXISTS modules (id {$autoinc}, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL UNIQUE, type VARCHAR(50) DEFAULT 'addon', enabled TINYINT DEFAULT 0, config TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['saved_cards'] = "CREATE TABLE IF NOT EXISTS saved_cards (id {$autoinc}, user_id INT NOT NULL, gateway VARCHAR(50) NOT NULL, card_token VARCHAR(255) NOT NULL, card_user_key VARCHAR(255) DEFAULT '', last4 VARCHAR(8) DEFAULT '', brand VARCHAR(30) DEFAULT '', holder_name VARCHAR(190) DEFAULT '', expiry_month VARCHAR(2) DEFAULT '', expiry_year VARCHAR(4) DEFAULT '', is_default TINYINT DEFAULT 0, status VARCHAR(20) DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['payment_logs'] = "CREATE TABLE IF NOT EXISTS payment_logs (id {$autoinc}, user_id INT NULL, invoice_id INT NULL, gateway VARCHAR(50) DEFAULT '', action VARCHAR(100) NOT NULL, reference VARCHAR(190) DEFAULT '', amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'info', message TEXT, ip VARCHAR(60) DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['bank_accounts'] = "CREATE TABLE IF NOT EXISTS bank_accounts (id {$autoinc}, bank_name VARCHAR(150) NOT NULL, account_holder VARCHAR(190) DEFAULT '', iban VARCHAR(40) DEFAULT '', account_no VARCHAR(40) DEFAULT '', branch_code VARCHAR(40) DEFAULT '', is_active TINYINT DEFAULT 1, sort_order INT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['announcements'] = "CREATE TABLE IF NOT EXISTS announcements (id {$autoinc}, title VARCHAR(255) NOT NULL, body TEXT, status TINYINT DEFAULT 1, published_at DATETIME DEFAULT CURRENT_TIMESTAMP, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['kb_categories'] = "CREATE TABLE IF NOT EXISTS kb_categories (id {$autoinc}, name VARCHAR(190) NOT NULL, description TEXT, sort_order INT DEFAULT 0)";
        $tables['kb_articles'] = "CREATE TABLE IF NOT EXISTS kb_articles (id {$autoinc}, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, body TEXT, views INT DEFAULT 0, status TINYINT DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL)";
        $tables['tld_pricing'] = "CREATE TABLE IF NOT EXISTS tld_pricing (id {$autoinc}, tld VARCHAR(30) NOT NULL UNIQUE, register_price DECIMAL(15,2) DEFAULT 0, transfer_price DECIMAL(15,2) DEFAULT 0, renew_price DECIMAL(15,2) DEFAULT 0, status TINYINT DEFAULT 1)";
        $tables['addons'] = "CREATE TABLE IF NOT EXISTS addons (id {$autoinc}, name VARCHAR(190) NOT NULL, description TEXT, price DECIMAL(15,2) DEFAULT 0, billing_cycle VARCHAR(20) DEFAULT 'monthly', status TINYINT DEFAULT 1)";
        $tables['service_addons'] = "CREATE TABLE IF NOT EXISTS service_addons (id {$autoinc}, service_id INT NOT NULL, addon_id INT NOT NULL, status VARCHAR(20) DEFAULT 'active')";
        $tables['promotions'] = "CREATE TABLE IF NOT EXISTS promotions (id {$autoinc}, code VARCHAR(50) NOT NULL UNIQUE, discount_type VARCHAR(20) DEFAULT 'percent', discount_value DECIMAL(15,2) DEFAULT 0, applies_to VARCHAR(100) DEFAULT 'all', valid_from DATE NULL, valid_until DATE NULL, max_uses INT DEFAULT 0, used INT DEFAULT 0, status TINYINT DEFAULT 1)";
        $tables['contacts'] = "CREATE TABLE IF NOT EXISTS contacts (id {$autoinc}, user_id INT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL, password VARCHAR(255) DEFAULT '', permissions TEXT, status VARCHAR(20) DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['predefined_replies'] = "CREATE TABLE IF NOT EXISTS predefined_replies (id {$autoinc}, name VARCHAR(190) NOT NULL, body TEXT)";
        $tables['quotes'] = "CREATE TABLE IF NOT EXISTS quotes (id {$autoinc}, quote_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, amount DECIMAL(15,2) DEFAULT 0, total DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'pending', valid_until DATE NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['quote_items'] = "CREATE TABLE IF NOT EXISTS quote_items (id {$autoinc}, quote_id INT NOT NULL, description VARCHAR(255) NOT NULL, amount DECIMAL(15,2) DEFAULT 0)";
        $tables['blog_categories'] = "CREATE TABLE IF NOT EXISTS blog_categories (id {$autoinc}, name VARCHAR(190) NOT NULL, slug VARCHAR(190) DEFAULT '', sort_order INT DEFAULT 0)";
        $tables['blog_posts'] = "CREATE TABLE IF NOT EXISTS blog_posts (id {$autoinc}, title VARCHAR(255) NOT NULL, slug VARCHAR(190) NOT NULL, category_id INT DEFAULT 0, excerpt TEXT, content TEXT, image VARCHAR(255) DEFAULT '', status TINYINT DEFAULT 1, views INT DEFAULT 0, seo_title VARCHAR(190) DEFAULT '', seo_description VARCHAR(255) DEFAULT '', published_at DATETIME DEFAULT CURRENT_TIMESTAMP, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['otp_codes'] = "CREATE TABLE IF NOT EXISTS otp_codes (id {$autoinc}, email VARCHAR(190) NOT NULL, code VARCHAR(10) NOT NULL, type VARCHAR(30) DEFAULT 'email', expires_at DATETIME NOT NULL, used TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['affiliate_commissions'] = "CREATE TABLE IF NOT EXISTS affiliate_commissions (id {$autoinc}, affiliate_id INT NOT NULL, referred_user_id INT NOT NULL, invoice_id INT NULL, amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['cancellation_requests'] = "CREATE TABLE IF NOT EXISTS cancellation_requests (id {$autoinc}, service_id INT NOT NULL, user_id INT NOT NULL, type VARCHAR(30) DEFAULT 'immediate', reason TEXT, status VARCHAR(20) DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['downloads'] = "CREATE TABLE IF NOT EXISTS downloads (id {$autoinc}, name VARCHAR(190) NOT NULL, description TEXT, file_path VARCHAR(255) DEFAULT '', category VARCHAR(100) DEFAULT '', status TINYINT DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)";
        $tables['client_custom_fields'] = "CREATE TABLE IF NOT EXISTS client_custom_fields (id {$autoinc}, name VARCHAR(190) NOT NULL, field_type VARCHAR(30) DEFAULT 'text', options TEXT, required TINYINT DEFAULT 0, sort_order INT DEFAULT 0)";
        $tables['client_custom_values'] = "CREATE TABLE IF NOT EXISTS client_custom_values (id {$autoinc}, user_id INT NOT NULL, field_id INT NOT NULL, value TEXT)";

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
            'announcements' => "CREATE TABLE IF NOT EXISTS announcements (id {$autoinc}, title VARCHAR(255) NOT NULL, body TEXT, status TINYINT DEFAULT 1, published_at DATETIME DEFAULT CURRENT_TIMESTAMP, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'kb_categories' => "CREATE TABLE IF NOT EXISTS kb_categories (id {$autoinc}, name VARCHAR(190) NOT NULL, description TEXT, sort_order INT DEFAULT 0)",
            'kb_articles' => "CREATE TABLE IF NOT EXISTS kb_articles (id {$autoinc}, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, body TEXT, views INT DEFAULT 0, status TINYINT DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL)",
            'tld_pricing' => "CREATE TABLE IF NOT EXISTS tld_pricing (id {$autoinc}, tld VARCHAR(30) NOT NULL UNIQUE, register_price DECIMAL(15,2) DEFAULT 0, transfer_price DECIMAL(15,2) DEFAULT 0, renew_price DECIMAL(15,2) DEFAULT 0, status TINYINT DEFAULT 1)",
            'addons' => "CREATE TABLE IF NOT EXISTS addons (id {$autoinc}, name VARCHAR(190) NOT NULL, description TEXT, price DECIMAL(15,2) DEFAULT 0, billing_cycle VARCHAR(20) DEFAULT 'monthly', status TINYINT DEFAULT 1)",
            'service_addons' => "CREATE TABLE IF NOT EXISTS service_addons (id {$autoinc}, service_id INT NOT NULL, addon_id INT NOT NULL, status VARCHAR(20) DEFAULT 'active')",
            'promotions' => "CREATE TABLE IF NOT EXISTS promotions (id {$autoinc}, code VARCHAR(50) NOT NULL UNIQUE, discount_type VARCHAR(20) DEFAULT 'percent', discount_value DECIMAL(15,2) DEFAULT 0, applies_to VARCHAR(100) DEFAULT 'all', valid_from DATE NULL, valid_until DATE NULL, max_uses INT DEFAULT 0, used INT DEFAULT 0, status TINYINT DEFAULT 1)",
            'contacts' => "CREATE TABLE IF NOT EXISTS contacts (id {$autoinc}, user_id INT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL, password VARCHAR(255) DEFAULT '', permissions TEXT, status VARCHAR(20) DEFAULT 'active', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'predefined_replies' => "CREATE TABLE IF NOT EXISTS predefined_replies (id {$autoinc}, name VARCHAR(190) NOT NULL, body TEXT)",
            'quotes' => "CREATE TABLE IF NOT EXISTS quotes (id {$autoinc}, quote_number VARCHAR(50) NOT NULL UNIQUE, user_id INT NOT NULL, amount DECIMAL(15,2) DEFAULT 0, total DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'pending', valid_until DATE NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'quote_items' => "CREATE TABLE IF NOT EXISTS quote_items (id {$autoinc}, quote_id INT NOT NULL, description VARCHAR(255) NOT NULL, amount DECIMAL(15,2) DEFAULT 0)",
            'blog_categories' => "CREATE TABLE IF NOT EXISTS blog_categories (id {$autoinc}, name VARCHAR(190) NOT NULL, slug VARCHAR(190) DEFAULT '', sort_order INT DEFAULT 0)",
            'blog_posts' => "CREATE TABLE IF NOT EXISTS blog_posts (id {$autoinc}, title VARCHAR(255) NOT NULL, slug VARCHAR(190) NOT NULL, category_id INT DEFAULT 0, excerpt TEXT, content TEXT, image VARCHAR(255) DEFAULT '', status TINYINT DEFAULT 1, views INT DEFAULT 0, seo_title VARCHAR(190) DEFAULT '', seo_description VARCHAR(255) DEFAULT '', published_at DATETIME DEFAULT CURRENT_TIMESTAMP, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'otp_codes' => "CREATE TABLE IF NOT EXISTS otp_codes (id {$autoinc}, email VARCHAR(190) NOT NULL, code VARCHAR(10) NOT NULL, type VARCHAR(30) DEFAULT 'email', expires_at DATETIME NOT NULL, used TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'affiliate_commissions' => "CREATE TABLE IF NOT EXISTS affiliate_commissions (id {$autoinc}, affiliate_id INT NOT NULL, referred_user_id INT NOT NULL, invoice_id INT NULL, amount DECIMAL(15,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'cancellation_requests' => "CREATE TABLE IF NOT EXISTS cancellation_requests (id {$autoinc}, service_id INT NOT NULL, user_id INT NOT NULL, type VARCHAR(30) DEFAULT 'immediate', reason TEXT, status VARCHAR(20) DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'downloads' => "CREATE TABLE IF NOT EXISTS downloads (id {$autoinc}, name VARCHAR(190) NOT NULL, description TEXT, file_path VARCHAR(255) DEFAULT '', category VARCHAR(100) DEFAULT '', status TINYINT DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            'client_custom_fields' => "CREATE TABLE IF NOT EXISTS client_custom_fields (id {$autoinc}, name VARCHAR(190) NOT NULL, field_type VARCHAR(30) DEFAULT 'text', options TEXT, required TINYINT DEFAULT 0, sort_order INT DEFAULT 0)",
            'client_custom_values' => "CREATE TABLE IF NOT EXISTS client_custom_values (id {$autoinc}, user_id INT NOT NULL, field_id INT NOT NULL, value TEXT)",
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

        // Missing columns on tickets (rating)
        $tcols = self::columns($db, 'tickets');
        if (!in_array('rating', $tcols, true)) {
            $db->exec('ALTER TABLE tickets ADD COLUMN rating TINYINT DEFAULT 0');
        }
        if (!in_array('rating_comment', $tcols, true)) {
            $db->exec('ALTER TABLE tickets ADD COLUMN rating_comment TEXT');
        }

        // Missing columns on users (KYC / account type)
        $ucols = self::columns($db, 'users');
        foreach (['account_type' => "VARCHAR(20) DEFAULT 'individual'", 'tc_no' => "VARCHAR(20) DEFAULT ''", 'tax_no' => "VARCHAR(20) DEFAULT ''", 'verified' => 'TINYINT DEFAULT 0'] as $col => $def) {
            if (!in_array($col, $ucols, true)) {
                $db->exec("ALTER TABLE users ADD COLUMN {$col} {$def}");
            }
        }
        // Missing affiliate columns on users
        $ucols = self::columns($db, 'users');
        foreach (['referral_code' => "VARCHAR(20) DEFAULT ''", 'referred_by' => 'INT NULL', 'affiliate' => 'TINYINT DEFAULT 0', 'affiliate_balance' => 'DECIMAL(15,2) DEFAULT 0'] as $col => $def) {
            if (!in_array($col, $ucols, true)) {
                $db->exec("ALTER TABLE users ADD COLUMN {$col} {$def}");
            }
        }
        // Missing invoice columns (discount / promo)
        $icols = self::columns($db, 'invoices');
        if (!in_array('discount', $icols, true)) {
            $db->exec('ALTER TABLE invoices ADD COLUMN discount DECIMAL(15,2) DEFAULT 0');
        }
        if (!in_array('promo_code', $icols, true)) {
            $db->exec("ALTER TABLE invoices ADD COLUMN promo_code VARCHAR(50) DEFAULT ''");
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

        // Default email templates (if none)
        if ((int)$db->query('SELECT COUNT(*) FROM email_templates')->fetchColumn() === 0) {
            $stmt = $db->prepare('INSERT INTO email_templates (code, subject, body, enabled) VALUES (?, ?, ?, 1)');
            $templates = [
                ['welcome', 'Hoş Geldiniz {first_name}!', "Merhaba {first_name},\n\n{site_name} hesabınıza hoş geldiniz. Hesabınıza {login_url} adresinden giriş yapabilirsiniz.\n\nSaygılarımızla,\n{site_name}"],
                ['invoice_created', 'Yeni Faturanız: {invoice_number}', "Merhaba {first_name},\n\n{invoice_number} numaralı {total} tutarında faturanız oluşturuldu.\n\nGörüntülemek için: {invoice_url}\n\n{site_name}"],
                ['invoice_paid', 'Ödemeniz Alındı — {invoice_number}', "Merhaba {first_name},\n\n{invoice_number} numaralı faturanıza yapılan {total} tutarındaki ödemeniz için teşekkür ederiz.\n\n{site_name}"],
                ['ticket_opened', 'Destek Biletiniz Oluşturuldu: {ticket_number}', "Merhaba {first_name},\n\n{ticket_number} numaralı destek biletiniz oluşturuldu. Ekibimiz en kısa sürede size dönüş yapacaktır.\n\n{site_name}"],
                ['ticket_reply', 'Destek Biletinize Yanıt: {ticket_number}', "Merhaba {first_name},\n\n{ticket_number} numaralı biletinize yeni bir yanıt eklendi.\n\nGörüntülemek için: {ticket_url}\n\n{site_name}"],
                ['service_suspended', 'Hizmetiniz Askıya Alındı', "Merhaba {first_name},\n\n{domain} hizmetiniz ödeme yapılmadığı için askıya alınmıştır. Lütfen en kısa sürede ödeme yapın.\n\n{site_name}"],
            ];
            foreach ($templates as $t) {
                $stmt->execute($t);
            }
        }

        // Default TLD pricing (if none)
        if ((int)$db->query('SELECT COUNT(*) FROM tld_pricing')->fetchColumn() === 0) {
            $stmt = $db->prepare('INSERT INTO tld_pricing (tld, register_price, transfer_price, renew_price, status) VALUES (?, ?, ?, ?, 1)');
            $tlds = [['.com', 15.90, 15.90, 15.90], ['.net', 18.90, 18.90, 18.90], ['.org', 16.90, 16.90, 16.90], ['.info', 9.90, 9.90, 9.90], ['.xyz', 7.90, 7.90, 7.90], ['.co', 25.90, 25.90, 25.90], ['.io', 45.90, 45.90, 45.90], ['.dev', 22.90, 22.90, 22.90], ['.app', 24.90, 24.90, 24.90], ['.site', 8.90, 8.90, 8.90], ['.online', 8.90, 8.90, 8.90], ['.shop', 12.90, 12.90, 12.90]];
            foreach ($tlds as $t) {
                $stmt->execute($t);
            }
        }

        // Default settings (SMTP / SMS / Netlen registrar)
        $defaults = [
            'smtp_enabled' => '0', 'smtp_host' => '', 'smtp_port' => '587', 'smtp_user' => '', 'smtp_pass' => '', 'smtp_encryption' => 'tls', 'smtp_from_email' => '', 'smtp_from_name' => '', 'mail_method' => 'php',
            'sms_enabled' => '0', 'sms_gateway' => 'whatsapp', 'sms_api_key' => '', 'sms_api_secret' => '', 'sms_sender' => '',
            'netlen_enabled' => '0', 'netlen_api_key' => '', 'netlen_api_url' => 'https://api.netlen.com.tr/v2', 'netlen_ns1' => 'ns1.netlen.com.tr', 'netlen_ns2' => 'ns2.netlen.com.tr',
            'affiliate_enabled' => '1', 'affiliate_rate' => '10', 'affiliate_payout_min' => '50',
        ];
        $stmt = $db->prepare('SELECT COUNT(*) FROM settings WHERE name = ?');
        $ins = $db->prepare('INSERT INTO settings (name, value) VALUES (?, ?)');
        foreach ($defaults as $k => $v) {
            $stmt->execute([$k]);
            if ((int)$stmt->fetchColumn() === 0) {
                $ins->execute([$k, $v]);
            }
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
