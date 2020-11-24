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
    private Database $db;
    private string $method;
    private ArtistRepo $artistRepo;
    private ?string $id;

    public function __construct($method, $id)
    {
        $this->db = new Database();
        $this->method = $method;
        $this->id = $id;
        $this->artistRepo = new ArtistRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->method) {
            case 'GET':

                if (isset($this->id)) {
                    $response = $this->getArtist($this->id);
                } else {
                    $name = isset($_GET['name']) ? $_GET['name'] : null;
                    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 0;

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

            case 'PUT':
                $this->validatePathId();
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->updateArtist($this->id, $data);
                break;

            case 'DELETE':
                $this->validatePathId();
                $response = $this->deleteArtist($this->id);
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
        $artist = $this->artistRepo->find($id);
        if (!$artist) {
            return Response::notFoundResponse();
        }
        return Response::success($artist);
    }

    private function getArtists($page)
    {
        $artists = $this->artistRepo->findAll($page);
        return Response::success($artists);
    }

    private function getArtistsByName($name, $page)
    {
        $artists = $this->artistRepo->findAllByName($name, $page);
        return Response::success($artists);
    }

    private function createArtist($data){
        // validate request
        $rules = [
            'name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 120]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()){
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid
        $artist = Artist::make($data['name']);

        // insert new artist
        $artist->id = $this->artistRepo->createArtist($artist);

        return new Response(HttpCode::CREATED, $artist);
    }

    private function updateArtist($artistId, $data){
        // validate request
        $rules = [
            'name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 120]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()){
            // request is invalid
            return Response::badRequest($validator->error());
        }

        $artist = Artist::makeWithId($artistId, $data['name']);

        // update artist
        if (!$this->artistRepo->updateArtist($artist)){
            // fails
            return Response::conflictFkFails();
        }

        // success return artist
        return Response::success($artist);
    }

    private function deleteArtist($id){
        if ($this->artistRepo->delete($id)){
            // delete success
            return Response::okNoContent();
        } else {
            // error, integrity violation
            return Response::conflictFkFails();
        }
    }

    private function validatePathId(){
        if (!$this->id){
            Response::notFoundResponse()->send();
            exit();
        }
    }
}