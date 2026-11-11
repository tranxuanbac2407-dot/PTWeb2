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
     * Thêm tài khoản mới, trả về id vừa tạo.
     * $data gồm: full_name, username, email, phone, password (đã hash), role.
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
}
