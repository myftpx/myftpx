<div class="page-head"><h1>Alan Adlarım</h1></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Alan Adı</th><th>Kayıt Firması</th><th>Bitiş Tarihi</th><th>Durum</th><th></th></tr>
        <?php if (empty($domains)): ?><tr><td colspan="5" class="empty">Henüz alan adınız yok.</td></tr><?php endif; ?>
        <?php foreach ($domains as $d): ?>
            <tr>
                <td><strong><?= e($d['domain']) ?></strong></td>
                <td><?= e($d['registrar'] ?: '—') ?></td>
                <td><?= e($d['expiry_date'] ?: '—') ?></td>
                <td><?= status_badge($d['status']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('client/domains/' . $d['id']) ?>">Yönet</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
