<section class="hero">
    <div class="container">
        <h1>Hayalinizdeki Alan Adını Alın</h1>
        <p>Mükemmel alan adınızı arayın, saniyeler içinde kaydedin.</p>
        <form method="get" action="<?= url('domains/search') ?>" class="hero-search">
            <input name="domain" placeholder="ornek.com" value="<?= e($_GET['domain'] ?? '') ?>">
            <button class="btn btn-primary btn-lg" type="submit">Ara</button>
        </form>
    </div>
</section>
<div class="container">
    <h2 class="section-title">Popüler Uzantılar</h2>
    <p class="section-sub">Şeffaf fiyatlandırma, gizli ücret yok.</p>
    <div class="tld-table">
        <?php foreach ($tlds as $t): ?>
            <div class="tld-cell">
                <div class="tld"><?= e($t['tld']) ?></div>
                <div class="price"><?= money($t['register_price']) ?>/yıl</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
