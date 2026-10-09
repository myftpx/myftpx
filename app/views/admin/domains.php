<div class="page-head"><h1>Alan Adları</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Alan Adı</th><th>Müşteri</th><th>Kayıt Firması</th><th>Bitiş</th><th>Durum</th><th></th></tr>
        <?php if (empty($domains)): ?><tr><td colspan="6" class="empty">Alan adı yok.</td></tr><?php endif; ?>
        <?php foreach ($domains as $d): ?>
            <tr>
                <td><strong><?= e($d['domain']) ?></strong></td>
                <td><?= e($d['first_name'] . ' ' . $d['last_name']) ?></td>
                <td><?= e($d['registrar'] ?: '—') ?></td>
                <td><?= e($d['expiry_date'] ?: '—') ?></td>
                <td><?= status_badge($d['status']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('admin/domains/' . $d['id']) ?>">Yönet</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
