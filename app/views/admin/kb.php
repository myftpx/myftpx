<div class="page-head"><h1>Bilgi Bankası</h1></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="kb-admin">
    <div class="card mb-3">
        <div class="card-header"><h3>Kategoriler</h3></div>
        <div class="card-body">
            <form method="post" action="<?= url('admin/kb/category/add') ?>" class="mb-3">
                <?= csrf_field() ?>
                <div class="form-group"><label>Kategori Adı</label><input class="form-control" name="name" required></div>
                <div class="form-group"><label>Açıklama</label><input class="form-control" name="description"></div>
                <button class="btn btn-primary btn-sm" type="submit">Kategori Ekle</button>
            </form>
            <div class="table-wrap"><table class="table">
                <tr><th>Kategori</th><th>Makale</th><th></th></tr>
                <?php foreach ($categories as $c): ?>
                    <tr><td><?= e($c['name']) ?></td><td><?= (int)$c['cnt'] ?></td>
                    <td><form method="post" action="<?= url('admin/kb/category/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
                <?php endforeach; ?>
            </table></div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3>Makaleler</h3></div>
        <div class="card-body">
            <form method="post" action="<?= url('admin/kb/article/add') ?>" class="mb-3">
                <?= csrf_field() ?>
                <div class="form-group"><label>Kategori</label>
                    <select class="form-control" name="category_id">
                        <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Başlık</label><input class="form-control" name="title" required></div>
                <div class="form-group"><label>İçerik</label><textarea class="form-control" name="body" rows="4"></textarea></div>
                <button class="btn btn-primary btn-sm" type="submit">Makale Ekle</button>
            </form>
            <div class="table-wrap"><table class="table">
                <tr><th>Başlık</th><th>Kategori</th><th></th></tr>
                <?php foreach ($articles as $a): ?>
                    <tr><td><?= e($a['title']) ?></td><td><?= e($a['cat']) ?></td>
                    <td><form method="post" action="<?= url('admin/kb/article/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Silinsin mi?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Sil</button></form></td></tr>
                <?php endforeach; ?>
            </table></div>
        </div>
    </div>
</div>
<style>@media(max-width:800px){.kb-admin{grid-template-columns:1fr}}</style>
