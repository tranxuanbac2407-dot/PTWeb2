<?php
/**
 * views/cart.php
 * Giao diện giỏ hàng khách hàng SPORTIX.
 * Hiển thị đầy đủ danh sách sản phẩm: ảnh, tên, giá, size, màu sắc, số lượng.
 */

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
$totalQuantity = 0;

foreach ($cart as $item) {
    $qty = (int) ($item['quantity'] ?? 1);
    $subtotal += ((float) ($item['price'] ?? 0)) * $qty;
    $totalQuantity += $qty;
}

$freeShippingThreshold = 500000;
$shippingFee = ($subtotal > 0 && $subtotal < $freeShippingThreshold) ? 30000 : 0;
$totalAmount = $subtotal + $shippingFee;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/**
 * Helper ánh xạ tên màu sắc sang mã HEX hiển thị chấm màu (color indicator swatch).
 */
if (!function_exists('get_cart_color_hex')) {
    function get_cart_color_hex($colorName) {
        $c = mb_strtolower(trim($colorName));
        if (strpos($c, 'đen') !== false || strpos($c, 'black') !== false) return '#111827';
        if (strpos($c, 'trắng') !== false || strpos($c, 'white') !== false) return '#ffffff';
        if (strpos($c, 'đỏ') !== false || strpos($c, 'red') !== false) return '#ef4444';
        if (strpos($c, 'navy') !== false || strpos($c, 'xanh than') !== false) return '#1e3a8a';
        if (strpos($c, 'xanh dương') !== false || strpos($c, 'xanh lam') !== false || strpos($c, 'blue') !== false) return '#2563eb';
        if (strpos($c, 'xanh lá') !== false || strpos($c, 'green') !== false) return '#10b981';
        if (strpos($c, 'vàng') !== false || strpos($c, 'yellow') !== false) return '#f59e0b';
        if (strpos($c, 'cam') !== false || strpos($c, 'orange') !== false) return '#f97316';
        if (strpos($c, 'xám') !== false || strpos($c, 'ghi') !== false || strpos($c, 'gray') !== false || strpos($c, 'grey') !== false) return '#6b7280';
        if (strpos($c, 'hồng') !== false || strpos($c, 'pink') !== false) return '#ec4899';
        if (strpos($c, 'tím') !== false || strpos($c, 'purple') !== false) return '#8b5cf6';
        return '#0d1b2a';
    }
}
?>

