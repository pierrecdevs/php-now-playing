<?php

namespace Afh\Spotify\Models;

class SpotifyClient
{
  private $client_secret = '';
  private $client_id = '';
  private $redirect_uri = '';
  private $oauth_token = '';
  private $scope = '';
  function __construct(array $config)
  {
    $this->client_id = $config['client_id'];
    $this->client_secret = $config['client_secret'];
    $this->redirect_uri = $config['redirect_uri'];
    $this->scope = $config['scope'];
  }


  public function getClientId()
  {
    return $this->client_id;
  }

  public function getRedirectUri()
  {
    return  $this->redirect_uri;
  }

  public function getClientSecret()
  {
    return $this->client_secret;
  }

  public function getScope()
  {
    return $this->scope;
  }
  public function getToken()
  {
    return $this->oauth_token;
  }
}
