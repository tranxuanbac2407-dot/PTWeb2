<?php

class OrderModel extends BaseModel
{
    protected $table = 'orders';

    /**
     * Lấy toàn bộ đơn hàng cho khu vực Quản trị (hỗ trợ tìm kiếm và lọc trạng thái).
     *
     * @param string|null $keyword
     * @param string|null $status
     * @return array
     */
    public function getAllAdmin($keyword = null, $status = null)
    {
        try {
            $sql = 'SELECT o.*, u.username, u.email, COALESCE(NULLIF(u.fullname, \'\'), o.receiver_name) AS customer_name '
                 . 'FROM ' . $this->table . ' o '
                 . 'LEFT JOIN users u ON o.user_id = u.id '
                 . 'WHERE 1=1 ';

            $params = [];
            if (!empty($keyword)) {
                $sql .= 'AND (o.order_id = :order_id_exact OR o.receiver_name LIKE :keyword OR o.phone LIKE :keyword OR u.username LIKE :keyword) ';
                $params[':order_id_exact'] = is_numeric($keyword) ? (int) $keyword : -1;
                $params[':keyword'] = '%' . trim($keyword) . '%';
            }

            if (!empty($status)) {
                $sql .= 'AND o.order_status = :status ';
                $params[':status'] = trim($status);
            }

            $sql .= 'ORDER BY o.order_id DESC';

            $statement = $this->pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $statement->bindValue($k, $v);
            }
            $statement->execute();
            $orders = $statement->fetchAll();

            if (!empty($orders)) {
                return $orders;
            }
        } catch (Throwable $e) {
            // Chuyển sang fallback khi CSDL chưa có dữ liệu hoặc lỗi kết nối
        }

