<?php
require_once __DIR__ . '/../partials/guard.php';

$keyword      = $keyword ?? '';
$status       = $status ?? '';
$orders       = $orders ?? [];
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;
$totalOrders  = count($orders);

// Ánh xạ trạng thái hiển thị
$statusLabels = [
    'pending'        => ['text' => 'Chờ xử lý',          'icon' => 'bi-clock-history',           'class' => 'admin-order-pending'],
    'processing'     => ['text' => 'Đang xử lý',         'icon' => 'bi-gear-wide-connected',     'class' => 'admin-order-processing'],
    'shipping'       => ['text' => 'Đang giao',          'icon' => 'bi-truck',                   'class' => 'admin-order-shipping'],
    'completed'      => ['text' => 'Hoàn thành',         'icon' => 'bi-check-circle-fill',       'class' => 'admin-order-completed'],
    'cancelled'      => ['text' => 'Đã hủy',             'icon' => 'bi-x-circle-fill',           'class' => 'admin-order-cancelled'],
    'return_request' => ['text' => 'Yêu cầu hoàn hàng', 'icon' => 'bi-arrow-counterclockwise', 'class' => 'admin-order-return-request'],
    'returned'       => ['text' => 'Đã hoàn hàng',       'icon' => 'bi-arrow-return-left',       'class' => 'admin-order-returned'],
];
?>

