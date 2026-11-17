<?php

$action = $_GET['action'] ?? '/';

// Nhánh default tránh lỗi fatal khi truy cập route chưa được xây dựng,
// thay vào đó hiển thị trang thông báo (chờ các thành viên khác tích hợp).
match ($action) {
    '/'              => (new HomeController)->index(),
    'search'         => (new HomeController)->search(),
    'product-detail' => (new HomeController)->productDetail(),
    'cart'           => (new HomeController)->cart(),
    'add-to-cart'    => (new HomeController)->addToCart(),
    'cart-update'    => (new HomeController)->updateCart(),
    'cart-remove'    => (new HomeController)->removeFromCart(),
    'register'       => (new AuthController)->register(),
    'login'    => (new AuthController)->login(),
    'logout'   => (new AuthController)->logout(),

    // Khu vực quản trị (mỗi route đều được AdminController kiểm tra quyền)
    'admin'                 => (new AdminController)->dashboard(),
    'admin-categories'      => (new AdminController)->categories(),
    'admin-category-create' => (new AdminController)->categoryCreate(),
    'admin-category-edit'   => (new AdminController)->categoryEdit(),
    'admin-category-delete' => (new AdminController)->categoryDelete(),
    'admin-products'        => (new AdminController)->products(),
    'admin-product-create'  => (new AdminController)->productCreate(),
    'admin-product-edit'    => (new AdminController)->productEdit(),
    'admin-product-delete'  => (new AdminController)->productDelete(),
    'admin-users'               => (new AdminController)->users(),
    'admin-user-delete'         => (new AdminController)->userDelete(),
    'admin-orders'              => (new AdminController)->orders(),
    'admin-order-detail'        => (new AdminController)->orderDetail(),
    'admin-order-update-status' => (new AdminController)->orderUpdateStatus(),

    default    => (new HomeController)->notFound(),
};