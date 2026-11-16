<?php
require_once __DIR__ . '/../partials/guard.php';

$sessionUser = $_SESSION['user'] ?? null;
if ($sessionUser instanceof __PHP_Incomplete_Class || is_object($sessionUser)) {
    unset($_SESSION['user']);
    $sessionUser = null;
}
if (!is_array($adminUser ?? null)) {
    $adminUser = is_array($sessionUser) ? $sessionUser : [];
}

$adminName = isset($adminUser['username']) && is_scalar($adminUser['username'])
    ? (string) $adminUser['username']
    : 'Admin';
$adminRole = isset($adminUser['role']) && is_scalar($adminUser['role'])
    ? (string) $adminUser['role']
    : 'admin';
$initial   = strtoupper(substr($adminName, 0, 1));
?>

<header class="admin-header">

    <label for="admin-sidebar-toggle" class="admin-menu-toggle" aria-label="Mở menu quản trị">
        <i class="bi bi-list" aria-hidden="true"></i>
    </label>

    <div class="admin-header-title">
        <h1><?= htmlspecialchars($pageTitle ?? 'Quản trị SPORTIX', ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($pageSubtitle)): ?>
            <p><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>

    <div class="admin-header-user">

        <span class="admin-avatar" aria-hidden="true"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></span>

        <div class="admin-user-meta">
            <span class="admin-user-name"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="admin-user-role"><?= htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <a class="admin-logout" href="<?= BASE_URL ?>?action=logout" title="Đăng xuất khỏi tài khoản Admin">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            <span class="admin-logout-text">Đăng xuất</span>
        </a>

    </div>

</header>