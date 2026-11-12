<?php

class ProductModel extends BaseModel
{
    protected $table = 'products';

    /**
     * Tìm kiếm sản phẩm theo từ khóa.
     *
     * @param string $keyword
     * @param int $limit
     * @return array
     */
    public function searchByKeyword($keyword, $limit = 20)
    {
        try {
            $sql = 'SELECT p.*, c.categories_name FROM ' . $this->table . ' p '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'WHERE p.product_name LIKE :keyword '
                 . 'ORDER BY p.product_id DESC '
                 . 'LIMIT ' . (int) $limit;

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':keyword', '%' . $keyword . '%');
            $statement->execute();

            return $statement->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Lấy danh sách sản phẩm mới nhất (sắp xếp theo product_id giảm dần).
     *
     * @param int $limit
     * @return array
     */
    public function getNewProducts($limit = 8)
    {
        try {
            $sql = 'SELECT p.*, c.categories_name '
                 . 'FROM ' . $this->table . ' p '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'ORDER BY p.product_id DESC '
                 . 'LIMIT ' . (int) $limit;

            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            $products = $statement->fetchAll();

            if (!empty($products)) {
                return $products;
            }
        } catch (Throwable $e) {
            // Trường hợp lỗi kết nối hoặc bảng chưa có dữ liệu
        }

        return $this->getDefaultNewProducts($limit);
    }

    /**
     * Lấy danh sách sản phẩm bán chạy nhất.
     * Ưu tiên tính theo tổng số lượng bán trong order_items.
     *
     * @param int $limit
     * @return array
     */
    public function getBestSellingProducts($limit = 8)
    {
        try {
            $sql = 'SELECT p.*, c.categories_name, COALESCE(SUM(oi.quantity), 0) AS total_sold '
                 . 'FROM ' . $this->table . ' p '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'LEFT JOIN order_items oi ON p.product_id = oi.product_id '
                 . 'GROUP BY p.product_id, c.categories_name '
                 . 'ORDER BY total_sold DESC, p.product_id DESC '
                 . 'LIMIT ' . (int) $limit;

            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            $products = $statement->fetchAll();

            if (!empty($products)) {
                return $products;
            }
        } catch (Throwable $e) {
            // Trường hợp lỗi kết nối hoặc bảng chưa có dữ liệu
        }

        return $this->getDefaultBestSellingProducts($limit);
    }

    /**
     * Dữ liệu sản phẩm mới mẫu khi CSDL chưa có dữ liệu.
     *
     * @param int $limit
     * @return array
     */
    public function getDefaultNewProducts($limit = 8)
    {
        $products = [
            [
                'product_id'      => 101,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Thun Thể Thao Nam Pro Dry-Fit Co Giãn 4 Chiều',
                'price'           => 289000,
                'old_price'       => 350000,
                'image'           => 'ao_thun_pro_dryfit.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 45,
                'rating'          => 4.9,
            ],
            [
                'product_id'      => 102,
                'category_id'     => 2,
                'categories_name' => 'Áo thể thao nữ',
                'product_name'    => 'Áo Bra Thể Thao Tập Gym & Yoga Nâng Đỡ Tối Đa',
                'price'           => 249000,
                'old_price'       => 320000,
                'image'           => 'ao_bra_gym_yoga.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 68,
                'rating'          => 4.8,
            ],
            [
                'product_id'      => 103,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Jogger Thể Thao Phối Lưới Thoáng Khí',
                'price'           => 359000,
                'old_price'       => 420000,
                'image'           => 'quan_jogger_thoang_khi.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 32,
                'rating'          => 4.7,
            ],
            [
                'product_id'      => 104,
                'category_id'     => 4,
                'categories_name' => 'Giày & Dép thể thao',
                'product_name'    => 'Giày Chạy Bộ Nam Nữ SPORTIX Ultralight Êm Chân',
                'price'           => 799000,
                'old_price'       => 950000,
                'image'           => 'giay_chay_bo_ultralight.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 89,
                'rating'          => 5.0,
            ],
            [
                'product_id'      => 105,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Khoác Gió Thể Thao Chống Nước & Cản Gió Mùa Đông',
                'price'           => 499000,
                'old_price'       => 590000,
                'image'           => 'ao_khoac_gio_the_thao.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 27,
                'rating'          => 4.9,
            ],
            [
                'product_id'      => 106,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Short Thể Thao 2 Lớp Chống Lộ Tập Gym Cho Nữ',
                'price'           => 199000,
                'old_price'       => 260000,
                'image'           => 'quan_short_2_lop_nu.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 54,
                'rating'          => 4.8,
            ],
            [
                'product_id'      => 107,
                'category_id'     => 5,
                'categories_name' => 'Bộ đồ thể thao',
                'product_name'    => 'Set Đồ Thể Thao Nam Nữ Dynamic Phong Cách Đường Phố',
                'price'           => 589000,
                'old_price'       => 690000,
                'image'           => 'set_the_thao_dynamic.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1483721074576-579a571a29b3?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 41,
                'rating'          => 4.9,
            ],
            [
                'product_id'      => 108,
                'category_id'     => 6,
                'categories_name' => 'Phụ kiện thể thao',
                'product_name'    => 'Balo Thể Thao Chống Nước Ngăn Chứa Giày Tiện Lợi',
                'price'           => 329000,
                'old_price'       => 399000,
                'image'           => 'balo_the_thao_chong_nuoc.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=80',
                'is_new'          => true,
                'total_sold'      => 63,
                'rating'          => 4.8,
            ],
        ];

        return array_slice($products, 0, $limit);
    }

    /**
     * Dữ liệu sản phẩm bán chạy mẫu khi CSDL chưa có dữ liệu.
     *
     * @param int $limit
     * @return array
     */
    public function getDefaultBestSellingProducts($limit = 8)
    {
        $products = [
            [
                'product_id'      => 201,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Polo Thể Thao Nam Vải Pique Cao Cấp Thấm Hút Mồ Hôi',
                'price'           => 320000,
                'old_price'       => 399000,
                'image'           => 'ao_polo_the_thao_nam.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1625910513413-7222d3d95711?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 342,
                'rating'          => 5.0,
            ],
            [
                'product_id'      => 202,
                'category_id'     => 4,
                'categories_name' => 'Giày & Dép thể thao',
                'product_name'    => 'Giày Sneaker Thể Thao Phối Màu Streetwear Unisex',
                'price'           => 850000,
                'old_price'       => 1100000,
                'image'           => 'giay_sneaker_streetwear.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 285,
                'rating'          => 4.9,
            ],
            [
                'product_id'      => 203,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Legging Thể Thao Nữ Cạp Cao Định Hình Dáng Chuẩn',
                'price'           => 280000,
                'old_price'       => 350000,
                'image'           => 'quan_legging_cap_cao.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 264,
                'rating'          => 4.9,
            ],
            [
                'product_id'      => 204,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Thun Sát Nách Nam Chuyên Tập Gym Cơ Bắp',
                'price'           => 179000,
                'old_price'       => 220000,
                'image'           => 'ao_sat_nach_tap_gym.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 231,
                'rating'          => 4.8,
            ],
            [
                'product_id'      => 205,
                'category_id'     => 5,
                'categories_name' => 'Bộ đồ thể thao',
                'product_name'    => 'Bộ Nỉ Thể Thao Dài Tay Unisex Thu Đông Ấm Áp',
                'price'           => 650000,
                'old_price'       => 780000,
                'image'           => 'bo_ni_the_thao_thu_dong.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 198,
                'rating'          => 4.9,
            ],
            [
                'product_id'      => 206,
                'category_id'     => 6,
                'categories_name' => 'Phụ kiện thể thao',
                'product_name'    => 'Bình Nước Giữ Nhiệt Thể Thao Dung Tích 1000ml Inox 316',
                'price'           => 219000,
                'old_price'       => 280000,
                'image'           => 'binh_nuoc_the_thao.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 176,
                'rating'          => 4.7,
            ],
            [
                'product_id'      => 207,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Short Gió Nam 1 Lớp Nhanh Khô Chạy Bộ Bóng Đá',
                'price'           => 189000,
                'old_price'       => 230000,
                'image'           => 'quan_short_gio_nam.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 165,
                'rating'          => 4.8,
            ],
            [
                'product_id'      => 208,
                'category_id'     => 6,
                'categories_name' => 'Phụ kiện thể thao',
                'product_name'    => 'Mũ Lưỡi Trai Thể Thao Nửa Đầu Thoáng Khí Chống Tia UV',
                'price'           => 139000,
                'old_price'       => 180000,
                'image'           => 'mu_the_thao_chong_uv.jpg',
                'image_url'       => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=500&auto=format&fit=crop&q=80',
                'is_hot'          => true,
                'total_sold'      => 154,
                'rating'          => 4.8,
            ],
        ];

        return array_slice($products, 0, $limit);
    }
}
