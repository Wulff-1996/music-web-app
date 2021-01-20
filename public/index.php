<?php

use Wulff\controllers\CustomerController;
use Wulff\controllers\AlbumController;
use Wulff\controllers\ArtistController;
use Wulff\controllers\AuthController;
use Wulff\controllers\TrackController;
use Wulff\entities\Auth;
use Wulff\entities\Request;
use Wulff\entities\Response;
use Wulff\util\Validator;
use Wulff\util\SessionHandler;
use Wulff\controllers\SearchController;

require '../vendor/autoload.php';

// paths
const ARTISTS_PATH = 'artists';
const ALBUMS_PATH = 'albums';
const TRACKS_PATH = 'tracks';
const AUTH_PATH = 'auth';

// search paths
const SEARCH_GENRES_PATH = 'search-genres';
const SEARCH_MEDIA_PATH = 'search-media';

// customer paths
const CUSTOMERS_PATH = 'customers';
const CUSTOMER_INVOICES_PATH = 'customer-invoices';

// auth paths
const ADMIN_LOGIN_PATH = 'admin-login';
const CUSTOMER_LOGIN_PATH = 'customer-login';
const CUSTOMER_SIGN_UP_PATH = 'customer-signup';
const LOGOUT_PATH = 'logout';

// path indexes
const CONTROLLER_INDEX = 3; // when changing url, easier to just change index here
const RESOURCE_INDEX = 4; // TODO remove these two

const CONTROLLER_INDEX_OFFSET = 2;
const RESOURCE_INDEX_OFFSET = 3;

$controller = null;
$resourceId = null;

// get reqeust info
$url = $url = strtok($_SERVER['REQUEST_URI'], "?");
$urlPaths = explode('/', $url);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$request = validatePath2($urlPaths, $controller, $resourceId, $requestMethod);

// map to controller
switch ($request->controller) {
    case ARTISTS_PATH:
        authenticateUser();
        $controller = new ArtistController($request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case ALBUMS_PATH:
        authenticateUser();
        $controller = new AlbumController($request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case TRACKS_PATH:
        authenticateUser();
        $controller = new TrackController($request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case CUSTOMERS_PATH:
    case CUSTOMER_INVOICES_PATH:
        authenticateUser();
        $useCase = $request->controller;
        $controller = new CustomerController($useCase, $request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case SEARCH_GENRES_PATH:
    case SEARCH_MEDIA_PATH:
        authenticateUser();
        $useCase = $request->controller;
        $controller = new SearchController($useCase, $request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case ADMIN_LOGIN_PATH:
    case CUSTOMER_LOGIN_PATH:
    case CUSTOMER_SIGN_UP_PATH:
    case LOGOUT_PATH:
        // public endpoints
        $useCase = $request->controller;
        $controller = new AuthController($useCase, $request->method, $request->resourceId);
        $controller->processRequest();
        break;

    default:
        // error no paths found
        $response = Response::notFoundResponse();
        $response->send();
        break;
}

function authenticateUser()
{
    SessionHandler::startSession();
    if (!SessionHandler::hasSession()) {
        Response::unauthorizedResponse()->send();
        exit();
    }
}

function validatePath2($urlPaths, $controller, $resourceId, $requestMethod){
    $controllerIndex = null;
    $resourceIdIndex = null;

    // find indexes from path
    foreach($urlPaths as $key => $value) {
        if ($value === 'music-web-app-api'){
            $controllerIndex = $key + CONTROLLER_INDEX_OFFSET;
            $resourceIdIndex = $key + RESOURCE_INDEX_OFFSET;
            break;
        }
    }

    // parse values to paths
    $controller = (isset($urlPaths[$controllerIndex])) ? $urlPaths[$controllerIndex] : null;
    $resourceId = (isset($urlPaths[$resourceIdIndex])) ? $urlPaths[$resourceIdIndex] : null;

    // check if path is correct length
    if (isset($urlPaths[$resourceIdIndex + 1])) {
        // path not known, too long
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // check if controller is present
    if (!$controller) {
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // validate resource id if present
    if (isset($resourceId) && !empty($resourceId)) {
        // parse to int
        if (filter_var($resourceId, FILTER_VALIDATE_INT)){
            $resourceId = (int) $resourceId;
        } else {
            // not valid int
            $response = Response::badRequest(['message' => 'id must be a valid int above 0']);
            $response->send();
            exit();
        }
    } else {
        // if empty make it null to support ending / in url like: api/customers/
        $resourceId = null;
    }

    return new Request($controller, $resourceId, $requestMethod);
}

function validatePath($urlPaths)
{
    // check if path is correct length
    if (isset($urlPaths[RESOURCE_INDEX + 1])) {
        // path not known, too long
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // check if controller is present
    if (!isset($urlPaths[CONTROLLER_INDEX])) {
        $response = Response::notFoundResponse();
        $response->send();
        exit();
    }

    // validate resource id if present
    if (isset($urlPaths[RESOURCE_INDEX])) {
        $id = $urlPaths[RESOURCE_INDEX];
        // validate album id
        $data = ['id' => $id];
        $rules = ['id' => [Validator::REQUIRED, Validator::NUMERIC, Validator::MIN_VALUE => 0]];
        $validator = new Validator();
        $validator->validate($data, $rules);

        // check if path id is valid
        if ($validator->error()) {
            $response = Response::badRequest($validator->error());
            $response->send();
            exit();
        }
    }
}

function validateResourceId($resourceId)
{
    if (!isset($resourceId)){
        return null;
    }

    if (!is_numeric($resourceId)){
        // id invalid
        Response::notFoundResponse(['path not found, id was not an integer'])->send();
        exit();
    }


    return (int) $resourceId;
}