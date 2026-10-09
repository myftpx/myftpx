<div class="container" style="padding-top:40px">
    <h2 class="mb-3">Duyurular</h2>
    <?php if (empty($announcements)): ?><div class="card"><div class="card-body"><p class="text-muted">Henüz duyuru yok.</p></div></div><?php endif; ?>
    <?php foreach ($announcements as $a): ?>
        <div class="announcement">
            <div class="date"><?= e(date('d.m.Y', strtotime($a['published_at']))) ?></div>
            <h3 class="mb-1"><a href="<?= url('announcements/' . $a['id']) ?>"><?= e($a['title']) ?></a></h3>
            <p class="text-muted"><?= e(mb_substr(strip_tags($a['body']), 0, 180)) ?>…</p>
        </div>
    <?php endforeach; ?>
</div>
