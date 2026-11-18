<?php
require_once __DIR__ . '/../partials/guard.php';

$isEdit     = $isEdit ?? false;
$product    = $product ?? null;
$categories = $categories ?? [];
$old        = $old ?? [];
$errors     = $errors ?? [];
$csrfToken  = $csrfToken ?? '';
$flashError = $flashError ?? null;

$pId = $product ? (int) ($product['product_id'] ?? 0) : 0;
$formAction = $isEdit
    ? BASE_URL . '?action=admin-product-edit&amp;id=' . $pId
    : BASE_URL . '?action=admin-product-create';

$valName  = $old['product_name'] ?? ($product['product_name'] ?? '');
$valCat   = (int) ($old['category_id'] ?? ($product['category_id'] ?? 0));
$valPrice = $old['price'] ?? ($product['price'] ?? '');
$valStock = $old['stock_quantity'] ?? ($product['stock_quantity'] ?? 0);
$valSize  = $old['size'] ?? ($product['size'] ?? '');
$valColor = $old['color'] ?? ($product['color'] ?? '');
$valDesc  = $old['description'] ?? ($product['description'] ?? '');
$valUrl   = $old['image_url'] ?? '';

$currentImage = $product['image'] ?? null;
$currentImgSrc = !empty($currentImage) ? get_product_image_url($currentImage) : '';
?>

