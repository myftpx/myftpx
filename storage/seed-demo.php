<?php
/**
 * RCVXTR — Demo verisi oluşturucu (opsiyonel)
 * Kullanım: php storage/seed-demo.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

if (!Database::isInstalled()) {
    echo "Önce /install ile kurulum yapın.\n";
    exit(1);
}

$db = db();

// Örnek ürünler
$products = [
    ['Başlangıç Hosting', 'hosting', 19.90, 'monthly', '5GB SSD, 1 site, ücretsiz SSL, %99.9 uptime'],
    ['Kurumsal Hosting', 'hosting', 49.90, 'monthly', '20GB SSD, sınırsız site, ücretsiz SSL, günlük yedek'],
    ['Premium Hosting', 'hosting', 99.90, 'monthly', '50GB NVMe, sınırsız site, öncelikli destek, CDN'],
    ['Reseller Başlangıç', 'reseller', 129.90, 'monthly', '50 cPanel hesabı, WHM, özel IP'],
    ['VPS 4GB', 'vps', 199.90, 'monthly', '4 vCPU, 4GB RAM, 80GB NVMe, 4TB trafik'],
    ['Dedicated Sunucu', 'server', 499.90, 'monthly', 'E-2388G, 64GB RAM, 2x1TB NVMe, 1Gbps'],
];

$stmt = $db->prepare('INSERT INTO products (name, slug, description, category, type, price, billing_cycle, status, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, "active", ?, ?)');
$i = 0;
foreach ($products as $p) {
    $stmt->execute([$p[0], slug($p[0]), $p[3], $p[1] === 'hosting' ? 'Web Hosting' : ucfirst($p[1]), $p[1], $p[2], $p[4], $i < 3 ? 1 : 0, $i++]);
}
echo "Ürünler eklendi: " . count($products) . "\n";

// Demo müşteri (varsa ekleme)
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute(['demo@rcvxtr.com']);
if (!$stmt->fetch()) {
    $db->prepare('INSERT INTO users (first_name, last_name, email, password, balance, company) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute(['Demo', 'Müşteri', 'demo@rcvxtr.com', password_hash('demo123', PASSWORD_DEFAULT), 250.00, 'Demo A.Ş.']);
    echo "Demo müşteri eklendi: demo@rcvxtr.com / demo123\n";
} else {
    echo "Demo müşteri zaten mevcut.\n";
}

echo "Tamamlandı.\n";
