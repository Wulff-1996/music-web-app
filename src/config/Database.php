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

    public function update(string $tableName, $idName, int $idValue, array $data): bool{
        $query = "UPDATE $tableName SET ";
        $params = array();
        $isFirstIndex = true;
        foreach ($data as $column => $value){
            if ($isFirstIndex){
                $isFirstIndex = false;
                $query .= "$column = :$column";
            } else {
                $query .= ", $column = :$column";
            }

            // add binding param
            $params[":$column"] = $value;
        }

        $query .= " WHERE $idName = :id";
        $params[':id'] = $idValue;

        $stmt = $this->conn->prepare($query);

        try {
            $stmt->execute($params);
            return true;
        } catch (PDOException $e){
            return false;
        }
    }

    public function insert(string $tableName, array $data){
        <<<SQL
        INSERT INTO track (name, age, color)
        VALUES (?, ?, ?)
SQL;

        $query = "INSERT INTO $tableName ";
        $insetColumns = '';
        $valuesPlaceholders = '';
        $values = array();

        $isFirst = true;
        foreach($data as $key => $value){
            if ($isFirst){
                $isFirst = false;

                $insetColumns .= "($key";
                $valuesPlaceholders .= '(?';

            } else {
                $insetColumns .= ", $key";
                $valuesPlaceholders .= ', ?';
            }

            $values[] = $value;
        }

        $insetColumns .= ')';
        $valuesPlaceholders .= ');';

        $query .= $insetColumns . ' VALUES ' . $valuesPlaceholders;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($values);
        return $this->conn->lastInsertId();
    }
}