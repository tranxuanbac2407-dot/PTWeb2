<?php
require_once __DIR__ . '/../partials/guard.php';

$categories = $categories ?? [];
$old        = $old ?? [];
$errors     = $errors ?? [];
$csrfToken  = $csrfToken ?? '';
$flashError = $flashError ?? null;

$valName     = (string) ($old['product_name'] ?? '');
$valCategory = (string) ($old['category_id'] ?? '');
$valPrice    = (string) ($old['price'] ?? '');
$valDesc     = (string) ($old['description'] ?? '');
$valSize     = (string) ($old['size'] ?? '');
$valColor    = (string) ($old['color'] ?? '');
$valStock    = (string) ($old['stock_quantity'] ?? '');
?>

<div class="admin-product-form-page">

    <!-- Nút quay lại danh sách -->
    <div class="admin-form-back">
        <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <span>Quay lại danh sách sản phẩm</span>
        </a>
    </div>

    <!-- Thông báo lỗi chung -->
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

    <?php if (empty($categories)): ?>
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
            <span>Chưa có danh mục nào trong cơ sở dữ liệu. Vui lòng thêm danh mục trước khi thêm sản phẩm.</span>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3>
                <i class="bi bi-plus-square" aria-hidden="true"></i>
                <span>Thông tin sản phẩm mới</span>
            </h3>
        </div>

        <div class="admin-card-body">
            <form action="<?= BASE_URL ?>?action=admin-product-create"
                  method="post"
                  enctype="multipart/form-data"
                  novalidate>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="admin-form-group">
                    <label for="product_name" class="admin-form-label">
                        Tên sản phẩm <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="product_name"
                           name="product_name"
                           class="admin-form-input <?= !empty($errors['product_name']) ? 'is-invalid' : '' ?>"
                           placeholder="Ví dụ: Áo thun thể thao nam Dry-Fit..."
                           value="<?= htmlspecialchars($valName, ENT_QUOTES, 'UTF-8') ?>"
                           maxlength="200"
                           required
                           autofocus>
                    <?php if (!empty($errors['product_name'])): ?>
                        <p class="admin-field-error">
                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                            <?= htmlspecialchars($errors['product_name'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php else: ?>
                        <p class="admin-form-hint">Tối đa 200 ký tự.</p>
                    <?php endif; ?>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="category_id" class="admin-form-label">
                            Danh mục sản phẩm <span class="text-danger">*</span>
                        </label>
                        <select id="category_id"
                                name="category_id"
                                class="admin-form-input <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>"
                                required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                    $catId   = (string) ($category['categories_id'] ?? '');
                                    $catName = (string) ($category['categories_name'] ?? '');
                                ?>
                                <option value="<?= htmlspecialchars($catId, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $valCategory === $catId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['category_id'])): ?>
                            <p class="admin-field-error">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="admin-form-group">
                        <label for="price" class="admin-form-label">
                            Giá bán (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               id="price"
                               name="price"
                               class="admin-form-input <?= !empty($errors['price']) ? 'is-invalid' : '' ?>"
                               placeholder="Ví dụ: 289000"
                               value="<?= htmlspecialchars($valPrice, ENT_QUOTES, 'UTF-8') ?>"
                               min="0.01"
                               step="0.01"
                               required>
                        <?php if (!empty($errors['price'])): ?>
                            <p class="admin-field-error">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php else: ?>
                            <p class="admin-form-hint">Giá bán phải là số lớn hơn 0.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="size" class="admin-form-label">Kích thước</label>
                        <input type="text"
                               id="size"
                               name="size"
                               class="admin-form-input <?= !empty($errors['size']) ? 'is-invalid' : '' ?>"
                               placeholder="Ví dụ: M,L,XL,XXL"
                               value="<?= htmlspecialchars($valSize, ENT_QUOTES, 'UTF-8') ?>"
                               maxlength="50">
                        <?php if (!empty($errors['size'])): ?>
                            <p class="admin-field-error">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <?= htmlspecialchars($errors['size'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="admin-form-group">
                        <label for="color" class="admin-form-label">Màu sắc</label>
                        <input type="text"
                               id="color"
                               name="color"
                               class="admin-form-input <?= !empty($errors['color']) ? 'is-invalid' : '' ?>"
                               placeholder="Ví dụ: Đen, Trắng, Xanh Navy"
                               value="<?= htmlspecialchars($valColor, ENT_QUOTES, 'UTF-8') ?>"
                               maxlength="50">
                        <?php if (!empty($errors['color'])): ?>
                            <p class="admin-field-error">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <?= htmlspecialchars($errors['color'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="stock_quantity" class="admin-form-label">
                        Số lượng tồn kho <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           id="stock_quantity"
                           name="stock_quantity"
                           class="admin-form-input <?= !empty($errors['stock_quantity']) ? 'is-invalid' : '' ?>"
                           placeholder="Ví dụ: 50"
                           value="<?= htmlspecialchars($valStock, ENT_QUOTES, 'UTF-8') ?>"
                           min="0"
                           step="1"
                           required>
                    <?php if (!empty($errors['stock_quantity'])): ?>
                        <p class="admin-field-error">
                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                            <?= htmlspecialchars($errors['stock_quantity'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php else: ?>
                        <p class="admin-form-hint">Số nguyên không âm. Nhập 0 nếu sản phẩm tạm hết hàng.</p>
                    <?php endif; ?>
                </div>

                <div class="admin-form-group">
                    <label for="image" class="admin-form-label">Hình ảnh sản phẩm</label>
                    <input type="file"
                           id="image"
                           name="image"
                           class="admin-form-input admin-form-file <?= !empty($errors['image']) ? 'is-invalid' : '' ?>"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <?php if (!empty($errors['image'])): ?>
                        <p class="admin-field-error">
                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                            <?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php else: ?>
                        <p class="admin-form-hint">Chấp nhận JPG, JPEG, PNG, WEBP. Dung lượng tối đa 3MB.</p>
                    <?php endif; ?>
                </div>

                <div class="admin-form-group">
                    <label for="description" class="admin-form-label">Mô tả sản phẩm</label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              class="admin-form-input admin-form-textarea"
                              placeholder="Mô tả chất liệu, kiểu dáng, công dụng của sản phẩm..."><?= htmlspecialchars($valDesc, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-accent">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <span>Lưu sản phẩm</span>
                    </button>

                    <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline">
                        Quay lại danh sách
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
