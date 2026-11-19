<?php
/**
 * views/order-success.php
 * Giao diện thông báo Đặt hàng thành công SPORTIX.
 * Hiển thị chi tiết biên nhận ngay sau khi hệ thống tự động tạo đơn hàng.
 */

$order = $order ?? [];
$items = $items ?? [];

$ordId     = (int) ($order['order_id'] ?? 0);
$total     = (float) ($order['total_amount'] ?? 0);
$status    = $order['order_status'] ?? 'pending';
$orderDate = !empty($order['order_date']) ? date('d/m/Y - H:i:s', strtotime($order['order_date'])) : date('d/m/Y - H:i:s');

function get_order_success_payment_name($method) {
    switch ($method) {
        case 'banking': return 'Chuyển khoản ngân hàng (VietQR 24/7)';
        case 'momo':    return 'Ví điện tử MoMo';
        case 'cod':
        default:        return 'Thanh toán tiền mặt khi nhận hàng (COD)';
    }
}

function get_order_success_status_badge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill"><i class="bi bi-hourglass-split me-1"></i>Chờ xác nhận</span>';
        case 'processing':
            return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill"><i class="bi bi-box-seam me-1"></i>Đang xử lý</span>';
        case 'shipping':
            return '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill"><i class="bi bi-truck me-1"></i>Đang giao hàng</span>';
        case 'completed':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Đã hoàn thành</span>';
        case 'cancelled':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>';
        default:
            return '<span class="badge bg-light text-dark border px-2 py-1 rounded-pill"><i class="bi bi-question-circle me-1"></i>Không xác định</span>';
    }
}
$csrfToken = $csrfToken ?? '';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$paymentMethod = $order['payment_method'] ?? 'cod';
$paymentStatus = $order['payment_status'] ?? 'unpaid';
$qrUrl = function_exists('get_vietqr_url') ? get_vietqr_url($total, 'SPORTIX DH' . $ordId) : '';
?>

