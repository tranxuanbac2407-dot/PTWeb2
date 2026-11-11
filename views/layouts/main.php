<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SPORTIX - Quần áo và phụ kiện thể thao chính hãng dành cho nam và nữ.">

    <title><?= $title ?? 'SPORTIX | Thời trang thể thao nam & nữ' ?></title>

    <!-- Bootstrap CSS (không dùng Bootstrap JS theo yêu cầu dự án) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS dùng chung của SPORTIX -->
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php require_once PATH_VIEW_LAYOUT . 'header.php'; ?>

    <main class="site-content">
        <div class="container">
            <?php
            if (isset($view)) {
                require_once PATH_VIEW . $view . '.php';
            }
            ?>
        </div>
    </main>

    <?php require_once PATH_VIEW_LAYOUT . 'footer.php'; ?>

</body>

</html>
