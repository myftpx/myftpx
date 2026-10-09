<div class="container" style="padding-top:40px;max-width:900px">
    <h1 class="mb-3">Ağ Durumu</h1>
    <div class="card">
        <div class="card-header"><h3>Hizmet Durumu</h3><span class="badge badge-success"><span class="status-dot ok"></span>Tüm sistemler çalışıyor</span></div>
        <div class="table-wrap"><table class="table">
            <tr><th>Hizmet</th><th>Durum</th><th>Son Kontrol</th></tr>
            <?php if (empty($services)): ?><tr><td colspan="3" class="empty">İzlenen hizmet yok.</td></tr><?php endif; ?>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><?= e($s['pname']) ?> (<?= e($s['domain'] ?: '#'.$s['id']) ?>)</td>
                    <td><?= $s['status'] === 'active' ? '<span class="badge badge-success"><span class="status-dot ok"></span>Çalışıyor</span>' : '<span class="badge badge-warning"><span class="status-dot warn"></span>Askıda</span>' ?></td>
                    <td class="text-muted"><?= e(date('d.m.Y H:i', strtotime($s['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>
