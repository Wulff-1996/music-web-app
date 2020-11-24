<?php
namespace Src\Entities;

class Artist {

    public $id;
    public $name;

    private function __construct(){}

    public static function make($name){
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