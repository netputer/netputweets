<?php
	require('config.php');
	
	function curl_redirect_exec($ch) {
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$data = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if ($http_code == 301 || $http_code == 302) {
			list($header) = explode("\r\n\r\n", $data, 2);
			$matches = array();
			//this part has been changes from the original
			preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
			$url = trim(str_replace($matches[1],"",$matches[0]));
			//end changes
			$url_parsed = parse_url($url);
			if (isset($url_parsed)) {
				curl_setopt($ch, CURLOPT_URL, $url);
				return curl_redirect_exec($ch);
			}
		}
		
		return $data;
	}

	if (IMGPROXY && isset($_COOKIE["USER_AUTH"]) && isset($_GET["u"])) {
		$url = strrev(base64_decode($_GET["u"]));

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$ret = curl_redirect_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);

		if ($httpcode == "200") {
			$header = substr($ret, 0, $hsize);
			$pat = "/(Content-Type:\s?image\/\w+)/i";

			if (preg_match_all($pat, $header, $m)) {
				$header = $m[0][0];
				$ret = substr($ret,$hsize);
				header($header);
				echo $ret;
			}
		}
	}
