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
     * Trang quản lý sản phẩm (khung giao diện, chờ nhiệm vụ CRUD).
     */
    public function products()
    {
        $this->renderAdminPage(
            'products/index',
            'Quản lý sản phẩm | Quản trị SPORTIX',
            'Quản lý sản phẩm',
            'Danh sách sản phẩm đang kinh doanh tại SPORTIX.',
            'products'
        );
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
