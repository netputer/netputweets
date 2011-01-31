<?php
require 'gettext.php';
require 'streams.php';

function get_locale() {
	if (defined('LANG')) $locale = LANG;
	$langs = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	$locale = str_replace('-', '_', $langs[0]);
	$settings = (array) unserialize(base64_decode($_COOKIE['settings']));
	if (array_key_exists("locale", $settings)) $locale = $settings["locale"];
	if (empty($locale)) $locale = 'zh_CN';
	return $locale;
}

function load_textdomain() {
	$locale = get_locale();
	$mofile = "./languages/$locale.mo";
	if (is_readable($mofile)) {
		$input = new CachedFileReader($mofile);
	} else {
		return;
	}
	$gettext = new gettext_reader($input);
	if (isset($gettext)) $gettext->load_tables();
	return $gettext;
}

function __($text) {
	global $gettext;
	if(is_object($gettext)) {
		return $gettext->translate($text);
	}
	return $text;
}

$gettext = load_textdomain();
?>
