<section class="hero">
    <div class="container">
        <h1>Güçlü Hosting & Domain Yönetimi</h1>
        <p>İşletmenizi büyütmek için tasarlanmış kurumsal barındırma, alan adı ve bulut çözümleri. Kurulum, fatura ve destek tek panelde.</p>
        <div class="flex gap-2" style="justify-content:center">
            <a class="btn btn-primary btn-lg" href="#products">Hizmetleri Gör</a>
            <a class="btn btn-outline btn-lg" href="<?= url('register') ?>">Ücretsiz Kayıt</a>
        </div>
    </div>
</section>

<div class="container">
    <?php foreach ($categories as $cat => $items): ?>
        <h2 class="section-title" id="products"><?= e($cat) ?></h2>
        <div class="pricing-grid">
            <?php foreach ($items as $p): ?>
                <div class="pricing-card <?= $p['featured'] ? 'featured' : '' ?>">
                    <?php if ($p['featured']): ?><span class="tag">Öne Çıkan</span><?php endif; ?>
                    <div class="name"><?= e($p['name']) ?></div>
                    <div class="price"><?= money($p['price']) ?></div>
                    <div class="cycle">/ <?= e(['monthly' => 'aylık', 'quarterly' => '3 aylık', 'annually' => 'yıllık'][$p['billing_cycle']] ?? $p['billing_cycle']) ?></div>
                    <div class="text-muted small mb-2"><?= e($p['type']) ?></div>
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

    <h2 class="section-title" id="domains">Alan Adı Kaydı</h2>
    <p class="section-sub">Popüler uzantılarda uygun fiyatlarla alan adınızı güvence altına alın.</p>
    <div class="card" style="max-width:640px;margin:24px auto 0">
        <div class="card-body">
            <form method="get" action="<?= url('store') ?>" class="flex gap-2">
                <input class="form-control" name="domain" placeholder="aranacakalanadi.com">
                <button class="btn btn-primary" type="submit">Ara</button>
            </form>
        </div>
    </div>
</div>
