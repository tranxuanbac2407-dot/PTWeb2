<?php
/**
 * views/order-detail-customer.php
 * Chi tiết đơn hàng cho khách hàng SPORTIX.
 */

$order = $order ?? [];
$items = $items ?? [];

$ordId     = (int) ($order['order_id'] ?? 0);
$total     = (float) ($order['total_amount'] ?? 0);
$status    = $order['order_status'] ?? 'pending';
$orderDate = !empty($order['order_date']) ? date('d/m/Y - H:i:s', strtotime($order['order_date'])) : 'Không xác định';

// Helper tên phương thức thanh toán
function get_detail_payment_name($method) {
    switch ($method) {
        case 'banking': return 'Chuyển khoản ngân hàng (QR Code)';
        case 'momo':    return 'Ví điện tử MoMo';
        case 'cod':
        default:        return 'Thanh toán khi nhận hàng (COD)';
    }
}

// Tính subtotal của các mặt hàng
$itemsSubtotal = 0;
foreach ($items as $it) {
    $itemsSubtotal += (float) ($it['subtotal'] ?? ((float) ($it['unit_price'] ?? 0) * (int) ($it['quantity'] ?? 1)));
}
$shippingFee = ($total > $itemsSubtotal) ? ($total - $itemsSubtotal) : 0;
?>

<div class="order-detail-customer-page py-4">
    <div class="container">

        <!-- Breadcrumbs -->
        <nav class="sx-breadcrumb mb-4" aria-label="Đường dẫn trang">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=order-history">Lịch sử đơn hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Đơn hàng #<?= $ordId ?></li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h1 class="fs-2 fw-bold mb-1">
                    <i class="bi bi-receipt-cutoff text-accent me-2"></i>Chi tiết đơn hàng #<?= $ordId ?>
                </h1>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-clock me-1"></i>Ngày đặt: <?= $orderDate ?>
                </p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>?action=order-history" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
                </a>
                <button type="button" class="btn btn-outline-primary ms-2" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> In hóa đơn
                </button>
            </div>
        </div>

        <!-- Trạng thái đơn hàng - Stepper tiến trình -->
        <div class="sx-card p-4 mb-4">
            <h2 class="fs-5 fw-bold mb-4 pb-2 border-bottom">Tiến độ giao hàng</h2>

            <?php if ($status === 'cancelled'): ?>
                <div class="alert alert-danger d-flex align-items-center mb-0">
                    <i class="bi bi-x-circle-fill fs-3 me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Đơn hàng này đã bị hủy</h6>
                        <small>Nếu bạn có thắc mắc hoặc cần hỗ trợ đặt lại, vui lòng liên hệ hotline SPORTIX: 1900 8888.</small>
                    </div>
                </div>
            <?php else: ?>
                <div class="order-stepper d-flex justify-content-between position-relative flex-wrap gap-3">
                    <?php
                        $stepLevels = [
                            'pending'    => 1,
                            'processing' => 2,
                            'shipping'   => 3,
                            'completed'  => 4,
                        ];
                        $curLevel = $stepLevels[$status] ?? 1;
                    ?>
                    <div class="step-item text-center flex-fill <?= $curLevel >= 1 ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= $curLevel >= 1 ? 'bg-primary text-white' : 'bg-light text-muted border' ?>" style="width: 44px; height: 44px;">
                            <i class="bi bi-file-earmark-text fs-5"></i>
                        </div>
                        <div class="fw-bold small">Đặt hàng thành công</div>
                        <div class="text-muted small">Đã ghi nhận đơn</div>
                    </div>

                    <div class="step-item text-center flex-fill <?= $curLevel >= 2 ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= $curLevel >= 2 ? 'bg-primary text-white' : 'bg-light text-muted border' ?>" style="width: 44px; height: 44px;">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                        <div class="fw-bold small">Đang chuẩn bị hàng</div>
                        <div class="text-muted small">Đang đóng gói</div>
                    </div>

                    <div class="step-item text-center flex-fill <?= $curLevel >= 3 ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= $curLevel >= 3 ? 'bg-primary text-white' : 'bg-light text-muted border' ?>" style="width: 44px; height: 44px;">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                        <div class="fw-bold small">Đang giao hàng</div>
                        <div class="text-muted small">Shipper đang trên đường</div>
                    </div>

                    <div class="step-item text-center flex-fill <?= $curLevel >= 4 ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= $curLevel >= 4 ? 'bg-success text-white' : 'bg-light text-muted border' ?>" style="width: 44px; height: 44px;">
                            <i class="bi bi-check-circle fs-5"></i>
                        </div>
                        <div class="fw-bold small">Đã giao thành công</div>
                        <div class="text-muted small">Khách đã nhận hàng</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <!-- Cột trái: Danh sách các sản phẩm trong đơn -->
            <div class="col-12 col-lg-8">
                <div class="sx-card p-3">
                    <h2 class="fs-5 fw-bold mb-3 pb-2 border-bottom">Danh sách sản phẩm (<?= count($items) ?> món)</h2>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 250px;">Sản phẩm</th>
                                    <th style="width: 120px;" class="text-end">Đơn giá</th>
                                    <th style="width: 80px;" class="text-center">SL</th>
                                    <th style="width: 140px;" class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                        $pName    = $item['product_name'] ?? 'Sản phẩm';
                                        $pPrice   = (float) ($item['unit_price'] ?? 0);
                                        $pQty     = (int) ($item['quantity'] ?? 1);
                                        $pSub     = (float) ($item['subtotal'] ?? ($pPrice * $pQty));
                                        $pImg     = (string) ($item['image'] ?? '');
                                        $pImgSrc  = get_product_image_url($pImg);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div style="width: 55px; height: 55px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0;">
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
                                                <div>
                                                    <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= (int) ($item['product_id'] ?? 0) ?>"
                                                       class="fw-bold text-dark text-decoration-none d-block">
                                                        <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                    <div class="d-flex gap-2 small text-muted mt-1">
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
                                        <td class="text-end fw-semibold">
                                            <?= number_format($pPrice, 0, ',', '.') ?> đ
                                        </td>
                                        <td class="text-center fw-bold">
                                            x<?= $pQty ?>
                                        </td>
                                        <td class="text-end fw-bold text-accent">
                                            <?= number_format($pSub, 0, ',', '.') ?> đ
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thông tin nhận hàng & Thanh toán -->
            <div class="col-12 col-lg-4">
                <div class="sx-card p-4 mb-4">
                    <h3 class="fs-5 fw-bold mb-3 pb-2 border-bottom">Địa chỉ nhận hàng</h3>
                    <div class="mb-2">
                        <strong class="text-dark d-block"><?= htmlspecialchars($order['receiver_name'] ?? 'Khách hàng', ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($order['shipping_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <div class="sx-card p-4">
                    <h3 class="fs-5 fw-bold mb-3 pb-2 border-bottom">Thanh toán</h3>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phương thức:</span>
                        <span class="fw-semibold text-dark text-end"><?= get_detail_payment_name($order['payment_method'] ?? 'cod') ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tạm tính hàng:</span>
                        <span class="fw-bold"><?= number_format($itemsSubtotal, 0, ',', '.') ?> đ</span>
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
                            <?= number_format($total, 0, ',', '.') ?> đ
                        </span>
                    </div>

                    <a href="<?= BASE_URL ?>?action=products" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-bag-plus me-1"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
