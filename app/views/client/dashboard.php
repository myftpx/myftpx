<div class="stats-grid">
    <div class="stat-card"><span class="ico">◈</span><div class="label">Hizmetlerim</div><div class="value"><?= (int)$stats['services'] ?></div></div>
    <div class="stat-card"><span class="ico">⛓</span><div class="label">Alan Adlarım</div><div class="value"><?= (int)$stats['domains'] ?></div></div>
    <div class="stat-card"><span class="ico">✉</span><div class="label">Açık Bilet</div><div class="value"><?= (int)$stats['open_tickets'] ?></div></div>
    <div class="stat-card"><span class="ico">▤</span><div class="label">Ödenmemiş Fatura</div><div class="value"><?= (int)$stats['unpaid_invoices'] ?></div></div>
    <div class="stat-card"><span class="ico">◉</span><div class="label">Bakiye</div><div class="value"><?= money($stats['balance']) ?></div></div>
</div>

<div class="card">
    <div class="card-header"><h3>Hizmetlerim</h3><a class="btn btn-outline btn-sm" href="<?= url('client/services') ?>">Tümü</a></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Hizmet</th><th>Alan Adı</th><th>Tutar</th><th>Son Ödeme</th><th>Durum</th></tr>
        <?php if (empty($recentServices)): ?><tr><td colspan="5" class="empty">Henüz hizmetiniz yok. <a href="<?= url('/') ?>">Sipariş verin</a></td></tr><?php endif; ?>
        <?php foreach ($recentServices as $s): ?>
            <tr>
                <td><a href="<?= url('client/services/' . $s['id']) ?>"><?= e($s['product_name']) ?></a></td>
                <td><?= e($s['domain'] ?: '—') ?></td>
                <td><?= money($s['amount']) ?></td>
                <td><?= e($s['next_due_date']) ?></td>
                <td><?= status_badge($s['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3>Son Biletlerim</h3><a class="btn btn-primary btn-sm" href="<?= url('client/tickets/new') ?>">Yeni Bilet</a></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Bilet</th><th>Konu</th><th>Durum</th><th>Son Yanıt</th></tr>
        <?php if (empty($recentTickets)): ?><tr><td colspan="4" class="empty">Bilet yok</td></tr><?php endif; ?>
        <?php foreach ($recentTickets as $t): ?>
            <tr>
                <td><a href="<?= url('client/tickets/' . $t['id']) ?>">#<?= e($t['ticket_number']) ?></a></td>
                <td><?= e($t['subject']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td class="text-muted"><?= e(time_ago($t['last_reply_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3>Son Faturalarım</h3><a class="btn btn-outline btn-sm" href="<?= url('client/invoices') ?>">Tümü</a></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Fatura</th><th>Tutar</th><th>Durum</th><th>Tarih</th></tr>
        <?php if (empty($recentInvoices)): ?><tr><td colspan="4" class="empty">Fatura yok</td></tr><?php endif; ?>
        <?php foreach ($recentInvoices as $i): ?>
            <tr>
                <td><a href="<?= url('client/invoices/' . $i['id']) ?>"><?= e($i['invoice_number']) ?></a></td>
                <td><?= money($i['total']) ?></td>
                <td><?= status_badge($i['status']) ?></td>
                <td class="text-muted"><?= e(date('d.m.Y', strtotime($i['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
