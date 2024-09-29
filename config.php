<?php
$root = $_SERVER['DOCUMENT_ROOT'];

$envFilepath = "{$root}/now-playing/.env";

if (is_file($envFilepath)) {
  $file = new \SplFileObject($envFilepath);

  // Loop until we reach the end of the file.
  while (false === $file->eof()) {
    // Get the current line value, trim it and save by 
    $line = $file->fgets();
    preg_match("/([^#]+)\=(.*)/", $line, $matches);
    if (isset($matches[2])) {
      putenv(trim($line));
    }
  }
}

$clientId = getenv('CLIENT_ID', 'REPLACE THIS WITH YOURS');
$clientSecret = getenv('CLIENT_SECRET', 'REPLACE THIS WITH YOURS');
$redirectUri = getenv('REDIRECT_URI', 'REPLACE THIS WITH YOURS');

$dbhost = getenv('DB_HOST', 'REPLACE THIS WITH YOURS');
$dbuser = getenv('DB_USER', 'REPLACE THIS WITH YOURS');
$dbpass = getenv('DB_PASSWORD', 'REPLACE THIS WITH YOURS');
$dbname = getenv('DB_NAME', 'REPLACE THIS WITH YOURS');

define('CLIENT_ID', $clientId);
define('CLIENT_SECRET', $clientSecret);
define('REDIRECT_URI', $redirectUri);

define('DB_HOST', $dbhost);
define('DB_USER', $dbuser);
define('DB_PASSWORD', $dbpass);
define('DB_NAME', $dbname);
