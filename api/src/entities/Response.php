<?php
namespace Src\entities;

require_once 'src/util/HttpCode.php';

use Src\util\HttpCode;

class Response
{
    public $statusCode;
    public $body;

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

    public static function notFoundResponse($message = null)
    {
        $body = isset($message) ? ['message' => $message] : null ;
        return new Response(
            HttpCode::NOT_FOUND,
            $body);
    }

    public static function badRequest($message = null)
    {
        $body = isset($body) ? ['message' => $body] : null;
        return new Response(HttpCode::BAD_REQUEST, $body);
    }
}