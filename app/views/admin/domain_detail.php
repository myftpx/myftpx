<div class="page-head">
    <h1><?= e($domain['domain']) ?></h1>
    <div class="flex gap-2">
        <form method="post" action="<?= url('admin/domains/' . $domain['id'] . '/delete') ?>" onsubmit="return confirm('Bu alan adını silmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form>
    </div>
</div>
<div class="card" style="max-width:720px">
    <div class="card-header"><h3>Bilgiler</h3><?= status_badge($domain['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Müşteri</span><span class="v"><?= e($domain['first_name'] . ' ' . $domain['last_name']) ?></span></div>
            <div class="info-row"><span class="k">Kayıt Tarihi</span><span class="v"><?= e($domain['created_at']) ?></span></div>
        </div>
        <form method="post" action="<?= url('admin/domains/' . $domain['id']) ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Kayıt Firması</label><input class="form-control" name="registrar" value="<?= e($domain['registrar']) ?>"></div>
                <div class="form-group"><label>Durum</label>
                    <select class="form-control" name="status">
                        <option value="active" <?= $domain['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="expired" <?= $domain['status'] === 'expired' ? 'selected' : '' ?>>Süresi Doldu</option>
                        <option value="pending" <?= $domain['status'] === 'pending' ? 'selected' : '' ?>>Beklemede</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Bitiş Tarihi</label><input class="form-control" type="date" name="expiry_date" value="<?= e($domain['expiry_date']) ?>"></div>
                <div class="form-group"><label>Kayıt Süresi (yıl)</label><input class="form-control" type="number" name="registration_period" value="<?= (int)$domain['registration_period'] ?>"></div>
            </div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
