<?php
namespace Src\entities;

require_once 'src/util/HttpCode.php';

use Src\util\HttpCode;

class Response
{
    public int $statusCode;
    public $body; // TODO implement type

    function __construct($statusCode, $body){
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    function send(){
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