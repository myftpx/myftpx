<div class="page-head"><h1>Faturalarım</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Fatura</th><th>Tutar</th><th>Durum</th><th>Son Ödeme</th><th>Tarih</th><th></th></tr>
        <?php if (empty($invoices)): ?><tr><td colspan="6" class="empty">Fatura yok.</td></tr><?php endif; ?>
        <?php foreach ($invoices as $i): ?>
            <tr>
                <td><strong><?= e($i['invoice_number']) ?></strong></td>
                <td><?= money($i['total']) ?></td>
                <td><?= status_badge($i['status']) ?></td>
                <td><?= e($i['due_date'] ?: '—') ?></td>
                <td class="text-muted"><?= e(date('d.m.Y', strtotime($i['created_at']))) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('client/invoices/' . $i['id']) ?>">Görüntüle</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
