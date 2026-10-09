<div class="page-head"><h1>Ortaklık (Affiliate)</h1></div>
<div class="card mb-3">
    <div class="card-header"><h3>Ortaklar</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Ortak</th><th>E-posta</th><th>Ref Kodu</th><th>Kazanç</th><th>Davet</th><th></th></tr>
        <?php if (empty($affiliates)): ?><tr><td colspan="6" class="empty">Ortak yok.</td></tr><?php endif; ?>
        <?php foreach ($affiliates as $a): ?>
            <tr>
                <td><strong><?= e($a['first_name'] . ' ' . $a['last_name']) ?></strong></td>
                <td><?= e($a['email']) ?></td>
                <td class="mono"><?= e($a['referral_code']) ?></td>
                <td><?= money($a['affiliate_balance']) ?></td>
                <td><?= (int)db()->query('SELECT COUNT(*) FROM users WHERE referred_by = ' . (int)$a['id'])->fetchColumn() ?></td>
                <td><form method="post" action="<?= url('admin/affiliates/' . $a['id'] . '/payout') ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit">Ödeme Yap</button></form></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
<div class="card">
    <div class="card-header"><h3>Komisyonlar</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Ortak</th><th>Yönlendirilen</th><th>Tutar</th><th>Durum</th><th>Tarih</th></tr>
        <?php if (empty($commissions)): ?><tr><td colspan="5" class="empty">Komisyon yok.</td></tr><?php endif; ?>
        <?php foreach ($commissions as $c): ?>
            <tr>
                <td><?= e($c['afname'] . ' ' . $c['aflname']) ?></td>
                <td><?= e($c['rfname'] . ' ' . $c['rlname']) ?></td>
                <td><?= money($c['amount']) ?></td>
                <td><?= status_badge($c['status']) ?></td>
                <td class="text-muted"><?= e(date('d.m.Y', strtotime($c['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
