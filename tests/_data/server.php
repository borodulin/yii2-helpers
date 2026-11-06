<?php
// router for the PHP built-in web server used by CurlTest
switch (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    case '/redirect':
        setcookie('session', 'abc');
        header('Location: /cookie');
        return;
    case '/cookie':
        setcookie('n', (string)mt_rand());
        echo isset($_COOKIE['session']) ? 'session=' . $_COOKIE['session'] : 'no-session';
        return;
    case '/file-redirect':
        header('Location: file:///etc/passwd');
        return;
    default:
        echo 'ok';
}
