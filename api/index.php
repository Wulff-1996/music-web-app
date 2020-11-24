<?php

use Src\controllers\AlbumController;
use Src\Controllers\ArtistController;
use Src\entities\Request;
use Src\entities\Response;
use Src\util\Validator;

require_once 'src/config/Database.php';
require_once 'src/controllers/ArtistController.php';
require_once 'src/controllers/AlbumController.php';
require_once 'src/entities/Request.php';



header('Content-Type:application/json');
header('Accept-version:v1');

const ARTISTS_PATH = 'artists';
const ALBUMS_PATH = 'albums';
const TRACKS_PATH = 'tracks';

$url = $url = strtok($_SERVER['REQUEST_URI'], "?"); // /web-dev-final-mandatory/music_api/constroller/resource
$urlPaths = explode('/', $url);
$request_method = $_SERVER['REQUEST_METHOD'];

validatePath($urlPaths);

$resourceId = isset($urlPaths[4]) ? $urlPaths[4] : null;
$request = new Request($urlPaths[3], $resourceId, $request_method);

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
        break;

    default:
        // error no paths found
        $response = Response::notFoundResponse();
        $response->send();
        break;
}

function validatePath($urlPaths){
    // check if path is correct length
    if (isset($urlPaths[5])) {
        // path not known
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // check if controller is present
    if (!isset($urlPaths[3])){
        echo 'path 3::';
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // validate resource id if present
    if (isset($urlPaths[4])){
        $id = $urlPaths[4];
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





