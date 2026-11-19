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
            <?php elseif ($status === 'return_request'): ?>
                <div class="alert alert-warning d-flex align-items-center mb-0 p-3 rounded-3">
                    <i class="bi bi-arrow-counterclockwise fs-2 me-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Đơn hàng đang chờ xử lý yêu cầu hoàn hàng</h6>
                        <p class="mb-1 small">Lý do hoàn hàng: <strong><?= htmlspecialchars($order['return_reason'] ?? 'Chưa ghi chú lý do', ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <small class="text-muted">SPORTIX đã tiếp nhận yêu cầu và nhân viên chăm sóc khách hàng sẽ liên hệ với bạn trong vòng 24 giờ làm việc.</small>
                    </div>
                </div>
            <?php elseif ($status === 'returned'): ?>
                <div class="alert alert-secondary d-flex align-items-center mb-0 p-3 rounded-3">
                    <i class="bi bi-arrow-return-left fs-2 me-3 text-secondary"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Đơn hàng đã hoàn hàng thành công</h6>
                        <p class="mb-0 small">Ghi chú: <?= htmlspecialchars($order['return_reason'] ?? 'Đã tiếp nhận hàng và hoàn tất xử lý.', ENT_QUOTES, 'UTF-8') ?></p>
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

                <?php if ($status === 'completed'): ?>
                    <div class="alert alert-light border rounded-3 p-3 mt-4 mb-0 d-flex justify-content-between align-items-center flex-wrap gap-2" id="return-box">
                        <div>
                            <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-check text-success me-2"></i>Chính sách hoàn trả SPORTIX</h6>
                            <div class="small text-muted">Sản phẩm bị lỗi hoặc không vừa size? Quý khách có thể yêu cầu hoàn hàng/đổi trả trong vòng 7 ngày kể từ khi nhận hàng.</div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="toggleReturnModal(true)">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Yêu cầu hoàn hàng
                        </button>
                    </div>
                <?php endif; ?>
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
            <form action="<?= BASE_URL ?>?action=order-return" method="post" id="form-return-order">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="order_id" value="<?= $ordId ?>">

                <div class="sx-modal-body p-3">
                    <div class="alert alert-warning py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Quý khách vui lòng cung cấp lý do để SPORTIX hỗ trợ thu hồi sản phẩm và hoàn tiền hoặc đổi mới kịp thời.
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

                    <div class="small text-muted">
                        <i class="bi bi-clock-history me-1"></i> Sau khi gửi yêu cầu, nhân viên CSKH SPORTIX sẽ liên hệ xác nhận trong vòng 24 giờ.
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

<script>
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
</script>
<?php endif; ?>
