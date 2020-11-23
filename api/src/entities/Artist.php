<?php
namespace Src\Entities;

class Artist {

    public $name;

    function __construct($data)
    {
        $this->name = $data['name'];
    }
}