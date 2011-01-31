<?php
function browser_detect() {
	if ($browser = setting_fetch('browser')) {
		return browser_load($browser);
	}
	if ($_SERVER['HTTP_X_NOKIA_BEARER'] == 'GPRS') {
		return browser_load('text');
	}
	if (array_key_exists('HTTP_X_DEVICE_USER_AGENT', $_SERVER)) {
		$user_agent = $_SERVER['HTTP_X_DEVICE_USER_AGENT'];
	} else {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	}
	$handle = fopen('browsers/list.csv', 'r');
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		if (preg_match("#{$data[0]}#", $user_agent, $matches)) {
			browser_load($data[1]);
			break;
		}
	}
	fclose($handle);
}

function browser_load($browser) {
	$GLOBALS['current_theme'] = $browser;
	$file = "browsers/$browser.php";
	if (file_exists($file)) {
		require_once($file);
	}
}

?>