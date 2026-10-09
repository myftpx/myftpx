<h3 class="mb-2">Şifremi Unuttum</h3>
<p class="text-muted mb-3">E-posta adresinizi girin, size şifre sıfırlama bağlantısı gönderelim.</p>
<form method="post" action="<?= url('forgot-password') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>E-posta</label>
        <input class="form-control" type="email" name="email" required autofocus>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Sıfırlama Bağlantısı Gönder</button>
</form>
<div class="divider"></div>
<p class="text-center text-muted small"><a href="<?= url('login') ?>">Giriş sayfasına dön</a></p>
