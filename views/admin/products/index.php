<?php
require_once __DIR__ . '/../partials/guard.php';

$keyword      = $keyword ?? '';
$categoryId   = (int) ($categoryId ?? 0);
$products     = $products ?? [];
$categories   = $categories ?? [];
$loadError    = $loadError ?? null;
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;
$total        = count($products);
?>

<div class="admin-product-page">

    <!-- Thông báo kết quả thao tác & lỗi tải -->
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

    <?php if (!empty($loadError)): ?>
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <!-- Thanh công cụ điều khiển -->
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <form class="admin-search-form admin-filter-form" action="<?= BASE_URL ?>" method="get" role="search">
                <input type="hidden" name="action" value="admin-products">

                <div class="admin-filter-group">
                    <!-- Ô tìm kiếm tên sản phẩm -->
                    <div class="admin-search-input-group">
                        <i class="bi bi-search admin-search-icon" aria-hidden="true"></i>
                        <input type="text"
                               name="keyword"
                               class="admin-form-input admin-search-input"
                               placeholder="Tìm tên sản phẩm..."
                               value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($keyword !== ''): ?>
                            <a href="<?= BASE_URL ?>?action=admin-products<?= $categoryId > 0 ? '&amp;category_id=' . $categoryId : '' ?>"
                               class="admin-search-clear"
                               title="Xóa từ khóa tìm kiếm">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Dropdown lọc danh mục -->
                    <select name="category_id" class="admin-form-input admin-select-filter" aria-label="Lọc theo danh mục" onchange="this.form.submit()">
                        <option value="0">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php
                                $cId   = (int) ($cat['categories_id'] ?? 0);
                                $cName = (string) ($cat['categories_name'] ?? '');
                            ?>
                            <option value="<?= $cId ?>" <?= $categoryId === $cId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="admin-btn admin-btn-secondary">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>

                    <?php if ($keyword !== '' || $categoryId > 0): ?>
                        <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline" title="Đặt lại bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="admin-toolbar-right">
            <a href="<?= BASE_URL ?>?action=admin-product-create" class="admin-btn admin-btn-accent">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                <span>Thêm sản phẩm mới</span>
            </a>
        </div>
    </div>

    <!-- Bảng danh sách sản phẩm -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span>Danh sách sản phẩm</span>
                <span class="admin-count-badge"><?= $total ?> sản phẩm</span>
            </h3>

            <div class="admin-filter-badges">
                <?php if ($keyword !== ''): ?>
                    <span class="admin-filter-badge">
                        Từ khóa: "<strong><?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?></strong>"
                    </span>
                <?php endif; ?>

                <?php if ($categoryId > 0): ?>
                    <?php
                        $filteredCatName = '';
                        foreach ($categories as $c) {
                            if ((int) ($c['categories_id'] ?? 0) === $categoryId) {
                                $filteredCatName = $c['categories_name'] ?? '';
                                break;
                            }
                        }
                    ?>
                    <?php if ($filteredCatName !== ''): ?>
                        <span class="admin-filter-badge">
                            Danh mục: <strong><?= htmlspecialchars($filteredCatName, ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($products)): ?>
            <div class="table-responsive">
                <table class="admin-table admin-product-table">
                    <thead>
                        <tr>
                            <th style="width: 65px;" class="text-center">ID</th>
                            <th style="width: 80px;" class="text-center">Ảnh</th>
                            <th style="min-width: 240px;">Tên sản phẩm &amp; Danh mục</th>
                            <th style="width: 140px;" class="text-end">Đơn giá</th>
                            <th style="width: 140px;" class="text-center">Size / Màu</th>
                            <th style="width: 130px;" class="text-center">Tồn kho</th>
                            <th style="width: 150px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <?php
                                $pId       = (int) ($p['product_id'] ?? 0);
                                $pName     = (string) ($p['product_name'] ?? '');
                                $pCatName  = (string) ($p['categories_name'] ?? 'Chưa phân loại');
                                $pPrice    = (float) ($p['price'] ?? 0);
                                $pStock    = (int) ($p['stock_quantity'] ?? 0);
                                $pSize     = (string) ($p['size'] ?? '');
                                $pColor    = (string) ($p['color'] ?? '');
                                $pImage    = (string) ($p['image'] ?? '');
                                $imgSrc    = get_product_image_url($pImage);
                            ?>
                            <tr>
                                <!-- ID -->
                                <td class="text-center">
                                    <span class="admin-id-badge">#<?= $pId ?></span>
                                </td>

                                <!-- Thumbnail ảnh sản phẩm -->
                                <td class="text-center">
                                    <div class="admin-product-thumb">
                                        <?php if (!empty($imgSrc)): ?>
                                            <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="admin-product-thumb-empty" title="Chưa có hình ảnh">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Tên sản phẩm & danh mục -->
                                <td>
                                    <div class="admin-product-info-cell">
                                        <a href="<?= BASE_URL ?>?action=admin-product-edit&amp;id=<?= $pId ?>"
                                           class="admin-product-name"
                                           title="Nhấn để chỉnh sửa sản phẩm">
                                            <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        <div class="admin-product-meta">
                                            <span class="admin-badge-cat">
                                                <i class="bi bi-tag-fill"></i>
                                                <?= htmlspecialchars($pCatName, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Giá bán -->
                                <td class="text-end">
                                    <span class="admin-product-price">
                                        <?= number_format($pPrice, 0, ',', '.') ?> <small>đ</small>
                                    </span>
                                </td>

                                <!-- Size & Màu sắc -->
                                <td class="text-center">
                                    <div class="admin-product-attr">
                                        <?php if ($pSize !== ''): ?>
                                            <span class="admin-attr-pill" title="Kích cỡ">
                                                <i class="bi bi-aspect-ratio"></i> <?= htmlspecialchars($pSize, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($pColor !== ''): ?>
                                            <span class="admin-attr-pill" title="Màu sắc">
                                                <i class="bi bi-palette"></i> <?= htmlspecialchars($pColor, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($pSize === '' && $pColor === ''): ?>
                                            <span class="text-muted small">&mdash;</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Tồn kho -->
                                <td class="text-center">
                                    <?php if ($pStock <= 0): ?>
                                        <span class="admin-stock-badge stock-out" title="Sản phẩm đã hết hàng trong kho">
                                            <i class="bi bi-x-circle-fill"></i> Hết hàng
                                        </span>
                                    <?php elseif ($pStock <= 10): ?>
                                        <span class="admin-stock-badge stock-low" title="Sắp hết hàng, cần nhập thêm">
                                            <i class="bi bi-exclamation-triangle-fill"></i> <?= $pStock ?> (Sắp hết)
                                        </span>
                                    <?php else: ?>
                                        <span class="admin-stock-badge stock-in" title="Còn <?= $pStock ?> sản phẩm">
                                            <i class="bi bi-check-circle-fill"></i> <?= $pStock ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Thao tác Sửa / Xóa -->
                                <td class="text-center">
                                    <div class="admin-row-actions">
                                        <a href="<?= BASE_URL ?>?action=admin-product-edit&amp;id=<?= $pId ?>"
                                           class="admin-btn-action admin-btn-action-edit"
                                           title="Chỉnh sửa thông tin &amp; ảnh sản phẩm">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                            <span>Sửa</span>
                                        </a>

                                        <a href="<?= BASE_URL ?>?action=admin-product-delete&amp;id=<?= $pId ?>"
                                           class="admin-btn-action admin-btn-action-delete"
                                           title="Xóa sản phẩm"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm &quot;<?= addslashes(htmlspecialchars($pName, ENT_QUOTES, 'UTF-8')) ?>&quot; (ID: #<?= $pId ?>)?\nHình ảnh và dữ liệu liên quan sẽ bị xóa.');">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                            <span>Xóa</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <?php if ($keyword !== '' || $categoryId > 0): ?>
                    <strong>Không tìm thấy sản phẩm nào phù hợp</strong>
                    <span>Không có sản phẩm nào khớp với bộ lọc hiện tại. Hãy thử tìm từ khóa khác hoặc bỏ chọn danh mục.</span>
                    <div style="margin-top: 1rem;">
                        <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Xem tất cả sản phẩm
                        </a>
                    </div>
                <?php else: ?>
                    <strong>Chưa có sản phẩm nào trong kho</strong>
                    <span>Hệ thống chưa có sản phẩm nào. Bạn có thể bắt đầu đăng bán sản phẩm đầu tiên ngay bây giờ.</span>
                    <div style="margin-top: 1rem;">
                        <a href="<?= BASE_URL ?>?action=admin-product-create" class="admin-btn admin-btn-accent">
                            <i class="bi bi-plus-lg"></i> Thêm sản phẩm đầu tiên
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
