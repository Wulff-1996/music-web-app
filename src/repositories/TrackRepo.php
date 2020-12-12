<?php

namespace Wulff\repositories;

use PDO;
use PDOException;
use Wulff\config\Database;
use Wulff\entities\EntityMapper;
use Wulff\entities\Track;
use Wulff\interfaces\RepoInterface;
use Wulff\util\RepoUtil;

class TrackRepo
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
              SELECT t.TrackId, t.Name AS TrackName, al.AlbumId, al.Title AS AlbumTitle, 
                   ar.ArtistId, ar.Name AS ArtistName, g.GenreId, g.Name AS GenreName, m.MediaTypeId ,m.Name AS MediaName, 
                   t.Composer, t.Milliseconds, 
                   t.Bytes, t.UnitPrice
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

        return EntityMapper::toJsonTrackSingle($track);
    }

    public function findAll($page)
    {
        $query = <<<SQL
            SELECT t.TrackId, t.Name AS TrackName, al.AlbumId, al.Title AS AlbumTitle, 
                   ar.ArtistId, ar.Name AS ArtistName, g.GenreId, g.Name AS GenreName, m.MediaTypeId ,m.Name AS MediaName, 
                   t.Composer, t.Milliseconds, 
                   t.Bytes, t.UnitPrice
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
        $tracks = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['tracks'] = $tracks;

        return EntityMapper::toJsonTrackMultiple($result);
    }

    public function findAllByArtist($artistId, $page)
    {
        $query = <<<SQL
            SELECT t.TrackId, t.Name AS TrackName, al.AlbumId, al.Title AS AlbumTitle, 
                   ar.ArtistId, ar.Name AS ArtistName, g.GenreId, g.Name AS GenreName, m.MediaTypeId ,m.Name AS MediaName, 
                   t.Composer, t.Milliseconds, 
                   t.Bytes, t.UnitPrice
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
        $tracks = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['tracks'] = $tracks;

        return EntityMapper::toJsonTrackMultiple($result);
    }

    public function findAllByAlbum($albumId, $page)
    {
        $query = <<<SQL
            SELECT t.TrackId, t.Name AS TrackName, al.AlbumId, al.Title AS AlbumTitle, 
                   ar.ArtistId, ar.Name AS ArtistName, g.GenreId, g.Name AS GenreName, m.MediaTypeId ,m.Name AS MediaName, 
                   t.Composer, t.Milliseconds, 
                   t.Bytes, t.UnitPrice
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

        return EntityMapper::toJsonTrackMultiple($result);
    }

    public function findAllBySearch($search, $page)
    {
        $query = <<<SQL
            SELECT t.TrackId, t.Name AS TrackName, al.AlbumId, al.Title AS AlbumTitle, 
                       ar.ArtistId, ar.Name AS ArtistName, g.GenreId, g.Name AS GenreName, m.MediaTypeId ,m.Name AS MediaName, 
                       t.Composer, t.Milliseconds, 
                       t.Bytes, t.UnitPrice
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
        $search = '%' . $search . '%';

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
        $tracks = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['tracks'] = $tracks;

        return EntityMapper::toJsonTrackMultiple($result);
    }

    public function findAllByCustomerId(int $customerId, int $page)
    {
        $query = <<<SQL
            SELECT t.TrackId, t.Name AS TrackName, al.AlbumId, al.Title AS AlbumTitle, 
                       ar.ArtistId, ar.Name AS ArtistName, g.GenreId, g.Name AS GenreName, m.MediaTypeId ,m.Name AS MediaName, 
                       t.Composer, t.Milliseconds, 
                       t.Bytes, t.UnitPrice
            FROM track t
            LEFT JOIN album al ON t.AlbumId = al.AlbumId
            LEFT JOIN artist ar ON al.ArtistId = ar.ArtistId
            LEFT JOIN mediatype m ON m.MediaTypeId = t.MediaTypeId
            LEFT JOIN genre g ON g.GenreId = t.GenreId
            LEFT JOIN invoiceline il on t.TrackId = il.TrackId
            LEFT JOIN invoice i on i.InvoiceId = il.InvoiceId
            WHERE i.CustomerId = :id
            LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page);

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':id', $customerId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', RepoUtil::COUNT, PDO::PARAM_INT);
        $stmt->execute();
        $tracks = $stmt->fetchAll();

        $result['page'] = $page;
        $result['tracks'] = $tracks;

        return EntityMapper::toJsonTrackMultiple($result);
    }

    public function add(Track $track): ?string
    {
        $query = <<<SQL
           INSERT INTO track (Name, AlbumId, MediaTypeId, GenreId, Composer, Milliseconds, Bytes, UnitPrice) 
           VALUES (:name, :albumId, :mediaTypeId, :genreId, :composer, :milliseconds, :bytes, :unitPrice);
SQL;

        $stmt = $this->db->conn->prepare($query);

        $stmt->bindValue(':name', $track->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':mediaTypeId', $track->getMediaTypeId(), PDO::PARAM_INT);
        $stmt->bindValue(':milliseconds', $track->getMilliseconds(), PDO::PARAM_INT);
        $stmt->bindValue(':unitPrice', $track->getUnitPrice(), PDO::PARAM_STR); // str used for floats/doubles
        $stmt->bindValue(':albumId', $track->getAlbumId(), PDO::PARAM_INT);
        $stmt->bindValue(':composer', $track->getComposer(), PDO::PARAM_STR);
        $stmt->bindValue(':bytes', $track->getBytes(), PDO::PARAM_INT);
        $stmt->bindValue(':genreId', $track->getGenreId(), PDO::PARAM_INT);

        $stmt->execute();
        $trackId = $this->db->conn->lastInsertId();

        // return created artist id
        return $trackId;
    }

    public function update(int $id, array $data)
    {
        return $this->db->update(
            'track',
            'TrackId',
            $id,
            $data
        );
    }

    public function delete($id)
    {
        $query = <<<SQL
            DELETE FROM track WHERE TrackId = :id;
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