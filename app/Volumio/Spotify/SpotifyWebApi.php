<?php

namespace App\Volumio\Spotify;

use App\Volumio\WebApis\WebApi;

class SpotifyWebApi
{
    private $api;
    private $baseUrl = "https://api.spotify.com";
    private $userId;
    
    public function __construct(WebApi $api)
    {
        $accessToken = session("spotify_access_token");
        
        if ($accessToken)
        {
            $api->setHeaders([ "Authorization" => "Bearer " . $accessToken,
                                "Content-Type" => "application/json" ]);            
        }
        
        $this->userId = session("spotify_userId");
        
        $api->setBaseUrl($this->baseUrl);
        
        $this->api = $api;
    }
    
    public function isAuthenticated()
    {
        return session("spotify_access_token");
    }
    
    public function authenticate($code)
    {
        $clientId = "ab6fd2e9ddd04857947ea58e3e44678a";
        $clientSecret = "9ff97405ccfd47e9b656ae0af0c981a1";
        
        $body = [
            "code" => $code,
            "grant_type" => "authorization_code",
            "redirect_uri" => "http://homestead.app:8000",
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
        $clientIdSecret = base64_encode($clientId . ":" . $clientSecret);
        $client = new \GuzzleHttp\Client();
        $request = $client->request('POST', 'https://accounts.spotify.com/api/token', ["form_params" => $body]);
        $response = json_decode($request->getBody());
        session(["spotify_access_token" => $response->access_token,
                 "spotify_refresh_token" => $response->refresh_token]);
                 
        $this->api->setHeaders([ "Authorization" => "Bearer " . $response->access_token ]);
        
        $user = $this->getCurrentUser();
        
        session(["spotify_userId" => $user->id]);
    }
    
    public function addMyAlbums($albums)
    {
        $albums = json_encode((array) $albums);
        
        $uri = '/v1/me/albums';
        return $this->api->put($uri, $albums);
    }

    public function addMyTracks($tracks)
    {
        $tracks = json_encode((array) $tracks);
        
        $uri = '/v1/me/tracks';
        return $this->api->put($uri, $tracks);
    }

    public function addUserPlaylistTracks($userId, $playlistId, $tracks, $options = array())
    {
        $options = http_build_query($options);
        $tracks = $this->idToUri($tracks);
        $tracks = json_encode((array) $tracks);
        
        // We need to manually append data to the URI since it's a POST request
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/tracks?' . $options;
        return $this->api->post($uri, $tracks);
    }

    public function createUserPlaylist($options, $userId = null)
    {        
        $userId = $userId ?: $this->userId;
        
        $uri = '/v1/users/' . $userId . '/playlists';
        return $this->api->post($uri, $options);
    }

    public function currentUserFollows($type, $ids)
    {
        $ids = implode(',', (array) $ids);
        $options = array(
            'ids' => $ids,
            'type' => $type,
        );
        
        $uri = '/v1/me/following/contains';
        return $this->api->get($uri, $options);
    }

    public function deleteMyAlbums($albums)
    {
        $albums = json_encode(
            (array) $albums
        );
        
        $uri = '/v1/me/albums';
        return $this->api->delete($uri, $albums);
    }

    public function deleteMyTracks($tracks)
    {
        $tracks = json_encode(
            (array) $tracks
        );
        
        $uri = '/v1/me/tracks';
        return $this->api->delete($uri, $tracks);
    }

    public function deleteUserPlaylistTracks($userId, $playlistId, $tracks, $snapshotId = '')
    {
        $options = array();
        if ($snapshotId) {
            $options['snapshot_id'] = $snapshotId;
        }
        $options['tracks'] = array();
        for ($i = 0; $i < count($tracks); $i++) {
            $track = array();
            if (isset($tracks[$i]['positions'])) {
                $track['positions'] = (array) $tracks[$i]['positions'];
            }
            $track['uri'] = $this->idToUri($tracks[$i]['id']);
            $options['tracks'][] = $track;
        }
        $options = json_encode($options);
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/tracks';
        $body = $this->api->delete($uri, $options);
        //$body = $this->lastResponse['body'];
        if (isset($body->snapshot_id)) {
            return $body->snapshot_id;
        }
        return false;
    }

    public function followArtistsOrUsers($type, $ids)
    {
        $ids = json_encode(array(
            'ids' => (array) $ids,
        ));
        
        // We need to manually append data to the URI since it's a PUT request
        $uri = '/v1/me/following?type=' . $type;
        return $this->api->put($uri, $ids);
    }

    public function followPlaylist($userId, $playlistId, $options = array())
    {
        $options = json_encode($options);
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/followers';
        
        return $this->api->put($uri, $options);
    }

    public function getAlbum($albumId)
    {
        $uri = '/v1/albums/' . $albumId;
        
        return $this->api->get($uri, array());
    }

    public function getAlbums($albumIds, $options = array())
    {
        $options['ids'] = implode(',', $albumIds);
        
        $uri = '/v1/albums/';
        return $this->api->get($uri, $options);
    }

    public function getAlbumTracks($albumId, $options = array())
    {
        $uri = '/v1/albums/' . $albumId . '/tracks';
        
        return $this->api->get($uri, $options);
    }

    public function getArtist($artistId)
    {
        $uri = '/v1/artists/' . $artistId;
        
        return $this->api->get($uri, array());
    }

    public function getArtists($artistIds)
    {
        $artistIds = implode(',', $artistIds);
        $options = array(
            'ids' => $artistIds,
        );
        
        $uri = '/v1/artists/';
        
        return $this->api->get($uri, $options);
    }

    public function getArtistRelatedArtists($artistId)
    {
        $uri = '/v1/artists/' . $artistId . '/related-artists';
        
        return $this->api->get($uri, array());
    }

    public function getArtistAlbums($artistId, $options = array())
    {
        $options = (array) $options;
        if (isset($options['album_type'])) {
            $options['album_type'] = implode(',', (array) $options['album_type']);
        }
        
        $uri = '/v1/artists/' . $artistId . '/albums';
        
        return $this->api->get($uri, $options);
    }

    public function getArtistTopTracks($artistId, $options)
    {
        $uri = '/v1/artists/' . $artistId . '/top-tracks';
        
        return $this->api->get($uri, $options);
    }
    
    public function getFeaturedPlaylists($options = array())
    {
        $uri = '/v1/browse/featured-playlists';
        
        return $this->api->get($uri, $options);
    }

    public function getCategoriesList($options = array())
    {
        $uri = '/v1/browse/categories';
        
        return $this->api->get($uri, $options)->categories;
    }

    public function getCategory($categoryId, $options = array())
    {
        $uri = '/v1/browse/categories/' . $categoryId;
        
        return $this->api->get($uri, $options);
    }

    public function getCategoryPlaylists($categoryId, $options = array())
    {
        $uri = '/v1/browse/categories/' . $categoryId . '/playlists';
        
        return $this->api->get($uri, $options);
    }

    public function getNewReleases($options = array())
    {
        $uri = '/v1/browse/new-releases';
        
        return $this->api->get($uri, $options);
    }

    public function getMyPlaylists($options = array())
    {
        $uri = '/v1/me/playlists';
        
        return $this->api->get($uri, $options);
    }

    public function getMySavedAlbums($options = array())
    {
        $uri = '/v1/me/albums';
        
        return $this->api->get($uri, $options);
    }

    public function getMySavedTracks($options = array())
    {
        $uri = '/v1/me/tracks';
        
        return $this->api->get($uri, $options);
    }

    public function getTrack($trackId, $options = array())
    {
        $uri = '/v1/tracks/' . $trackId;
        return $this->api->get($uri, $options);
    }

    public function getTracks($trackIds, $options = array())
    {
        $options['ids'] = implode(',', $trackIds);
        
        $uri = '/v1/tracks/';
        return $this->api->get($uri, $options);
    }

    public function getUser($userId)
    {
        $uri = '/v1/users/' . $userId;
        return $this->api->get($uri, array());
    }

    public function getUserFollowedArtists($options = array())
    {
        $options = (array) $options;
        if (!isset($options['type'])) {
            $options['type'] = 'artist'; // Undocumented until more values are supported.
        }
        
        $uri = '/v1/me/following';
        return $this->api->get($uri, $options);
    }

    public function getUserPlaylist($playlistId, $userId = null, $options = array())
    {
        $options = (array) $options;
        if (isset($options['fields'])) {
            $options['fields'] = implode(',', (array) $options['fields']);
        }
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId;
        return $this->api->get($uri, $options);
    }
    
    public function getUserPlaylists($userId = null, $options = array())
    {
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists';
        
        return $this->api->get($uri, $options);
    }

    public function getUserPlaylistTracks($playlistId, $userId = null, $options = array())
    {
        if (isset($options['fields'])) {
            $options['fields'] = implode(',', (array) $options['fields']);
        }
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/tracks';
        return $this->api->get($uri, $options);
    }

    public function getCurrentUser()
    {
        $uri = '/v1/me';
        return $this->api->get($uri, array());
    }

    public function myAlbumsContains($albums)
    {
        $albums = implode(',', (array) $albums);
        $options = array(
            'ids' => $albums,
        );
        
        $uri = '/v1/me/albums/contains';
        return $this->api->get($uri, $options);
    }

    public function myTracksContains($tracks)
    {
        $tracks = implode(',', (array) $tracks);
        $options = array(
            'ids' => $tracks,
        );
        
        $uri = '/v1/me/tracks/contains';
        return $this->api->get($uri, $options);
    }

    public function reorderUserPlaylistTracks($playlistId, $options, $userId = null)
    {
        $options = json_encode($options);
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/tracks';
        $body = $this->api->put($uri, $options);
        //$body = $this->lastResponse['body'];
        if (isset($body->snapshot_id)) {
            return $body->snapshot_id;
        }
        return false;
    }

    public function replaceUserPlaylistTracks($playlistId, $tracks, $userId = null)
    {
        $tracks = $this->idToUri($tracks);
        $tracks = json_encode(array(
            'uris' => (array) $tracks,
        ));
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/tracks';
        return $this->api->put($uri, $tracks);
    }

    public function search($query, $type, $options = array())
    {
        $type = implode(',', (array) $type);
        $options = array_merge((array) $options, array(
            'q' => $query,
            'type' => $type,
        ));
        
        $uri = '/v1/search';
        
        return $this->api->get($uri, $options);
    }
    
    public function unfollowArtistsOrUsers($type, $ids)
    {
        $ids = json_encode(array(
            'ids' => (array) $ids,
        ));
        
        // We need to manually append data to the URI since it's a DELETE request
        $uri = '/v1/me/following?type=' . $type;
        return $this->api->delete($uri, $ids);
    }

    public function unfollowPlaylist($playlistId, $userId = null)
    {
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId . '/followers';
        
        return $this->api->delete($uri, array());
    }
    
    public function updateUserPlaylist($playlistId, $options, $userId = null)
    {
        $options = json_encode($options);
        
        $uri = '/v1/users/' . $userId ?: $this->userId . '/playlists/' . $playlistId;
        return $this->api->put($uri, $options);
    }
    
    public function userFollowsPlaylist($playlistId, $options, $ownerId = null)
    {
        $options = (array) $options;
        if (isset($options['ids'])) {
            $options['ids'] = implode(',', (array) $options['ids']);
        }
        
        $uri = '/v1/users/' . $ownerId ?: $this->userId . '/playlists/' . $playlistId . '/followers/contains';
        return $this->api->get($uri, $options);
    }
}
