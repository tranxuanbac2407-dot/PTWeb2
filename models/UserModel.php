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
        $sql = 'INSERT INTO ' . $this->table
             . ' (full_name, username, email, phone, password, role)'
             . ' VALUES (:full_name, :username, :email, :phone, :password, :role)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':full_name' => $data['full_name'],
            ':username'  => $data['username'],
            ':email'     => $data['email'],
            ':phone'     => $data['phone'],
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
}
