<?php

namespace Wulff\entities;

class Request
{
    public string $controller;
    public ?int $resourceId;
    public string $method;

    public function __construct(string $controller, ?int $resourceId, string $method)
    {
        $this->controller = $controller;
        $this->resourceId = $resourceId;
        $this->method = $method;
    }

}