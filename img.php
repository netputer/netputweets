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

		if ($httpcode != '200') exit; // 图片不存在
		
		$header = substr($ret, 0, $hsize);
		$pat = "/(Content-Type:\s?image\/\w+)/i";

		if (!preg_match_all($pat, $header, $m)) exit; // 非图片
		
		header($m[0][0]);
		$ret = substr($ret, $hsize);
		
		// 若存在缩略标识且图片宽度超过150则输出缩略图
		if (isset($_GET['t'])) {
			$photo = imagecreatefromstring($ret);
			
			$photo_w = ImageSX($photo);
			$photo_h = ImageSY($photo);
			
			if ($photo_w > 150) {
				$new_w = 150;
				$new_h = floor($photo_h / ($photo_w / 150));

				$thumb = imagecreatetruecolor($new_w, $new_h); // 创建缩略图的画布
				imagealphablending($thumb, false); // 关闭混色模式
				$color = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
				imagefill($thumb, 0, 0, $color); // 获取透明色并填充
				imagesavealpha($thumb, true); // 保存缩略图时保留完整的 alpha 通道信息
				imagecopyresampled($thumb, $photo, 0, 0, 0, 0, $new_w, $new_h, $photo_w, $photo_h); // 进行缩放

				$ret = imagejpeg($thumb); // 输出缩略图

				imagedestroy($thumb);
			}
			
			imagedestroy($photo);
		}

		echo $ret;
	}
