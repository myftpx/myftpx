<div class="page-head">
    <h1>Yeni Fatura</h1>
    <a class="btn btn-outline" href="<?= url('admin/invoices') ?>">Geri</a>
</div>
<form method="post" action="<?= url('admin/invoices/new') ?>">
    <?= csrf_field() ?>
    <div class="card mb-3" style="max-width:760px">
        <div class="card-header"><h3>Fatura Bilgileri</h3></div>
        <div class="card-body">
            <div class="form-group"><label>Müşteri</label>
                <select class="form-control" name="user_id" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= e($c['first_name'] . ' ' . $c['last_name'] . ' (' . $c['email'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <h4 class="mb-2">Kalemler</h4>
            <div id="items">
                <div class="form-row mb-2">
                    <div class="form-group" style="flex:2"><label>Açıklama</label><input class="form-control" name="description[]" required></div>
                    <div class="form-group"><label>Tutar</label><input class="form-control" type="number" step="0.01" name="amount[]" required></div>
                </div>
            </div>
            <button class="btn btn-outline btn-sm" type="button" onclick="addItem()">+ Kalem Ekle</button>
        </div>
    </div>
    <button class="btn btn-primary btn-lg" type="submit">Faturayı Oluştur</button>
</form>
<script>
function addItem() {
    var d = document.createElement('div');
    d.className = 'form-row mb-2';
    d.innerHTML = '<div class="form-group" style="flex:2"><label>Açıklama</label><input class="form-control" name="description[]" required></div>' +
        '<div class="form-group"><label>Tutar</label><input class="form-control" type="number" step="0.01" name="amount[]" required></div>';
    document.getElementById('items').appendChild(d);
}
</script>
