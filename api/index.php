<?php

use Src\Controllers\ArtistController;
use Src\entities\Response;

require_once 'src/config/Database.php';
require_once 'src/controllers/ArtistController.php';

header('Content-Type:application/json');
header('Accept-version:v1');

const ARTISTS_PATH = 'artists';
const ALBUMS_PATH = 'albums';
const TRACKS_PATH = 'tracks';

$url = $url = strtok($_SERVER['REQUEST_URI'], "?"); // /web-dev-final-mandatory/music_api/constroller/resource
$url_paths = explode('/', $url);

$controller = isset($url_paths[3]) ? $url_paths[3]: null;

// check if there is a controller in the path, like api/artists
if (!isset($controller)) {
    // path not known
    $response = Response::notFoundResponse('Path not found');
    $response->send();
    return;
}

// check if the path is too long
// like api/artists/{id}/somethingNotPermitted
if (isset($url_paths[5])){
    // path not known
    $response = Response::notFoundResponse('Path not found');
    $response->send();
    return;
}

$request_method = $_SERVER['REQUEST_METHOD'];
$resourceId = isset($url_paths[4]) ? $url_paths[4] : null;

// map to controller
switch ($controller) {
    case ARTISTS_PATH:
        $controller = new ArtistController($request_method, $resourceId);
        $controller->processRequest();
        break;

    case ALBUMS_PATH:

        break;

    case TRACKS_PATH:
        break;

    default:
        // error no paths found
        $response = Response::notFoundResponse('Path not found');
        $response->send();
        break;
}





