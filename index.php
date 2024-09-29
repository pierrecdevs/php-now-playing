<?php
require_once('vendor/autoload.php');
require_once('config.php');

use Afh\Spotify\Controllers\SpotifyController;

$u = trim($_GET['u']);
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>[FKS] Now Playing</title>
  <base href="https://scripts.slreviews.com/now-playing/">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/styles.css?r=<?= rand(1, 999); ?>">
</head>

<body>
  <div id="app" class="container center">
    <h1 id="title">Loading</h1>
    <h3 id="sub" class="flex-row">Please wait...</h3>
    <img id="loader" src="assets/images/loading.gif?r=<?= rand(1, 999); ?>" alt="Loading..." class="flex-row icon">
    <div id="alert" class="alert"></div>
  </div>
  <script defer>
    const app = document.getElementById('app');
    const title = document.getElementById('title');
    const sub = document.getElementById('sub');
    const img = document.getElementById('loader');
    const alert = document.getElementById("alert");
    let avkey = '';

    /**
     * llXorBase64
     * @param {string} string1 - data
     * @param {string} string2 - key
     * @returns {string} - encode/decoded LSL equivelant
     */
    const llXorBase64 = (s1, s2) => {
      s1 = atob(s1);
      const l1 = s1.length;

      s2 = atob(s2);

      if (l1 > s2.length)
        s2 = s2.padEnd(l1, s2);

      const xored = xorStrings(s1, s2);
      return btoa(xored);
    };

    /**
     * xorString
     * @param {string} a - first string
     * @param {string} b - second string
     * @return {string}
     */
    const xorStrings = (a, b) => {
      let s = [];

      for (let i = 0; i < Math.max(a.length, b.length); i++) {
        s.push(String.fromCharCode((a.charCodeAt(i) || 0) ^ (b.charCodeAt(i) || 0)));
      }
      return s.join('');
    }

    /**
     * isValidUUID
     * @param {string} uuid
     * @return {boolean}
     */
    const isValidUUID = (uuid) => {
      const pattern = /^[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i;
      return pattern.test(uuid);
    };

    /**
     * getToken
     */
    const getToken = async () => {
      const hash = location.hash ? location.hash : undefined;
      const code = location.search.includes('code=') ? location.search.replace('?code=', '') : undefined;
      let response;
      let url = 'db.php?action=insert';
      let user;

      if (!hash && !code) {
        location.replace('<?= SpotifyController::index(); ?>');
      } else if (code) {
        try {
          response = await fetch('auth.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json; charset=UTF8'
            },
            body: JSON.stringify({
              code
            })
          });
          const {
            access_token,
            refresh_token,
            error
          } = await response.json();

          avkey = localStorage.getItem('fks.slspotify.uuid');
          if (avkey) {
            response = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                avkey,
                access_token,
                refresh_token
              })
            });
            if (200 === response.status) {
              localStorage.clear();

              title.innerText = "Authorized";
              sub.innerText = 'You may now close this page.';
              img.src = "assets/images/spotify_icon_green.png";
              history.pushState({}, '', '/now-playing/authorized');
            }
          }
        } catch (error) {
          title.innerText = "Error";
          sub.innerText = error.message;
          img.src = "assets/images/Spotify_Icon_RGB_Black.png";
        }

      } else {
        console.log('No token.');
      }
    };

    const setup = () => {
      const u = '<?= $u; ?>';
      const k = 'c2xyZXZpZXdzLmNvbQ==';
      if (u.trim() !== '') {
        const decoded = atob(llXorBase64(u, k));
        const parts = decoded.split('|');
        const [uuid, url] = parts;
        if (!localStorage.getItem('fks.slspotify.url')) {
          localStorage.setItem('fks.slspotify.uuid', uuid);
          localStorage.setItem('fks.slspotify.url', url);
        }
      }
      getToken();
    };

    setup();
    addEventListener('beforeunload', (e) => {
      e.preventDefault();
      localStorage.clear();
      return e.returnValue = "Are you sure you want to exit?";
    });
  </script>
</body>

</html>