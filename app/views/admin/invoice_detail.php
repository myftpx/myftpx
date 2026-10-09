<div class="page-head">
    <h1><?= e($invoice['invoice_number']) ?></h1>
    <div class="flex gap-2">
        <?php if ($invoice['status'] === 'unpaid'): ?>
            <form method="post" action="<?= url('admin/invoices/' . $invoice['id'] . '/mark-paid') ?>"><?= csrf_field() ?><button class="btn btn-success btn-sm" type="submit">Ödendi İşaretle</button></form>
        <?php endif; ?>
        <form method="post" action="<?= url('admin/invoices/' . $invoice['id'] . '/delete') ?>" onsubmit="return confirm('Bu faturayı silmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form>
    </div>
</div>
<div class="card mb-3">
    <div class="card-header"><h3>Detay</h3><?= status_badge($invoice['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Müşteri</span><span class="v"><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?> (<?= e($invoice['email']) ?>)</span></div>
            <div class="info-row"><span class="k">Tarih</span><span class="v"><?= e($invoice['created_at']) ?></span></div>
            <div class="info-row"><span class="k">Son Ödeme</span><span class="v"><?= e($invoice['due_date'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Ödeme Tarihi</span><span class="v"><?= e($invoice['paid_at'] ?: '—') ?></span></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3>Kalemler</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Açıklama</th><th class="text-right">Tutar</th></tr>
        <?php foreach ($items as $it): ?>
            <tr><td><?= e($it['description']) ?></td><td class="text-right"><?= money($it['amount']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td class="text-right"><strong>Ara Toplam</strong></td><td class="text-right"><?= money($invoice['amount']) ?></td></tr>
        <tr><td class="text-right">KDV</td><td class="text-right"><?= money($invoice['tax']) ?></td></tr>
        <tr><td class="text-right"><strong>Toplam</strong></td><td class="text-right"><strong><?= money($invoice['total']) ?></strong></td></tr>
    </table></div>
</div>
