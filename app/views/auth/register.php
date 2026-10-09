<h3 class="mb-2">Yeni hesap oluşturun</h3>
<p class="text-muted mb-3">Saniyeler içinde kayıt olun. E-posta doğrulaması gerekir.</p>
<form method="post" action="<?= url('register') ?>" id="registerForm">
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
    <div class="form-group">
        <label>Hesap Türü</label>
        <select class="form-control" name="account_type" id="account_type" onchange="toggleKyc()">
            <option value="individual">Bireysel</option>
            <option value="corporate">Kurumsal</option>
        </select>
    </div>
    <div id="individual-fields">
        <div class="form-group">
            <label>T.C. Kimlik No</label>
            <input class="form-control" name="tc_no" inputmode="numeric" maxlength="11" placeholder="11 haneli T.C. kimlik no">
        </div>
    </div>
    <div id="corporate-fields" style="display:none">
        <div class="form-group">
            <label>Şirket Adı</label>
            <input class="form-control" name="company">
        </div>
        <div class="form-group">
            <label>Vergi No</label>
            <input class="form-control" name="tax_no" inputmode="numeric" maxlength="10" placeholder="10 haneli vergi no">
        </div>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Kayıt Ol</button>
</form>
<div class="divider"></div>
<p class="text-center text-muted small">Zaten hesabınız var mı? <a href="<?= url('login') ?>">Giriş yapın</a></p>
<script>
function toggleKyc() {
    var t = document.getElementById('account_type').value;
    document.getElementById('individual-fields').style.display = t === 'individual' ? 'block' : 'none';
    document.getElementById('corporate-fields').style.display = t === 'corporate' ? 'block' : 'none';
}
</script>
