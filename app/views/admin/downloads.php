<div class="page-head"><h1>İndirmeler</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni İndirme</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/downloads/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Ad</label><input class="form-control" name="name" required></div>
                <div class="form-group"><label>Kategori</label><input class="form-control" name="category"></div>
            </div>
            <div class="form-group"><label>Dosya URL</label><input class="form-control" name="file_path"></div>
            <div class="form-group"><label>Açıklama</label><input class="form-control" name="description"></div>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Dosya</th><th>Kategori</th><th>URL</th><th></th></tr>
        <?php if (empty($downloads)): ?><tr><td colspan="4" class="empty">İndirme yok.</td></tr><?php endif; ?>
        <?php foreach ($downloads as $d): ?>
            <tr><td><strong><?= e($d['name']) ?></strong></td><td><?= e($d['category'] ?: '—') ?></td><td class="mono small"><?= e($d['file_path']) ?></td>
            <td><form method="post" action="<?= url('admin/downloads/' . $d['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
