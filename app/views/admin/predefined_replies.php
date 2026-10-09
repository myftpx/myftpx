<div class="page-head"><h1>Hazır Yanıtlar</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Hazır Yanıt</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/predefined-replies/add') ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Ad</label><input class="form-control" name="name" required></div>
            <div class="form-group"><label>İçerik</label><textarea class="form-control" name="body" rows="4"></textarea></div>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Ad</th><th>İçerik</th><th></th></tr>
        <?php if (empty($replies)): ?><tr><td colspan="3" class="empty">Hazır yanıt yok.</td></tr><?php endif; ?>
        <?php foreach ($replies as $r): ?>
            <tr><td><strong><?= e($r['name']) ?></strong></td><td class="text-muted"><?= e(mb_substr($r['body'], 0, 80)) ?></td>
            <td><form method="post" action="<?= url('admin/predefined-replies/' . $r['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
