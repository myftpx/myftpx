<div class="page-head"><h1>İndirmeler</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Dosya</th><th>Kategori</th><th>Açıklama</th><th></th></tr>
        <?php if (empty($downloads)): ?><tr><td colspan="4" class="empty">İndirme yok.</td></tr><?php endif; ?>
        <?php foreach ($downloads as $d): ?>
            <tr><td><strong><?= e($d['name']) ?></strong></td><td><?= e($d['category'] ?: '—') ?></td><td class="text-muted"><?= e($d['description']) ?></td>
            <td><?php if ($d['file_path']): ?><a class="btn btn-outline btn-sm" href="<?= e($d['file_path']) ?>">İndir</a><?php endif; ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
