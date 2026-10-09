<h3 class="mb-2">Yönetici Girişi</h3>
<p class="text-muted mb-3">Yönetim paneline erişmek için giriş yapın.</p>
<form method="post" action="<?= url('admin/login') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>E-posta</label>
        <input class="form-control" type="email" name="email" required autofocus>
    </div>
    <div class="form-group">
        <label>Şifre</label>
        <input class="form-control" type="password" name="password" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Giriş Yap</button>
</form>
