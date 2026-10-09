<?php $isEdit = $product !== null; ?>
<div class="page-head">
    <h1><?= $isEdit ? 'Ürünü Düzenle' : 'Yeni Ürün' ?></h1>
    <a class="btn btn-outline" href="<?= url('admin/products') ?>">Geri</a>
</div>

<form method="post" action="<?= url($isEdit ? 'admin/products/' . $product['id'] : 'admin/products/new') ?>">
    <?= csrf_field() ?>
    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Ürün Bilgileri</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group"><label>Ürün Adı</label><input class="form-control" name="name" value="<?= e($product['name'] ?? '') ?>" required></div>
                <div class="form-group"><label>Kategori</label><input class="form-control" name="category" value="<?= e($product['category'] ?? '') ?>" placeholder="örn: Web Hosting"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Tür</label>
                    <select class="form-control" name="type">
                        <?php foreach (['hosting' => 'Hosting', 'reseller' => 'Reseller', 'vps' => 'VPS', 'server' => 'Sunucu', 'domain' => 'Alan Adı', 'ssl' => 'SSL', 'other' => 'Diğer'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($product['type'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Modül</label>
                    <select class="form-control" name="module">
                        <option value="none">Yok</option>
                        <?php foreach ($modules as $m): ?>
                            <option value="<?= e($m['code']) ?>" <?= ($product['module'] ?? '') === $m['code'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group"><label>Fiyat</label><input class="form-control" type="number" step="0.01" name="price" value="<?= e($product['price'] ?? 0) ?>"></div>
                <div class="form-group"><label>Kurulum Ücreti</label><input class="form-control" type="number" step="0.01" name="setup_fee" value="<?= e($product['setup_fee'] ?? 0) ?>"></div>
                <div class="form-group"><label>Faturalama Dönemi</label>
                    <select class="form-control" name="billing_cycle">
                        <?php foreach (['monthly' => 'Aylık', 'quarterly' => '3 Aylık', 'semi_annual' => '6 Aylık', 'annually' => 'Yıllık', 'biennially' => '2 Yıllık'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($product['billing_cycle'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Durum</label>
                    <select class="form-control" name="status">
                        <option value="active" <?= ($product['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                    </select>
                </div>
                <div class="form-group"><label>Öne Çıkan</label>
                    <label class="form-check mt-2"><input type="checkbox" name="featured" value="1" <?= !empty($product['featured']) ? 'checked' : '' ?>> Ana sayfada öne çıkar</label>
                </div>
            </div>
            <div class="form-group"><label>Açıklama</label><textarea class="form-control" name="description" rows="5"><?= e($product['description'] ?? '') ?></textarea></div>
        </div>
    </div>
    <div class="card mb-3" style="max-width:860px">
        <div class="card-header"><h3>Yapılandırma Seçenekleri</h3><span class="text-muted small">Virgülle ayrılmış değerler</span></div>
        <div class="card-body">
            <div id="opts">
                <?php
                $existing = $options ?: [];
                if (empty($existing)) $existing = [null];
                foreach ($existing as $o):
                    $o = $o ?: [];
                    $o['options'] = is_array($o['options'] ?? null) ? $o['options'] : [];
                ?>
                    <div class="form-row mb-2" data-opt>
                        <div class="form-group"><label>Ad</label><input class="form-control" name="opt_name[]" value="<?= e($o['name'] ?? '') ?>" placeholder="örn: RAM"></div>
                        <div class="form-group"><label>Tip</label>
                            <select class="form-control" name="opt_type[]">
                                <option value="dropdown" <?= ($o['type'] ?? '') === 'dropdown' ? 'selected' : '' ?>>Dropdown</option>
                                <option value="radio" <?= ($o['type'] ?? '') === 'radio' ? 'selected' : '' ?>>Radio</option>
                                <option value="text" <?= ($o['type'] ?? '') === 'text' ? 'selected' : '' ?>>Metin</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Değerler</label><input class="form-control" name="opt_options[]" value="<?= e(implode(',', $o['options'])) ?>" placeholder="2GB,4GB,8GB"></div>
                        <div class="form-group"><label>Zorunlu</label><label class="form-check mt-2"><input type="checkbox" name="opt_required[]" <?= !empty($o['required']) ? 'checked' : '' ?>></label></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-outline btn-sm" type="button" onclick="addOpt()">+ Seçenek Ekle</button>
        </div>
    </div>

    <button class="btn btn-primary btn-lg" type="submit"><?= $isEdit ? 'Güncelle' : 'Oluştur' ?></button>
</form>

<script>
function addOpt() {
    var d = document.createElement('div');
    d.className = 'form-row mb-2';
    d.innerHTML = '<div class="form-group"><label>Ad</label><input class="form-control" name="opt_name[]" placeholder="örn: Disk"></div>' +
        '<div class="form-group"><label>Tip</label><select class="form-control" name="opt_type[]"><option value="dropdown">Dropdown</option><option value="radio">Radio</option><option value="text">Metin</option></select></div>' +
        '<div class="form-group"><label>Değerler</label><input class="form-control" name="opt_options[]" placeholder="10GB,50GB"></div>' +
        '<div class="form-group"><label>Zorunlu</label><label class="form-check mt-2"><input type="checkbox" name="opt_required[]"></label></div>';
    document.getElementById('opts').appendChild(d);
}
</script>
