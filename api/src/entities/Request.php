<?php

namespace Src\entities;


class Request
{
    public $controller;
    public $resourceId;
    public $method;

    public function __construct($controller, $resourceId, $method)
    {
        $this->controller = $controller;
        $this->resourceId = $resourceId;
        $this->method = $method;
    }

}