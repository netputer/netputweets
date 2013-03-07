<?php
	require('config.php');

	function curl_redirect_exec($ch) {
		if (ini_get('open_basedir') == '') {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			return curl_exec($ch);
		}

		$data = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 301 || $http_code == 302) {
			list($header) = explode("\r\n\r\n", $data, 2);
			$matches = array();

			preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);

			$url = trim(str_replace($matches[1], "", $matches[0]));

			if (is_array(parse_url($url))) {
				curl_setopt($ch, CURLOPT_URL, $url);
				return curl_redirect_exec($ch);
			}
		}

		return $data;
	}

	if (IMGPROXY && isset($_COOKIE["USER_AUTH"]) && isset($_GET["u"])) {
		$url = strrev(base64_decode($_GET["u"]));

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		$ret = curl_redirect_exec($ch);

		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);

		if ($httpcode != '200') exit; // 图片不存在

		$header = substr($ret, 0, $hsize);
		$pat = "/(Content-Type:\s?image\/(\w+))/i";

		if (!preg_match_all($pat, $header, $m)) exit; // 非图片

		header($m[0][0]);
		$ret = substr($ret, $hsize);

		// 若存在缩略标识且图片宽度超过150则输出缩略图
		if (isset($_GET['t']) && IMGTHUMB && function_exists('gd_info')) {
			$new_w = is_numeric($_GET['t']) ? intval($_GET['t']) : 150; // 缩略图宽度

			$photo = imagecreatefromstring($ret);

			$photo_w = ImageSX($photo);
			$photo_h = ImageSY($photo);

			if ($photo_w > $new_w) {
				$new_h = floor($photo_h / ($photo_w / $new_w));

				$thumb = imagecreateTRUEcolor($new_w, $new_h); // 创建缩略图的画布
				imagealphablending($thumb, FALSE); // 关闭混色模式
				$color = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
				imagefill($thumb, 0, 0, $color); // 获取透明色并填充
				imagesavealpha($thumb, TRUE); // 保存缩略图时保留完整的 alpha 通道信息
				imagecopyresampled($thumb, $photo, 0, 0, 0, 0, $new_w, $new_h, $photo_w, $photo_h); // 进行缩放

				switch ($m[2][0]) {
					// 根据 header 输出特定格式的缩略图
					case 'png':
						$ret = imagepng($thumb);
						break;
					case 'gif':
						$ret = imagegif($thumb);
						break;
					default:
						$ret = imagejpeg($thumb);
				}

				imagedestroy($thumb);
			}

			imagedestroy($photo);
		}

		echo $ret;
	}
