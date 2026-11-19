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
    'cart-qty'       => (new HomeController)->changeCartQty(),
    'cart-remove'    => (new HomeController)->removeFromCart(),
    'cart-clear'     => (new HomeController)->clearCart(),
    'checkout'       => (new HomeController)->checkout(),
    'place-order'    => (new HomeController)->placeOrder(),
    'order-success'  => (new HomeController)->orderSuccess(),
    'order-history'  => (new HomeController)->orderHistory(),
    'order-detail'   => (new HomeController)->orderDetailCustomer(),
    'order-return'   => (new HomeController)->requestReturn(),
    'confirm-payment'=> (new HomeController)->confirmPayment(),
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