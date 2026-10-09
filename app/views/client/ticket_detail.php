<div class="page-head">
    <div>
        <h1>#<?= e($ticket['ticket_number']) ?> — <?= e($ticket['subject']) ?></h1>
        <p class="text-muted"><?= e($ticket['department']) ?> · <?= status_badge($ticket['status']) ?></p>
    </div>
    <a class="btn btn-outline" href="<?= url('client/tickets') ?>">Geri</a>
</div>

<div class="thread mb-3">
    <?php foreach ($replies as $r): ?>
        <div class="thread-msg <?= $r['is_admin'] ? 'staff' : 'client' ?>">
            <div class="meta">
                <span class="who"><?= $r['is_admin'] ? 'Destek Ekibi' : e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></span>
                <span><?= e(date('d.m.Y H:i', strtotime($r['created_at']))) ?></span>
            </div>
            <div class="body"><?= nl2br(e($r['message'])) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($ticket['status'] !== 'closed'): ?>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('client/tickets/' . $ticket['id'] . '/reply') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Yanıtınız</label>
                <textarea class="form-control" name="message" required></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Yanıtla</button>
        </form>
    </div>
</div>
<?php endif; ?>
