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
<?php elseif (empty($ticket['rating'])): ?>
<div class="card">
    <div class="card-header"><h3>Bilet Deneyiminizi Değerlendirin</h3></div>
    <div class="card-body">
        <form method="post" action="<?= url('client/tickets/' . $ticket['id'] . '/rate') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Puanınız (1-5)</label>
                <select class="form-control" name="rating" required>
                    <option value="5">★★★★★ — Mükemmel</option>
                    <option value="4">★★★★ — İyi</option>
                    <option value="3">★★★ — Orta</option>
                    <option value="2">★★ — Kötü</option>
                    <option value="1">★ — Çok Kötü</option>
                </select>
            </div>
            <div class="form-group">
                <label>Yorumunuz (opsiyonel)</label>
                <textarea class="form-control" name="rating_comment"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Değerlendir</button>
        </form>
    </div>
</div>
<?php elseif ($ticket['rating']): ?>
<div class="card">
    <div class="card-body">
        <div class="rating-stars"><?= str_repeat('★', (int)$ticket['rating']) ?><?= str_repeat('☆', 5 - (int)$ticket['rating']) ?></div>
        <?php if ($ticket['rating_comment']): ?><p class="text-muted mt-1"><?= e($ticket['rating_comment']) ?></p><?php endif; ?>
    </div>
</div>
<?php endif; ?>
