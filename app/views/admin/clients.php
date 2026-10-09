<div class="page-head">
    <h1>Müşteriler</h1>
    <form method="get" class="flex gap-2">
        <input class="form-control" name="q" placeholder="Ara (isim/e-posta)" value="<?= e($q) ?>">
        <button class="btn btn-outline" type="submit">Ara</button>
    </form>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>ID</th><th>Müşteri</th><th>E-posta</th><th>Bakiye</th><th>Durum</th><th>Kayıt</th><th></th></tr>
        <?php if (empty($clients)): ?><tr><td colspan="7" class="empty">Müşteri bulunamadı.</td></tr><?php endif; ?>
        <?php foreach ($clients as $c): ?>
            <tr>
                <td>#<?= (int)$c['id'] ?></td>
                <td><strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong><?= $c['company'] ? ' <span class="text-muted small">(' . e($c['company']) . ')</span>' : '' ?></td>
                <td><?= e($c['email']) ?></td>
                <td><?= money($c['balance']) ?></td>
                <td><?= status_badge($c['status']) ?></td>
                <td class="text-muted"><?= e(date('d.m.Y', strtotime($c['created_at']))) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('admin/clients/' . $c['id']) ?>">Yönet</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
