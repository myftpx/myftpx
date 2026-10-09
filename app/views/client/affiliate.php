<div class="page-head"><h1>Ortaklık (Affiliate)</h1></div>
<div class="stats-grid">
    <div class="stat-card"><span class="ico">◉</span><div class="label">Kazanç Bakiyesi</div><div class="value"><?= money($user['affiliate_balance']) ?></div></div>
    <div class="stat-card"><span class="ico">👥</span><div class="label">Davet Edilen</div><div class="value"><?= (int)$referrals ?></div></div>
    <div class="stat-card"><span class="ico">%</span><div class="label">Komisyon Oranı</div><div class="value">%<?= e(setting('affiliate_rate', '10')) ?></div></div>
</div>
<div class="card mb-3">
    <div class="card-header"><h3>Davet Bağlantınız</h3></div>
    <div class="card-body">
        <p class="text-muted mb-2">Bu bağlantı ile gelen kullanıcıların ödemelerinden komisyon kazanırsınız.</p>
        <?php $refLink = url('register?ref=' . $user['referral_code']); ?>
        <div class="key-box mb-2" id="ref-link"><?= e($refLink) ?></div>
        <button class="btn btn-outline btn-sm" type="button" data-copy="#ref-link">Kopyala</button>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3>Komisyon Geçmişi</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Tarih</th><th>Tutar</th><th>Durum</th></tr>
        <?php if (empty($commissions)): ?><tr><td colspan="3" class="empty">Henüz komisyon yok.</td></tr><?php endif; ?>
        <?php foreach ($commissions as $c): ?>
            <tr><td class="text-muted"><?= e(date('d.m.Y', strtotime($c['created_at']))) ?></td><td><?= money($c['amount']) ?></td><td><?= status_badge($c['status']) ?></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
