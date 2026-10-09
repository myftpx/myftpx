<div class="page-head"><h1>Yeni Destek Bileti</h1></div>
<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="post" action="<?= url('client/tickets/new') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Konu</label>
                <input class="form-control" name="subject" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Departman</label>
                    <select class="form-control" name="department">
                        <?php foreach ($departments as $d): ?><option><?= e($d) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Öncelik</label>
                    <select class="form-control" name="priority">
                        <option value="low">Düşük</option>
                        <option value="medium" selected>Orta</option>
                        <option value="high">Yüksek</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Mesaj</label>
                <textarea class="form-control" name="message" required></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Bileti Oluştur</button>
        </form>
    </div>
</div>
