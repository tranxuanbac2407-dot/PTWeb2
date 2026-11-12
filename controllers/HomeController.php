<?php

class HomeController
{
    /**
     * Trang chủ website SPORTIX.
     * Tải dữ liệu Banner, Danh mục nổi bật, Sản phẩm mới và Sản phẩm bán chạy.
     */
    public function index()
    {
        $categoryModel = new CategoryModel();
        $productModel  = new ProductModel();

        // 1. Dữ liệu Banner trình chiếu
        $banners = [
            [
                'id'          => 1,
                'eyebrow'     => 'BỘ SƯU TẬP MỚI 2026',
                'title'       => 'Bứt Phá Giới Hạn <span class="accent">Nâng Tầm Phong Cách</span>',
                'subtitle'    => 'Trang phục & phụ kiện thể thao chính hãng, thiết kế khí động học giúp tối ưu hiệu suất thi đấu và tập luyện.',
                'link'        => BASE_URL . '?action=products',
                'btn_text'    => 'Khám phá ngay',
                'badge'       => 'HOT COLLECTION',
                'theme'       => 'banner-theme-dark',
                'image_bg'    => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=1400&auto=format&fit=crop&q=80',
            ],
            [
                'id'          => 2,
                'eyebrow'     => 'ƯU ĐÃI ĐẶC BIỆT',
                'title'       => 'Đại Tiệc Thể Thao <span class="accent">Giảm Đến 50%</span>',
                'subtitle'    => 'Hàng trăm sản phẩm áo thun, quần jogger và giày chạy bộ đang có mức giá hấp dẫn nhất mùa giải.',
                'link'        => BASE_URL . '?action=products&amp;filter=sale',
                'btn_text'    => 'Săn sale liền tay',
                'badge'       => 'SALE 50%',
                'theme'       => 'banner-theme-fire',
                'image_bg'    => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?w=1400&auto=format&fit=crop&q=80',
            ],
            [
                'id'          => 3,
                'eyebrow'     => 'GYM & RUNNING GEAR',
                'title'       => 'Chinh Phục Mọi <span class="accent">Đường Chạy</span>',
                'subtitle'    => 'Công nghệ vải Dry-Fit thấm hút mồ hôi siêu tốc, sợi co giãn 4 chiều cho chuyển động tự do không giới hạn.',
                'link'        => BASE_URL . '?action=products&amp;category=nam',
                'btn_text'    => 'Xem bộ sưu tập',
                'badge'       => 'PRO RUNNING',
                'theme'       => 'banner-theme-blue',
                'image_bg'    => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1400&auto=format&fit=crop&q=80',
            ],
        ];

        // 2. Danh mục nổi bật
        $featuredCategories = $categoryModel->getFeaturedCategories(6);

        // 3. Sản phẩm mới
        $newProducts = $productModel->getNewProducts(8);

        // 4. Sản phẩm bán chạy
        $bestSellingProducts = $productModel->getBestSellingProducts(8);

        $title = 'SPORTIX | Thời trang thể thao nam & nữ chính hãng';
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
