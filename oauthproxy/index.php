<?php
	/*
	 * This work is licensed under the Creative Commons Attribution-Share Alike 2.5 China Mainland License.
	 * To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/2.5/cn/ or
	 * send a letter to Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
	 *
	 * @name: iTAP
	 * @author: Aveline Lan (Twitter @LonelySwan) (12@34.la / i@vii.im)
	 * @website: http://vii.im
	 * @version: r8 2010-06-25 12:02
	 *
	 * The login page is modified from NetPutter
	 */

	error_reporting(0);


	define('OAUTH_URL','https://twitter.com/oauth/');
	define('API_URL','https://api.twitter.com/');
	define('ITAP_VERSION','r8 - 20100816 - Valentine\'s Day');

	$allowed_method = array(
		'authenticate_post','authenticate','authorize','authorize_post'
	);

	$method = substr($_SERVER['REQUEST_URI'],strripos($_SERVER['REQUEST_URI'],'/')+1);

	if(strpos($method,'?')!=false) {
		$method = substr($method,0,strpos($method,'?'));
	}


	$query = '?'.(isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING']);


	require_once './handler.php';

	$a = new OAPHandle;
		//$a->keepCookie();
		if($method=='authorize') {
			$a->auth_show();
		} elseif($method=='authorize_post') {
			$a->auth_post();
		} elseif($method=='authenticate_post') {
			$a->auth_post_l();
		} elseif($method=='authenticate') {
			$a->auth_show_l();
		} elseif($method=='') {
			$a->test_connection();
		} else {
			exit('Invalid Method `'.htmlspecialchars($method).'`.');
		}
?>
