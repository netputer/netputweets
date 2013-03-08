<?php

$base_url = is_https() ? "https" : "http";
$base_url .= "://".$_SERVER["HTTP_HOST"];
$base_url .= ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";

define('BASE_URL', $base_url);

define('ENCRYPTION_KEY', 'putyourinfohere');
define('OAUTH_KEY', 'putyourinfohere');
define('OAUTH_SECRET', 'putyourinfohere');
define('EMBEDLY_KEY', 'putyourinfohere');

define('NPT_TITLE', 'putyourinfohere');
define('API_ROOT', 'https://api.twitter.com/1.1/');
define('LANG', 'zh_CN');

define('INVITE', 0);
define('INVITE_CODE', 'putyourinfohere');

define('IMGPROXY', 0);
define('IMGTHUMB', 0);

define('GA_ACCOUNT', 'putyourinfohere');
