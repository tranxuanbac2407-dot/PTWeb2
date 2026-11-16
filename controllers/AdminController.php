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
        $productModel  = new ProductModel();

        $totalCategories = count($categoryModel->getAll());
        $totalProducts   = $productModel->countAll();

        $this->renderAdminPage(
            'dashboard',
            'Dashboard | Quản trị SPORTIX',
            'Dashboard',
            'Tổng quan hoạt động của cửa hàng SPORTIX.',
            'dashboard',
            [
                'totalCategories' => $totalCategories,
                'totalProducts'   => $totalProducts,
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
       QUẢN LÝ SẢN PHẨM (CRUD & QUẢN LÝ ẢNH)
       ========================================================= */

    /**
     * Danh sách sản phẩm (hỗ trợ tìm kiếm từ khóa và lọc theo danh mục).
     */
    public function products()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();

        $keyword    = trim($_GET['keyword'] ?? '');
        $categoryId = (int) ($_GET['category_id'] ?? 0);

        $products     = $productModel->getAllAdmin($keyword, $categoryId);
        $categories   = $categoryModel->getAll();
        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        $this->renderAdminPage(
            'products/index',
            'Quản lý sản phẩm | Quản trị SPORTIX',
            'Quản lý sản phẩm',
            'Danh sách sản phẩm thể thao đang kinh doanh tại SPORTIX.',
            'products',
            [
                'products'     => $products,
                'categories'   => $categories,
                'keyword'      => $keyword,
                'categoryId'   => $categoryId,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]
        );
    }

    /**
     * Thêm mới sản phẩm (GET: hiển thị form, POST: xử lý lưu kèm upload ảnh).
     */
    public function productCreate()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();
        $categories    = $categoryModel->getAll();

        $errors = [];
        $old = [
            'product_name'   => '',
            'category_id'    => 0,
            'price'          => '',
            'size'           => '',
            'color'          => '',
            'stock_quantity' => 0,
            'description'    => '',
            'image_url'      => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['product_name']   = trim($_POST['product_name'] ?? '');
            $old['category_id']    = (int) ($_POST['category_id'] ?? 0);
            $old['price']          = trim($_POST['price'] ?? '');
            $old['size']           = trim($_POST['size'] ?? '');
            $old['color']          = trim($_POST['color'] ?? '');
            $old['stock_quantity'] = trim($_POST['stock_quantity'] ?? '0');
            $old['description']    = trim($_POST['description'] ?? '');
            $old['image_url']      = trim($_POST['image_url'] ?? '');

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            }

            // 2. Validate tên sản phẩm
            if ($old['product_name'] === '') {
                $errors['product_name'] = 'Vui lòng nhập tên sản phẩm.';
            } elseif (mb_strlen($old['product_name']) > 200) {
                $errors['product_name'] = 'Tên sản phẩm không được vượt quá 200 ký tự.';
            }

            // 3. Validate danh mục
            if ($old['category_id'] <= 0) {
                $errors['category_id'] = 'Vui lòng chọn danh mục cho sản phẩm.';
            }

            // 4. Validate giá bán
            if ($old['price'] === '') {
                $errors['price'] = 'Vui lòng nhập đơn giá sản phẩm.';
            } elseif (!is_numeric($old['price']) || (float) $old['price'] < 0) {
                $errors['price'] = 'Giá bán phải là số hợp lệ và lớn hơn hoặc bằng 0.';
            }

            // 5. Validate số lượng tồn kho
            if ($old['stock_quantity'] === '' || !is_numeric($old['stock_quantity']) || (int) $old['stock_quantity'] < 0) {
                $errors['stock_quantity'] = 'Số lượng tồn kho phải là số nguyên không âm.';
            }

            // 6. Xử lý ảnh tải lên hoặc URL ảnh
            $finalImage = null;
            $imgError = null;

            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                if (!is_valid_image_upload($_FILES['image_file'], $imgError)) {
                    $errors['image'] = $imgError;
                } else {
                    try {
                        $finalImage = upload_file('products', $_FILES['image_file']);
                    } catch (Exception $e) {
                        $errors['image'] = $e->getMessage();
                    }
                }
            } elseif ($old['image_url'] !== '') {
                $finalImage = $old['image_url'];
            }

            // 7. Nếu không có lỗi, tiến hành lưu vào CSDL
            if (empty($errors)) {
                $newId = $productModel->create([
                    'product_name'   => $old['product_name'],
                    'category_id'    => $old['category_id'],
                    'price'          => (float) $old['price'],
                    'size'           => $old['size'] !== '' ? $old['size'] : null,
                    'color'          => $old['color'] !== '' ? $old['color'] : null,
                    'stock_quantity' => (int) $old['stock_quantity'],
                    'description'    => $old['description'] !== '' ? $old['description'] : null,
                    'image'          => $finalImage,
                ]);

                if ($newId !== false) {
                    $this->setFlash('success', 'Thêm sản phẩm "' . htmlspecialchars($old['product_name']) . '" thành công!');
                    $this->redirect('?action=admin-products');
                } else {
                    // Nếu lưu thất bại mà đã upload ảnh thì dọn dẹp file vừa upload
                    if (!empty($finalImage)) {
                        delete_upload_file($finalImage);
                    }
                    $errors['general'] = 'Không thể lưu sản phẩm vào cơ sở dữ liệu. Vui lòng thử lại sau.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'products/form',
            'Thêm sản phẩm mới | Quản trị SPORTIX',
            'Thêm sản phẩm mới',
            'Nhập thông tin sản phẩm và tải ảnh lên hệ thống.',
            'products',
            [
                'isEdit'     => false,
                'product'    => null,
                'categories' => $categories,
                'old'        => $old,
                'errors'     => $errors,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Chỉnh sửa và cập nhật sản phẩm (hỗ trợ quản lý, thay đổi và xóa ảnh).
     */
    public function productEdit()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();
        $categories    = $categoryModel->getAll();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('error', 'Mã sản phẩm không hợp lệ.');
            $this->redirect('?action=admin-products');
        }

        $product = $productModel->getById($id);
        if (!$product) {
            $this->setFlash('error', 'Không tìm thấy sản phẩm yêu cầu hoặc đã bị xóa.');
            $this->redirect('?action=admin-products');
        }

        $errors = [];
        $old = [
            'product_name'   => $product['product_name'] ?? '',
            'category_id'    => (int) ($product['category_id'] ?? 0),
            'price'          => $product['price'] ?? '',
            'size'           => $product['size'] ?? '',
            'color'          => $product['color'] ?? '',
            'stock_quantity' => $product['stock_quantity'] ?? 0,
            'description'    => $product['description'] ?? '',
            'image_url'      => (isset($product['image']) && (str_starts_with($product['image'], 'http://') || str_starts_with($product['image'], 'https://'))) ? $product['image'] : '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['product_name']   = trim($_POST['product_name'] ?? '');
            $old['category_id']    = (int) ($_POST['category_id'] ?? 0);
            $old['price']          = trim($_POST['price'] ?? '');
            $old['size']           = trim($_POST['size'] ?? '');
            $old['color']          = trim($_POST['color'] ?? '');
            $old['stock_quantity'] = trim($_POST['stock_quantity'] ?? '0');
            $old['description']    = trim($_POST['description'] ?? '');
            $old['image_url']      = trim($_POST['image_url'] ?? '');
            $deleteImage           = !empty($_POST['delete_image']);

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            }

            // 2. Validate tên sản phẩm
            if ($old['product_name'] === '') {
                $errors['product_name'] = 'Vui lòng nhập tên sản phẩm.';
            } elseif (mb_strlen($old['product_name']) > 200) {
                $errors['product_name'] = 'Tên sản phẩm không được vượt quá 200 ký tự.';
            }

            // 3. Validate danh mục
            if ($old['category_id'] <= 0) {
                $errors['category_id'] = 'Vui lòng chọn danh mục hợp lệ.';
            }

            // 4. Validate giá bán
            if ($old['price'] === '') {
                $errors['price'] = 'Vui lòng nhập đơn giá sản phẩm.';
            } elseif (!is_numeric($old['price']) || (float) $old['price'] < 0) {
                $errors['price'] = 'Giá bán phải là số hợp lệ và lớn hơn hoặc bằng 0.';
            }

            // 5. Validate số lượng tồn kho
            if ($old['stock_quantity'] === '' || !is_numeric($old['stock_quantity']) || (int) $old['stock_quantity'] < 0) {
                $errors['stock_quantity'] = 'Số lượng tồn kho phải là số nguyên không âm.';
            }

            // 6. Xử lý quản lý và cập nhật ảnh sản phẩm
            $finalImage = $product['image'] ?? null;
            $newUploadedFile = null;
            $shouldDeleteOldFile = false;

            if ($deleteImage) {
                // Người dùng chọn xóa ảnh hiện tại
                $finalImage = null;
                $shouldDeleteOldFile = true;
            } elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Người dùng tải tệp ảnh mới lên từ máy
                $imgError = null;
                if (!is_valid_image_upload($_FILES['image_file'], $imgError)) {
                    $errors['image'] = $imgError;
                } else {
                    try {
                        $newUploadedFile = upload_file('products', $_FILES['image_file']);
                        $finalImage = $newUploadedFile;
                        $shouldDeleteOldFile = true;
                    } catch (Exception $e) {
                        $errors['image'] = $e->getMessage();
                    }
                }
            } elseif ($old['image_url'] !== '' && $old['image_url'] !== ($product['image'] ?? '')) {
                // Người dùng nhập link URL ảnh mới
                $finalImage = $old['image_url'];
                $shouldDeleteOldFile = true;
            }

            // 7. Cập nhật vào CSDL nếu không có lỗi
            if (empty($errors)) {
                $updated = $productModel->update($id, [
                    'product_name'   => $old['product_name'],
                    'category_id'    => $old['category_id'],
                    'price'          => (float) $old['price'],
                    'size'           => $old['size'] !== '' ? $old['size'] : null,
                    'color'          => $old['color'] !== '' ? $old['color'] : null,
                    'stock_quantity' => (int) $old['stock_quantity'],
                    'description'    => $old['description'] !== '' ? $old['description'] : null,
                    'image'          => $finalImage,
                ]);

                if ($updated !== false) {
                    // Nếu cập nhật thành công và cần dọn dẹp ảnh cũ
                    if ($shouldDeleteOldFile && !empty($product['image'])) {
                        delete_upload_file($product['image']);
                    }

                    $this->setFlash('success', 'Cập nhật sản phẩm #' . $id . ' thành công!');
                    $this->redirect('?action=admin-products');
                } else {
                    // Nếu lỗi CSDL và vừa upload ảnh mới, dọn dẹp file vừa upload
                    if ($newUploadedFile) {
                        delete_upload_file($newUploadedFile);
                    }
                    $errors['general'] = 'Không thể cập nhật sản phẩm vào cơ sở dữ liệu. Vui lòng thử lại.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'products/form',
            'Chỉnh sửa sản phẩm #' . $id . ' | Quản trị SPORTIX',
            'Chỉnh sửa sản phẩm',
            'Cập nhật thông tin, giá bán và hình ảnh: ' . htmlspecialchars($product['product_name'] ?? ''),
            'products',
            [
                'isEdit'     => true,
                'product'    => $product,
                'categories' => $categories,
                'old'        => $old,
                'errors'     => $errors,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Xóa sản phẩm (kiểm tra đơn hàng liên kết và tự động xóa ảnh trên đĩa).
     */
    public function productDelete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã sản phẩm không hợp lệ.');
            $this->redirect('?action=admin-products');
        }

        $productModel = new ProductModel();
        $product = $productModel->getById($id);

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại hoặc đã bị xóa trước đó.');
            $this->redirect('?action=admin-products');
        }

        // Kiểm tra ràng buộc khóa ngoại với đơn hàng
        $orderCount = $productModel->countInOrders($id);
        if ($orderCount > 0) {
            $this->setFlash('error', 'Không thể xóa sản phẩm "' . htmlspecialchars($product['product_name']) . '" vì đang nằm trong ' . $orderCount . ' đơn hàng của khách hàng!');
            $this->redirect('?action=admin-products');
        }

        // Thực hiện xóa sản phẩm
        $deleted = $productModel->delete($id);
        if ($deleted) {
            // Tự động xóa file ảnh vật lý trên máy chủ nếu là ảnh upload nội bộ
            if (!empty($product['image'])) {
                delete_upload_file($product['image']);
            }
            $this->setFlash('success', 'Đã xóa sản phẩm "' . htmlspecialchars($product['product_name']) . '" (ID: #' . $id . ') thành công!');
        } else {
            $this->setFlash('error', 'Không thể xóa sản phẩm do ràng buộc dữ liệu hoặc lỗi hệ thống.');
        }

        $this->redirect('?action=admin-products');
    }

    /* =========================================================
       CÁC TRANG QUẢN TRỊ KHÁC
       ========================================================= */

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
