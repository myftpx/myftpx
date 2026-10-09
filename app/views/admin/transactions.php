<div class="page-head"><h1>İşlemler</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>ID</th><th>Müşteri</th><th>Tutar</th><th>Yöntem</th><th>Referans</th><th>Durum</th><th>Tarih</th><th></th></tr>
        <?php if (empty($transactions)): ?><tr><td colspan="8" class="empty">İşlem yok.</td></tr><?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td>#<?= (int)$t['id'] ?></td>
                <td><?= e($t['first_name'] . ' ' . $t['last_name']) ?></td>
                <td><?= money($t['amount']) ?></td>
                <td><?= e($t['gateway']) ?></td>
                <td class="mono"><?= e($t['transaction_id']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td class="text-muted"><?= e($t['created_at']) ?></td>
                <td>
                    <?php if ($t['status'] === 'pending' && $t['gateway'] === 'bank_transfer'): ?>
                        <div class="flex gap-1">
                            <form method="post" action="<?= url('admin/transactions/' . $t['id'] . '/confirm') ?>"><?= csrf_field() ?><button class="btn btn-success btn-sm" type="submit">Onayla</button></form>
                            <form method="post" action="<?= url('admin/transactions/' . $t['id'] . '/deny') ?>" onsubmit="return confirm('Bu havale bildirimini reddetmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Reddet</button></form>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