<div class="order-success-page py-5">
    <div class="container">

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success d-flex align-items-center mb-4 p-3 shadow-sm rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-3 fs-3 text-success"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Thao tác thành công!</h5>
                    <p class="mb-0"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4 p-3 shadow-sm rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-3 text-danger"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Đã xảy ra sự cố!</h5>
                    <p class="mb-0"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Khối thông báo thành công trung tâm -->
        <div class="text-center mb-5">
            <div class="success-icon-wrapper mb-3 d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle shadow-sm" style="width: 88px; height: 88px;">
                <i class="bi bi-check-lg" style="font-size: 3.5rem;"></i>
            </div>
            <h1 class="fs-2 fw-bold text-dark mb-2">ĐẶT HÀNG THÀNH CÔNG!</h1>
            <p class="text-muted fs-6 mb-2">
                Cảm ơn bạn đã tin tưởng mua sắm tại <strong>SPORTIX</strong>. Hệ thống đã tự động tạo đơn hàng cho bạn!
            </p>
            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-light border rounded-pill">
                <span class="text-muted small">Mã đơn hàng:</span>
                <span class="fs-5 fw-bold text-accent">#<?= $ordId ?></span>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Cột trái: Thông tin nhận hàng & Tiến trình -->
            <div class="col-12 col-lg-5">
                <!-- Thông tin đơn hàng tóm tắt -->
                <div class="sx-card p-4 mb-4 shadow-sm">
                    <h2 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-accent me-2"></i>Thông tin đơn hàng
                    </h2>

                    <div class="d-flex flex-column gap-3 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Mã đơn hàng:</span>
                            <span class="fw-bold text-dark">#<?= $ordId ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Thời gian đặt:</span>
                            <span class="fw-semibold text-dark"><?= $orderDate ?></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Trạng thái:</span>
                            <?= get_order_success_status_badge($status) ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Phương thức thanh toán:</span>
                            <span class="fw-semibold text-dark text-end"><?= get_order_success_payment_name($order['payment_method'] ?? 'cod') ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($paymentMethod === 'banking' || $paymentMethod === 'momo'): ?>
                    <!-- Khối mã QR thanh toán Online VietQR -->
                    <div class="sx-card p-4 mb-4 shadow-sm border border-primary border-opacity-25" id="qr-payment-card" style="background: linear-gradient(to bottom, #f0fdf4, #ffffff);">
                        <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                            <h2 class="fs-5 fw-bold text-primary mb-0 d-flex align-items-center">
                                <i class="bi bi-qr-code-scan me-2 fs-4"></i>Mã QR thanh toán Online
                            </h2>
                            <?php if ($paymentStatus === 'paid'): ?>
                                <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Đã thanh toán</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Chờ chuyển khoản</span>
                            <?php endif; ?>
                        </div>

                        <!-- Ảnh QR code VietQR -->
                        <div class="text-center mb-3">
                            <div class="d-inline-block p-2 bg-white rounded-3 shadow-sm border">
                                <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Mã QR thanh toán VietQR đơn hàng #<?= $ordId ?>"
                                     class="img-fluid rounded"
                                     style="max-width: 250px; height: auto;"
                                     loading="lazy">
                            </div>
                            <div class="small text-muted mt-2">
                                <i class="bi bi-phone me-1"></i>Quét mã bằng bất kỳ App Ngân hàng hoặc Ví MoMo
                            </div>
                        </div>

                        <!-- Thông tin chuyển khoản chi tiết kèm nút sao chép -->
                        <div class="bg-white p-3 rounded-3 border small d-flex flex-column gap-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Ngân hàng:</span>
                                <strong class="text-dark"><?= defined('BANK_NAME') ? BANK_NAME : 'MBBank' ?></strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Chủ tài khoản:</span>
                                <strong class="text-dark"><?= defined('BANK_ACCOUNT_NAME') ? BANK_ACCOUNT_NAME : 'SPORTIX STORE' ?></strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Số tài khoản:</span>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="text-dark fs-6" id="copy-bank-acc"><?= defined('BANK_ACCOUNT_NO') ? BANK_ACCOUNT_NO : '0987654321' ?></strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 sx-copy-btn" onclick="copyToClipboard('<?= defined('BANK_ACCOUNT_NO') ? BANK_ACCOUNT_NO : '0987654321' ?>', this)" title="Sao chép số tài khoản">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Số tiền:</span>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="text-accent fs-6"><?= number_format($total, 0, ',', '.') ?> đ</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 sx-copy-btn" onclick="copyToClipboard('<?= (int) round($total) ?>', this)" title="Sao chép số tiền">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Nội dung CK:</span>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="text-primary fs-6" id="copy-memo">SPORTIX DH<?= $ordId ?></strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 sx-copy-btn" onclick="copyToClipboard('SPORTIX DH<?= $ordId ?>', this)" title="Sao chép nội dung">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if ($paymentStatus !== 'paid'): ?>
                            <form action="<?= BASE_URL ?>?action=confirm-payment" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $ordId ?>">
                                <input type="hidden" name="return_to" value="success">
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                                    <i class="bi bi-check2-circle me-2 fs-5"></i>
                                    <span>Tôi đã chuyển khoản xong</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success py-2 px-3 mb-0 text-center small fw-semibold">
                                <i class="bi bi-patch-check-fill me-1"></i> Bạn đã xác nhận chuyển khoản. SPORTIX sẽ xác thực và đóng gói đơn sớm!
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Thông tin người nhận hàng -->
                <div class="sx-card p-4 shadow-sm">
                    <h2 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-accent me-2"></i>Địa chỉ nhận hàng
                    </h2>

                    <?php
                        $receiverRaw = $order['receiver_name'] ?? '';
                        $phoneRaw = $order['phone'] ?? '';
                        $addressRaw = $order['shipping_address'] ?? '';
                        $receiverName = is_scalar($receiverRaw) ? trim((string) $receiverRaw) : '';
                        $receiverPhone = is_scalar($phoneRaw) ? trim((string) $phoneRaw) : '';
                        $shippingAddress = is_scalar($addressRaw) ? trim((string) $addressRaw) : '';
                        $emptyValue = 'Chưa cập nhật';
                    ?>

                    <div class="small d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-person-fill text-accent flex-shrink-0 mt-1" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <div class="text-muted">Người nhận</div>
                                <div class="fw-semibold text-dark text-break"><?= htmlspecialchars($receiverName !== '' ? $receiverName : $emptyValue, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-telephone-fill text-accent flex-shrink-0 mt-1" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <div class="text-muted">Số điện thoại</div>
                                <div class="fw-semibold text-dark text-break"><?= htmlspecialchars($receiverPhone !== '' ? $receiverPhone : $emptyValue, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-house-door-fill text-accent flex-shrink-0 mt-1" aria-hidden="true"></i>
                            <div class="min-w-0">
                                <div class="text-muted">Địa chỉ giao hàng</div>
                                <div class="fw-semibold text-dark text-break"><?= htmlspecialchars($shippingAddress !== '' ? $shippingAddress : $emptyValue, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Danh sách các món hàng đã đặt -->
            <div class="col-12 col-lg-7">
                <div class="sx-card p-4 shadow-sm">
                    <h2 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-bag-check-fill text-accent me-2"></i>Chi tiết sản phẩm đã đặt</span>
                        <span class="badge bg-light text-dark border fw-normal small"><?= count($items) ?> món</span>
                    </h2>

                    <div class="order-success-items-list mb-3">
                        <?php foreach ($items as $item): ?>
                            <?php
                                $pName    = $item['product_name'] ?? 'Sản phẩm thể thao';
                                $pPrice   = (float) ($item['unit_price'] ?? 0);
                                $pQty     = (int) ($item['quantity'] ?? 1);
                                $pSub     = (float) ($item['subtotal'] ?? ($pPrice * $pQty));
                                $pImg     = (string) ($item['image'] ?? '');
                                $pImgSrc  = get_product_image_url($pImg);
                            ?>
                            <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                                <div style="width: 56px; height: 56px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <?php if (!empty($pImgSrc)): ?>
                                        <img src="<?= htmlspecialchars($pImgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-dark text-truncate small" title="<?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-1 small text-muted" style="font-size: 0.75rem;">
                                        <?php if (!empty($item['size'])): ?>
                                            <span class="badge bg-light text-dark border">Size: <?= htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['color'])): ?>
                                            <span class="badge bg-light text-dark border">Màu: <?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <span class="ms-1">x<strong><?= $pQty ?></strong></span>
                                    </div>
                                </div>

                                <div class="text-end fw-bold text-dark text-nowrap small">
                                    <?= number_format($pSub, 0, ',', '.') ?> đ
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tổng thanh toán -->
                    <div class="d-flex justify-content-between align-items-baseline pt-2 mb-4">
                        <span class="fs-5 fw-bold">Tổng thanh toán:</span>
                        <span class="fs-3 fw-bold text-accent"><?= number_format($total, 0, ',', '.') ?> đ</span>
                    </div>

                    <!-- Các nút điều hướng -->
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a href="<?= BASE_URL ?>?action=products" class="btn btn-outline-primary fw-semibold px-4">
                            <i class="bi bi-bag me-1"></i> Tiếp tục mua hàng
                        </a>
                        <a href="<?= BASE_URL ?>?action=order-history" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-clock-history me-1"></i> Xem lịch sử đơn hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function copyToClipboard(text, btnElement) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopyFeedback(btnElement);
        }).catch(function() {
            fallbackCopy(text, btnElement);
        });
    } else {
        fallbackCopy(text, btnElement);
    }
}

function fallbackCopy(text, btnElement) {
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    try {
        document.execCommand('copy');
        showCopyFeedback(btnElement);
    } catch (e) {
        alert('Không thể sao chép, bạn hãy sao chép thủ công: ' + text);
    }
    document.body.removeChild(input);
}

function showCopyFeedback(btnElement) {
    if (!btnElement) return;
    const origHtml = btnElement.innerHTML;
    btnElement.innerHTML = '<i class="bi bi-check2 text-success"></i>';
    btnElement.classList.add('btn-success', 'text-white');
    btnElement.classList.remove('btn-outline-secondary', 'btn-outline-primary');
    setTimeout(function() {
        btnElement.innerHTML = origHtml;
        btnElement.classList.remove('btn-success', 'text-white');
        btnElement.classList.add(origHtml.includes('primary') ? 'btn-outline-primary' : 'btn-outline-secondary');
    }, 1800);
}
</script>
