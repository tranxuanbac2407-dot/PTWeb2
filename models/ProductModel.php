<?php

class ProductModel extends BaseModel
{
    protected $table = 'products';

    public function searchByKeyword($keyword, $limit = 20)
    {
        $sql = 'SELECT * FROM ' . $this->table
             . ' WHERE product_name LIKE :keyword'
             . ' ORDER BY product_id DESC'
             . ' LIMIT ' . (int) $limit;

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':keyword', '%' . $keyword . '%');
        $statement->execute();

        return $statement->fetchAll();
    }
}
