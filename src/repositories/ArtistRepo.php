<?php

namespace Wulff\repositories;

use PDO;
use PDOException;
use Wulff\config\Database;
use Wulff\entities\Artist;
use Wulff\util\RepoUtil;

class ArtistRepo
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

    function find($id)
    {
        $query = <<<'SQL'
                SELECT a.ArtistId, a.Name, COUNT(al.AlbumId) AS AlbumTotal
                FROM artist a
                LEFT JOIN album al on a.ArtistId = al.ArtistId
                WHERE a.ArtistId = :id
                GROUP BY a.ArtistId;
SQL;
        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $artist = $stmt->fetch();

        return $artist;
    }

    function findAll($page, $count = null)
    {
        $query = <<<'SQL'
            SELECT a.ArtistId, a.Name, COUNT(al.AlbumId) AS AlbumTotal
            FROM artist a
            LEFT JOIN album al on a.ArtistId = al.ArtistId
            GROUP BY a.ArtistId
            LIMIT :offset, :count;
SQL;

        $count = ($count) ? $count : RepoUtil::COUNT;
        $offset = RepoUtil::getOffset($page, $count);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', $count, PDO::PARAM_INT);
        $stmt->execute();
        $artists = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['artists'] = $artists;

        return $result;
    }

    function findAllByName($name, $page = 0, $count = null)
    {
        $query = <<<'SQL'
            SELECT a.ArtistId, a.Name, COUNT(al.AlbumId) AS AlbumTotal
            FROM artist a
            LEFT JOIN album al on a.ArtistId = al.ArtistId
            WHERE Name LIKE :name
            GROUP BY a.ArtistId
            LIMIT :offset, :count;
SQL;

        $count = ($count) ? $count : RepoUtil::COUNT;
        $offset = RepoUtil::getOffset($page, $count);
        $name = '%' . $name . '%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', $count, PDO::PARAM_INT);

        $stmt->execute();
        $artists = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['artists'] = $artists;

        return $result;
    }

    function createArtist(Artist $artist)
    {
        $query = <<<SQL
            INSERT INTO artist (Name)
            VALUES (:name);
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $artist->name, PDO::PARAM_STR);
        $stmt->execute();
        $artistId = $this->db->conn->lastInsertId();

        // return created artist id
        return $artistId;
    }

    function updateArtist(Artist $artist)
    {
        $query = <<<SQL
            UPDATE artist 
            SET Name = :name
            WHERE ArtistId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $artist->name, PDO::PARAM_STR);
        $stmt->bindValue(':id', $artist->id, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }

    function delete($id)
    {
        $query = <<<SQL
            DELETE FROM artist WHERE ArtistId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}