<div class="page-head"><h1>Ödeme Yöntemleri</h1></div>

<div class="card mb-3">
    <div class="card-body">
        <p class="text-muted">PayTR ve iyzico kart saklama (token) özelliğini destekler — müşterilerin kartları güvenle ödeme kuruluşunda saklanır ve otomatik ödeme için kullanılır. Havale/EFT için banka hesaplarınızı <a href="<?= url('admin/bank-accounts') ?>">Banka Hesapları</a> bölümünden tanımlayın.</p>
    </div>
</div>

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

            <?php if ($g['code'] === 'bank_transfer'): ?>
                <div class="form-group">
                    <label>Ödeme Talimatı</label>
                    <textarea class="form-control" name="config[instruction]"><?= e($g['config']['instruction'] ?? '') ?></textarea>
                    <div class="form-hint">Banka hesaplarınızı <a href="<?= url('admin/bank-accounts') ?>">Banka Hesapları</a> bölümünden yönetebilirsiniz.</div>
                </div>
            <?php else: ?>
                <?php foreach ($g['config'] as $k => $v): ?>
                    <?php if ($k === 'api_mode'): ?>
                        <?php $liveMode = in_array($v, ['live', 'prod', 'production'], true); ?>
                        <div class="form-group">
                            <label>Mod</label>
                            <select class="form-control" name="config[api_mode]">
                                <option value="test" <?= !$liveMode ? 'selected' : '' ?>>Test / Sandbox (simülasyon)</option>
                                <option value="live" <?= $liveMode ? 'selected' : '' ?>>Canlı (live)</option>
                            </select>
                            <div class="form-hint">Test modunda gerçek para çekilmez; ödemeler simüle edilir. Canlıya geçmek için gerçek API bilgilerini girin.</div>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label><?= e(ucwords(str_replace('_', ' ', $k))) ?></label>
                            <input class="form-control" name="config[<?= e($k) ?>]" value="<?= e($v) ?>" <?= str_contains($k, 'key') || str_contains($k, 'salt') ? 'type="password"' : '' ?>>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
<?php endforeach; ?>

