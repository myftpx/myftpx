<div class="page-head">
    <h1><?= e($service['domain'] ?: $service['product_name']) ?></h1>
    <div class="flex gap-2">
        <a class="btn btn-outline" href="<?= url('client/services') ?>">Geri</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Hizmet Bilgileri</h3><?= status_badge($service['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Ürün</span><span class="v"><?= e($service['product_name']) ?></span></div>
            <div class="info-row"><span class="k">Tür</span><span class="v"><?= e($service['product_type']) ?></span></div>
            <div class="info-row"><span class="k">Alan Adı</span><span class="v"><?= e($service['domain'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Faturalama</span><span class="v"><?= money($service['amount']) ?> / <?= e($service['billing_cycle']) ?></span></div>
            <div class="info-row"><span class="k">Son Ödeme Tarihi</span><span class="v"><?= e($service['next_due_date'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Başlangıç</span><span class="v"><?= e(date('d.m.Y', strtotime($service['created_at']))) ?></span></div>
        </div>
    </div>
</div>

<?php if (!empty($service['config'])): ?>
<div class="card mb-3">
    <div class="card-header"><h3>Yapılandırma</h3></div>
    <div class="card-body">
        <div class="info-list">
            <?php foreach ($service['config'] as $k => $v): ?>
                <div class="info-row"><span class="k"><?= e($k) ?></span><span class="v"><?= e($v) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($service['module'] && $service['module'] !== 'none'): ?>
<div class="card">
    <div class="card-header"><h3>Panel Erişimi</h3></div>
    <div class="card-body">
        <div class="alert alert-info">Bu hizmet <strong><?= e($service['module']) ?></strong> modülüne bağlıdır. Giriş bilgileriniz aktifleştirme sonrası burada görüntülenir.</div>
        <div class="info-list">
            <div class="info-row"><span class="k">Kullanıcı Adı</span><span class="v mono"><?= e($service['username'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Şifre</span><span class="v mono"><?= e($service['password'] ? str_repeat('•', 10) : '—') ?></span></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mt-3">
    <div class="card-header"><h3>Otomatik Ödeme (Abonelik)</h3></div>
    <div class="card-body">
        <p class="text-muted mb-2">Ödeme tarihiniz geldiğinde kayıtlı kartınızdan otomatik ödeme çekilir. Kart bilgileriniz ödeme kuruluşunda (PayTR / iyzico) güvenle saklanır.</p>
        <form method="post" action="<?= url('client/services/' . $service['id'] . '/autorenew') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Otomatik Ödeme</label>
                    <select class="form-control" name="auto_renew">
                        <option value="0" <?= !$service['auto_renew'] ? 'selected' : '' ?>>Kapalı</option>
                        <option value="1" <?= $service['auto_renew'] ? 'selected' : '' ?>>Açık</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ödeme Kartı</label>
                    <select class="form-control" name="card_id" <?= empty($cards) ? 'disabled' : '' ?>>
                        <option value="">— Varsayılan kart —</option>
                        <?php foreach ($cards as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= (int)$service['card_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= e($c['brand']) ?> •••• <?= e($c['last4']) ?> (<?= $c['gateway'] === 'paytr' ? 'PayTR' : 'iyzico' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if (empty($cards)): ?>
                <p class="small text-muted mb-2">Otomatik ödeme için önce <a href="<?= url('client/cards') ?>">kart kaydetmeniz</a> gerekir.</p>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
        <?php if ($service['next_due_date']): ?>
            <p class="small text-muted mt-2">Sonraki ödeme tarihi: <strong><?= e($service['next_due_date']) ?></strong> · Tutar: <strong><?= money($service['amount']) ?></strong></p>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3>Eklentiler (Addons)</h3></div>
    <div class="card-body">
        <?php if ($activeAddons): ?>
            <p class="text-muted mb-2">Aktif eklentileriniz:</p>
            <?php foreach ($activeAddons as $aa): ?>
                <div class="flex justify-between items-center mb-2">
                    <span><strong><?= e($aa['name']) ?></strong> — <?= money($aa['price']) ?>/<?= e($aa['billing_cycle']) ?></span>
                    <form method="post" action="<?= url('client/service-addons/' . $aa['said'] . '/remove') ?>"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Kaldır</button></form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($addons): ?>
            <form method="post" action="<?= url('client/services/' . $service['id'] . '/addon') ?>" class="flex gap-2 items-center" style="flex-wrap:wrap">
                <?= csrf_field() ?>
                <select class="form-control" name="addon_id" style="max-width:280px">
                    <?php foreach ($addons as $a): ?>
                        <option value="<?= (int)$a['id'] ?>"><?= e($a['name']) ?> — <?= money($a['price']) ?>/<?= e($a['billing_cycle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Eklenti Ekle</button>
            </form>
        <?php else: ?>
            <p class="text-muted">Kullanılabilir eklenti yok.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($service['status'] === 'active'): ?>
<div class="card mt-3">
    <div class="card-header"><h3>Hizmeti İptal Et</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/services/' . $service['id'] . '/cancel') ?>" onsubmit="return confirm('Hizmeti iptal etmek istediğinize emin misiniz?')">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label>İptal Türü</label>
                    <select class="form-control" name="type">
                        <option value="immediate">Hemen iptal et</option>
                        <option value="end_of_period">Dönem sonunda iptal et</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sebep (opsiyonel)</label>
                    <input class="form-control" name="reason">
                </div>
            </div>
            <button class="btn btn-danger" type="submit">İptal Talebi Gönder</button>
        </form>
    </div>
</div>
<?php endif; ?>
