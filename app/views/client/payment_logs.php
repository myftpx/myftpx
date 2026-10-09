<div class="page-head">
    <h1>Ödeme Geçmişim</h1>
    <a class="btn btn-outline" href="<?= url('client/cards') ?>">Kayıtlı Kartlarım</a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-3">Ödemelerinizle ilgili tüm işlemler şeffaf şekilde burada kayıt altına alınır.</p>
        <div class="table-wrap"><table class="table">
            <tr><th>Tarih</th><th>İşlem</th><th>Kuruluş</th><th>Tutar</th><th>Durum</th><th>Açıklama</th></tr>
            <?php if (empty($logs)): ?><tr><td colspan="6" class="empty">Henüz ödeme kaydı yok.</td></tr><?php endif; ?>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="text-muted nowrap"><?= e(date('d.m.Y H:i', strtotime($l['created_at']))) ?></td>
                    <td><span class="badge badge-info"><?= e($l['action']) ?></span></td>
                    <td><?= e($l['gateway'] ?: '—') ?></td>
                    <td><?= (float)$l['amount'] ? money($l['amount']) : '—' ?></td>
                    <td><?= status_badge($l['status'] === 'info' ? 'info' : $l['status']) ?></td>
                    <td class="text-muted"><?= e($l['message']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>
