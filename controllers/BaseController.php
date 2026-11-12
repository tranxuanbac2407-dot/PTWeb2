<?php

/**
 * BaseController - Các tiện ích dùng chung cho Controller.
 *
 * Quản lý xác thực quyền Admin, điều hướng, bảo mật CSRF và Flash messages.
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
            $this->setFlash('error', 'Vui lòng đăng nhập với tài khoản Admin để tiếp tục.');
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
        if (!defined('SPORTIX_ADMIN_AREA')) {
            define('SPORTIX_ADMIN_AREA', true);
        }

        return $user;
    }

    /**
     * Điều hướng về một đường dẫn trong ứng dụng rồi dừng xử lý.
     *
     * @param string $path
     */
    protected function redirect($path)
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    /**
     * Lấy hoặc tạo CSRF token cho phiên làm việc hiện tại.
     *
     * @return string
     */
    protected function csrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Xác thực CSRF token gửi lên từ biểu mẫu POST.
     *
     * @return bool
     */
    protected function csrfValid()
    {
        $token = $_POST['csrf_token'] ?? '';

        return !empty($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Lưu thông báo flash vào session (hiển thị 1 lần duy nhất).
     *
     * @param string $type Loại thông báo (success, error, warning, info)
     * @param string $message Nội dung thông báo
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash_' . $type] = $message;
    }

    /**
     * Lấy và xóa thông báo flash khỏi session.
     *
     * @param string $type
     * @return string|null
     */
    protected function getFlash($type)
    {
        $key = 'flash_' . $type;
        $msg = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);

        return $msg;
    }
}
