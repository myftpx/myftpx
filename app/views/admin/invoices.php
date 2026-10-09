<div class="page-head">
    <h1>Faturalar</h1>
    <a class="btn btn-primary" href="<?= url('admin/invoices/new') ?>">Yeni Fatura</a>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Fatura</th><th>Müşteri</th><th>Tutar</th><th>KDV</th><th>Toplam</th><th>Durum</th><th>Son Ödeme</th><th></th></tr>
        <?php if (empty($invoices)): ?><tr><td colspan="8" class="empty">Fatura yok.</td></tr><?php endif; ?>
        <?php foreach ($invoices as $i): ?>
            <tr>
                <td><a href="<?= url('admin/invoices/' . $i['id']) ?>"><?= e($i['invoice_number']) ?></a></td>
                <td><?= e($i['first_name'] . ' ' . $i['last_name']) ?></td>
                <td><?= money($i['amount']) ?></td>
                <td><?= money($i['tax']) ?></td>
                <td><strong><?= money($i['total']) ?></strong></td>
                <td><?= status_badge($i['status']) ?></td>
                <td><?= e($i['due_date'] ?: '—') ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('admin/invoices/' . $i['id']) ?>">Detay</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
