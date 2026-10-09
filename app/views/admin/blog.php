<div class="page-head"><h1>Blog Yönetimi</h1></div>
<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:20px" class="blog-admin">
    <div class="card mb-3">
        <div class="card-header"><h3>Kategoriler</h3></div>
        <div class="card-body">
            <form method="post" action="<?= url('admin/blog/category/add') ?>" class="mb-3">
                <?= csrf_field() ?>
                <div class="form-group"><label>Kategori Adı</label><input class="form-control" name="name" required></div>
                <button class="btn btn-primary btn-sm" type="submit">Ekle</button>
            </form>
            <div class="table-wrap"><table class="table">
                <tr><th>Kategori</th><th></th></tr>
                <?php foreach ($categories as $c): ?>
                    <tr><td><?= e($c['name']) ?></td>
                    <td><form method="post" action="<?= url('admin/blog/category/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
                <?php endforeach; ?>
            </table></div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3>Yeni Yazı</h3></div>
        <div class="card-body">
            <form method="post" action="<?= url('admin/blog/post/add') ?>">
                <?= csrf_field() ?>
                <div class="form-group"><label>Başlık</label><input class="form-control" name="title" required></div>
                <div class="form-group"><label>Kategori</label><select class="form-control" name="category_id">
                    <option value="0">—</option>
                    <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Özet</label><input class="form-control" name="excerpt"></div>
                <div class="form-group"><label>İçerik</label><textarea class="form-control" name="content" rows="6"></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label>Görsel URL</label><input class="form-control" name="image"></div>
                    <div class="form-group"><label>SEO Başlık</label><input class="form-control" name="seo_title"></div>
                </div>
                <div class="form-group"><label>SEO Açıklama</label><input class="form-control" name="seo_description"></div>
                <button class="btn btn-primary" type="submit">Yayınla</button>
            </form>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3>Yazılar</h3></div>
    <div class="table-wrap"><table class="table">
        <tr><th>Başlık</th><th>Kategori</th><th>Görüntülenme</th><th></th></tr>
        <?php foreach ($posts as $p): ?>
            <tr><td><strong><?= e($p['title']) ?></strong></td><td><?= e($p['cat'] ?: '—') ?></td><td><?= (int)$p['views'] ?></td>
            <td><form method="post" action="<?= url('admin/blog/post/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
        <?php endforeach; ?>
    </table></div>
</div>
<style>@media(max-width:800px){.blog-admin{grid-template-columns:1fr}}</style>
