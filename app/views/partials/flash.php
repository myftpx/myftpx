<?php $f = flash(); if ($f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endif; ?>
