<div class="page-head"><h1>E-posta Şablonları</h1></div>
<p class="text-muted mb-3">Değişkenler: {first_name} {last_name} {site_name} {invoice_number} {total} {invoice_url} {ticket_number} {ticket_url} {login_url} {domain}</p>
<?php foreach ($templates as $t): ?>
<div class="card mb-3" style="max-width:860px">
    <div class="card-header"><h3 class="mono"><?= e($t['code']) ?></h3><?= $t['enabled'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge">Pasif</span>' ?></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/email-templates/' . $t['code']) ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Konu</label><input class="form-control" name="subject" value="<?= e($t['subject']) ?>"></div>
            <div class="form-group"><label>İçerik</label><textarea class="form-control" name="body" rows="6"><?= e($t['body']) ?></textarea></div>
            <label class="form-check mb-2"><input type="checkbox" name="enabled" value="1" <?= $t['enabled'] ? 'checked' : '' ?>> Etkin</label>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
