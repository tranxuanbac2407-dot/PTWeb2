<?php

$action = $_GET['action'] ?? '/';

// Nhánh default tránh lỗi fatal khi truy cập route chưa được xây dựng,
// thay vào đó hiển thị trang thông báo (chờ các thành viên khác tích hợp).
match ($action) {
    '/'        => (new HomeController)->index(),
    'search'   => (new HomeController)->search(),
    'register' => (new AuthController)->register(),
    'login'    => (new AuthController)->login(),
    'logout'   => (new AuthController)->logout(),
    default    => (new HomeController)->notFound(),
};
