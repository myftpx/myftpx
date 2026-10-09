<div class="page-head">
    <h1><?= e($order['order_number']) ?></h1>
    <a class="btn btn-outline" href="<?= url('admin/orders') ?>">Geri</a>
</div>
<div class="card mb-3">
    <div class="card-header"><h3>Sipariş Detayı</h3><?= status_badge($order['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Müşteri</span><span class="v"><?= e($order['first_name'] . ' ' . $order['last_name']) ?></span></div>
            <div class="info-row"><span class="k">Ürün</span><span class="v"><?= e($order['pname']) ?></span></div>
            <div class="info-row"><span class="k">Alan Adı</span><span class="v"><?= e($order['domain'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Dönem</span><span class="v"><?= e($order['billing_cycle']) ?></span></div>
            <div class="info-row"><span class="k">Tutar</span><span class="v"><?= money($order['amount']) ?></span></div>
            <div class="info-row"><span class="k">Ödeme</span><span class="v"><?= e($order['payment_method']) ?></span></div>
            <div class="info-row"><span class="k">Tarih</span><span class="v"><?= e($order['created_at']) ?></span></div>
        </div>
    </div>
</div>
<div class="card" style="max-width:520px">
    <div class="card-header"><h3>Durum Güncelle</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/orders/' . $order['id']) ?>">
            <?= csrf_field() ?>
            <select class="form-control mb-2" name="status">
                <?php foreach (['pending' => 'Beklemede', 'active' => 'Aktif', 'cancelled' => 'İptal'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $order['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Güncelle</button>
        </form>
    </div>
</div>
