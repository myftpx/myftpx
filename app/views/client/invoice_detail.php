<div class="page-head">
    <h1>Fatura <?= e($invoice['invoice_number']) ?></h1>
    <a class="btn btn-outline" href="<?= url('client/invoices') ?>">Geri</a>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Fatura Detayı</h3><?= status_badge($invoice['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Fatura No</span><span class="v"><?= e($invoice['invoice_number']) ?></span></div>
            <div class="info-row"><span class="k">Oluşturma</span><span class="v"><?= e(date('d.m.Y H:i', strtotime($invoice['created_at']))) ?></span></div>
            <div class="info-row"><span class="k">Son Ödeme</span><span class="v"><?= e($invoice['due_date'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Ödeme Tarihi</span><span class="v"><?= e($invoice['paid_at'] ? date('d.m.Y H:i', strtotime($invoice['paid_at'])) : '—') ?></span></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Kalemler</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Açıklama</th><th class="text-right">Tutar</th></tr>
        <?php foreach ($items as $it): ?>
            <tr><td><?= e($it['description']) ?></td><td class="text-right"><?= money($it['amount']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td class="text-right"><strong>Ara Toplam</strong></td><td class="text-right"><?= money($invoice['amount']) ?></td></tr>
        <tr><td class="text-right">KDV (%<?= e(setting('tax_rate', '0')) ?>)</td><td class="text-right"><?= money($invoice['tax']) ?></td></tr>
        <tr><td class="text-right"><strong>Toplam</strong></td><td class="text-right"><strong><?= money($invoice['total']) ?></strong></td></tr>
    </table></div>
</div>

<?php if ($invoice['status'] === 'unpaid'): ?>
<div class="card">
    <div class="card-header"><h3>Ödeme</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/invoices/' . $invoice['id'] . '/pay') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Ödeme Yöntemi</label>
                <select class="form-control" name="gateway">
                    <option value="balance">Bakiye (<?= money(auth()->user()['balance']) ?>)</option>
                    <?php foreach ($gateways as $g): ?>
                        <option value="<?= e($g['code']) ?>"><?= e($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-success btn-lg" type="submit">Ödemeyi Tamamla (<?= money($invoice['total']) ?>)</button>
        </form>
    </div>
</div>
<?php endif; ?>
