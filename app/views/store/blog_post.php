<div class="container" style="padding-top:40px;max-width:860px">
    <div class="text-muted small mb-1"><a href="<?= url('blog') ?>">Blog</a> › <?= e($post['cat'] ?: 'Genel') ?></div>
    <h1 class="mb-2"><?= e($post['title']) ?></h1>
    <div class="text-muted small mb-3"><?= e(date('d.m.Y', strtotime($post['published_at']))) ?> · <?= (int)$post['views'] ?> görüntülenme</div>
    <?php if ($post['image']): ?><img src="<?= e($post['image']) ?>" alt="<?= e($post['title']) ?>" style="width:100%;border-radius:12px;margin-bottom:16px"><?php endif; ?>
    <div class="prose"><?= nl2br(e($post['content'])) ?></div>
</div>
