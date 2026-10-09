<div class="page-head"><h1>Netlen Bayilik (Domain)</h1></div>
<div class="card mb-3" style="max-width:860px">
    <div class="card-header"><h3>API Ayarları</h3><?= (int)setting('netlen_enabled') ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge">Pasif</span>' ?></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/netlen/save') ?>">
            <?= csrf_field() ?>
            <label class="form-check mb-3"><input type="checkbox" name="netlen_enabled" value="1" <?= setting('netlen_enabled') ? 'checked' : '' ?>> Modülü etkinleştir</label>
            <div class="form-group"><label>API Anahtarı</label><input class="form-control" type="password" name="netlen_api_key" value="<?= e(setting('netlen_api_key')) ?>"></div>
            <div class="form-group"><label>API URL</label><input class="form-control" name="netlen_api_url" value="<?= e(setting('netlen_api_url', 'https://api.netlen.com.tr/v2')) ?>"></div>
            <div class="form-row">
                <div class="form-group"><label>NS1</label><input class="form-control" name="netlen_ns1" value="<?= e(setting('netlen_ns1')) ?>"></div>
                <div class="form-group"><label>NS2</label><input class="form-control" name="netlen_ns2" value="<?= e(setting('netlen_ns2')) ?>"></div>
            </div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
        <div class="divider"></div>
        <form method="post" action="<?= url('admin/netlen/sync') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline" type="submit">⇄ Alan Adlarını Senkronize Et</button>
        </form>
        <?php if ($balance !== null): ?>
            <div class="mt-2"><span class="badge badge-primary">Bakiye: <?= e(json_encode($balance, JSON_UNESCAPED_UNICODE)) ?></span></div>
        <?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger mt-2"><?= e($error) ?></div><?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Netlen Alan Adları</h3><span class="text-muted small"><?= is_array($domains) ? count($domains) . ' adet' : '' ?></span></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Alan Adı</th><th>TLD</th><th>Durum</th><th>Bitiş</th></tr>
        <?php if (empty($domains)): ?><tr><td colspan="4" class="empty"><?= (int)setting('netlen_enabled') ? 'API'den alan adı alınamadı.' : 'Modül etkin değil. API anahtarını girin.' ?></td></tr><?php endif; ?>
        <?php foreach ((array)$domains as $d): ?>
            <tr><td><strong><?= e($d['domain'] ?? ($d['name'] ?? '')) ?></strong></td><td><?= e($d['tld'] ?? '') ?></td><td><?= e($d['status'] ?? '') ?></td><td><?= e($d['expires_at'] ?? ($d['expiry_date'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>

<div class="card">
    <div class="card-header"><h3>Manuel Kayıt</h3></div>
    <div class="card-body" style="max-width:720px">
        <form method="post" action="<?= url('admin/netlen/register') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Alan Adı</label><input class="form-control" name="domain" placeholder="example.com" required></div>
                <div class="form-group"><label>Yıl</label><input class="form-control" type="number" name="years" value="1"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Kayıt Sahibi</label><input class="form-control" name="contact_name" required></div>
                <div class="form-group"><label>E-posta</label><input class="form-control" type="email" name="contact_email" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Telefon</label><input class="form-control" name="contact_phone" placeholder="+905321234567"></div>
                <div class="form-group"><label>Şehir</label><input class="form-control" name="contact_city"></div>
            </div>
            <div class="form-group"><label>Adres</label><input class="form-control" name="contact_address"></div>
            <div class="form-row">
                <div class="form-group"><label>Posta Kodu</label><input class="form-control" name="contact_postal"></div>
                <div class="form-group"><label>Ülke</label><input class="form-control" value="TR" disabled></div>
            </div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
