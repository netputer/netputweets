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

// Google Analytics Mobile tracking code
// You need to download ga.php from the Google Analytics website for this to work
// Copyright 2009 Google Inc. All Rights Reserved.
$GA_ACCOUNT = "";
$GA_PIXEL = "ga.php";

function googleAnalyticsGetImageUrl() {
  global $GA_ACCOUNT, $GA_PIXEL;
  $url = "";
  $url .= $GA_PIXEL . "?";
  $url .= "utmac=" . $GA_ACCOUNT;
  $url .= "&utmn=" . rand(0, 0x7fffffff);
  $referer = $_SERVER["HTTP_REFERER"];
  $query = $_SERVER["QUERY_STRING"];
  $path = $_SERVER["REQUEST_URI"];
  if (empty($referer)) {
    $referer = "-";
  }
  $url .= "&utmr=" . urlencode($referer);
  if (!empty($path)) {
    $url .= "&utmp=" . urlencode($path);
  }
  $url .= "&guid=ON";
  return str_replace("&", "&amp;", $url);
}
