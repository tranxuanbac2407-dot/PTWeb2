<?php
require_once __DIR__ . '/../partials/guard.php';

$order        = $order ?? [];
$orderItems   = $orderItems ?? [];
$csrfToken    = $csrfToken ?? '';
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;

$orderId      = (int) ($order['order_id'] ?? 0);
$orderDate    = (string) ($order['order_date'] ?? '');
$totalAmount  = (float) ($order['total_amount'] ?? 0);
$payMethod    = (string) ($order['payment_method'] ?? 'cod');
$payStatus    = (string) ($order['payment_status'] ?? 'unpaid');
$orderStatus  = (string) ($order['order_status'] ?? '');
$customerName = (string) ($order['customer_name'] ?? ($order['receiver_name'] ?? ''));
$receiverName = (string) ($order['receiver_name'] ?? '');
$phone        = (string) ($order['phone'] ?? '');
$address      = (string) ($order['shipping_address'] ?? '');
$reason       = trim((string) ($order['return_reason'] ?? ''));
$returnImage  = trim((string) ($order['return_image'] ?? ''));

$statusLabels = [
    'return_request'  => ['text' => 'Chờ xử lý',    'icon' => 'bi-hourglass-split',   'class' => 'admin-return-badge--pending'],
    'return_accepted' => ['text' => 'Đã chấp nhận', 'icon' => 'bi-check-circle-fill', 'class' => 'admin-return-badge--accepted'],
    'return_rejected' => ['text' => 'Đã từ chối',   'icon' => 'bi-x-circle-fill',     'class' => 'admin-return-badge--rejected'],
    'returned'        => ['text' => 'Đã hoàn tất',  'icon' => 'bi-arrow-return-left', 'class' => 'admin-return-badge--completed'],
];
$statusInfo = $statusLabels[$orderStatus] ?? ['text' => $orderStatus, 'icon' => 'bi-question-circle', 'class' => 'admin-return-badge--pending'];

