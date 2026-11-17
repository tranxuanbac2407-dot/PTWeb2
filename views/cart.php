<?php
/**
 * views/cart.php
 * Giao diện giỏ hàng khách hàng SPORTIX.
 */

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
}

$freeShippingThreshold = 500000;
$shippingFee = ($subtotal > 0 && $subtotal < $freeShippingThreshold) ? 30000 : 0;
$totalAmount = $subtotal + $shippingFee;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
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
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <div class="cart-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="fs-2 fw-bold mb-1">
                    <i class="bi bi-bag-check text-accent me-2"></i>Giỏ hàng của bạn
                </h1>
                <p class="text-muted mb-0 small">Kiểm tra danh sách sản phẩm trước khi thanh toán</p>
            </div>
            <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2 rounded-pill">
                <?= count($cart) ?> mục sản phẩm
            </span>
        </div>

        <?php if (!empty($cart)): ?>
            <!-- Thanh thông báo Miễn phí vận chuyển -->
            <?php if ($subtotal < $freeShippingThreshold): ?>
                <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-truck text-warning fs-4 me-2"></i>
                        <span>Mua thêm <strong><?= number_format($freeShippingThreshold - $subtotal, 0, ',', '.') ?> đ</strong> để được <strong>MIỄN PHÍ VẬN CHUYỂN</strong> toàn quốc!</span>
                    </div>
                    <a href="<?= BASE_URL ?>?action=products" class="btn btn-sm btn-outline-dark fw-bold">Mua thêm ngay</a>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="bi bi-truck text-success fs-4 me-2"></i>
                    <span>Chúc mừng! Đơn hàng của bạn đủ điều kiện nhận <strong>MIỄN PHÍ GIAO HÀNG</strong> toàn quốc.</span>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Danh sách sản phẩm trong giỏ -->
                <div class="col-12 col-lg-8">
                    <div class="cart-table-card sx-card p-3">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 250px;">Sản phẩm</th>
                                        <th style="width: 130px;" class="text-end">Đơn giá</th>
                                        <th style="width: 170px;" class="text-center">Số lượng</th>
                                        <th style="width: 140px;" class="text-end">Thành tiền</th>
                                        <th style="width: 60px;" class="text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $key => $item): ?>
                                        <?php
                                            $itemPrice = (float) ($item['price'] ?? 0);
                                            $itemQty   = (int) ($item['quantity'] ?? 1);
                                            $itemSubtotal = $itemPrice * $itemQty;
                                            $itemImage = (string) ($item['image'] ?? '');
                                            $itemImgSrc = get_product_image_url($itemImage);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="cart-item-thumb" style="width: 64px; height: 64px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0;">
                                                        <?php if (!empty($itemImgSrc)): ?>
                                                            <img src="<?= htmlspecialchars($itemImgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                                 alt="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                                <i class="bi bi-image"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= (int) ($item['product_id'] ?? 0) ?>"
                                                           class="fw-bold text-dark text-decoration-none d-block mb-1">
                                                            <?= htmlspecialchars($item['name'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                                        </a>
                                                        <div class="d-flex flex-wrap gap-2 small text-muted">
                                                            <?php if (!empty($item['size'])): ?>
                                                                <span class="badge bg-light text-dark border">Size: <?= htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8') ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['color'])): ?>
                                                                <span class="badge bg-light text-dark border">Màu: <?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Đơn giá -->
                                            <td class="text-end fw-semibold">
                                                <?= number_format($itemPrice, 0, ',', '.') ?> đ
                                            </td>

                                            <!-- Tăng giảm số lượng -->
                                            <td class="text-center">
                                                <div class="d-inline-flex align-items-center border rounded-pill overflow-hidden bg-light" style="padding: 2px;">
                                                    <!-- Nút giảm -->
                                                    <a href="<?= BASE_URL ?>?action=cart-qty&amp;key=<?= urlencode((string) $key) ?>&amp;delta=-1"
                                                       class="btn btn-sm btn-link text-dark px-2 text-decoration-none"
                                                       title="Giảm số lượng">
                                                        <i class="bi bi-dash"></i>
                                                    </a>

                                                    <!-- Form nhập số lượng trực tiếp -->
                                                    <form action="<?= BASE_URL ?>?action=cart-update" method="post" class="m-0 p-0">
                                                        <input type="hidden" name="cart_key" value="<?= htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="number"
                                                               name="quantity"
                                                               value="<?= $itemQty ?>"
                                                               min="1"
                                                               max="99"
                                                               class="form-control form-control-sm text-center fw-bold border-0 bg-transparent"
                                                               style="width: 50px; box-shadow: none;"
                                                               onchange="this.form.submit()">
                                                    </form>

                                                    <!-- Nút tăng -->
                                                    <a href="<?= BASE_URL ?>?action=cart-qty&amp;key=<?= urlencode((string) $key) ?>&amp;delta=1"
                                                       class="btn btn-sm btn-link text-dark px-2 text-decoration-none"
                                                       title="Tăng số lượng">
                                                        <i class="bi bi-plus"></i>
                                                    </a>
                                                </div>
                                            </td>

                                            <!-- Thành tiền -->
                                            <td class="text-end fw-bold text-accent">
                                                <?= number_format($itemSubtotal, 0, ',', '.') ?> đ
                                            </td>

                                            <!-- Nút xóa món -->
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>?action=cart-remove&amp;key=<?= urlencode((string) $key) ?>"
                                                   class="btn btn-sm btn-outline-danger border-0"
                                                   title="Xóa món này khỏi giỏ"
                                                   onclick="return confirm('Bạn có chắc muốn xóa món này khỏi giỏ hàng?');">
                                                    <i class="bi bi-trash fs-6"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Hàng thao tác giỏ hàng -->
                        <div class="cart-actions-row mt-3 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="<?= BASE_URL ?>?action=products" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua sắm
                            </a>
                            <a href="<?= BASE_URL ?>?action=cart-clear"
                               class="btn btn-outline-danger"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa TOÀN BỘ sản phẩm trong giỏ hàng không?');">
                                <i class="bi bi-trash3 me-1"></i> Xóa toàn bộ giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tóm tắt đơn hàng & Tiến hành đặt hàng -->
                <div class="col-12 col-lg-4">
                    <div class="order-summary-card sx-card p-4">
                        <h3 class="fs-5 fw-bold mb-3 pb-2 border-bottom">
                            <i class="bi bi-receipt me-2 text-accent"></i>Tóm tắt đơn hàng
                        </h3>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính (<?= count($cart) ?> món):</span>
                            <span class="fw-bold"><?= number_format($subtotal, 0, ',', '.') ?> đ</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="<?= $shippingFee === 0 ? 'text-success fw-bold' : 'fw-semibold' ?>">
                                <?= $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.') . ' đ' ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fs-5 fw-bold">Tổng thanh toán:</span>
                            <span class="fs-4 fw-bold text-accent">
                                <?= number_format($totalAmount, 0, ',', '.') ?> đ
                            </span>
                        </div>

                        <!-- Nút tiến hành đặt hàng - chuyển hướng sang trang checkout -->
                        <a href="<?= BASE_URL ?>?action=checkout" class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                            <i class="bi bi-credit-card me-2"></i> Tiến hành đặt hàng
                        </a>

                        <div class="mt-3 text-center text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i> Cam kết 100% hàng thể thao chính hãng
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-empty-box sx-card text-center py-5 px-3">
                <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                <h2 class="fs-4 fw-bold mt-3 mb-2">Giỏ hàng của bạn đang trống</h2>
                <p class="text-muted mb-4">Hãy khám phá các trang phục & phụ kiện thể thao chính hãng SPORTIX ngay nhé!</p>
                <a href="<?= BASE_URL ?>?action=products" class="btn btn-primary btn-lg">
                    <i class="bi bi-bag-plus me-2"></i> Khám phá sản phẩm ngay
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>
