<?php

// ----- Quy ước tích hợp với các thành viên khác (chưa hoạt động ở giai đoạn giao diện) -----
// 1) Xác thực: khi đăng nhập thành công, vui lòng set $_SESSION['user'] là mảng
//    ['id' => ..., 'username' => ..., ...] (hoặc chuỗi username) - header sẽ tự hiển thị.
// 2) Giỏ hàng: vui lòng set $_SESSION['cart'] dạng [product_id => so_luong],
//    header sẽ tự tính và hiển thị tổng số lượng.
//
// ----- Các route đang chờ thành viên khác xây dựng (hiện trỏ về trang thông báo 404) -----
// products (danh sách sản phẩm), product-detail (chi tiết), login, register,
// logout, account, cart và các trang nội dung trong footer.

$currentUserName = null;

if (isset($_SESSION['user'])) {
    $currentUserName = is_array($_SESSION['user'])
        ? ($_SESSION['user']['username'] ?? null)
        : $_SESSION['user'];
}

$cartCount = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>

<div class="promo-bar">
    <div class="container">
        <span class="promo-bar-text">
            <strong>GIẢM ĐẾN 50%</strong> CHO BỘ SƯU TẬP THỂ THAO MỚI &mdash; <a href="<?= BASE_URL ?>?action=products&amp;filter=sale">Xem ngay</a>
        </span>
    </div>
</div>

<header class="site-header">
    <input type="checkbox" id="nav-toggle" class="nav-toggle-input">

    <div class="container">
        <div class="header-main">

            <label class="nav-toggle-label" for="nav-toggle" aria-label="Mở menu chính">
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
            </label>

            <a class="brand" href="<?= BASE_URL ?>">SPORTIX<span class="brand-dot">.</span></a>

            <?php // Nhãn các mục menu con là danh mục mẫu - chờ dữ liệu thật từ bảng `categories`
            // và trang danh sách sản phẩm (`?action=products`) do thành viên phụ trách xây dựng. ?>

            <nav class="main-nav" aria-label="Menu chính">
                <ul class="nav-menu">
                <li>
                    <a href="<?= BASE_URL ?>">Trang chủ</a>
                </li>

                <li class="has-submenu">
                    <a href="<?= BASE_URL ?>?action=products&amp;category=nam">Nam <span class="caret">&#9662;</span></a>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=nam&amp;type=ao">Áo thể thao nam</a></li>
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=nam&amp;type=quan">Quần thể thao nam</a></li>
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=nam&amp;type=bo">Bộ đồ thể thao nam</a></li>
                    </ul>
                </li>

                <li class="has-submenu">
                    <a href="<?= BASE_URL ?>?action=products&amp;category=nu">Nữ <span class="caret">&#9662;</span></a>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=nu&amp;type=ao">Áo thể thao nữ</a></li>
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=nu&amp;type=quan">Quần thể thao nữ</a></li>
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=nu&amp;type=bo">Bộ đồ thể thao nữ</a></li>
                    </ul>
                </li>

                <li class="has-submenu">
                    <a href="<?= BASE_URL ?>?action=products&amp;category=phu-kien">Phụ kiện <span class="caret">&#9662;</span></a>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=phu-kien&amp;type=giay">Giày &amp; dép thể thao</a></li>
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=phu-kien&amp;type=balo">Balo &amp; túi thể thao</a></li>
                        <li><a href="<?= BASE_URL ?>?action=products&amp;category=phu-kien&amp;type=khac">Mũ &amp; phụ kiện khác</a></li>
                    </ul>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>?action=products&amp;filter=new">Sản phẩm mới</a>
                </li>

                <li>
                    <a class="nav-sale" href="<?= BASE_URL ?>?action=products&amp;filter=sale">Khuyến mãi</a>
                </li>
                </ul>
            </nav>

            <form class="search-form" action="<?= BASE_URL ?>" method="get" role="search">
                <input type="hidden" name="action" value="search">
                <input class="search-input" type="text" name="keyword"
                       placeholder="Tìm kiếm sản phẩm..."
                       value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button class="search-btn" type="submit" aria-label="Tìm kiếm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.09.09l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.09-.09zm-1.443-.178a5.5 5.5 0 1 1 .9-.9z"/>
                    </svg>
                </button>
            </form>

            <div class="header-actions">

                <div class="account-menu">
                    <?php if ($currentUserName): ?>
                        <a class="account-toggle" href="<?= BASE_URL ?>?action=account">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4"/>
                            </svg>
                            <span class="account-name"><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="caret">&#9662;</span>
                        </a>
                        <ul class="account-submenu">
                            <li><a href="<?= BASE_URL ?>?action=account">Thông tin tài khoản</a></li>
                            <li><a href="<?= BASE_URL ?>?action=logout">Đăng xuất</a></li>
                        </ul>
                    <?php else: ?>
                        <a class="account-toggle" href="<?= BASE_URL ?>?action=login">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4"/>
                            </svg>
                            <span class="account-name">Tài khoản</span>
                            <span class="caret">&#9662;</span>
                        </a>
                        <ul class="account-submenu">
                            <li><a href="<?= BASE_URL ?>?action=login">Đăng nhập</a></li>
                            <li><a href="<?= BASE_URL ?>?action=register">Đăng ký</a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <a class="cart-link" href="<?= BASE_URL ?>?action=cart" aria-label="Giỏ hàng">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1-5zM5 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2m7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                    </svg>
                    <span class="cart-count"><?= (int) $cartCount ?></span>
                </a>

            </div>
        </div>
    </div>
</header>
