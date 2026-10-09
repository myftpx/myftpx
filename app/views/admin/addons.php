<div class="page-head"><h1>Ürün Eklentileri</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Eklenti</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/addons/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Eklenti Adı</label><input class="form-control" name="name" required></div>
                <div class="form-group"><label>Fiyat</label><input class="form-control" type="number" step="0.01" name="price"></div>
            </div>
            <div class="form-group"><label>Açıklama</label><input class="form-control" name="description"></div>
            <div class="form-group"><label>Dönem</label><select class="form-control" name="billing_cycle">
                <option value="monthly">Aylık</option><option value="annually">Yıllık</option>
            </select></div>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Eklenti</th><th>Fiyat</th><th>Dönem</th><th></th></tr>
        <?php if (empty($addons)): ?><tr><td colspan="4" class="empty">Eklenti yok.</td></tr><?php endif; ?>
        <?php foreach ($addons as $a): ?>
            <tr><td><strong><?= e($a['name']) ?></strong><div class="small text-muted"><?= e($a['description']) ?></div></td><td><?= money($a['price']) ?></td><td><?= e($a['billing_cycle']) ?></td>
            <td><form method="post" action="<?= url('admin/addons/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
