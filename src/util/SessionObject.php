<?php

namespace Wulff\util;

use JsonSerializable;
use Serializable;

class SessionObject implements JsonSerializable, Serializable
{

    private ?int $id;
    private bool $isAdmin;

    public function __construct(?int $id, bool $isAdmin)
    {
        $this->id = $id;
        $this->isAdmin = $isAdmin;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     */
    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function serialize()
    {
        return json_encode($this);
    }

    public function unserialize($serialized)
    {
        $json = json_decode($serialized);
        $this->id = $json->id;
        $this->isAdmin = $json->isAdmin;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'isAdmin' => $this->isAdmin
            ];
    }
}