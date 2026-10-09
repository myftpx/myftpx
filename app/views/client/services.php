<div class="page-head">
    <h1>Hizmetlerim</h1>
    <a class="btn btn-primary" href="<?= url('/') ?>">Yeni Hizmet Al</a>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Hizmet</th><th>Tür</th><th>Alan Adı</th><th>Tutar</th><th>Son Ödeme</th><th>Durum</th><th></th></tr>
        <?php if (empty($services)): ?><tr><td colspan="7" class="empty">Henüz hizmetiniz yok.</td></tr><?php endif; ?>
        <?php foreach ($services as $s): ?>
            <tr>
                <td><strong><?= e($s['product_name']) ?></strong></td>
                <td><?= e($s['product_type']) ?></td>
                <td><?= e($s['domain'] ?: '—') ?></td>
                <td><?= money($s['amount']) ?> / <?= e($s['billing_cycle']) ?></td>
                <td><?= e($s['next_due_date'] ?: '—') ?></td>
                <td><?= status_badge($s['status']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('client/services/' . $s['id']) ?>">Yönet</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
