<div class="container" style="padding-top:40px">
    <div class="page-head">
        <div>
            <h1><?= e($product['name']) ?></h1>
            <p class="text-muted"><?= e($product['type']) ?> · <?= e($product['category'] ?: 'Genel') ?></p>
        </div>
        <span class="badge badge-success"><?= e($product['billing_cycle']) ?></span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:24px" class="product-layout">
        <div class="card"><div class="card-body">
            <h3 class="mb-2">Açıklama</h3>
            <div><?= nl2br(e($product['description'])) ?></div>
        </div></div>

        <div class="card">
            <div class="card-header"><h3>Sipariş Ver</h3></div>
            <div class="card-body">
                <div class="price" style="font-size:1.8rem;font-weight:800"><?= money($product['price']) ?> <span class="small text-muted">/ <?= e($product['billing_cycle']) ?></span></div>
                <?php if ((float)$product['setup_fee'] > 0): ?>
                    <div class="text-muted small mb-2">Kurulum ücreti: <?= money($product['setup_fee']) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= url('store/order') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <div class="form-group mt-2">
                        <label>Faturalama Dönemi</label>
                        <select class="form-control" name="billing_cycle">
                            <option value="monthly">Aylık</option>
                            <option value="quarterly">3 Aylık</option>
                            <option value="semi_annual">6 Aylık</option>
                            <option value="annually">Yıllık</option>
                            <option value="biennially">2 Yıllık</option>
                        </select>
                    </div>
                    <?php if ($product['type'] === 'hosting' || $product['type'] === 'reseller' || $product['type'] === 'vps' || $product['type'] === 'server'): ?>
                        <div class="form-group">
                            <label>Alan Adı</label>
                            <input class="form-control" name="domain" placeholder="example.com">
                        </div>
                    <?php endif; ?>
                    <?php foreach ($options as $o): ?>
                        <div class="form-group">
                            <label><?= e($o['name']) ?><?= $o['required'] ? ' *' : '' ?></label>
                            <?php if ($o['type'] === 'dropdown'): ?>
                                <select class="form-control" name="config_<?= (int)$o['id'] ?>">
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($o['options'] as $opt): ?><option value="<?= e($opt) ?>"><?= e($opt) ?></option><?php endforeach; ?>
                                </select>
                            <?php elseif ($o['type'] === 'radio'): ?>
                                <?php foreach ($o['options'] as $opt): ?><label class="form-check"><input type="radio" name="config_<?= (int)$o['id'] ?>" value="<?= e($opt) ?>"> <?= e($opt) ?></label><?php endforeach; ?>
                            <?php else: ?>
                                <input class="form-control" name="config_<?= (int)$o['id'] ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="form-group">
                        <label>Kupon Kodu (opsiyonel)</label>
                        <input class="form-control" name="promo_code" placeholder="WELCOME10">
                    </div>
                    <div class="form-group">
                        <label>Ödeme Yöntemi</label>
                        <select class="form-control" name="payment_method">
                            <option value="balance">Bakiye</option>
                            <?php foreach (db()->query('SELECT * FROM payment_gateways WHERE enabled=1 ORDER BY sort_order')->fetchAll() as $g): ?>
                                <option value="<?= e($g['code']) ?>"><?= e($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg" type="submit">Siparişi Tamamla</button>
                    <?php if (!auth()->check()): ?>
                        <p class="small text-muted mt-2 text-center">Sipariş vermek için <a href="<?= url('login') ?>">giriş yapmanız</a> gerekir.</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<style>.product-layout{grid-template-columns:1fr 380px}@media(max-width:800px){.product-layout{grid-template-columns:1fr}}</style>
