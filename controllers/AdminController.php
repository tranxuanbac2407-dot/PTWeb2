<?php

/**
 * AdminController - Khu vực quản trị của SPORTIX.
 *
 * Mọi phương thức đều gọi requireAdmin() trước khi hiển thị nội dung,
 * nhờ đó các route Admin được bảo vệ ở phía server (không phụ thuộc vào View).
 */
class AdminController extends BaseController
{
    /**
     * Trang Dashboard quản trị.
     */
    public function dashboard()
    {
        $categoryModel = new CategoryModel();
        $totalCategories = count($categoryModel->getAll());

        $this->renderAdminPage(
            'dashboard',
            'Dashboard | Quản trị SPORTIX',
            'Dashboard',
            'Tổng quan hoạt động của cửa hàng SPORTIX.',
            'dashboard',
            [
                'totalCategories' => $totalCategories,
            ]
        );
    }

    /* =========================================================
       QUẢN LÝ DANH MỤC SẢN PHẨM (CRUD)
       ========================================================= */

    /**
     * Danh sách danh mục sản phẩm.
     */
    public function categories()
    {
        $categoryModel = new CategoryModel();
        $keyword = trim($_GET['keyword'] ?? '');

        $categories   = $categoryModel->getAllWithCount($keyword);
        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        $this->renderAdminPage(
            'categories/index',
            'Quản lý danh mục | Quản trị SPORTIX',
            'Quản lý danh mục',
            'Danh sách danh mục sản phẩm đang kinh doanh tại SPORTIX.',
            'categories',
            [
                'categories'   => $categories,
                'keyword'      => $keyword,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]
        );
    }

