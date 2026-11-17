<?php

/**
 * UserModel - Truy vấn dữ liệu bảng `users`.
 *
 * Chỉ chịu trách nhiệm làm việc với database (PDO + Prepared Statements),
 * không xử lý giao diện và không chứa logic điều hướng.
 */
class UserModel extends BaseModel
{
    protected $table = 'users';

    /**
     * Tìm tài khoản theo tên đăng nhập HOẶC email (dùng cho đăng nhập).
     */
    public function findByLogin($login)
    {
        $sql = 'SELECT * FROM ' . $this->table
             . ' WHERE username = :login OR email = :login'
             . ' LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':login', $login);
        $statement->execute();

        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Kiểm tra tên đăng nhập đã tồn tại chưa.
     */
    public function usernameExists($username)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE username = :username';

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':username', $username);
        $statement->execute();

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * Kiểm tra email đã tồn tại chưa.
     */
    public function emailExists($email)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE email = :email';

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->execute();

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * ThA�m tA�i kho���n m��>i, tr��� v��? id v���a t���o.
     * $data g��"m: full_name, username, email, phone, password (�`A� hash), role.
     */
    public function create(array $data)
    {
        $phone = $data['phone'] ?? null;
        if ($phone !== null && !is_valid_phone($phone)) {
            return 0;
        }

        $sql = 'INSERT INTO ' . $this->table
             . ' (full_name, username, email, phone, password, role)'
             . ' VALUES (:full_name, :username, :email, :phone, :password, :role)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':full_name' => $data['full_name'],
            ':username'  => $data['username'],
            ':email'     => $data['email'],
            ':phone'     => $phone === null ? null : $phone,
            ':password'  => $data['password'],
            ':role'      => $data['role'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Lấy danh sách tài khoản cho trang quản trị (không bao gồm mật khẩu).
     * Chỉ truy vấn các cột thực tế đang tồn tại trong bảng users.
     *
     * @return array
     */
    public function getAllForAdmin()
    {
        try {
            $columnStatement = $this->pdo->query('SHOW COLUMNS FROM ' . $this->table);
            $columns = $columnStatement ? $columnStatement->fetchAll(PDO::FETCH_COLUMN) : [];

            $wantedColumns = ['id', 'full_name', 'username', 'email', 'phone', 'role'];
            $selectColumns = [];

            foreach ($wantedColumns as $column) {
                if (in_array($column, $columns, true)) {
                    $selectColumns[] = '`' . $column . '`';
                }
            }

            if (empty($selectColumns)) {
                return [];
            }

            $sql = 'SELECT ' . implode(', ', $selectColumns)
                 . ' FROM ' . $this->table
                 . ' ORDER BY `id` DESC';

            $statement = $this->pdo->query($sql);

            return $statement ? $statement->fetchAll() : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Đếm tổng số tài khoản trong hệ thống.
     *
     * @return int
     */
    public function countAll()
    {
        try {
            $sql = 'SELECT COUNT(*) AS total FROM ' . $this->table;
            $statement = $this->pdo->query($sql);
            $row = $statement ? $statement->fetch() : null;

            return isset($row['total']) ? (int) $row['total'] : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Lấy thông tin tài khoản theo ID (không bao gồm mật khẩu).
     *
     * @param int $id
     * @return array|null
     */
    public function findById($id)
    {
        try {
            $columnStatement = $this->pdo->query('SHOW COLUMNS FROM ' . $this->table);
            $columns = $columnStatement ? $columnStatement->fetchAll(PDO::FETCH_COLUMN) : [];

            $wantedColumns = ['id', 'full_name', 'username', 'email', 'phone', 'role'];
            $selectColumns = [];

            foreach ($wantedColumns as $column) {
                if (in_array($column, $columns, true)) {
                    $selectColumns[] = '`' . $column . '`';
                }
            }

            if (!in_array('id', $columns, true) || empty($selectColumns)) {
                return null;
            }

            $sql = 'SELECT ' . implode(', ', $selectColumns)
                 . ' FROM ' . $this->table
                 . ' WHERE `id` = :id LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $user = $statement->fetch();

            return $user ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Kiểm tra tài khoản có đơn hàng hay không.
     *
     * @param int $userId
     * @return bool
     */
    public function hasOrders($userId)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM `orders` WHERE `user_id` = :user_id';
            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':user_id', (int) $userId, PDO::PARAM_INT);
            $statement->execute();

            return (int) $statement->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Xóa tài khoản khách hàng (chỉ cho phép role khách hàng, không xóa admin).
     * Kiểm tra lại vai trò và sự tồn tại từ database trước khi xóa.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCustomerById($id)
    {
        try {
            $user = $this->findById($id);
            if (!$user) {
                return false;
            }

            $role = (string) ($user['role'] ?? '');
            if ($role === 'admin' || $role !== 'client') {
                return false;
            }

            $sql = 'DELETE FROM ' . $this->table
                 . ' WHERE `id` = :id AND `role` = :role LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->bindValue(':role', 'client');

            return $statement->execute() && $statement->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}
