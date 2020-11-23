<?php

const COUNT = 10;

class ArtistRepo
{
    private $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    function find($id)
    {
        $query = <<<'SQL'
                SELECT a.ArtistId, a.Name
                FROM artist a 
                WHERE a.ArtistId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $artist = $stmt->fetch();
        $this->db->close();

        return $artist;
    }

    function findAll($page)
    {
        $query = <<<SQL
            SELECT *
            FROM artist
            LIMIT :offset, :count;
SQL;

        $offset = $this->getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $artists = $stmt->fetchAll();
        $this->db->close();

        $result = array();
        $result['page'] = $page;
        $result['artists'] = $artists;


        return $result;
    }

    function findAllByName($name, $page = 0)
    {
        $query = <<<"SQL"
            SELECT *
            FROM artist
            WHERE Name LIKE :name
            LIMIT :offset, :count;
SQL;

        $offset = $this->getOffset($page);
        $name = '%' . $name . '%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', COUNT, PDO::PARAM_INT);

        $stmt->execute();
        $artists = $stmt->fetchAll();
        $this->db->close();

        $result = array();
        $result['page'] = $page;
        $result['artists'] = $artists;


        return $result;
    }

    private function getOffset($page)
    {
        return COUNT * $page;
    }
}