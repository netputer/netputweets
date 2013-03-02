<?php
error_reporting(error_reporting() & ~E_NOTICE);

if (!file_exists('config.php')) {
	$base_url = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off" ? "https" : "http";
	$base_url .= "://".$_SERVER["HTTP_HOST"];
	$base_url .= ($directory = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\,")) ? "/$directory/" : "/";

	header('Location: '.$base_url.'setup.php');
	exit;
}

require('config.php');

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . date('r'));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-type: text/html; charset=utf-8');

require 'languages/languages.php';
require 'common/theme.php';
require 'common/browser.php';
require 'common/menu.php';
if (!function_exists('mcrypt_module_open')) require 'common/class.xxtea.php';
require 'common/user.php';
require 'common/twitter.php';
require 'common/settings.php';

menu_register(array(
	'about' => array(
		'callback' => 'about_page',
		'title' => __("About"),
	),
	'logout' => array(
		'security' => true,
		'callback' => 'logout_page',
		'title' => __("Logout"),
	),
));

function logout_page() {
	user_logout();
	header("Location: ".BASE_URL);
	exit;
}

function about_page() {
	$content = file_get_contents('about.html');
	theme('page', __("About"), $content);
}

browser_detect();
menu_execute_active_handler();
