<div class="container" style="padding-top:40px">
    <h2><?= e($category) ?></h2>
    <div class="pricing-grid">
        <?php foreach ($products as $p): ?>
            <div class="pricing-card <?= $p['featured'] ? 'featured' : '' ?>">
                <div class="name"><?= e($p['name']) ?></div>
                <div class="price"><?= money($p['price']) ?></div>
                <div class="cycle">/ <?= e($p['billing_cycle']) ?></div>
                <a class="btn btn-primary btn-block" href="<?= url('store/product/' . $p['slug']) ?>">Sipariş Ver</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
