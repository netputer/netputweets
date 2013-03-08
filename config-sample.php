<?php

define('ENCRYPTION_KEY', 'putyourinfohere');

define('OAUTH_KEY', 'putyourinfohere');
define('OAUTH_SECRET', 'putyourinfohere');
define('EMBEDLY_KEY', 'putyourinfohere');

//define BASE_URL as you want
define('BASE_URL_CONFIG', 'putyourinfohere');

if (BASE_URL_CONFIG) {
    define('BASE_URL', BASE_URL_CONFIG);
}
//else we will figure it out
else {
    $base_url = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off" ? "https" : "http";
    $base_url .= "://".$_SERVER["HTTP_HOST"];
    $base_url .= ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";
    define('BASE_URL', $base_url);
}

$relative_url = ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";
define('RELATIVE_URL', $relative_url);

define('NPT_TITLE', 'putyourinfohere');
define('API_ROOT', 'https://api.twitter.com/1.1/');
define('LANG', 'zh_CN');

define('IMGPROXY', 0);
define('IMGTHUMB', 0);
define('INVITE', 0);
define('INVITE_CODE', 'putyourinfohere');

define('GA_ACCOUNT', 'putyourinfohere');
