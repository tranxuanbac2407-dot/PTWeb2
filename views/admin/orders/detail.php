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
$normalStatuses = ['pending', 'processing', 'shipping', 'completed'];
$isSpecialStatus = !in_array($currStatus, $normalStatuses, true);
$hasReturnFlow = in_array($currStatus, ['return_request', 'return_accepted', 'return_rejected', 'returned'], true);

$statusLabels = [
    'pending'        => ['text' => 'Chờ xử lý',          'icon' => 'bi-clock-history',           'class' => 'admin-order-pending'],
    'processing'     => ['text' => 'Đang xử lý',         'icon' => 'bi-gear-wide-connected',     'class' => 'admin-order-processing'],
    'shipping'       => ['text' => 'Đang giao hàng',     'icon' => 'bi-truck',                   'class' => 'admin-order-shipping'],
    'completed'      => ['text' => 'Giao thành công',    'icon' => 'bi-check-circle-fill',       'class' => 'admin-order-completed'],
    'cancelled'      => ['text' => 'Đã hủy',             'icon' => 'bi-x-circle-fill',           'class' => 'admin-order-cancelled'],
    'return_request'  => ['text' => 'Chờ xử lý hoàn hàng', 'icon' => 'bi-arrow-counterclockwise', 'class' => 'admin-order-return-request'],
    'return_accepted' => ['text' => 'Đã chấp nhận hoàn hàng', 'icon' => 'bi-check2-circle',      'class' => 'admin-order-return-accepted'],
    'return_rejected' => ['text' => 'Đã từ chối hoàn hàng', 'icon' => 'bi-x-circle',             'class' => 'admin-order-return-rejected'],
    'returned'        => ['text' => 'Hoàn tất hoàn hàng', 'icon' => 'bi-arrow-return-left',      'class' => 'admin-order-returned'],
];

$currStatusInfo = $statusLabels[$currStatus] ?? ['text' => $currStatus, 'icon' => 'bi-question-circle', 'class' => 'admin-order-pending'];

$isOnlinePayment = ($payMethod !== 'cod');
$isPaid = (($order['payment_status'] ?? 'unpaid') === 'paid') || ($isOnlinePayment && in_array($currStatus, ['shipping', 'completed'], true));

