<div class="page-head"><h1>Promosyonlar / Kupon Kodları</h1></div>
<div class="card mb-3" style="max-width:760px">
    <div class="card-header"><h3>Yeni Promosyon</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/promotions/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row-3">
                <div class="form-group"><label>Kupon Kodu</label><input class="form-control" name="code" placeholder="WELCOME10" required></div>
                <div class="form-group"><label>İndirim Tipi</label><select class="form-control" name="discount_type"><option value="percent">Yüzde (%)</option><option value="fixed">Sabit Tutar</option></select></div>
                <div class="form-group"><label>İndirim Değeri</label><input class="form-control" type="number" step="0.01" name="discount_value" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Geçerlilik Başlangıç</label><input class="form-control" type="date" name="valid_from"></div>
                <div class="form-group"><label>Geçerlilik Bitiş</label><input class="form-control" type="date" name="valid_until"></div>
            </div>
            <div class="form-group"><label>Maksimum Kullanım (0 = sınırsız)</label><input class="form-control" type="number" name="max_uses" value="0"></div>
            <button class="btn btn-primary" type="submit">Oluştur</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Kod</th><th>Tip</th><th>Değer</th><th>Kullanım</th><th>Geçerlilik</th><th></th></tr>
        <?php if (empty($promos)): ?><tr><td colspan="6" class="empty">Promosyon yok.</td></tr><?php endif; ?>
        <?php foreach ($promos as $p): ?>
            <tr><td><strong class="mono"><?= e($p['code']) ?></strong></td><td><?= $p['discount_type'] === 'percent' ? '%' : '₺' ?></td><td><?= e($p['discount_value']) ?></td><td><?= (int)$p['used'] ?>/<?= (int)$p['max_uses'] ?: '∞' ?></td>
            <td><?= e($p['valid_until'] ?: '—') ?></td>
            <td><form method="post" action="<?= url('admin/promotions/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
