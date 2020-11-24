<?php

namespace Src\repositories;

require_once 'src/util/RepoUtil.php';
require_once 'src/interfaces/RepoInterface.php';

use PDO;
use PDOException;
use Src\Config\Database;
use Src\entities\Track;
use Src\Interfaces\RepoInterface;
use Src\util\RepoUtil;

class TrackRepo implements RepoInterface
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
                SELECT t.TrackId AS 'id', t.Name AS 'title', al.Title AS 'album', 
                       ar.Name AS 'artist', g.Name AS 'genre', m.Name AS 'media', 
                       t.Composer AS 'composer', t.Milliseconds AS 'milliseconds', 
                       t.Bytes AS 'bytes', t.UnitPrice AS 'unit_price'
                FROM track t
                LEFT JOIN album al ON t.AlbumId = al.AlbumId
                LEFT JOIN artist ar ON al.ArtistId = ar.ArtistId
                LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
                LEFT JOIN genre g ON g.GenreId = t.GenreId
                WHERE t.TrackId = :id;
SQL;

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $track = $stmt->fetch();



        return $track;
    }

    public function findAll($page)
    {
        $query = <<<SQL
            SELECT t.TrackId AS 'id', t.Name AS 'title', al.Title AS 'album', 
                       ar.Name AS 'artist', g.Name AS 'genre', m.Name AS 'media', 
                       t.Composer AS 'composer', t.Milliseconds AS 'milliseconds', 
                       t.Bytes AS 'bytes', t.UnitPrice AS 'unit_price'
                FROM track t
                LEFT JOIN album al ON t.AlbumId = al.AlbumId
                LEFT JOIN artist ar ON al.ArtistId = ar.ArtistId
                LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
                LEFT JOIN genre g ON g.GenreId = t.GenreId
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
        $result['tracks'] = $albums;

        return $result;
    }

    public function findAllByArtist($artistId, $page)
    {
        $query = <<<SQL
            SELECT t.TrackId AS 'id', t.Name AS 'title', al.Title AS 'album', 
                       ar.Name AS 'artist', g.Name AS 'genre', m.Name AS 'media', 
                       t.Composer AS 'composer', t.Milliseconds AS 'milliseconds', 
                       t.Bytes AS 'bytes', t.UnitPrice AS 'unit_price'
                FROM track t
                LEFT JOIN album al ON t.AlbumId = al.AlbumId
                LEFT JOIN artist ar ON al.ArtistId = ar.ArtistId
                LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
                LEFT JOIN genre g ON g.GenreId = t.GenreId
                WHERE ar.ArtistId = :id
                LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $artistId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $albums = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['tracks'] = $albums;

        return $result;
    }

    public function findAllByAlbum($albumId, $page){
        $query = <<<SQL
            SELECT t.TrackId AS 'id', t.Name AS 'title', al.Title AS 'album', 
                       ar.Name AS 'artist', g.Name AS 'genre', m.Name AS 'media', 
                       t.Composer AS 'composer', t.Milliseconds AS 'milliseconds', 
                       t.Bytes AS 'bytes', t.UnitPrice AS 'unit_price'
                FROM track t
                LEFT JOIN album al ON t.AlbumId = al.AlbumId
                LEFT JOIN artist ar ON al.ArtistId = ar.ArtistId
                LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
                LEFT JOIN genre g ON g.GenreId = t.GenreId
                WHERE t.AlbumId = :id
                LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $albumId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $albums = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['tracks'] = $albums;

        return $result;
    }

    public function findAllBySearch($search, $page){
        $query = <<<SQL
            SELECT t.TrackId AS 'id', t.Name AS 'title', al.Title AS 'album', 
                       ar.Name AS 'artist', g.Name AS 'genre', m.Name AS 'media', 
                       t.Composer AS 'composer', t.Milliseconds AS 'milliseconds', 
                       t.Bytes AS 'bytes', t.UnitPrice AS 'unit_price'
                FROM track t
                LEFT JOIN album al ON t.AlbumId = al.AlbumId
                LEFT JOIN artist ar ON al.ArtistId = ar.ArtistId
                LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
                LEFT JOIN genre g ON g.GenreId = t.GenreId
                WHERE (t.Name LIKE :search) OR 
                      (al.Title LIKE :search1) OR 
                      (ar.Name LIKE :search2) OR 
                      (t.Composer LIKE :search3) OR 
                      (g.Name LIKE :search4) OR
                      (m.Name LIKE :search5)
                ORDER BY t.Name, al.Title, ar.Name, t.Composer, g.Name, m.Name
                LIMIT :offset, :count;
SQL;
        $offset = RepoUtil::getOffset($page);
        $search = '%'.$search.'%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':search', $search, PDO::PARAM_INT);
        $stmt->bindValue(':search1', $search, PDO::PARAM_INT);
        $stmt->bindValue(':search2', $search, PDO::PARAM_INT);
        $stmt->bindValue(':search3', $search, PDO::PARAM_INT);
        $stmt->bindValue(':search4', $search, PDO::PARAM_INT);
        $stmt->bindValue(':search5', $search, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $albums = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['tracks'] = $albums;

        return $result;
    }

    public function add($data)
    {
        // TODO: Implement add() method.
    }

    public function update($id, $data)
    {
        // TODO: Implement update() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }
}