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
