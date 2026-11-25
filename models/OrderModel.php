<?php

class OrderModel extends BaseModel
{
    protected $table = 'orders';

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchemaUpdated();
    }

    /**
     * Tự động bổ sung các cột payment_status và return_reason nếu CSDL chưa có,
     * đồng thời tự động đồng bộ trạng thái thanh toán cho các đơn hàng online đang giao hoặc hoàn thành.
     */
    private function ensureSchemaUpdated()
    {
        static $checked = false;
        if ($checked || !$this->pdo) {
            return;
        }
        $checked = true;

        try {
            $this->pdo->exec("ALTER TABLE {$this->table} ADD COLUMN `payment_status` VARCHAR(50) DEFAULT 'unpaid' AFTER `payment_method`");
        } catch (Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE {$this->table} ADD COLUMN `return_reason` TEXT NULL AFTER `order_status`");
        } catch (Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE {$this->table} ADD COLUMN `return_image` VARCHAR(255) NULL AFTER `return_reason`");
        } catch (Throwable $e) {}

        try {
            // Tự động đồng bộ: mọi đơn hàng online mà admin đã chuyển sang shipping hoặc completed đều là paid
            $this->pdo->exec("UPDATE {$this->table} SET payment_status = 'paid' WHERE payment_method != 'cod' AND order_status IN ('shipping', 'completed') AND (payment_status IS NULL OR payment_status != 'paid')");
        } catch (Throwable $e) {}
    }

    /**
     * Chuẩn hóa dữ liệu đơn hàng:
     * Nếu là đơn hàng online và trạng thái là 'shipping' hoặc 'completed',
     * chắc chắn đã được thanh toán thành công -> payment_status = 'paid'.
     */
    public function normalizeOrder(?array $order)
    {
        if (!$order) {
            return null;
        }

        $status    = $order['order_status'] ?? 'pending';
        $payMethod = $order['payment_method'] ?? 'cod';
        $payStatus = $order['payment_status'] ?? '';

        if ($payMethod !== 'cod' && in_array($status, ['shipping', 'completed'], true)) {
            $order['payment_status'] = 'paid';
        } elseif ($payMethod === 'cod' && $status === 'completed') {
            $order['payment_status'] = 'paid';
        } elseif (empty($payStatus)) {
            $order['payment_status'] = 'unpaid';
        }

        return $order;
    }

    /**
     * Lấy toàn bộ đơn hàng cho khu vực Quản trị (hỗ trợ tìm kiếm và lọc trạng thái).
     *
     * @param string|null $keyword
     * @param string|null $status
     * @return array
     */
    public function getAllAdmin($keyword = null, $status = null)
    {
        $sql = 'SELECT o.*, u.username, u.email, COALESCE(NULLIF(u.full_name, \'\'), o.receiver_name) AS customer_name '
             . 'FROM ' . $this->table . ' o '
             . 'LEFT JOIN users u ON o.user_id = u.id '
             . 'WHERE 1=1 ';

        $params = [];
        if (!empty($keyword)) {
            $sql .= 'AND (o.order_id = :order_id_exact OR o.receiver_name LIKE :keyword OR o.phone LIKE :keyword OR u.username LIKE :keyword) ';
            $params[':order_id_exact'] = (is_numeric($keyword) && !is_valid_phone($keyword)) ? (int) $keyword : -1;
            $params[':keyword'] = '%' . $keyword . '%';
        }

        if (!empty($status)) {
            $sql .= 'AND o.order_status = :status ';
            $params[':status'] = trim($status);
        }

        $sql .= 'ORDER BY o.order_id DESC';

        try {
            $statement = $this->pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $statement->bindValue($k, $v);
            }
            $statement->execute();
            $rows = $statement->fetchAll();

            return array_map([$this, 'normalizeOrder'], $rows);
        } catch (Throwable $e) {
            throw new RuntimeException('Không thể tải danh sách đơn hàng từ cơ sở dữ liệu.', 0, $e);
        }
    }

    /**
     * Lấy thông tin một đơn hàng theo ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id, $userId = null)
    {
        $id = (int) $id;
        $scopeUserId = ($userId === null) ? null : (int) $userId;

        if ($id <= 0 || ($scopeUserId !== null && $scopeUserId <= 0)) {
            return null;
        }

        try {
            $sql = 'SELECT o.*, u.username, u.email, COALESCE(NULLIF(u.full_name, \'\'), o.receiver_name) AS customer_name '
                 . 'FROM ' . $this->table . ' o '
                 . 'LEFT JOIN users u ON o.user_id = u.id '
                 . 'WHERE o.order_id = :id';

            $params = [':id' => $id];

            if ($scopeUserId !== null) {
                $sql .= ' AND o.user_id = :user_id';
                $params[':user_id'] = $scopeUserId;
            }

            $sql .= ' LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $statement->bindValue($key, $value, PDO::PARAM_INT);
            }
            $statement->execute();
            $row = $statement->fetch();

            return $row ? $this->normalizeOrder($row) : null;
        } catch (Throwable $e) {
            return null;
        }
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

            return $statement->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Tự động tạo mới một đơn hàng kèm các món hàng chi tiết.
     * Tự động cập nhật giảm số lượng tồn kho (stock_quantity) và dọn dẹp giỏ hàng.
     * Sử dụng transaction để bảo toàn dữ liệu.
     *
     * @param array $orderData
     * @param array $cartItems
     * @return int ID đơn hàng vừa tạo, hoặc 0 nếu thất bại
     */
    public function createOrder(array $orderData, array $cartItems)
    {
        $userId = (int) ($orderData['user_id'] ?? 0);
        $phone  = $orderData['phone'] ?? null;

        if ($userId <= 0 || empty($cartItems) || !is_valid_phone($phone)) {
            return 0;
        }

        try {
            $this->pdo->beginTransaction();

            $sql = 'INSERT INTO ' . $this->table . ' '
                 . '(user_id, receiver_name, phone, shipping_address, total_amount, payment_method, order_status, order_date) '
                 . 'VALUES (:user_id, :receiver_name, :phone, :shipping_address, :total_amount, :payment_method, :order_status, NOW())';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $statement->bindValue(':receiver_name', trim($orderData['receiver_name'] ?? ''));
            $statement->bindValue(':phone', $phone, PDO::PARAM_STR);
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

            $itemSql = 'INSERT INTO order_items '
                     . '(order_id, product_id, product_name, size, color, unit_price, quantity, subtotal) '
                     . 'VALUES (:order_id, :product_id, :product_name, :size, :color, :unit_price, :quantity, :subtotal)';
            $itemStmt = $this->pdo->prepare($itemSql);

            $stockSql = 'UPDATE products SET stock_quantity = stock_quantity - :qty WHERE product_id = :p_id AND stock_quantity >= :qty';
            $stockStmt = $this->pdo->prepare($stockSql);

            foreach ($cartItems as $item) {
                $unitPrice = (float) ($item['price'] ?? 0);
                $qty       = (int) ($item['quantity'] ?? 1);
                $subtotal  = $unitPrice * $qty;
                $pId       = (int) ($item['product_id'] ?? 0);

                if ($pId <= 0 || $qty <= 0 || $unitPrice < 0) {
                    throw new RuntimeException('Dữ liệu sản phẩm trong giỏ hàng không hợp lệ.');
                }

                $itemStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_id', $pId, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_name', trim($item['name'] ?? 'Sản phẩm'));
                $itemStmt->bindValue(':size', !empty($item['size']) ? trim($item['size']) : null);
                $itemStmt->bindValue(':color', !empty($item['color']) ? trim($item['color']) : null);
                $itemStmt->bindValue(':unit_price', $unitPrice);
                $itemStmt->bindValue(':quantity', $qty, PDO::PARAM_INT);
                $itemStmt->bindValue(':subtotal', $subtotal);
                $itemStmt->execute();

                $stockStmt->bindValue(':qty', $qty, PDO::PARAM_INT);
                $stockStmt->bindValue(':p_id', $pId, PDO::PARAM_INT);
                $stockStmt->execute();

                if ($stockStmt->rowCount() < 1) {
                    throw new RuntimeException('Sản phẩm "' . (string) ($item['name'] ?? 'Sản phẩm') . '" không đủ tồn kho.');
                }
            }

            $this->removePurchasedDbCartItems($userId, $cartItems);

            $this->pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return 0;
        }
    }

    private function removePurchasedDbCartItems($userId, array $cartItems)
    {
        $userId = (int) $userId;
        if ($userId <= 0 || empty($cartItems)) {
            return;
        }

        try {
            $sql = 'SELECT ci.cart_item_id, ci.product_id, ci.size, ci.color, ci.quantity '
                 . 'FROM cart_items ci INNER JOIN carts c ON ci.cart_id = c.cart_id '
                 . 'WHERE c.user_id = :u_id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':u_id', $userId, PDO::PARAM_INT);
            $statement->execute();
            $dbItems = $statement->fetchAll();
        } catch (Throwable $e) {
            return;
        }

        $needed = [];
        foreach ($cartItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $signature = $productId . '|' . trim((string) ($item['size'] ?? '')) . '|' . trim((string) ($item['color'] ?? ''));
            $needed[$signature] = ($needed[$signature] ?? 0) + $quantity;
        }

        if (empty($needed) || empty($dbItems)) {
            return;
        }

        try {
            foreach ($dbItems as $dbItem) {
                $signature = (int) $dbItem['product_id'] . '|' . trim((string) ($dbItem['size'] ?? '')) . '|' . trim((string) ($dbItem['color'] ?? ''));
                if (!isset($needed[$signature]) || $needed[$signature] <= 0) {
                    continue;
                }

                $dbQuantity = (int) ($dbItem['quantity'] ?? 0);
                $removeQuantity = min($dbQuantity, $needed[$signature]);
                if ($removeQuantity <= 0) {
                    continue;
                }

                if ($dbQuantity > $removeQuantity) {
                    $update = $this->pdo->prepare('UPDATE cart_items SET quantity = quantity - :qty WHERE cart_item_id = :id');
                    $update->bindValue(':qty', $removeQuantity, PDO::PARAM_INT);
                    $update->bindValue(':id', (int) $dbItem['cart_item_id'], PDO::PARAM_INT);
                    $update->execute();
                } else {
                    $delete = $this->pdo->prepare('DELETE FROM cart_items WHERE cart_item_id = :id');
                    $delete->bindValue(':id', (int) $dbItem['cart_item_id'], PDO::PARAM_INT);
                    $delete->execute();
                }

                $needed[$signature] -= $removeQuantity;
            }
        } catch (Throwable $e) {
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

        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id ORDER BY order_id DESC';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $statement->execute();
            $rows = $statement->fetchAll();

            return array_map([$this, 'normalizeOrder'], $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getNextDeliveryStatus($status)
    {
        $transitions = [
            'pending'    => 'processing',
            'processing' => 'shipping',
            'shipping'   => 'completed',
            'completed'  => null,
        ];

        $status = (string) $status;

        return $transitions[$status] ?? null;
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
        $orderId = (int) $orderId;
        $status  = (string) $status;

        $order = $this->getById($orderId);
        if (!$order) {
            return false;
        }

        $currentStatus = (string) ($order['order_status'] ?? '');
        if ($status !== $this->getNextDeliveryStatus($currentStatus)) {
            return false;
        }

        try {
            $sql = 'UPDATE ' . $this->table . ' SET order_status = :status WHERE order_id = :id AND order_status = :current_status';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':status', $status);
            $statement->bindValue(':current_status', $currentStatus);
            $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
            $res = $statement->execute() && $statement->rowCount() > 0;

            // Nếu đơn hàng online được chuyển sang đang giao hàng (shipping) hoặc hoàn thành (completed) -> chắc chắn đã thanh toán thành công
            if ($res && in_array($status, ['shipping', 'completed'], true)) {
                try {
                    if ((($order['payment_method'] ?? '') !== 'cod') || $status === 'completed') {
                        $this->updatePaymentStatus($orderId, 'paid');
                    }
                } catch (Throwable $pe) {}
            }

            return $res;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Khách hàng gửi yêu cầu hoàn hàng cho một đơn hàng đã giao thành công.
     *
     * @param int $orderId ID đơn hàng
     * @param int $userId  ID người dùng sở hữu đơn
     * @param string $reason Lý do hoàn hàng
     * @param string|null $image Đường dẫn ảnh chụp sản phẩm còn tem mác
     * @return bool
     */
    public function requestReturn($orderId, $userId, $reason, $image = null)
    {
        $orderId = (int) $orderId;
        $userId  = (int) $userId;
        $reason  = trim((string) $reason);
        $image   = !empty($image) ? trim((string) $image) : null;

        if ($orderId <= 0 || $userId <= 0 || $reason === '') {
            return false;
        }

        // Kiểm tra đơn hàng thuộc sở hữu của người dùng và có trạng thái là completed
        $order = $this->getById($orderId, $userId);
        if (!$order) {
            return false;
        }

        $currentStatus = $order['order_status'] ?? '';
        if ($currentStatus !== 'completed') {
            return false;
        }

        try {
            // Thử cập nhật trạng thái, lý do và ảnh chụp tem mác
            $sql = 'UPDATE ' . $this->table . ' 
                    SET order_status = :status, return_reason = :reason, return_image = :image 
                    WHERE order_id = :id AND user_id = :user_id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':status', 'return_request');
            $statement->bindValue(':reason', $reason);
            $statement->bindValue(':image', $image);
            $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            // Fallback nếu cột return_image chưa có
            try {
                $sqlFallback = 'UPDATE ' . $this->table . ' 
                                SET order_status = :status, return_reason = :reason 
                                WHERE order_id = :id AND user_id = :user_id';
                $statement = $this->pdo->prepare($sqlFallback);
                $statement->bindValue(':status', 'return_request');
                $statement->bindValue(':reason', $reason);
                $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
                $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);

                return $statement->execute();
            } catch (Throwable $e2) {
                return false;
            }
        }
    }

    private function returnStatuses()
    {
        return ['return_request', 'return_accepted', 'return_rejected', 'returned'];
    }

    private function returnRequestCondition()
    {
        return "(o.order_status IN ('return_request', 'return_accepted', 'return_rejected', 'returned') "
             . "OR (o.return_reason IS NOT NULL AND TRIM(o.return_reason) <> ''))";
    }

    public function getReturnRequests($keyword = null, $status = null)
    {
        $sql = 'SELECT o.*, u.username, u.email, COALESCE(NULLIF(u.full_name, \'\'), o.receiver_name) AS customer_name '
             . 'FROM ' . $this->table . ' o '
             . 'LEFT JOIN users u ON o.user_id = u.id '
             . 'WHERE ' . $this->returnRequestCondition() . ' ';

        $params = [];
        $keyword = is_string($keyword) ? trim($keyword) : '';

        if ($keyword !== '') {
            $sql .= 'AND (o.order_id = :order_id_exact OR o.receiver_name LIKE :keyword OR o.phone LIKE :keyword OR u.username LIKE :keyword OR u.full_name LIKE :keyword) ';
            $params[':order_id_exact'] = (is_numeric($keyword) && !is_valid_phone($keyword)) ? (int) $keyword : -1;
            $params[':keyword'] = '%' . $keyword . '%';
        }

        $status = is_string($status) ? trim($status) : '';
        if (in_array($status, $this->returnStatuses(), true)) {
            $sql .= 'AND o.order_status = :status ';
            $params[':status'] = $status;
        }

        $sql .= 'ORDER BY FIELD(o.order_status, \'return_request\', \'return_accepted\', \'return_rejected\', \'returned\'), o.order_id DESC';

        try {
            $statement = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $statement->bindValue($key, $value);
            }
            $statement->execute();

            return array_map([$this, 'normalizeOrder'], $statement->fetchAll());
        } catch (Throwable $e) {
            throw new RuntimeException('Không thể tải danh sách yêu cầu hoàn hàng từ cơ sở dữ liệu.', 0, $e);
        }
    }

    public function getReturnStats()
    {
        $sql = 'SELECT COUNT(*) AS total, '
             . 'SUM(CASE WHEN o.order_status = \'return_request\' THEN 1 ELSE 0 END) AS pending, '
             . 'SUM(CASE WHEN o.order_status = \'return_accepted\' THEN 1 ELSE 0 END) AS accepted, '
             . 'SUM(CASE WHEN o.order_status = \'return_rejected\' THEN 1 ELSE 0 END) AS rejected '
             . 'FROM ' . $this->table . ' o '
             . 'WHERE ' . $this->returnRequestCondition();

        try {
            $row = $this->pdo->query($sql)->fetch();
        } catch (Throwable $e) {
            $row = null;
        }

        return [
            'total'    => (int) ($row['total'] ?? 0),
            'pending'  => (int) ($row['pending'] ?? 0),
            'accepted' => (int) ($row['accepted'] ?? 0),
            'rejected' => (int) ($row['rejected'] ?? 0),
        ];
    }

    public function getReturnRequestById($orderId)
    {
        $order = $this->getById($orderId);
        if (!$order) {
            return null;
        }

        $status    = (string) ($order['order_status'] ?? '');
        $hasReason = trim((string) ($order['return_reason'] ?? '')) !== '';

        if (!in_array($status, $this->returnStatuses(), true) && !$hasReason) {
            return null;
        }

        return $order;
    }

    public function updateReturnRequestDecision($orderId, $decision)
    {
        $orderId = (int) $orderId;
        $map = [
            'accept' => 'return_accepted',
            'reject' => 'return_rejected',
        ];

        if ($orderId <= 0 || !isset($map[$decision])) {
            return false;
        }

        try {
            $sql = 'UPDATE ' . $this->table . ' '
                 . 'SET order_status = :status '
                 . 'WHERE order_id = :id AND order_status = \'return_request\'';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':status', $map[$decision]);
            $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function completeReturn($orderId)
    {
        $orderId = (int) $orderId;
        if ($orderId <= 0) {
            return false;
        }

        try {
            $sql = 'UPDATE ' . $this->table . ' '
                 . 'SET order_status = :status '
                 . 'WHERE order_id = :id AND order_status = \'return_accepted\'';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':status', 'returned');
            $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Cập nhật trạng thái thanh toán của đơn hàng (unpaid, paid, refunded).
     *
     * @param int $orderId
     * @param string $paymentStatus
     * @return bool
     */
    public function updatePaymentStatus($orderId, $paymentStatus)
    {
        $orderId = (int) $orderId;
        $allowed = ['unpaid', 'paid', 'refunded'];
        if ($orderId <= 0 || !in_array($paymentStatus, $allowed, true)) {
            return false;
        }

        try {
            $sql = 'UPDATE ' . $this->table . ' SET payment_status = :p_status WHERE order_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':p_status', $paymentStatus);
            $statement->bindValue(':id', $orderId, PDO::PARAM_INT);

            return $statement->execute();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Admin xác nhận thanh toán thành công và đồng thời chuyển sang trạng thái đang giao hàng (shipping).
     * Áp dụng cho các đơn hàng thanh toán Online khi đã nhận được tiền chuyển khoản.
     *
     * @param int $orderId
     * @return bool
     */
    public function confirmPaymentAndShip($orderId)
    {
        $orderId = (int) $orderId;
        if ($orderId <= 0) {
            return false;
        }

        try {
            $sql = 'UPDATE ' . $this->table . '
                    SET payment_status = :p_status, order_status = :o_status
                    WHERE order_id = :id
                      AND order_status = :current_status
                      AND (payment_method IS NULL OR payment_method <> :cod)
                      AND (payment_status IS NULL OR payment_status = :unpaid)';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':p_status', 'paid');
            $statement->bindValue(':o_status', 'shipping');
            $statement->bindValue(':current_status', 'processing');
            $statement->bindValue(':cod', 'cod');
            $statement->bindValue(':unpaid', 'unpaid');
            $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (Throwable $e) {
            // Fallback nếu cột payment_status chưa được tạo
            try {
                $sqlFallback = 'UPDATE ' . $this->table . '
                                SET order_status = :o_status
                                WHERE order_id = :id
                                  AND order_status = :current_status
                                  AND (payment_method IS NULL OR payment_method <> :cod)';
                $statement = $this->pdo->prepare($sqlFallback);
                $statement->bindValue(':o_status', 'shipping');
                $statement->bindValue(':current_status', 'processing');
                $statement->bindValue(':cod', 'cod');
                $statement->bindValue(':id', $orderId, PDO::PARAM_INT);
                $statement->execute();

                return $statement->rowCount() > 0;
            } catch (Throwable $e2) {
                return false;
            }
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
            throw new RuntimeException('Không thể đếm số đơn hàng từ cơ sở dữ liệu.', 0, $e);
        }

        throw new RuntimeException('Không thể đếm số đơn hàng từ cơ sở dữ liệu.');
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
                'payment_status'   => 'unpaid',
                'order_status'     => 'cancelled',
                'return_reason'    => null,
                'order_date'       => '2026-11-14 11:10:00',
            ],
            [
                'order_id'         => 6,
                'user_id'          => 1,
                'username'         => 'nguyenan',
                'customer_name'    => 'Nguyễn Văn An',
                'receiver_name'    => 'Nguyễn Văn An',
                'phone'            => '0987654321',
                'shipping_address' => 'Số 13 Trịnh Văn Bô, Nam Từ Liêm, Hà Nội',
                'total_amount'     => 640000,
                'payment_method'   => 'banking',
                'payment_status'   => 'paid',
                'order_status'     => 'return_request',
                'return_reason'    => 'Sản phẩm bị lỗi bung chỉ đường may ở tay áo, cần hoàn hàng để đổi mẫu khác.',
                'order_date'       => '2026-11-16 10:20:00',
            ],
            [
                'order_id'         => 7,
                'user_id'          => 2,
                'username'         => 'tranmai',
                'customer_name'    => 'Trần Thị Mai',
                'receiver_name'    => 'Trần Thị Mai',
                'phone'            => '0912345678',
                'shipping_address' => 'Tòa Keangnam, Mễ Trì, Cầu Giấy, Hà Nội',
                'total_amount'     => 850000,
                'payment_method'   => 'banking',
                'payment_status'   => 'refunded',
                'order_status'     => 'returned',
                'return_reason'    => 'Khách mang không vừa chân, shop đã thu hồi hàng và hoàn tiền thành công.',
                'order_date'       => '2026-11-13 14:00:00',
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
