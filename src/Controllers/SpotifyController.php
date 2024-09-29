<?php

namespace Afh\Spotify\Controllers;

require_once('config.php');

use Afh\Spotify\Services\SpotifyService;
use Afh\Spotify\Models\SpotifyClient;


function formatMilliseconds($milliseconds)
{
  $seconds = floor($milliseconds / 1000);
  $minutes = floor($seconds / 60);
  $hours = floor($minutes / 60);
  $milliseconds = $milliseconds % 1000;
  $seconds = $seconds % 60;
  $minutes = $minutes % 60;
}

class SpotifyController
{

  public static function index(): string
  {

    $client = new SpotifyClient(array(
      'client_id' => CLIENT_ID,
      'client_secret' => CLIENT_SECRET,
      'redirect_uri' =>  REDIRECT_URI,
      'scope' => array(
        'user-modify-playback-state',
        'user-read-playback-state',
        'user-read-currently-playing',
        'user-read-private',
      ),
    ));

    return SpotifyService::generateAuthorizationURL($client);
  }

  public static function hud(): string
  {

    $client = new SpotifyClient(array(
      'client_id' => CLIENT_ID,
      'client_secret' => CLIENT_SECRET,
      'redirect_uri' =>  REDIRECT_URI,
      'scope' => array(
        'user-modify-playback-state',
        'user-read-playback-state',
        'user-read-currently-playing',
        'user-read-private',
      ),
    ));

    return SpotifyService::generateAuthorizationURL($client);
  }

  public static function getAccessToken($code)
  {
    return json_decode(SpotifyService::getAccessToken(CLIENT_ID, CLIENT_SECRET, REDIRECT_URI, $code)->getBody(), true);
  }

  public static function getRefreshToken($code)
  {
    return json_decode(SpotifyService::getRefreshToken(CLIENT_ID, CLIENT_SECRET, REDIRECT_URI, $code)->getBody(), true);
  }

  public static function nextSong($token)
  {
    $response = SpotifyService::nextSong($token);
    return $response->getStatusCode();
  }

  public static function previousSong($token)
  {

    $response = SpotifyService::previousSong($token);
    return $response->getStatusCode();
  }

  public static function togglePlayback($token)
  {
    // $response = json_decode(SpotifyService::getPlaybackState($token)->getBody(), true);
    // $response = json_decode(SpotifyService::getDevices($token)->getBody(), true);
    $response = SpotifyService::pausePlayback($token);
    return $response;
  }

  public static function getCurrentlyPlayingSong($token)
  {
    $json = json_decode(SpotifyService::getCurrentlyPlayingSong($token)->getBody(), true);
    file_put_contents('./data/song.json', json_encode($json));

    // $duration = ($json['item']['duration_ms']) ? gmdate('H:i:s', intval($json['item']['duration_ms'])/1000) : '00:00:00';
    // $progress = ($json['progress_ms']) ? gmdate('H:i:s', intval($json['progress_ms'])/1000) : '00:00:00';

    if ($json['error']) {
      return $json;
    }


    $duration = $json['item']['duration_ms'];
    $progress = $json['progress_ms'];

    $song = array(
      'track_id' => $json['item']['id'],
      'track_name' => $json['item']['name'],
      'duration' => $duration,
      'progress' => $progress,
      'link' => $json['item']['external_urls']['spotify'],
      'is_playing' => $json['is_playing'],
    );


    foreach ($json['item']['artists'] as $artist) {
      $artists[] = $artist['name'];
    }


    $song['artists'] = $artists;
    $song['album'] = $json['item']['album'];
    return $song;
  }

  public static function getProfile($token)
  {
    $json = json_decode(SpotifyService::getProfile($token)->getBody(), true);

    file_put_contents("data/profile.json", json_encode($json));
    if ($json['error']) {
      return $json;
    } else {
      return array(
        'display_name' => $json['display_name'],
        'id' => $json['id'],
        'followers' => $json['followers']['total'],
        'type' => $json['product'],

      );
    }
  }

  public static function getDevices($token)
  {
    $json = json_decode(SpotifyService::getDevices($token)->getBody(), true);

    return $json;
  }

  public static function getActiveDevice($token)
  {
    $json = SpotifyController::getDevices($token);

    $devices = array_filter($json['devices'], function ($d) {
      return $d['is_active'] === true;
    });

    return count($devices) > 0 ?  $devices[1] : $devices;
  }
}
