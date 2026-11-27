<?php
require_once __DIR__ . '/../partials/guard.php';

$product      = $product ?? [];
$variants     = $variants ?? [];
$totalStock   = (int) ($totalStock ?? 0);
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;

$productId    = (int) ($product['product_id'] ?? 0);
$productName  = (string) ($product['product_name'] ?? '');
$categoryName = (string) ($product['categories_name'] ?? '');
$price        = (float) ($product['price'] ?? 0);
$description  = trim((string) ($product['description'] ?? ''));
$imageUrl     = get_product_image_url($product['image'] ?? null);
$sizeValue    = trim((string) ($product['size'] ?? ''));
$colorValue   = trim((string) ($product['color'] ?? ''));
?>
<div class="admin-product-detail-page">

    <div class="admin-form-back mb-3">
        <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            <span>Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="admin-alert admin-alert-success mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="admin-alert admin-alert-danger mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h3><i class="bi bi-image me-1" aria-hidden="true"></i> Ảnh sản phẩm</h3>
                </div>
                <div class="admin-card-body text-center">
                    <?php if ($imageUrl !== ''): ?>
                        <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                             class="img-fluid rounded border"
                             style="max-height: 320px; object-fit: contain;">
                    <?php else: ?>
                        <div class="admin-product-thumb-empty mx-auto" style="height: 260px;">
                            <i class="bi bi-image fs-1" aria-hidden="true"></i>
                            <span class="d-block mt-2">Chưa có hình ảnh</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3><i class="bi bi-box-seam me-1" aria-hidden="true"></i> Thông tin sản phẩm #<?= $productId ?></h3>
                    <span class="admin-product-price fs-5"><?= number_format($price, 0, ',', '.') ?> <small>đ</small></span>
                </div>
                <div class="admin-card-body">
                    <div class="admin-order-meta-grid">
                        <div class="row g-3">
                            <div class="col-12">
                                <span class="text-muted small d-block">Tên sản phẩm:</span>
                                <strong><?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Mã sản phẩm:</span>
                                <strong>#<?= $productId ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Danh mục:</span>
                                <strong><?= htmlspecialchars($categoryName !== '' ? $categoryName : 'Chưa phân loại', ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Đơn giá:</span>
                                <strong><?= number_format($price, 0, ',', '.') ?> đ</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Tổng tồn kho biến thể:</span>
                                <strong><?= $totalStock ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Size cấp sản phẩm:</span>
                                <strong><?= htmlspecialchars($sizeValue !== '' ? $sizeValue : '—', ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Màu cấp sản phẩm:</span>
                                <strong><?= htmlspecialchars($colorValue !== '' ? $colorValue : '—', ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="fs-6 fw-bold mb-2"><i class="bi bi-card-text me-1" aria-hidden="true"></i> Mô tả sản phẩm</h4>
                        <?php if ($description !== ''): ?>
                            <div class="admin-return-info-box">
                                <?= nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Sản phẩm này chưa có mô tả.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3><i class="bi bi-grid-3x3-gap me-1" aria-hidden="true"></i> Tồn kho theo biến thể</h3>
            <?php if (!empty($variants)): ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Tổng: <?= $totalStock ?></span>
            <?php endif; ?>
        </div>
        <div class="admin-card-body">
            <?php if (empty($variants)): ?>
                <div class="alert alert-info mb-0" role="status">
                    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                    Sản phẩm này chưa có biến thể nào. Vui lòng mở form sửa sản phẩm để tạo ma trận tồn kho.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 90px;">Mã</th>
                                <th>Màu</th>
                                <th>Size</th>
                                <th class="text-end" style="width: 140px;">Tồn kho</th>
                                <th class="text-center" style="width: 150px;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variants as $variant): ?>
                                <?php $isActive = (int) ($variant['is_active'] ?? 1) === 1; ?>
                                <tr>
                                    <td class="text-center">#<?= (int) ($variant['variant_id'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars((string) ($variant['color'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($variant['size'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end"><strong><?= (int) ($variant['stock_quantity'] ?? 0) ?></strong></td>
                                    <td class="text-center">
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Đang dùng</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Ngừng dùng</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Tổng tồn kho</th>
                                <th class="text-end"><?= $totalStock ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Quay lại danh sách
        </a>
        <a href="<?= BASE_URL ?>?action=admin-product-edit&amp;id=<?= $productId ?>" class="admin-btn admin-btn-accent">
            <i class="bi bi-pencil-square me-1" aria-hidden="true"></i> Sửa sản phẩm
        </a>
    </div>

</div>
