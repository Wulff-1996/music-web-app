<?php

namespace Wulff\controllers;

use PDOException;
use Wulff\config\Database;
use Wulff\entities\Response;
use Wulff\entities\Track;
use Wulff\repositories\TrackRepo;
use Wulff\util\SessionHandler;
use Wulff\util\Validator;

// TODO only close the DB connection when response is send and not after each DB operation
//TODO new url = http://music-web-app/tracks
// mark each property with type, for classes make them private and make getters and setters, for methods mark return type

class TrackController // TODO create abstract class for similar properties for all controllers
{
    private Database $db;
    private string $method;
    private TrackRepo $trackRepo;
    private ?string $id;

    public function __construct($method, $id)
    {
        $this->db = new Database();
        $this->method = $method;
        $this->id = $id;
        $this->trackRepo = new TrackRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->method) {
            case 'GET':
                if (isset($this->id)) {
                    // get one track
                    $response = $this->getTrack($this->id);
                } else {
                    // get all
                    $search = isset($_GET['search']) ? $_GET['search'] : null;
                    $artistId = isset($_GET['artist_id']) && is_numeric($_GET['artist_id']) ? (int)$_GET['artist_id'] : null;
                    $albumId = isset($_GET['album_id']) && is_numeric($_GET['album_id']) ? (int)$_GET['album_id'] : null;
                    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 0;

                    if (isset($search)) {
                        // search by albums by search eg track name, artis name, album name
                        $response = $this->getTracksBySearch($search, $page);
                    } else if (isset($artistId)) {
                        // get all tracks for artist
                        $response = $this->getTracksForArtist($artistId, $page);
                    } else if (isset($albumId)) {
                        // get tracks for album
                        $response = $this->getTracksForAlbum($albumId, $page);
                    } else {
                        // get all tracks
                        $response = $this->getTracks($page);
                    }
                }
                break;

            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->createTrack($data);
                break;

            case 'PATCH':
                $this->validatePathId();
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->update($this->id, $data);
                break;

            case 'DELETE':
                $this->validatePathId();
                $this->hasAccess(true);
                $response = $this->deleteTrack($this->id);
                break;

            default:
                $response = Response::notFoundResponse();
                break;
        }

        // send response
        $response->send();
    }

    private function getTrack($id): Response
    {
        $track = $this->trackRepo->find($id);
        // check if album exists
        if (!$track) {
            // not found
            return Response::notFoundResponse();
        }
        $this->trackRepo->closeConnection();

        // album exists
        return Response::success($track);
    }

    private function getTracks($page): Response
    {
        $tracks = $this->trackRepo->FindAll($page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function getTracksForArtist($artistId, $page): Response
    {
        $tracks = $this->trackRepo->findAllByArtist($artistId, $page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function getTracksForAlbum($albumId, $page): Response
    {
        $tracks = $this->trackRepo->findAllByAlbum($albumId, $page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function getTracksBySearch($search, int $page): Response
    {
        $tracks = $this->trackRepo->findAllBySearch($search, $page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function createTrack($data): Response
    {
        // validate request
        $rules = [
            'name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 200],
            'album_id' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'media_type_id' => [Validator::REQUIRED, Validator::INTEGER, Validator::MIN_VALUE => 0],
            'genre_id' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'composer' => [Validator::TEXT, Validator::MAX_LENGTH => 220],
            'milliseconds' => [Validator::REQUIRED, Validator::INTEGER, Validator::MIN_VALUE => 0],
            'bytes' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'unit_price' => [Validator::REQUIRED, Validator::NUMERIC, Validator::MIN_VALUE => 0]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid
        $track = new Track(
            null,
            $data['name'],
            isset($data['album_id']) ? $data['album_id'] : null,
            $data['media_type_id'],
            isset($data['genre_id']) ? $data['genre_id'] : null,
            isset($data['composer']) ? $data['composer'] : null,
            $data['milliseconds'],
            isset($data['bytes']) ? $data['bytes'] : null,
            $data['unit_price']
        );

        try {
            // add track
            $trackId = $this->trackRepo->add($track);

        } catch (PDOException $e) {
            // integrity error
            return Response::conflictFkFails();
        }

        // get inserted track
        $track = $this->trackRepo->find($trackId);

        return Response::success($track);
    }

    private function update($trackId, $data): Response
    {
        // validate request
        $rules = [
            'name' => [Validator::TEXT, Validator::MAX_LENGTH => 200],
            'album_id' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'media_type_id' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'genre_id' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'composer' => [Validator::TEXT, Validator::MAX_LENGTH => 220],
            'milliseconds' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'bytes' => [Validator::INTEGER, Validator::MIN_VALUE => 0],
            'unit_price' => [Validator::NUMERIC, Validator::MIN_VALUE => 0]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid update track
        $isSuccess = $this->trackRepo->update(
            'track',
            'TrackId',
            $trackId,
            Track::toDbEntity($validator->data())
        );

        if (!$isSuccess) {
            // failed due to FK constraint
            return Response::conflictFkFails();
        } else {
            // update success, send updated resource back
            $trackUpdated = $this->trackRepo->find($trackId);
            return Response::success($trackUpdated);
        }
    }

    private function deleteTrack(int $id): Response
    {
        if ($this->trackRepo->delete($id)) {
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

    private function hasAccess(string $isAdminRequired)
    {
        // TODO add privileges to all endpoints, maybe some general in the index.php file for checking if logged in
        SessionHandler::startSession();
        $session = SessionHandler::getSession();

        if (!$session) {
            // not logged in
            Response::unauthorizedResponse(['message' => 'Access denied, not logged in'])->send();
            exit();
        }

        if ($session->isAdmin() === false && $isAdminRequired) {
            // admin required
            Response::unauthorizedResponse(['message' => 'Access denied, needs admin privileges'])->send();
            exit();
        }
    }
}