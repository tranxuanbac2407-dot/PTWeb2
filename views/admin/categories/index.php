<?php
require_once __DIR__ . '/../partials/guard.php';

$keyword = $keyword ?? '';
$categories = $categories ?? [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
?>

<div class="admin-category-page">

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

    <!-- Thanh công cụ điều khiển -->
    <div class="admin-toolbar">
        <div class="admin-toolbar-left">
            <form class="admin-search-form" action="<?= BASE_URL ?>" method="get" role="search">
                <input type="hidden" name="action" value="admin-categories">
                <div class="admin-search-input-group">
                    <i class="bi bi-search admin-search-icon" aria-hidden="true"></i>
                    <input type="text"
                           name="keyword"
                           class="admin-form-input admin-search-input"
                           placeholder="Tìm kiếm danh mục theo tên..."
                           value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($keyword !== ''): ?>
                        <a href="<?= BASE_URL ?>?action=admin-categories" class="admin-search-clear" title="Xóa bộ lọc">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="admin-btn admin-btn-secondary">Tìm kiếm</button>
                </div>
            </form>
        </div>

        <div class="admin-toolbar-right">
            <a href="<?= BASE_URL ?>?action=admin-category-create" class="admin-btn admin-btn-accent">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                <span>Thêm danh mục mới</span>
            </a>
        </div>
    </div>

    <!-- Bảng danh sách danh mục -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-tags" aria-hidden="true"></i>
                <span>Danh sách danh mục</span>
                <span class="admin-count-badge"><?= count($categories) ?> danh mục</span>
            </h3>

            <?php if ($keyword !== ''): ?>
                <span class="admin-filter-badge">
                    Đang lọc theo: "<strong><?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?></strong>"
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($categories)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;" class="text-center">Mã ID</th>
                            <th style="min-width: 200px;">Tên danh mục</th>
                            <th>Mô tả</th>
                            <th style="width: 140px;" class="text-center">Số sản phẩm</th>
                            <th style="width: 160px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <?php
                                $cId    = (int) ($cat['categories_id'] ?? 0);
                                $cName  = (string) ($cat['categories_name'] ?? '');
                                $cDesc  = (string) ($cat['description'] ?? '');
                                $cCount = (int) ($cat['product_count'] ?? 0);
                            ?>
                            <tr>
                                <td class="text-center">
                                    <span class="admin-id-badge">#<?= $cId ?></span>
                                </td>
                                <td>
                                    <div class="admin-category-cell">
                                        <div class="admin-category-icon">
                                            <i class="bi bi-tag-fill" aria-hidden="true"></i>
                                        </div>
                                        <div class="admin-category-text">
                                            <strong class="admin-category-name"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-category-desc">
                                        <?= !empty($cDesc) ? htmlspecialchars($cDesc, ENT_QUOTES, 'UTF-8') : '<em class="text-muted">Chưa có mô tả</em>' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($cCount > 0): ?>
                                        <span class="badge-count badge-count-active" title="<?= $cCount ?> sản phẩm liên kết">
                                            <i class="bi bi-box-seam"></i> <?= $cCount ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-count badge-count-empty" title="Chưa có sản phẩm nào">
                                            0
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="admin-row-actions">
                                        <a href="<?= BASE_URL ?>?action=admin-category-edit&amp;id=<?= $cId ?>"
                                           class="admin-btn-action admin-btn-action-edit"
                                           title="Chỉnh sửa danh mục">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                            <span>Sửa</span>
                                        </a>

                                        <a href="<?= BASE_URL ?>?action=admin-category-delete&amp;id=<?= $cId ?>"
                                           class="admin-btn-action admin-btn-action-delete"
                                           title="Xóa danh mục"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục &quot;<?= addslashes(htmlspecialchars($cName, ENT_QUOTES, 'UTF-8')) ?>&quot; (ID: #<?= $cId ?>)?\nThao tác này không thể hoàn tác!');">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                            <span>Xóa</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty">
                <i class="bi bi-tags" aria-hidden="true"></i>
                <?php if ($keyword !== ''): ?>
                    <strong>Không tìm thấy danh mục nào phù hợp</strong>
                    <span>Không có danh mục nào chứa từ khóa "<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>". Hãy thử với từ khóa khác.</span>
                    <div style="margin-top: 1rem;">
                        <a href="<?= BASE_URL ?>?action=admin-categories" class="admin-btn admin-btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Xem tất cả danh mục
                        </a>
                    </div>
                <?php else: ?>
                    <strong>Chưa có danh mục sản phẩm nào</strong>
                    <span>Hệ thống chưa có danh mục sản phẩm nào. Bạn có thể bắt đầu tạo danh mục đầu tiên ngay bây giờ.</span>
                    <div style="margin-top: 1rem;">
                        <a href="<?= BASE_URL ?>?action=admin-category-create" class="admin-btn admin-btn-accent">
                            <i class="bi bi-plus-lg"></i> Thêm danh mục đầu tiên
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
