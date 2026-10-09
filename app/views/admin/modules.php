<div class="page-head"><h1>Modüller & Entegrasyonlar</h1></div>
<div class="card mb-3">
    <div class="card-body">
        <p class="text-muted">Sunucu modülleri (cPanel, Plesk), alan adı kayıt firmaları ve eklentiler. Bir modülü etkinleştirip API bilgilerini girerek hizmetlerinizi otomatik yönetebilirsiniz.</p>
    </div>
</div>
<?php foreach ($modules as $m): ?>
<div class="card mb-3" style="max-width:760px">
    <div class="card-header">
        <h3><?= e($m['name']) ?> <span class="badge badge-info"><?= e($m['type']) ?></span></h3>
        <?= $m['enabled'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge">Pasif</span>' ?>
    </div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/modules/' . $m['code']) ?>">
            <?= csrf_field() ?>
            <label class="form-check mb-3"><input type="checkbox" name="enabled" value="1" <?= $m['enabled'] ? 'checked' : '' ?>> Bu modülü etkinleştir</label>
            <?php foreach ($m['config'] as $k => $v): ?>
                <div class="form-group"><label><?= e(ucwords(str_replace('_', ' ', $k))) ?></label>
                    <input class="form-control" name="config[<?= e($k) ?>]" value="<?= e($v) ?>">
                </div>
            <?php endforeach; ?>
            <?php if (empty($m['config'])): ?>
                <p class="text-muted small">Bu modül için ek yapılandırma gerekmiyor.</p>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
