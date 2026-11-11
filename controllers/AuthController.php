<?php

/**
 * AuthController - Xác thực người dùng: đăng ký, đăng nhập, đăng xuất.
 *
 * - Tiếp nhận & kiểm tra dữ liệu biểu mẫu (server-side).
 * - Gọi UserModel để truy vấn / ghi database.
 * - Quản lý trạng thái đăng nhập qua $_SESSION['user'] (đúng quy ước với Header).
 * - Giữ nguyên $_SESSION['cart'] của chức năng giỏ hàng.
 */
class AuthController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /* =========================================================
       ĐĂNG KÝ
       ========================================================= */

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
            return;
        }

        $this->showRegister();
    }

    /**
     * Hiển thị trang đăng ký (kèm lỗi và dữ liệu cũ nếu có).
     */
    private function showRegister(array $errors = [], array $old = [])
    {
        $title     = 'Đăng ký tài khoản | SPORTIX';
        $view      = 'auth/register';
        $csrfToken = $this->csrfToken();

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xử lý dữ liệu đăng ký (POST).
     */
    private function handleRegister()
    {
        $old = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'username'  => trim($_POST['username'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
        ];

        $password        = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        $errors = [];

        // CSRF
        if (!$this->csrfValid()) {
            $errors['general'] = 'Phiên làm việc đã hết hạn, vui lòng gửi lại biểu mẫu.';
        }

        // 1. Trường bắt buộc: họ tên
        if ($old['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên.';
        } elseif (mb_strlen($old['full_name']) > 255) {
            $errors['full_name'] = 'Họ và tên không được vượt quá 255 ký tự.';
        }

        // 2. Tên đăng nhập: độ dài và ký tự hợp lệ
        if ($old['username'] === '') {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $old['username'])) {
            $errors['username'] = 'Tên đăng nhập gồm 3-30 ký tự chữ, số hoặc dấu gạch dưới.';
        }

        // 3. Email đúng định dạng
        if ($old['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        // 4. Số điện thoại (không bắt buộc, chỉ kiểm tra khi có nhập)
        if ($old['phone'] !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $old['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        // 5. Mật khẩu tối thiểu 6 ký tự
        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải có tối thiểu 6 ký tự.';
        }

        // 6. Mật khẩu nhập lại phải trùng khớp
        if ($passwordConfirm === '') {
            $errors['password_confirm'] = 'Vui lòng nhập lại mật khẩu.';
        } elseif ($passwordConfirm !== $password) {
            $errors['password_confirm'] = 'Mật khẩu nhập lại không khớp.';
        }

        // 7. Tên đăng nhập chưa tồn tại
        if (!isset($errors['username']) && $this->userModel->usernameExists($old['username'])) {
            $errors['username'] = 'Tên đăng nhập đã tồn tại, vui lòng chọn tên khác.';
        }

        // 8. Email chưa tồn tại
        if (!isset($errors['email']) && $this->userModel->emailExists($old['email'])) {
            $errors['email'] = 'Email này đã được sử dụng.';
        }

        // Có lỗi -> hiển thị lại biểu mẫu kèm dữ liệu hợp lệ (không lưu, không giữ mật khẩu)
        if (!empty($errors)) {
            $this->showRegister($errors, $old);
            return;
        }

        // Hợp lệ -> lưu tài khoản (mật khẩu đã hash, vai trò mặc định client)
        $this->userModel->create([
            'full_name' => $old['full_name'],
            'username'  => $old['username'],
            'email'     => $old['email'],
            'phone'     => $old['phone'] !== '' ? $old['phone'] : null,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'role'      => 'client',
        ]);

        $_SESSION['flash_success'] = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.';
        $this->redirect('?action=login');
    }

    /* =========================================================
       ĐĂNG NHẬP
       ========================================================= */

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }

        $this->showLogin();
    }

    /**
     * Hiển thị trang đăng nhập.
     */
    private function showLogin(array $errors = [], array $old = [])
    {
        $title     = 'Đăng nhập | SPORTIX';
        $view      = 'auth/login';
        $csrfToken = $this->csrfToken();
        $success   = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xử lý đăng nhập (POST).
     */
    private function handleLogin()
    {
        $old      = ['login' => trim($_POST['login'] ?? '')];
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];

        if (!$this->csrfValid()) {
            $errors['general'] = 'Phiên làm việc đã hết hạn, vui lòng gửi lại biểu mẫu.';
        }

        if ($old['login'] === '') {
            $errors['login'] = 'Vui lòng nhập email hoặc tên đăng nhập.';
        }

        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        if (empty($errors)) {
            $user = $this->userModel->findByLogin($old['login']);

            // Không tiết lộ tài khoản có tồn tại hay không: dùng chung một thông báo.
            if (!$user || !password_verify($password, $user['password'])) {
                $errors['general'] = 'Email/tên đăng nhập hoặc mật khẩu không đúng.';
            } else {
                // Đăng nhập thành công -> chống session fixation
                session_regenerate_id(true);

                // Quy ước với Header SPORTIX (giữ nguyên khóa 'user')
                $_SESSION['user'] = [
                    'id'       => (int) $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'] ?? null,
                    'role'     => $user['role'],
                ];

                // $_SESSION['cart'] được giữ nguyên (không đụng tới).
                $this->redirect('');
            }
        }

        $this->showLogin($errors, $old);
    }

    /* =========================================================
       ĐĂNG XUẤT
       ========================================================= */

    public function logout()
    {
        // Chỉ xóa trạng thái đăng nhập, KHÔNG xóa giỏ hàng.
        unset($_SESSION['user']);

        // Đổi id phiên để tránh session fixation.
        session_regenerate_id(true);

        $this->redirect('');
    }

    /* =========================================================
       TIỆN ÍCH DÙNG CHUNG
       ========================================================= */

    /**
     * Lấy (hoặc tạo) CSRF token cho phiên hiện tại.
     */
    private function csrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Kiểm tra CSRF token gửi lên từ biểu mẫu POST.
     */
    private function csrfValid()
    {
        $token = $_POST['csrf_token'] ?? '';

        return !empty($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Điều hướng về một đường dẫn trong ứng dụng rồi dừng xử lý.
     */
    private function redirect($path)
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }
}
