<?php

class CategoryModel extends BaseModel
{
    protected $table = 'categories';

    /**
     * Lấy danh sách danh mục nổi bật kèm số lượng sản phẩm.
     * Có fallback dữ liệu mẫu nếu CSDL chưa có dữ liệu.
     *
     * @param int $limit
     * @return array
     */
    public function getFeaturedCategories($limit = 6)
    {
        try {
            $sql = 'SELECT c.categories_id, c.categories_name, c.description, COUNT(p.product_id) AS product_count '
                 . 'FROM ' . $this->table . ' c '
                 . 'LEFT JOIN products p ON c.categories_id = p.category_id '
                 . 'GROUP BY c.categories_id, c.categories_name, c.description '
                 . 'ORDER BY product_count DESC, c.categories_id ASC '
                 . 'LIMIT ' . (int) $limit;

            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            $categories = $statement->fetchAll();

            if (!empty($categories)) {
                return $categories;
            }
        } catch (Throwable $e) {
            // Trường hợp lỗi truy vấn hoặc bảng chưa được tạo, chuyển sang dữ liệu mẫu
        }

        return $this->getDefaultCategories($limit);
    }

    /**
     * Lấy tất cả danh mục.
     *
     * @return array
     */
    public function getAll()
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' ORDER BY categories_id ASC';
            $statement = $this->pdo->query($sql);
            $categories = $statement->fetchAll();

            if (!empty($categories)) {
                return $categories;
            }
        } catch (Throwable $e) {
            // Bỏ qua lỗi và trả về dữ liệu mẫu
        }

        return $this->getDefaultCategories();
    }

    /**
     * Dữ liệu danh mục mẫu phục vụ hiển thị khi CSDL trống.
     *
     * @param int|null $limit
     * @return array
     */
    public function getDefaultCategories($limit = null)
    {
        $defaults = [
            [
                'categories_id'   => 1,
                'categories_name' => 'Áo thể thao nam',
                'description'     => 'Áo thun, áo khoác, áo nỉ tập luyện co giãn thoáng mát',
                'icon'            => 'bi-fire',
                'product_count'   => 18,
                'badge'           => 'Hot'
            ],
            [
                'categories_id'   => 2,
                'categories_name' => 'Áo thể thao nữ',
                'description'     => 'Áo bra, croptop, áo tank-top thời trang tôn dáng',
                'icon'            => 'bi-heart',
                'product_count'   => 14,
                'badge'           => 'Xu hướng'
            ],
            [
                'categories_id'   => 3,
                'categories_name' => 'Quần thể thao',
                'description'     => 'Quần jogger, legging, short thể thao năng động',
                'icon'            => 'bi-shield-check',
                'product_count'   => 22,
                'badge'           => 'Phổ biến'
            ],
            [
                'categories_id'   => 4,
                'categories_name' => 'Giày & Dép thể thao',
                'description'     => 'Giày chạy bộ, giày sneaker và dép thể thao êm ái',
                'icon'            => 'bi-lightning-charge',
                'product_count'   => 16,
                'badge'           => 'Mới'
            ],
            [
                'categories_id'   => 5,
                'categories_name' => 'Bộ đồ thể thao',
                'description'     => 'Set đồ tập gym, chạy bộ đồng bộ phong cách cá tính',
                'icon'            => 'bi-award',
                'product_count'   => 12,
                'badge'           => 'Bán chạy'
            ],
            [
                'categories_id'   => 6,
                'categories_name' => 'Phụ kiện thể thao',
                'description'     => 'Balo, mũ, bình nước, tất thể thao cao cấp',
                'icon'            => 'bi-bag',
                'product_count'   => 25,
                'badge'           => 'Đa dạng'
            ],
        ];

        if ($limit !== null && $limit > 0) {
            return array_slice($defaults, 0, $limit);
        }

        return $defaults;
    }
}
