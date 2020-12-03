<?php

namespace Wulff\controllers;


use Wulff\config\Database;
use Wulff\entities\Album;
use Wulff\entities\Auth;
use Wulff\entities\Response;
use Wulff\repositories\AlbumRepo;
use Wulff\util\SessionObject;
use Wulff\util\Validator;

class AlbumController
{
    private Database $db;
    private string $method;
    private AlbumRepo $albumRepo;
    private ?string $id;

    public function __construct($method, $id, $db = null)
    {
        $this->db = $db ?? new Database();
        $this->method = $method;
        $this->id = $id;
        $this->albumRepo = new AlbumRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->method) {
            case 'GET':

                $user = Auth::current();

                exit(print_r($user));

                if (isset($this->id)) {
                    // get one album
                    $response = $this->getAlbum($this->id);

                } else {
                    // get all albums

                    $artistId = isset($_GET['artist_id']) && is_numeric($_GET['artist_id']) ? (int)$_GET['artist_id'] : null;
                    $title = isset($_GET['title']) ? $_GET['title'] : null;
                    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 0;

                    if (isset($artistId)) {
                        // get all albums for artist
                        $response = $this->getAlbumsForArtist($artistId, $page);
                    } else if (isset($title)) {
                        // get all albums match title
                        $response = $this->getAlbumsByTitle($title, $page);
                    } else {
                        // get all albums
                        $response = $this->getAlbums($page);
                    }
                }
                break;

            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->createAlbum($data);
                break;

            case 'PUT':
                $this->validatePathId();
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->updateAlbum($this->id, $data);
                break;

            case 'DELETE':
                $this->validatePathId();
                $response = $this->deleteAlbum($this->id);
                break;

            default:
                $response = Response::notFoundResponse();
                break;
        }

        // send response
        $response->send();
    }

    private function getAlbum($id)
    {
        $album = $this->albumRepo->find($id);
        // check if album exists
        if (!$album) {
            // not found
            return Response::notFoundResponse();
        }
        $this->albumRepo->closeConnection();

        // album exists
        return Response::success($album);
    }

    private function getAlbums($page)
    {
        $albums = $this->albumRepo->findAll($page);
        $this->albumRepo->closeConnection();
        return Response::success($albums);
    }

    private function getAlbumsForArtist($artistId, $page)
    {
        $albums = $this->albumRepo->findAllByArtistId($artistId, $page);
        $this->albumRepo->closeConnection();
        return Response::success($albums);
    }

    private function getAlbumsByTitle($title, $page)
    {
        $albums = $this->albumRepo->findAllByTitle($title, $page);
        $this->albumRepo->closeConnection();
        return Response::success($albums);
    }

    private function createAlbum($data)
    {
        // validate post body
        $rules = [
            'title' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 160],
            'artist_id' => [Validator::REQUIRED, Validator::NUMERIC, Validator::MIN_VALUE => 0]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid, create album from data
        $album = Album::make($data['title'], $data['artist_id']);

        // insert album
        $albumId = $this->albumRepo->add($album);

        // get inserted album
        $album = $this->albumRepo->find($albumId);

        // close connection
        $this->albumRepo->closeConnection();

        return Response::created($album);
    }

    private function updateAlbum($id, $data){
        // validate request
        $rules = [
            'title' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 160],
            'artist_id' => [Validator::REQUIRED, Validator::NUMERIC, Validator::MIN_VALUE => 0]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()){
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid
        $album = Album::makeWithId($id, $data['title'], $data['artist_id']);

        $isSuccess = $this->albumRepo->update($album);

        // check if was success
        if (!$isSuccess){
            // fails
            return Response::conflictFkFails();
        }

        // success return album
        return Response::success($album);
    }

    private function deleteAlbum($id){
        if ($this->albumRepo->delete($id)){
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