<div class="cart-page py-4">
    <div class="container">

        <!-- Breadcrumbs -->
        <nav class="sx-breadcrumb mb-4" aria-label="Đường dẫn trang">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
            </ol>
        </nav>

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success d-flex align-items-center mb-4 p-3 rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-4 text-success"></i>
                <div class="fw-semibold"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4 p-3 rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4 text-danger"></i>
                <div class="fw-semibold"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Tiêu đề giỏ hàng & thống kê số lượng -->
        <div class="cart-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="fs-2 fw-bold mb-1">
                    <i class="bi bi-bag-check-fill text-accent me-2"></i>Giỏ hàng của bạn
                </h1>
                <p class="text-muted mb-0 small">
                    Bạn đang có <strong><?= $totalQuantity ?></strong> sản phẩm (<?= count($cart) ?> mục) trong giỏ hàng
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white fs-6 px-3 py-2 rounded-pill">
                    <i class="bi bi-box2 me-1"></i> <?= $totalQuantity ?> món đồ
                </span>
                <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2 rounded-pill">
                    <?= count($cart) ?> dòng sản phẩm
                </span>
            </div>
        </div>

        <?php if (!empty($cart)): ?>
            <!-- Thanh thông báo Miễn phí vận chuyển -->
            <?php if ($subtotal < $freeShippingThreshold): ?>
                <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2 rounded-3 border-warning-subtle">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-truck text-warning fs-3 me-3"></i>
                        <div>
                            <span>Mua thêm <strong><?= number_format($freeShippingThreshold - $subtotal, 0, ',', '.') ?> đ</strong> để nhận ưu đãi <strong>MIỄN PHÍ VẬN CHUYỂN</strong> toàn quốc!</span>
                            <div class="progress mt-2" style="height: 6px; width: 220px; background-color: #fde68a;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= min(100, round(($subtotal / $freeShippingThreshold) * 100)) ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>?action=products" class="btn btn-sm btn-dark fw-bold px-3">
                        <i class="bi bi-plus-lg me-1"></i> Mua thêm ngay
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center mb-4 rounded-3 border-success-subtle">
                    <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                    <div>
                        <strong class="d-block">Tuyệt vời! Bạn đủ điều kiện nhận MIỄN PHÍ VẬN CHUYỂN!</strong>
                        <small class="text-muted">Đơn hàng của bạn đạt giá trị trên <?= number_format($freeShippingThreshold, 0, ',', '.') ?> đ.</small>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Danh sách sản phẩm trong giỏ -->
                <div class="col-12 col-lg-8">
                    <div class="cart-table-card sx-card p-3 shadow-sm">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 270px;">Sản phẩm</th>
                                        <th style="width: 125px;" class="text-end">Đơn giá</th>
                                        <th style="width: 160px;" class="text-center">Số lượng</th>
                                        <th style="width: 135px;" class="text-end">Thành tiền</th>
                                        <th style="width: 50px;" class="text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $key => $item): ?>
                                        <?php
                                            $productId    = (int) ($item['product_id'] ?? 0);
                                            $productName  = (string) ($item['name'] ?? 'Sản phẩm thể thao');
                                            $itemPrice    = (float) ($item['price'] ?? 0);
                                            $itemQty      = (int) ($item['quantity'] ?? 1);
                                            $itemSubtotal = $itemPrice * $itemQty;
                                            $itemImage    = (string) ($item['image'] ?? '');
                                            $itemImgSrc   = get_product_image_url($itemImage);
                                            $itemSize     = trim((string) ($item['size'] ?? ''));
                                            $itemColor    = trim((string) ($item['color'] ?? ''));
                                            $categoryName = (string) ($item['category_name'] ?? '');
                                            $colorHex     = !empty($itemColor) ? get_cart_color_hex($itemColor) : '#111827';
                                        ?>
                                        <tr class="cart-row">
                                            <!-- Cột thông tin sản phẩm: Ảnh + Tên + Size + Màu sắc -->
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <!-- Hình ảnh sản phẩm -->
                                                    <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= $productId ?>"
                                                       class="cart-item-thumb d-block flex-shrink-0"
                                                       title="Xem chi tiết: <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>">
                                                        <?php if (!empty($itemImgSrc)): ?>
                                                            <img src="<?= htmlspecialchars($itemImgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                                 alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                                                 loading="lazy"
                                                                 class="cart-thumb-img">
                                                        <?php else: ?>
                                                            <div class="cart-thumb-placeholder d-flex align-items-center justify-content-center text-muted">
                                                                <i class="bi bi-image fs-4"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </a>

                                                    <!-- Tên sản phẩm & Thuộc tính phân loại (Size, Màu sắc) -->
                                                    <div class="cart-item-info">
                                                        <?php if (!empty($categoryName)): ?>
                                                            <div class="text-muted small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                                                <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Tên sản phẩm -->
                                                        <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= $productId ?>"
                                                           class="cart-product-title fw-bold text-dark text-decoration-none d-block">
                                                            <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>
                                                        </a>

                                                        <!-- Hiển thị Size và Màu sắc trực quan -->
                                                        <div class="cart-attributes-wrap d-flex flex-wrap align-items-center gap-2 mt-2">
                                                            <!-- Thuộc tính Size (Kích cỡ) -->
                                                            <?php if (!empty($itemSize)): ?>
                                                                <span class="badge-cart-variant badge-size" title="Kích cỡ đã chọn">
                                                                    <i class="bi bi-rulers text-secondary me-1"></i>Size: <strong><?= htmlspecialchars($itemSize, ENT_QUOTES, 'UTF-8') ?></strong>
                                                                </span>
                                                            <?php endif; ?>

                                                            <!-- Thuộc tính Màu sắc (Color với dot indicator) -->
                                                            <?php if (!empty($itemColor)): ?>
                                                                <span class="badge-cart-variant badge-color" title="Màu sắc đã chọn">
                                                                    <span class="color-dot-indicator" style="background-color: <?= $colorHex ?>; <?= $colorHex === '#ffffff' ? 'border: 1px solid #cbd5e1;' : '' ?>"></span>
                                                                    Màu: <strong><?= htmlspecialchars($itemColor, ENT_QUOTES, 'UTF-8') ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Đơn giá sản phẩm -->
                                            <td class="text-end">
                                                <span class="cart-unit-price fw-semibold text-dark">
                                                    <?= number_format($itemPrice, 0, ',', '.') ?> đ
                                                </span>
                                            </td>

                                            <!-- Hiển thị & Cập nhật số lượng sản phẩm -->
                                            <td class="text-center">
                                                <div class="cart-qty-stepper d-inline-flex align-items-center border rounded-pill overflow-hidden bg-light">
                                                    <!-- Nút giảm số lượng (-) -->
                                                    <a href="<?= BASE_URL ?>?action=cart-qty&amp;key=<?= urlencode((string) $key) ?>&amp;delta=-1"
                                                       class="cart-qty-btn text-dark text-decoration-none px-2 py-1"
                                                       title="Giảm 1 sản phẩm">
                                                        <i class="bi bi-dash fs-6"></i>
                                                    </a>

                                                    <!-- Ô hiển thị & nhập số lượng -->
                                                    <form action="<?= BASE_URL ?>?action=cart-update" method="post" class="m-0 p-0">
                                                        <input type="hidden" name="cart_key" value="<?= htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="number"
                                                               name="quantity"
                                                               value="<?= $itemQty ?>"
                                                               min="1"
                                                               max="99"
                                                               class="cart-qty-input form-control form-control-sm text-center fw-bold border-0 bg-transparent p-0"
                                                               style="width: 48px; box-shadow: none;"
                                                               title="Số lượng sản phẩm"
                                                               onchange="this.form.submit()">
                                                    </form>

                                                    <!-- Nút tăng số lượng (+) -->
                                                    <a href="<?= BASE_URL ?>?action=cart-qty&amp;key=<?= urlencode((string) $key) ?>&amp;delta=1"
                                                       class="cart-qty-btn text-dark text-decoration-none px-2 py-1"
                                                       title="Tăng 1 sản phẩm">
                                                        <i class="bi bi-plus fs-6"></i>
                                                    </a>
                                                </div>
                                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                    SL: <strong><?= $itemQty ?></strong> cái
                                                </div>
                                            </td>

                                            <!-- Thành tiền = Đơn giá x Số lượng -->
                                            <td class="text-end">
                                                <span class="cart-subtotal fw-bold text-accent fs-6">
                                                    <?= number_format($itemSubtotal, 0, ',', '.') ?> đ
                                                </span>
                                            </td>

                                            <!-- Nút xóa sản phẩm khỏi giỏ -->
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>?action=cart-remove&amp;key=<?= urlencode((string) $key) ?>"
                                                   class="btn btn-sm btn-outline-danger border-0 rounded-circle"
                                                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"
                                                   title="Xóa sản phẩm này khỏi giỏ hàng"
                                                   onclick="return confirm('Bạn có chắc muốn xóa &quot;<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>&quot; khỏi giỏ hàng?');">
                                                    <i class="bi bi-trash3"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Hàng thao tác dưới bảng -->
                        <div class="cart-actions-row mt-3 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="<?= BASE_URL ?>?action=products" class="btn btn-outline-primary fw-semibold">
                                <i class="bi bi-arrow-left me-1"></i> Tiếp tục chọn thêm sản phẩm
                            </a>
                            <a href="<?= BASE_URL ?>?action=cart-clear"
                               class="btn btn-outline-danger fw-semibold"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa TOÀN BỘ <?= count($cart) ?> sản phẩm trong giỏ hàng không?');">
                                <i class="bi bi-trash3 me-1"></i> Xóa toàn bộ giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cột tóm tắt đơn hàng & Tiến hành đặt hàng -->
                <div class="col-12 col-lg-4">
                    <div class="order-summary-card sx-card p-4 shadow-sm position-sticky" style="top: 20px;">
                        <h3 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-receipt me-2 text-accent"></i>Tóm tắt đơn hàng</span>
                            <span class="badge bg-light text-dark border small fw-normal"><?= $totalQuantity ?> sản phẩm</span>
                        </h3>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tổng số lượng:</span>
                            <span class="fw-bold"><?= $totalQuantity ?> sản phẩm</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính (<?= count($cart) ?> mục):</span>
                            <span class="fw-bold"><?= number_format($subtotal, 0, ',', '.') ?> đ</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="<?= $shippingFee === 0 ? 'text-success fw-bold' : 'fw-semibold' ?>">
                                <?= $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.') . ' đ' ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <div>
                                <span class="fs-5 fw-bold d-block">Tổng thanh toán:</span>
                                <small class="text-muted">(Đã bao gồm VAT nếu có)</small>
                            </div>
                            <span class="fs-3 fw-bold text-accent">
                                <?= number_format($totalAmount, 0, ',', '.') ?> đ
                            </span>
                        </div>

                        <!-- Nút tiến hành đặt hàng - chuyển hướng sang trang checkout -->
                        <a href="<?= BASE_URL ?>?action=checkout" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span>Tiến hành đặt hàng (<?= $totalQuantity ?>)</span>
                        </a>

                        <div class="summary-perks mt-4 pt-3 border-top small text-muted d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <span>Cam kết 100% hàng thể thao chính hãng SPORTIX</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-counterclockwise text-primary fs-5"></i>
                                <span>Đổi hàng miễn phí trong vòng 7 ngày nếu không vừa size</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-dark fs-5"></i>
                                <span>Giao hàng hỏa tốc toàn quốc từ 1 - 3 ngày</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-empty-box sx-card text-center py-5 px-3 shadow-sm">
                <i class="bi bi-cart-x text-muted" style="font-size: 4.5rem;"></i>
                <h2 class="fs-4 fw-bold mt-3 mb-2">Giỏ hàng của bạn đang trống</h2>
                <p class="text-muted mb-4">Bạn chưa chọn sản phẩm thể thao nào vào giỏ hàng. Hãy khám phá bộ sưu tập mới nhất ngay nhé!</p>
                <a href="<?= BASE_URL ?>?action=products" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
                    <i class="bi bi-bag-plus me-2"></i> Khám phá sản phẩm SPORTIX
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>
