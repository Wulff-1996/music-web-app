<?php

namespace Wulff\entities;

use Wulff\util\HttpCode;

class Response
{
    public int $statusCode;
    public $body; // TODO implement typecompo

    function __construct($statusCode, $body){
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    function send(){
        header('Content-Type: application/json');
        header('Accept-version: v1');
        http_response_code($this->statusCode);
        if ($this->body != null){
            echo json_encode($this->body);
        }
    }

    // success
    public static function success($body = null){
        return new Response(HttpCode::OK, $body);
    }

    public static function created($body = null){
        return new Response(HttpCode::CREATED, $body);
    }

    public static function okNoContent(){
        return new Response(HttpCode::NO_CONTENT, null);
    }

    // client errors
    public static function notFoundResponse($message = null)
    {
        $body = isset($message) ? ['message' => $message] : ['message' => 'Path not found'] ;
        return new Response(
            HttpCode::NOT_FOUND,
            $body);
    }

    public static function unauthorizedResponse($message = null)
    {
        $body = isset($message) ? ['message' => $message] : ['message' => 'Unauthorized'] ;
        return new Response(
            HttpCode::UNAUTHORIZED,
            $body);
    }

    public static function badRequest($body = null)
    {
        $body = isset($body) ? ['message' => $body] : null;
        return new Response(HttpCode::BAD_REQUEST, $body);
    }

    public static function conflictFkFails(){
        $message = ['message' => 'Fk constraint fails'];
        return new Response(HttpCode::CONFLICT, $message);
    }
}