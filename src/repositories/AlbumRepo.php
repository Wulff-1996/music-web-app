<?php

namespace Wulff\repositories;

use PDO;
use PDOException;
use Wulff\config\Database;
use Wulff\entities\Album;
use Wulff\entities\EntityMapper;
use Wulff\util\RepoUtil;

class AlbumRepo
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

    public function find($id)
    {
        $query = <<<'SQL'
                SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId
                FROM album al
                LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
                WHERE al.AlbumId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $album = $stmt->fetch();

        return $album;
    }

    public function getTracksByAlbumId(int $id){
        $query = <<<'SQL'
              SELECT t.TrackId, t.Name AS TrackName, g.GenreId, g.Name AS GenreName, 
                     m.MediaTypeId ,m.Name AS MediaName, 
                     t.Composer, t.Milliseconds, t.Bytes, t.UnitPrice
            FROM track t
            LEFT JOIN album al ON t.AlbumId = al.AlbumId
            LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
            LEFT JOIN genre g ON g.GenreId = t.GenreId
            WHERE al.AlbumId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $track = $stmt->fetchAll();

        return $track;
    }

    public function findAll($page)
    {
        $query = <<<SQL
            SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId, COUNT(t.TrackId) AS TrackTotal
            FROM album al
            LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
            LEFT JOIN track t on al.AlbumId = t.AlbumId
            GROUP BY al.AlbumId
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

        return EntityMapper::toJsonAlbumMultiple($result);
    }

    public function findAllByTitle($title, $page){
        $query = <<<SQL
            SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId, COUNT(t.TrackId) AS TrackTotal
            FROM album al
            LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
            LEFT JOIN track t on al.AlbumId = t.AlbumId
            WHERE al.Title LIKE :title
            GROUP BY al.AlbumId
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

        return EntityMapper::toJsonAlbumMultiple($result);
    }

    public function findAllByArtistId($artistId, $page){
        $query = <<<SQL
            SELECT al.AlbumId, al.Title, ar.Name, al.ArtistId, COUNT(t.TrackId) AS TrackTotal
            FROM album al
            LEFT JOIN artist ar on al.ArtistId = ar.ArtistId
            LEFT JOIN track t on al.AlbumId = t.AlbumId
            WHERE al.ArtistId = :artistId
            GROUP BY al.AlbumId
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

        return EntityMapper::toJsonAlbumMultiple($result);
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