<div class="container" style="padding-top:40px;max-width:860px">
    <h1 class="mb-1">"<?= e($query) ?>" için sonuçlar</h1>
    <p class="text-muted mb-3">Uygun olan alan adını seçip kaydedin.</p>
    <?php foreach ($results as $r): ?>
        <div class="domain-result">
            <div>
                <div class="dname"><?= e($r['domain']) ?></div>
                <?php if ($r['available']): ?><span class="text-success small">✓ Uygun</span><?php else: ?><span class="text-danger small">✗ Alınmış</span><?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($r['available']): ?>
                    <div class="text-right mr-2">
                        <div class="small text-muted"><?= money($r['register_price']) ?>/yıl</div>
                        <div class="small text-muted">Yenileme <?= money($r['renew_price']) ?></div>
                    </div>
                    <form method="post" action="<?= url('domains/register') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="domain" value="<?= e($r['domain']) ?>">
                        <input type="hidden" name="tld" value="<?= e($r['tld']) ?>">
                        <input type="hidden" name="years" value="1">
                        <button class="btn btn-primary btn-sm" type="submit">Kaydet</button>
                    </form>
                <?php else: ?>
                    <span class="btn btn-outline btn-sm" style="opacity:.5">Alınmış</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="mt-3"><a class="btn btn-outline" href="<?= url('domains') ?>">← Yeni Arama</a></div>
</div>
