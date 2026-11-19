<?php

define('BASE_URL',          'http://localhost/base-duanmau/');

define('PATH_ROOT',         __DIR__ . '/../');

define('PATH_VIEW',         PATH_ROOT . 'views/');

define('PATH_VIEW_MAIN',    PATH_ROOT . 'views/layouts/main.php');

define('PATH_VIEW_LAYOUT', PATH_ROOT . 'views/layouts/');

define('PATH_VIEW_ADMIN',        PATH_ROOT . 'views/admin/');

define('PATH_VIEW_ADMIN_LAYOUT', PATH_VIEW_ADMIN . 'layouts/');

define('PATH_VIEW_ADMIN_MAIN',   PATH_VIEW_ADMIN_LAYOUT . 'main.php');

define('BASE_ASSETS_UPLOADS',   BASE_URL . 'assets/uploads/');

define('PATH_ASSETS_UPLOADS',   PATH_ROOT . 'assets/uploads/');

define('PATH_CONTROLLER',       PATH_ROOT . 'controllers/');

define('PATH_MODEL',            PATH_ROOT . 'models/');

define('DB_HOST',     'localhost');
define('DB_PORT',     '3306');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME',     'ptweb');
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Cấu hình thanh toán online qua VietQR / Ngân hàng
define('BANK_ID',           'MB');            // Mã ngân hàng theo VietQR (MB, VCB, ACB, VPB, TCB,...)
define('BANK_NAME',         'MBBank (Ngân hàng Quân Đội)');
define('BANK_ACCOUNT_NO',   '0987654321');    // Số tài khoản ngân hàng của cửa hàng
define('BANK_ACCOUNT_NAME', 'SPORTIX STORE'); // Tên chủ tài khoản thụ hưởng

