<?php
require_once __DIR__ . '/../partials/guard.php';

$activeMenu = $activeMenu ?? '';

$overviewItems = [
    [
        'key'   => 'dashboard',
        'label' => 'Dashboard',
        'icon'  => 'bi-speedometer2',
        'href'  => BASE_URL . '?action=admin',
    ],
];

$manageItems = [
    [
        'key'   => 'categories',
        'label' => 'Quản lý danh mục',
        'icon'  => 'bi-tags',
        'href'  => BASE_URL . '?action=admin-categories',
    ],
    [
        'key'   => 'products',
        'label' => 'Quản lý sản phẩm',
        'icon'  => 'bi-box-seam',
        'href'  => BASE_URL . '?action=admin-products',
    ],
    [
        'key'   => 'users',
        'label' => 'Quản lý tài khoản',
        'icon'  => 'bi-people',
        'href'  => BASE_URL . '?action=admin-users',
    ],
    [
        'key'   => 'orders',
        'label' => 'Quản lý đơn hàng',
        'icon'  => 'bi-receipt',
        'href'  => BASE_URL . '?action=admin-orders',
    ],
];
?>

<aside class="admin-sidebar">

    <a class="admin-brand" href="<?= BASE_URL ?>?action=admin">
        <span class="admin-brand-name">SPORTIX<span class="admin-brand-dot">.</span></span>
        <span class="admin-brand-badge">Admin</span>
    </a>

    <nav class="admin-nav" aria-label="Menu quản trị">

        <p class="admin-nav-label">Tổng quan</p>

        <?php foreach ($overviewItems as $item): ?>
            <a class="admin-nav-link<?= $activeMenu === $item['key'] ? ' active' : '' ?>"
               href="<?= $item['href'] ?>"
               <?= $activeMenu === $item['key'] ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>

        <p class="admin-nav-label">Quản lý</p>

        <?php foreach ($manageItems as $item): ?>
            <a class="admin-nav-link<?= $activeMenu === $item['key'] ? ' active' : '' ?>"
               href="<?= $item['href'] ?>"
               <?= $activeMenu === $item['key'] ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>

    </nav>

    <div class="admin-sidebar-footer">
        <a class="admin-back-link" href="<?= BASE_URL ?>">
            <i class="bi bi-arrow-left-circle" aria-hidden="true"></i>
            <span>Quay lại website</span>
        </a>
    </div>

</aside>
