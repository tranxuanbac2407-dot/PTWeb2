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

$isOnlineMethod = (($order['payment_method'] ?? 'cod') !== 'cod');
// Đơn online coi như đã thanh toán nếu payment_status là paid, HOẶC admin đã chuyển sang shipping/completed
$isPaidStatus   = (($order['payment_status'] ?? 'unpaid') === 'paid')
               || ($isOnlineMethod && in_array($status, ['shipping', 'completed'], true));

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

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
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
                    <h5 class="alert-heading fw-bold mb-1">Đã xảy ra lỗi!</h5>
                    <p class="mb-0"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h1 class="fs-2 fw-bold mb-1">
                    <i class="bi bi-receipt-cutoff text-accent me-2"></i>Chi tiết đơn hàng #<?= $ordId ?>
                </h1>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-clock me-1"></i>Ngày đặt: <?= $orderDate ?>
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>?action=order-history" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
                </a>
                <?php if ($status === 'completed'): ?>
                    <button type="button" class="btn btn-outline-danger" onclick="toggleReturnModal(true)">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Yêu cầu hoàn hàng
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> In hóa đơn
                </button>
            </div>
        </div>

        <!-- Trạng thái đơn hàng - Stepper tiến trình -->
        <div class="sx-card p-4 mb-4">
            <h2 class="fs-5 fw-bold mb-4 pb-2 border-bottom">Tiến độ đơn hàng</h2>

            <?php if ($status === 'cancelled'): ?>
                <div class="alert alert-danger d-flex align-items-center mb-0">
                    <i class="bi bi-x-circle-fill fs-3 me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Đơn hàng này đã bị hủy</h6>
                        <small>Nếu bạn có thắc mắc hoặc cần hỗ trợ đặt lại, vui lòng liên hệ hotline SPORTIX: 1900 8888.</small>
                    </div>
                </div>
            <?php elseif (in_array($status, ['return_request', 'return_accepted', 'return_rejected', 'returned'], true)): ?>
                <?php
                    $retStep = match ($status) {
                        'return_request'  => 1,
                        'return_accepted' => 2,
                        'return_rejected' => 2,
                        'returned'        => 4,
                        default           => 1,
                    };
                    $isOrderRefunded = (($order['payment_status'] ?? '') === 'refunded');
                    $refundMethodHint = ($order['payment_method'] ?? 'cod') === 'cod'
                        ? 'Hoàn tiền mặt trực tiếp khi nhân viên đến lấy lại hàng hoặc chuyển khoản ngân hàng theo thông tin khách cung cấp.'
                        : 'Chuyển khoản hoàn tiền trực tiếp về tài khoản ngân hàng / ví điện tử khách đã thanh toán ban đầu.';
                ?>
                <!-- Tiến trình 4 bước Hoàn hàng & Hoàn tiền -->
                <div class="order-stepper d-flex justify-content-between position-relative flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div class="step-item text-center flex-fill step-active">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center bg-warning text-dark" style="width: 42px; height: 42px;">
                            <i class="bi bi-camera-fill fs-5"></i>
                        </div>
                        <div class="fw-bold small">1. Gửi yêu cầu</div>
                        <div class="text-muted small">Đã gửi ảnh tem mác</div>
                    </div>

                    <div class="step-item text-center flex-fill <?= $retStep >= 2 ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= $status === 'return_rejected' ? 'bg-danger text-white' : ($retStep >= 2 ? 'bg-info text-white' : 'bg-light text-muted border') ?>" style="width: 42px; height: 42px;">
                            <i class="bi <?= $status === 'return_rejected' ? 'bi-x-lg' : 'bi-clipboard-check' ?> fs-5"></i>
                        </div>
                        <div class="fw-bold small"><?= $status === 'return_rejected' ? '2. Từ chối hoàn' : '2. Duyệt & Lấy hàng' ?></div>
                        <div class="text-muted small"><?= $status === 'return_rejected' ? 'Không đủ điều kiện' : 'Kiểm tra tem mác' ?></div>
                    </div>

                    <div class="step-item text-center flex-fill <?= $retStep >= 4 ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= $retStep >= 4 ? 'bg-primary text-white' : 'bg-light text-muted border' ?>" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-arrow-in-left fs-5"></i>
                        </div>
                        <div class="fw-bold small">3. Đã thu hồi hàng</div>
                        <div class="text-muted small">Hàng nguyên vẹn</div>
                    </div>

                    <div class="step-item text-center flex-fill <?= ($retStep >= 4 && $isOrderRefunded) ? 'step-active' : '' ?>">
                        <div class="step-icon rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center <?= ($retStep >= 4 && $isOrderRefunded) ? 'bg-success text-white' : 'bg-light text-muted border' ?>" style="width: 42px; height: 42px;">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                        <div class="fw-bold small">4. Hoàn tiền</div>
                        <div class="text-muted small"><?= $isOrderRefunded ? 'Đã hoàn tiền' : 'Chờ đối soát' ?></div>
                    </div>
                </div>

                <?php if ($status === 'return_request'): ?>
                    <div class="alert alert-warning mb-0 p-3 rounded-3 shadow-sm border border-warning-subtle">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-arrow-counterclockwise fs-2 me-3 text-warning flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark">Đơn hàng đang chờ Admin duyệt yêu cầu hoàn hàng</h6>
                                <p class="mb-1 small">Lý do hoàn hàng: <strong><?= htmlspecialchars($order['return_reason'] ?? 'Chưa ghi chú lý do', ENT_QUOTES, 'UTF-8') ?></strong></p>
                                <small class="text-muted d-block mb-1">SPORTIX đã tiếp nhận yêu cầu và ảnh xác nhận hàng còn nguyên vẹn, còn tem mác.</small>
                                <small class="text-dark d-block"><i class="bi bi-cash-stack me-1 text-warning"></i><strong>Quy trình hoàn tiền dự kiến:</strong> <?= $refundMethodHint ?></small>

                                <?php if (!empty($order['return_image'])): ?>
                                    <div class="mt-3 pt-2 border-top border-warning-subtle d-flex align-items-center gap-3">
                                        <span class="small fw-semibold text-dark"><i class="bi bi-tag-fill text-warning me-1"></i>Ảnh hàng còn nguyên tem mác:</span>
                                        <a href="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="d-inline-block border rounded p-1 bg-white shadow-xs text-decoration-none" title="Bấm để xem ảnh lớn">
                                            <img src="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh tem mác khi hoàn hàng" style="height: 52px; width: 52px; object-fit: cover; border-radius: 4px;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif ($status === 'return_accepted'): ?>
                    <div class="alert alert-info mb-0 p-3 rounded-3 shadow-sm border border-info-subtle">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check2-circle fs-2 me-3 text-info flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark">Yêu cầu hoàn hàng đã được chấp nhận — Đang hẹn thu hồi hàng</h6>
                                <p class="mb-1 small">Lý do hoàn hàng: <strong><?= htmlspecialchars($order['return_reason'] ?? 'Chưa ghi chú lý do', ENT_QUOTES, 'UTF-8') ?></strong></p>
                                <small class="text-muted d-block mb-1">Nhân viên giao nhận SPORTIX sẽ liên hệ đến địa chỉ của bạn để lấy lại hàng còn nguyên tem mác.</small>
                                <small class="text-dark d-block"><i class="bi bi-cash-stack me-1 text-info"></i><strong>Phương thức hoàn tiền sau khi thu hồi:</strong> <?= $refundMethodHint ?></small>

                                <?php if (!empty($order['return_image'])): ?>
                                    <div class="mt-3 pt-2 border-top border-info-subtle d-flex align-items-center gap-3">
                                        <span class="small fw-semibold text-dark"><i class="bi bi-tag-fill text-info me-1"></i>Ảnh hàng khi gửi yêu cầu:</span>
                                        <a href="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="d-inline-block border rounded p-1 bg-white shadow-xs text-decoration-none" title="Bấm để xem ảnh lớn">
                                            <img src="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh tem mác khi hoàn hàng" style="height: 52px; width: 52px; object-fit: cover; border-radius: 4px;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif ($status === 'return_rejected'): ?>
                    <div class="alert alert-danger mb-0 p-3 rounded-3 shadow-sm border border-danger-subtle">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-x-circle-fill fs-2 me-3 text-danger flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark">Yêu cầu hoàn hàng đã bị từ chối</h6>
                                <p class="mb-1 small">Lý do đã gửi: <strong><?= htmlspecialchars($order['return_reason'] ?? 'Chưa ghi chú lý do', ENT_QUOTES, 'UTF-8') ?></strong></p>
                                <small class="text-muted d-block">Yêu cầu hoàn hàng của bạn không đủ điều kiện chấp nhận. Nếu cần hỗ trợ thêm, vui lòng liên hệ SPORTIX qua hotline 1900 8888.</small>
                            </div>
                        </div>
                    </div>
                <?php elseif ($status === 'returned'): ?>
                    <div class="alert alert-success mb-0 p-3 rounded-3 shadow-sm border border-success-subtle">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-patch-check-fill fs-2 me-3 text-success flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-success">Hoàn tất thu hồi hàng &amp; Hoàn tiền thành công</h6>
                                <p class="mb-1 small">Lý do hoàn: <?= htmlspecialchars($order['return_reason'] ?? 'Đã tiếp nhận hàng và hoàn tất xử lý.', ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if (!empty($order['refund_note'])): ?>
                                    <p class="mb-1 small text-dark"><strong><i class="bi bi-cash-coin me-1 text-success"></i>Chi tiết hoàn tiền:</strong> <?= htmlspecialchars($order['refund_note'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php else: ?>
                                    <small class="text-muted d-block">SPORTIX đã kiểm tra tem mác, hoàn tất lấy lại hàng và hoàn tiền cho quý khách.</small>
                                <?php endif; ?>

                                <?php if (!empty($order['return_image'])): ?>
                                    <div class="mt-3 pt-2 border-top border-success-subtle d-flex align-items-center gap-3">
                                        <span class="small fw-semibold text-dark"><i class="bi bi-tag-fill text-success me-1"></i>Ảnh hàng hoàn trả:</span>
                                        <a href="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="d-inline-block border rounded p-1 bg-white shadow-xs text-decoration-none" title="Bấm để xem ảnh lớn">
                                            <img src="<?= htmlspecialchars(get_product_image_url($order['return_image']), ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh tem mác hoàn hàng" style="height: 52px; width: 52px; object-fit: cover; border-radius: 4px;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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

                <?php if ($status === 'completed'): ?>
                    <div class="alert alert-light border rounded-3 p-3 mt-4 mb-0 d-flex justify-content-between align-items-center flex-wrap gap-2" id="return-box">
                        <div>
                            <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-check text-success me-2"></i>Chính sách hoàn trả SPORTIX</h6>
                            <div class="small text-muted">Sản phẩm bị lỗi hoặc không vừa size? Quý khách có thể yêu cầu hoàn hàng trong vòng 7 ngày kể từ khi nhận hàng. <strong>Yêu cầu sản phẩm còn nguyên vẹn, còn nguyên tem mác kèm hình ảnh chụp thực tế</strong> để shop xác nhận hoàn tiền và đến lấy lại hàng tận nơi.</div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="toggleReturnModal(true)">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Yêu cầu hoàn hàng
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($isOnlineMethod && $isPaidStatus && $status === 'shipping'): ?>
                    <!-- Banner nổi bật: Đã thanh toán thành công và đang giao hàng -->
                    <div class="alert alert-success d-flex align-items-center mb-0 mt-3 p-3 rounded-3 shadow-sm border border-success-subtle">
                        <i class="bi bi-truck fs-1 me-3 text-success"></i>
                        <div>
                            <h5 class="alert-heading fw-bold mb-1 text-success">
                                <i class="bi bi-check-circle-fill me-1"></i> Đã thanh toán thành công và đang giao hàng
                            </h5>
                            <p class="mb-0 small text-dark">
                                Đơn hàng của bạn đã được admin xác nhận thanh toán thành công qua hình thức Online. Shipper đang trên đường vận chuyển sản phẩm tới địa chỉ nhận hàng!
                            </p>
                        </div>
                    </div>
                <?php elseif ($isOnlineMethod && !$isPaidStatus && $status !== 'cancelled'): ?>
                    <div class="alert alert-warning d-flex align-items-center mb-0 mt-3 p-3 rounded-3 border border-warning-subtle">
                        <i class="bi bi-info-circle-fill fs-3 me-3 text-warning"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Đơn hàng Online: Chờ xác nhận thanh toán</h6>
                            <p class="mb-0 small text-muted">
                                Quý khách vui lòng quét mã VietQR để hoàn tất chuyển khoản. <strong>Chỉ có thanh toán thành công thì mới giao hàng.</strong> Hệ thống sẽ xác nhận và tiến hành giao hàng ngay khi nhận được tiền.
                            </p>
                        </div>
                    </div>
                <?php elseif (!$isOnlineMethod && $status === 'shipping'): ?>
                    <div class="alert alert-info d-flex align-items-center mb-0 mt-3 p-3 rounded-3 border border-info-subtle">
                        <i class="bi bi-truck fs-2 me-3 text-info"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Đang giao hàng (Thanh toán khi nhận hàng COD)</h6>
                            <p class="mb-0 small text-muted">
                                Đơn hàng đang trên đường vận chuyển. Quý khách vui lòng chuẩn bị số tiền <strong><?= number_format($total, 0, ',', '.') ?> đ</strong> để thanh toán cho nhân viên giao hàng khi nhận sản phẩm.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <!-- Cột trái: Danh sách các sản phẩm trong đơn & Mã VietQR thanh toán -->
            <div class="col-12 col-lg-8">
                <div class="sx-card p-3 mb-4 shadow-sm">
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

                <?php if (($order['payment_method'] ?? '') === 'banking' || ($order['payment_method'] ?? '') === 'momo'): ?>
                    <?php
                        $paymentStatus = $isPaidStatus ? 'paid' : (string) ($order['payment_status'] ?? 'unpaid');
                        $qrDetailUrl = function_exists('get_vietqr_url') ? get_vietqr_url($total, 'SPORTIX DH' . $ordId) : '';
                    ?>
                    <!-- Khối mã VietQR thanh toán được bố trí sang cột trái rộng rãi -->
                    <div class="sx-card p-4 border border-primary border-opacity-25 shadow-sm" style="background: linear-gradient(to bottom, #f0fdf4, #ffffff);">
                        <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom flex-wrap gap-2">
                            <h4 class="fs-5 fw-bold text-primary mb-0 d-flex align-items-center">
                                <i class="bi bi-qr-code-scan me-2 fs-4"></i>Mã VietQR thanh toán tự động
                            </h4>
                            <span id="qr-status-badge-detail">
                            <?php if ($isPaidStatus): ?>
                                <span class="badge bg-success px-3 py-2 fs-6"><i class="bi bi-check-circle-fill me-1"></i>Đã thanh toán thành công</span>
                            <?php elseif ($paymentStatus === 'pending_confirm'): ?>
                                <span class="badge bg-info text-dark px-3 py-2 fs-6"><i class="bi bi-qr-code-scan me-1"></i>Đã thanh toán QR (Chờ Admin xác nhận)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="bi bi-hourglass-split me-1"></i>Chờ quét mã thanh toán</span>
                            <?php endif; ?>
                            </span>
                        </div>

                        <div class="row g-4 align-items-center">
                            <!-- Cột trái trong card: Ảnh mã VietQR -->
                            <div class="col-12 col-md-5 text-center">
                                <div class="d-inline-block p-2 bg-white rounded-3 shadow-sm border">
                                    <img src="<?= htmlspecialchars($qrDetailUrl, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="VietQR #<?= $ordId ?>"
                                         class="img-fluid rounded"
                                         style="max-width: 220px; height: auto;"
                                         loading="lazy">
                                </div>
                                <div class="small text-muted mt-2">
                                    <i class="bi bi-phone me-1"></i>Mở App Ngân hàng bất kỳ quét mã để thanh toán
                                </div>
                            </div>

                            <!-- Cột phải trong card: Chi tiết số tài khoản, số tiền và nội dung -->
                            <div class="col-12 col-md-7">
                                <div class="bg-white p-3 rounded-3 border small d-flex flex-column gap-2 mb-3 shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Ngân hàng thụ hưởng:</span>
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
                                        <span class="text-muted">Số tiền chuyển khoản:</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-accent fs-6"><?= number_format($total, 0, ',', '.') ?> đ</strong>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 sx-copy-btn" onclick="copyToClipboard('<?= (int) round($total) ?>', this)" title="Sao chép số tiền">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Nội dung chuyển khoản:</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-primary fs-6" id="copy-memo">SPORTIX DH<?= $ordId ?></strong>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 sx-copy-btn" onclick="copyToClipboard('SPORTIX DH<?= $ordId ?>', this)" title="Sao chép nội dung">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="qr-action-area-detail">
                                <?php if ($paymentStatus === 'unpaid' && $status !== 'cancelled'): ?>
                                    <form action="<?= BASE_URL ?>?action=confirm-payment" method="post" class="m-0" id="form-qr-confirm-detail">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="order_id" value="<?= $ordId ?>">
                                        <input type="hidden" name="return_to" value="detail">
                                        <button type="button" onclick="confirmQrPaymentAjax('form-qr-confirm-detail', 'qr-action-area-detail', 'qr-status-badge-detail', this)" class="btn btn-success w-100 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center">
                                            <i class="bi bi-qr-code-scan me-2 fs-5"></i>
                                            <span>Tôi đã quét QR &amp; Thanh toán xong</span>
                                        </button>
                                    </form>
                                    <div class="text-muted text-center small mt-2" style="font-size: 0.78rem;">
                                        <i class="bi bi-info-circle me-1"></i>Sau khi quét mã, hệ thống xác nhận ngay phía bạn và chờ Admin đối soát nhận tiền để giao hàng.
                                    </div>
                                <?php elseif ($paymentStatus === 'pending_confirm'): ?>
                                    <div class="alert alert-info py-2 px-3 mb-0 text-center small fw-semibold border border-info-subtle shadow-xs">
                                        <i class="bi bi-check2-circle me-1 text-primary"></i> Bạn đã thanh toán qua mã QR! Đơn hàng đang chờ <strong>Admin kiểm tra và xác nhận thêm 1 bước</strong> trước khi giao hàng.
                                    </div>
                                <?php elseif ($isPaidStatus): ?>
                                    <div class="alert alert-success py-2 px-3 mb-0 text-center small fw-semibold border border-success-subtle shadow-xs">
                                        <i class="bi bi-patch-check-fill me-1 text-success"></i> Admin đã xác nhận thanh toán thành công! Kiện hàng đang được giao đến bạn.
                                    </div>
                                <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột phải: Thông tin thanh toán & Địa chỉ nhận hàng -->
            <div class="col-12 col-lg-4">
                <!-- Khối thanh toán được đẩy lên trên cùng của cột phải -->
                <div class="sx-card p-4 mb-4 shadow-sm">
                    <h3 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center">
                        <i class="bi bi-credit-card-2-front text-accent me-2"></i>Thông tin thanh toán
                    </h3>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phương thức:</span>
                        <span class="fw-semibold text-dark text-end"><?= get_detail_payment_name($order['payment_method'] ?? 'cod') ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Trạng thái TT:</span>
                        <?php if (($order['payment_status'] ?? '') === 'refunded'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="bi bi-arrow-return-left me-1"></i>Đã hoàn tiền</span>
                        <?php elseif ($isOnlineMethod): ?>
                            <?php if ($isPaidStatus): ?>
                                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Đã thanh toán thành công</span>
                            <?php elseif (($order['payment_status'] ?? '') === 'pending_confirm'): ?>
                                <span class="badge bg-info text-dark px-2 py-1"><i class="bi bi-qr-code-scan me-1"></i>Đã quét QR (Chờ Admin)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Chờ quét mã thanh toán</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($status === 'completed'): ?>
                                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Đã thanh toán (COD)</span>
                            <?php else: ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="bi bi-cash me-1"></i>Thanh toán khi nhận hàng</span>
                            <?php endif; ?>
                        <?php endif; ?>
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

                    <a href="<?= BASE_URL ?>?action=products" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-bag-plus me-1"></i> Tiếp tục mua sắm
                    </a>
                </div>

                <!-- Khối địa chỉ nhận hàng đặt phía dưới khối thanh toán -->
                <div class="sx-card p-4 shadow-sm">
                    <h3 class="fs-5 fw-bold mb-3 pb-2 border-bottom d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-accent me-2"></i>Địa chỉ nhận hàng
                    </h3>
                    <div class="mb-2">
                        <strong class="text-dark d-block"><?= htmlspecialchars($order['receiver_name'] ?? 'Khách hàng', ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($order['shipping_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php if ($status === 'completed'): ?>
<!-- Modal Yêu cầu hoàn hàng (Thuần HTML/CSS/JS, không phụ thuộc Bootstrap JS) -->
<div id="modalReturnOrder" class="sx-modal-overlay" style="display: none;">
    <div class="sx-modal-dialog">
        <div class="sx-modal-card">
            <div class="sx-modal-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="fw-bold mb-0 text-danger d-flex align-items-center">
                    <i class="bi bi-arrow-counterclockwise me-2 fs-5"></i>Yêu cầu hoàn hàng / trả hàng #<?= $ordId ?>
                </h5>
                <button type="button" class="btn-close" aria-label="Đóng" onclick="toggleReturnModal(false)"></button>
            </div>
            <form action="<?= BASE_URL ?>?action=order-return" method="post" enctype="multipart/form-data" id="form-return-order">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="order_id" value="<?= $ordId ?>">

                <div class="sx-modal-body p-3">
                    <div class="alert alert-warning py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Quý khách vui lòng cung cấp lý do kèm ảnh chụp sản phẩm còn nguyên tem mác để SPORTIX kiểm tra, xác nhận hoàn tiền và điều phối nhân viên đến lấy lại hàng tận nơi.
                    </div>

                    <div class="mb-3">
                        <label for="preset_reason" class="form-label fw-semibold">Lý do hoàn hàng <span class="text-danger">*</span></label>
                        <select class="form-select" id="preset_reason" name="preset_reason" required>
                            <option value="">-- Chọn lý do chính --</option>
                            <option value="Sản phẩm bị lỗi kỹ thuật, bung chỉ, rách vải">Sản phẩm bị lỗi kỹ thuật, bung chỉ, rách vải</option>
                            <option value="Giao sai sản phẩm (sai mẫu mã, màu sắc hoặc kích cỡ)">Giao sai sản phẩm (sai mẫu mã, màu sắc hoặc kích cỡ)</option>
                            <option value="Sản phẩm khác với mô tả / hình ảnh trên website">Sản phẩm khác với mô tả / hình ảnh trên website</option>
                            <option value="Không vừa vặn kích thước, muốn đổi size khác">Không vừa vặn kích thước, muốn đổi size khác</option>
                            <option value="Hàng bị móp méo, hư hỏng trong quá trình vận chuyển">Hàng bị móp méo, hư hỏng trong quá trình vận chuyển</option>
                            <option value="Lý do khác">Lý do khác</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="detail_reason" class="form-label fw-semibold">Mô tả chi tiết lý do <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="detail_reason"
                                  name="detail_reason"
                                  rows="3"
                                  placeholder="Vui lòng mô tả chi tiết lỗi hoặc tình trạng sản phẩm bạn nhận được..."
                                  required></textarea>
                    </div>

                    <!-- Bổ sung trường tải ảnh chụp hàng còn nguyên tem mác -->
                    <div class="mb-3">
                        <label for="return_image" class="form-label fw-semibold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-camera-fill text-danger me-1"></i> Ảnh hàng còn nguyên vẹn, còn tem mác <span class="text-danger">*</span></span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-normal small" style="font-size: 0.75rem;">Bắt buộc</span>
                        </label>
                        <input type="file"
                               class="form-control"
                               id="return_image"
                               name="return_image"
                               accept="image/*"
                               required
                               onchange="previewReturnImage(this)">
                        <div class="form-text small text-muted mt-1">
                            <i class="bi bi-info-circle me-1"></i> Chụp rõ góc sản phẩm cùng tem/mác còn nguyên để shop đối chiếu (JPG, PNG, WEBP, tối đa 5MB).
                        </div>

                        <!-- Vùng xem trước ảnh đã chọn -->
                        <div id="return-image-preview-container" class="mt-2" style="display: none;">
                            <div class="position-relative d-inline-block border rounded p-1 bg-white shadow-xs">
                                <img id="return-image-preview" src="" alt="Xem trước ảnh tem mác" style="max-height: 120px; max-width: 100%; border-radius: 4px; object-fit: contain;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" onclick="clearReturnImagePreview()" title="Xóa ảnh">
                                    <i class="bi bi-x" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="small text-muted">
                        <i class="bi bi-clock-history me-1"></i> Sau khi gửi yêu cầu, nhân viên CSKH SPORTIX sẽ kiểm tra ảnh tem mác và liên hệ xác nhận hoàn tiền trong vòng 24 giờ.
                    </div>
                </div>

                <div class="sx-modal-footer d-flex justify-content-end gap-2 p-3 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleReturnModal(false)">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger fw-semibold d-flex align-items-center">
                        <i class="bi bi-send me-2"></i> Gửi yêu cầu hoàn hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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
        alert('Không thể sao chép: ' + text);
    }
    document.body.removeChild(input);
}

function showCopyFeedback(btnElement) {
    if (!btnElement) return;
    const origHtml = btnElement.innerHTML;
    btnElement.innerHTML = '<i class="bi bi-check2 text-success"></i>';
    setTimeout(function() {
        btnElement.innerHTML = origHtml;
    }, 1800);
}

function toggleReturnModal(show) {
    const modal = document.getElementById('modalReturnOrder');
    if (modal) {
        modal.style.display = show ? 'flex' : 'none';
        if (show) {
            document.body.style.overflow = 'hidden';
            const preset = document.getElementById('preset_reason');
            if (preset) preset.focus();
        } else {
            document.body.style.overflow = '';
        }
    }
}

function previewReturnImage(input) {
    const container = document.getElementById('return-image-preview-container');
    const preview = document.getElementById('return-image-preview');
    if (!container || !preview) return;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        container.style.display = 'none';
        preview.src = '';
    }
}

function clearReturnImagePreview() {
    const input = document.getElementById('return_image');
    const container = document.getElementById('return-image-preview-container');
    const preview = document.getElementById('return-image-preview');
    if (input) input.value = '';
    if (container) container.style.display = 'none';
    if (preview) preview.src = '';
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#return-box' || window.location.hash === '#return-modal') {
        toggleReturnModal(true);
    }

    const modal = document.getElementById('modalReturnOrder');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                toggleReturnModal(false);
            }
        });
    }
});

function confirmQrPaymentAjax(formId, areaId, badgeId, btnEl) {
    const form = document.getElementById(formId);
    if (!form) return;
    if (btnEl) {
        btnEl.disabled = true;
        btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xác nhận thanh toán...';
    }
    const formData = new FormData(form);
    formData.append('ajax', '1');

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data && data.success) {
            const badge = document.getElementById(badgeId);
            if (badge) {
                badge.innerHTML = '<span class="badge bg-info text-dark px-3 py-2 fs-6"><i class="bi bi-qr-code-scan me-1"></i>Đã thanh toán QR (Chờ Admin xác nhận)</span>';
            }
            const area = document.getElementById(areaId);
            if (area) {
                area.innerHTML = '<div class="alert alert-info py-2 px-3 mb-0 text-center small fw-semibold border border-info-subtle shadow-xs">'
                    + '<i class="bi bi-check2-circle me-1 text-primary"></i> Bạn đã thanh toán qua mã QR! Đơn hàng đang chờ <strong>Admin kiểm tra và xác nhận thêm 1 bước</strong> trước khi giao hàng.'
                    + '</div>';
            }
        } else {
            form.submit();
        }
    })
    .catch(function() {
        form.submit();
    });
}
</script>
