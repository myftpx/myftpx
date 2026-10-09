<div class="container" style="padding-top:40px;max-width:860px">
    <h1 class="mb-2"><?= e($category['name']) ?></h1>
    <p class="text-muted mb-3"><?= e($category['description']) ?></p>
    <div class="article-list">
        <?php if (empty($articles)): ?><p class="text-muted">Bu kategoride makale yok.</p><?php endif; ?>
        <?php foreach ($articles as $a): ?>
            <a href="<?= url('knowledgebase/' . $a['id']) ?>"><span><?= e($a['title']) ?></span><span class="text-muted small"><?= (int)$a['views'] ?> görüntüleme</span></a>
        <?php endforeach; ?>
    </div>
    <div class="mt-3"><a class="btn btn-outline" href="<?= url('knowledgebase') ?>">← Bilgi Bankası</a></div>
</div>
