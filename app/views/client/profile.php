<div class="page-head"><h1>Profilim</h1></div>

<div class="card mb-3" style="max-width:760px">
    <div class="card-header"><h3>Kişisel Bilgiler</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/profile') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Ad</label><input class="form-control" name="first_name" value="<?= e($user['first_name']) ?>" required></div>
                <div class="form-group"><label>Soyad</label><input class="form-control" name="last_name" value="<?= e($user['last_name']) ?>" required></div>
            </div>
            <div class="form-group"><label>Telefon</label><input class="form-control" name="phone" value="<?= e($user['phone']) ?>"></div>
            <div class="form-group"><label>Şirket</label><input class="form-control" name="company" value="<?= e($user['company']) ?>"></div>
            <div class="form-group"><label>Adres</label><input class="form-control" name="address" value="<?= e($user['address']) ?>"></div>
            <div class="form-row-3">
                <div class="form-group"><label>Şehir</label><input class="form-control" name="city" value="<?= e($user['city']) ?>"></div>
                <div class="form-group"><label>Ülke</label><input class="form-control" name="country" value="<?= e($user['country']) ?>"></div>
                <div class="form-group"><label>Posta Kodu</label><input class="form-control" name="postal_code" value="<?= e($user['postal_code']) ?>"></div>
            </div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>

<div class="card" style="max-width:760px">
    <div class="card-header"><h3>Şifre Değiştir</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/profile/password') ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Mevcut Şifre</label><input class="form-control" type="password" name="current_password" required></div>
            <div class="form-row">
                <div class="form-group"><label>Yeni Şifre</label><input class="form-control" type="password" name="new_password" required minlength="6"></div>
                <div class="form-group"><label>Yeni Şifre (Tekrar)</label><input class="form-control" type="password" name="confirm_password" required minlength="6"></div>
            </div>
            <button class="btn btn-primary" type="submit">Şifreyi Güncelle</button>
        </form>
    </div>
</div>
