<div class="stats-grid">
    <div class="stat-card"><span class="ico">👥</span><div class="label">Müşteriler</div><div class="value"><?= (int)$stats['clients'] ?></div></div>
    <div class="stat-card"><span class="ico">◉</span><div class="label">Aktif Hizmet</div><div class="value"><?= (int)$stats['services'] ?></div></div>
    <div class="stat-card"><span class="ico">⛓</span><div class="label">Alan Adları</div><div class="value"><?= (int)$stats['domains'] ?></div></div>
    <div class="stat-card"><span class="ico">✉</span><div class="label">Açık Bilet</div><div class="value"><?= (int)$stats['open_tickets'] ?></div></div>
    <div class="stat-card"><span class="ico">▤</span><div class="label">Ödenmemiş Fatura</div><div class="value"><?= (int)$stats['unpaid_invoices'] ?></div></div>
    <div class="stat-card"><span class="ico">₺</span><div class="label">Toplam Gelir</div><div class="value"><?= money($stats['revenue']) ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="dash-grid">
    <div class="card">
        <div class="card-header"><h3>Son Destek Biletleri</h3><a class="btn btn-outline btn-sm" href="<?= url('admin/tickets') ?>">Tümü</a></div>
        <div class="table-wrap"><table class="table">
            <tr><th>Bilet</th><th>Müşteri</th><th>Konu</th><th>Durum</th></tr>
            <?php if (empty($recentTickets)): ?><tr><td colspan="4" class="empty">Bilet yok</td></tr><?php endif; ?>
            <?php foreach ($recentTickets as $t): ?>
                <tr>
                    <td><a href="<?= url('admin/tickets/' . $t['id']) ?>">#<?= e($t['ticket_number']) ?></a></td>
                    <td><?= e($t['first_name'] . ' ' . $t['last_name']) ?></td>
                    <td class="nowrap" style="max-width:180px;overflow:hidden;text-overflow:ellipsis"><?= e($t['subject']) ?></td>
                    <td><?= status_badge($t['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Son Faturalar</h3><a class="btn btn-outline btn-sm" href="<?= url('admin/invoices') ?>">Tümü</a></div>
        <div class="table-wrap"><table class="table">
            <tr><th>Fatura</th><th>Müşteri</th><th>Tutar</th><th>Durum</th></tr>
            <?php if (empty($recentInvoices)): ?><tr><td colspan="4" class="empty">Fatura yok</td></tr><?php endif; ?>
            <?php foreach ($recentInvoices as $i): ?>
                <tr>
                    <td><a href="<?= url('admin/invoices/' . $i['id']) ?>"><?= e($i['invoice_number']) ?></a></td>
                    <td><?= e($i['first_name'] . ' ' . $i['last_name']) ?></td>
                    <td><?= money($i['total']) ?></td>
                    <td><?= status_badge($i['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3>Son Siparişler</h3><a class="btn btn-outline btn-sm" href="<?= url('admin/orders') ?>">Tümü</a></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Sipariş</th><th>Müşteri</th><th>Ürün</th><th>Tutar</th><th>Durum</th></tr>
        <?php if (empty($recentOrders)): ?><tr><td colspan="5" class="empty">Sipariş yok</td></tr><?php endif; ?>
        <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td><a href="<?= url('admin/orders/' . $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                <td><?= e($o['first_name'] . ' ' . $o['last_name']) ?></td>
                <td><?= e($o['pname']) ?></td>
                <td><?= money($o['amount']) ?></td>
                <td><?= status_badge($o['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
<style>@media(max-width:800px){.dash-grid{grid-template-columns:1fr}}</style>
