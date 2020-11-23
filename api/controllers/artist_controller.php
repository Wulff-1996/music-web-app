<?php
require_once 'config/database.php';
require_once 'repositories/artist_repo.php';
require_once 'util/response_util.php';

class ArtistsController
{
    private $db;
    private $requestMethod;
    private $params;
    private $artistRepo;
    private $artistId;

    public function __construct($requestMethod, $params, $artistId)
    {
        $this->db = new Database();
        $this->requestMethod = $requestMethod;
        $this->params = $params;
        $this->artistId = $artistId;
        $this->artistRepo = new ArtistRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':

                if (isset($this->artistId)) {
                    $response = $this->getArtist($this->artistId);
                } else {
                    $name = isset($this->params['name']) ? $this->params['name'] : null;
                    $page = isset($this->params['page']) ? $this->params['page'] : 0;

                    if (isset($name)) {
                        // search artists by name
                        $response = $this->getArtistsByName($name, $page);
                    } else {
                        // get all artists
                        $response = $this->getArtists($page);
                    }
                }
                break;

            default:
                $response = ResponseUtil::notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getArtist($id)
    {
        $result = $this->artistRepo->find($id);
        if (!$result) {
            return ResponseUtil::notFoundResponse();
        }
        $response['status_code_header'] = '200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getArtists($page)
    {
        $result = $this->artistRepo->findAll($page);
        $response['status_code_header'] = '200 OK';
        $response['body'] = json_encode($result);
        return $response;

    }

    private function getArtistsByName($name, $page)
    {
        $result = $this->artistRepo->findAllByName($name, $page);
        $response['status_code_header'] = '200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


}

?>