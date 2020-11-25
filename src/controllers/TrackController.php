<?php

namespace Wulff\controllers;

use Wulff\config\Database;
use Wulff\entities\Response;
use Wulff\repositories\TrackRepo;
use Wulff\util\SessionHandler;
use Wulff\util\SessionObject;

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

        SessionHandler::startSession();
        SessionHandler::setSession(new SessionObject(3454));

        /*
        if (!$session) {
            $response = Response::unauthorizedResponse();
            $response->send();
            return;
        }
        */


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

                    if (isset($artistId)) {
                        // get all tracks for artist
                        $response = $this->getTracksForArtist($artistId, $page);

                    } else if (isset($albumId)) {
                        // get tracks for album
                        $response = $this->getTracksForAlbum($albumId, $page);

                    } else if (isset($search)) {
                        // search by albums by search eg track name, artis name, album name
                        $response = $this->getTracksBySearch($search, $page);

                    } else {
                        // get all tracks
                        $response = $this->getTracks($page);
                    }
                }
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
}