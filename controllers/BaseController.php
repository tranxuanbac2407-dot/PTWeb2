<?php

/**
 * BaseController - Các tiện ích dùng chung cho Controller.
 *
 * Hiện tại tập trung vào việc kiểm tra quyền truy cập khu vực Admin.
 * Quy ước đăng nhập của SPORTIX: $_SESSION['user'] là mảng gồm id, username, role.
 */
class BaseController
{
    /**
     * Bảo vệ khu vực Admin ở phía server.
     *
     * - Chưa đăng nhập: chuyển về trang đăng nhập hiện có.
     * - Đã đăng nhập nhưng role không phải 'admin': trả về 403, không cho xem nội dung.
     * - Hợp lệ: trả về thông tin người dùng trong session.
     */
    protected function requireAdmin()
    {
        $user = $_SESSION['user'] ?? null;

        // 1) Chưa đăng nhập -> chuyển tới trang đăng nhập của website.
        if (empty($user)) {
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        // 2) Đã đăng nhập nhưng không phải Admin -> từ chối truy cập.
        if (!is_array($user) || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);

            $title = 'Không có quyền truy cập | SPORTIX';
            $view  = '403';

            require_once PATH_VIEW_MAIN;
            exit;
        }

        // Đánh dấu phiên hiện tại đã đi qua kiểm tra quyền Admin.
        // Các View Admin dùng hằng này để chặn truy cập trực tiếp file View.
        if (!defined('SPORTIX_ADMIN_AREA')) {
            define('SPORTIX_ADMIN_AREA', true);
        }

        return $user;
    }

    /**
     * Điều hướng về một đường dẫn trong ứng dụng rồi dừng xử lý.
     */
    protected function redirect($path)
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }
}
