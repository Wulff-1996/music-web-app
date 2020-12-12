<?php


namespace Wulff\entities;


class EntityMapper
{
    public static function toJsonTrackSingle(array $track)
    {
        return [
            'id' => $track['TrackId'],
            'title' => $track['TrackName'],
            'composer' => $track['Composer'],
            'milliseconds' => $track['Milliseconds'],
            'bytes' => $track['Bytes'],
            'unit_price' => $track['UnitPrice'],
            'artist' => [
                'id' => $track['ArtistId'],
                'name' => $track['ArtistName']
            ],
            'album' => [
                'id' => $track['AlbumId'],
                'title' => $track['AlbumTitle']
            ],
            'genre' => [
                'id' => $track['GenreId'],
                'name' => $track['GenreName']
            ],
            'media' => [
                'id' => $track['MediaTypeId'],
                'name' => $track['MediaName']
            ]
        ];
    }

    public static function toJsonTrackMultiple(array $data): array
    {
        $result = array();
        $result['page'] = $data['page'];
        $result['tracks'] = array();

        foreach ($data['tracks'] as $track) {

            array_push($result['tracks'], [
                'id' => $track['TrackId'],
                'title' => $track['TrackName'],
                'composer' => $track['Composer'],
                'milliseconds' => $track['Milliseconds'],
                'bytes' => $track['Bytes'],
                'unit_price' => $track['UnitPrice'],
                'artist' => [
                    'id' => $track['ArtistId'],
                    'name' => $track['ArtistName']
                ],
                'album' => [
                    'id' => $track['AlbumId'],
                    'title' => $track['AlbumTitle']
                ],
                'genre' => [
                    'id' => $track['GenreId'],
                    'name' => $track['GenreName']
                ],
                'media' => [
                    'id' => $track['MediaTypeId'],
                    'name' => $track['MediaName']
                ]
            ]);
        }
        return $result;
    }

}