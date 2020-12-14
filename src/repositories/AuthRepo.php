<?php


namespace Wulff\repositories;

use PDO;
use http\Params;
use Wulff\config\Database;
use Wulff\entities\Customer;

class AuthRepo
{
    private Database $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    public function closeConnection()
    {
        $this->db->close();
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
                SELECT CustomerId, Password FROM customer WHERE Email = :email;
            SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function isEmailUnique(string $email): bool
    {
        $query = <<<'SQL'
                SELECT Email from customer where Email = :email;
            SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch()){
            // email exists
            return false;
        } else return true;
    }

    public function createCustomer(Customer $customer): ?int
    {
        $query = <<<'SQL'
                INSERT INTO customer (FirstName, LastName, Password, Company, Address, City, State, Country, PostalCode, Phone, Fax, Email) 
                VALUES (:firstName, :lastName, :password, :company, :address, :city, :state, :country, :postalCode, :phone, :fax, :email);
SQL;

        $stmt = $this->db->conn->prepare($query);

        $stmt->bindValue(':firstName', $customer->getFirstName(), PDO::PARAM_STR);
        $stmt->bindValue(':lastName', $customer->getLastName(), PDO::PARAM_STR);
        $stmt->bindValue(':password', $customer->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(':company', $customer->getCompany(), PDO::PARAM_STR);
        $stmt->bindValue(':address', $customer->getAddress(), PDO::PARAM_STR);
        $stmt->bindValue(':city', $customer->getCity(), PDO::PARAM_STR);
        $stmt->bindValue(':state', $customer->getState(), PDO::PARAM_STR);
        $stmt->bindValue(':country', $customer->getCountry(), PDO::PARAM_STR);
        $stmt->bindValue(':postalCode', $customer->getPostalCode(), PDO::PARAM_STR);
        $stmt->bindValue(':phone', $customer->getPhone(), PDO::PARAM_STR);
        $stmt->bindValue(':fax', $customer->getFax(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $customer->getEmail(), PDO::PARAM_STR);

        $stmt->execute();
        $customerId = $this->db->conn->lastInsertId();

        // return created customer id
        return $customerId;
    }
}