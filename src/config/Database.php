<?php

namespace Wulff\config;

use PDO;
use PDOException;

class Database
{

    // specify your own database credentials
    private string $host = 'localhost:3306';
    private string $db_name = 'chinook_abridged';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';
    public ?PDO $conn = null;

    public function __construct(){
        $this->getConnection();
    }

    public function getConnection(){
        $this->conn = null;

        $dsn = "mysql:host=$this->host;dbname=$this->db_name;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false // makes int return as int except double they are string
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