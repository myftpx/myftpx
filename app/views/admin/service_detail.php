<div class="page-head">
    <h1><?= e($service['domain'] ?: $service['pname']) ?></h1>
    <a class="btn btn-outline" href="<?= url('admin/services') ?>">Geri</a>
</div>
<div class="card mb-3">
    <div class="card-header"><h3>Hizmet Bilgileri</h3><?= status_badge($service['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Müşteri</span><span class="v"><?= e($service['first_name'] . ' ' . $service['last_name']) ?></span></div>
            <div class="info-row"><span class="k">Ürün</span><span class="v"><?= e($service['pname']) ?></span></div>
            <div class="info-row"><span class="k">Modül</span><span class="v"><?= e($service['module']) ?></span></div>
            <div class="info-row"><span class="k">Tutar</span><span class="v"><?= money($service['amount']) ?> / <?= e($service['billing_cycle']) ?></span></div>
        </div>
    </div>
</div>
<div class="card" style="max-width:720px">
    <div class="card-header"><h3>Yönetim</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/services/' . $service['id']) ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Durum</label>
                    <select class="form-control" name="status">
                        <?php foreach (['active' => 'Aktif', 'pending' => 'Beklemede', 'suspended' => 'Askıda', 'terminated' => 'Sonlandırıldı'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= $service['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Alan Adı</label><input class="form-control" name="domain" value="<?= e($service['domain']) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Kullanıcı Adı</label><input class="form-control" name="username" value="<?= e($service['username']) ?>"></div>
                <div class="form-group"><label>Şifre</label><input class="form-control" name="password" placeholder="Değiştirmek için doldurun"></div>
            </div>
            <div class="form-group"><label>Son Ödeme Tarihi</label><input class="form-control" type="date" name="next_due_date" value="<?= e($service['next_due_date']) ?>"></div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
