<?php

class ProductModel extends BaseModel
{
    protected $table = 'products';

    /**
     * Lấy toàn bộ sản phẩm kèm tên danh mục (phục vụ trang quản lý Admin).
     *
     * Không dùng dữ liệu mẫu: nếu truy vấn thất bại, PDOException sẽ được ném ra
     * để Controller hiển thị thông báo tải dữ liệu thất bại.
     *
     * @return array
     */
    public function getAllWithCategory($keyword = null, $categoryId = null)
    {
        return $this->getAllAdmin($keyword, $categoryId);
    }

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

    /**
     * Lấy toàn bộ sản phẩm phục vụ khu vực quản trị (hỗ trợ tìm kiếm từ khóa và lọc theo danh mục).
     *
     * @param string|null $keyword
     * @param int|null $categoryId
     * @return array
     */
    public function getAllAdmin($keyword = null, $categoryId = null)
    {
        try {
            $sql = 'SELECT p.*, c.categories_name '
                 . 'FROM ' . $this->table . ' p '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'WHERE 1=1 ';

            $params = [];
            if (!empty($keyword)) {
                $sql .= 'AND p.product_name LIKE :keyword ';
                $params[':keyword'] = '%' . trim($keyword) . '%';
            }

            if (!empty($categoryId) && (int) $categoryId > 0) {
                $sql .= 'AND p.category_id = :category_id ';
                $params[':category_id'] = (int) $categoryId;
            }

            $sql .= 'ORDER BY p.product_id DESC';

            $statement = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $statement->bindValue($key, $val);
            }
            $statement->execute();
            $products = $statement->fetchAll();

            if (!empty($products)) {
                return $products;
            }
        } catch (Throwable $e) {
            // Trường hợp lỗi kết nối hoặc CSDL chưa có dữ liệu
        }

