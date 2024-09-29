<?php

namespace Afh\Spotify\Services;

use Afh\Spotify\Models\SpotifyClient;
use GuzzleHttp\Client as GuzzleClient;

define('API_URL', 'https://api.spotify.com/');
define('AUTH_URL', 'https://accounts.spotify.com/');

class SpotifyService
{
  public static function generateAuthorizationURL(SpotifyClient $client): string
  {
    $client_id = $client->getClientId();
    $client_secret = $client->getClientSecret();
    $scope = implode(' ', $client->getScope());
    $redirect_uri = $client->getRedirectUri();

    // Implicit Grant (no refresh token, short lived)
    // return AUTH_URL . "authorize/?response_type=token&client_id={$client_id}&scope=" . urlencode($scope) . "&redirect_uri=" . $redirect_uri;

    // Requires DB
    return AUTH_URL . "authorize/?response_type=code&client_id={$client_id}&scope=" . urlencode($scope) . "&redirect_uri=" . $redirect_uri;
  }

  public static function getAccessToken(string $clientId, string $clientSecret, string $redirectUri, string $code)
  {

    $headers = array(
      "Content-Type" => "application/x-www-form-urlencoded",
      "Accept" => "application/json",
      "Authorization" => "Basic " . base64_encode($clientId . ":" . $clientSecret)
    );

    $payload = array(
      'grant_type' => 'authorization_code',
      'code' => $code,
      'redirect_uri' => $redirectUri
    );

    $gClient = new GuzzleClient();
    return $gClient->request('POST', AUTH_URL . "api/token", ['headers' => $headers, 'form_params' => $payload, 'allow_redirects' => true]);
  }

  public static function getRefreshToken(string $clientId, string $clientSecret, string $redirectUri, string $code)
  {

    $headers = array(
      "Content-Type" => "application/x-www-form-urlencoded",
      "Accept" => "application/json",
      "Authorization" => "Basic " . base64_encode($clientId . ":" . $clientSecret)
    );

    $payload = array(
      'grant_type' => 'refresh_token',
      'redirect_uri' =>  $redirectUri,
      'refresh_token' => $code

    );

    $gClient = new GuzzleClient();
    return  $gClient->request('POST', AUTH_URL . "api/token", ['headers' => $headers, 'form_params' => $payload, 'allow_redirects' => true]);
  }

  public static function getCurrentlyPlayingSong(string $token)
  {
    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('GET', API_URL . "v1/me/player/currently-playing", ['headers' => $headers]);
    return $response;
  }

  public static function getProfile(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('GET', API_URL . "v1/me", ['headers' => $headers, 'http_errors' => false]);
    return $response;
  }

  public static function resumePlayback(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('PUT', API_URL . "v1/me/player/play", ['headers' => $headers, 'http_errors' => false]);

    return $response;
  }

  public static function pausePlayback(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('PUT', API_URL . "v1/me/player/pause", ['headers' => $headers, 'http_errors' => false]);

    return $response;
  }

  public static function getPlaybackState(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('PUT', API_URL . "v1/me/player", ['headers' => $headers, 'http_errors' => false]);

    return $response;
  }

  public static function getDevices(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('GET', API_URL . "v1/me/player/devices", ['headers' => $headers, 'http_errors' => false]);

    return $response;
  }

  public static function previousSong(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('POST', API_URL . "v1/me/player/previous", ['headers' => $headers, 'http_errors' => false]);
    return $response;
  }

  public static function nextSong(string $token)
  {

    $headers = array(
      "Content-Type" => "application/json",
      "Accept" => "application/json",
      "Authorization" => "Bearer " . $token
    );

    $gClient = new GuzzleClient();
    $response = $gClient->request('POST', API_URL . "v1/me/player/next", ['headers' => $headers, 'http_errors' => false]);
    return $response;
  }
}
