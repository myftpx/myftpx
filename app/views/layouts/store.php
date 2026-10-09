<!DOCTYPE html>
<html lang="tr" data-theme="<?= e(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? setting('site_name', 'RCVXTR')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<header class="store-header">
    <div class="container store-nav">
        <a class="brand" href="<?= url('/') ?>">
            <span class="logo">R</span> <?= e(setting('site_name', 'RCVXTR')) ?>
        </a>
        <nav class="store-nav-links">
            <a href="<?= url('/') ?>" class="<?= active_nav('/', true) ?: active_nav('/store', true) ?>">Ana Sayfa</a>
            <a href="<?= url('/#products') ?>">Hizmetler</a>
            <a href="<?= url('domains') ?>" class="<?= active_nav('/domains') ?>">Alan Adları</a>
            <a href="<?= url('announcements') ?>" class="<?= active_nav('/announcements') ?>">Duyurular</a>
            <a href="<?= url('knowledgebase') ?>" class="<?= active_nav('/knowledgebase') ?>">Bilgi Bankası</a>
            <a href="<?= url('network-status') ?>" class="<?= active_nav('/network-status') ?>">Ağ Durumu</a>
            <?php if (auth()->check()): ?>
                <a href="<?= url('client') ?>" class="<?= active_nav('/client') ?>">Müşteri Paneli</a>
                <a href="<?= url('logout') ?>">Çıkış</a>
            <?php else: ?>
                <a href="<?= url('login') ?>">Giriş</a>
                <a href="<?= url('register') ?>" class="btn btn-primary btn-sm">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<div class="container"><?php include __DIR__ . '/../partials/flash.php'; ?></div>

<?= $content ?>
<?= $content ?>

<footer class="site-footer">
    <div class="container">
        <div>© <?= date('Y') ?> <?= e(setting('site_name', 'RCVXTR')) ?>. Tüm hakları saklıdır.</div>
        <div class="flex gap-2">
            <a href="<?= e(setting('terms_url', '#')) ?>">Kullanım Şartları</a>
            <a href="<?= e(setting('privacy_url', '#')) ?>">Gizlilik</a>
            <a href="mailto:<?= e(setting('support_email', setting('admin_email', ''))) ?>">Destek</a>
        </div>
    </div>
</footer>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
