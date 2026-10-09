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
<?php if (show_otp_on_screen()): ?>
    <?php
    $stmt = db()->prepare('SELECT code FROM otp_codes WHERE email = ? AND type = "admin" AND used = 0 ORDER BY id DESC LIMIT 1');
    $stmt->execute([$email]);
    $devCode = $stmt->fetchColumn();
    ?>
    <?php if ($devCode): ?>
        <div class="alert alert-warning mt-2">Doğrulama kodunuz: <strong><?= e($devCode) ?></strong> <span class="small">(e-posta yapılandırılmadığı için kod burada)</span></div>
    <?php endif; ?>
<?php endif; ?>
<div class="divider"></div>
<p class="text-center text-muted small"><a href="<?= url('admin/login') ?>">Giriş sayfasına dön</a></p>
