<?php
require_once('vendor/autoload.php');
require_once('config.php');

use Afh\Spotify\Controllers\SpotifyController;

$pdo = new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=UTF8', DB_USER,  DB_PASSWORD) or die('Could not connect');

$action = (isset($_GET['action'])) ? $_GET['action'] : null;
$data = file_get_contents('php://input');
$json = json_decode($data, true);

if ($action === 'insert') {
    $sql = 'SELECT uid,oauth FROM users WHERE avkey = :avkey LIMIT 1;';
    $stmt = $pdo->prepare($sql);

    $data = array(':avkey' => $json['avkey']);

    $stmt->execute($data);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (! $results) {
        $sql = 'INSERT INTO users (avkey, oauth, refresh_token) VALUES (:avkey, :oauth, :refresh_token);';
        $stmt = $pdo->prepare($sql);

        $data = array(
            ':avkey' => $json['avkey'],
            ':oauth' => $json['access_token'],
            ':refresh_token' => $json['refresh_token'],
        );

        $success = $stmt->execute($data);
        $results = ['success' => $success, 'id' => $pdo->lastInsertId(), 'token' => $json['token']];
    } else {
        $results = ['success' => $success, 'id' => $results['id'], 'token' => $results['token']];
    }
} elseif ($action === 'delete') {
    $sql = 'DELETE FROM users WHERE avkey = :avkey;';
    $stmt = $pdo->prepare($sql);

    $data = array(
        ':avkey' => $json['avkey']
    );

    $bSuccess = $stmt->execute($data);
    $results = [
        'action' => "require_authorization"
    ];
} elseif ($action === 'read') {

    $stmt = $pdo->prepare('SELECT oauth as token, refresh_token FROM users WHERE avkey = :avkey LIMIT 1;');
    $stmt->execute(array(':avkey' => $json['avkey']));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) > 0) {
        $results = [
            'action' => 'authorized',
            'refresh_token' => $rows[0]['refresh_token'],
            'token' => $rows[0]['token'],
            'avkey' => $json['avkey']
        ];

        $json = SpotifyController::getProfile($results['token']);
        if ($json['error']) {
            if (401 === $json['error']['status']) {
                $reply = SpotifyController::getRefreshToken($results['refresh_token']);
                if ($reply['access_token']) {
                    $results['token'] = $reply['access_token'];
                    $sql = 'UPDATE users SET oauth = :oauth WHERE avkey = :avkey';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(
                        array(
                            ':oauth' => $reply['access_token'],
                            ':avkey' => $json['avkey']
                        )
                    );
                }
            }
        }
    } else {
        $results = [
            'action' => "require_authorization",
        ];
    }
} else {
    $results = [];
}

header("Content-type: application/json");
echo json_encode($results);
