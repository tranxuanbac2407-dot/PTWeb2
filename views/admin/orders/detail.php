<?php
require_once __DIR__ . '/../partials/guard.php';

$order        = $order ?? [];
$orderItems   = $orderItems ?? [];
$csrfToken    = $csrfToken ?? '';
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;

$orderId      = (int) ($order['order_id'] ?? 0);
$customerName = (string) ($order['customer_name'] ?? ($order['receiver_name'] ?? 'Khách lẻ'));
$receiverName = (string) ($order['receiver_name'] ?? '');
$phone        = (string) ($order['phone'] ?? '');
$address      = (string) ($order['shipping_address'] ?? 'Chưa cập nhật');
$totalAmount  = (float) ($order['total_amount'] ?? 0);
$payMethod    = (string) ($order['payment_method'] ?? 'cod');
$orderDate    = (string) ($order['order_date'] ?? '');
$currStatus   = (string) ($order['order_status'] ?? 'pending');

$statusLabels = [
    'pending'        => ['text' => 'Chờ xử lý',          'icon' => 'bi-clock-history',           'class' => 'admin-order-pending'],
    'processing'     => ['text' => 'Đang xử lý',         'icon' => 'bi-gear-wide-connected',     'class' => 'admin-order-processing'],
    'shipping'       => ['text' => 'Đang giao hàng',     'icon' => 'bi-truck',                   'class' => 'admin-order-shipping'],
    'completed'      => ['text' => 'Đã hoàn thành',      'icon' => 'bi-check-circle-fill',       'class' => 'admin-order-completed'],
    'cancelled'      => ['text' => 'Đã hủy',             'icon' => 'bi-x-circle-fill',           'class' => 'admin-order-cancelled'],
    'return_request' => ['text' => 'Yêu cầu hoàn hàng', 'icon' => 'bi-arrow-counterclockwise', 'class' => 'admin-order-return-request'],
    'returned'       => ['text' => 'Đã hoàn hàng',       'icon' => 'bi-arrow-return-left',       'class' => 'admin-order-returned'],
];

$currStatusInfo = $statusLabels[$currStatus] ?? ['text' => $currStatus, 'icon' => 'bi-question-circle', 'class' => 'admin-order-pending'];

$payMethodLabels = [
    'cod'     => 'Thanh toán khi nhận hàng (COD)',
    'momo'    => 'Ví điện tử MoMo',
    'banking' => 'Chuyển khoản ngân hàng',
    'vnpay'   => 'Cổng thanh toán VNPAY',
];
?>

