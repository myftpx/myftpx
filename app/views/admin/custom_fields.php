<div class="page-head"><h1>Özel Alanlar (Müşteri)</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Alan</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/custom-fields/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Alan Adı</label><input class="form-control" name="name" required></div>
                <div class="form-group"><label>Tip</label><select class="form-control" name="field_type"><option value="text">Metin</option><option value="textarea">Uzun Metin</option><option value="dropdown">Dropdown</option></select></div>
            </div>
            <div class="form-group"><label>Seçenekler (virgülle, dropdown için)</label><input class="form-control" name="options"></div>
            <label class="form-check mb-2"><input type="checkbox" name="required" value="1"> Zorunlu</label>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Alan</th><th>Tip</th><th>Zorunlu</th><th></th></tr>
        <?php if (empty($fields)): ?><tr><td colspan="4" class="empty">Alan yok.</td></tr><?php endif; ?>
        <?php foreach ($fields as $f): ?>
            <tr><td><strong><?= e($f['name']) ?></strong></td><td><?= e($f['field_type']) ?></td><td><?= $f['required'] ? 'Evet' : 'Hayır' ?></td>
            <td><form method="post" action="<?= url('admin/custom-fields/' . $f['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
