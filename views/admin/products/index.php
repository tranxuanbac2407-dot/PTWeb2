<?php
require_once __DIR__ . '/../partials/guard.php';

$products     = $products ?? [];
$loadError    = $loadError ?? null;
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;
$total        = count($products);
?>

<div class="admin-product-page">

    <!-- Thông báo kết quả thao tác -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="admin-alert admin-alert-success" role="alert">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <!-- Thanh công cụ -->
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <span class="admin-filter-badge">
                Quản lý thông tin, giá bán và tồn kho sản phẩm.
            </span>
        </div>

        <div class="admin-toolbar-right">
            <a href="<?= BASE_URL ?>?action=admin-product-create" class="admin-btn admin-btn-accent">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                <span>Thêm sản phẩm</span>
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span>Danh sách sản phẩm</span>
                <span class="admin-count-badge"><?= $total ?> sản phẩm</span>
            </h3>
        </div>

        <?php if (!empty($loadError)): ?>
            <div class="admin-empty">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <strong>Không thể tải danh sách sản phẩm</strong>
                <span><?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8') ?></span>
                <div style="margin-top: 1rem;">
                    <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Thử lại
                    </a>
                </div>
            </div>
        <?php elseif (empty($products)): ?>
            <div class="admin-empty">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <strong>Chưa có sản phẩm nào để hiển thị</strong>
                <span>Hệ thống chưa có sản phẩm nào. Bạn có thể bắt đầu thêm sản phẩm đầu tiên ngay bây giờ.</span>
                <div style="margin-top: 1rem;">
                    <a href="<?= BASE_URL ?>?action=admin-product-create" class="admin-btn admin-btn-accent">
                        <i class="bi bi-plus-lg"></i> Thêm sản phẩm đầu tiên
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 70px;">ID</th>
                            <th scope="col" class="text-center" style="width: 90px;">Hình ảnh</th>
                            <th scope="col" style="min-width: 220px;">Tên sản phẩm</th>
                            <th scope="col" style="min-width: 150px;">Danh mục</th>
                            <th scope="col" class="text-end" style="width: 130px;">Giá bán</th>
                            <th scope="col" style="min-width: 120px;">Kích thước</th>
                            <th scope="col" style="min-width: 130px;">Màu sắc</th>
                            <th scope="col" class="text-center" style="width: 120px;">Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php
                                $pId       = (int) ($product['product_id'] ?? 0);
                                $pName     = (string) ($product['product_name'] ?? '');
                                $pCategory = (string) ($product['categories_name'] ?? '');
                                $pPrice    = (float) ($product['price'] ?? 0);
                                $pSize     = (string) ($product['size'] ?? '');
                                $pColor    = (string) ($product['color'] ?? '');
                                $pStock    = (int) ($product['stock_quantity'] ?? 0);
                                $pImage    = (string) ($product['image'] ?? '');

                                $imgSrc = '';
                                if ($pImage !== '') {
                                    if (preg_match('~^https?://~i', $pImage)) {
                                        $imgSrc = $pImage;
                                    } else {
                                        $imgSrc = BASE_ASSETS_UPLOADS . ltrim($pImage, '/');
                                    }
                                }

                                if ($pStock <= 0) {
                                    $stockClass = 'admin-stock-out';
                                    $stockText  = 'Hết hàng';
                                } elseif ($pStock <= 10) {
                                    $stockClass = 'admin-stock-low';
                                    $stockText  = 'Sắp hết';
                                } else {
                                    $stockClass = 'admin-stock-in';
                                    $stockText  = 'Còn hàng';
                                }
                            ?>
                            <tr>
                                <td class="text-center">
                                    <span class="admin-id-badge">#<?= $pId ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($imgSrc !== ''): ?>
                                        <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>"
                                             class="admin-product-thumb">
                                    <?php else: ?>
                                        <span class="admin-product-no-image" title="Chưa có hình ảnh">
                                            <i class="bi bi-image" aria-hidden="true"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="admin-product-name"><?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td>
                                    <?php if ($pCategory !== ''): ?>
                                        <span class="admin-product-category"><?= htmlspecialchars($pCategory, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <span class="admin-product-price">
                                        <?= number_format($pPrice, 0, ',', '.') ?>&#8363;
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-product-meta">
                                        <?= $pSize !== '' ? htmlspecialchars($pSize, ENT_QUOTES, 'UTF-8') : '&mdash;' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-product-meta">
                                        <?= $pColor !== '' ? htmlspecialchars($pColor, ENT_QUOTES, 'UTF-8') : '&mdash;' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="admin-stock-badge <?= $stockClass ?>">
                                        <?= $pStock ?> &middot; <?= $stockText ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
