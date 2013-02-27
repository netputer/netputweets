<?php
$base_url = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off" ? "https" : "http";
$base_url .= "://".$_SERVER["HTTP_HOST"];
$base_url .= ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";

define("ENCRYPTION_KEY", "putyourinfohere");

define("OAUTH_KEY", "putyourinfohere");
define("OAUTH_SECRET", "putyourinfohere");
define("EMBEDLY_KEY", "putyourinfohere");

define("BASE_URL", $base_url);

define("NPT_TITLE", "putyourinfohere");
define("API_ROOT", "https://api.twitter.com/1.1/");
define("LANG", "zh_CN");

define("IMGPROXY", 0);
define("IMGPROXY_THUMB", 1);
define("INVITE", 0);
define("INVITE_CODE", "putyourinfohere");
