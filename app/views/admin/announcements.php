<div class="page-head"><h1>Duyurular</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Duyuru</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/announcements/add') ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Başlık</label><input class="form-control" name="title" required></div>
            <div class="form-group"><label>İçerik</label><textarea class="form-control" name="body" rows="4"></textarea></div>
            <label class="form-check mb-2"><input type="checkbox" name="status" value="1" checked> Yayınla</label>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Başlık</th><th>Tarih</th><th>Durum</th><th></th></tr>
        <?php if (empty($items)): ?><tr><td colspan="4" class="empty">Duyuru yok.</td></tr><?php endif; ?>
        <?php foreach ($items as $a): ?>
            <tr><td><?= e($a['title']) ?></td><td><?= e(date('d.m.Y', strtotime($a['published_at']))) ?></td><td><?= $a['status'] ? '<span class="badge badge-success">Yayında</span>' : '<span class="badge">Gizli</span>' ?></td>
            <td><form method="post" action="<?= url('admin/announcements/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
