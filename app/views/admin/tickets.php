<div class="page-head">
    <h1>Destek Biletleri</h1>
    <div class="filter-bar">
        <a class="btn <?= $status === '' ? 'btn-primary' : 'btn-outline' ?> btn-sm" href="<?= url('admin/tickets') ?>">Tümü</a>
        <?php foreach (['open' => 'Açık', 'answered' => 'Yanıtlandı', 'closed' => 'Kapalı'] as $v => $l): ?>
            <a class="btn <?= $status === $v ? 'btn-primary' : 'btn-outline' ?> btn-sm" href="<?= url('admin/tickets?status=' . $v) ?>"><?= $l ?></a>
        <?php endforeach; ?>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Bilet</th><th>Müşteri</th><th>Konu</th><th>Departman</th><th>Öncelik</th><th>Durum</th><th>Son Yanıt</th></tr>
        <?php if (empty($tickets)): ?><tr><td colspan="7" class="empty">Bilet yok.</td></tr><?php endif; ?>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td><a href="<?= url('admin/tickets/' . $t['id']) ?>">#<?= e($t['ticket_number']) ?></a></td>
                <td><?= e($t['first_name'] . ' ' . $t['last_name']) ?></td>
                <td><?= e($t['subject']) ?></td>
                <td><?= e($t['department']) ?></td>
                <td><?= status_badge($t['priority']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td class="text-muted"><?= e(time_ago($t['last_reply_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
