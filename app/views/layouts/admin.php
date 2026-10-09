<?php $a = auth()->admin(); ?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?= e(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Yönetim') ?> — <?= e(setting('site_name', 'RCVXTR')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="<?= url('admin') ?>"><span class="logo">R</span> <?= e(setting('site_name', 'RCVXTR')) ?></a>
        <nav class="side-nav">
            <div class="section-label">Genel</div>
            <a href="<?= url('admin') ?>" class="<?= active_nav('/admin', true) ?>"><span class="ico">▦</span> Panel</a>
            <a href="<?= url('admin/reports') ?>" class="<?= active_nav('/admin/reports') ?>"><span class="ico">◔</span> Raporlar</a>
            <div class="section-label">Müşteriler</div>
            <a href="<?= url('admin/clients') ?>" class="<?= active_nav('/admin/clients') ?>"><span class="ico">👥</span> Müşteriler</a>
            <div class="section-label">Ürün & Hizmet</div>
            <a href="<?= url('admin/products') ?>" class="<?= active_nav('/admin/products') ?>"><span class="ico">◈</span> Ürünler</a>
            <a href="<?= url('admin/orders') ?>" class="<?= active_nav('/admin/orders') ?>"><span class="ico">➜</span> Siparişler</a>
            <a href="<?= url('admin/services') ?>" class="<?= active_nav('/admin/services') ?>"><span class="ico">◉</span> Hizmetler</a>
            <a href="<?= url('admin/domains') ?>" class="<?= active_nav('/admin/domains') ?>"><span class="ico">⛓</span> Alan Adları</a>
            <div class="section-label">Faturalama</div>
            <a href="<?= url('admin/invoices') ?>" class="<?= active_nav('/admin/invoices') ?>"><span class="ico">▤</span> Faturalar</a>
            <a href="<?= url('admin/transactions') ?>" class="<?= active_nav('/admin/transactions') ?>"><span class="ico">⇄</span> İşlemler</a>
            <div class="section-label">Destek</div>
            <a href="<?= url('admin/tickets') ?>" class="<?= active_nav('/admin/tickets') ?>"><span class="ico">✉</span> Biletler</a>
            <div class="section-label">Sistem</div>
            <a href="<?= url('admin/payments') ?>" class="<?= active_nav('/admin/payments') ?>"><span class="ico">💳</span> Ödemeler</a>
            <a href="<?= url('admin/bank-accounts') ?>" class="<?= active_nav('/admin/bank-accounts') ?>"><span class="ico">🏦</span> Banka Hesapları</a>
            <a href="<?= url('admin/payment-logs') ?>" class="<?= active_nav('/admin/payment-logs') ?>"><span class="ico">↺</span> Ödeme Logları</a>
            <a href="<?= url('admin/modules') ?>" class="<?= active_nav('/admin/modules') ?>"><span class="ico">⬡</span> Modüller</a>
            <a href="<?= url('admin/settings') ?>" class="<?= active_nav('/admin/settings') ?>"><span class="ico">⚙</span> Ayarlar</a>
        </nav>
        <div class="side-user">
            <div class="name"><?= e($a['name']) ?></div>
            <div class="email"><?= e($a['email']) ?></div>
        </div>
    </aside>
    <div class="main">
        <div class="topbar">
            <div class="flex items-center gap-2">
                <button class="btn btn-ghost menu-toggle">☰</button>
                <h1><?= e($title ?? 'Yönetim Paneli') ?></h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= url('/') ?>" class="btn btn-ghost btn-sm" target="_blank">Siteyi Gör</a>
                <a href="<?= url('admin/logout') ?>" class="btn btn-outline btn-sm">Çıkış</a>
            </div>
        </div>
        <div class="content">
            <?php include __DIR__ . '/../partials/flash.php'; ?>
            <?= $content ?>
        </div>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
