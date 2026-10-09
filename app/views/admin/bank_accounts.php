<div class="page-head"><h1>Banka Hesapları</h1></div>

<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Yeni Banka Hesabı</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/bank-accounts/add') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Banka Adı</label><input class="form-control" name="bank_name" placeholder="örn: Ziraat Bankası" required></div>
                <div class="form-group"><label>Hesap Sahibi</label><input class="form-control" name="account_holder" required></div>
            </div>
            <div class="form-group"><label>IBAN</label><input class="form-control" name="iban" placeholder="TR00 0000 0000 0000 0000 0000 00" required></div>
            <div class="form-row">
                <div class="form-group"><label>Hesap No</label><input class="form-control" name="account_no"></div>
                <div class="form-group"><label>Şube Kodu</label><input class="form-control" name="branch_code"></div>
            </div>
            <button class="btn btn-primary" type="submit">Hesap Ekle</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Tanımlı Hesaplar</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Banka</th><th>Hesap Sahibi</th><th>IBAN</th><th>Hesap No</th><th>Şube</th><th>Durum</th><th></th></tr>
        <?php if (empty($accounts)): ?><tr><td colspan="7" class="empty">Henüz banka hesabı tanımlanmamış.</td></tr><?php endif; ?>
        <?php foreach ($accounts as $a): ?>
            <tr>
                <td><strong><?= e($a['bank_name']) ?></strong></td>
                <td><?= e($a['account_holder']) ?></td>
                <td class="mono"><?= e($a['iban']) ?></td>
                <td><?= e($a['account_no'] ?: '—') ?></td>
                <td><?= e($a['branch_code'] ?: '—') ?></td>
                <td><?= $a['is_active'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge">Pasif</span>' ?></td>
                <td class="flex gap-1">
                    <form method="post" action="<?= url('admin/bank-accounts/' . $a['id'] . '/toggle') ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit"><?= $a['is_active'] ? 'Pasifleştir' : 'Aktifleştir' ?></button></form>
                    <form method="post" action="<?= url('admin/bank-accounts/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Bu hesabı silmek istediğinize emin misiniz?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
