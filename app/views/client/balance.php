<div class="page-head"><h1>Bakiye</h1></div>

<div class="stats-grid">
    <div class="stat-card"><span class="ico">◉</span><div class="label">Mevcut Bakiye</div><div class="value"><?= money($user['balance']) ?></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h3>Bakiye Yükle</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/balance/add') ?>" class="flex gap-2 items-center" style="flex-wrap:wrap">
            <?= csrf_field() ?>
            <input class="form-control" type="number" name="amount" min="1" step="0.01" placeholder="Tutar" required style="max-width:180px">
            <select class="form-control" name="gateway" style="max-width:220px">
                <?php foreach (db()->query('SELECT * FROM payment_gateways WHERE enabled=1 ORDER BY sort_order')->fetchAll() as $g): ?>
                    <option value="<?= e($g['code']) ?>"><?= e($g['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Yükle</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Bakiye Hareketleri</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Tarih</th><th>Açıklama</th><th>Yöntem</th><th>Durum</th><th class="text-right">Tutar</th></tr>
        <?php if (empty($transactions)): ?><tr><td colspan="5" class="empty">Hareket yok.</td></tr><?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td class="text-muted"><?= e(date('d.m.Y H:i', strtotime($t['created_at']))) ?></td>
                <td><?= e($t['transaction_id']) ?></td>
                <td><?= e($t['gateway']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td class="text-right"><strong><?= money($t['amount']) ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
