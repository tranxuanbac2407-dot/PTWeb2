<?php

/**
 * BaseController - Các tiện ích dùng chung cho Controller.
 *
 * Quản lý xác thực quyền Admin, điều hướng, bảo mật CSRF và Flash messages.
 */
class BaseController
{
    protected function requireAuthenticatedUser($redirectAction = '')
    {
        $user = $_SESSION['user'] ?? null;

        if ($user instanceof __PHP_Incomplete_Class || is_object($user)) {
            unset($_SESSION['user']);
            $user = null;
        }

        $userId = is_array($user) ? (int) ($user['id'] ?? 0) : 0;

        if (!is_array($user) || $userId <= 0) {
            $this->redirectToLogin($redirectAction);
        }

        $databaseUser = (new UserModel())->findById($userId);

        if (!$databaseUser || (int) ($databaseUser['id'] ?? 0) !== $userId) {
            unset($_SESSION['user']);
            $this->redirectToLogin($redirectAction);
        }

        return $databaseUser;
    }

    /**
     * Bảo vệ khu vực Admin ở phía server.
     *
     * - Chưa đăng nhập / session không hợp lệ: chuyển về trang đăng nhập.
     * - Đã đăng nhập nhưng role không phải 'admin': trả về 403.
     * - Hợp lệ: trả về thông tin người dùng trong session.
     */
    protected function requireAdmin()
    {
        $user = $_SESSION['user'] ?? null;

        // Session cũ/không hợp lệ (ví dụ object __PHP_Incomplete_Class) -> coi như chưa đăng nhập.
        if ($user instanceof __PHP_Incomplete_Class || is_object($user)) {
            unset($_SESSION['user']);
            $user = null;
        }

        if (empty($user) || !is_array($user)) {
            $this->setFlash('error', 'Vui lòng đăng nhập với tài khoản Admin để tiếp tục.');
            header('Location: ' . BASE_URL . '?action=login');
            exit;
        }

        if (($user['role'] ?? '') !== 'admin') {
            http_response_code(403);

            $title = 'Không có quyền truy cập | SPORTIX';
            $view  = '403';

            require_once PATH_VIEW_MAIN;
            exit;
        }

        if (!defined('SPORTIX_ADMIN_AREA')) {
            define('SPORTIX_ADMIN_AREA', true);
        }

        return $user;
    }

    private function redirectToLogin($redirectAction)
    {
        if (is_string($redirectAction) && in_array($redirectAction, ['checkout'], true)) {
            $_SESSION['login_redirect'] = $redirectAction;
        }

        $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục đặt hàng.';
        $this->redirect('?action=login');
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
     * @param string $type
     * @param string $message
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