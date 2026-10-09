<div class="inv-head">
    <div><div class="brand"><?= e(setting('site_name', 'RCVXTR')) ?></div>
    <div class="meta"><?= e(setting('admin_email', '')) ?></div></div>
    <div style="text-align:right">
        <h1><?= e($invoice['invoice_number']) ?></h1>
        <div class="meta">Tarih: <?= e(date('d.m.Y', strtotime($invoice['created_at']))) ?></div>
        <div class="meta">Son Ödeme: <?= e($invoice['due_date'] ?: '—') ?></div>
    </div>
</div>
<div class="meta mb-2">
    <strong><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></strong><?= $invoice['company'] ? ' · ' . e($invoice['company']) : '' ?><br>
    <?= e($invoice['address']) ?><?= $invoice['city'] ? ', ' . e($invoice['city']) : '' ?><?= $invoice['country'] ? ', ' . e($invoice['country']) : '' ?><br>
    <?= e($invoice['email']) ?>
</div>
<table>
    <tr><th>Açıklama</th><th style="text-align:right">Tutar</th></tr>
    <?php foreach ($items as $it): ?>
        <tr><td><?= e($it['description']) ?></td><td style="text-align:right"><?= money($it['amount']) ?></td></tr>
    <?php endforeach; ?>
</table>
<table class="totals">
    <tr><td>Ara Toplam</td><td style="text-align:right"><?= money($invoice['amount']) ?></td></tr>
    <?php if ((float)$invoice['discount'] > 0): ?>
    <tr><td>İndirim <?= $invoice['promo_code'] ? '(' . e($invoice['promo_code']) . ')' : '' ?></td><td style="text-align:right">-<?= money($invoice['discount']) ?></td></tr>
    <?php endif; ?>
    <tr><td>KDV</td><td style="text-align:right"><?= money($invoice['tax']) ?></td></tr>
    <tr class="grand"><td>TOPLAM</td><td style="text-align:right"><?= money($invoice['total']) ?></td></tr>
</table>
<div class="meta" style="margin-top:30px"><?= e(setting('site_name', 'RCVXTR')) ?> — Bu fatura <?= e(setting('site_name', 'RCVXTR')) ?> sistemi tarafından oluşturulmuştur.</div>
