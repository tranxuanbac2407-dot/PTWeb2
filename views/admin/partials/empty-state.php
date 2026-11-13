<?php
require_once __DIR__ . '/guard.php';

$emptyIcon  = $emptyIcon ?? 'bi-inbox';
$emptyTitle = $emptyTitle ?? 'Chưa có dữ liệu để hiển thị';

?>

<div class="admin-card">
    <div class="admin-card-body">
        <div class="admin-placeholder">
            <span class="admin-placeholder-icon">
                <i class="bi <?= htmlspecialchars($emptyIcon, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
            </span>
            <h2 class="admin-placeholder-title"><?= htmlspecialchars($emptyTitle, ENT_QUOTES, 'UTF-8') ?></h2>
            <a class="admin-placeholder-link" href="<?= BASE_URL ?>?action=admin">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Quay lại Dashboard
            </a>
        </div>
    </div>
</div>
