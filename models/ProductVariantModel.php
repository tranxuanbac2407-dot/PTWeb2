<?php

class ProductVariantModel extends BaseModel
{
    protected $table = 'product_variants';

    public function __construct(PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;

            return;
        }

        parent::__construct();
    }

    public function getByProduct($productId)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE product_id = :product_id ORDER BY variant_id ASC';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':product_id', (int) $productId, PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getById($variantId)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE variant_id = :id LIMIT 1';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $variantId, PDO::PARAM_INT);
            $statement->execute();
            $row = $statement->fetch();

            return $row !== false ? $row : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function findExact($productId, $color, $size)
    {
        try {
            $sql = 'SELECT * FROM ' . $this->table . ' '
                 . 'WHERE product_id = :product_id AND color = :color AND size = :size LIMIT 1';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':product_id', (int) $productId, PDO::PARAM_INT);
            $statement->bindValue(':color', normalize_variant_value($color));
            $statement->bindValue(':size', normalize_variant_value($size));
            $statement->execute();
            $row = $statement->fetch();

            return $row !== false ? $row : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function getStockTotals()
    {
        $totals = [];
        try {
            $sql = 'SELECT product_id, SUM(stock_quantity) AS total FROM ' . $this->table . ' GROUP BY product_id';
            foreach ($this->pdo->query($sql)->fetchAll() as $row) {
                $totals[(int) $row['product_id']] = (int) $row['total'];
            }
        } catch (Throwable $e) {
            return [];
        }

        return $totals;
    }

    public function create($productId, $color, $size, $stock, $isActive = 1)
    {
        try {
            $sql = 'INSERT INTO ' . $this->table . ' '
                 . '(product_id, color, size, stock_quantity, is_active) '
                 . 'VALUES (:product_id, :color, :size, :stock_quantity, :is_active)';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':product_id', (int) $productId, PDO::PARAM_INT);
            $statement->bindValue(':color', normalize_variant_value($color));
            $statement->bindValue(':size', normalize_variant_value($size));
            $statement->bindValue(':stock_quantity', (int) $stock, PDO::PARAM_INT);
            $statement->bindValue(':is_active', (int) $isActive, PDO::PARAM_INT);
            $statement->execute();

            return (int) $this->pdo->lastInsertId();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function updateStock($variantId, $stock, $isActive = null)
    {
        try {
            $sql = 'UPDATE ' . $this->table . ' SET stock_quantity = :stock_quantity';
            if ($isActive !== null) {
                $sql .= ', is_active = :is_active';
            }
            $sql .= ' WHERE variant_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':stock_quantity', (int) $stock, PDO::PARAM_INT);
            if ($isActive !== null) {
                $statement->bindValue(':is_active', (int) $isActive, PDO::PARAM_INT);
            }
            $statement->bindValue(':id', (int) $variantId, PDO::PARAM_INT);
            $statement->execute();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function setActive($variantId, $isActive)
    {
        try {
            $sql = 'UPDATE ' . $this->table . ' SET is_active = :is_active WHERE variant_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':is_active', (int) $isActive, PDO::PARAM_INT);
            $statement->bindValue(':id', (int) $variantId, PDO::PARAM_INT);
            $statement->execute();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function decreaseStock($variantId, $quantity)
    {
        try {
            $sql = 'UPDATE ' . $this->table . ' '
                 . 'SET stock_quantity = stock_quantity - :qty '
                 . 'WHERE variant_id = :id AND is_active = 1 AND stock_quantity >= :qty';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':qty', (int) $quantity, PDO::PARAM_INT);
            $statement->bindValue(':id', (int) $variantId, PDO::PARAM_INT);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function increaseStock($variantId, $quantity)
    {
        try {
            $sql = 'UPDATE ' . $this->table . ' SET stock_quantity = stock_quantity + :qty WHERE variant_id = :id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':qty', (int) $quantity, PDO::PARAM_INT);
            $statement->bindValue(':id', (int) $variantId, PDO::PARAM_INT);
            $statement->execute();

            return $statement->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function syncForProduct($productId, array $rows, array $cartKeys = [])
    {
        $submitted = [];
        foreach ($rows as $row) {
            $color = normalize_variant_value($row['color'] ?? '');
            $size  = normalize_variant_value($row['size'] ?? '');
            if ($color === '' || $size === '') {
                throw new RuntimeException('Màu và size của biến thể không được để trống.');
            }
            $key = $color . '|' . $size;
            if (isset($submitted[$key])) {
                throw new RuntimeException('Tổ hợp ' . $color . ' - ' . $size . ' bị lặp. Vui lòng kiểm tra lại ma trận tồn kho.');
            }
            $submitted[$key] = [
                'color' => $color,
                'size'  => $size,
                'stock' => (int) ($row['stock'] ?? 0),
            ];
        }

        $existing = [];
        foreach ($this->getByProduct($productId) as $variant) {
            $key = normalize_variant_value($variant['color']) . '|' . normalize_variant_value($variant['size']);
            $existing[$key] = $variant;
        }

        foreach ($existing as $key => $variant) {
            if (isset($submitted[$key])) {
                continue;
            }
            if ((int) $variant['stock_quantity'] > 0) {
                throw new RuntimeException('Biến thể ' . $variant['color'] . ' - ' . $variant['size'] . ' vẫn còn tồn kho. Vui lòng đưa tồn về 0 trước khi bỏ biến thể.');
            }
            if (in_array($key, $cartKeys, true)) {
                throw new RuntimeException('Biến thể ' . $variant['color'] . ' - ' . $variant['size'] . ' đang có trong giỏ hàng. Vui lòng yêu cầu khách cập nhật giỏ trước khi bỏ biến thể.');
            }
            if (!$this->setActive((int) $variant['variant_id'], 0)) {
                throw new RuntimeException('Không thể ngừng sử dụng biến thể ' . $variant['color'] . ' - ' . $variant['size'] . '.');
            }
        }

        foreach ($submitted as $key => $row) {
            if (isset($existing[$key])) {
                if (!$this->updateStock((int) $existing[$key]['variant_id'], $row['stock'], 1)) {
                    throw new RuntimeException('Không thể cập nhật tồn kho biến thể ' . $row['color'] . ' - ' . $row['size'] . '.');
                }
                continue;
            }
            if (!$this->create($productId, $row['color'], $row['size'], $row['stock'], 1)) {
                throw new RuntimeException('Không thể tạo biến thể ' . $row['color'] . ' - ' . $row['size'] . '.');
            }
        }
    }
}
