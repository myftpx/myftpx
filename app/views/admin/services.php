<div class="page-head"><h1>Hizmetler</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>ID</th><th>Müşteri</th><th>Ürün</th><th>Alan Adı</th><th>Tutar</th><th>Son Ödeme</th><th>Durum</th><th></th></tr>
        <?php if (empty($services)): ?><tr><td colspan="8" class="empty">Hizmet yok.</td></tr><?php endif; ?>
        <?php foreach ($services as $s): ?>
            <tr>
                <td>#<?= (int)$s['id'] ?></td>
                <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td><?= e($s['pname']) ?></td>
                <td><?= e($s['domain'] ?: '—') ?></td>
                <td><?= money($s['amount']) ?></td>
                <td><?= e($s['next_due_date'] ?: '—') ?></td>
                <td><?= status_badge($s['status']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('admin/services/' . $s['id']) ?>">Yönet</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
