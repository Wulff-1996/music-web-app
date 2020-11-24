<?php


namespace Src\controllers;

require_once 'src/repositories/TrackRepo.php';
require_once 'src/config/Database.php';
require_once 'src/entities/Track.php';
require_once 'src/util/Validator.php';
require_once 'src/entities/Response.php';

use Src\Config\Database;
use Src\entities\Response;
use Src\repositories\TrackRepo;
use Src\util\Validator;
use Src\Entities\Track;

class TrackController
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

    private function getTrack($id){
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

    private function getTracks($page){
        $tracks = $this->trackRepo->FindAll($page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function getTracksForArtist($artistId, $page){
        $tracks = $this->trackRepo->findAllByArtist($artistId, $page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function getTracksForAlbum($albumId, $page){
        $tracks = $this->trackRepo->findAllByAlbum($albumId, $page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }

    private function getTracksBySearch($search, int $page)
    {
        $tracks = $this->trackRepo->findAllBySearch($search, $page);
        $this->trackRepo->closeConnection();
        return Response::success($tracks);
    }
}