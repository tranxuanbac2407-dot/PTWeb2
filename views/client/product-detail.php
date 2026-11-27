<?php
/**
 * views/product-detail.php
 * Giao diện xem chi tiết sản phẩm SPORTIX.
 *
 * @var array $product          Thông tin chi tiết sản phẩm
 * @var array $relatedProducts   Danh sách sản phẩm liên quan
 * @var array $sizes            Danh sách kích thước
 * @var array $colors           Danh sách màu sắc
 * @var string|null $flashSuccess
 * @var string|null $flashError
 */

$variants   = $variants ?? [];
$variantMap = $variantMap ?? [];

$pId       = (int) ($product['product_id'] ?? 0);
$pName     = (string) ($product['product_name'] ?? 'Sản phẩm thể thao');
$pCatId    = (int) ($product['category_id'] ?? 0);
$pCatName  = (string) ($product['categories_name'] ?? 'Thể thao');
$pPrice    = (float) ($product['price'] ?? 0);
$pOld      = isset($product['old_price']) ? (float) $product['old_price'] : ($pPrice * 1.25);
$pDesc     = (string) ($product['description'] ?? '');
$pImage    = (string) ($product['image'] ?? '');
$imgSrc    = get_product_image_url($pImage);

$pStock        = 0;
$initialVariant = null;
foreach ($variants as $variant) {
    if ((int) ($variant['is_active'] ?? 0) !== 1) {
        continue;
    }
    $stock = (int) ($variant['stock_quantity'] ?? 0);
    if ($stock > $pStock) {
        $pStock = $stock;
    }
    if ($initialVariant === null && $stock > 0) {
        $initialVariant = $variant;
    }
}

$initialSize  = $initialVariant ? normalize_variant_value($initialVariant['size'] ?? '') : ($sizes[0] ?? '');
$initialColor = $initialVariant ? normalize_variant_value($initialVariant['color'] ?? '') : ($colors[0] ?? '');

$availableSizes  = [];
$availableColors = [];
foreach ($variantMap as $variant) {
    if ((int) ($variant['is_active'] ?? 0) !== 1 || (int) ($variant['stock_quantity'] ?? 0) <= 0) {
        continue;
    }
    $availableColors[normalize_variant_value($variant['color'] ?? '')] = true;
    $availableSizes[normalize_variant_value($variant['size'] ?? '')] = true;
}

// % giảm giá
$discountPercent = 0;
if ($pOld > $pPrice) {
    $discountPercent = round((($pOld - $pPrice) / $pOld) * 100);
}

