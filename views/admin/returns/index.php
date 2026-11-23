<?php
require_once __DIR__ . '/../partials/guard.php';

$keyword      = $keyword ?? '';
$status       = $status ?? '';
$requests     = $requests ?? [];
$stats        = $stats ?? ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;

$statusLabels = [
    'return_request'  => ['text' => 'Chờ xử lý hoàn hàng', 'icon' => 'bi-hourglass-split',   'class' => 'admin-return-badge--pending'],
    'return_accepted' => ['text' => 'Đã chấp nhận hoàn hàng', 'icon' => 'bi-check-circle-fill', 'class' => 'admin-return-badge--accepted'],
    'return_rejected' => ['text' => 'Đã từ chối hoàn hàng', 'icon' => 'bi-x-circle-fill',     'class' => 'admin-return-badge--rejected'],
    'returned'        => ['text' => 'Hoàn tất hoàn hàng', 'icon' => 'bi-arrow-return-left',   'class' => 'admin-return-badge--completed'],
];
?>
<div class="admin-returns-page">

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

    <div class="admin-return-stats">
        <div class="admin-return-stat-card">
            <span class="admin-return-stat-card__icon"><i class="bi bi-inbox" aria-hidden="true"></i></span>
            <span class="admin-return-stat-card__label">Tổng yêu cầu</span>
            <strong class="admin-return-stat-card__value"><?= (int) ($stats['total'] ?? 0) ?></strong>
        </div>
        <div class="admin-return-stat-card admin-return-stat-card--pending">
            <span class="admin-return-stat-card__icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
            <span class="admin-return-stat-card__label">Chờ xử lý hoàn hàng</span>
            <strong class="admin-return-stat-card__value"><?= (int) ($stats['pending'] ?? 0) ?></strong>
        </div>
        <div class="admin-return-stat-card admin-return-stat-card--accepted">
            <span class="admin-return-stat-card__icon"><i class="bi bi-check-circle-fill" aria-hidden="true"></i></span>
            <span class="admin-return-stat-card__label">Đã chấp nhận hoàn hàng</span>
            <strong class="admin-return-stat-card__value"><?= (int) ($stats['accepted'] ?? 0) ?></strong>
        </div>
        <div class="admin-return-stat-card admin-return-stat-card--rejected">
            <span class="admin-return-stat-card__icon"><i class="bi bi-x-circle-fill" aria-hidden="true"></i></span>
            <span class="admin-return-stat-card__label">Đã từ chối hoàn hàng</span>
            <strong class="admin-return-stat-card__value"><?= (int) ($stats['rejected'] ?? 0) ?></strong>
        </div>
    </div>

    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <form class="admin-search-form admin-filter-form" action="<?= BASE_URL ?>" method="get" role="search">
                <input type="hidden" name="action" value="admin-returns">

                <div class="admin-search-row">
                    <div class="admin-search-field admin-search-field--grow">
                        <div class="admin-search-input-group">
                            <i class="bi bi-search admin-search-icon" aria-hidden="true"></i>
                            <input type="text"
                                   name="keyword"
                                   class="admin-form-input admin-search-input"
                                   placeholder="Tìm mã đơn hoặc tên khách hàng..."
                                   value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="admin-search-field admin-search-field--filter">
                        <select name="status" class="admin-form-input admin-select-filter" aria-label="Lọc theo trạng thái yêu cầu">
                            <option value="" <?= $status === '' ? 'selected' : '' ?>>Tất cả yêu cầu</option>
                            <option value="return_request" <?= $status === 'return_request' ? 'selected' : '' ?>>Chờ xử lý hoàn hàng</option>
                            <option value="return_accepted" <?= $status === 'return_accepted' ? 'selected' : '' ?>>Đã chấp nhận hoàn hàng</option>
                            <option value="return_rejected" <?= $status === 'return_rejected' ? 'selected' : '' ?>>Đã từ chối hoàn hàng</option>
                            <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Hoàn tất hoàn hàng</option>
                        </select>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-secondary admin-search-btn">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <span>Tìm kiếm</span>
                    </button>

                    <?php if ($keyword !== '' || $status !== ''): ?>
                        <a href="<?= BASE_URL ?>?action=admin-returns" class="admin-btn admin-btn-outline admin-search-reset">
                            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Đặt lại
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                <span>Danh sách yêu cầu hoàn hàng</span>
                <span class="admin-count-badge"><?= count($requests) ?> yêu cầu</span>
            </h3>
        </div>

        <?php if (!empty($requests)): ?>
            <div class="table-responsive">
                <table class="admin-table admin-returns-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;" class="text-center">Mã đơn</th>
                            <th style="min-width: 180px;">Khách hàng</th>
                            <th style="width: 140px;">Số điện thoại</th>
                            <th style="width: 150px;">Ngày đặt</th>
                            <th style="min-width: 220px;">Lý do hoàn hàng</th>
                            <th style="width: 150px;" class="text-center">Trạng thái</th>
                            <th style="width: 120px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <?php
                                $orderId      = (int) ($request['order_id'] ?? 0);
                                $customerName = (string) ($request['customer_name'] ?? ($request['receiver_name'] ?? ''));
                                $phone        = (string) ($request['phone'] ?? '');
                                $orderDate    = (string) ($request['order_date'] ?? '');
                                $reason       = trim((string) ($request['return_reason'] ?? ''));
                                $statusKey    = (string) ($request['order_status'] ?? '');
                                $statusInfo   = $statusLabels[$statusKey] ?? ['text' => $statusKey, 'icon' => 'bi-question-circle', 'class' => 'admin-return-badge--pending'];
                                $formattedDate = $orderDate !== '' ? date('d/m/Y H:i', strtotime($orderDate)) : '—';
                            ?>
                            <tr>
                                <td class="text-center"><span class="admin-id-badge fw-bold">#<?= $orderId ?></span></td>
                                <td><strong><?= htmlspecialchars($customerName !== '' ? $customerName : 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= $phone !== '' ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                <td><?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="admin-return-reason" title="<?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($reason !== '' ? $reason : 'Chưa cung cấp lý do hoàn hàng', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="admin-return-badge <?= htmlspecialchars($statusInfo['class'], ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi <?= htmlspecialchars($statusInfo['icon'], ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i>
                                        <?= htmlspecialchars($statusInfo['text'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>?action=admin-return-detail&amp;id=<?= $orderId ?>"
                                       class="admin-btn-action admin-btn-action-view"
                                       title="Xem chi tiết yêu cầu hoàn hàng #<?= $orderId ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                        <span>Xem chi tiết</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-return-empty">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <strong>Chưa có yêu cầu hoàn hàng nào</strong>
                <span>
                    <?php if ($keyword !== '' || $status !== ''): ?>
                        Không có yêu cầu nào khớp với điều kiện tìm kiếm hiện tại.
                    <?php else: ?>
                        Các yêu cầu hoàn hàng từ khách hàng sẽ xuất hiện tại đây.
                    <?php endif; ?>
                </span>
                <?php if ($keyword !== '' || $status !== ''): ?>
                    <a href="<?= BASE_URL ?>?action=admin-returns" class="admin-btn admin-btn-outline">
                        <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i> Xem tất cả
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
