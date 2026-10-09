<div class="page-head"><h1>İptal Talepleri</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Müşteri</th><th>Hizmet</th><th>Tip</th><th>Sebep</th><th>Durum</th><th>Tarih</th><th></th></tr>
        <?php if (empty($requests)): ?><tr><td colspan="7" class="empty">İptal talebi yok.</td></tr><?php endif; ?>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                <td><?= e($r['domain'] ?: ('#' . $r['service_id'])) ?></td>
                <td><?= $r['type'] === 'immediate' ? 'Hemen' : 'Dönem Sonu' ?></td>
                <td class="text-muted"><?= e($r['reason']) ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td class="text-muted"><?= e(date('d.m.Y', strtotime($r['created_at']))) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                    <div class="flex gap-1">
                        <form method="post" action="<?= url('admin/cancellations/' . $r['id'] . '/approve') ?>"><?= csrf_field() ?><button class="btn btn-success btn-sm" type="submit">Onayla</button></form>
                        <form method="post" action="<?= url('admin/cancellations/' . $r['id'] . '/deny') ?>"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Reddet</button></form>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
