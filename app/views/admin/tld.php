<div class="page-head"><h1>TLD Fiyatlandırma</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Uzantı</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/tld/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row-3">
                <div class="form-group"><label>Uzantı (TLD)</label><input class="form-control" name="tld" placeholder=".com" required></div>
                <div class="form-group"><label>Kayıt Fiyatı</label><input class="form-control" type="number" step="0.01" name="register_price" required></div>
                <div class="form-group"><label>Transfer Fiyatı</label><input class="form-control" type="number" step="0.01" name="transfer_price"></div>
            </div>
            <div class="form-group"><label>Yenileme Fiyatı</label><input class="form-control" type="number" step="0.01" name="renew_price"></div>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Uzantı</th><th>Kayıt</th><th>Transfer</th><th>Yenileme</th><th></th></tr>
        <?php if (empty($tlds)): ?><tr><td colspan="5" class="empty">Uzantı yok.</td></tr><?php endif; ?>
        <?php foreach ($tlds as $t): ?>
            <tr><td><strong><?= e($t['tld']) ?></strong></td><td><?= money($t['register_price']) ?></td><td><?= money($t['transfer_price']) ?></td><td><?= money($t['renew_price']) ?></td>
            <td><form method="post" action="<?= url('admin/tld/' . $t['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
