<?php
namespace Src\Controllers;

require_once 'src/repositories/ArtistRepo.php';
require_once 'src/config/Database.php';
require_once 'src/entities/Artist.php';
require_once 'src/util/Validator.php';
require_once 'src/entities/Response.php';

use Src\Config\Database;
use Src\Entities\Artist;
use Src\entities\Response;
use Src\Repositories\ArtistRepo;
use Src\util\HttpCode;
use Src\util\Validator;

class ArtistController
{
    private $db;
    private $requestMethod;
    private $artistRepo;
    private $artistId;

    public function __construct($requestMethod, $artistId)
    {
        $this->db = new Database();
        $this->requestMethod = $requestMethod;
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
                    $name = isset($_GET['name']) ? $_GET['name'] : null;
                    $page = is_numeric($_GET['page']) ? (int) $_GET['page'] : 0;

                    if (isset($name)) {
                        // search artists by name
                        $response = $this->getArtistsByName($name, $page);
                    } else {
                        // get all artists
                        $response = $this->getArtists($page);
                    }
                }
                break;

            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->createArtist($data);
                break;

            default:
                $response = Response::notFoundResponse();
                break;
        }

        // send response
        $response->send();
    }

    private function getArtist($id)
    {
        $result = $this->artistRepo->find($id);
        if (!$result) {
            return Response::notFoundResponse();
        }
        return new Response(HttpCode::OK, $result);
    }

    private function getArtists($page)
    {
        $result = $this->artistRepo->findAll($page);
        return new Response(HttpCode::OK, $result);
    }

    private function getArtistsByName($name, $page)
    {
        $result = $this->artistRepo->findAllByName($name, $page);
        return new Response(HttpCode::OK, $result);
    }

    private function createArtist($data){
        // validate request
        $rules = [
            'name' => [Validator::REQUIRED, Validator::TEXT]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()){
            // request is invalid
            return new Response(
                HttpCode::BAD_REQUEST,
                ['message' => $validator->error()]);
        }

        // request valid
        $artist = new Artist($validator->data());

        // insert new artist
        $artistId = $this->artistRepo->createArtist($artist);

        // get inserted artist
        $artist = $this->artistRepo->find($artistId);
        return new Response(HttpCode::CREATED, $artist);
    }
}