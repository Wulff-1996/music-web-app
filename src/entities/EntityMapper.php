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

    public static function toJsonAlbum(array $album): array
    {
        $result = array();
        $result['id'] = $album['AlbumId'];
        $result['title'] = $album['Title'];
        $result['artist'] = [
            'id' => $album['ArtistId'],
            'name' => $album['Name']
        ];
        return $result;
    }

    public static function toJsonAlbumDetails(array $album, array $tracks): array
    {
        $result = array();
        $result['id'] = $album['AlbumId'];
        $result['title'] = $album['Title'];
        $result['artist'] = [
            'id' => $album['ArtistId'],
            'name' => $album['Name']
        ];
        $result['track_total'] = sizeof($tracks);
        $result['tracks'] = array();

        foreach ($tracks as $track) {
            array_push($result['tracks'], [
                'id' => $track['TrackId'],
                'title' => $track['TrackName'],
                'composer' => $track['Composer'],
                'milliseconds' => $track['Milliseconds'],
                'bytes' => $track['Bytes'],
                'unit_price' => $track['UnitPrice'],
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

    public static function toJsonAlbumMultiple(array $album): array
    {
        $result = array();
        $result['page'] = $album['page'];
        $result['albums'] = array();

        foreach ($album['albums'] as $album) {
            array_push($result['albums'], [
                'id' => $album['AlbumId'],
                'title' => $album['Title'],
                'artist' => [
                    'id' => $album['ArtistId'],
                    'name' => $album['Name']
                ],
                'track_total' => $album['TrackTotal']
            ]);
        }
        return $result;
    }

    public static function toJsonArtist(array $aritst)
    {
        return [
            'id' => $aritst['ArtistId'],
            'name' => $aritst['Name'],
            'album_total' => $aritst['AlbumTotal']
        ];
    }

    public static function toJsonArtistMultiple(array $data)
    {
        $result = array();
        $result['page'] = $data['page'];
        $result['artists'] = array();

        foreach ($data['artists'] as $artist) {
            array_push($result['artists'], [
                'id' => $artist['ArtistId'],
                'name' => $artist['Name'],
                'album_total' => $artist['AlbumTotal']
            ]);
        }

        return $result;
    }
}