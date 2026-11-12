<?php
require_once __DIR__ . '/../partials/guard.php';

$isEdit     = $isEdit ?? false;
$category   = $category ?? null;
$old        = $old ?? [];
$errors     = $errors ?? [];
$csrfToken  = $csrfToken ?? '';
$flashError = $flashError ?? null;

$catId = $category ? (int) ($category['categories_id'] ?? 0) : 0;
$formAction = $isEdit
    ? BASE_URL . '?action=admin-category-edit&amp;id=' . $catId
    : BASE_URL . '?action=admin-category-create';

$valName = $old['categories_name'] ?? ($category['categories_name'] ?? '');
$valDesc = $old['description'] ?? ($category['description'] ?? '');
?>

<div class="admin-category-form-page">

    <!-- Nút quay lại danh sách -->
    <div class="admin-form-back">
        <a href="<?= BASE_URL ?>?action=admin-categories" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách danh mục</span>
        </a>
    </div>

    <!-- Thông báo lỗi chung nếu có -->
    <?php if (!empty($flashError)): ?>
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Khối biểu mẫu chính -->
        <div class="col-12 col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>
                        <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-folder-plus' ?>" aria-hidden="true"></i>
                        <span><?= $isEdit ? 'Chỉnh sửa thông tin danh mục #' . $catId : 'Thông tin danh mục mới' ?></span>
                    </h3>
                </div>

                <div class="admin-card-body">
                    <form action="<?= $formAction ?>" method="post" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                        <!-- Tên danh mục -->
                        <div class="admin-form-group">
                            <label for="categories_name" class="admin-form-label">
                                Tên danh mục <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="categories_name"
                                   name="categories_name"
                                   class="admin-form-input <?= !empty($errors['categories_name']) ? 'is-invalid' : '' ?>"
                                   placeholder="Ví dụ: Áo thể thao nam, Giày chạy bộ..."
                                   value="<?= htmlspecialchars($valName, ENT_QUOTES, 'UTF-8') ?>"
                                   maxlength="100"
                                   required
                                   autofocus>
                            <?php if (!empty($errors['categories_name'])): ?>
                                <p class="admin-field-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    <?= htmlspecialchars($errors['categories_name'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php else: ?>
                                <p class="admin-form-hint">Tối đa 100 ký tự. Tên danh mục nên ngắn gọn, dễ nhớ và không trùng lặp.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Mô tả danh mục -->
                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">
                                Mô tả danh mục
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="4"
                                      class="admin-form-input admin-form-textarea <?= !empty($errors['description']) ? 'is-invalid' : '' ?>"
                                      placeholder="Mô tả tóm tắt về loại sản phẩm hoặc nhóm đối tượng khách hàng của danh mục này..."><?= htmlspecialchars($valDesc, ENT_QUOTES, 'UTF-8') ?></textarea>
                            <?php if (!empty($errors['description'])): ?>
                                <p class="admin-field-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    <?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php else: ?>
                                <p class="admin-form-hint">Mô tả giúp khách hàng và nhân viên quản trị hiểu rõ hơn về các sản phẩm trong danh mục.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Các nút hành động -->
                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-accent">
                                <i class="bi <?= $isEdit ? 'bi-check-lg' : 'bi-plus-circle' ?>"></i>
                                <span><?= $isEdit ? 'Lưu thay đổi' : 'Thêm danh mục' ?></span>
                            </button>

                            <a href="<?= BASE_URL ?>?action=admin-categories" class="admin-btn admin-btn-outline">
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Khối hướng dẫn / lưu ý -->
        <div class="col-12 col-lg-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>
                        <i class="bi bi-info-circle-fill text-accent" aria-hidden="true"></i>
                        <span>Lưu ý khi quản lý</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <ul class="admin-tips-list">
                        <li>
                            <strong>Tên danh mục duy nhất:</strong>
                            <span>Không được trùng với tên danh mục đã có trong hệ thống để tránh nhầm lẫn khi phân loại sản phẩm.</span>
                        </li>
                        <li>
                            <strong>Ràng buộc dữ liệu:</strong>
                            <span>Khi danh mục đang có sản phẩm liên kết, hệ thống sẽ bảo vệ và không cho phép xóa để bảo đảm toàn vẹn dữ liệu đơn hàng và sản phẩm.</span>
                        </li>
                        <li>
                            <strong>Hiển thị trên website:</strong>
                            <span>Các danh mục có sản phẩm sẽ được tự động đồng bộ hiển thị lên menu điều hướng và mục Danh mục nổi bật tại trang chủ SPORTIX.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