    /**
     * Thêm mới danh mục sản phẩm (GET: hiển thị form, POST: xử lý lưu).
     */
    public function categoryCreate()
    {
        $categoryModel = new CategoryModel();
        $errors = [];
        $old = [
            'categories_name' => '',
            'description'     => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['categories_name'] = trim($_POST['categories_name'] ?? '');
            $old['description']     = trim($_POST['description'] ?? '');

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            }

            // 2. Validate tên danh mục
            if ($old['categories_name'] === '') {
                $errors['categories_name'] = 'Vui lòng nhập tên danh mục.';
            } elseif (mb_strlen($old['categories_name']) > 100) {
                $errors['categories_name'] = 'Tên danh mục không được vượt quá 100 ký tự.';
            } elseif ($categoryModel->findByName($old['categories_name'])) {
                $errors['categories_name'] = 'Tên danh mục này đã tồn tại, vui lòng chọn tên khác.';
            }

            // Nếu không có lỗi, tiến hành lưu
            if (empty($errors)) {
                $newId = $categoryModel->create([
                    'categories_name' => $old['categories_name'],
                    'description'     => $old['description'],
                ]);

                if ($newId !== false) {
                    $this->setFlash('success', 'Thêm danh mục "' . htmlspecialchars($old['categories_name']) . '" thành công!');
                    $this->redirect('?action=admin-categories');
                } else {
                    $errors['general'] = 'Không thể lưu danh mục vào cơ sở dữ liệu. Vui lòng thử lại sau.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'categories/form',
            'Thêm danh mục mới | Quản trị SPORTIX',
            'Thêm danh mục mới',
            'Tạo mới phân loại danh mục sản phẩm cho cửa hàng.',
            'categories',
            [
                'isEdit'     => false,
                'category'   => null,
                'old'        => $old,
                'errors'     => $errors,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Sửa thông tin danh mục sản phẩm (GET: hiển thị form, POST: xử lý cập nhật).
     */
    public function categoryEdit()
    {
        $categoryModel = new CategoryModel();
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã danh mục không hợp lệ.');
            $this->redirect('?action=admin-categories');
        }

        $category = $categoryModel->getById($id);
        if (!$category) {
            $this->setFlash('error', 'Không tìm thấy danh mục yêu cầu.');
            $this->redirect('?action=admin-categories');
        }

        $errors = [];
        $old = [
            'categories_name' => $category['categories_name'] ?? '',
            'description'     => $category['description'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['categories_name'] = trim($_POST['categories_name'] ?? '');
            $old['description']     = trim($_POST['description'] ?? '');

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            }

            // 2. Validate tên danh mục
            if ($old['categories_name'] === '') {
                $errors['categories_name'] = 'Vui lòng nhập tên danh mục.';
            } elseif (mb_strlen($old['categories_name']) > 100) {
                $errors['categories_name'] = 'Tên danh mục không được vượt quá 100 ký tự.';
            } else {
                // Kiểm tra trùng tên loại trừ chính danh mục đang sửa
                $existing = $categoryModel->findByName($old['categories_name'], $id);
                if ($existing) {
                    $errors['categories_name'] = 'Tên danh mục này đã tồn tại, vui lòng chọn tên khác.';
                }
            }

            // Nếu không có lỗi, tiến hành cập nhật
            if (empty($errors)) {
                $updated = $categoryModel->update($id, [
                    'categories_name' => $old['categories_name'],
                    'description'     => $old['description'],
                ]);

                if ($updated !== false) {
                    $this->setFlash('success', 'Cập nhật danh mục #' . $id . ' thành công!');
                    $this->redirect('?action=admin-categories');
                } else {
                    $errors['general'] = 'Không thể cập nhật danh mục. Vui lòng thử lại.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'categories/form',
            'Chỉnh sửa danh mục #' . $id . ' | Quản trị SPORTIX',
            'Chỉnh sửa danh mục',
            'Cập nhật thông tin phân loại: ' . htmlspecialchars($category['categories_name'] ?? ''),
            'categories',
            [
                'isEdit'     => true,
                'category'   => $category,
                'old'        => $old,
                'errors'     => $errors,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Xóa danh mục sản phẩm.
     */
    public function categoryDelete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã danh mục không hợp lệ.');
            $this->redirect('?action=admin-categories');
        }

        $categoryModel = new CategoryModel();
        $category = $categoryModel->getById($id);

        if (!$category) {
            $this->setFlash('error', 'Danh mục không tồn tại hoặc đã bị xóa.');
            $this->redirect('?action=admin-categories');
        }

        // Kiểm tra ràng buộc khóa ngoại: nếu đang có sản phẩm thuộc danh mục thì chặn xóa
        $productCount = $categoryModel->countProducts($id);
        if ($productCount > 0) {
            $this->setFlash('error', 'Không thể xóa danh mục "' . htmlspecialchars($category['categories_name']) . '" vì đang có ' . $productCount . ' sản phẩm liên kết!');
            $this->redirect('?action=admin-categories');
        }

        // Thực hiện xóa
        $deleted = $categoryModel->delete($id);
        if ($deleted) {
            $this->setFlash('success', 'Đã xóa danh mục "' . htmlspecialchars($category['categories_name']) . '" thành công!');
        } else {
            $this->setFlash('error', 'Không thể xóa danh mục do lỗi hệ thống hoặc ràng buộc dữ liệu.');
        }

        $this->redirect('?action=admin-categories');
    }

    /* =========================================================
       CÁC TRANG QUẢN TRỊ KHÁC
       ========================================================= */

    /**
     * Danh sách sản phẩm trong khu vực quản trị.
     */
    public function products()
    {
        $this->requireAdmin();

        $productModel = new ProductModel();
        $products     = [];
        $loadError    = null;

        try {
            $products = $productModel->getAllWithCategory();
        } catch (Throwable $e) {
            $loadError = 'Không thể tải danh sách sản phẩm từ cơ sở dữ liệu. Vui lòng thử lại sau.';
        }

        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        $this->renderAdminPage(
            'products/index',
            'Quản lý sản phẩm | Quản trị SPORTIX',
            'Quản lý sản phẩm',
            'Danh sách sản phẩm đang kinh doanh tại SPORTIX.',
            'products',
            [
                'products'     => $products,
                'loadError'    => $loadError,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]
        );
    }

    /**
     * Thêm mới sản phẩm (GET: hiển thị form, POST: xử lý lưu).
     */
    public function productCreate()
    {
        $this->requireAdmin();

        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();

        $errors = [];
        $old = [
            'product_name'   => '',
            'category_id'    => '',
            'price'          => '',
            'description'    => '',
            'size'           => '',
            'color'          => '',
            'stock_quantity' => '',
        ];

        $categories = $categoryModel->getAll(false);
        $flashError = $this->getFlash('error');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['product_name']   = trim($_POST['product_name'] ?? '');
            $old['category_id']    = trim($_POST['category_id'] ?? '');
            $old['price']          = trim($_POST['price'] ?? '');
            $old['description']    = trim($_POST['description'] ?? '');
            $old['size']           = trim($_POST['size'] ?? '');
            $old['color']          = trim($_POST['color'] ?? '');
            $old['stock_quantity'] = trim($_POST['stock_quantity'] ?? '');

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            }

            // 2. Tên sản phẩm
            if ($old['product_name'] === '') {
                $errors['product_name'] = 'Vui lòng nhập tên sản phẩm.';
            } elseif (mb_strlen($old['product_name']) > 200) {
                $errors['product_name'] = 'Tên sản phẩm không được vượt quá 200 ký tự.';
            }

            // 3. Danh mục phải tồn tại trong CSDL
            $categoryId = filter_var($old['category_id'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($categoryId === false) {
                $errors['category_id'] = 'Vui lòng chọn danh mục sản phẩm.';
            } elseif (!$categoryModel->exists($categoryId)) {
                $errors['category_id'] = 'Danh mục đã chọn không tồn tại. Vui lòng chọn danh mục khác.';
            }

            // 4. Giá bán: số hợp lệ, lớn hơn 0
            if ($old['price'] === '' || !is_numeric($old['price'])) {
                $errors['price'] = 'Giá bán phải là số hợp lệ.';
            } elseif ((float) $old['price'] <= 0) {
                $errors['price'] = 'Giá bán phải lớn hơn 0.';
            } elseif ((float) $old['price'] > 9999999999.99) {
                $errors['price'] = 'Giá bán vượt quá giới hạn cho phép.';
            }

            // 5. Số lượng tồn kho: số nguyên không âm
            $stock = filter_var($old['stock_quantity'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0],
            ]);
            if ($stock === false) {
                $errors['stock_quantity'] = 'Số lượng tồn kho phải là số nguyên không âm.';
            }

            // 6. Kích thước / màu sắc không vượt quá độ dài cột
            if (mb_strlen($old['size']) > 50) {
                $errors['size'] = 'Kích thước không được vượt quá 50 ký tự.';
            }
            if (mb_strlen($old['color']) > 50) {
                $errors['color'] = 'Màu sắc không được vượt quá 50 ký tự.';
            }

            // 7. Hình ảnh (không bắt buộc)
            $imagePath = null;
            $hasUpload = isset($_FILES['image'])
                && is_array($_FILES['image'])
                && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if (empty($errors) && $hasUpload) {
                try {
                    $imagePath = $this->storeProductImage($_FILES['image']);
                } catch (Throwable $e) {
                    $errors['image'] = $e->getMessage();
                }
            }

            // 8. Lưu sản phẩm khi dữ liệu hợp lệ
            if (empty($errors)) {
                $created = $productModel->create([
                    'category_id'    => (int) $categoryId,
                    'product_name'   => $old['product_name'],
                    'price'          => (float) $old['price'],
                    'description'    => $old['description'],
                    'image'          => $imagePath,
                    'size'           => $old['size'],
                    'color'          => $old['color'],
                    'stock_quantity' => (int) $stock,
                ]);

                if ($created !== false) {
                    $this->setFlash('success', 'Thêm sản phẩm "' . $old['product_name'] . '" thành công!');
                    $this->redirect('?action=admin-products');
                }

                // Dọn file vừa upload nếu INSERT thất bại
                if (!empty($imagePath)) {
                    @unlink(PATH_ASSETS_UPLOADS . str_replace('/', DIRECTORY_SEPARATOR, $imagePath));
                }

                $errors['general'] = 'Không thể lưu sản phẩm vào cơ sở dữ liệu. Vui lòng thử lại sau.';
            }
        }

        $csrfToken = $this->csrfToken();

        $this->renderAdminPage(
            'products/create',
            'Thêm sản phẩm mới | Quản trị SPORTIX',
            'Thêm sản phẩm mới',
            'Tạo mới sản phẩm thể thao cho cửa hàng.',
            'products',
            [
                'categories'  => $categories,
                'old'         => $old,
                'errors'      => $errors,
                'csrfToken'   => $csrfToken,
                'flashError'  => $flashError,
            ]
        );
    }

    /**
     * Kiểm tra và lưu file hình ảnh sản phẩm vào assets/uploads/products.
     *
     * @param array $file Một phần tử trong $_FILES
     * @return string Đường dẫn tương đối lưu trong cột products.image
     * @throws Exception Khi file không hợp lệ hoặc lưu thất bại
     */
    private function storeProductImage(array $file)
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Dữ liệu hình ảnh không hợp lệ.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Tải hình ảnh lên không thành công. Vui lòng thử lại.');
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Không tìm thấy file hình ảnh tải lên.');
        }

        $maxSize = 3 * 1024 * 1024;
        $fileSize = (int) @filesize($file['tmp_name']);
        if ($fileSize <= 0) {
            throw new Exception('Hình ảnh tải lên không hợp lệ hoặc bị rỗng.');
        }
        if ($fileSize > $maxSize) {
            throw new Exception('Hình ảnh không được vượt quá 3MB.');
        }

        $allowed = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
        ];

        $originalName = (string) ($file['name'] ?? '');
        $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!isset($allowed[$extension])) {
            throw new Exception('Hình ảnh chỉ chấp nhận định dạng JPG, JPEG, PNG hoặc WEBP.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if ($mime !== $allowed[$extension]) {
            throw new Exception('Nội dung file không đúng định dạng hình ảnh cho phép.');
        }

        if (@getimagesize($file['tmp_name']) === false) {
            throw new Exception('File tải lên không phải là hình ảnh hợp lệ.');
        }

        $uploadDir = PATH_ASSETS_UPLOADS . 'products';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new Exception('Không thể tạo thư mục lưu hình ảnh.');
        }

        // Đổi tên an toàn, tránh trùng lặp và không giữ tên gốc của người dùng
        $safeName = 'product-' . bin2hex(random_bytes(8)) . '.' . $extension;

        try {
            return upload_file('products', [
                'name'     => $safeName,
                'tmp_name' => $file['tmp_name'],
            ]);
        } catch (Throwable $e) {
            throw new Exception('Không thể lưu hình ảnh sản phẩm. Vui lòng thử lại.');
        }
    }

    /**
     * Trang quản lý tài khoản người dùng (khung giao diện, chờ nhiệm vụ CRUD).
     */
    public function users()
    {
        $this->renderAdminPage(
            'users/index',
            'Quản lý tài khoản | Quản trị SPORTIX',
            'Quản lý tài khoản',
            'Danh sách tài khoản người dùng của hệ thống.',
            'users'
        );
    }

    /**
     * Trang quản lý đơn hàng (khung giao diện, chờ nhiệm vụ CRUD).
     */
    public function orders()
    {
        $this->renderAdminPage(
            'orders/index',
            'Quản lý đơn hàng | Quản trị SPORTIX',
            'Quản lý đơn hàng',
            'Theo dõi và xử lý đơn hàng của khách hàng.',
            'orders'
        );
    }

    /**
     * Kiểm tra quyền Admin rồi hiển thị một trang trong Admin Layout.
     *
     * @param string $view         Đường dẫn View tính từ thư mục views/admin/.
     * @param string $title        Tiêu đề <title> của trang.
     * @param string $pageTitle    Tiêu đề hiển thị trên Header Admin.
     * @param string $pageSubtitle Mô tả ngắn dưới tiêu đề.
     * @param string $activeMenu   Khóa menu đang chọn trên Sidebar.
     * @param array  $data         Mảng dữ liệu truyền sang View.
     */
    private function renderAdminPage($view, $title, $pageTitle, $pageSubtitle, $activeMenu, array $data = [])
    {
        // Bắt buộc kiểm tra quyền trước khi làm bất kỳ việc gì khác.
        $adminUser = $this->requireAdmin();

        // Trích xuất các biến để View sử dụng trực tiếp
        extract($data);

        $view = 'admin/' . $view;

        require_once PATH_VIEW_ADMIN_MAIN;
    }
}
