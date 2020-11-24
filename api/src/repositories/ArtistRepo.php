<?php
namespace Src\Repositories;

require_once 'src/util/RepoUtil.php';

use PDO;
use PDOException;
use Src\Entities\Artist;
use Src\util\RepoUtil;

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

        $offset = RepoUtil::getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
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

        $offset = RepoUtil::getOffset($page);
        $name = '%' . $name . '%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);

        $stmt->execute();
        $artists = $stmt->fetchAll();
        $this->db->close();

        $result = array();
        $result['page'] = $page;
        $result['artists'] = $artists;


        return $result;
    }

    function createArtist(Artist $artist){
        $query = <<<SQL
            INSERT INTO artist (Name)
            VALUES (:name);
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $artist->name, PDO::PARAM_STR);
        $stmt->execute();
        $artistId = $this->db->conn->lastInsertId();
        $this->db->close();

        // return created artist id
        return $artistId;
    }

    function updateArtist(Artist $artist){
        $query = <<<SQL
            UPDATE artist 
            SET Name = :name
            WHERE ArtistId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':name', $artist->name, PDO::PARAM_STR);
        $stmt->bindValue(':id', $artist->id, PDO::PARAM_INT);
        $stmt->execute();
        $this->db->close();

        return true;
    }

    function delete($id){
        $query = <<<SQL
            DELETE FROM artist WHERE ArtistId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e){
            return false;
        } finally {
            $this->db->close();
        }
    }
}