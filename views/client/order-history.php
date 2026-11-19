<?php
/**
 * views/order-history.php
 * Giao diện lịch sử đơn hàng của khách hàng SPORTIX.
 */

$currentUser = $currentUser ?? null;
$orders      = $orders ?? [];

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Helper hiển thị badge trạng thái
function render_order_status_badge($status) {
    switch ($status) {
        case 'completed':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Giao thành công</span>';
        case 'shipping':
            return '<span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill"><i class="bi bi-truck me-1"></i> Đang giao hàng</span>';
        case 'processing':
            return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill"><i class="bi bi-box-seam me-1"></i> Đang chuẩn bị hàng</span>';
        case 'return_request':
            return '<span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-2 rounded-pill"><i class="bi bi-arrow-counterclockwise me-1 text-warning"></i> Chờ xử lý hoàn hàng</span>';
        case 'returned':
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill"><i class="bi bi-arrow-return-left me-1"></i> Đã hoàn hàng</span>';
        case 'cancelled':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Đã hủy</span>';
        case 'pending':
        default:
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Chờ xác nhận</span>';
    }
}

// Helper tên phương thức thanh toán
function get_payment_name($method) {
    switch ($method) {
        case 'banking': return 'Chuyển khoản ngân hàng (QR Code)';
        case 'momo':    return 'Ví điện tử MoMo';
        case 'cod':
        default:        return 'Thanh toán khi nhận hàng (COD)';
    }
}
?>

<div class="order-history-page py-4">
    <div class="container">

        <!-- Breadcrumbs -->
        <nav class="sx-breadcrumb mb-4" aria-label="Đường dẫn trang">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng</li>
            </ol>
        </nav>

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
                <div><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="fs-2 fw-bold mb-1">
                    <i class="bi bi-clock-history text-accent me-2"></i>Lịch sử mua hàng
                </h1>
                <p class="text-muted mb-0 small">
                    Quản lý và theo dõi tiến độ các đơn hàng của tài khoản <?= htmlspecialchars($currentUser['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="orders-list d-flex flex-column gap-4">
                <?php foreach ($orders as $order): ?>
                    <?php
                        $ordId     = (int) ($order['order_id'] ?? 0);
                        $items     = $order['items'] ?? [];
                        $total     = (float) ($order['total_amount'] ?? 0);
                        $orderDate = !empty($order['order_date']) ? date('d/m/Y - H:i', strtotime($order['order_date'])) : 'Vừa xong';
                    ?>
                    <div class="order-card sx-card p-0 overflow-hidden">

                        <!-- Header card -->
                        <div class="order-card-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="fs-6 fw-bold text-dark">
                                    Đơn hàng <span class="text-accent">#<?= $ordId ?></span>
                                </span>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i><?= $orderDate ?>
                                </span>
                            </div>

                            <div>
                                <?= render_order_status_badge($order['order_status'] ?? 'pending') ?>
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm trong đơn -->
                        <div class="order-card-body p-3">
                            <div class="order-items-wrapper d-flex flex-column gap-3">
                                <?php foreach ($items as $item): ?>
                                    <?php
                                        $pName    = $item['product_name'] ?? 'Sản phẩm';
                                        $pPrice   = (float) ($item['unit_price'] ?? 0);
                                        $pQty     = (int) ($item['quantity'] ?? 1);
                                        $pSub     = (float) ($item['subtotal'] ?? ($pPrice * $pQty));
                                        $pImg     = (string) ($item['image'] ?? '');
                                        $pImgSrc  = get_product_image_url($pImg);
                                    ?>
                                    <div class="order-item-row d-flex align-items-center gap-3 py-1">
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

                                        <div class="flex-grow-1 min-w-0">
                                            <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= (int) ($item['product_id'] ?? 0) ?>"
                                               class="fw-bold text-dark text-decoration-none d-block text-truncate">
                                                <?= htmlspecialchars($pName, ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <div class="d-flex flex-wrap gap-2 small text-muted mt-1">
                                                <?php if (!empty($item['size'])): ?>
                                                    <span>Size: <strong><?= htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item['color'])): ?>
                                                    <span>Màu: <strong><?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                                                <?php endif; ?>
                                                <span>Số lượng: <strong>x<?= $pQty ?></strong></span>
                                            </div>
                                        </div>

                                        <div class="text-end fw-semibold text-nowrap">
                                            <?= number_format($pSub, 0, ',', '.') ?> đ
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Thông tin người nhận và địa chỉ -->
                            <div class="receiver-box mt-3 pt-3 border-top bg-light p-3 rounded-2 small text-muted">
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <div><strong class="text-dark"><i class="bi bi-person me-1"></i>Người nhận:</strong> <?= htmlspecialchars($order['receiver_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($order['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</div>
                                        <div class="mt-1"><strong class="text-dark"><i class="bi bi-geo-alt me-1"></i>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="col-12 col-md-6 text-md-end">
                                        <div>
                                            <strong class="text-dark">Thanh toán:</strong> <?= get_payment_name($order['payment_method'] ?? 'cod') ?>
                                            <?php if (($order['payment_status'] ?? '') === 'paid'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Đã thanh toán</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer card -->
                        <div class="order-card-footer p-3 bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="text-muted">Tổng số tiền:</span>
                                <span class="fs-4 fw-bold text-accent"><?= number_format($total, 0, ',', '.') ?> đ</span>
                            </div>

                            <div class="d-flex gap-2">
                                <?php if (($order['order_status'] ?? '') === 'completed'): ?>
                                    <a href="<?= BASE_URL ?>?action=order-detail&amp;id=<?= $ordId ?>#return-box"
                                       class="btn btn-sm btn-outline-danger fw-semibold"
                                       title="Gửi yêu cầu hoàn hàng cho đơn này">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Hoàn hàng
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>?action=order-detail&amp;id=<?= $ordId ?>"
                                   class="btn btn-sm btn-outline-primary fw-semibold">
                                    <i class="bi bi-eye me-1"></i> Xem chi tiết
                                </a>
                                <a href="<?= BASE_URL ?>?action=products"
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-bag-plus me-1"></i> Mua lại
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="order-empty-box sx-card text-center py-5 px-3">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                <h2 class="fs-4 fw-bold mt-3 mb-2">Chưa có đơn hàng nào</h2>
                <p class="text-muted mb-4">
                    Bạn chưa có đơn đặt hàng nào tại SPORTIX. Hãy thỏa sức mua sắm các sản phẩm thể thao ngay hôm nay!
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>?action=products" class="btn btn-primary btn-lg">
                        <i class="bi bi-bag-heart me-2"></i> Khám phá sản phẩm
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