<div class="admin-orders-page">

    <!-- Thông báo kết quả thao tác -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="admin-alert admin-alert-success" role="alert">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <!-- Thanh công cụ lọc & tìm kiếm đơn hàng -->
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <form class="admin-search-form admin-filter-form" action="<?= BASE_URL ?>" method="get" role="search">
                <input type="hidden" name="action" value="admin-orders">

                <div class="admin-search-row">
                    <div class="admin-search-field admin-search-field--grow">
                        <div class="admin-search-input-group">
                            <i class="bi bi-search admin-search-icon" aria-hidden="true"></i>
                            <input type="text"
                                   name="keyword"
                                   class="admin-form-input admin-search-input"
                                   placeholder="Tìm mã đơn hoặc tên khách hàng..."
                                   value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($keyword !== ''): ?>
                                <a href="<?= BASE_URL ?>?action=admin-orders<?= $status !== '' ? '&amp;status=' . urlencode($status) : '' ?>"
                                   class="admin-search-clear"
                                   title="Xóa từ khóa tìm kiếm">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-search-field admin-search-field--filter">
                        <select name="status" class="admin-form-input admin-select-filter" aria-label="Lọc theo trạng thái" onchange="this.form.submit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                            <option value="shipping" <?= $status === 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            <option value="return_request" <?= $status === 'return_request' ? 'selected' : '' ?>>Yêu cầu hoàn hàng</option>
                            <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Đã hoàn hàng</option>
                        </select>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-secondary admin-search-btn">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <span>Tìm kiếm</span>
                    </button>

                    <?php if ($keyword !== '' || $status !== ''): ?>
                        <a href="<?= BASE_URL ?>?action=admin-orders" class="admin-btn admin-btn-outline admin-search-reset" title="Đặt lại bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                        </a>
                    <?php endif; ?>

                    <span class="admin-search-note">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>Hệ thống tự động cập nhật doanh số
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách đơn hàng -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-receipt" aria-hidden="true"></i>
                <span>Danh sách đơn hàng</span>
                <span class="admin-count-badge"><?= $totalOrders ?> đơn hàng</span>
            </h3>

            <div class="admin-filter-badges">
                <?php if ($keyword !== ''): ?>
                    <span class="admin-filter-badge">
                        Tìm kiếm: "<strong><?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?></strong>"
                    </span>
                <?php endif; ?>

                <?php if ($status !== '' && isset($statusLabels[$status])): ?>
                    <span class="admin-filter-badge">
                        Trạng thái: <strong><?= htmlspecialchars($statusLabels[$status]['text'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="table-responsive">
                <table class="admin-table admin-orders-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;" class="text-center">Mã đơn</th>
                            <th style="min-width: 180px;">Khách hàng</th>
                            <th style="width: 140px;">Số điện thoại</th>
                            <th style="width: 160px;">Ngày đặt</th>
                            <th style="width: 150px;" class="text-end">Tổng tiền</th>
                            <th style="width: 150px;" class="text-center">Trạng thái</th>
                            <th style="width: 130px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $orderId      = (int) ($order['order_id'] ?? 0);
                                $customerName = (string) ($order['customer_name'] ?? ($order['receiver_name'] ?? 'Khách lẻ'));
                                $phone        = (string) ($order['phone'] ?? '');
                                $orderDate    = (string) ($order['order_date'] ?? '');
                                $totalAmount  = (float) ($order['total_amount'] ?? 0);
                                $stKey        = (string) ($order['order_status'] ?? 'pending');
                                $stInfo       = $statusLabels[$stKey] ?? ['text' => $stKey, 'icon' => 'bi-question-circle', 'class' => 'admin-order-pending'];

                                $formattedDate = !empty($orderDate) ? date('d/m/Y H:i', strtotime($orderDate)) : '&mdash;';
                            ?>
                            <tr>
                                <!-- Cột 1: Mã đơn (ID đơn hàng) -->
                                <td class="text-center">
                                    <span class="admin-id-badge fw-bold">#<?= $orderId ?></span>
                                </td>

                                <!-- Cột 2: Khách hàng (Tên người đặt) -->
                                <td>
                                    <div class="admin-customer-info">
                                        <strong class="admin-customer-name">
                                            <?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                        <?php if (!empty($order['receiver_name']) && $order['receiver_name'] !== $customerName): ?>
                                            <div class="text-muted small">
                                                Nhận: <?= htmlspecialchars($order['receiver_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Cột 3: Số điện thoại (Người nhận) -->
                                <td>
                                    <?php if ($phone !== ''): ?>
                                        <a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" class="admin-phone-link text-decoration-none">
                                            <i class="bi bi-telephone me-1 text-muted"></i>
                                            <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột 4: Ngày đặt (Ngày tạo đơn) -->
                                <td>
                                    <span class="admin-order-date text-nowrap" title="<?= htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                                        <?= $formattedDate ?>
                                    </span>
                                </td>

                                <!-- Cột 5: Tổng tiền (Tổng giá trị đơn) -->
                                <td class="text-end">
                                    <span class="admin-order-price fw-bold text-accent">
                                        <?= number_format($totalAmount, 0, ',', '.') ?> <small>đ</small>
                                    </span>
                                </td>

                                <!-- Cột 6: Trạng thái (Trạng thái hiện tại) -->
                                <td class="text-center">
                                    <span class="admin-order-badge <?= htmlspecialchars($stInfo['class'], ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi <?= htmlspecialchars($stInfo['icon'], ENT_QUOTES, 'UTF-8') ?> me-1"></i>
                                        <?= htmlspecialchars($stInfo['text'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <?php if (!empty($order['return_reason'])): ?>
                                        <div class="small text-danger mt-1 text-truncate mx-auto" style="max-width: 160px;" title="Lý do hoàn hàng: <?= htmlspecialchars($order['return_reason'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi bi-chat-left-dots me-1"></i>Lý do: <?= htmlspecialchars($order['return_reason'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột 7: Thao tác (Xem chi tiết) -->
                                <td class="text-center">
                                    <div class="admin-row-actions justify-content-center">
                                        <a href="<?= BASE_URL ?>?action=admin-order-detail&amp;id=<?= $orderId ?>"
                                           class="admin-btn-action admin-btn-action-view"
                                           title="Xem chi tiết đơn hàng #<?= $orderId ?>">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                            <span>Xem chi tiết</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty py-5 text-center">
                <i class="bi bi-receipt text-muted" style="font-size: 3.5rem;"></i>
                <?php if ($keyword !== '' || $status !== ''): ?>
                    <strong class="d-block fs-5 mt-3 mb-1">Không tìm thấy đơn hàng nào phù hợp</strong>
                    <span class="text-muted">Không có đơn hàng nào khớp với tiêu chí tìm kiếm hiện tại.</span>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>?action=admin-orders" class="admin-btn admin-btn-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại bộ lọc
                        </a>
                    </div>
                <?php else: ?>
                    <strong class="d-block fs-5 mt-3 mb-1">Chưa có đơn hàng nào</strong>
                    <span class="text-muted">Cửa hàng hiện chưa có đơn hàng nào từ khách hàng.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
