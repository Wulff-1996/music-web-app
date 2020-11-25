<?php

namespace Wulff\entities;

class Request
{
    public string $controller;
    public ?string $resourceId;
    public string $method;

    public function __construct(string $controller, ?string $resourceId, string $method)
    {
        $this->controller = $controller;
        $this->resourceId = $resourceId;
        $this->method = $method;
    }

}