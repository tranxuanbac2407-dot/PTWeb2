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
     * Giai đoạn này chỉ hiển thị giao diện, chưa truy vấn số liệu thống kê.
     */
    public function dashboard()
    {
        $this->renderAdminPage(
            'dashboard',
            'Dashboard | Quản trị SPORTIX',
            'Dashboard',
            'Tổng quan hoạt động của cửa hàng SPORTIX.',
            'dashboard'
        );
    }

    /**
     * Trang quản lý danh mục sản phẩm (khung giao diện, chờ nhiệm vụ CRUD).
     */
    public function categories()
    {
        $this->renderAdminPage(
            'categories/index',
            'Quản lý danh mục | Quản trị SPORTIX',
            'Quản lý danh mục',
            'Danh sách danh mục sản phẩm của cửa hàng.',
            'categories'
        );
    }

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
     */
    private function renderAdminPage($view, $title, $pageTitle, $pageSubtitle, $activeMenu)
    {
        // Bắt buộc kiểm tra quyền trước khi làm bất kỳ việc gì khác.
        $adminUser = $this->requireAdmin();

        $view = 'admin/' . $view;

        require_once PATH_VIEW_ADMIN_MAIN;
    }
}
