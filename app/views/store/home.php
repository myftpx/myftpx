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

    <?php
    $catIcons = ['Web Hosting' => '🌐', 'VPS' => '⚡', 'Sunucu' => '🖥', 'Reseller' => '📦', 'Alan Adı' => '⛓', 'SSL' => '🔒', 'Genel' => '📦'];
    ?>
    <h2 class="section-title" id="products">Hizmetlerimiz</h2>
    <p class="section-sub">İhtiyacınıza uygun, şeffaf fiyatlı çözümler.</p>

    <?php foreach ($categories as $cat => $items): ?>
        <div class="card mt-3" style="border:1px solid var(--border);overflow:hidden">
            <div class="card-header" style="background:var(--surface-2)">
                <h3 style="display:flex;align-items:center;gap:10px">
                    <span><?= $catIcons[$cat] ?? '📦' ?></span> <?= e($cat) ?>
                </h3>
                <a class="btn btn-outline btn-sm" href="<?= url('store/category/' . urlencode($cat)) ?>">Tümünü Gör →</a>
            </div>
            <div class="card-body">
                <div class="table-wrap"><table class="table">
                    <tr>
                        <th>Paket</th><th>Fiyat</th><th>Dönem</th><th>Öne Çıkan Özellikler</th><th></th>
                    </tr>
                    <?php foreach ($items as $p): ?>
                        <tr>
                            <td><strong><?= e($p['name']) ?></strong><?= $p['featured'] ? ' <span class="badge badge-primary">Öne Çıkan</span>' : '' ?></td>
                            <td class="nowrap"><strong><?= money($p['price']) ?></strong></td>
                            <td><?= e(['monthly' => 'Aylık', 'quarterly' => '3 Aylık', 'semi_annual' => '6 Aylık', 'annually' => 'Yıllık', 'biennially' => '2 Yıllık'][$p['billing_cycle']] ?? $p['billing_cycle']) ?></td>
                            <td class="text-muted"><?= e(mb_substr(strip_tags($p['description']), 0, 90)) ?></td>
                            <td><a class="btn btn-primary btn-sm" href="<?= url('store/product/' . $p['slug']) ?>">İncele & Sipariş</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table></div>
            </div>
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
