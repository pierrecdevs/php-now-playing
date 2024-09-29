<?php
require_once('vendor/autoload.php');
require_once('config.php');

use Afh\Spotify\Controllers\SpotifyController;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/hud.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>[FKS] Now Playing</title>
</head>

<body class="bg-slate-800">
    <div class="flex justify-center">
        <div class="flex flex-col md:flex-row bg-white shadow-lg">
            <img class=" transition ease-in-out duration-300 w-full h-96 md:h-auto object-cover md:w-48" src="assets/images/spotify_icon_green.png" alt="artwork" id="artwork" />
            <div class="p-3 flex flex-col justify-center">
                <a id="playback" href="https://open.spotify.com/" target="_blank" class="mb-4">
                    <img class=" transition ease-in-out duration-300 w-full h-96 md:h-auto object-cover md:w-48 hidden" src="assets/images/Spotify_Logo_RGB_Green.png" alt="spotify logo" id="logo" />
                </a>
                <h5 class="text-gray-900 text-xl font-medium mb-2" id="song">Authorization Required</h5>
                <h5 class="text-gray-900 text-xl font-medium mb-4" id="artist">Please authorize the application</h5>
                <div class="w-full">

                    <div class="flex justify-between mb-1">
                        <span class="text-base font-medium text-blue-700" id="current">00:00:00</span>
                        <span class="text-sm font-medium text-blue-700" id="max">00:00:00</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: 0%" id="uiBar"></div>
                    </div>
                </div>
                <!-- <div id="progressBar" class="w-full mb-4"></div> -->
                <!-- <div class="flex flex-col md:flex-row md:max-w-xl hidden text-gray-600 text-sm">
                        <img src="assets/images/spotify_icon_green.png" class="w-10 h-10 mr-2">
                        <span class="mt-2">Listen on <a href="https://open.spotify.com" id="playback" class="text-green-300 hover:text-green-500 transition duration-300 ease-in-out"> Spotify</a></span>
                </div> -->
            </div>
        </div>
    </div>
    <script>
        const sEl = document.getElementById('song');
        const aEl = document.getElementById('artist');
        const pEl = document.getElementById('playback');
        const iEl = document.getElementById('artwork');
        // const bEl = document.getElementById('progressBar');
        const uEl = document.getElementById('uiBar');
        const lEl = document.getElementById('logo');
        const mEl = document.getElementById('max');
        const cEl = document.getElementById('current');
        let lastCheck = Date.now();
        let progress = 0;

        const formatTimeCode = async (milliseconds) => {
            let seconds = Math.floor(milliseconds / 1000);
            let minutes = Math.floor(seconds / 60);
            let hours = Math.floor(minutes / 60);

            milliseconds %= 1000;
            hours %= 60;
            seconds %= 60;
            minutes %= 60;

            hours = (hours <= 9) ? hours = '0' + hours : hours;
            seconds = (seconds <= 9) ? seconds = '0' + seconds : seconds;
            minutes = (minutes <= 9) ? minutes = '0' + minutes : minutes;

            return (hours > 1) ? `${hours}:${minutes}:${seconds}` : `${minutes}:${seconds}`;
        }

        const makeBar = async (current, max) => {
            if (current === 0) return;
            if (max === 0) return;
            if (uEl) {
                const perc = Math.floor((current / max) * 100);
                const currentText = await formatTimeCode(current);
                const durationText = await formatTimeCode(max);
                console.log('Current', current);
                console.log('Duration', max);
                console.log('Perc', perc);
                if (current > 0) {
                    mEl.textContent = durationText;
                    cEl.textContent = currentText;
                    uEl.style.width = perc + '%';
                }
            }
        };
        const asciiBar = async (current, max, width, fillChar, shadeChar) => {
            if (current === 0) return;
            if (max === 0) return;

            const plus = Math.round(current / max * width);
            const minus = width - plus;

            let i = 0;

            let p = "",
                m = "";

            while (i++ < plus) p += fillChar;

            i = 0;

            while (i++ < minus) m += shadeChar;

            const currentText = await formatTimeCode(current);
            const durationText = await formatTimeCode(max);

            bEl.textContent = `${currentText} [${p}${m}] ${durationText}`;
        }

        const getSong = async () => {
            const access_token = localStorage.getItem('fksnowplaying.access_token');
            if (!access_token) {
                authorize();
                return;
            }

            const response = await fetch(
                'song.php', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${access_token}`,
                        'Content-Type': 'application/json; charset=UTF8'
                    },
                }
            );

            const json = await response.json();
            if (json && json.track_name) {
                aEl.textContent = json.artists.join(',');
                sEl.textContent = json.track_name;
                pEl.parentNode.parentNode.classList.remove('hidden');
                makeBar(json.progress, json.duration);
                // asciiBar(json.progress, json.duration, 15, '▇', '―');
                iEl.setAttribute('src', json.album.images[1].url);
                iEl.classList.remove('w-full', 'h-96', 'md:h-auto', 'md:w-48');
                pEl.setAttribute('href', json.link);

                lEl.classList.remove('hidden');
                progress = json.progress;
            }

            setTimeout(async () => {
                try {
                    if ((Date.now() - lastCheck) > 35)
                        await getSong();
                    else
                        makeBar(json.progress, json.duration);
                    // asciiBar(json.progress, json.duration, 15, '▇', '―');
                } catch (error) {}
            }, 1000);
        };

        const getProfile = async () => {
            const access_token = localStorage.getItem('fksnowplaying.access_token');
            if (!access_token) {
                authorize();
                return;
            }

            const response = await fetch(
                'me.php', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${access_token}`,
                        'Content-Type': 'application/json; charset=UTF8'
                    },
                }
            );

            const json = await response.json();
            sEl.textContent = `Welcome ${json.display_name}`;
            aEl.textContent = 'Fetching current song...';
            await getSong();
        }

        const authorize = async () => {
            const code = location.search.includes('code=') ? location.search.replace('?code=', '') : undefined;
            const access_token = localStorage.getItem('fksnowplaying.access_token');
            let response;

            let user;
            if (!access_token && !code) {
                location.replace('<?= SpotifyController::hud(); ?>');
            } else if (code) {
                try {
                    response = await fetch('auth.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${code}`,
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

                    const avkey = localStorage.getItem('fks.slspotify.uuid');
                    if (avkey) {
                        const url = 'db.php?action=insert';
                        response = await fetch('db.php?action=insert', {
                            method: 'POST',
                            headers: {
                                'Content-type': 'application/json; charset=utf-8',
                            },
                            body: JSON.stringify({
                                avkey,
                                access_token,
                                refresh_token
                            }),
                        });
                    }
                    if (200 === response.status) {
                        localStorage.setItem('fksnowplaying.access_token', access_token);
                        localStorage.setItem('fksnowplaying.refresh_token', refresh_token);

                        history.pushState({}, '', '/now-playing/authorized');
                        sEl.textContent = 'Authorized!';
                        aEl.textContent = 'Getting profile...';

                        const profile = await getProfile();
                    }
                    /* } */
                } catch (error) {
                    sEl.textContent = "Error";
                    aEl.textContent = error.message;
                    img.src = "assets/images/Spotify_Icon_RGB_Black.png";
                    localStorage.clear();
                }

            } else {
                console.log('No token.');
            }
        };
        getProfile();
        addEventListener('beforeunload', (e) => {
            e.preventDefault();
            localStorage.clear();
            return e.returnValue = "Are you sure you want to exit?";
        });
    </script>
</body>

</html>