$payMethodLabels = [
    'cod'     => 'Thanh toán khi nhận hàng (COD)',
    'momo'    => 'Ví điện tử MoMo',
    'banking' => 'Chuyển khoản ngân hàng (VietQR 24/7)',
    'vnpay'   => 'Cổng thanh toán VNPAY',
];
$payStatusLabels = [
    'unpaid'   => 'Chưa thanh toán',
    'paid'     => 'Đã thanh toán',
    'refunded' => 'Đã hoàn tiền',
];
?>
<div class="admin-return-detail-page">

    <div class="admin-form-back mb-3">
        <a href="<?= BASE_URL ?>?action=admin-returns" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            <span>Quay lại danh sách hoàn hàng</span>
        </a>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="admin-alert admin-alert-success mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="admin-alert admin-alert-danger mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex justify-content-between align-items-center">
                    <h3>
                        <i class="bi bi-receipt me-1" aria-hidden="true"></i>
                        <span>Yêu cầu hoàn hàng #<?= $orderId ?></span>
                    </h3>
                    <span class="admin-return-badge <?= htmlspecialchars($statusInfo['class'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi <?= htmlspecialchars($statusInfo['icon'], ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i>
                        <?= htmlspecialchars($statusInfo['text'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <div class="admin-card-body">
                    <div class="admin-order-meta-grid">
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="text-muted small d-block">Mã đơn hàng:</span>
                                <strong>#<?= $orderId ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Ngày đặt hàng:</span>
                                <strong><?= $orderDate !== '' ? htmlspecialchars(date('d/m/Y H:i:s', strtotime($orderDate)), ENT_QUOTES, 'UTF-8') : '—' ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Tổng tiền:</span>
                                <strong class="fs-5 text-accent"><?= number_format($totalAmount, 0, ',', '.') ?> đ</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Phương thức thanh toán:</span>
                                <strong><?= htmlspecialchars($payMethodLabels[$payMethod] ?? strtoupper($payMethod), ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Trạng thái đơn hàng:</span>
                                <strong><?= htmlspecialchars($statusInfo['text'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Trạng thái thanh toán:</span>
                                <strong><?= htmlspecialchars($payStatusLabels[$payStatus] ?? $payStatus, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="admin-return-info-box">
                        <h4 class="admin-return-info-box__title">
                            <i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>
                            Thông tin yêu cầu hoàn hàng
                        </h4>

                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Lý do hoàn hàng:</span>
                            <div class="admin-return-reason-detail">
                                <?= $reason !== ''
                                    ? nl2br(htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'))
                                    : 'Chưa cung cấp lý do hoàn hàng.' ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Hình ảnh hoàn hàng:</span>
                            <?php if ($returnImage !== ''): ?>
                                <?php $imageUrl = get_product_image_url($returnImage); ?>
                                <a href="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="admin-return-image-link">
                                    <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Hình ảnh hoàn hàng đơn #<?= $orderId ?>">
                                    <span>Xem ảnh kích thước đầy đủ</span>
                                </a>
                            <?php else: ?>
                                <div class="admin-return-no-image">
                                    <i class="bi bi-image me-1" aria-hidden="true"></i>
                                    Khách hàng chưa cung cấp hình ảnh hoàn hàng.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="admin-return-note">
                            <?php if ($orderStatus === 'return_request'): ?>
                                Yêu cầu đang chờ xử lý. Chấp nhận yêu cầu không đồng nghĩa với đã hoàn tiền.
                            <?php elseif ($orderStatus === 'return_accepted'): ?>
                                Yêu cầu đã được chấp nhận. Trạng thái thanh toán chưa bị thay đổi; cần đối soát giao dịch hoàn tiền thực tế trước khi cập nhật.
                            <?php elseif ($orderStatus === 'return_rejected'): ?>
                                Yêu cầu đã bị từ chối. Không thể duyệt lại yêu cầu này.
                            <?php else: ?>
                                Yêu cầu đã ở trạng thái hoàn tất theo dữ liệu hiện có.
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($orderStatus === 'return_request'): ?>
                        <div class="admin-return-actions">
                            <form action="<?= BASE_URL ?>?action=admin-return-accept" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <button type="submit" class="admin-btn admin-btn-accent">
                                    <i class="bi bi-check-circle me-1" aria-hidden="true"></i>
                                    Chấp nhận yêu cầu
                                </button>
                            </form>
                            <form action="<?= BASE_URL ?>?action=admin-return-reject" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <button type="submit" class="admin-btn admin-btn-outline-danger">
                                    <i class="bi bi-x-circle me-1" aria-hidden="true"></i>
                                    Từ chối yêu cầu
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="admin-return-processed">
                            <i class="bi bi-lock me-1" aria-hidden="true"></i>
                            Yêu cầu này đã được xử lý, không thể chấp nhận hoặc từ chối lại.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="admin-card h-100">
                <div class="admin-card-header">
                    <h3>
                        <i class="bi bi-person-lines-fill me-1" aria-hidden="true"></i>
                        <span>Thông tin khách hàng</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <ul class="admin-customer-details-list list-unst mb-0 d-flex flex-column gap-3">
                        <li>
                            <span class="text-muted small d-block">Họ tên:</span>
                            <strong><?= htmlspecialchars($customerName !== '' ? $customerName : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></strong>
                        </li>
                        <li>
                            <span class="text-muted small d-block">Số điện thoại:</span>
                            <?php if ($phone !== ''): ?>
                                <a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="fw-bold text-decoration-none">
                                    <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                <strong>—</strong>
                            <?php endif; ?>
                        </li>
                        <li>
                            <span class="text-muted small d-block">Người nhận:</span>
                            <strong><?= htmlspecialchars($receiverName !== '' ? $receiverName : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></strong>
                        </li>
                        <li>
                            <span class="text-muted small d-block">Địa chỉ giao hàng:</span>
                            <span class="d-block lh-base"><?= htmlspecialchars($address !== '' ? $address : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-box-seam me-1" aria-hidden="true"></i>
                <span>Sản phẩm trong đơn hàng (<?= count($orderItems) ?> món)</span>
            </h3>
        </div>
        <?php if (!empty($orderItems)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="min-width: 250px;">Tên sản phẩm</th>
                            <th style="width: 140px;" class="text-center">Kích thước</th>
                            <th style="width: 140px;" class="text-center">Màu sắc</th>
                            <th style="width: 100px;" class="text-center">Số lượng</th>
                            <th style="width: 140px;" class="text-end">Đơn giá</th>
                            <th style="width: 150px;" class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars((string) ($item['product_name'] ?? 'Sản phẩm'), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td class="text-center"><?= !empty($item['size']) ? htmlspecialchars((string) $item['size'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                <td class="text-center"><?= !empty($item['color']) ? htmlspecialchars((string) $item['color'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border px-3 py-2"><?= (int) ($item['quantity'] ?? 0) ?></span></td>
                                <td class="text-end"><?= number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') ?> đ</td>
                                <td class="text-end fw-bold"><?= number_format((float) ($item['subtotal'] ?? 0), 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-return-empty">
                <i class="bi bi-box" aria-hidden="true"></i>
                <strong>Chưa có sản phẩm trong đơn hàng này</strong>
                <span>Dữ liệu order_items tương ứng sẽ được hiển thị tại đây.</span>
            </div>
        <?php endif; ?>
    </div>

</div>
