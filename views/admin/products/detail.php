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

$sizeColumns = [];
$colorRows   = [];
$stockMatrix = [];
$colorTotals = [];

foreach ($variants as $variant) {
    $color = trim((string) ($variant['color'] ?? ''));
    $size  = trim((string) ($variant['size'] ?? ''));
    $stock = (int) ($variant['stock_quantity'] ?? 0);

    if ($color === '') {
        $color = '—';
    }
    if ($size === '') {
        $size = '—';
    }

    $colorRows[$color]   = $color;
    $sizeColumns[$size]  = $size;
    $stockMatrix[$color][$size] = $stock;
    $colorTotals[$color] = ($colorTotals[$color] ?? 0) + $stock;
}

$apparelSizeOrder = ['S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5];
$sortedSizes = array_values($sizeColumns);
usort($sortedSizes, static function ($left, $right) use ($apparelSizeOrder) {
    $normalizedLeft  = strtoupper(trim((string) $left));
    $normalizedRight = strtoupper(trim((string) $right));

    $rank = static function ($size) use ($apparelSizeOrder) {
        $normalized = strtoupper(trim((string) $size));
        if (isset($apparelSizeOrder[$normalized])) {
            return [$apparelSizeOrder[$normalized], 0.0];
        }
        if (is_numeric($normalized)) {
            return [1000, (float) $normalized];
        }
        return [2000, 0.0];
    };

    [$leftRank, $leftValue] = $rank($left);
    [$rightRank, $rightValue] = $rank($right);

    if ($leftRank !== $rightRank) {
        return $leftRank <=> $rightRank;
    }
    if ($leftRank === 1000 && $leftValue !== $rightValue) {
        return $leftValue <=> $rightValue;
    }

    return strnatcasecmp($normalizedLeft, $normalizedRight);
});
$sizeColumns = $sortedSizes;
$matrixTotal = array_sum($colorTotals);
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
                <div class="admin-card-header">
                    <h3><i class="bi bi-box-seam me-1" aria-hidden="true"></i> Thông tin sản phẩm #<?= $productId ?></h3>
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
        <div class="admin-card-header">
            <h3><i class="bi bi-grid-3x3-gap me-1" aria-hidden="true"></i> Tồn kho theo màu &amp; size</h3>
        </div>
        <div class="admin-card-body">
            <?php if (empty($variants)): ?>
                <div class="alert alert-info mb-0" role="status">
                    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                    Sản phẩm này chưa có biến thể nào. Vui lòng mở form sửa sản phẩm để tạo ma trận tồn kho.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 admin-variant-matrix">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="admin-variant-matrix-label">Màu</th>
                                <?php foreach ($sizeColumns as $size): ?>
                                    <th scope="col" class="text-end"><?= htmlspecialchars($size, ENT_QUOTES, 'UTF-8') ?></th>
                                <?php endforeach; ?>
                                <th scope="col" class="text-end">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colorRows as $color): ?>
                                <tr>
                                    <th scope="row" class="admin-variant-matrix-label"><?= htmlspecialchars($color, ENT_QUOTES, 'UTF-8') ?></th>
                                    <?php foreach ($sizeColumns as $size): ?>
                                        <?php if (array_key_exists($size, $stockMatrix[$color] ?? [])): ?>
                                            <td class="text-end admin-variant-matrix-qty"><?= (int) $stockMatrix[$color][$size] ?></td>
                                        <?php else: ?>
                                            <td class="text-end admin-variant-matrix-empty">—</td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <td class="text-end admin-variant-matrix-total"><?= (int) ($colorTotals[$color] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end fw-semibold">Tổng tồn kho: <strong><?= $matrixTotal ?></strong></div>
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
