<div class="page-head"><h1>Raporlar</h1></div>

<div class="stats-grid">
    <div class="stat-card"><span class="ico">₺</span><div class="label">Toplam Gelir (ödendi)</div><div class="value"><?= money(array_sum(array_column(db()->query("SELECT total FROM invoices WHERE status='paid'")->fetchAll(), 'total'))) ?></div></div>
    <div class="stat-card"><span class="ico">▤</span><div class="label">Toplam Fatura</div><div class="value"><?= (int)db()->query('SELECT COUNT(*) FROM invoices')->fetchColumn() ?></div></div>
    <div class="stat-card"><span class="ico">◈</span><div class="label">Toplam Hizmet</div><div class="value"><?= (int)db()->query('SELECT COUNT(*) FROM services')->fetchColumn() ?></div></div>
    <div class="stat-card"><span class="ico">👥</span><div class="label">Toplam Müşteri</div><div class="value"><?= (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn() ?></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Aylık Gelir (son 12 ay)</h3></div>
    <div class="card-body">
        <div class="table-wrap"><table class="table">
            <tr><th>Ay</th><th class="text-right">Gelir</th></tr>
            <?php if (empty($monthly)): ?><tr><td colspan="2" class="empty">Veri yok</td></tr><?php endif; ?>
            <?php foreach ($monthly as $m): ?>
                <tr><td><?= e($m['ym']) ?></td><td class="text-right"><?= money($m['s']) ?></td></tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Ödeme Yöntemine Göre</h3></div>
    <div class="card-body">
        <div class="table-wrap"><table class="table">
            <tr><th>Yöntem</th><th>İşlem Sayısı</th><th class="text-right">Toplam</th></tr>
            <?php if (empty($byGateway)): ?><tr><td colspan="3" class="empty">Veri yok</td></tr><?php endif; ?>
            <?php foreach ($byGateway as $g): ?>
                <tr><td><?= e($g['gateway'] ?: '—') ?></td><td><?= (int)$g['c'] ?></td><td class="text-right"><?= money($g['s']) ?></td></tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>
