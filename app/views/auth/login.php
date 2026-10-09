<h3 class="mb-2">Hesabınıza giriş yapın</h3>
<p class="text-muted mb-3">Devam etmek için e-posta ve şifrenizle giriş yapın.</p>
<form method="post" action="<?= url('login') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
        <label>E-posta</label>
        <input class="form-control" type="email" name="email" required autofocus>
    </div>
    <div class="form-group">
        <label>Şifre</label>
        <input class="form-control" type="password" name="password" required>
    </div>
    <div class="flex justify-between items-center mb-3">
        <a class="small" href="<?= url('forgot-password') ?>">Şifremi unuttum</a>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Giriş Yap</button>
</form>
<div class="divider"></div>
<p class="text-center text-muted small">Hesabınız yok mu? <a href="<?= url('register') ?>">Kayıt olun</a></p>
