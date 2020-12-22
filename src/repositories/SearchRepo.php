<?php


namespace Wulff\repositories;

const SEARCH_OFFSET = 5;

use Wulff\config\Database;
use Wulff\entities\EntityMapper;
use Wulff\util\RepoUtil;
use PDO;

class SearchRepo
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

    public function searchGenres(string $search, int $page){
        $query = <<<SQL
            SELECT g.GenreId, g.Name
            FROM genre g
            WHERE g.Name LIKE :search
            ORDER BY g.Name
            LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page, 5);
        $search = '%' . $search . '%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':search', $search, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', 5, PDO::PARAM_INT);
        $stmt->execute();
        $genres = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['genres'] = $genres;

        return EntityMapper::toJsonGenres($result);
    }

    public function searchMedia(string $search, int $page){
        $query = <<<SQL
            SELECT m.MediaTypeId, m.Name
            FROM mediatype m
            WHERE m.Name LIKE :search
            ORDER BY m.Name
            LIMIT :offset, :count;
SQL;

        $offset = RepoUtil::getOffset($page, 5);
        $search = '%' . $search . '%';

        $stmt = $this->db->conn->prepare($query);
        $stmt->bindValue(':search', $search, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':count', 5, PDO::PARAM_INT);
        $stmt->execute();
        $media = $stmt->fetchAll();

        $result = array();
        $result['page'] = $page;
        $result['media'] = $media;

        return EntityMapper::toJsonMedia($result);
    }


}