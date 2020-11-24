<?php

namespace Src\entities;


class Album
{
    public ?string $id;
    public string $title;
    public string $artistId;

    private function __construct(){}

    public static function make($title, $artistId){
        $album = new Album();
        $album->title = $title;
        $album->artistId = $artistId;
        return $album;
    }

    public static function makeWithId($id, $title, $artistId){
        $album = new Album();
        $album->id = $id;
        $album->title = $title;
        $album->artistId = $artistId;
        return $album;
    }

    public static function makeFromArray($data){
        $album = new Album();
        $album->id = $data['id'];
        $album->title = $data['title'];
        $album->artistId = $data['artist_id'];
        return $album;
    }
}