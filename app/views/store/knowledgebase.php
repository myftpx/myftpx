<div class="container" style="padding-top:40px">
    <div class="text-center mb-4">
        <h1>Bilgi Bankası</h1>
        <p class="text-muted">Aradığınız cevabı burada bulun.</p>
        <form method="get" action="<?= url('knowledgebase') ?>" class="hero-search" style="margin-top:18px">
            <input name="q" placeholder="Soru veya anahtar kelime ara…" value="">
            <button class="btn btn-primary btn-lg" type="submit">Ara</button>
        </form>
    </div>
    <h3 class="mb-2">Kategoriler</h3>
    <?php if (empty($categories)): ?><div class="card"><div class="card-body"><p class="text-muted">Henüz kategori yok.</p></div></div><?php endif; ?>
    <?php foreach ($categories as $c): ?>
        <a class="kb-cat" href="<?= url('knowledgebase/category/' . $c['id']) ?>">
            <div class="name"><?= e($c['name']) ?></div>
            <div class="desc"><?= e($c['description'] ?: ($c['cnt'] . ' makale')) ?></div>
        </a>
    <?php endforeach; ?>
    <?php if ($popular): ?>
    <h3 class="mt-4 mb-2">Popüler Makaleler</h3>
    <div class="article-list">
        <?php foreach ($popular as $p): ?>
            <a href="<?= url('knowledgebase/' . $p['id']) ?>"><span><?= e($p['title']) ?></span><span class="text-muted small"><?= (int)$p['views'] ?> görüntüleme</span></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
