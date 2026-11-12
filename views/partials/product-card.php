<?php
/**
 * views/partials/product-card.php
 * Card hiển thị sản phẩm dùng chung cho Trang chủ, Tìm kiếm, Danh sách sản phẩm.
 *
 * Yêu cầu biến đầu vào:
 * @var array  $product  Mảng thông tin sản phẩm
 * @var string $cardBadge Nhãn huy hiệu ('new', 'hot', 'sale', hoặc tùy biến)
 */

$pId    = (int) ($product['product_id'] ?? 0);
$pName  = (string) ($product['product_name'] ?? 'Sản phẩm thể thao');
$pPrice = (float) ($product['price'] ?? 0);
$pOld   = isset($product['old_price']) ? (float) $product['old_price'] : 0;
$pCat   = (string) ($product['categories_name'] ?? 'Thể thao');
$pSold  = (int) ($product['total_sold'] ?? 0);
$pRate  = isset($product['rating']) ? (float) $product['rating'] : 4.9;

// Xác định nguồn ảnh: URL mẫu trực tiếp hoặc file upload trong thư mục hệ thống
$imgSrc = null;
if (!empty($product['image_url'])) {
    $imgSrc = $product['image_url'];
} elseif (!empty($product['image'])) {
    if (str_starts_with($product['image'], 'http://') || str_starts_with($product['image'], 'https://')) {
        $imgSrc = $product['image'];
    } else {
        $imgSrc = BASE_ASSETS_UPLOADS . htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8');
    }
}

// Tính % giảm giá nếu có giá cũ lớn hơn giá hiện tại
$discountPercent = 0;
if ($pOld > $pPrice) {
    $discountPercent = round((($pOld - $pPrice) / $pOld) * 100);
}

$detailUrl = BASE_URL . '?action=product-detail&amp;id=' . $pId;
$badgeMode = $cardBadge ?? '';
?>

<div class="product-card sx-card">
    <div class="product-thumb-wrapper">
        <a class="product-thumb" href="<?= $detailUrl ?>">
            <?php if (!empty($imgSrc)): ?>
                <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>"
                     loading="lazy">
            <?php else: ?>
                <div class="product-no-image">
                    <i class="bi bi-card-image" style="font-size: 2.2rem;"></i>
                    <span>Chưa có hình ảnh</span>
                </div>
            <?php endif; ?>
        </a>

        <!-- Huy hiệu trạng thái -->
        <div class="product-badges">
            <?php if ($badgeMode === 'new' || !empty($product['is_new'])): ?>
                <span class="badge-tag badge-new">
                    <i class="bi bi-stars"></i> Mới
                </span>
            <?php elseif ($badgeMode === 'hot' || !empty($product['is_hot'])): ?>
                <span class="badge-tag badge-hot">
                    <i class="bi bi-fire"></i> Bán chạy
                </span>
            <?php endif; ?>

            <?php if ($discountPercent > 0): ?>
                <span class="badge-tag badge-discount">-<?= $discountPercent ?>%</span>
            <?php endif; ?>
        </div>

        <!-- Nút thao tác nhanh khi rê chuột -->
        <div class="product-actions-hover">
            <a href="<?= $detailUrl ?>" class="btn-action-icon" title="Xem chi tiết" aria-label="Xem chi tiết">
                <i class="bi bi-eye"></i>
            </a>
            <a href="<?= BASE_URL ?>?action=cart&amp;add=<?= $pId ?>" class="btn-action-icon" title="Thêm vào giỏ hàng" aria-label="Thêm vào giỏ">
                <i class="bi bi-cart-plus"></i>
            </a>
        </div>
    </div>

    <div class="product-info">
        <span class="product-category"><?= htmlspecialchars($pCat, ENT_QUOTES, 'UTF-8') ?></span>

        <h3 class="product-name">
            <a href="<?= $detailUrl ?>" title="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
            </a>
        </h3>

        <div class="product-meta-row">
            <div class="product-rating">
                <i class="bi bi-star-fill text-warning"></i>
                <span><?= number_format($pRate, 1) ?></span>
            </div>
            <?php if ($pSold > 0): ?>
                <span class="product-sold-text">Đã bán <?= $pSold ?></span>
            <?php endif; ?>
        </div>

        <div class="product-pricing">
            <span class="product-price">
                <?= number_format($pPrice, 0, ',', '.') ?>&#8363;
            </span>
            <?php if ($pOld > $pPrice): ?>
                <del class="product-old-price">
                    <?= number_format($pOld, 0, ',', '.') ?>&#8363;
                </del>
            <?php endif; ?>
        </div>

        <div class="product-footer-action">
            <a href="<?= $detailUrl ?>" class="btn-view-detail">
                <span>Xem chi tiết</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
