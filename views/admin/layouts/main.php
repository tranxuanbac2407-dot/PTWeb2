<?php require_once __DIR__ . '/../partials/guard.php'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Khu vực quản trị website SPORTIX.">

    <title><?= htmlspecialchars($title ?? 'Quản trị SPORTIX', ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap CSS (không dùng Bootstrap JS theo yêu cầu dự án) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- CSS riêng cho khu vực quản trị -->
    <link href="<?= BASE_URL ?>assets/css/admin.css?v=<?= @filemtime(PATH_ROOT . 'assets/css/admin.css') ?>" rel="stylesheet">
</head>

<body class="admin-body">

    <!-- Checkbox ẩn điều khiển Sidebar trên mobile bằng CSS thuần (không dùng JS) -->
    <input type="checkbox" id="admin-sidebar-toggle" class="admin-sidebar-toggle-input">

    <div class="admin-shell">

        <?php require_once PATH_VIEW_ADMIN_LAYOUT . 'sidebar.php'; ?>

        <div class="admin-main">

            <?php require_once PATH_VIEW_ADMIN_LAYOUT . 'header.php'; ?>

            <main class="admin-content">
                <?php
                if (isset($view)) {
                    require_once PATH_VIEW . $view . '.php';
                }
                ?>
            </main>

        </div>

        <!-- Lớp phủ dùng để đóng Sidebar trên mobile -->
        <label for="admin-sidebar-toggle" class="admin-sidebar-overlay" aria-hidden="true"></label>

    </div>

</body>

</html>
