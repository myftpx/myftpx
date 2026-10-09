<div class="page-head"><h1>Yöneticiler</h1></div>
<div class="card mb-3" style="max-width:640px">
    <div class="card-header"><h3>Yeni Yönetici Ekle</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/admins/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Ad Soyad</label><input class="form-control" name="name" required></div>
                <div class="form-group"><label>Rol</label><select class="form-control" name="role"><option value="admin">Admin</option><option value="staff">Staff</option></select></div>
            </div>
            <div class="form-group"><label>E-posta</label><input class="form-control" type="email" name="email" required></div>
            <div class="form-group"><label>Şifre</label><input class="form-control" type="password" name="password" minlength="6" required></div>
            <button class="btn btn-primary" type="submit">Ekle</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Ad</th><th>E-posta</th><th>Rol</th><th>Son Giriş</th><th></th></tr>
        <?php foreach ($admins as $a): ?>
            <tr><td><strong><?= e($a['name']) ?></strong></td><td><?= e($a['email']) ?></td><td><?= e($a['role']) ?></td><td class="text-muted"><?= e($a['last_login'] ?: '—') ?></td>
            <td><?php if ((int)$a['id'] !== (int)(auth()->admin()['id'] ?? 0)): ?><form method="post" action="<?= url('admin/admins/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form><?php endif; ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
