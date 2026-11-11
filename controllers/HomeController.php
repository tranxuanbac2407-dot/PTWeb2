<?php

class HomeController
{
    public function index()
    {
        $title = 'SPORTIX | Thời trang thể thao nam & nữ';
        $view  = 'home';

        require_once PATH_VIEW_MAIN;
    }

    public function search()
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $products = [];

        if ($keyword !== '') {
            $productModel = new ProductModel();
            $products = $productModel->searchByKeyword($keyword);
        }

        $title = 'Tìm kiếm "' . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . '" | SPORTIX';
        $view  = 'search';

        require_once PATH_VIEW_MAIN;
    }

    public function notFound()
    {
        http_response_code(404);

        $title = 'Không tìm thấy trang | SPORTIX';
        $view  = '404';

        require_once PATH_VIEW_MAIN;
    }
}
