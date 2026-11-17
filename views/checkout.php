<?php
/**
 * views/checkout.php
 * Giao diện đặt hàng và thanh toán SPORTIX.
 */

$cart = $_SESSION['cart'] ?? [];
$currentUser = $_SESSION['user'] ?? null;

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
}

$freeShippingThreshold = 500000;
$shippingFee = ($subtotal > 0 && $subtotal < $freeShippingThreshold) ? 30000 : 0;
$totalAmount = $subtotal + $shippingFee;

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Tự động điền dữ liệu nếu khách hàng đã đăng nhập
$defaultName    = $currentUser['fullname'] ?? $currentUser['username'] ?? '';
$defaultPhone   = $currentUser['phone'] ?? '';
$defaultAddress = $currentUser['address'] ?? '';
?>

<div class="checkout-page py-4">
    <div class="container">

        <!-- Breadcrumbs -->
        <nav class="sx-breadcrumb mb-4" aria-label="Đường dẫn trang">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=cart">Giỏ hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Thanh toán &amp; Đặt hàng</li>
            </ol>
        </nav>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <div class="checkout-header mb-4">
            <h1 class="fs-2 fw-bold mb-1">
                <i class="bi bi-credit-card-2-front text-accent me-2"></i>Thanh toán &amp; Đặt hàng
            </h1>
            <p class="text-muted mb-0 small">Vui lòng kiểm tra kỹ thông tin nhận hàng trước khi xác nhận đơn</p>
        </div>

        <form action="<?= BASE_URL ?>?action=place-order" method="post" id="checkout-form">
            <div class="row g-4">

                <!-- Cột trái: Thông tin nhận hàng & Phương thức thanh toán -->
                <div class="col-12 col-lg-7">
                    <!-- Khối thông tin người nhận -->
                    <div class="sx-card p-4 mb-4">
                        <h2 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-accent me-2"></i>1. Thông tin giao hàng
                        </h2>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="receiver_name" class="form-label fw-semibold">Họ và tên người nhận <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="receiver_name"
                                       name="receiver_name"
                                       placeholder="Ví dụ: Nguyễn Văn An"
                                       value="<?= htmlspecialchars($_POST['receiver_name'] ?? $defaultName, ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-semibold">Số điện thoại nhận hàng <span class="text-danger">*</span></label>
                                <input type="tel"
                                       class="form-control"
                                       id="phone"
                                       name="phone"
                                       placeholder="Ví dụ: 0912345678"
                                       pattern="0[0-9]{9,10}"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? $defaultPhone, ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                                <div class="form-text">Nhập số điện thoại 10 chữ số để shipper liên hệ giao hàng.</div>
                            </div>

                            <div class="col-12">
                                <label for="shipping_address" class="form-label fw-semibold">Địa chỉ nhận hàng chi tiết <span class="text-danger">*</span></label>
                                <textarea class="form-control"
                                          id="shipping_address"
                                          name="shipping_address"
                                          rows="3"
                                          placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                                          required><?= htmlspecialchars($_POST['shipping_address'] ?? $defaultAddress, ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="note" class="form-label fw-semibold">Ghi chú đơn hàng (tùy chọn)</label>
                                <input type="text"
                                       class="form-control"
                                       id="note"
                                       name="note"
                                       placeholder="Ví dụ: Giao vào giờ hành chính, gọi trước khi đến..."
                                       value="<?= htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Khối phương thức thanh toán -->
                    <div class="sx-card p-4">
                        <h2 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center">
                            <i class="bi bi-wallet2 text-accent me-2"></i>2. Phương thức thanh toán
                        </h2>

                        <div class="payment-methods-list d-flex flex-column gap-3">
                            <!-- COD -->
                            <label class="payment-method-item d-flex align-items-center p-3 border rounded-3 cursor-pointer">
                                <input type="radio" name="payment_method" value="cod" class="form-check-input me-3" checked>
                                <div class="flex-grow-1">
                                    <div class="fw-bold d-flex align-items-center gap-2">
                                        <i class="bi bi-cash-coin text-success fs-5"></i>
                                        Thanh toán khi nhận hàng (COD)
                                    </div>
                                    <div class="small text-muted mt-1">Quý khách nhận hàng, kiểm tra sản phẩm rồi thanh toán tiền mặt cho nhân viên giao hàng.</div>
                                </div>
                            </label>

                            <!-- Chuyển khoản ngân hàng -->
                            <label class="payment-method-item d-flex align-items-center p-3 border rounded-3 cursor-pointer">
                                <input type="radio" name="payment_method" value="banking" class="form-check-input me-3">
                                <div class="flex-grow-1">
                                    <div class="fw-bold d-flex align-items-center gap-2">
                                        <i class="bi bi-bank text-primary fs-5"></i>
                                        Chuyển khoản ngân hàng (QR Code 24/7)
                                    </div>
                                    <div class="small text-muted mt-1">Chuyển khoản nhanh qua quét mã VietQR tự động xác nhận đơn trong 1 phút.</div>
                                </div>
                            </label>

                            <!-- MoMo -->
                            <label class="payment-method-item d-flex align-items-center p-3 border rounded-3 cursor-pointer">
                                <input type="radio" name="payment_method" value="momo" class="form-check-input me-3">
                                <div class="flex-grow-1">
                                    <div class="fw-bold d-flex align-items-center gap-2">
                                        <i class="bi bi-phone text-danger fs-5"></i>
                                        Ví điện tử MoMo
                                    </div>
                                    <div class="small text-muted mt-1">Thanh toán an toàn, tức thì qua ứng dụng ví MoMo.</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Tóm tắt đơn hàng & Nút đặt hàng -->
                <div class="col-12 col-lg-5">
                    <div class="order-summary-card sx-card p-4 position-sticky" style="top: 20px;">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h2 class="fs-5 fw-bold mb-0">Đơn hàng của bạn</h2>
                            <a href="<?= BASE_URL ?>?action=cart" class="small text-decoration-none text-primary">
                                <i class="bi bi-pencil-square me-1"></i>Sửa giỏ hàng
                            </a>
                        </div>

                        <!-- Danh sách món hàng thu gọn -->
                        <div class="checkout-items-list mb-3" style="max-height: 280px; overflow-y: auto;">
                            <?php foreach ($cart as $item): ?>
                                <?php
                                    $itemPrice    = (float) ($item['price'] ?? 0);
                                    $itemQty      = (int) ($item['quantity'] ?? 1);
                                    $itemSubtotal = $itemPrice * $itemQty;
                                    $itemImage    = (string) ($item['image'] ?? '');
                                    $itemImgSrc   = get_product_image_url($itemImage);
                                ?>
                                <div class="checkout-item d-flex align-items-center gap-3 py-2 border-bottom">
                                    <div style="width: 54px; height: 54px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0;">
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
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="text-truncate fw-semibold small text-dark" title="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($item['name'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mt-1 small text-muted">
                                            <?php if (!empty($item['size'])): ?>
                                                <span class="badge bg-light text-dark border py-0 px-1" style="font-size: 0.72rem;">Size: <?= htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['color'])): ?>
                                                <span class="badge bg-light text-dark border py-0 px-1" style="font-size: 0.72rem;">Màu: <?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                            <span class="text-secondary ms-1" style="font-size: 0.75rem;">x<?= $itemQty ?></span>
                                        </div>
                                    </div>
                                    <div class="text-end fw-bold small text-accent text-nowrap">
                                        <?= number_format($itemSubtotal, 0, ',', '.') ?> đ
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tính toán chi phí -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold"><?= number_format($subtotal, 0, ',', '.') ?> đ</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Phí giao hàng:</span>
                            <span class="<?= $shippingFee === 0 ? 'text-success fw-bold' : 'fw-semibold' ?>">
                                <?= $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.') . ' đ' ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fs-5 fw-bold">Tổng thanh toán:</span>
                            <span class="fs-3 fw-bold text-accent">
                                <?= number_format($totalAmount, 0, ',', '.') ?> đ
                            </span>
                        </div>

                        <button type="submit" id="btn-place-order" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                            <i class="bi bi-check2-circle me-2 fs-5"></i>
                            <span id="btn-place-order-text">Xác nhận đặt hàng</span>
                        </button>

                        <div class="mt-3 text-center text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i> Thông tin của bạn được bảo mật tuyệt đối
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('btn-place-order');
    const btnText = document.getElementById('btn-place-order-text');

    if (checkoutForm && submitBtn) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!checkoutForm.checkValidity()) {
                return; // Để trình duyệt kích hoạt validation HTML5 nếu trường bắt buộc bị thiếu
            }

            // Vô hiệu hóa nút và hiển thị spinner để chống double-click gây trùng đơn
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
            if (btnText) {
                btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang tự động tạo đơn hàng...';
            }
        });
    }
});
</script>
