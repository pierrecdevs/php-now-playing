<?php
require_once('vendor/autoload.php');
require_once('config.php');

use Afh\Spotify\Controllers\SpotifyController;

$input = file_get_contents('php://input');
$json = json_decode($input, true);

$code = SpotifyController::getAccessToken($json['code']);
header('Content-Type: application/json; charset=UTF8');
echo json_encode($code);