<div class="admin-product-form-page">

    <!-- Nút quay lại danh sách -->
    <div class="admin-form-back">
        <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách sản phẩm</span>
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

    <form action="<?= $formAction ?>" method="post" enctype="multipart/form-data" novalidate id="productForm">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="row g-4">
            <!-- Cột thông tin sản phẩm & Quản lý ảnh -->
            <div class="<?= $isEdit ? 'col-12 col-lg-8' : 'col-12' ?>">

                <!-- 1. THÔNG TIN CHÍNH -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>
                            <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-plus-circle' ?>" aria-hidden="true"></i>
                            <span><?= $isEdit ? 'Chỉnh sửa sản phẩm #' . $pId : 'Thông tin sản phẩm mới' ?></span>
                        </h3>
                    </div>

                    <div class="admin-card-body">
                        <!-- Tên sản phẩm -->
                        <div class="admin-form-group">
                            <label for="product_name" class="admin-form-label">
                                Tên sản phẩm <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="product_name"
                                   name="product_name"
                                   class="admin-form-input <?= !empty($errors['product_name']) ? 'is-invalid' : '' ?>"
                                   placeholder="Ví dụ: Áo Thun Thể Thao Nam Pro Dry-Fit..."
                                   value="<?= htmlspecialchars($valName, ENT_QUOTES, 'UTF-8') ?>"
                                   maxlength="200"
                                   required
                                   autofocus>
                            <?php if (!empty($errors['product_name'])): ?>
                                <p class="admin-field-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    <?= htmlspecialchars($errors['product_name'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php else: ?>
                                <p class="admin-form-hint">Tên sản phẩm rõ ràng, không quá 200 ký tự.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Danh mục & Đơn giá -->
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="admin-form-group">
                                    <label for="category_id" class="admin-form-label">
                                        Danh mục <span class="text-danger">*</span>
                                    </label>
                                    <select id="category_id"
                                            name="category_id"
                                            class="admin-form-input <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>"
                                            required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <?php
                                                $cId   = (int) ($cat['categories_id'] ?? 0);
                                                $cName = (string) ($cat['categories_name'] ?? '');
                                            ?>
                                            <option value="<?= $cId ?>" <?= $valCat === $cId ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($errors['category_id'])): ?>
                                        <p class="admin-field-error">
                                            <i class="bi bi-exclamation-circle"></i>
                                            <?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="admin-form-group">
                                    <label for="price" class="admin-form-label">
                                        Đơn giá (VNĐ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="admin-input-group-currency">
                                        <input type="number"
                                               id="price"
                                               name="price"
                                               class="admin-form-input <?= !empty($errors['price']) ? 'is-invalid' : '' ?>"
                                               placeholder="Ví dụ: 289000"
                                               value="<?= htmlspecialchars((string) $valPrice, ENT_QUOTES, 'UTF-8') ?>"
                                               min="0"
                                               step="1000"
                                               required>
                                        <span class="admin-input-currency-suffix">đ</span>
                                    </div>
                                    <?php if (!empty($errors['price'])): ?>
                                        <p class="admin-field-error">
                                            <i class="bi bi-exclamation-circle"></i>
                                            <?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Số lượng tồn kho, Size, Màu sắc -->
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="admin-form-group">
                                    <label for="stock_quantity" class="admin-form-label">
                                        Tồn kho <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           id="stock_quantity"
                                           name="stock_quantity"
                                           class="admin-form-input <?= !empty($errors['stock_quantity']) ? 'is-invalid' : '' ?>"
                                           placeholder="Số lượng"
                                           value="<?= htmlspecialchars((string) $valStock, ENT_QUOTES, 'UTF-8') ?>"
                                           min="0"
                                           step="1"
                                           required>
                                    <?php if (!empty($errors['stock_quantity'])): ?>
                                        <p class="admin-field-error">
                                            <i class="bi bi-exclamation-circle"></i>
                                            <?= htmlspecialchars($errors['stock_quantity'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="admin-form-group">
                                    <label for="size" class="admin-form-label">
                                        Kích thước (Size)
                                    </label>
                                    <input type="text"
                                           id="size"
                                           name="size"
                                           class="admin-form-input <?= !empty($errors['size']) ? 'is-invalid' : '' ?>"
                                           placeholder="Ví dụ: S, M, L, XL"
                                           value="<?= htmlspecialchars($valSize, ENT_QUOTES, 'UTF-8') ?>"
                                           maxlength="50">
                                    <p class="admin-form-hint">Các size cách nhau bằng dấu phẩy</p>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="admin-form-group">
                                    <label for="color" class="admin-form-label">
                                        Màu sắc
                                    </label>
                                    <input type="text"
                                           id="color"
                                           name="color"
                                           class="admin-form-input <?= !empty($errors['color']) ? 'is-invalid' : '' ?>"
                                           placeholder="Ví dụ: Đen, Trắng, Đỏ"
                                           value="<?= htmlspecialchars($valColor, ENT_QUOTES, 'UTF-8') ?>"
                                           maxlength="50">
                                    <p class="admin-form-hint">Các màu cách nhau bằng dấu phẩy</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả sản phẩm -->
                        <div class="admin-form-group">
                            <label for="description" class="admin-form-label">
                                Mô tả chi tiết sản phẩm
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="5"
                                      class="admin-form-input admin-form-textarea <?= !empty($errors['description']) ? 'is-invalid' : '' ?>"
                                      placeholder="Mô tả chất liệu, tính năng, đặc điểm nổi bật và hướng dẫn bảo quản..."><?= htmlspecialchars($valDesc, ENT_QUOTES, 'UTF-8') ?></textarea>
                            <?php if (!empty($errors['description'])): ?>
                                <p class="admin-field-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    <?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 2. QUẢN LÝ HÌNH ẢNH SẢN PHẨM -->
                <div class="admin-card" style="margin-top: 1.5rem;">
                    <div class="admin-card-header">
                        <h3>
                            <i class="bi bi-card-image" aria-hidden="true"></i>
                            <span>Quản lý hình ảnh sản phẩm</span>
                        </h3>
                    </div>

                    <div class="admin-card-body">
                        <?php if (!empty($errors['image'])): ?>
                            <div class="admin-alert admin-alert-danger" style="margin-bottom: 1.25rem;">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Ảnh hiện tại nếu đang chỉnh sửa -->
                        <?php if ($isEdit && !empty($currentImgSrc)): ?>
                            <div class="admin-current-image-wrapper">
                                <label class="admin-form-label">Ảnh hiện tại của sản phẩm:</label>
                                <div class="admin-current-image-card">
                                    <div class="admin-current-image-preview">
                                        <img src="<?= htmlspecialchars($currentImgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="Ảnh hiện tại của <?= htmlspecialchars($valName, ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="admin-current-image-info">
                                        <p class="admin-current-image-path text-truncate" title="<?= htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi bi-link-45deg"></i>
                                            <?= htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <label class="admin-delete-image-checkbox">
                                            <input type="checkbox" name="delete_image" value="1" id="delete_image">
                                            <span class="text-danger">
                                                <i class="bi bi-trash3"></i> Xóa ảnh này (sản phẩm sẽ không còn ảnh)
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Chọn cách thêm / đổi ảnh -->
                        <div class="admin-image-upload-options">
                            <label class="admin-form-label">
                                <?= ($isEdit && !empty($currentImgSrc)) ? 'Thay đổi hình ảnh mới:' : 'Tải lên hoặc nhập liên kết ảnh:' ?>
                            </label>

                            <div class="row g-3">
                                <!-- Cách 1: Tải file từ máy tính -->
                                <div class="col-12 col-md-6">
                                    <div class="admin-upload-box">
                                        <div class="admin-upload-box-icon">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                        </div>
                                        <div class="admin-upload-box-text">
                                            <strong>Tải tệp ảnh từ máy</strong>
                                            <span>Hỗ trợ JPG, PNG, WEBP, GIF (tối đa 5MB)</span>
                                        </div>
                                        <input type="file"
                                               id="image_file"
                                               name="image_file"
                                               class="admin-file-input"
                                               accept="image/jpeg,image/png,image/webp,image/gif">
                                        <label for="image_file" class="admin-btn admin-btn-outline admin-btn-sm admin-choose-file-btn">
                                            <i class="bi bi-folder2-open"></i> Chọn tệp ảnh...
                                        </label>
                                        <p id="fileChosenName" class="admin-file-chosen-name text-truncate"></p>
                                    </div>
                                </div>

                                <!-- Cách 2: Nhập đường dẫn URL ảnh -->
                                <div class="col-12 col-md-6">
                                    <div class="admin-url-box">
                                        <div class="admin-upload-box-icon text-secondary">
                                            <i class="bi bi-globe"></i>
                                        </div>
                                        <div class="admin-upload-box-text">
                                            <strong>Hoặc nhập URL ảnh trực tiếp</strong>
                                            <span>Đường dẫn ảnh từ internet (Unsplash, CDN...)</span>
                                        </div>
                                        <input type="url"
                                               id="image_url"
                                               name="image_url"
                                               class="admin-form-input"
                                               placeholder="https://images.unsplash.com/..."
                                               value="<?= htmlspecialchars($valUrl, ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Khung xem trước ảnh mới thời gian thực (Live Preview) -->
                        <div class="admin-image-preview-section" id="previewSection" style="display: none;">
                            <label class="admin-form-label">
                                <i class="bi bi-eye"></i> Xem trước hình ảnh mới:
                            </label>
                            <div class="admin-image-preview-box">
                                <img id="imagePreview" src="" alt="Xem trước hình ảnh sản phẩm">
                                <button type="button" class="admin-btn-preview-clear" id="clearPreviewBtn" title="Hủy ảnh vừa chọn">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nút thao tác lưu -->
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-accent">
                        <i class="bi <?= $isEdit ? 'bi-check-lg' : 'bi-plus-lg' ?>" aria-hidden="true"></i>
                        <span><?= $isEdit ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></span>
                    </button>
                    <a href="<?= BASE_URL ?>?action=admin-products" class="admin-btn admin-btn-outline">
                        <span>Hủy bỏ</span>
                    </a>
                </div>

            </div>

            <?php if ($isEdit): ?>
            <!-- Cột phải: Thông tin bản ghi (chỉ hiển thị khi chỉnh sửa) -->
            <div class="col-12 col-lg-4">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Thông tin bản ghi</span>
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <p class="mb-2"><strong>Mã sản phẩm:</strong> #<?= $pId ?></p>
                        <p class="mb-3"><strong>Tên hiện tại:</strong> <?= htmlspecialchars($product['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= $pId ?>"
                           target="_blank"
                           class="admin-btn admin-btn-outline admin-btn-sm w-100">
                            <i class="bi bi-box-arrow-up-right"></i> Xem trên website
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </form>

</div>

<!-- JavaScript xử lý xem trước ảnh (Live Image Preview) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput      = document.getElementById('image_file');
    const urlInput       = document.getElementById('image_url');
    const previewSection = document.getElementById('previewSection');
    const previewImg     = document.getElementById('imagePreview');
    const clearBtn       = document.getElementById('clearPreviewBtn');
    const fileChosenName = document.getElementById('fileChosenName');
    const deleteImgCheck = document.getElementById('delete_image');

    // 1. Khi chọn file từ máy tính
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileChosenName.textContent = file.name;

                // Bỏ chọn xóa ảnh cũ nếu có
                if (deleteImgCheck) {
                    deleteImgCheck.checked = false;
                }

                // Xóa nội dung URL
                if (urlInput) {
                    urlInput.value = '';
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewSection.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Khi nhập hoặc dán link URL ảnh
    if (urlInput) {
        urlInput.addEventListener('input', function () {
            const url = this.value.trim();
            if (url !== '') {
                // Xóa file đã chọn nếu có
                if (fileInput) {
                    fileInput.value = '';
                }
                if (fileChosenName) {
                    fileChosenName.textContent = '';
                }
                if (deleteImgCheck) {
                    deleteImgCheck.checked = false;
                }

                previewImg.src = url;
                previewSection.style.display = 'block';
            } else if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                previewSection.style.display = 'none';
            }
        });
    }

    // 3. Khi bấm nút hủy ảnh xem trước
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (fileInput) {
                fileInput.value = '';
            }
            if (urlInput) {
                urlInput.value = '';
            }
            if (fileChosenName) {
                fileChosenName.textContent = '';
            }
            previewImg.src = '';
            previewSection.style.display = 'none';
        });
    }

    // 4. Khi chọn checkbox "Xóa ảnh"
    if (deleteImgCheck) {
        deleteImgCheck.addEventListener('change', function () {
            if (this.checked) {
                if (fileInput) fileInput.value = '';
                if (urlInput) urlInput.value = '';
                if (fileChosenName) fileChosenName.textContent = '';
                previewImg.src = '';
                previewSection.style.display = 'none';
            }
        });
    }
});
</script>
