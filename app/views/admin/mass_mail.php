<div class="page-head"><h1>Toplu E-posta</h1></div>
<div class="card" style="max-width:860px">
    <div class="card-body">
        <div class="alert alert-info"><?= (int)$clientCount ?> aktif müşteriye e-posta gönderilecek.</div>
        <form method="post" action="<?= url('admin/mass-mail/send') ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Konu</label><input class="form-control" name="subject" required></div>
            <div class="form-group"><label>İçerik</label><textarea class="form-control" name="body" rows="8" required></textarea>
            <div class="form-hint">Değişkenler: {first_name} {last_name} {email} {site_name}</div></div>
            <button class="btn btn-primary btn-lg" type="submit" onclick="return confirm('Tüm müşterilere gönderilecek. Emin misiniz?')">Gönder</button>
        </form>
    </div>
</div>
