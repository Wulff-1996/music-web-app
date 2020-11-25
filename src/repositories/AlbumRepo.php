<?php

namespace Wulff\repositories;

use PDO;
use PDOException;
use Wulff\config\Database;
use Wulff\entities\Album;
use Wulff\util\RepoUtil;

class AlbumRepo
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
                SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId 
                FROM album al
                LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
                WHERE AlbumId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $album = $stmt->fetch();

        return $album;
    }

    public function findAll($page)
    {
        $query = <<<SQL
            SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId 
            FROM album al
            LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
            LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $albums = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['albums'] = $albums;

        return $result;
    }

    public function findAllByTitle($title, $page){
        $query = <<<SQL
            SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId 
            FROM album al
            LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
            WHERE al.Title LIKE :title
            LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page);
        $title = '%' . $title . '%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $albums = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['albums'] = $albums;

        return $result;
    }

    public function findAllByArtistId($artistId, $page){
        $query = <<<SQL
            SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId 
            FROM album al
            LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
            WHERE al.ArtistId = :artistId
            LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':artistId', $artistId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $albums = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['albums'] = $albums;

        return $result;
    }

    public function add(Album $album)
    {
        $query = <<<SQL
           INSERT INTO album (title, artistid) 
           VALUES (:title, :artistId);
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':title', $album->title, PDO::PARAM_STR);
        $stmt->bindValue(':artistId', $album->artistId, PDO::PARAM_INT);
        $stmt->execute();
        $albumId = $this->db->conn->lastInsertId();

        // return created artist id
        return $albumId;

    }

    public function update(Album $album)
    {
        $query = <<<SQL
            UPDATE album 
            SET Title = :title,
                ArtistId = :artistId
            WHERE AlbumId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':title', $album->title, PDO::PARAM_STR);
        $stmt->bindValue(':artistId', $album->artistId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $album->id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e){
            return false;
        }
    }

    public function delete($id)
    {
        $query = <<<SQL
            DELETE FROM album WHERE AlbumId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e){
            return false;
        }
    }
}