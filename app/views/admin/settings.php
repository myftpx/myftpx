<div class="page-head"><h1>Genel Ayarlar</h1></div>

<form method="post" action="<?= url('admin/settings') ?>">
    <?= csrf_field() ?>
    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Genel</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group"><label>Site Adı</label><input class="form-control" name="site_name" value="<?= e(setting('site_name')) ?>"></div>
                <div class="form-group"><label>Yönetici E-posta</label><input class="form-control" name="admin_email" value="<?= e(setting('admin_email')) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Destek E-posta</label><input class="form-control" name="support_email" value="<?= e(setting('support_email')) ?>"></div>
                <div class="form-group"><label>Varsayılan Dil</label>
                    <select class="form-control" name="default_language">
                        <option value="tr" <?= setting('default_language') === 'tr' ? 'selected' : '' ?>>Türkçe</option>
                        <option value="en" <?= setting('default_language') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Tema</h3></div>
        <div class="card-body">
            <p class="text-muted mb-3">Site genelinde kullanılacak kurumsal temayı seçin. Değişiklik anında tüm arayüzlere yansır.</p>
            <div class="form-row">
                <?php foreach ($themes as $code => $label): ?>
                    <label class="card" style="cursor:pointer;padding:16px;border:2px solid <?= setting('theme') === $code ? 'var(--primary)' : 'var(--border)' ?>">
                        <input type="radio" name="theme" value="<?= $code ?>" <?= setting('theme') === $code ? 'checked' : '' ?> style="margin-right:8px">
                        <strong><?= e($label) ?></strong>
                        <div class="small text-muted mt-1"><?= $code === 'rcvxtrwhite' ? 'Açık (beyaz) kurumsal tema' : 'Koyu (dark) kurumsal tema' ?></div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Otomatik Ödeme (Cron)</h3></div>
        <div class="card-body">
            <p class="text-muted mb-2">Otomatik ödemelerin çalışması için bu adresi cron/cronjob olarak zamanlayın (örn. her saat):</p>
            <?php $cronSecret = setting('cron_secret', ''); ?>
            <div class="key-box mb-2" id="cron-url"><?= e(url('cron/billing?key=' . $cronSecret)) ?></div>
            <button class="btn btn-outline btn-sm" type="button" data-copy="#cron-url">Kopyala</button>
            <div class="form-hint mt-2">Crontab örneği: <code class="mono">0 * * * * curl -s "<?= e(url('cron/billing?key=' . $cronSecret)) ?>" > /dev/null</code></div>
        </div>
    </div>

    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Faturalama</h3></div>
        <div class="card-body">
            <div class="form-row-3">
                <div class="form-group"><label>Para Birimi</label>
                    <select class="form-control" name="currency">
                        <?php foreach (['TRY', 'USD', 'EUR', 'GBP'] as $c): ?>
                            <option value="<?= $c ?>" <?= setting('currency') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>KDV Oranı (%)</label><input class="form-control" type="number" step="0.01" name="tax_rate" value="<?= e(setting('tax_rate', '20')) ?>"></div>
                <div class="form-group"><label>Fatura Öneki</label><input class="form-control" name="invoice_prefix" value="<?= e(setting('invoice_prefix')) ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>E-posta (SMTP) & SMS/WhatsApp</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group"><label>Gönderim Yöntemi</label>
                    <select class="form-control" name="mail_method">
                        <option value="php" <?= setting('mail_method') === 'php' ? 'selected' : '' ?>>PHP mail()</option>
                        <option value="smtp" <?= setting('mail_method') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                        <option value="log" <?= setting('mail_method') === 'log' ? 'selected' : '' ?>>Log (test/yerel — e-posta gönderilmez)</option>
                    </select>
                    <div class="form-hint">E-posta almıyorsanız "Log" seçin; kodlar <code class="mono">storage/logs/mail.log</code> dosyasına yazılır.</div>
                </div>
                <div class="form-group"><label>Test E-postası Gönder</label>
                    <div class="flex gap-2 items-center" style="margin-top:4px">
                        <input class="form-control" id="test-email" placeholder="ornek@mail.com">
                        <button class="btn btn-outline" type="button" onclick="sendTestMail()">Test</button>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>SMTP Host</label><input class="form-control" name="smtp_host" value="<?= e(setting('smtp_host')) ?>"></div>
                <div class="form-group"><label>SMTP Port</label><input class="form-control" name="smtp_port" value="<?= e(setting('smtp_port', '587')) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>SMTP Kullanıcı</label><input class="form-control" name="smtp_user" value="<?= e(setting('smtp_user')) ?>"></div>
                <div class="form-group"><label>SMTP Şifre</label><input class="form-control" type="password" name="smtp_pass" value="<?= e(setting('smtp_pass')) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Gönderen E-posta</label><input class="form-control" name="smtp_from_email" value="<?= e(setting('smtp_from_email')) ?>"></div>
                <div class="form-group"><label>Gönderen Adı</label><input class="form-control" name="smtp_from_name" value="<?= e(setting('smtp_from_name')) ?>"></div>
            </div>
            <div class="divider"></div>
            <div class="form-row">
                <div class="form-group"><label>SMS/WhatsApp Gateway</label>
                    <select class="form-control" name="sms_gateway">
                        <?php foreach (['whatsapp' => 'WhatsApp (CallMeBot)', 'netgsm' => 'Netgsm', 'twilio' => 'Twilio'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= setting('sms_gateway') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>API Key</label><input class="form-control" name="sms_api_key" value="<?= e(setting('sms_api_key')) ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>API Secret</label><input class="form-control" type="password" name="sms_api_secret" value="<?= e(setting('sms_api_secret')) ?>"></div>
                <div class="form-group"><label>Gönderen (Sender)</label><input class="form-control" name="sms_sender" value="<?= e(setting('sms_sender')) ?>"></div>
            </div>
            <label class="form-check mb-2"><input type="checkbox" name="sms_enabled" value="1" <?= setting('sms_enabled') ? 'checked' : '' ?>> OTP SMS/WhatsApp gönderimini etkinleştir</label>
        </div>
    </div>

    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Sistem</h3></div>
        <div class="card-body">
            <label class="form-check mb-2"><input type="checkbox" name="api_enabled" value="1" <?= setting('api_enabled', '1') ? 'checked' : '' ?>> API erişimini etkinleştir</label>
            <label class="form-check mb-2"><input type="checkbox" name="allow_registration" value="1" <?= setting('allow_registration', '1') ? 'checked' : '' ?>> Yeni müşteri kayıtlarına izin ver</label>
            <label class="form-check mb-2"><input type="checkbox" name="maintenance_mode" value="1" <?= setting('maintenance_mode', '0') ? 'checked' : '' ?>> Bakım modu</label>
            <div class="form-row mt-2">
                <div class="form-group"><label>Kullanım Şartları URL</label><input class="form-control" name="terms_url" value="<?= e(setting('terms_url')) ?>"></div>
                <div class="form-group"><label>Gizlilik URL</label><input class="form-control" name="privacy_url" value="<?= e(setting('privacy_url')) ?>"></div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg" type="submit">Ayarları Kaydet</button>
</form>

<script>
function sendTestMail() {
    var email = document.getElementById('test-email').value;
    if (!email) { alert('E-posta girin.'); return; }
    var token = document.querySelector('input[name="_token"]').value;
    fetch('<?= url('admin/settings/test-mail') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
        body: '_token=' + encodeURIComponent(token) + '&email=' + encodeURIComponent(email)
    }).then(r => r.json()).then(d => { alert(d.message); }).catch(e => alert('Hata: ' + e));
}
</script>