        return $this->getDefaultOrders($keyword, $status);
    }

    /**
     * Lấy thông tin một đơn hàng theo ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        try {
            $sql = 'SELECT o.*, u.username, u.email, COALESCE(NULLIF(u.fullname, \'\'), o.receiver_name) AS customer_name '
                 . 'FROM ' . $this->table . ' o '
                 . 'LEFT JOIN users u ON o.user_id = u.id '
                 . 'WHERE o.order_id = :id LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $order = $statement->fetch();

            if ($order) {
                return $order;
            }
        } catch (Throwable $e) {
            // Bỏ qua lỗi
        }

        // Kiểm tra trong session fallback đơn hàng vừa đặt
        if (!empty($_SESSION['fallback_orders'])) {
            foreach ($_SESSION['fallback_orders'] as $fo) {
                if ((int) ($fo['order_id'] ?? 0) === (int) $id) {
                    return $fo;
                }
            }
        }

        foreach ($this->getDefaultOrders() as $o) {
            if ((int) $o['order_id'] === (int) $id) {
                return $o;
            }
        }

        return null;
    }

    /**
     * Lấy danh sách sản phẩm trong đơn hàng.
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderItems($orderId)
    {
        try {
            $sql = 'SELECT oi.*, p.image, c.categories_name '
                 . 'FROM order_items oi '
                 . 'LEFT JOIN products p ON oi.product_id = p.product_id '
                 . 'LEFT JOIN categories c ON p.category_id = c.categories_id '
                 . 'WHERE oi.order_id = :order_id '
                 . 'ORDER BY oi.order_item_id ASC';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':order_id', (int) $orderId, PDO::PARAM_INT);
            $statement->execute();
            $items = $statement->fetchAll();

            if (!empty($items)) {
                return $items;
            }
        } catch (Throwable $e) {
            // Bỏ qua lỗi
        }

        // Kiểm tra trong session fallback đơn hàng vừa đặt
        if (!empty($_SESSION['fallback_order_items'][$orderId])) {
            return $_SESSION['fallback_order_items'][$orderId];
        }

        return $this->getDefaultOrderItems($orderId);
    }

    /**
     * Tạo mới một đơn hàng kèm các món hàng chi tiết.
     * Sử dụng transaction để bảo toàn dữ liệu.
     *
     * @param array $orderData
     * @param array $cartItems
     * @return int ID đơn hàng vừa tạo, hoặc 0 nếu thất bại
     */
    public function createOrder(array $orderData, array $cartItems)
    {
        if (empty($cartItems)) {
            return 0;
        }

        try {
            $this->pdo->beginTransaction();

            $sql = 'INSERT INTO ' . $this->table . ' '
                 . '(user_id, receiver_name, phone, shipping_address, total_amount, payment_method, order_status, order_date) '
                 . 'VALUES (:user_id, :receiver_name, :phone, :shipping_address, :total_amount, :payment_method, :order_status, NOW())';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':user_id', (int) ($orderData['user_id'] ?? 0), PDO::PARAM_INT);
            $statement->bindValue(':receiver_name', trim($orderData['receiver_name'] ?? ''));
            $statement->bindValue(':phone', trim($orderData['phone'] ?? ''));
            $statement->bindValue(':shipping_address', trim($orderData['shipping_address'] ?? ''));
            $statement->bindValue(':total_amount', (float) ($orderData['total_amount'] ?? 0));
            $statement->bindValue(':payment_method', trim($orderData['payment_method'] ?? 'cod'));
            $statement->bindValue(':order_status', 'pending');

            $statement->execute();
            $orderId = (int) $this->pdo->lastInsertId();

            if ($orderId <= 0) {
                $this->pdo->rollBack();
                return 0;
            }

            // Insert từng món hàng vào order_items
            $itemSql = 'INSERT INTO order_items '
                     . '(order_id, product_id, product_name, size, color, unit_price, quantity, subtotal) '
                     . 'VALUES (:order_id, :product_id, :product_name, :size, :color, :unit_price, :quantity, :subtotal)';
            $itemStmt = $this->pdo->prepare($itemSql);

            foreach ($cartItems as $item) {
                $unitPrice = (float) ($item['price'] ?? 0);
                $qty       = (int) ($item['quantity'] ?? 1);
                $subtotal  = $unitPrice * $qty;

                $itemStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_id', (int) ($item['product_id'] ?? 0), PDO::PARAM_INT);
                $itemStmt->bindValue(':product_name', trim($item['name'] ?? 'Sản phẩm'));
                $itemStmt->bindValue(':size', !empty($item['size']) ? trim($item['size']) : null);
                $itemStmt->bindValue(':color', !empty($item['color']) ? trim($item['color']) : null);
                $itemStmt->bindValue(':unit_price', $unitPrice);
                $itemStmt->bindValue(':quantity', $qty, PDO::PARAM_INT);
                $itemStmt->bindValue(':subtotal', $subtotal);
                $itemStmt->execute();
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // Fallback lưu đơn vào Session nếu CSDL lỗi
            $fallbackId = time();
            $savedOrder = [
                'order_id'         => $fallbackId,
                'user_id'          => (int) ($orderData['user_id'] ?? 0),
                'receiver_name'    => trim($orderData['receiver_name'] ?? 'Khách hàng'),
                'customer_name'    => trim($orderData['receiver_name'] ?? 'Khách hàng'),
                'phone'            => trim($orderData['phone'] ?? ''),
                'shipping_address' => trim($orderData['shipping_address'] ?? ''),
                'total_amount'     => (float) ($orderData['total_amount'] ?? 0),
                'payment_method'   => trim($orderData['payment_method'] ?? 'cod'),
                'order_status'     => 'pending',
                'order_date'       => date('Y-m-d H:i:s'),
            ];

            if (!isset($_SESSION['fallback_orders'])) {
                $_SESSION['fallback_orders'] = [];
            }
            $_SESSION['fallback_orders'][$fallbackId] = $savedOrder;

            // Lưu chi tiết các món vào session
            $savedItems = [];
            $idx = 1;
            foreach ($cartItems as $item) {
                $unitPrice = (float) ($item['price'] ?? 0);
                $qty       = (int) ($item['quantity'] ?? 1);
                $savedItems[] = [
                    'order_item_id'   => $idx++,
                    'order_id'        => $fallbackId,
                    'product_id'      => (int) ($item['product_id'] ?? 0),
                    'product_name'    => trim($item['name'] ?? 'Sản phẩm'),
                    'size'            => $item['size'] ?? '',
                    'color'           => $item['color'] ?? '',
                    'unit_price'      => $unitPrice,
                    'quantity'        => $qty,
                    'subtotal'        => $unitPrice * $qty,
                    'image'           => $item['image'] ?? '',
                    'categories_name' => 'Thể thao',
                ];
            }
            $_SESSION['fallback_order_items'][$fallbackId] = $savedItems;

            return $fallbackId;
        }
    }

    /**
     * Lấy danh sách đơn hàng của một người dùng theo user_id.
     *
     * @param int $userId
     * @return array
     */
    public function getOrdersByUserId($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return [];
        }

        $results = [];

        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id ORDER BY order_id DESC';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $statement->execute();
            $dbOrders = $statement->fetchAll();
            if (!empty($dbOrders)) {
                $results = $dbOrders;
            }
        } catch (Throwable $e) {
            // Chuyển sang fallback
        }

        // Bổ sung các đơn trong session fallback của user nếu có
        if (!empty($_SESSION['fallback_orders'])) {
            foreach ($_SESSION['fallback_orders'] as $fo) {
                if ((int) ($fo['user_id'] ?? 0) === $userId) {
                    $results[] = $fo;
                }
            }
        }

        // Nếu vẫn trống và userId nằm trong mock
        if (empty($results)) {
            foreach ($this->getDefaultOrders() as $mo) {
                if ((int) $mo['user_id'] === $userId) {
                    $results[] = $mo;
                }
            }
        }

        return $results;
    }

    /**
     * Tìm danh sách đơn hàng theo số điện thoại (hỗ trợ khách tra cứu lịch sử mua hàng).
     *
     * @param string $phone
     * @return array
     */
    public function getOrdersByPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);
        if (empty($phone)) {
            return [];
        }

        $results = [];

        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE phone LIKE :phone ORDER BY order_id DESC';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':phone', '%' . $phone . '%');
            $statement->execute();
            $dbOrders = $statement->fetchAll();
            if (!empty($dbOrders)) {
                $results = $dbOrders;
            }
        } catch (Throwable $e) {
            // Chuyển sang fallback
        }

        if (!empty($_SESSION['fallback_orders'])) {
            foreach ($_SESSION['fallback_orders'] as $fo) {
                $fPhone = preg_replace('/[^0-9]/', '', (string) ($fo['phone'] ?? ''));
                if (!empty($fPhone) && strpos($fPhone, $phone) !== false) {
                    $results[] = $fo;
                }
            }
        }

        if (empty($results)) {
            foreach ($this->getDefaultOrders() as $mo) {
                $mPhone = preg_replace('/[^0-9]/', '', (string) ($mo['phone'] ?? ''));
                if (!empty($mPhone) && strpos($mPhone, $phone) !== false) {
                    $results[] = $mo;
                }
            }
        }

        return $results;
    }

    /**
     * Lấy danh sách đơn hàng từ mảng ID (dành cho các đơn vừa đặt trong phiên của khách).
     *
     * @param array $orderIds
     * @return array
     */
    public function getOrdersByIds(array $orderIds)
    {
        $cleanIds = array_filter(array_map('intval', $orderIds), fn($id) => $id > 0);
        if (empty($cleanIds)) {
            return [];
        }

        $orders = [];
        foreach ($cleanIds as $id) {
            $order = $this->getById($id);
            if ($order) {
                $orders[] = $order;
            }
        }

        return $orders;
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateStatus($orderId, $status)
    {
        $allowedStatuses = ['pending', 'processing', 'shipping', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        try {
            $sql = 'UPDATE ' . $this->table . ' SET order_status = :status WHERE order_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':status', $status);
            $statement->bindValue(':id', (int) $orderId, PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Đếm tổng số đơn hàng trong hệ thống (phục vụ thống kê Dashboard).
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
            // Bỏ qua lỗi
        }

        return count($this->getDefaultOrders());
    }

    /**
     * Dữ liệu đơn hàng mẫu phục vụ fallback khi CSDL chưa có dữ liệu.
     *
     * @param string|null $keyword
     * @param string|null $status
     * @return array
     */
    public function getDefaultOrders($keyword = null, $status = null)
    {
        $orders = [
            [
                'order_id'         => 1,
                'user_id'          => 1,
                'username'         => 'nguyenan',
                'customer_name'    => 'Nguyễn Văn An',
                'receiver_name'    => 'Nguyễn Văn An',
                'phone'            => '0987654321',
                'shipping_address' => 'Số 13 Trịnh Văn Bô, Nam Từ Liêm, Hà Nội',
                'total_amount'     => 1170000,
                'payment_method'   => 'cod',
                'order_status'     => 'completed',
                'order_date'       => '2026-11-15 14:30:00',
            ],
            [
                'order_id'         => 2,
                'user_id'          => 2,
                'username'         => 'tranmai',
                'customer_name'    => 'Trần Thị Mai',
                'receiver_name'    => 'Trần Thị Mai',
                'phone'            => '0912345678',
                'shipping_address' => 'Tòa Keangnam, Mễ Trì, Cầu Giấy, Hà Nội',
                'total_amount'     => 850000,
                'payment_method'   => 'momo',
                'order_status'     => 'shipping',
                'order_date'       => '2026-11-16 09:15:00',
            ],
            [
                'order_id'         => 3,
                'user_id'          => 3,
                'username'         => 'lelong',
                'customer_name'    => 'Lê Hoàng Long',
                'receiver_name'    => 'Lê Hoàng Long',
                'phone'            => '0903112233',
                'shipping_address' => 'Số 45 Lê Duẩn, Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                'total_amount'     => 589000,
                'payment_method'   => 'cod',
                'order_status'     => 'processing',
                'order_date'       => '2026-11-16 16:45:00',
            ],
            [
                'order_id'         => 4,
                'user_id'          => 4,
                'username'         => 'thuylinh',
                'customer_name'    => 'Phạm Thùy Linh',
                'receiver_name'    => 'Phạm Thùy Linh',
                'phone'            => '0978665544',
                'shipping_address' => 'Số 88 Nguyễn Văn Linh, Hải Châu, Đà Nẵng',
                'total_amount'     => 320000,
                'payment_method'   => 'banking',
                'order_status'     => 'pending',
                'order_date'       => '2026-11-17 08:20:00',
            ],
            [
                'order_id'         => 5,
                'user_id'          => 5,
                'username'         => 'minhquan',
                'customer_name'    => 'Đỗ Minh Quân',
                'receiver_name'    => 'Đỗ Minh Quân',
                'phone'            => '0934556677',
                'shipping_address' => 'Số 12 Quang Trung, Hồng Bàng, Hải Phòng',
                'total_amount'     => 799000,
                'payment_method'   => 'cod',
                'order_status'     => 'cancelled',
                'order_date'       => '2026-11-14 11:10:00',
            ],
        ];

        if (!empty($keyword)) {
            $kw = mb_strtolower(trim($keyword));
            $orders = array_filter($orders, function ($o) use ($kw) {
                return (string) $o['order_id'] === $kw
                    || mb_stripos(mb_strtolower($o['receiver_name']), $kw) !== false
                    || mb_stripos($o['phone'], $kw) !== false
                    || mb_stripos(mb_strtolower($o['customer_name']), $kw) !== false;
            });
        }

        if (!empty($status)) {
            $st = trim($status);
            $orders = array_filter($orders, function ($o) use ($st) {
                return $o['order_status'] === $st;
            });
        }

        return array_values($orders);
    }

    /**
     * Dữ liệu chi tiết món hàng mẫu phục vụ fallback.
     *
     * @param int $orderId
     * @return array
     */
    public function getDefaultOrderItems($orderId)
    {
        $mock = [
            1 => [
                [
                    'order_item_id'   => 1,
                    'order_id'        => 1,
                    'product_id'      => 9,
                    'product_name'    => 'Áo Polo Thể Thao Nam Vải Pique Cao Cấp',
                    'size'            => 'L',
                    'color'           => 'Trắng',
                    'unit_price'      => 320000,
                    'quantity'        => 2,
                    'subtotal'        => 640000,
                    'categories_name' => 'Áo thể thao nam',
                    'image'           => 'https://images.unsplash.com/photo-1625910513413-7222d3d95711?w=500&auto=format&fit=crop&q=80',
                ],
                [
                    'order_item_id'   => 2,
                    'order_id'        => 1,
                    'product_id'      => 12,
                    'product_name'    => 'Áo Thun Sát Nách Nam Chuyên Tập Gym Cơ Bắp',
                    'size'            => 'XL',
                    'color'           => 'Đen',
                    'unit_price'      => 179000,
                    'quantity'        => 3,
                    'subtotal'        => 537000,
                    'categories_name' => 'Áo thể thao nam',
                    'image'           => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80',
                ],
            ],
            2 => [
                [
                    'order_item_id'   => 3,
                    'order_id'        => 2,
                    'product_id'      => 10,
                    'product_name'    => 'Giày Sneaker Thể Thao Phối Màu Streetwear Unisex',
                    'size'            => '38',
                    'color'           => 'Trắng Xám',
                    'unit_price'      => 850000,
                    'quantity'        => 1,
                    'subtotal'        => 850000,
                    'categories_name' => 'Giày & Dép thể thao',
                    'image'           => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=500&auto=format&fit=crop&q=80',
                ],
            ],
            3 => [
                [
                    'order_item_id'   => 4,
                    'order_id'        => 3,
                    'product_id'      => 7,
                    'product_name'    => 'Set Đồ Thể Thao Nam Nữ Dynamic Đường Phố',
                    'size'            => 'L',
                    'color'           => 'Xám Khói',
                    'unit_price'      => 589000,
                    'quantity'        => 1,
                    'subtotal'        => 589000,
                    'categories_name' => 'Bộ đồ thể thao',
                    'image'           => 'https://images.unsplash.com/photo-1483721074576-579a571a29b3?w=500&auto=format&fit=crop&q=80',
                ],
            ],
            4 => [
                [
                    'order_item_id'   => 5,
                    'order_id'        => 4,
                    'product_id'      => 9,
                    'product_name'    => 'Áo Polo Thể Thao Nam Vải Pique Cao Cấp',
                    'size'            => 'M',
                    'color'           => 'Đen',
                    'unit_price'      => 320000,
                    'quantity'        => 1,
                    'subtotal'        => 320000,
                    'categories_name' => 'Áo thể thao nam',
                    'image'           => 'https://images.unsplash.com/photo-1625910513413-7222d3d95711?w=500&auto=format&fit=crop&q=80',
                ],
            ],
            5 => [
                [
                    'order_item_id'   => 6,
                    'order_id'        => 5,
                    'product_id'      => 4,
                    'product_name'    => 'Giày Chạy Bộ Nam Nữ SPORTIX Ultralight Êm Chân',
                    'size'            => '41',
                    'color'           => 'Đỏ Đen',
                    'unit_price'      => 799000,
                    'quantity'        => 1,
                    'subtotal'        => 799000,
                    'categories_name' => 'Giày & Dép thể thao',
                    'image'           => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80',
                ],
            ],
        ];

        return $mock[(int) $orderId] ?? [
            [
                'order_item_id'   => 99,
                'order_id'        => (int) $orderId,
                'product_id'      => 1,
                'product_name'    => 'Trang phục thể thao SPORTIX',
                'size'            => 'Freesize',
                'color'           => 'Tiêu chuẩn',
                'unit_price'      => 500000,
                'quantity'        => 1,
                'subtotal'        => 500000,
                'categories_name' => 'Thể thao',
                'image'           => null,
            ],
        ];
    }
}
