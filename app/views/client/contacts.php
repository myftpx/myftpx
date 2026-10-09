<div class="page-head"><h1>Alt Hesaplar / Kişiler</h1></div>
<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Alt Hesap</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/contacts/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Ad</label><input class="form-control" name="first_name" required></div>
                <div class="form-group"><label>Soyad</label><input class="form-control" name="last_name" required></div>
            </div>
            <div class="form-group"><label>E-posta</label><input class="form-control" type="email" name="email" required></div>
            <div class="form-group"><label>Şifre</label><input class="form-control" type="password" name="password" minlength="6" required></div>
            <div class="form-group"><label>İzinler</label>
                <?php foreach (['Hizmetleri yönet', 'Faturaları görüntüle', 'Bilet aç', 'Alan adlarını yönet'] as $i => $p): ?>
                    <label class="form-check mb-1"><input type="checkbox" name="permissions[]" value="<?= $i + 1 ?>"> <?= $p ?></label>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary" type="submit">Oluştur</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Ad</th><th>E-posta</th><th>İzinler</th><th></th></tr>
        <?php if (empty($contacts)): ?><tr><td colspan="4" class="empty">Alt hesap yok.</td></tr><?php endif; ?>
        <?php foreach ($contacts as $c): ?>
            <tr><td><strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong></td><td><?= e($c['email']) ?></td><td class="text-muted small"><?= e(implode(', ', json_decode($c['permissions'], true) ?: [])) ?></td>
            <td><form method="post" action="<?= url('client/contacts/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
