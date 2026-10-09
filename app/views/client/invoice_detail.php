<div class="page-head">
    <h1>Fatura <?= e($invoice['invoice_number']) ?></h1>
    <a class="btn btn-outline" href="<?= url('client/invoices') ?>">Geri</a>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Fatura Detayı</h3><?= status_badge($invoice['status']) ?></div>
    <div class="card-body">
        <div class="info-list">
            <div class="info-row"><span class="k">Fatura No</span><span class="v"><?= e($invoice['invoice_number']) ?></span></div>
            <div class="info-row"><span class="k">Oluşturma</span><span class="v"><?= e(date('d.m.Y H:i', strtotime($invoice['created_at']))) ?></span></div>
            <div class="info-row"><span class="k">Son Ödeme</span><span class="v"><?= e($invoice['due_date'] ?: '—') ?></span></div>
            <div class="info-row"><span class="k">Ödeme Tarihi</span><span class="v"><?= e($invoice['paid_at'] ? date('d.m.Y H:i', strtotime($invoice['paid_at'])) : '—') ?></span></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Kalemler</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Açıklama</th><th class="text-right">Tutar</th></tr>
        <?php foreach ($items as $it): ?>
            <tr><td><?= e($it['description']) ?></td><td class="text-right"><?= money($it['amount']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td class="text-right"><strong>Ara Toplam</strong></td><td class="text-right"><?= money($invoice['amount']) ?></td></tr>
        <tr><td class="text-right">KDV (%<?= e(setting('tax_rate', '0')) ?>)</td><td class="text-right"><?= money($invoice['tax']) ?></td></tr>
        <tr><td class="text-right"><strong>Toplam</strong></td><td class="text-right"><strong><?= money($invoice['total']) ?></strong></td></tr>
    </table></div>
</div>

<?php if ($invoice['status'] === 'unpaid'): ?>
<?php
$cardGateways = array_filter($gateways, fn($g) => in_array($g['code'], ['paytr', 'iyzico'], true));
$hasBankTransfer = (bool)array_filter($gateways, fn($g) => $g['code'] === 'bank_transfer');
?>
<div class="card mb-3">
    <div class="card-header"><h3>Ödeme</h3></div>
    <div class="card-body">
        <div class="alert alert-info">Toplam ödenecek tutar: <strong><?= money($invoice['total']) ?></strong></div>

        <!-- Balance -->
        <div class="card mb-3" style="border:1px solid var(--border)">
            <div class="card-body">
                <h4 class="mb-2">Bakiye ile Öde</h4>
                <p class="text-muted mb-2">Mevcut bakiyeniz: <strong><?= money(auth()->user()['balance']) ?></strong></p>
                <form method="post" action="<?= url('client/invoices/' . $invoice['id'] . '/pay') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="gateway" value="balance">
                    <button class="btn btn-success" type="submit" <?= (float)auth()->user()['balance'] < (float)$invoice['total'] ? 'disabled' : '' ?>>Bakiyeden Öde</button>
                </form>
            </div>
        </div>

        <!-- Card payment -->
        <?php foreach ($cardGateways as $cg): ?>
        <div class="card mb-3" style="border:1px solid var(--border)">
            <div class="card-body">
                <h4 class="mb-2"><?= e($cg['name']) ?> ile Kartla Öde</h4>
                <form method="post" action="<?= url('client/invoices/' . $invoice['id'] . '/pay') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="gateway" value="<?= e($cg['code']) ?>">

                    <?php $gatewayCards = array_values(array_filter($cards, fn($c) => $c['gateway'] === $cg['code'])); ?>
                    <?php if ($gatewayCards): ?>
                        <div class="form-group">
                            <label>Kayıtlı Kart Seç</label>
                            <?php foreach ($gatewayCards as $gc): ?>
                                <label class="form-check mb-1">
                                    <input type="radio" name="saved_card_id" value="<?= (int)$gc['id'] ?>" <?= $gc['is_default'] ? 'checked' : '' ?>>
                                    <strong><?= e($gc['brand']) ?> •••• <?= e($gc['last4']) ?></strong> (<?= e($gc['expiry_month']) ?>/<?= e($gc['expiry_year']) ?>)<?= $gc['is_default'] ? ' <span class="badge badge-primary">Varsayılan</span>' : '' ?>
                                </label>
                            <?php endforeach; ?>
                            <button class="btn btn-primary mt-2" type="submit">Seçili Kartla Öde (<?= money($invoice['total']) ?>)</button>
                        </div>
                    <?php endif; ?>

                    <div class="divider"></div>
                    <p class="text-muted small mb-2"><?= $gatewayCards ? 'Veya yeni kartla öde:' : 'Kart bilgilerinizi girin:' ?></p>
                    <div class="form-row">
                        <div class="form-group"><label>Kart Üzerindeki İsim</label><input class="form-control" name="holder"></div>
                        <div class="form-group"><label>Kart Numarası</label><input class="form-control" name="number" inputmode="numeric"></div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group"><label>Ay</label><input class="form-control" name="expiry_month" maxlength="2" placeholder="MM"></div>
                        <div class="form-group"><label>Yıl</label><input class="form-control" name="expiry_year" maxlength="2" placeholder="YY"></div>
                        <div class="form-group"><label>CVV</label><input class="form-control" name="cvv" maxlength="4"></div>
                    </div>
                    <label class="form-check mb-2"><input type="checkbox" name="save_card" value="1"> Bu kartı ilerideki ödemeler için kaydet</label>
                    <button class="btn btn-primary" type="submit">Yeni Kartla Öde</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Bank transfer -->
        <?php if ($hasBankTransfer): ?>
        <div class="card" style="border:1px solid var(--border)">
            <div class="card-body">
                <h4 class="mb-2">Havale / EFT</h4>
                <?php if ($bankAccounts): ?>
                    <p class="text-muted mb-2">Aşağıdaki hesaplardan birine ödeme yapın, ardından "Ödeme Yaptım" butonuna tıklayın.</p>
                    <?php foreach ($bankAccounts as $ba): ?>
                        <div class="key-box mb-2">
                            <strong><?= e($ba['bank_name']) ?></strong> · <?= e($ba['account_holder']) ?><br>
                            IBAN: <span class="mono"><?= e($ba['iban']) ?></span>
                            <?= $ba['account_no'] ? ' · Hesap No: ' . e($ba['account_no']) : '' ?>
                            <?= $ba['branch_code'] ? ' · Şube: ' . e($ba['branch_code']) : '' ?>
                        </div>
                    <?php endforeach; ?>
                    <form method="post" action="<?= url('client/invoices/' . $invoice['id'] . '/bank-transfer') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label>Referans / Açıklama</label>
                            <input class="form-control" name="reference" placeholder="örn: Ad Soyad - Fatura No">
                        </div>
                        <button class="btn btn-primary" type="submit">Ödeme Yaptım</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">Henüz banka hesabı tanımlanmamış. Lütfen destek ile iletişime geçin.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
