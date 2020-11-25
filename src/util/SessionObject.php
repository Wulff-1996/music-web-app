<?php

namespace Wulff\util;

use JsonSerializable;
use Serializable;

class SessionObject implements JsonSerializable, Serializable
{

    private int $id;

    public function __construct(int $id)
    {

        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function serialize()
    {
        return json_encode($this);
    }

    public function unserialize($serialized)
    {

        $json = json_decode($serialized);

        $this->id = $json->id;

    }

    public function jsonSerialize()
    {
        return ['id' => $this->id];
    }
}