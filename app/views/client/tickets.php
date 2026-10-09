<div class="page-head">
    <h1>Destek Biletlerim</h1>
    <a class="btn btn-primary" href="<?= url('client/tickets/new') ?>">Yeni Bilet</a>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Bilet</th><th>Konu</th><th>Departman</th><th>Öncelik</th><th>Durum</th><th>Son Yanıt</th></tr>
        <?php if (empty($tickets)): ?><tr><td colspan="6" class="empty">Bilet yok.</td></tr><?php endif; ?>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td><a href="<?= url('client/tickets/' . $t['id']) ?>">#<?= e($t['ticket_number']) ?></a></td>
                <td><?= e($t['subject']) ?></td>
                <td><?= e($t['department']) ?></td>
                <td><?= status_badge($t['priority']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td class="text-muted"><?= e(time_ago($t['last_reply_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
