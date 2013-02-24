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

	class OAPHandle {
		private $data = array(
			'cookie_file' => '',
			'keepCookie' => false,
		);

		protected function _cookie() {
			if(!$this->data['cookie_file']) {
			$this->data['cookie_file'] = getcwd().'/cache/c.'.MD5(time().microtime().mt_rand(0,6530)).'.txt';
			}
			@file_put_contents($this->data['cookie_file'],'');
			return $this->data['cookie_file'];
		}

		public function _GET($url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookie());
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookie());
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}

		public function _POST($url,$data) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookie());
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookie());
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}
		protected function WoahThere($response) {
			if(strpos($response,'Woah there!')) {
				exit('This page is no longer valid. It looks like someone already used the token information you provided. Please return to the site that sent you to this page and try again, it was probably an honest mistake.');
			}
		}
		public function auth_show() {
			global $query;
			$response = $this->_GET(OAUTH_URL.'authorize'.$query);
			$this->WoahThere($response);
			// get application data
			preg_match('/ class\=\"app-icon\" src\=\"(.*?)\" \/\>\<\/a\>/i',$response,$ms);
				list(,$app_icon) = $ms;
			preg_match('/<h4 style\=\"font\-weight\:normal\;\"\>The application \<strong\>(.*?)\<\/strong\> by \<strong\>(.*?)<\/strong\> wou/i',$response,$ms);
				list(,$app_name,$app_provider) = $ms;
			// ..被正则烦死了,用strpos吧
				$at_pos = strpos($response,'"authenticity_token" type="hidden" value="') + 42;
				$ata_ct = substr($response,$at_pos);
				$at_value = substr($ata_ct,0,strpos($ata_ct,'"'));

				$ot_pos = strpos($ata_ct,'<input id="oauth_token" name="oauth_token" type="hidden" value="') + 64;
				$ota_ct = substr($ata_ct,$ot_pos);
				$ot_value = substr($ota_ct,0,strpos($ota_ct,'"'));

			unset($ot_pos,$at_pos,$ata_ct,$ota_ct,$ms);
			require 'login.php';
			}

		public function auth_show_l() {
			global $query;
			$response = $this->_GET(OAUTH_URL.'authenticate'.$query);
			$this->WoahThere($response);
			// get application data
			preg_match('/ class\=\"app-icon\" src\=\"(.*?)\" \/\>\<\/a\>/i',$response,$ms);
				list(,$app_icon) = $ms;
			preg_match('/<h4 style\=\"font\-weight\:normal\;\"\>The application \<strong\>(.*?)\<\/strong\> by \<strong\>(.*?)<\/strong\> wou/i',$response,$ms);
				list(,$app_name,$app_provider) = $ms;
			// ..被正则烦死了,用strpos吧
				$at_pos = strpos($response,'"authenticity_token" type="hidden" value="') + 42;
				$ata_ct = substr($response,$at_pos);
				$at_value = substr($ata_ct,0,strpos($ata_ct,'"'));

				$ot_pos = strpos($ata_ct,'<input id="oauth_token" name="oauth_token" type="hidden" value="') + 64;
				$ota_ct = substr($ata_ct,$ot_pos);
				$ot_value = substr($ota_ct,0,strpos($ota_ct,'"'));

			unset($ot_pos,$at_pos,$ata_ct,$ota_ct,$ms);
			require 'login.php';
		}

		public function auth_post() {
			global $_POST;
			$response = $this->_POST(OAUTH_URL.'authorize?oauth_token='.$_POST['ot'],array(
				'authenticity_token'			=> $_POST['at'],
				'oauth_token'				=> $_POST['ot'],

				'session[username_or_email]'	=> $_POST['username'],
				'session[password]'			=> $_POST['password'],
			));
			preg_match('/please \<a href\=\"(.*?)\"\>click here\<\/a\>/i',$response,$ms);
			$this->WoahThere($response);
			if(strpos($response,'Invalid user name or password')) {
				$this->invaid_password();
			} elseif(!$ms) {
				echo $response;
				exit;
			}
			header("Location: {$ms[1]}");
		}

		public function auth_post_l() {
			global $_POST;
			$response = $this->_POST(OAUTH_URL.'authenticate?oauth_token='.$_POST['ot'],array(
				'authenticity_token'			=> $_POST['at'],
				'oauth_token'				=> $_POST['ot'],

				'session[username_or_email]'	=> $_POST['username'],
				'session[password]'			=> $_POST['password'],
			));
			preg_match('/please \<a href\=\"(.*?)\"\>click here\<\/a\>/i',$response,$ms);
			$this->WoahThere($response);
			if(strpos($response,'Invalid user name or password')) {
				$this->invaid_password();
			} elseif(!$ms) {
				echo $response;
				exit;
			}
			header("Location: {$ms[1]}");
		}

		public function __destruct() {
			if(!$this->data['keepCookie']) {
				@unlink($this->_cookie());
			}
		}

		public function keepCookie() {
			$this->data['keepCookie'] = true;
		}

		protected function invaid_password() {
			if(isset($_SERVER['HTTP_REFERER'])) {
				header("Location: {$_SERVER['HTTP_REFERER']}&p=true");
			} else {
				echo('Invaid Username or Password.');
			}
			exit;
		}
	}
