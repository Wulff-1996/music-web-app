<?php

namespace Wulff\entities;

class Track
{
    public ?int $id;
    public string $name;
    public int $albumId;
    public int $mediaTypeId;
    public int $genreId;
    public ?string $composer;
    public int $milliseconds;
    public int $bytes;
    public float $unitPrice;

    private function __construct(?int $id, string $name, int $albumId, int $mediaTypeId,
                                 int $genreId, ?string $composer, int $milliseconds,
                                 int $bytes, float $unitPrice){

        $this->id = $id;
        $this->name = $name;
        $this->albumId = $albumId;
        $this->mediaTypeId = $mediaTypeId;
        $this->genreId = $genreId;
        $this->composer = $composer;
        $this->milliseconds = $milliseconds;
        $this->bytes = $bytes;
        $this->unitPrice = $unitPrice;
    }
}