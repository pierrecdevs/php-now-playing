<?php
require_once('vendor/autoload.php');
require_once('config.php');

use Afh\Spotify\Controllers\SpotifyController;

$avkey = $_SERVER['HTTP_X_SECONDLIFE_OWNER_KEY'];
$pdo = new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=UTF8', DB_USER,  DB_PASSWORD) or die('Could not connect');

$sql = 'SELECT oauth, refresh_token FROM users WHERE avkey = :avkey LIMIT 1;';
$stmt = $pdo->prepare($sql);
$success = $stmt->execute(array(':avkey' => $avkey));
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$profile = [];

if ($success && count($rows) === 1) {
  $user = [
    'token' => $rows[0]['oauth'],
    'refresh_token' => $rows[0]['refresh_token'],
  ];

  $profile = SpotifyController::getProfile($user['token']);
  $json = SpotifyController::getRefreshToken($user['refresh_token']);

  if (!is_null($json['access_token'])) {
    $sql = 'UPDATE users set oauth=:oauth;';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':oauth' => $json['access_token']));
    $profile = SpotifyController::getProfile($json['access_token']);
  }
} else {
  if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    list($type, $data) = explode(" ", $_SERVER['HTTP_AUTHORIZATION'], 2);

    if (strcasecmp($type, "Bearer") === 0) {
      $profile = SpotifyController::getProfile($data);
    }
  }
}
if (! headers_sent()) {
  header('Content-Type: application/json');
}

echo json_encode($profile);
