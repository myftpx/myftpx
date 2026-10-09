<div class="container" style="padding-top:40px">
    <h1 class="mb-3">Blog</h1>
    <div style="display:grid;grid-template-columns:1fr 260px;gap:24px" class="blog-layout">
        <div>
            <?php if (empty($posts)): ?><div class="card"><div class="card-body"><p class="text-muted">Henüz yazı yok.</p></div></div><?php endif; ?>
            <?php foreach ($posts as $p): ?>
                <div class="announcement">
                    <div class="date"><?= e(date('d.m.Y', strtotime($p['published_at']))) ?> · <?= e($p['cat'] ?: 'Genel') ?></div>
                    <h3 class="mb-1"><a href="<?= url('blog/' . $p['slug']) ?>"><?= e($p['title']) ?></a></h3>
                    <?php if ($p['excerpt']): ?><p class="text-muted"><?= e($p['excerpt']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div>
            <div class="card">
                <div class="card-header"><h3>Kategoriler</h3></div>
                <div class="card-body">
                    <a href="<?= url('blog') ?>" class="d-block mb-1">Tümü</a>
                    <?php foreach ($categories as $c): ?>
                        <a href="<?= url('blog?category=' . $c['slug']) ?>" class="d-block mb-1"><?= e($c['name']) ?> (<?= (int)$c['cnt'] ?>)</a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>.d-block{display:block}@media(max-width:800px){.blog-layout{grid-template-columns:1fr}}</style>
