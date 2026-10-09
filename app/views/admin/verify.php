<h3 class="mb-2">Yönetici Doğrulama</h3>
<p class="text-muted mb-3"><strong><?= e($email) ?></strong> adresine gönderilen 6 haneli kodu girin.</p>
<form method="post" action="<?= url('admin/verify') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>Doğrulama Kodu</label>
        <input class="form-control" name="code" inputmode="numeric" maxlength="6" placeholder="123456" style="text-align:center;font-size:22px;letter-spacing:8px" required autofocus>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Doğrula</button>
</form>
<div class="divider"></div>
<p class="text-center text-muted small"><a href="<?= url('admin/login') ?>">Giriş sayfasına dön</a></p>
