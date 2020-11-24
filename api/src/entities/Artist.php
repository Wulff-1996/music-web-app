<?php
namespace Src\Entities;

class Artist {

    public ?string $id;
    public string $name;

    private function __construct(){}

    public static function make(string $name){
        $artist = new Artist();
        $artist->name = $name;
        return $artist;
    }

    public static function makeWithId($id, $name){
        $artist = new Artist();
        $artist->id = $id;
        $artist->name = $name;
        return $artist;
    }


}