<div class="page-head"><h1>Ödeme Yöntemleri</h1></div>
<?php foreach ($gateways as $g): ?>
<div class="card mb-3" style="max-width:760px">
    <div class="card-header">
        <h3><?= e($g['name']) ?></h3>
        <?= $g['enabled'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge">Pasif</span>' ?>
    </div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/payments/' . $g['code']) ?>">
            <?= csrf_field() ?>
            <label class="form-check mb-3"><input type="checkbox" name="enabled" value="1" <?= $g['enabled'] ? 'checked' : '' ?>> Bu yöntemi etkinleştir</label>
            <?php foreach ($g['config'] as $k => $v): ?>
                <div class="form-group"><label><?= e(ucwords(str_replace('_', ' ', $k))) ?></label>
                    <input class="form-control" name="config[<?= e($k) ?>]" value="<?= e($v) ?>">
                </div>
            <?php endforeach; ?>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
