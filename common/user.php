<?php

menu_register(array(
	'oauth' => array(
		'callback' => 'user_oauth',
		'hidden' => 'true',
	),
	'itap' => array(
		'callback' => 'user_itap',
		'hidden' => 'true',
	),
));

function user_oauth() {
	require_once 'OAuth.php';
	session_start();
	
	$GLOBALS['user']['type'] = 'oauth';
	
	if ($oauth_token = $_GET['oauth_token']) {
		$params = array('oauth_verifier' => $_GET['oauth_verifier']);
		$response = twitter_process('https://api.twitter.com/oauth/access_token', $params);
		
		unset($_SESSION['oauth_request_token_secret']);
		
		parse_str($response, $token);
		
		// 判断 user 是否在列表中
		if (INVITE && !_is_user_invited($token['screen_name'])) {
			unset($GLOBALS['user']);
			exit('对不起，您不是受邀用户，无法登录（如果你有邀请码，<a href="'.RELATIVE_URL.'invite.php">请自行添加</a>）');
		}
		
		$GLOBALS['user']['username'] = $token['screen_name'];
		$GLOBALS['user']['password'] = $token['oauth_token'] .'|'.$token['oauth_token_secret'];
		
		_user_save_cookie();
		header('Location: '. RELATIVE_URL);
		return;
	}
	
	$params = array('oauth_callback' => BASE_URL.'oauth');
	$response = twitter_process('https://api.twitter.com/oauth/request_token', $params);
	
	parse_str($response, $token);
	$_SESSION['oauth_request_token_secret'] = $token['oauth_token_secret'];
	$authorise_url = 'https://api.twitter.com/oauth/authorize?oauth_token='.$token['oauth_token'];
	
	header('Location: '.$authorise_url);
}

function user_itap() {
	require_once 'OAuth.php';
	session_start();
	$GLOBALS['user']['type'] = 'oauth';
	$params = array('oauth_callback' => RELATIVE_URL.'oauth');
	$response = twitter_process('https://api.twitter.com/oauth/request_token', $params);
	parse_str($response, $token);
	$_SESSION['oauth_request_token_secret'] = $token['oauth_token_secret'];
	$authorise_url = RELATIVE_URL.'oauthproxy/authorize?oauth_token='.$token['oauth_token'];
	header("Location: $authorise_url");
}

function user_oauth_sign(&$url, &$args = false) {
	require_once 'OAuth.php';
	$method = $args !== false ? 'POST' : 'GET';
	if (preg_match_all('#[?&]([^=]+)=([^&]+)#', $url, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$args[$match[1]] = $match[2];
		}
	$url = substr($url, 0, strpos($url, '?'));
	}
	$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
	$consumer = new OAuthConsumer(OAUTH_KEY, OAUTH_SECRET);
	$token = NULL;
	if (($oauth_token = $_GET['oauth_token']) && $_SESSION['oauth_request_token_secret']) {
		$oauth_token_secret = $_SESSION['oauth_request_token_secret'];
	} else {
		list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);
	}
	if ($oauth_token && $oauth_token_secret) {
		$token = new OAuthConsumer($oauth_token, $oauth_token_secret);
	}
	$request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $args);
	$request->sign_request($sig_method, $consumer, $token);
	switch ($method) {
		case 'GET':
			$url = $request->to_url();
		$args = false;
			return;
		case 'POST':
			$url = $request->get_normalized_http_url();
			$args = $request->to_postdata();
			return;
	}
}

function user_ensure_authenticated() {
	if (!user_is_authenticated()) {
		$content = theme('login');
		$content .= file_get_contents('about.html');
		theme('page', __("Login"), $content);
	}
}

function user_logout() {
	unset($GLOBALS['user']);
	setcookie('USER_AUTH', '', time() - 3600, '/');
}

function user_is_authenticated() {
	if (!isset($GLOBALS['user'])) {
		if(array_key_exists('USER_AUTH', $_COOKIE)) {
			_user_decrypt_cookie($_COOKIE['USER_AUTH']);
		} else {
			$GLOBALS['user'] = array();
		}
	}

	if (user_current_username() && user_type() !== 'oauth') {
		user_logout();
		twitter_refresh('logout');
	}

	if (!user_current_username()) return false;
	return true;
}

function user_current_username() {
	return $GLOBALS['user']['username'];
}

function user_is_current_user($username) {
	return (strcasecmp($username, user_current_username()) == 0);
}

function user_type() {
	return $GLOBALS['user']['type'];
}

function _is_user_invited($username) {
	$allowed_users = file(dirname(dirname(__FILE__)).'/invited');

	if (in_array(strtolower($username)."\n", $allowed_users)) {
		return TRUE;
	}
	
	return FALSE;
}

function _user_save_cookie() {
	$cookie = _user_encrypt_cookie();
	$duration = time() + (3600 * 24 * 365);
	setcookie('USER_AUTH', $cookie, $duration, '/');
}

function _user_encryption_key() {
	return ENCRYPTION_KEY;
}

function _user_encrypt_cookie() {
	$plain_text = $GLOBALS['user']['username'] . ':' . $GLOBALS['user']['password'] . ':' . $GLOBALS['user']['type'];
	if (function_exists('mcrypt_module_open')) {
		$td = mcrypt_module_open('blowfish', '', 'cfb', '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, _user_encryption_key(), $iv);
		$crypt_text = mcrypt_generic($td, $plain_text);
		mcrypt_generic_deinit($td);
		return base64_encode($iv.$crypt_text);
	} else {
		$crypt_text = xxtea_encrypt($plain_text,_user_encryption_key());
		return base64_encode($crypt_text);
	}
}

function _user_decrypt_cookie($crypt_text) {
	$crypt_text = base64_decode($crypt_text);
	if(function_exists('mcrypt_module_open')) {
		$td = mcrypt_module_open('blowfish', '', 'cfb', '');
		$ivsize = mcrypt_enc_get_iv_size($td);
		$iv = substr($crypt_text, 0, $ivsize);
		$crypt_text = substr($crypt_text, $ivsize);
		mcrypt_generic_init($td, _user_encryption_key(), $iv);
		$plain_text = mdecrypt_generic($td, $crypt_text);
		mcrypt_generic_deinit($td);
	} else {
		$plain_text = xxtea_decrypt($crypt_text,_user_encryption_key());
	}
	list($GLOBALS['user']['username'], $GLOBALS['user']['password'], $GLOBALS['user']['type']) = explode(':', $plain_text);
}

function theme_login() {
	$content = '<p>　→ <strong><a href="'.RELATIVE_URL.'oauth">'.__("Sign In with Twitter OAuth").'</a></strong> / <a href="'.RELATIVE_URL.'itap">'.__("Proxy").'</a></p>';
	return $content;
}

function theme_logged_out() {
	return '<p>'.__("Logged out").'. <a href="'.RELATIVE_URL.'">'.__("Login again").'?</a></p>';
}
