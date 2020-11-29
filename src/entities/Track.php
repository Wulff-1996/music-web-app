<?php

namespace Wulff\entities;

use JsonSerializable;

class Track implements JsonSerializable
{
    private ?int $id;
    private string $name;
    private ?int $albumId;
    private int $mediaTypeId;
    private ?int $genreId;
    private ?string $composer;
    private int $milliseconds;
    private ?int $bytes;
    private float $unitPrice;

    public function __construct(?int $id, string $name, ?int $albumId, int $mediaTypeId,
                                 ?int $genreId, ?string $composer, int $milliseconds,
                                 ?int $bytes, float $unitPrice){
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

    public static function makeFromJson(array $data) : Track{
        return new Track(
            isset($data['id']) ? $data['id'] : null,
            $data['name'],
            isset($data['album_id']) ? $data['album_id'] : null,
            $data['media_type_id'],
            isset($data['genre_id']) ? $data['genre_id'] : null,
            isset($data['composer']) ? $data['composer'] : null,
            $data['milliseconds'],
            isset($data['bytes']) ? $data['bytes'] : null,
            $data['unit_price']
        );
    }

    public static function toDbEntity(array $data): array {
        $dbEntity = array();

        isset($data['id']) ? $dbEntity['TrackId'] = $data['id'] : null;
        isset($data['name']) ? $dbEntity['Name'] = $data['name'] : null;
        isset($data['album_id']) ? $dbEntity['AlbumId'] = $data['album_id'] : null;
        isset($data['media_type_id']) ? $dbEntity['MediaTypeId'] = $data['media_type_id'] : null;
        isset($data['genre_id']) ? $dbEntity['GenreId'] = $data['genre_id'] : null;
        isset($data['composer']) ? $dbEntity['Composer'] = $data['composer'] : null;
        isset($data['milliseconds']) ? $dbEntity['Milliseconds'] = $data['milliseconds'] : null;
        isset($data['bytes']) ? $dbEntity['Bytes'] = $data['bytes'] : null;
        isset($data['unit_price']) ? $dbEntity['UnitPrice'] = $data['unit_price'] : null;

        return $dbEntity;
    }

    public function jsonSerialize() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'album_id' => $this->getAlbumId(),
            'media_type_id' => $this->getMediaTypeId(),
            'genre_id' => $this->getGenreId(),
            'composer' => $this->getComposer(),
            'milliseconds' => $this->getMilliseconds(),
            'bytes' => $this->getBytes(),
            'unit_price' => $this->getUnitPrice()
        ];
    }



    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getAlbumId(): ?int
    {
        return $this->albumId;
    }

    /**
     * @param int|null $albumId
     */
    public function setAlbumId(?int $albumId): void
    {
        $this->albumId = $albumId;
    }

    /**
     * @return int
     */
    public function getMediaTypeId(): int
    {
        return $this->mediaTypeId;
    }

    /**
     * @param int $mediaTypeId
     */
    public function setMediaTypeId(int $mediaTypeId): void
    {
        $this->mediaTypeId = $mediaTypeId;
    }

    /**
     * @return int|null
     */
    public function getGenreId(): ?int
    {
        return $this->genreId;
    }

    /**
     * @param int|null $genreId
     */
    public function setGenreId(?int $genreId): void
    {
        $this->genreId = $genreId;
    }

    /**
     * @return string|null
     */
    public function getComposer(): ?string
    {
        return $this->composer;
    }

    /**
     * @param string|null $composer
     */
    public function setComposer(?string $composer): void
    {
        $this->composer = $composer;
    }

    /**
     * @return int
     */
    public function getMilliseconds(): int
    {
        return $this->milliseconds;
    }

    /**
     * @param int $milliseconds
     */
    public function setMilliseconds(int $milliseconds): void
    {
        $this->milliseconds = $milliseconds;
    }

    /**
     * @return int|null
     */
    public function getBytes(): ?int
    {
        return $this->bytes;
    }

    /**
     * @param int|null $bytes
     */
    public function setBytes(?int $bytes): void
    {
        $this->bytes = $bytes;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    /**
     * @param float $unitPrice
     */
    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }
}