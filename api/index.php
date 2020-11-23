<?php

header('Content-Type:application/json');
header('Accept-version:v1');

const ARTISTS_PATH = 'artists';
const ALBUMS_PATH = 'albums';
const TRACKS_PATH = 'tracks';

$url = $url = strtok($_SERVER['REQUEST_URI'], "?"); // /web-dev-final-mandatory/music_api/constroller/resource
$params = $_REQUEST; // everything after the ? like ?username=user1
$url_paths = explode('/', $url);


$controller = isset($url_paths[3]) ? $url_paths[3]: null;

if (!isset($controller)) {
    // path not known
    echo badRequest();
}

$request_method = $_SERVER['REQUEST_METHOD'];


// map to controller
switch ($controller) {
    case ARTISTS_PATH:
        require_once 'controllers/artist_controller.php';
        $artist_id = isset($url_paths[4]) ? $url_paths[4] : null;
        $controller = new ArtistsController($request_method, $params, $artist_id);
        $controller->processRequest();
        break;

    case ALBUMS_PATH:

        break;

    case TRACKS_PATH:
        break;

    default:
        // error no paths found
        break;
}


function badRequest()
{
    http_response_code(403);
    $response_body = ['message' => 'invalid request'];
    return json_encode($response_body);
}





