<?php

use Wulff\controllers\AlbumController;
use Wulff\controllers\ArtistController;
use Wulff\controllers\TrackController;
use Wulff\entities\Request;
use Wulff\entities\Response;
use Wulff\util\Validator;

require '../vendor/autoload.php';

const ARTISTS_PATH = 'artists';
const ALBUMS_PATH = 'albums';
const TRACKS_PATH = 'tracks';

$url = $url = strtok($_SERVER['REQUEST_URI'], "?"); // /web-dev-final-mandatory/music_api/constroller/resource
$urlPaths = explode('/', $url);
$request_method = $_SERVER['REQUEST_METHOD'];

validatePath($urlPaths);

$resourceId = isset($urlPaths[2]) ? $urlPaths[2] : null;
$request = new Request($urlPaths[1], $resourceId, $request_method);

// map to controller
switch ($request->controller) {
    case ARTISTS_PATH:
        $controller = new ArtistController($request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case ALBUMS_PATH:
        $controller = new AlbumController($request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case TRACKS_PATH:
        $controller = new TrackController($request->method, $request->resourceId);
        $controller->processRequest();
        break;

    default:
        // error no paths found
        $response = Response::notFoundResponse();
        $response->send();
        break;
}

function validatePath($urlPaths){
    // check if path is correct length
    if (isset($urlPaths[3])) {
        // path not known
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // check if controller is present
    if (!isset($urlPaths[1])){
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // validate resource id if present
    if (isset($urlPaths[2])){
        $id = $urlPaths[2];
        // validate album id
        $data = ['id' => $id];
        $rules = ['id' => [Validator::REQUIRED, Validator::NUMERIC, Validator::MIN_VALUE => 0]];
        $validator = new Validator();
        $validator->validate($data, $rules);

        // check if path id is valid
        if ($validator->error()) {
            $response = Response::notFoundResponse();
            $response->send();
            exit();
        }
    }
}