$payMethodLabels = [
    'cod'     => 'Thanh toán khi nhận hàng (COD)',
    'momo'    => 'Ví điện tử MoMo',
    'banking' => 'Chuyển khoản ngân hàng (VietQR 24/7)',
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
                            <div class="col-6">
                                <span class="text-muted small d-block">Trạng thái thanh toán:</span>
                                <?php if ($isOnlinePayment): ?>
                                    <?php if ($isPaid): ?>
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Đã thanh toán thành công</span>
                                    <?php elseif (($order['payment_status'] ?? '') === 'refunded'): ?>
                                        <span class="badge bg-secondary px-2 py-1"><i class="bi bi-arrow-return-left me-1"></i>Đã hoàn tiền</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Chưa thanh toán (Chờ xác nhận)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($currStatus === 'completed'): ?>
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Đã thu tiền mặt</span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="bi bi-cash me-1"></i>Thu tiền mặt khi nhận hàng (COD)</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($isOnlinePayment && $isPaid && $currStatus === 'shipping'): ?>
                        <!-- Banner nổi bật: Đã thanh toán thành công & đang giao hàng -->
                        <div class="alert alert-success d-flex align-items-center p-3 mb-3 rounded-3 border border-success-subtle shadow-sm">
                            <i class="bi bi-truck fs-1 me-3 text-success"></i>
                            <div>
                                <h5 class="alert-heading fw-bold mb-1 text-success">
                                    <i class="bi bi-check-circle-fill me-1"></i> Đã thanh toán thành công &amp; Đang giao hàng
                                </h5>
                                <p class="mb-0 small text-dark">
                                    Đơn hàng Online đã được xác nhận thanh toán thành công qua tài khoản ngân hàng. Sản phẩm hiện đang trong tiến trình giao hàng đến khách.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($isOnlinePayment && !$isPaid): ?>
                        <!-- Khối hành động: Xác nhận thanh toán Online & Bắt đầu giao hàng -->
                        <div class="admin-payment-action-card p-3 mb-3 rounded-3 border border-primary bg-primary bg-opacity-10">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h5 class="fs-6 fw-bold text-primary mb-1 d-flex align-items-center">
                                        <i class="bi bi-qr-code-scan me-2 fs-5"></i>Xác nhận thanh toán Online &amp; Bắt đầu giao hàng
                                    </h5>
                                    <p class="mb-0 text-muted small">
                                        Khách hàng chọn thanh toán Online. <strong>Chỉ có thanh toán thành công thì mới giao hàng.</strong> Khi tiền đã về tài khoản, hãy nhấn xác nhận:
                                    </p>
                                </div>
                                <form action="<?= BASE_URL ?>?action=admin-order-confirm-payment" method="post" class="m-0" onsubmit="return confirm('Bạn xác nhận tài khoản đã nhận đủ số tiền <?= number_format($totalAmount, 0, ',', '.') ?> đ và muốn chuyển đơn sang trạng thái ĐANG GIAO HÀNG?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <button type="submit" class="admin-btn admin-btn-accent fw-bold py-2 px-3 d-flex align-items-center">
                                        <i class="bi bi-check2-all me-1 fs-5"></i>
                                        <span>Xác nhận đã thanh toán &amp; Giao hàng</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form cập nhật trạng thái đơn hàng -->
                    <div class="admin-status-update-box p-3 rounded-3 bg-light border">
                        <h4 class="fs-6 fw-bold mb-2">
                            <i class="bi bi-arrow-repeat text-accent me-1"></i>Cập nhật trạng thái đơn hàng:
                        </h4>
                        <?php if ($isSpecialStatus): ?>
                            <div class="alert alert-warning mb-0 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold">
                                        <i class="bi <?= htmlspecialchars($currStatusInfo['icon'], ENT_QUOTES, 'UTF-8') ?> me-1"></i>
                                        Trạng thái hiện tại: <?= htmlspecialchars($currStatusInfo['text'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="small mt-1">
                                        Đơn hàng đang ở trạng thái đặc biệt, không thể cập nhật bằng dropdown thông thường.
                                        <?php if ($hasReturnFlow): ?>
                                            Vui lòng mở khu vực Quản lý hoàn hàng để xử lý.
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($hasReturnFlow): ?>
                                    <a href="<?= BASE_URL ?>?action=admin-return-detail&amp;id=<?= $orderId ?>" class="admin-btn admin-btn-outline">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Mở Quản lý hoàn hàng
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>?action=admin-order-update-status" method="post" class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">

                                <select name="order_status" class="admin-form-input admin-select-filter" style="min-width: 200px;">
                                    <option value="pending" <?= $currStatus === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="processing" <?= $currStatus === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <?php if ($isOnlinePayment && !$isPaid): ?>
                                        <option value="shipping" <?= $currStatus === 'shipping' ? 'selected' : '' ?> disabled>
                                            Đang giao hàng (Cần TT thành công trước)
                                        </option>
                                    <?php else: ?>
                                        <option value="shipping" <?= $currStatus === 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                                    <?php endif; ?>
                                    <option value="completed" <?= $currStatus === 'completed' ? 'selected' : '' ?>>Giao thành công</option>
                                </select>

                                <button type="submit" class="admin-btn admin-btn-accent">
                                    <i class="bi bi-check2-circle me-1"></i> Lưu trạng thái
                                </button>
                            </form>
                            <?php if ($isOnlinePayment && !$isPaid): ?>
                                <div class="form-text text-danger mt-1 small">
                                    <i class="bi bi-exclamation-circle me-1"></i>Đơn hàng online: Chỉ có thanh toán thành công thì mới giao hàng.
                                </div>
                            <?php elseif (!$isOnlinePayment): ?>
                                <div class="form-text text-muted mt-1 small">
                                    <i class="bi bi-info-circle me-1"></i>Đơn hàng COD: Khách hàng thanh toán tiền mặt khi nhận hàng từ shipper.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($order['return_reason']) || !empty($order['return_image'])): ?>
                        <div class="admin-return-box mt-3 p-3 rounded-3 border border-warning bg-warning bg-opacity-10">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2 fw-bold text-warning-emphasis">
                                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                    <span>Thông tin yêu cầu hoàn hàng từ khách:</span>
                                </div>
                                <?php if (!empty($order['return_image'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                        <i class="bi bi-patch-check-fill me-1"></i>Đã có ảnh tem mác
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border small">Chưa có ảnh</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <span class="small text-muted d-block mb-1">Lý do hoàn hàng:</span>
                                <div class="p-2 bg-white rounded border border-warning-subtle text-dark small">
                                    <?= nl2br(htmlspecialchars($order['return_reason'] ?? 'Không ghi rõ lý do', ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                            </div>

                            <?php if (!empty($order['return_image'])): ?>
                                <div class="mt-3">
                                    <span class="small text-muted d-block mb-1 fw-semibold">
                                        <i class="bi bi-camera-fill text-danger me-1"></i> Ảnh xác nhận hàng còn nguyên vẹn, còn tem mác:
                                    </span>
                                    <div class="d-flex align-items-start gap-3 p-2 bg-white rounded border border-warning-subtle flex-wrap">
                                        <a href="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="d-inline-block border rounded overflow-hidden flex-shrink-0 shadow-xs" title="Bấm vào để mở ảnh kích thước đầy đủ trong tab mới">
                                            <img src="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="Ảnh tem mác hoàn hàng #<?= $orderId ?>"
                                                 style="max-width: 140px; max-height: 140px; object-fit: contain; display: block;">
                                        </a>
                                        <div class="small text-muted flex-grow-1" style="min-width: 200px;">
                                            <div class="fw-semibold text-dark mb-1">Kiểm tra tem mác sản phẩm:</div>
                                            <p class="mb-1">Khách hàng đã cung cấp ảnh chụp chứng minh sản phẩm còn nguyên vẹn và còn tem mác đính kèm.</p>
                                            <p class="mb-0 text-success fw-semibold"><i class="bi bi-check2-circle me-1"></i>Hợp lệ để xác nhận hoàn tiền và điều phối nhân viên đến lấy lại hàng tận nơi.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
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
