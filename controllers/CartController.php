<?php
class CartController {
    public function index() {
        $title = 'Giỏ hàng | SPORTIX';
        $view = 'cart/index';
        require_once PATH_VIEW_MAIN;
    }

    public function checkout() {
        $title = 'Thanh toán | SPORTIX';
        $view = 'cart/checkout';
        require_once PATH_VIEW_MAIN;
    }
}
