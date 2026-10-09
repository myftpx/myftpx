<?php $u = auth()->user(); ?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?= e(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Panel') ?> — <?= e(setting('site_name', 'RCVXTR')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="<?= url('client') ?>"><span class="logo">R</span> <?= e(setting('site_name', 'RCVXTR')) ?></a>
        <nav class="side-nav">
            <div class="section-label">Genel</div>
            <a href="<?= url('client') ?>" class="<?= active_nav('/client', true) ?>"><span class="ico">▦</span> Panel</a>
            <a href="<?= url('/') ?>"><span class="ico">⇱</span> Ana Site</a>
            <div class="section-label">Hizmetler</div>
            <a href="<?= url('client/services') ?>" class="<?= active_nav('/client/services') ?>"><span class="ico">◈</span> Hizmetlerim</a>
            <a href="<?= url('client/domains') ?>" class="<?= active_nav('/client/domains') ?>"><span class="ico">⛓</span> Alan Adlarım</a>
            <div class="section-label">Faturalama</div>
            <a href="<?= url('client/invoices') ?>" class="<?= active_nav('/client/invoices') ?>"><span class="ico">▤</span> Faturalarım</a>
            <a href="<?= url('client/balance') ?>" class="<?= active_nav('/client/balance') ?>"><span class="ico">◉</span> Bakiye</a>
            <a href="<?= url('client/cards') ?>" class="<?= active_nav('/client/cards') ?>"><span class="ico">💳</span> Kartlarım</a>
            <a href="<?= url('client/payments') ?>" class="<?= active_nav('/client/payments') ?>"><span class="ico">↺</span> Ödeme Geçmişi</a>
            <div class="section-label">Destek</div>
            <a href="<?= url('client/tickets') ?>" class="<?= active_nav('/client/tickets') ?>"><span class="ico">✉</span> Destek Biletleri</a>
            <div class="section-label">Geliştirici</div>
            <a href="<?= url('client/api') ?>" class="<?= active_nav('/client/api') ?>"><span class="ico">⌘</span> API Erişimi</a>
            <a href="<?= url('client/profile') ?>" class="<?= active_nav('/client/profile') ?>"><span class="ico">⚙</span> Profilim</a>
            <a href="<?= url('client/contacts') ?>" class="<?= active_nav('/client/contacts') ?>"><span class="ico">👥</span> Alt Hesaplar</a>
            <a href="<?= url('client/quotes') ?>" class="<?= active_nav('/client/quotes') ?>"><span class="ico">📄</span> Tekliflerim</a>
            <a href="<?= url('client/downloads') ?>" class="<?= active_nav('/client/downloads') ?>"><span class="ico">⬇</span> İndirmeler</a>
            <a href="<?= url('client/affiliate') ?>" class="<?= active_nav('/client/affiliate') ?>"><span class="ico">🤝</span> Ortaklık</a>
        </nav>
        <div class="side-user">
            <div class="name"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></div>
            <div class="email"><?= e($u['email']) ?></div>
        </div>
    </aside>
    <div class="main">
        <div class="topbar">
            <div class="flex items-center gap-2">
                <button class="btn btn-ghost menu-toggle">☰</button>
                <h1><?= e($title ?? 'Panel') ?></h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-primary">Bakiye: <?= money($u['balance']) ?></span>
                <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm">Çıkış</a>
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
