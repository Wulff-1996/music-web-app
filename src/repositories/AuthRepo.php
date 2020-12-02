<?php


namespace Wulff\repositories;


use http\Params;
use Wulff\config\Database;

class AuthRepo
{
    private Database $db;

    function __construct($db)
    {
        $this->db = $db;
    }


    public function getAdminPassword()
    {
        $query = <<<'SQL'
                SELECT Password FROM admin;
            SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getCustomerLogin(string $email)
    {
        $query = <<<'SQL'
                SELECT CustomerId, Password FROM customer WHERE Email = ?;
            SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}