$flashSuccess = $flashSuccess ?? ($_SESSION['flash_success'] ?? null);
$flashError   = $flashError ?? ($_SESSION['flash_error'] ?? null);
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="product-detail-page">
    <div class="container">

        <!-- Breadcrumbs điều hướng -->
        <nav class="sx-breadcrumb" aria-label="Đường dẫn trang">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>?action=products&amp;category=<?= $pCatId ?>">
                        <?= htmlspecialchars($pCatName, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
                </li>
            </ol>
        </nav>

        <!-- Thông báo thao tác giỏ hàng nếu có -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
                <a href="<?= BASE_URL ?>?action=cart" class="btn btn-sm btn-success ms-3 text-nowrap">
                    <i class="bi bi-cart3"></i> Xem giỏ hàng
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Khung chi tiết sản phẩm chính -->
        <div class="product-detail-wrapper sx-card mb-5">
            <div class="row g-4 g-lg-5">

                <!-- 1. CỘT TRÁI: HÌNH ẢNH SẢN PHẨM -->
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="product-gallery">
                        <div class="product-main-image-wrap">
                            <?php if (!empty($imgSrc)): ?>
                                <img id="detailMainImage"
                                     src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>"
                                     class="product-main-image img-fluid">
                            <?php else: ?>
                                <div class="product-detail-no-image">
                                    <i class="bi bi-card-image"></i>
                                    <span>Chưa có hình ảnh sản phẩm</span>
                                </div>
                            <?php endif; ?>

                            <!-- Huy hiệu trạng thái -->
                            <div class="product-gallery-badges">
                                <?php if ($discountPercent > 0): ?>
                                    <span class="badge-tag badge-discount">-<?= $discountPercent ?>%</span>
                                <?php endif; ?>
                                <?php if ($pStock <= 0): ?>
                                    <span class="badge-tag badge-stock-out">Hết hàng</span>
                                <?php elseif ($pStock <= 10): ?>
                                    <span class="badge-tag badge-stock-low">Sắp hết hàng</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Cam kết chất lượng dưới ảnh -->
                        <div class="product-image-footer text-center mt-3 text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            Hình ảnh chụp thực tế &middot; Cam kết sản phẩm 100% như mô tả
                        </div>
                    </div>
                </div>

                <!-- 2. CỘT PHẢI: THÔNG TIN CHI TIẾT & BỘ CHỌN THUỘC TÍNH -->
                <div class="col-12 col-md-6 col-lg-7">
                    <div class="product-summary">

                        <!-- Danh mục & Mã sản phẩm -->
                        <div class="product-category-meta mb-2 d-flex align-items-center justify-content-between">
                            <span class="product-cat-badge">
                                <i class="bi bi-tag-fill me-1"></i>
                                <?= htmlspecialchars($pCatName, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="product-sku-text text-muted small">Mã SP: #<?= $pId ?></span>
                        </div>

                        <!-- Tên sản phẩm -->
                        <h1 class="product-detail-title mb-2">
                            <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
                        </h1>

                        <!-- Đánh giá và số lượng đã bán -->
                        <div class="product-rating-meta d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                            <div class="stars text-warning d-flex align-items-center">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="ms-1 fw-bold text-dark">4.9</span>
                            </div>
                            <span class="text-muted">&bull;</span>
                            <span class="text-muted small">Đã bán 128+</span>
                            <span class="text-muted">&bull;</span>
                            <?php if ($pStock > 0): ?>
                                <span class="stock-status-text in-stock text-success small fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>Còn <?= $pStock ?> sản phẩm trong kho
                                </span>
                            <?php else: ?>
                                <span class="stock-status-text out-stock text-danger small fw-semibold">
                                    <i class="bi bi-x-circle me-1"></i>Tạm hết hàng
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Giá bán -->
                        <div class="product-price-box p-3 mb-4 rounded-3">
                            <div class="d-flex align-items-baseline gap-3">
                                <span class="product-current-price fs-2 fw-bold text-accent">
                                    <?= number_format($pPrice, 0, ',', '.') ?> <small class="fs-6">đ</small>
                                </span>
                                <?php if ($pOld > $pPrice): ?>
                                    <span class="product-original-price text-muted text-decoration-line-through fs-5">
                                        <?= number_format($pOld, 0, ',', '.') ?> đ
                                    </span>
                                    <span class="product-save-badge badge bg-danger-subtle text-danger fw-semibold">
                                        Tiết kiệm <?= number_format($pOld - $pPrice, 0, ',', '.') ?> đ
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- FORM CHỌN THUỘC TÍNH VÀ THÊM VÀO GIỎ HÀNG -->
                        <form action="<?= BASE_URL ?>?action=add-to-cart" method="post" id="addToCartForm">
                            <input type="hidden" name="product_id" value="<?= $pId ?>">
                            <input type="hidden" name="variant_id" id="selectedVariantId" value="<?= $initialVariant ? (int) ($initialVariant['variant_id'] ?? 0) : 0 ?>">

                            <!-- CHỌN KÍCH CỠ (SIZE) -->
                            <?php if (!empty($sizes)): ?>
                                <div class="product-option-group mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="product-option-label fw-bold">
                                            Kích cỡ (Size): <span id="selectedSizeText" class="text-accent fw-normal ms-1"><?= htmlspecialchars($initialSize, ENT_QUOTES, 'UTF-8') ?></span>
                                        </label>
                                    </div>
                                    <div class="product-size-pills d-flex flex-wrap gap-2">
                                        <?php foreach ($sizes as $s): ?>
                                            <?php $isAvailable = isset($availableSizes[normalize_variant_value($s)]); ?>
                                            <label class="size-pill-label" title="<?= $isAvailable ? 'Còn hàng' : 'Tạm hết hàng' ?>">
                                                <input type="radio"
                                                       name="size"
                                                       value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"
                                                       class="size-pill-input"
                                                       <?= normalize_variant_value($s) === normalize_variant_value($initialSize) ? 'checked' : '' ?>
                                                       <?= $isAvailable ? '' : 'disabled' ?>>
                                                <span class="size-pill-btn"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- CHỌN MÀU SẮC (COLOR) -->
                            <?php if (!empty($colors)): ?>
                                <div class="product-option-group mb-4">
                                    <label class="product-option-label fw-bold mb-2">
                                        Màu sắc: <span id="selectedColorText" class="text-accent fw-normal ms-1"><?= htmlspecialchars($initialColor, ENT_QUOTES, 'UTF-8') ?></span>
                                    </label>
                                    <div class="product-color-pills d-flex flex-wrap gap-2">
                                        <?php foreach ($colors as $c): ?>
                                            <?php $isAvailable = isset($availableColors[normalize_variant_value($c)]); ?>
                                            <label class="color-pill-label" title="<?= $isAvailable ? 'Còn hàng' : 'Tạm hết hàng' ?>">
                                                <input type="radio"
                                                       name="color"
                                                       value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>"
                                                       class="color-pill-input"
                                                       <?= normalize_variant_value($c) === normalize_variant_value($initialColor) ? 'checked' : '' ?>
                                                       <?= $isAvailable ? '' : 'disabled' ?>>
                                                <span class="color-pill-btn"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- CHỌN SỐ LƯỢNG (QUANTITY) -->
                            <div class="product-quantity-group mb-4">
                                <label class="product-option-label fw-bold mb-2">Số lượng mua:</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="quantity-stepper input-group" style="width: 140px;">
                                        <button class="btn btn-outline-secondary"
                                                type="button"
                                                id="qtyMinus"
                                                aria-label="Giảm số lượng">&minus;</button>
                                        <input type="number"
                                               name="quantity"
                                               id="qtyInput"
                                               class="form-control text-center fw-bold"
                                               value="1"
                                               min="1"
                                               max="<?= max(1, $pStock) ?>"
                                               <?= $pStock <= 0 ? 'disabled' : '' ?>>
                                        <button class="btn btn-outline-secondary"
                                                type="button"
                                                id="qtyPlus"
                                                aria-label="Tăng số lượng">+</button>
                                    </div>
                                    <span class="text-muted small" id="variantStockHint">
                                        (Tối đa <?= $pStock ?> sản phẩm)
                                    </span>
                                </div>
                            </div>

                            <!-- NÚT THAO TÁC THÊM GIỎ / MUA NGAY -->
                            <div class="product-actions-group d-flex flex-wrap gap-3 mb-4">
                                <?php if ($pStock > 0): ?>
                                    <button type="submit"
                                            name="buy_now"
                                            value="0"
                                            class="btn btn-lg btn-outline-primary btn-add-to-cart flex-grow-1">
                                        <i class="bi bi-cart-plus me-2 fs-5"></i>
                                        <span>Thêm vào giỏ hàng</span>
                                    </button>

                                    <button type="submit"
                                            name="buy_now"
                                            value="1"
                                            class="btn btn-lg btn-primary btn-buy-now flex-grow-1">
                                        <i class="bi bi-lightning-fill me-1"></i>
                                        <span>Mua ngay</span>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-lg btn-secondary w-100" disabled>
                                        <i class="bi bi-slash-circle me-2"></i>
                                        <span>Sản phẩm hiện đang tạm hết hàng</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>

                        <!-- CHÍNH SÁCH DỊCH VỤ -->
                        <div class="product-perks-box p-3 rounded-3 bg-light border">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="perk-item d-flex align-items-center gap-2">
                                        <i class="bi bi-truck text-accent fs-4"></i>
                                        <div class="small">
                                            <strong>Miễn phí vận chuyển</strong>
                                            <div class="text-muted">Đơn hàng từ 500.000đ</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="perk-item d-flex align-items-center gap-2">
                                        <i class="bi bi-arrow-repeat text-accent fs-4"></i>
                                        <div class="small">
                                            <strong>Đổi trả 30 ngày</strong>
                                            <div class="text-muted">Nếu không vừa kích thước</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="perk-item d-flex align-items-center gap-2">
                                        <i class="bi bi-patch-check text-accent fs-4"></i>
                                        <div class="small">
                                            <strong>100% Chính hãng</strong>
                                            <div class="text-muted">Cam kết chất lượng cao</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="perk-item d-flex align-items-center gap-2">
                                        <i class="bi bi-headset text-accent fs-4"></i>
                                        <div class="small">
                                            <strong>Hỗ trợ 24/7</strong>
                                            <div class="text-muted">Hotline 1900 0000</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- THÔNG TIN MÔ TẢ CHI TIẾT SẢN PHẨM -->
        <div class="product-description-card sx-card mb-5 p-4">
            <h2 class="fs-4 fw-bold mb-3 pb-2 border-bottom">
                <i class="bi bi-file-text text-accent me-2"></i>Mô tả chi tiết sản phẩm
            </h2>
            <div class="product-description-content lh-lg text-secondary">
                <?php if (!empty($pDesc)): ?>
                    <?= nl2br(htmlspecialchars($pDesc, ENT_QUOTES, 'UTF-8')) ?>
                <?php else: ?>
                    <p>Sản phẩm thể thao chính hãng SPORTIX được may từ chất liệu cao cấp, co giãn và thấm hút mồ hôi tối ưu, đem lại cảm giác thoải mái tối đa cho người mặc trong suốt quá trình vận động và luyện tập thể thao chuyên nghiệp.</p>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top">
                    <h3 class="fs-5 fw-bold mb-2">Đặc điểm nổi bật:</h3>
                    <ul>
                        <li>Chất liệu chuyên dụng cho thể thao, mềm mại, thoáng mát và co giãn đàn hồi cao.</li>
                        <li>Đường may chắc chắn tỉ mỉ, không bai xù hay phai màu sau nhiều lần giặt.</li>
                        <li>Kiểu dáng hiện đại năng động, dễ dàng phối đồ khi đi tập gym, chạy bộ, đá bóng hoặc đi chơi.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- SẢN PHẨM LIÊN QUAN (CÙNG DANH MỤC) -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="related-products-section mb-5">
                <div class="section-header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill mb-2">BỘ SƯU TẬP</span>
                        <h2 class="section-title fs-3 fw-bold mb-0">Sản phẩm cùng danh mục</h2>
                    </div>
                    <a href="<?= BASE_URL ?>?action=products&amp;category=<?= $pCatId ?>" class="btn btn-outline-secondary btn-sm">
                        <span>Xem tất cả</span>
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row g-3 g-lg-4">
                    <?php foreach ($relatedProducts as $product): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <?php
                            $cardBadge = '';
                            require PATH_VIEW . 'partials/product-card.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- JavaScript xử lý chọn biến thể và tăng giảm số lượng mua -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus  = document.getElementById('qtyPlus');
    const variantIdInput = document.getElementById('selectedVariantId');
    const sizeInputs = document.querySelectorAll('.size-pill-input');
    const colorInputs = document.querySelectorAll('.color-pill-input');
    const addButtons = document.querySelectorAll('#addToCartForm button[type="submit"]');
    const stockHint = document.getElementById('variantStockHint');
    const selectedSizeText = document.getElementById('selectedSizeText');
    const selectedColorText = document.getElementById('selectedColorText');
    const variantData = <?= json_encode($variantMap, JSON_UNESCAPED_UNICODE) ?>;
    let maxStock = <?= (int) $pStock ?>;

    const normalize = function (value) {
        return String(value).trim().replace(/\s+/g, ' ');
    };

    const syncVariant = function () {
        const sizeInput = document.querySelector('.size-pill-input:checked:not(:disabled)');
        const colorInput = document.querySelector('.color-pill-input:checked:not(:disabled)');
        const size = sizeInput ? normalize(sizeInput.value) : '';
        const color = colorInput ? normalize(colorInput.value) : '';
        const variant = variantData[color + '|' + size];
        const stock = variant && Number(variant.is_active) === 1
            ? Math.max(0, parseInt(variant.stock_quantity, 10) || 0)
            : 0;

        maxStock = stock;

        if (variantIdInput) {
            variantIdInput.value = stock > 0 ? (variant.variant_id || '') : '';
        }
        if (qtyInput) {
            qtyInput.max = Math.max(1, stock);
            qtyInput.disabled = stock <= 0;
            let val = parseInt(qtyInput.value, 10) || 1;
            if (stock > 0 && val > stock) {
                val = stock;
            }
            if (stock <= 0) {
                val = 1;
            }
            qtyInput.value = val;
        }
        addButtons.forEach(function (button) {
            button.disabled = stock <= 0;
        });
        if (stockHint) {
            stockHint.textContent = stock > 0 ? '(Tối đa ' + stock + ' sản phẩm)' : '(Biến thể này tạm hết hàng)';
        }
        if (selectedSizeText && size !== '') {
            selectedSizeText.textContent = size;
        }
        if (selectedColorText && color !== '') {
            selectedColorText.textContent = color;
        }
    };

    if (qtyMinus && qtyInput) {
        qtyMinus.addEventListener('click', function () {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) {
                qtyInput.value = val - 1;
            }
        });
    }

    if (qtyPlus && qtyInput) {
        qtyPlus.addEventListener('click', function () {
            let val = parseInt(qtyInput.value) || 1;
            if (val < maxStock) {
                qtyInput.value = val + 1;
            }
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('change', function () {
            let val = parseInt(this.value) || 1;
            if (val < 1) val = 1;
            if (val > maxStock) val = maxStock;
            this.value = val;
        });
    }

    sizeInputs.forEach(function (input) {
        input.addEventListener('change', syncVariant);
    });
    colorInputs.forEach(function (input) {
        input.addEventListener('change', syncVariant);
    });
    syncVariant();
});
</script>
