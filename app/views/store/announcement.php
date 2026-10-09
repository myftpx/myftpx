<div class="container" style="padding-top:40px;max-width:860px">
    <div class="date text-muted"><?= e(date('d.m.Y', strtotime($announcement['published_at']))) ?></div>
    <h1 class="mb-3"><?= e($announcement['title']) ?></h1>
    <div class="prose"><?= nl2br(e($announcement['body'])) ?></div>
    <div class="mt-3"><a class="btn btn-outline" href="<?= url('announcements') ?>">← Tüm Duyurular</a></div>
</div>
