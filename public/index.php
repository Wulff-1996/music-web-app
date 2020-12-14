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

require '../vendor/autoload.php';

// paths
const ARTISTS_PATH = 'artists';
const ALBUMS_PATH = 'albums';
const TRACKS_PATH = 'tracks';
const AUTH_PATH = 'auth';

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
const RESOURCE_INDEX = 4;

// get reqeust info
$url = $url = strtok($_SERVER['REQUEST_URI'], "?");
$urlPaths = explode('/', $url);
$request_method = $_SERVER['REQUEST_METHOD'];

// validate url
validatePath($urlPaths);

// map paths to controller and resource id
$resourceId = isset($urlPaths[RESOURCE_INDEX]) ? $urlPaths[RESOURCE_INDEX] : null;
$request = new Request($urlPaths[CONTROLLER_INDEX], $resourceId, $request_method);

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
        $useCase = $request->controller;
        $controller = new CustomerController($useCase, $request->method, $request->resourceId);
        $controller->processRequest();
        break;

    case ADMIN_LOGIN_PATH:
    case CUSTOMER_LOGIN_PATH:
    case CUSTOMER_SIGN_UP_PATH:
    case LOGOUT_PATH:
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

function authenticateUser(){
    SessionHandler::startSession();
    if (!SessionHandler::hasSession()){
        Response::unauthorizedResponse()->send();
        exit();
    }
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
            // $response = Response::notFoundResponse();
            $response->send();
            exit();
        }
    }
}