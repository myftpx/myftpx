<section class="hero">
    <div class="container">
        <h1>Güçlü Hosting & Domain Çözümleri</h1>
        <p>İşletmenizi büyütmek için tasarlanmış kurumsal barındırma, alan adı ve bulut çözümleri. Kurulum, fatura ve destek tek panelde.</p>
        <form method="get" action="<?= url('domains/search') ?>" class="hero-search">
            <input name="domain" placeholder="Alan adınızı arayın, örn: ornek.com">
            <button class="btn btn-primary btn-lg" type="submit">Ara</button>
        </form>
    </div>
</section>

<div class="container">
    <?php if ($tlds): ?>
    <h2 class="section-title">Popüler Uzantılar</h2>
    <div class="tld-table">
        <?php foreach ($tlds as $t): ?>
            <div class="tld-cell">
                <div class="tld"><?= e($t['tld']) ?></div>
                <div class="price"><?= money($t['register_price']) ?>/yıl</div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="feature-grid">
        <div class="feature"><div class="fico">⚡</div><h3>%99.9 Uptime</h3><p>Kesintisiz hizmet için güvenilir altyapı.</p></div>
        <div class="feature"><div class="fico">🛡</div><h3>Ücretsiz SSL</h3><p>Tüm hosting paketlerinde ücretsiz SSL sertifikası.</p></div>
        <div class="feature"><div class="fico">🎧</div><h3>7/24 Destek</h3><p>Uzman destek ekibimiz her zaman yanınızda.</p></div>
        <div class="feature"><div class="fico">💰</div><h3>Para İade Garantisi</h3><p>30 gün içinde koşulsuz iade.</p></div>
    </div>

    <?php foreach ($categories as $cat => $items): ?>
        <h2 class="section-title" id="products"><?= e($cat) ?></h2>
        <div class="pricing-grid">
            <?php foreach ($items as $p): ?>
                <div class="pricing-card <?= $p['featured'] ? 'featured' : '' ?>">
                    <?php if ($p['featured']): ?><span class="tag">Öne Çıkan</span><?php endif; ?>
                    <div class="name"><?= e($p['name']) ?></div>
                    <div class="price"><?= money($p['price']) ?></div>
                    <div class="cycle">/ <?= e(['monthly' => 'aylık', 'quarterly' => '3 aylık', 'annually' => 'yıllık'][$p['billing_cycle']] ?? $p['billing_cycle']) ?></div>
                    <ul class="features">
                        <li>Ücretsiz SSL sertifikası</li>
                        <li>%99.9 uptime garantisi</li>
                        <li>7/24 teknik destek</li>
                    </ul>
                    <a class="btn btn-primary btn-block" href="<?= url('store/product/' . $p['slug']) ?>">Sipariş Ver</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($products)): ?>
        <div class="card text-center" style="padding:50px" id="products">
            <p class="text-muted">Henüz ürün eklenmemiş.</p>
        </div>
    <?php endif; ?>

    <?php if ($announcements): ?>
    <h2 class="section-title">Duyurular</h2>
    <p class="section-sub">Son gelişmeler ve güncellemeler.</p>
    <div style="max-width:860px;margin:20px auto 0">
        <?php foreach ($announcements as $a): ?>
            <div class="announcement">
                <div class="date"><?= e(date('d.m.Y', strtotime($a['published_at']))) ?></div>
                <a href="<?= url('announcements/' . $a['id']) ?>"><strong><?= e($a['title']) ?></strong></a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2 class="section-title">Yardım mı lazım?</h2>
    <p class="section-sub">Sorularınızın cevabını <a href="<?= url('knowledgebase') ?>">Bilgi Bankası</a>'nda bulabilir veya <a href="<?= url('client/tickets/new') ?>">destek bileti</a> açabilirsiniz.</p>
</div>
