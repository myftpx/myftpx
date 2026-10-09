<h3 class="mb-2">Yeni hesap oluşturun</h3>
<p class="text-muted mb-3">Saniyeler içinde ücretsiz kayıt olun.</p>
<form method="post" action="<?= url('register') ?>">
    <?= csrf_field() ?>
    <div class="form-row">
        <div class="form-group"><label>Ad</label><input class="form-control" name="first_name" required></div>
        <div class="form-group"><label>Soyad</label><input class="form-control" name="last_name" required></div>
    </div>
    <div class="form-group">
        <label>E-posta</label>
        <input class="form-control" type="email" name="email" required>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Şifre</label><input class="form-control" type="password" name="password" required minlength="6"></div>
        <div class="form-group"><label>Şifre (Tekrar)</label><input class="form-control" type="password" name="password_confirm" required minlength="6"></div>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Kayıt Ol</button>
</form>
<div class="divider"></div>
<p class="text-center text-muted small">Zaten hesabınız var mı? <a href="<?= url('login') ?>">Giriş yapın</a></p>
