<div class="page-head">
    <h1>API Erişimi</h1>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Yeni API Anahtarı</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/api/create') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Anahtar Adı</label>
                    <input class="form-control" name="name" placeholder="örn: Uygulamam" required>
                </div>
                <div class="form-group">
                    <label>IP İzin Listesi (virgülle ayırın, boş = tümü)</label>
                    <input class="form-control" name="allowed_ips" placeholder="1.2.3.4, 5.6.7.8">
                </div>
            </div>
            <div class="form-group">
                <label>İzinler</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px">
                    <?php
                    $perms = [
                        'account:read' => 'Hesap bilgilerini görüntüle',
                        'services:read' => 'Hizmetleri görüntüle',
                        'domains:read' => 'Alan adlarını görüntüle',
                        'domains:write' => 'Alan adlarını (DNS) yönet',
                        'invoices:read' => 'Faturaları görüntüle',
                        'invoices:pay' => 'Fatura öde',
                        'tickets:read' => 'Biletleri görüntüle',
                        'tickets:write' => 'Bilet oluştur / yanıtla',
                        'orders:write' => 'Sipariş oluştur',
                    ];
                    ?>
                    <?php foreach ($perms as $code => $label): ?>
                        <label class="form-check"><input type="checkbox" name="permissions[]" value="<?= e($code) ?>"> <?= e($label) ?></label>
                    <?php endforeach; ?>
                    <label class="form-check"><input type="checkbox" name="permissions[]" value="*"> <strong>Tümü (*)</strong></label>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Oluştur</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Anahtarlarım</h3></div>
    <div class="card-body">
        <?php if (empty($keys)): ?>
            <p class="text-muted">Henüz API anahtarınız yok.</p>
        <?php endif; ?>
        <?php foreach ($keys as $k): ?>
            <div class="card mb-2" style="border:1px solid var(--border)">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <strong><?= e($k['name']) ?></strong>
                            <?= $k['status'] === 'active' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge">Devre Dışı</span>' ?>
                        </div>
                        <div class="flex gap-1">
                            <form method="post" action="<?= url('client/api/' . $k['id'] . '/toggle') ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit"><?= $k['status'] === 'active' ? 'Devre Dışı Bırak' : 'Etkinleştir' ?></button></form>
                            <form method="post" action="<?= url('client/api/' . $k['id'] . '/delete') ?>" onsubmit="return confirm('Bu anahtarı silmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small">API Key</label>
                        <div class="flex gap-1 items-center">
                            <div class="key-box w-100" id="key-<?= (int)$k['id'] ?>"><?= e($k['api_key']) ?></div>
                            <button class="btn btn-outline btn-sm" type="button" data-copy="#key-<?= (int)$k['id'] ?>">Kopyala</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small">Auth Key (gizli)</label>
                        <div class="flex gap-1 items-center">
                            <div class="key-box w-100" id="auth-<?= (int)$k['id'] ?>"><?= e($k['auth_key']) ?></div>
                            <button class="btn btn-outline btn-sm" type="button" data-copy="#auth-<?= (int)$k['id'] ?>">Kopyala</button>
                        </div>
                    </div>
                    <div class="text-muted small">
                        İzinler: <?= e(implode(', ', json_decode($k['permissions'], true) ?: [])) ?>
                        <?php if ($k['allowed_ips']): ?> · IP: <?= e($k['allowed_ips']) ?><?php endif; ?>
                        <?php if ($k['last_used_at']): ?> · Son kullanım: <?= e(time_ago($k['last_used_at'])) ?><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h3>API Kullanımı</h3></div>
    <div class="card-body">
        <p class="text-muted mb-2">İsteklerinizde şu başlıkları kullanın:</p>
        <div class="key-box mb-2">X-Api-Key: rcvx_...</div>
        <div class="key-box mb-2">X-Auth-Key: (auth key)</div>
        <p class="text-muted small">Uç noktalar: <code class="mono">/api/v1/me</code>, <code class="mono">/api/v1/services</code>, <code class="mono">/api/v1/domains</code>, <code class="mono">/api/v1/invoices</code>, <code class="mono">/api/v1/tickets</code>, <code class="mono">/api/v1/balance</code>, <code class="mono">/api/v1/orders</code></p>
    </div>
</div>
