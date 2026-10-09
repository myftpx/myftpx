<div class="page-head">
    <h1><?= e($domain['domain']) ?></h1>
    <a class="btn btn-outline" href="<?= url('client/domains') ?>">Geri</a>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Alan Adı Bilgileri</h3><?= status_badge($domain['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Kayıt Firması</span><span class="v"><?= e($domain['registrar'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Bitiş Tarihi</span><span class="v"><?= e($domain['expiry_date'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Kayıt Süresi</span><span class="v"><?= (int)$domain['registration_period'] ?> yıl</span></div>
            <div class="info-row"><span class="k">Name Server</span><span class="v"><?= e(implode(', ', $domain['nameservers'])) ?></span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>DNS Yönetimi</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/domains/' . $domain['id'] . '/dns') ?>">
            <?= csrf_field() ?>
            <div class="table-wrap"><table class="table">
                <tr><th>Tip</th><th>Ad</th><th>Değer</th><th>TTL</th></tr>
                <?php $records = $domain['dns'] ?: [['type' => 'A', 'name' => '@', 'value' => '', 'ttl' => 3600]]; ?>
                <?php foreach ($records as $i => $r): ?>
                    <tr>
                        <td>
                            <select class="form-control" name="type[]">
                                <?php foreach (['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV'] as $t): ?>
                                    <option <?= ($r['type'] ?? 'A') === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input class="form-control" name="name[]" value="<?= e($r['name'] ?? '') ?>"></td>
                        <td><input class="form-control" name="value[]" value="<?= e($r['value'] ?? '') ?>"></td>
                        <td><input class="form-control" name="ttl[]" value="<?= e($r['ttl'] ?? 3600) ?>" style="width:90px"></td>
                    </tr>
                <?php endforeach; ?>
            </table></div>
            <div class="mt-2"><button class="btn btn-primary" type="submit">DNS Kayıtlarını Kaydet</button></div>
        </form>
    </div>
</div>
