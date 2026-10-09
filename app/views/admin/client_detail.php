<div class="page-head">
    <h1><?= e($client['first_name'] . ' ' . $client['last_name']) ?></h1>
    <div class="flex gap-2">
        <form method="post" action="<?= url('admin/clients/' . $client['id'] . '/login-as') ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Müşteri Olarak Giriş</button></form>
        <form method="post" action="<?= url('admin/clients/' . $client['id'] . '/delete') ?>" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Bilgiler</h3><?= status_badge($client['status']) ?></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/clients/' . $client['id']) ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Ad</label><input class="form-control" name="first_name" value="<?= e($client['first_name']) ?>"></div>
                <div class="form-group"><label>Soyad</label><input class="form-control" name="last_name" value="<?= e($client['last_name']) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>E-posta</label><input class="form-control" name="email" value="<?= e($client['email']) ?>"></div>
                <div class="form-group"><label>Telefon</label><input class="form-control" name="phone" value="<?= e($client['phone']) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Şirket</label><input class="form-control" name="company" value="<?= e($client['company']) ?>"></div>
                <div class="form-group"><label>Durum</label>
                    <select class="form-control" name="status">
                        <?php foreach (['active' => 'Aktif', 'suspended' => 'Askıda', 'inactive' => 'Pasif'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= $client['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Bakiye Yönetimi</h3><span class="badge badge-primary"><?= money($client['balance']) ?></span></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/clients/' . $client['id'] . '/balance') ?>" class="flex gap-2 items-center" style="flex-wrap:wrap">
            <?= csrf_field() ?>
            <input class="form-control" type="number" name="amount" step="0.01" placeholder="Tutar" style="max-width:160px" required>
            <select class="form-control" name="op" style="max-width:150px">
                <option value="add">Ekle</option>
                <option value="subtract">Çıkar</option>
                <option value="set">Ata</option>
            </select>
            <button class="btn btn-primary" type="submit">Uygula</button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Hizmetler</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Ürün</th><th>Alan Adı</th><th>Tutar</th><th>Durum</th></tr>
        <?php if (empty($services)): ?><tr><td colspan="4" class="empty">Hizmet yok</td></tr><?php endif; ?>
        <?php foreach ($services as $s): ?>
            <tr><td><?= e($s['pname']) ?></td><td><?= e($s['domain'] ?: '—') ?></td><td><?= money($s['amount']) ?></td><td><?= status_badge($s['status']) ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Alan Adları</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Alan Adı</th><th>Bitiş</th><th>Durum</th></tr>
        <?php if (empty($domains)): ?><tr><td colspan="3" class="empty">Alan adı yok</td></tr><?php endif; ?>
        <?php foreach ($domains as $d): ?>
            <tr><td><?= e($d['domain']) ?></td><td><?= e($d['expiry_date'] ?: '—') ?></td><td><?= status_badge($d['status']) ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Faturalar</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Fatura</th><th>Tutar</th><th>Durum</th></tr>
        <?php if (empty($invoices)): ?><tr><td colspan="3" class="empty">Fatura yok</td></tr><?php endif; ?>
        <?php foreach ($invoices as $i): ?>
            <tr><td><?= e($i['invoice_number']) ?></td><td><?= money($i['total']) ?></td><td><?= status_badge($i['status']) ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>

<div class="card">
    <div class="card-header"><h3>API Anahtarları</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Ad</th><th>Anahtar</th><th>Durum</th></tr>
        <?php if (empty($keys)): ?><tr><td colspan="3" class="empty">API anahtarı yok</td></tr><?php endif; ?>
        <?php foreach ($keys as $k): ?>
            <tr><td><?= e($k['name']) ?></td><td class="mono"><?= e($k['api_key']) ?></td><td><?= status_badge($k['status']) ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
