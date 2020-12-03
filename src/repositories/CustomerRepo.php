<?php

namespace Wulff\repositories;

use PDO;
use Wulff\config\Database;

class CustomerRepo
{
    private Database $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    public function closeConnection(){
        $this->db->close();
    }

    public function find($id)
    {
        $query = <<<'SQL'
                SELECT *
                FROM customer c
                WHERE CustomerId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $customer = $stmt->fetch();

        return $customer;
    }
}