<div class="page-head"><h1>Siparişler</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Sipariş</th><th>Müşteri</th><th>Ürün</th><th>Alan Adı</th><th>Tutar</th><th>Durum</th><th></th></tr>
        <?php if (empty($orders)): ?><tr><td colspan="7" class="empty">Sipariş yok.</td></tr><?php endif; ?>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="<?= url('admin/orders/' . $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                <td><?= e($o['first_name'] . ' ' . $o['last_name']) ?></td>
                <td><?= e($o['pname']) ?></td>
                <td><?= e($o['domain'] ?: '—') ?></td>
                <td><?= money($o['amount']) ?></td>
                <td><?= status_badge($o['status']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('admin/orders/' . $o['id']) ?>">Detay</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
