<div class="page-head">
    <h1>Ürünler</h1>
    <a class="btn btn-primary" href="<?= url('admin/products/new') ?>">Yeni Ürün</a>
</div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <tr><th>ID</th><th>Ürün</th><th>Kategori</th><th>Tür</th><th>Fiyat</th><th>Dönem</th><th>Modül</th><th>Durum</th><th></th></tr>
        <?php if (empty($products)): ?><tr><td colspan="9" class="empty">Ürün yok. <a href="<?= url('admin/products/new') ?>">İlk ürünü ekleyin</a></td></tr><?php endif; ?>
        <?php foreach ($products as $p): ?>
            <tr>
                <td>#<?= (int)$p['id'] ?></td>
                <td><strong><?= e($p['name']) ?></strong><?= $p['featured'] ? ' <span class="badge badge-primary">Öne Çıkan</span>' : '' ?></td>
                <td><?= e($p['category'] ?: '—') ?></td>
                <td><?= e($p['type']) ?></td>
                <td><?= money($p['price']) ?></td>
                <td><?= e($p['billing_cycle']) ?></td>
                <td><?= e($p['module']) ?></td>
                <td><?= status_badge($p['status']) ?></td>
                <td><a class="btn btn-outline btn-sm" href="<?= url('admin/products/' . $p['id']) ?>">Düzenle</a></td>
            </tr>
        <?php endforeach; ?>
    </table></div>
</div>
