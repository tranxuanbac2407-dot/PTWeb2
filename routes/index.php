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

    // Khu vực quản trị (mọi route đều được AdminController kiểm tra quyền)
    'admin'                 => (new AdminController)->dashboard(),
    'admin-categories'      => (new AdminController)->categories(),
    'admin-category-create' => (new AdminController)->categoryCreate(),
    'admin-category-edit'   => (new AdminController)->categoryEdit(),
    'admin-category-delete' => (new AdminController)->categoryDelete(),
    'admin-products'        => (new AdminController)->products(),
    'admin-users'           => (new AdminController)->users(),
    'admin-orders'          => (new AdminController)->orders(),

    default    => (new HomeController)->notFound(),
};
