<div class="page-head"><h1>İşlemler</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>ID</th><th>Müşteri</th><th>Tutar</th><th>Yöntem</th><th>Referans</th><th>Durum</th><th>Tarih</th></tr>
        <?php if (empty($transactions)): ?><tr><td colspan="7" class="empty">İşlem yok.</td></tr><?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td>#<?= (int)$t['id'] ?></td>
                <td><?= e($t['first_name'] . ' ' . $t['last_name']) ?></td>
                <td><?= money($t['amount']) ?></td>
                <td><?= e($t['gateway']) ?></td>
                <td class="mono"><?= e($t['transaction_id']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td class="text-muted"><?= e($t['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
