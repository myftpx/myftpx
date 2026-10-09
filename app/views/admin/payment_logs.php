<div class="page-head"><h1>Ödeme Kayıtları (Loglar)</h1></div>

<div class="card mb-3">
    <div class="card-body">
        <p class="text-muted">Tüm ödeme işlemleri — kart kaydetme, ödeme denemeleri, başarılı/başarısız ödemeler, otomatik ödeme ve havale onayları — şeffaf şekilde burada loglanır.</p>
    </div>
</div>

<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Tarih</th><th>Müşteri</th><th>Kuruluş</th><th>İşlem</th><th>Tutar</th><th>Durum</th><th>Referans</th><th>Açıklama</th></tr>
        <?php if (empty($logs)): ?><tr><td colspan="8" class="empty">Henüz ödeme kaydı yok.</td></tr><?php endif; ?>
        <?php foreach ($logs as $l): ?>
            <tr>
                <td class="text-muted nowrap"><?= e(date('d.m.Y H:i', strtotime($l['created_at']))) ?></td>
                <td><?= e(($l['first_name'] ?? '') . ' ' . ($l['last_name'] ?? '')) ?><div class="small text-muted"><?= e($l['email'] ?? '') ?></div></td>
                <td><?= e($l['gateway'] ?: '—') ?></td>
                <td><span class="badge badge-info"><?= e($l['action']) ?></span></td>
                <td><?= (float)$l['amount'] ? money($l['amount']) : '—' ?></td>
                <td><?= status_badge($l['status'] === 'info' ? 'info' : $l['status']) ?></td>
                <td class="mono small"><?= e($l['reference'] ?: '—') ?></td>
                <td class="text-muted"><?= e($l['message']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
