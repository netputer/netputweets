<?php
	if (IMGPROXY && isset($_COOKIE["USER_AUTH"]) && isset($_GET["u"])) {
		$url = base64_decode(base64_decode($_GET["u"]));

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$ret = curl_exec($ch);
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
?>