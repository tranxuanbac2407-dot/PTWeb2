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
    'return_request'  => ['text' => 'Chờ xử lý hoàn hàng', 'icon' => 'bi-hourglass-split',   'class' => 'admin-return-badge--pending'],
    'return_accepted' => ['text' => 'Đã chấp nhận hoàn hàng', 'icon' => 'bi-check-circle-fill', 'class' => 'admin-return-badge--accepted'],
    'return_rejected' => ['text' => 'Đã từ chối hoàn hàng', 'icon' => 'bi-x-circle-fill',     'class' => 'admin-return-badge--rejected'],
    'returned'        => ['text' => 'Hoàn tất hoàn hàng', 'icon' => 'bi-arrow-return-left',   'class' => 'admin-return-badge--completed'],
];
$statusInfo = $statusLabels[$orderStatus] ?? ['text' => $orderStatus, 'icon' => 'bi-question-circle', 'class' => 'admin-return-badge--pending'];

$payMethodLabels = [
    'cod'     => 'Thanh toán khi nhận hàng (COD)',
    'momo'    => 'Ví điện tử MoMo',
    'banking' => 'Chuyển khoản ngân hàng (VietQR 24/7)',
    'vnpay'   => 'Cổng thanh toán VNPAY',
];
$payStatusLabels = [
    'unpaid'          => 'Chưa thanh toán',
    'pending_confirm' => 'Khách đã quét QR (Chờ Admin xác nhận)',
    'paid'            => 'Đã thanh toán',
    'refunded'        => 'Đã hoàn tiền',
];
$isCodOrder   = ($payMethod === 'cod');
$refundNote   = trim((string) ($order['refund_note'] ?? ''));
$rejectReason = trim((string) ($order['return_reject_reason'] ?? ''));
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
                                <strong>Bước tiếp theo:</strong> Kiểm tra kỹ lý do và ảnh hàng nguyên tem mác. Nếu đồng ý hoàn hàng:
                                <?= $isCodOrder
                                    ? 'Đơn <strong>COD</strong> sẽ tiến hành hẹn shipper đến lấy lại hàng và hoàn tiền mặt/chuyển khoản cho khách.'
                                    : 'Đơn <strong>Thanh toán Online</strong> sẽ tiến hành lấy lại hàng và chuyển khoản hoàn tiền về tài khoản thanh toán ban đầu của khách.' ?>
                            <?php elseif ($orderStatus === 'return_accepted'): ?>
                                <strong>Đang chờ thu hồi hàng &amp; hoàn tiền (<?= $isCodOrder ? 'Đơn COD' : 'Đơn Thanh toán Online' ?>):</strong>
                                Vui lòng thực hiện lần lượt 2 bước xác nhận bên dưới sau khi đã nhận lại sản phẩm còn nguyên tem mác và đã hoàn đủ <strong><?= number_format($totalAmount, 0, ',', '.') ?> đ</strong> cho khách.
                            <?php elseif ($orderStatus === 'return_rejected'): ?>
                                Yêu cầu đã bị từ chối. Không thể duyệt lại yêu cầu này.
                                <?php if ($rejectReason !== ''): ?>
                                    <div class="mt-2 p-2 bg-white rounded border border-danger-subtle text-danger small">
                                        <strong><i class="bi bi-x-circle me-1"></i>Lý do từ chối:</strong> <?= nl2br(htmlspecialchars($rejectReason, ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                Yêu cầu đã hoàn tất quy trình thu hồi hàng và hoàn tiền (Trạng thái thanh toán: <strong><?= htmlspecialchars($payStatusLabels[$payStatus] ?? $payStatus, ENT_QUOTES, 'UTF-8') ?></strong>).
                                <?php if ($refundNote !== ''): ?>
                                    <div class="mt-1 text-dark"><strong>Chi tiết hoàn tiền:</strong> <?= htmlspecialchars($refundNote, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($orderStatus === 'return_request'): ?>
                        <div class="admin-return-actions">
                            <form action="<?= BASE_URL ?>?action=admin-return-accept" method="post" onsubmit="return confirm('Xác nhận ĐỒNG Ý yêu cầu hoàn hàng #<?= $orderId ?>?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                <button type="submit" class="admin-btn admin-btn-accent">
                                    <i class="bi bi-check-circle me-1" aria-hidden="true"></i>
                                    Đồng ý hoàn hàng (Bước 1)
                                </button>
                            </form>
                            <button type="button" class="admin-btn admin-btn-outline-danger" onclick="toggleRejectReturnBox(true)">
                                <i class="bi bi-x-circle me-1" aria-hidden="true"></i>
                                Từ chối yêu cầu
                            </button>
                        </div>

                        <!-- Khung nhập lý do từ chối bắt buộc (ẩn mặc định, mở khi bấm Từ chối yêu cầu) -->
                        <div id="reject-return-box" class="p-3 rounded-3 border border-danger-subtle bg-danger bg-opacity-10 mt-3" style="display: none;">
                            <h6 class="fw-bold text-danger mb-2 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-exclamation-triangle-fill me-1"></i> Nhập lý do từ chối hoàn hàng #<?= $orderId ?></span>
                                <button type="button" class="btn-close btn-sm" aria-label="Đóng" onclick="toggleRejectReturnBox(false)"></button>
                            </h6>
                            <form action="<?= BASE_URL ?>?action=admin-return-reject" method="post" onsubmit="return confirm('Bạn xác nhận TỪ CHỐI yêu cầu hoàn hàng #<?= $orderId ?> với lý do đã nhập?\n\nKhách hàng sẽ nhìn thấy lý do này trong chi tiết đơn hàng.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">

                                <div class="mb-2">
                                    <label for="reject_preset" class="form-label small text-muted mb-1">Chọn nhanh lý do mẫu (tùy chọn):</label>
                                    <select id="reject_preset" class="form-select form-select-sm" onchange="applyRejectPreset(this.value)">
                                        <option value="">-- Chọn lý do phổ biến --</option>
                                        <option value="Sản phẩm đã qua sử dụng, bị rách hoặc mất tem mác nguyên vẹn">Sản phẩm đã qua sử dụng, bị rách hoặc mất tem mác nguyên vẹn</option>
                                        <option value="Hình ảnh cung cấp không rõ ràng hoặc không đúng sản phẩm của cửa hàng">Hình ảnh cung cấp không rõ ràng hoặc không đúng sản phẩm của cửa hàng</option>
                                        <option value="Đơn hàng đã quá thời hạn 7 ngày hỗ trợ hoàn trả theo chính sách">Đơn hàng đã quá thời hạn 7 ngày hỗ trợ hoàn trả theo chính sách</option>
                                        <option value="Sản phẩm thuộc danh mục không áp dụng đổi trả">Sản phẩm thuộc danh mục không áp dụng đổi trả</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="reject_reason" class="form-label small fw-semibold text-dark mb-1">Lý do từ chối gửi tới khách hàng <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm" id="reject_reason" name="reject_reason" rows="3" placeholder="Vui lòng nhập rõ lý do từ chối để khách hàng nắm được..." required></textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleRejectReturnBox(false)">Hủy</button>
                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                                        <i class="bi bi-x-circle me-1"></i> Xác nhận từ chối hoàn hàng
                                    </button>
                                </div>
                            </form>
                        </div>

                        <script>
                        function toggleRejectReturnBox(show) {
                            const box = document.getElementById('reject-return-box');
                            if (!box) return;
                            box.style.display = show ? 'block' : 'none';
                            if (show) {
                                const input = document.getElementById('reject_reason');
                                if (input) input.focus();
                            }
                        }
                        function applyRejectPreset(val) {
                            const input = document.getElementById('reject_reason');
                            if (input && val) {
                                input.value = val;
                                input.focus();
                            }
                        }
                        </script>
                    <?php elseif ($orderStatus === 'return_accepted'): ?>
                        <div class="p-3 rounded-3 border bg-light mt-3">
                            <h5 class="fs-6 fw-bold mb-3 d-flex align-items-center text-dark">
                                <i class="bi bi-list-check text-accent me-2"></i>
                                <span>Xác nhận từng bước thu hồi hàng &amp; hoàn tiền (<?= $isCodOrder ? 'COD' : 'Thanh toán Online' ?>)</span>
                            </h5>
                            <form action="<?= BASE_URL ?>?action=admin-return-complete" method="post" onsubmit="return confirm('Xác nhận đã nhận lại hàng nguyên tem mác và đã hoàn đủ <?= number_format($totalAmount, 0, ',', '.') ?> đ cho khách hàng?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="order_id" value="<?= $orderId ?>">

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="confirm_received" value="1" id="confirm_received" required>
                                    <label class="form-check-label small fw-semibold text-dark" for="confirm_received">
                                        Bước 1: Đã thu hồi lại sản phẩm từ khách và kiểm tra còn nguyên vẹn, còn nguyên tem mác.
                                    </label>
                                </div>

                                <div class="mb-3 mt-3">
                                    <label for="refund_method" class="form-label small fw-semibold text-dark mb-1">
                                        Hình thức hoàn tiền (<?= $isCodOrder ? 'Đơn COD' : 'Đơn Thanh toán Online' ?>):
                                    </label>
                                    <select class="form-select form-select-sm" name="refund_method" id="refund_method" required>
                                        <?php if ($isCodOrder): ?>
                                            <option value="Hoàn tiền mặt trực tiếp cho khách khi shipper thu hồi hàng (COD)">Hoàn tiền mặt trực tiếp khi shipper thu hồi hàng (COD)</option>
                                            <option value="Chuyển khoản ngân hàng hoàn tiền đơn COD cho khách">Chuyển khoản ngân hàng hoàn tiền cho khách (COD)</option>
                                        <?php else: ?>
                                            <option value="Chuyển khoản hoàn tiền về tài khoản ngân hàng / ví MoMo khách đã thanh toán">Chuyển khoản hoàn tiền về tài khoản ngân hàng / ví MoMo ban đầu</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="confirm_refunded" value="1" id="confirm_refunded" required>
                                    <label class="form-check-label small fw-semibold text-dark" for="confirm_refunded">
                                        Bước 2: Đã hoàn đủ số tiền <strong><?= number_format($totalAmount, 0, ',', '.') ?> đ</strong> cho khách hàng theo hình thức trên.
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="refund_note" class="form-label small text-muted mb-1">Ghi chú / Mã giao dịch hoàn tiền (nếu có):</label>
                                    <input type="text" class="form-control form-control-sm" name="refund_note" id="refund_note" placeholder="VD: Đã CK Vietcombank lúc 14:30 - Mã GD FT2611...">
                                </div>

                                <button type="submit" class="admin-btn admin-btn-accent w-100 justify-content-center">
                                    <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                                    Xác nhận đã thu hồi hàng &amp; Hoàn tiền thành công
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
