<?php
require_once __DIR__ . '/partials/guard.php';

$adminName = $adminUser['username'] ?? 'Admin';

$quickLinks = [
    [
        'title' => 'Quản lý danh mục',
        'desc'  => 'Tổ chức danh mục sản phẩm theo bộ môn và đối tượng.',
        'icon'  => 'bi-tags',
        'class' => 'categories',
        'href'  => BASE_URL . '?action=admin-categories',
    ],
    [
        'title' => 'Quản lý sản phẩm',
        'desc'  => 'Theo dõi thông tin, giá bán và tồn kho sản phẩm.',
        'icon'  => 'bi-box-seam',
        'class' => 'products',
        'href'  => BASE_URL . '?action=admin-products',
    ],
    [
        'title' => 'Quản lý tài khoản',
        'desc'  => 'Xem và quản lý tài khoản người dùng của hệ thống.',
        'icon'  => 'bi-people',
        'class' => 'users',
        'href'  => BASE_URL . '?action=admin-users',
    ],
    [
        'title' => 'Quản lý đơn hàng',
        'desc'  => 'Theo dõi trạng thái và xử lý đơn hàng của khách.',
        'icon'  => 'bi-receipt',
        'class' => 'orders',
        'href'  => BASE_URL . '?action=admin-orders',
    ],
];
?>

<div class="admin-page-heading">
    <div>
        <h2>Xin chào, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p>Chào mừng bạn đến với khu vực quản trị website SPORTIX.</p>
    </div>
    <a class="admin-page-heading-link" href="<?= BASE_URL ?>">
        <i class="bi bi-globe2" aria-hidden="true"></i>
        Xem website
    </a>
</div>

<!-- ============ A. Thẻ thống kê tổng quan ============ -->
<section class="admin-section">
    <div class="admin-section-heading">
        <h3>Thống kê tổng quan</h3>
        <p>Số liệu thống kê sẽ được cập nhật khi chức năng báo cáo được hoàn thiện.</p>
    </div>

    <div class="row g-3 g-xl-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon admin-stat-icon--categories">
                    <i class="bi bi-tags" aria-hidden="true"></i>
                </span>
                <p class="admin-stat-label">Tổng số danh mục</p>
                <p class="admin-stat-value"><?= isset($totalCategories) ? (int) $totalCategories : '&mdash;' ?></p>
                <p class="admin-stat-note"><?= isset($totalCategories) ? 'Danh mục đang hoạt động' : 'Chưa có dữ liệu thống kê' ?></p>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon admin-stat-icon--products">
                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                </span>
                <p class="admin-stat-label">Tổng số sản phẩm</p>
                <p class="admin-stat-value">&mdash;</p>
                <p class="admin-stat-note">Chưa có dữ liệu thống kê</p>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon admin-stat-icon--users">
                    <i class="bi bi-people" aria-hidden="true"></i>
                </span>
                <p class="admin-stat-label">Tổng số tài khoản</p>
                <p class="admin-stat-value">&mdash;</p>
                <p class="admin-stat-note">Chưa có dữ liệu thống kê</p>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="admin-stat-card">
                <span class="admin-stat-icon admin-stat-icon--orders">
                    <i class="bi bi-receipt" aria-hidden="true"></i>
                </span>
                <p class="admin-stat-label">Tổng số đơn hàng</p>
                <p class="admin-stat-value">&mdash;</p>
                <p class="admin-stat-note">Chưa có dữ liệu thống kê</p>
            </article>
        </div>
    </div>
</section>

<!-- ============ B. Khu vực truy cập nhanh ============ -->
<section class="admin-section">
    <div class="admin-section-heading">
        <h3>Truy cập nhanh</h3>
        <p>Đi nhanh đến các khu vực quản lý chính của hệ thống.</p>
    </div>

    <div class="row g-3 g-xl-4">
        <?php foreach ($quickLinks as $link): ?>
            <div class="col-12 col-md-6 col-xl-3">
                <article class="admin-quick-card">
                    <span class="admin-quick-icon admin-quick-icon--<?= $link['class'] ?>">
                        <i class="bi <?= $link['icon'] ?>" aria-hidden="true"></i>
                    </span>
                    <h4 class="admin-quick-title"><?= htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <p class="admin-quick-desc"><?= htmlspecialchars($link['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="admin-quick-link" href="<?= $link['href'] ?>">
                        Truy cập
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ C. Khu vực đơn hàng gần đây ============ -->
<section class="admin-section">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                Đơn hàng gần đây
            </h3>
            <a class="admin-card-link" href="<?= BASE_URL ?>?action=admin-orders">
                Xem tất cả
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Mã đơn hàng</th>
                        <th scope="col">Người đặt hàng</th>
                        <th scope="col">Ngày đặt</th>
                        <th scope="col">Tổng tiền</th>
                        <th scope="col">Trạng thái đơn hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <div class="admin-empty">
                                <i class="bi bi-inbox" aria-hidden="true"></i>
                                <strong>Chưa có đơn hàng nào để hiển thị</strong>
                                <span>Dữ liệu đơn hàng sẽ xuất hiện tại đây khi chức năng quản lý đơn hàng được hoàn thiện.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
