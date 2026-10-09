<div class="stats-grid">
    <div class="stat-card"><span class="ico">👥</span><div class="label">Müşteriler</div><div class="value"><?= (int)$stats['clients'] ?></div></div>
    <div class="stat-card"><span class="ico">◉</span><div class="label">Aktif Hizmet</div><div class="value"><?= (int)$stats['services'] ?></div></div>
    <div class="stat-card"><span class="ico">⛓</span><div class="label">Alan Adları</div><div class="value"><?= (int)$stats['domains'] ?></div></div>
    <div class="stat-card"><span class="ico">✉</span><div class="label">Açık Bilet</div><div class="value"><?= (int)$stats['open_tickets'] ?></div></div>
    <div class="stat-card"><span class="ico">▤</span><div class="label">Ödenmemiş Fatura</div><div class="value"><?= (int)$stats['unpaid_invoices'] ?></div></div>
    <div class="stat-card"><span class="ico">₺</span><div class="label">Toplam Gelir</div><div class="value"><?= money($stats['revenue']) ?></div></div>
</div>

<?php
$chartDriver = db()->getAttribute(\PDO::ATTR_DRIVER_NAME);
if ($chartDriver === 'mysql') {
    $rev = db()->query("SELECT DATE_FORMAT(paid_at, '%m.%Y') lbl, SUM(total) s FROM invoices WHERE status='paid' AND paid_at IS NOT NULL GROUP BY DATE_FORMAT(paid_at, '%Y-%m') ORDER BY DATE_FORMAT(paid_at, '%Y-%m') DESC LIMIT 8")->fetchAll();
} else {
    $rev = db()->query("SELECT strftime('%m.%Y', paid_at) lbl, SUM(total) s FROM invoices WHERE status='paid' AND paid_at IS NOT NULL GROUP BY strftime('%Y-%m', paid_at) ORDER BY strftime('%Y-%m', paid_at) DESC LIMIT 8")->fetchAll();
}
$rev = array_reverse($rev);
$maxRev = 0; foreach ($rev as $r) { if ((float)$r['s'] > $maxRev) $maxRev = (float)$r['s']; }
?>
<div class="card mb-3">
    <div class="card-header"><h3>Gelir Grafiği (son aylar)</h3></div>
    <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:10px;height:180px;padding:10px 0">
            <?php foreach ($rev as $r): $h = $maxRev > 0 ? max(6, (float)$r['s'] / $maxRev * 150) : 6; ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
                    <div style="width:100%;height:<?= (int)$h ?>px;background:linear-gradient(180deg,var(--primary),var(--accent));border-radius:6px 6px 0 0" title="<?= money($r['s']) ?>"></div>
                    <div class="small text-muted"><?= e($r['lbl']) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($rev)): ?><div class="text-muted">Henüz veri yok.</div><?php endif; ?>
        </div>
    </div>
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
