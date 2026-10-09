<div class="container" style="padding-top:40px;max-width:860px">
    <h1 class="mb-3">Arama Sonuçları: "<?= e($q) ?>"</h1>
    <div class="article-list">
        <?php if (empty($articles)): ?><div class="card"><div class="card-body"><p class="text-muted">Sonuç bulunamadı.</p></div></div><?php endif; ?>
        <?php foreach ($articles as $a): ?>
            <a href="<?= url('knowledgebase/' . $a['id']) ?>"><span><?= e($a['title']) ?> <span class="text-muted small">(<?= e($a['cat']) ?>)</span></span></a>
        <?php endforeach; ?>
    </div>
</div>
