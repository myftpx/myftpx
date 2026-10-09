<div class="page-head">
    <h1>Kayıtlı Kartlarım</h1>
    <a class="btn btn-outline" href="<?= url('client/payments') ?>">Ödeme Geçmişim</a>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Kartlarım</h3></div>
    <div class="card-body">
        <?php if (empty($cards)): ?>
            <p class="text-muted">Henüz kayıtlı kartınız yok. Aşağıdan kart ekleyerek otomatik ödemeyi etkinleştirebilirsiniz.</p>
        <?php endif; ?>
        <div class="table-wrap"><table class="table">
            <tr><th>Kart</th><th>Kuruluş</th><th>Kart Sahibi</th><th>Son Kullanma</th><th>Varsayılan</th><th></th></tr>
            <?php foreach ($cards as $c): ?>
                <tr>
                    <td><strong><?= e($c['brand']) ?> •••• <?= e($c['last4']) ?></strong></td>
                    <td><?= e($c['gateway'] === 'paytr' ? 'PayTR' : 'iyzico') ?></td>
                    <td><?= e($c['holder_name']) ?></td>
                    <td><?= e($c['expiry_month']) ?>/<?= e($c['expiry_year']) ?></td>
                    <td><?= $c['is_default'] ? '<span class="badge badge-primary">Varsayılan</span>' : '' ?></td>
                    <td class="flex gap-1">
                        <?php if (!$c['is_default']): ?>
                            <form method="post" action="<?= url('client/cards/' . $c['id'] . '/default') ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Varsayılan Yap</button></form>
                        <?php endif; ?>
                        <form method="post" action="<?= url('client/cards/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Bu kartı silmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>

<?php if (!empty($gateways)): ?>
<div class="card">
    <div class="card-header"><h3>Yeni Kart Ekle</h3><span class="text-muted small">Kart bilgileriniz güvenli şekilde ödeme kuruluşunda saklanır (token).</span></div>
    <div class="card-body" style="max-width:620px">
        <form method="post" action="<?= url('client/cards/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Ödeme Kuruluşu</label>
                    <select class="form-control" name="gateway">
                        <?php foreach ($gateways as $g): ?><option value="<?= e($g['code']) ?>"><?= e($g['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Kart Üzerindeki İsim</label><input class="form-control" name="holder" required></div>
            </div>
            <div class="form-group"><label>Kart Numarası</label><input class="form-control" name="number" inputmode="numeric" placeholder="1234 5678 9012 3456" required></div>
            <div class="form-row-3">
                <div class="form-group"><label>Ay (MM)</label><input class="form-control" name="expiry_month" placeholder="12" maxlength="2" required></div>
                <div class="form-group"><label>Yıl (YY)</label><input class="form-control" name="expiry_year" placeholder="28" maxlength="2" required></div>
                <div class="form-group"><label>CVV</label><input class="form-control" name="cvv" placeholder="123" maxlength="4" required></div>
            </div>
            <label class="form-check mb-2"><input type="checkbox" name="set_default" value="1"> Varsayılan kart olarak ayarla</label>
            <button class="btn btn-primary" type="submit">Kartı Kaydet</button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="card"><div class="card-body"><div class="alert alert-info">Kart saklama özelliği şu anda devre dışı. Yöneticinin PayTR veya iyzico ödeme yöntemini etkinleştirmesi gerekiyor.</div></div></div>
<?php endif; ?>
