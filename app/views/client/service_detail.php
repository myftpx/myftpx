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
