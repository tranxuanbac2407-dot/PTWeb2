<?php

/**
 * Chặn truy cập trực tiếp vào các file View Admin.
 *
 * Hằng SPORTIX_ADMIN_AREA chỉ được định nghĩa sau khi BaseController::requireAdmin()
 * xác nhận người dùng đã đăng nhập và có role 'admin'.
 */
if (!defined('SPORTIX_ADMIN_AREA')) {
    http_response_code(403);
    exit('Bạn không có quyền truy cập khu vực quản trị.');
}
