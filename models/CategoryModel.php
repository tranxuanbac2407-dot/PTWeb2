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
     * Lấy toàn bộ danh mục kèm số lượng sản phẩm liên kết (phục vụ trang quản lý Admin).
     *
     * @param string|null $keyword Từ khóa tìm kiếm tên danh mục
     * @return array
     */
    public function getAllWithCount($keyword = null)
    {
        try {
            $sql = 'SELECT c.categories_id, c.categories_name, c.description, COUNT(p.product_id) AS product_count '
                 . 'FROM ' . $this->table . ' c '
                 . 'LEFT JOIN products p ON c.categories_id = p.category_id ';

            $params = [];
            if (!empty($keyword)) {
                $sql .= 'WHERE c.categories_name LIKE :keyword ';
                $params[':keyword'] = '%' . trim($keyword) . '%';
            }

            $sql .= 'GROUP BY c.categories_id, c.categories_name, c.description '
                  . 'ORDER BY c.categories_id DESC';

            $statement = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $statement->bindValue($key, $val);
            }
            $statement->execute();
            $categories = $statement->fetchAll();

            if (!empty($categories)) {
                return $categories;
            }
        } catch (Throwable $e) {
            // Chuyển sang fallback nếu có lỗi kết nối
        }

        return $this->getDefaultCategories();
    }

    /**
     * Lấy tất cả danh mục (danh sách đơn giản).
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
     * Tìm 1 danh mục theo ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE categories_id = :id LIMIT 1';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch();

            if ($result) {
                return $result;
            }
        } catch (Throwable $e) {
            // Trường hợp lỗi kết nối
        }

        // Fallback kiểm tra trong danh sách mẫu
        foreach ($this->getDefaultCategories() as $cat) {
            if ((int) $cat['categories_id'] === (int) $id) {
                return $cat;
            }
        }

        return null;
    }

    /**
     * Tìm danh mục theo tên (hỗ trợ kiểm tra trùng tên khi thêm hoặc sửa).
     *
     * @param string $name
     * @param int|null $excludeId ID danh mục cần loại trừ (khi sửa)
     * @return array|null
     */
    public function findByName($name, $excludeId = null)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE LOWER(TRIM(categories_name)) = LOWER(TRIM(:name)) ';
            if (!empty($excludeId)) {
                $sql .= 'AND categories_id != :exclude_id ';
            }
            $sql .= 'LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':name', $name);
            if (!empty($excludeId)) {
                $statement->bindValue(':exclude_id', (int) $excludeId, PDO::PARAM_INT);
            }
            $statement->execute();

            $result = $statement->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Thêm mới danh mục vào CSDL.
     *
     * @param array $data ['categories_name' => ..., 'description' => ...]
     * @return int|false ID vừa tạo hoặc false nếu thất bại
     */
    public function create(array $data)
    {
        try {
            $sql = 'INSERT INTO ' . $this->table . ' (categories_name, description) '
                 . 'VALUES (:categories_name, :description)';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':categories_name', trim($data['categories_name']));
            $statement->bindValue(':description', isset($data['description']) ? trim($data['description']) : null);
            $statement->execute();

            return (int) $this->pdo->lastInsertId();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Cập nhật thông tin danh mục theo ID.
     *
     * @param int $id
     * @param array $data ['categories_name' => ..., 'description' => ...]
     * @return bool
     */
    public function update($id, array $data)
    {
        try {
            $sql = 'UPDATE ' . $this->table . ' '
                 . 'SET categories_name = :categories_name, description = :description '
                 . 'WHERE categories_id = :id';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':categories_name', trim($data['categories_name']));
            $statement->bindValue(':description', isset($data['description']) ? trim($data['description']) : null);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Đếm số lượng sản phẩm đang thuộc về danh mục này.
     * Dùng để kiểm tra ràng buộc trước khi xóa danh mục.
     *
     * @param int $categoryId
     * @return int
     */
    public function countProducts($categoryId)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM products WHERE category_id = :category_id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':category_id', (int) $categoryId, PDO::PARAM_INT);
            $statement->execute();

            return (int) $statement->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Xóa danh mục theo ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            $sql = 'DELETE FROM ' . $this->table . ' WHERE categories_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            return false;
        }
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
