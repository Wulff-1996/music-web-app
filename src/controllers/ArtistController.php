<?php

namespace Wulff\controllers;

use Wulff\config\Database;
use Wulff\entities\Artist;
use Wulff\entities\EntityMapper;
use Wulff\entities\Response;
use Wulff\repositories\ArtistRepo;
use Wulff\util\HttpCode;
use Wulff\util\RepoUtil;
use Wulff\util\Validator;

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
                    $name = isset($_GET['name']) ? (string)$_GET['name'] : null;
                    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
                    $count = filter_input(INPUT_GET, 'count', FILTER_VALIDATE_INT);

                    if (!$page) $page = 0;
                    if (!$count) $count = RepoUtil::COUNT;

                    if ($name) {
                        // search artists by name
                        $response = $this->getArtistsByName($name, $page, $count);
                    } else {
                        // get all artists
                        $response = $this->getArtists($page, $count);
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

        // close connection
        $this->artistRepo->closeConnection();
    }

    private function getArtist($id)
    {
        $artist = $this->artistRepo->find($id);
        if (!$artist) {
            return Response::notFoundResponse(['no artist with given id']);
        }

        $artist = EntityMapper::toJsonArtist($artist);
        return Response::success($artist);
    }

    private function getArtists($page, $count)
    {
        $artists = $this->artistRepo->findAll($page, $count);
        $artists = EntityMapper::toJsonArtistMultiple($artists);
        return Response::success($artists);
    }

    private function getArtistsByName(string $name, int $page, int $count)
    {
        $artists = $this->artistRepo->findAllByName($name, $page, $count);
        $artists = EntityMapper::toJsonArtistMultiple($artists);
        return Response::success($artists);
    }

    private function createArtist($data)
    {
        // validate request
        $rules = [
            'name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 120]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid
        $artist = Artist::make($data['name']);

        // insert new artist
        $artistId = $this->artistRepo->createArtist($artist);

        // find inserted artist
        $artist = $this->artistRepo->find($artistId);
        $artist = EntityMapper::toJsonArtist((array) $artist);

        return Response::success((array)$artist);
    }

    private function updateArtist($artistId, $data)
    {
        // validate request
        $rules = [
            'name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 120]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        $artist = Artist::makeWithId($artistId, $data['name']);

        // update artist
        if (!$this->artistRepo->updateArtist($artist)) {
            // fails
            return Response::conflictFkFails();
        }

        // success return artist
        $artist = $this->artistRepo->find($artistId);
        $artist = EntityMapper::toJsonArtist((array) $artist);

        return Response::success((array)$artist);
    }

    private function deleteArtist($id)
    {
        if ($this->artistRepo->delete($id)) {
            // delete success
            return Response::okNoContent();
        } else {
            // error, integrity violation
            return Response::conflictFkFails();
        }
    }

    private function validatePathId()
    {
        if (!$this->id) {
            Response::notFoundResponse()->send();
            exit();
        }
    }
}