<div class="admin-order-detail-page">

    <!-- Nút quay lại danh sách -->
    <div class="admin-form-back mb-3">
        <a href="<?= BASE_URL ?>?action=admin-orders" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>
            <span>Quay lại danh sách đơn hàng</span>
        </a>
    </div>

    <!-- Thông báo kết quả thao tác -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="admin-alert admin-alert-success mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="admin-alert admin-alert-danger mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <!-- Thông tin đơn hàng & Cập nhật trạng thái -->
        <div class="col-12 col-lg-7">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex justify-content-between align-items-center">
                    <h3>
                        <i class="bi bi-receipt me-1 text-accent"></i>
                        <span>Chi tiết đơn hàng #<?= $orderId ?></span>
                    </h3>
                    <span class="admin-order-badge <?= htmlspecialchars($currStatusInfo['class'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi <?= htmlspecialchars($currStatusInfo['icon'], ENT_QUOTES, 'UTF-8') ?> me-1"></i>
                        <?= htmlspecialchars($currStatusInfo['text'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <div class="admin-card-body">
                    <div class="admin-order-meta-grid mb-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="text-muted small d-block">Ngày đặt hàng:</span>
                                <strong><?= !empty($orderDate) ? date('d/m/Y H:i:s', strtotime($orderDate)) : '&mdash;' ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Phương thức thanh toán:</span>
                                <strong><?= htmlspecialchars($payMethodLabels[$payMethod] ?? strtoupper($payMethod), ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Tổng giá trị đơn:</span>
                                <span class="fs-4 fw-bold text-accent">
                                    <?= number_format($totalAmount, 0, ',', '.') ?> đ
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Tài khoản đặt mua:</span>
                                <strong><?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <?php if (!empty($order['payment_status'])): ?>
                                <div class="col-6">
                                    <span class="text-muted small d-block">Trạng thái thanh toán:</span>
                                    <?php if ($order['payment_status'] === 'paid'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã thanh toán</span>
                                    <?php elseif ($order['payment_status'] === 'refunded'): ?>
                                        <span class="badge bg-secondary"><i class="bi bi-arrow-return-left me-1"></i>Đã hoàn tiền</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chưa thanh toán</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Form cập nhật trạng thái đơn hàng -->
                    <div class="admin-status-update-box p-3 rounded-3 bg-light border">
                        <h4 class="fs-6 fw-bold mb-2">
                            <i class="bi bi-arrow-repeat text-accent me-1"></i>Cập nhật trạng thái đơn hàng:
                        </h4>
                        <form action="<?= BASE_URL ?>?action=admin-order-update-status" method="post" class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="order_id" value="<?= $orderId ?>">

                            <select name="order_status" class="admin-form-input admin-select-filter" style="min-width: 200px;">
                                <option value="pending" <?= $currStatus === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                <option value="processing" <?= $currStatus === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                <option value="shipping" <?= $currStatus === 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                                <option value="completed" <?= $currStatus === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                <option value="cancelled" <?= $currStatus === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                <option value="return_request" <?= $currStatus === 'return_request' ? 'selected' : '' ?>>Yêu cầu hoàn hàng</option>
                                <option value="returned" <?= $currStatus === 'returned' ? 'selected' : '' ?>>Đã hoàn hàng</option>
                            </select>

                            <button type="submit" class="admin-btn admin-btn-accent">
                                <i class="bi bi-check2-circle me-1"></i> Lưu trạng thái
                            </button>
                        </form>
                    </div>

                    <?php if (!empty($order['return_reason'])): ?>
                        <div class="admin-return-box mt-3 p-3 rounded-3 border border-warning bg-warning bg-opacity-10">
                            <div class="d-flex align-items-center gap-2 fw-bold text-warning-emphasis mb-2">
                                <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                <span>Lý do khách hàng yêu cầu hoàn hàng:</span>
                            </div>
                            <div class="p-2 bg-white rounded border border-warning-subtle text-dark small">
                                <?= nl2br(htmlspecialchars($order['return_reason'], ENT_QUOTES, 'UTF-8')) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Thông tin giao nhận hàng -->
        <div class="col-12 col-lg-5">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h3>
                        <i class="bi bi-person-lines-fill me-1 text-accent"></i>
                        <span>Thông tin người nhận</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <ul class="admin-customer-details-list list-unstyled mb-0 d-flex flex-column gap-3">
                        <li>
                            <span class="text-muted small d-block">Họ và tên người nhận:</span>
                            <strong class="fs-6"><?= htmlspecialchars($receiverName, ENT_QUOTES, 'UTF-8') ?></strong>
                        </li>
                        <li>
                            <span class="text-muted small d-block">Số điện thoại liên hệ:</span>
                            <?php if ($phone !== ''): ?>
                                <a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="fw-bold text-decoration-none">
                                    <i class="bi bi-telephone me-1 text-accent"></i>
                                    <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </li>
                        <li>
                            <span class="text-muted small d-block">Địa chỉ giao nhận hàng:</span>
                            <span class="d-block lh-base">
                                <i class="bi bi-geo-alt text-danger me-1"></i>
                                <?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách các sản phẩm trong đơn hàng -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-box-seam me-1 text-accent"></i>
                <span>Danh sách món hàng (<?= count($orderItems) ?> món)</span>
            </h3>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;" class="text-center">Ảnh</th>
                        <th style="min-width: 250px;">Sản phẩm</th>
                        <th style="width: 140px;" class="text-center">Size / Màu</th>
                        <th style="width: 130px;" class="text-end">Đơn giá</th>
                        <th style="width: 100px;" class="text-center">Số lượng</th>
                        <th style="width: 150px;" class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <?php
                            $itemId       = (int) ($item['product_id'] ?? 0);
                            $itemName     = (string) ($item['product_name'] ?? 'Sản phẩm');
                            $itemSize     = (string) ($item['size'] ?? '');
                            $itemColor    = (string) ($item['color'] ?? '');
                            $itemPrice    = (float) ($item['unit_price'] ?? 0);
                            $itemQty      = (int) ($item['quantity'] ?? 1);
                            $itemSubtotal = (float) ($item['subtotal'] ?? ($itemPrice * $itemQty));
                            $itemImg      = (string) ($item['image'] ?? '');
                            $itemImgSrc   = get_product_image_url($itemImg);
                        ?>
                        <tr>
                            <!-- Ảnh -->
                            <td class="text-center">
                                <div class="admin-product-thumb" style="width: 50px; height: 50px;">
                                    <?php if (!empty($itemImgSrc)): ?>
                                        <img src="<?= htmlspecialchars($itemImgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($itemName, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <div class="admin-product-thumb-empty"><i class="bi bi-image"></i></div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Tên món hàng -->
                            <td>
                                <div>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($itemName, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($itemId > 0): ?>
                                        <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= $itemId ?>" target="_blank" class="small text-muted text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Xem sản phẩm
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Size & Màu -->
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center gap-1 small">
                                    <?php if ($itemSize !== ''): ?>
                                        <span class="admin-attr-pill">Size: <?= htmlspecialchars($itemSize, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <?php if ($itemColor !== ''): ?>
                                        <span class="admin-attr-pill">Màu: <?= htmlspecialchars($itemColor, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <?php if ($itemSize === '' && $itemColor === ''): ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Đơn giá -->
                            <td class="text-end fw-semibold">
                                <?= number_format($itemPrice, 0, ',', '.') ?> đ
                            </td>

                            <!-- Số lượng -->
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-3 py-1 fs-6">
                                    <?= $itemQty ?>
                                </span>
                            </td>

                            <!-- Thành tiền -->
                            <td class="text-end fw-bold text-accent">
                                <?= number_format($itemSubtotal, 0, ',', '.') ?> đ
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-bold fs-6">Tổng giá trị đơn hàng:</td>
                        <td class="text-end fw-bold fs-5 text-accent">
                            <?= number_format($totalAmount, 0, ',', '.') ?> đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
