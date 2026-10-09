<!DOCTYPE html>
<html lang="tr" data-theme="<?= e(current_theme()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? setting('site_name', 'RCVXTR')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { corePlugins: { preflight: false } }</script>
</head>
<body>
<header class="store-header">
    <div class="container store-nav">
        <a class="brand" href="<?= url('/') ?>">
            <span class="logo">R</span> <?= e(setting('site_name', 'RCVXTR')) ?>
        </a>
        <nav class="store-nav-links">
            <a href="<?= url('/') ?>" class="<?= active_nav('/', true) ?: active_nav('/store', true) ?>">Ana Sayfa</a>
            <div class="nav-dropdown">
                <a href="<?= url('/#products') ?>">Hizmetler ▾</a>
                <div class="mega-menu">
                    <div class="mega-grid">
                        <?php
                        $navCats = [];
                        foreach (db()->query('SELECT DISTINCT category FROM products WHERE status = "active"')->fetchAll() as $r) {
                            $navCats[] = $r['category'] ?: 'Genel';
                        }
                        foreach ($navCats as $nc): ?>
                            <div class="mega-col">
                                <h4><?= e($nc) ?></h4>
                                <?php
                                $stmt = db()->prepare('SELECT * FROM products WHERE status = "active" AND category = ? ORDER BY sort_order LIMIT 6');
                                $stmt->execute([$nc]);
                                foreach ($stmt->fetchAll() as $p): ?>
                                    <a href="<?= url('store/product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <a href="<?= url('domains') ?>" class="<?= active_nav('/domains') ?>">Alan Adları</a>
            <a href="<?= url('blog') ?>" class="<?= active_nav('/blog') ?>">Blog</a>
            <a href="<?= url('announcements') ?>" class="<?= active_nav('/announcements') ?>">Duyurular</a>
            <a href="<?= url('knowledgebase') ?>" class="<?= active_nav('/knowledgebase') ?>">Bilgi Bankası</a>
            <a href="<?= url('network-status') ?>" class="<?= active_nav('/network-status') ?>">Ağ Durumu</a>
            <a href="<?= url('api-docs') ?>" class="<?= active_nav('/api-docs') ?>">API</a>
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