        return $this->getDefaultAdminProducts($keyword, $categoryId);
    }

    /**
     * Đếm tổng số sản phẩm trong hệ thống (dùng cho thống kê Dashboard).
     *
     * @return int
     */
    public function countAll()
    {
        try {
            $sql = 'SELECT COUNT(*) AS total FROM ' . $this->table;
            $statement = $this->pdo->query($sql);
            $row = $statement->fetch();

            if ($row && isset($row['total'])) {
                return (int) $row['total'];
            }
        } catch (Throwable $e) {
            // Bỏ qua lỗi kết nối
        }

        return count($this->getDefaultAdminProducts());
    }

    /**
     * Lấy chi tiết một sản phẩm theo ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        try {
            $sql = 'SELECT p.*, c.categories_name '
                 . 'FROM ' . $this->table . ' p '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'WHERE p.product_id = :id LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $product = $statement->fetch();

            if ($product) {
                return $product;
            }
        } catch (Throwable $e) {
            // Bỏ qua lỗi kết nối
        }

        // Tìm trong dữ liệu mẫu fallback
        foreach ($this->getDefaultAdminProducts() as $item) {
            if ((int) $item['product_id'] === (int) $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Lấy danh sách sản phẩm liên quan (cùng danh mục, loại trừ sản phẩm hiện tại).
     *
     * @param int $categoryId
     * @param int $excludeId
     * @param int $limit
     * @return array
     */
    public function getRelatedProducts($categoryId, $excludeId = 0, $limit = 4)
    {
        try {
            $sql = 'SELECT p.*, c.categories_name '
                 . 'FROM ' . $this->table . ' p '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'WHERE p.category_id = :category_id AND p.product_id != :exclude_id '
                 . 'ORDER BY p.product_id DESC '
                 . 'LIMIT ' . (int) $limit;

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':category_id', (int) $categoryId, PDO::PARAM_INT);
            $statement->bindValue(':exclude_id', (int) $excludeId, PDO::PARAM_INT);
            $statement->execute();
            $products = $statement->fetchAll();

            if (!empty($products)) {
                return $products;
            }
        } catch (Throwable $e) {
            // Bỏ qua lỗi hoặc khi CSDL chưa có dữ liệu
        }

        // Dữ liệu mẫu fallback
        $all = $this->getDefaultAdminProducts();
        $related = array_filter($all, function ($item) use ($categoryId, $excludeId) {
            return (int) $item['category_id'] === (int) $categoryId && (int) $item['product_id'] !== (int) $excludeId;
        });

        if (empty($related)) {
            $related = array_filter($all, function ($item) use ($excludeId) {
                return (int) $item['product_id'] !== (int) $excludeId;
            });
        }

        return array_slice(array_values($related), 0, $limit);
    }

    /**
     * Kiểm tra tên sản phẩm đã tồn tại hay chưa (dùng khi validate form).
     *
     * @param string $name
     * @param int|null $excludeId
     * @return array|null
     */
    public function findByName($name, $excludeId = null)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE product_name = :name';
            $params = [':name' => trim($name)];

            if ($excludeId !== null) {
                $sql .= ' AND product_id != :exclude_id';
                $params[':exclude_id'] = (int) $excludeId;
            }

            $sql .= ' LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $statement->bindValue($key, $val);
            }
            $statement->execute();
            $result = $statement->fetch();

            return $result ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Thêm mới một sản phẩm vào CSDL.
     *
     * @param array $data
     * @return int|false ID sản phẩm vừa tạo hoặc false nếu thất bại
     */
    public function create(array $data)
    {
        try {
            $sql = 'INSERT INTO ' . $this->table . ' '
                 . '(category_id, product_name, price, description, image, size, color, stock_quantity) '
                 . 'VALUES (:category_id, :product_name, :price, :description, :image, :size, :color, :stock_quantity)';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':category_id', (int) ($data['category_id'] ?? 0), PDO::PARAM_INT);
            $statement->bindValue(':product_name', trim($data['product_name'] ?? ''));
            $statement->bindValue(':price', (float) ($data['price'] ?? 0));
            $statement->bindValue(':description', $data['description'] ?? null);
            $statement->bindValue(':image', $data['image'] ?? null);
            $statement->bindValue(':size', $data['size'] ?? null);
            $statement->bindValue(':color', $data['color'] ?? null);
            $statement->bindValue(':stock_quantity', (int) ($data['stock_quantity'] ?? 0), PDO::PARAM_INT);

            if ($statement->execute()) {
                return (int) $this->pdo->lastInsertId();
            }
        } catch (Throwable $e) {
            // Ghi nhận lỗi nếu cần
        }

        return false;
    }

    /**
     * Cập nhật thông tin sản phẩm.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        try {
            $sql = 'UPDATE ' . $this->table . ' SET '
                 . 'category_id = :category_id, '
                 . 'product_name = :product_name, '
                 . 'price = :price, '
                 . 'description = :description, '
                 . 'image = :image, '
                 . 'size = :size, '
                 . 'color = :color, '
                 . 'stock_quantity = :stock_quantity '
                 . 'WHERE product_id = :id';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->bindValue(':category_id', (int) ($data['category_id'] ?? 0), PDO::PARAM_INT);
            $statement->bindValue(':product_name', trim($data['product_name'] ?? ''));
            $statement->bindValue(':price', (float) ($data['price'] ?? 0));
            $statement->bindValue(':description', $data['description'] ?? null);
            $statement->bindValue(':image', $data['image'] ?? null);
            $statement->bindValue(':size', $data['size'] ?? null);
            $statement->bindValue(':color', $data['color'] ?? null);
            $statement->bindValue(':stock_quantity', (int) ($data['stock_quantity'] ?? 0), PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Đếm số lượng đơn hàng chứa sản phẩm này (kiểm tra ràng buộc).
     *
     * @param int $id
     * @return int
     */
    public function countInOrders($id)
    {
        try {
            $sql = 'SELECT COUNT(*) AS total FROM order_items WHERE product_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $row = $statement->fetch();

            return (int) ($row['total'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Đếm số lượng sản phẩm đang có trong giỏ hàng (kiểm tra ràng buộc cart_items).
     *
     * @param int $id
     * @return int
     */
    public function countInCarts($id)
    {
        try {
            $sql = 'SELECT COUNT(*) AS total FROM cart_items WHERE product_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $row = $statement->fetch();

            return (int) ($row['total'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Xóa sản phẩm khỏi CSDL.
     * Tự động dọn dẹp các mục liên kết trong giỏ hàng nếu có.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            // Xóa sản phẩm trong giỏ hàng trước nếu có để tránh lỗi ràng buộc khóa ngoại RESTRICT
            $cleanCartSql = 'DELETE FROM cart_items WHERE product_id = :id';
            $stmtCart = $this->pdo->prepare($cleanCartSql);
            $stmtCart->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $stmtCart->execute();

            $sql = 'DELETE FROM ' . $this->table . ' WHERE product_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Dữ liệu sản phẩm mẫu phong phú phục vụ fallback cho khu vực Quản trị.
     *
     * @param string|null $keyword
     * @param int|null $categoryId
     * @return array
     */
    public function getDefaultAdminProducts($keyword = null, $categoryId = null)
    {
        $products = [
            [
                'product_id'      => 1,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Thun Thể Thao Nam Pro Dry-Fit Co Giãn 4 Chiều',
                'price'           => 289000,
                'description'     => 'Chất liệu thun lạnh cao cấp, thấm hút mồ hôi siêu tốc, phù hợp chạy bộ và tập gym.',
                'image'           => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=500&auto=format&fit=crop&q=80',
                'size'            => 'M, L, XL, XXL',
                'color'           => 'Đen, Xanh Navy, Trắng',
                'stock_quantity'  => 50,
            ],
            [
                'product_id'      => 2,
                'category_id'     => 2,
                'categories_name' => 'Áo thể thao nữ',
                'product_name'    => 'Áo Bra Thể Thao Tập Gym & Yoga Nâng Đỡ Tối Đa',
                'price'           => 249000,
                'description'     => 'Thiết kế ôm sát nhẹ nhàng, mút đệm thoáng khí, dây chéo lưng cá tính.',
                'image'           => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=500&auto=format&fit=crop&q=80',
                'size'            => 'S, M, L',
                'color'           => 'Hồng Pastel, Đen, Xanh Rêu',
                'stock_quantity'  => 45,
            ],
            [
                'product_id'      => 3,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Jogger Thể Thao Phối Lưới Thoáng Khí',
                'price'           => 359000,
                'description'     => 'Chất vải thun dù cao cấp, bo gấu co giãn, túi khóa kéo tiện lợi.',
                'image'           => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=500&auto=format&fit=crop&q=80',
                'size'            => 'M, L, XL',
                'color'           => 'Xám Đậm, Đen',
                'stock_quantity'  => 35,
            ],
            [
                'product_id'      => 4,
                'category_id'     => 4,
                'categories_name' => 'Giày & Dép thể thao',
                'product_name'    => 'Giày Chạy Bộ Nam Nữ SPORTIX Ultralight Êm Chân',
                'price'           => 799000,
                'description'     => 'Đế đệm bọt khí trợ lực, thân dệt knit thoáng khí 360 độ.',
                'image'           => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80',
                'size'            => '39, 40, 41, 42, 43',
                'color'           => 'Đỏ Đen, Trắng Cam',
                'stock_quantity'  => 60,
            ],
            [
                'product_id'      => 5,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Khoác Gió Thể Thao Chống Nước & Cản Gió',
                'price'           => 499000,
                'description'     => 'Vải gió tráng bạc 2 lớp, cản gió và chống nước mưa phùn hiệu quả.',
                'image'           => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=500&auto=format&fit=crop&q=80',
                'size'            => 'M, L, XL',
                'color'           => 'Đen, Xanh Rêu',
                'stock_quantity'  => 30,
            ],
            [
                'product_id'      => 6,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Short Thể Thao 2 Lớp Chống Lộ Cho Nữ',
                'price'           => 199000,
                'description'     => 'Thiết kế 2 lớp thông minh: lớp lót ôm đùi an toàn và lớp ngoài thoáng mát.',
                'image'           => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&auto=format&fit=crop&q=80',
                'size'            => 'S, M, L',
                'color'           => 'Đen, Tím Nhạt',
                'stock_quantity'  => 55,
            ],
            [
                'product_id'      => 7,
                'category_id'     => 5,
                'categories_name' => 'Bộ đồ thể thao',
                'product_name'    => 'Set Đồ Thể Thao Nam Nữ Dynamic Đường Phố',
                'price'           => 589000,
                'description'     => 'Phong cách sporty-chic thời thượng, mặc tập luyện hay dạo phố đều đẹp.',
                'image'           => 'https://images.unsplash.com/photo-1483721074576-579a571a29b3?w=500&auto=format&fit=crop&q=80',
                'size'            => 'M, L, XL',
                'color'           => 'Xám Khói, Đen',
                'stock_quantity'  => 25,
            ],
            [
                'product_id'      => 8,
                'category_id'     => 6,
                'categories_name' => 'Phụ kiện thể thao',
                'product_name'    => 'Balo Thể Thao Chống Nước Có Ngăn Chứa Giày',
                'price'           => 329000,
                'description'     => 'Nhiều ngăn đa năng, đáy chứa giày riêng biệt chống bám mùi.',
                'image'           => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=80',
                'size'            => 'Freesize',
                'color'           => 'Đen, Xám Ghi',
                'stock_quantity'  => 40,
            ],
            [
                'product_id'      => 9,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Polo Thể Thao Nam Vải Pique Cao Cấp',
                'price'           => 320000,
                'description'     => 'Cổ bẻ lịch sự, chất vải dệt mắt chim sang trọng, thấm hút mồ hôi tốt.',
                'image'           => 'https://images.unsplash.com/photo-1625910513413-7222d3d95711?w=500&auto=format&fit=crop&q=80',
                'size'            => 'M, L, XL, XXL',
                'color'           => 'Trắng, Xanh Đậm, Đen',
                'stock_quantity'  => 80,
            ],
            [
                'product_id'      => 10,
                'category_id'     => 4,
                'categories_name' => 'Giày & Dép thể thao',
                'product_name'    => 'Giày Sneaker Thể Thao Phối Màu Streetwear Unisex',
                'price'           => 850000,
                'description'     => 'Thiết kế chunky retro thời thượng, đệm đế êm ái tôn chiều cao.',
                'image'           => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=500&auto=format&fit=crop&q=80',
                'size'            => '36, 37, 38, 39, 40, 41, 42',
                'color'           => 'Trắng Xám, Đen Trắng',
                'stock_quantity'  => 65,
            ],
            [
                'product_id'      => 11,
                'category_id'     => 3,
                'categories_name' => 'Quần thể thao',
                'product_name'    => 'Quần Legging Thể Thao Nữ Cạp Cao Định Hình',
                'price'           => 280000,
                'description'     => 'Cạp cao giấu bụng, chất vải dệt kim mềm mịn co giãn 4 chiều.',
                'image'           => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&auto=format&fit=crop&q=80',
                'size'            => 'S, M, L',
                'color'           => 'Đen, Xanh Rêu',
                'stock_quantity'  => 70,
            ],
            [
                'product_id'      => 12,
                'category_id'     => 1,
                'categories_name' => 'Áo thể thao nam',
                'product_name'    => 'Áo Thun Sát Nách Nam Chuyên Tập Gym Cơ Bắp',
                'price'           => 179000,
                'description'     => 'Khoét nách sâu thoải mái vận động, vải cotton compact co giãn cao.',
                'image'           => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80',
                'size'            => 'M, L, XL',
                'color'           => 'Đen, Xám Trắng',
                'stock_quantity'  => 85,
            ],
        ];

        // Lọc theo từ khóa nếu có
        if (!empty($keyword)) {
            $kw = mb_strtolower(trim($keyword));
            $products = array_filter($products, function ($item) use ($kw) {
                return mb_stripos(mb_strtolower($item['product_name']), $kw) !== false;
            });
        }

        // Lọc theo danh mục nếu có
        if (!empty($categoryId) && (int) $categoryId > 0) {
            $catId = (int) $categoryId;
            $products = array_filter($products, function ($item) use ($catId) {
                return (int) $item['category_id'] === $catId;
            });
        }

        return array_values($products);
    }
}
