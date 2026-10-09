<div class="page-head">
    <div>
        <h1>#<?= e($ticket['ticket_number']) ?> — <?= e($ticket['subject']) ?></h1>
        <p class="text-muted"><?= e($ticket['first_name'] . ' ' . $ticket['last_name'] . ' (' . $ticket['email'] . ')') ?> · <?= e($ticket['department']) ?> · <?= status_badge($ticket['status']) ?></p>
    </div>
    <a class="btn btn-outline" href="<?= url('admin/tickets') ?>">Geri</a>
</div>

<div class="thread mb-3">
    <?php foreach ($replies as $r): ?>
        <div class="thread-msg <?= $r['is_admin'] ? 'staff' : 'client' ?>">
            <div class="meta">
                <span class="who"><?= $r['is_admin'] ? 'Destek (' . e($r['admin_name'] ?? '') . ')' : e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></span>
                <span><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></span>
            </div>
            <div class="body"><?= nl2br(e($r['message'])) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mb-3" style="max-width:720px">
    <div class="card-header"><h3>Durum</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('admin/tickets/' . $ticket['id'] . '/status') ?>">
            <?= csrf_field() ?>
            <div class="flex gap-2">
                <select class="form-control" name="status">
                    <?php foreach (['open' => 'Açık', 'answered' => 'Yanıtlandı', 'closed' => 'Kapalı'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $ticket['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline" type="submit">Uygula</button>
            </div>
        </form>
    </div>
</div>

<?php if ($ticket['status'] !== 'closed'): ?>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('admin/tickets/' . $ticket['id'] . '/reply') ?>">
            <?= csrf_field() ?>
            <div class="form-group"><label>Yanıt</label><textarea class="form-control" name="message" required></textarea></div>
            <button class="btn btn-primary" type="submit">Yanıtla</button>
        </form>
    </div>
</div>
<?php endif; ?>
