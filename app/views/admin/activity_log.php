<div class="page-head"><h1>Kullanıcı Hareket Logu</h1></div>
<div class="card">
    <div class="card-body"><p class="text-muted">Kullanıcı ve yönetici hareketlerinin tam kaydı (giriş, kayıt, çıkış, ödeme, işlemler).</p></div>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>Tarih</th><th>Kullanıcı</th><th>Yönetici</th><th>İşlem</th><th>Açıklama</th><th>IP</th></tr>
        <?php if (empty($logs)): ?><tr><td colspan="6" class="empty">Kayıt yok.</td></tr><?php endif; ?>
        <?php foreach ($logs as $l): ?>
            <tr>
                <td class="text-muted nowrap"><?= e(date('d.m.Y H:i', strtotime($l['created_at']))) ?></td>
                <td><?= e(($l['first_name'] ?? '') . ' ' . ($l['last_name'] ?? '')) ?: '—' ?></td>
                <td><?= e($l['admin_name'] ?? '') ?: '—' ?></td>
                <td><span class="badge badge-info"><?= e($l['action']) ?></span></td>
                <td class="text-muted"><?= e($l['description']) ?></td>
                <td class="mono small"><?= e($l['ip']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
