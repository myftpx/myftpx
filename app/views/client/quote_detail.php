<div class="page-head">
    <h1><?= e($quote['quote_number']) ?></h1>
    <a class="btn btn-outline" href="<?= url('client/quotes') ?>">Geri</a>
</div>
<div class="card mb-3">
    <div class="card-header"><h3>Teklif Detayı</h3><?= status_badge($quote['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Geçerlilik</span><span class="v"><?= e($quote['valid_until'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Tarih</span><span class="v"><?= e($quote['created_at']) ?></span></div>
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
        <tr><td class="text-right"><strong>Toplam</strong></td><td class="text-right"><strong><?= money($quote['total']) ?></strong></td></tr>
    </table></div>
</div>
