<?php
namespace Src\Config;

use PDO;
use PDOException;

class Database
{

    // specify your own database credentials
    private $host = 'localhost:3306';
    private $db_name = 'chinook_abridged';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    public $conn = null;

    public function __construct(){
        $this->getConnection();
    }

    public function getConnection(){
        $this->conn = null;

        $dsn = "mysql:host=$this->host;dbname=$this->db_name;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
    }

    public function close(){
        $this->conn = null;
    }
}