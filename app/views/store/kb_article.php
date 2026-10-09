<div class="container" style="padding-top:40px;max-width:860px">
    <div class="text-muted small mb-1"><a href="<?= url('knowledgebase') ?>">Bilgi Bankası</a> › <?= e($article['cat']) ?></div>
    <h1 class="mb-3"><?= e($article['title']) ?></h1>
    <div class="prose"><?= nl2br(e($article['body'])) ?></div>
    <div class="mt-3 text-muted small"><?= (int)$article['views'] ?> görüntüleme</div>
</div>
