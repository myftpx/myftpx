<div class="page-head"><h1>Tekliflerim</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Teklif</th><th>Tutar</th><th>Durum</th><th>Geçerlilik</th><th></th></tr>
        <?php if (empty($quotes)): ?><tr><td colspan="5" class="empty">Teklif yok.</td></tr><?php endif; ?>
        <?php foreach ($quotes as $q): ?>
            <tr><td><strong><?= e($q['quote_number']) ?></strong></td><td><?= money($q['total']) ?></td><td><?= status_badge($q['status']) ?></td><td><?= e($q['valid_until'] ?: '—') ?></td>
            <td><a class="btn btn-outline btn-sm" href="<?= url('client/quotes/' . $q['id']) ?>">Görüntüle</a></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
