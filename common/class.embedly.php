<?php
function img_proxy_url($url, $thumb = FALSE) {
	if (!IMGPROXY) return $url;

	$img_url = BASE_URL.'img.php?u='.base64_encode(strrev($url));
	if ($thumb) $img_url .= '&t=150';

	return $img_url;
}

function embedly_embed_thumbnails(&$feed) {
	$matched_urls = array();
	$embedly_re = '/(www\.flickr\.com\/photos\/.*|flic\.kr\/.*|www\.mobypicture\.com\/user\/.*\/view\/.*|moby\.to\/.*|.*imgur\.com\/.*|.*\.posterous\.com\/.*|post\.ly\/.*|i.*\.photobucket\.com\/albums\/.*|s.*\.photobucket\.com\/albums\/.*|phodroid\.com\/.*\/.*\/.*|xkcd\.com\/.*|www\.xkcd\.com\/.*|imgs\.xkcd\.com\/.*|www\.asofterworld\.com\/index\.php\?id=.*|www\.asofterworld\.com\/.*\.jpg|asofterworld\.com\/.*\.jpg|www\.qwantz\.com\/index\.php\?comic=.*|23hq\.com\/.*\/photo\/.*|www\.23hq\.com\/.*\/photo\/.*|.*dribbble\.com\/shots\/.*|drbl\.in\/.*|.*\.smugmug\.com\/.*|.*\.smugmug\.com\/.*#.*|emberapp\.com\/.*\/images\/.*|emberapp\.com\/.*\/images\/.*\/sizes\/.*|emberapp\.com\/.*\/collections\/.*\/.*|emberapp\.com\/.*\/categories\/.*\/.*\/.*|embr\.it\/.*|picasaweb\.google\.com.*\/.*\/.*#.*|picasaweb\.google\.com.*\/lh\/photo\/.*|picasaweb\.google\.com.*\/.*\/.*|dailybooth\.com\/.*\/.*|brizzly\.com\/pic\/.*|pics\.brizzly\.com\/.*\.jpg|www\.tinypic\.com\/view\.php.*|tinypic\.com\/view\.php.*|www\.tinypic\.com\/player\.php.*|tinypic\.com\/player\.php.*|www\.tinypic\.com\/r\/.*\/.*|tinypic\.com\/r\/.*\/.*|.*\.tinypic\.com\/.*\.jpg|.*\.tinypic\.com\/.*\.png|meadd\.com\/.*\/.*|meadd\.com\/.*|.*\.deviantart\.com\/art\/.*|.*\.deviantart\.com\/gallery\/.*|.*\.deviantart\.com\/#\/.*|fav\.me\/.*|.*\.deviantart\.com|.*\.deviantart\.com\/gallery|.*\.deviantart\.com\/.*\/.*\.jpg|.*\.deviantart\.com\/.*\/.*\.gif|.*\.deviantart\.net\/.*\/.*\.jpg|.*\.deviantart\.net\/.*\/.*\.gif|plixi\.com\/p\/.*|plixi\.com\/profile\/home\/.*|plixi\.com\/.*|www\.fotopedia\.com\/.*\/.*|fotopedia\.com\/.*\/.*|photozou\.jp\/photo\/show\/.*\/.*|photozou\.jp\/photo\/photo_only\/.*\/.*|skitch\.com\/.*\/.*\/.*|img\.skitch\.com\/.*|https:\/\/skitch\.com\/.*\/.*\/.*|https:\/\/img\.skitch\.com\/.*|share\.ovi\.com\/media\/.*\/.*|www\.questionablecontent\.net\/|questionablecontent\.net\/|www\.questionablecontent\.net\/view\.php.*|questionablecontent\.net\/view\.php.*|questionablecontent\.net\/comics\/.*\.png|www\.questionablecontent\.net\/comics\/.*\.png|twitrpix\.com\/.*|.*\.twitrpix\.com\/.*|www\.someecards\.com\/.*\/.*|someecards\.com\/.*\/.*|some\.ly\/.*|www\.some\.ly\/.*|pikchur\.com\/.*|achewood\.com\/.*|www\.achewood\.com\/.*|achewood\.com\/index\.php.*|www\.achewood\.com\/index\.php.*)/i';

	$services = array(
		'#twitpic\.com\/([\d\w]+)#i' => 'http://twitpic.com/show/thumb/%s',
		'#twitgoo\.com\/([\d\w]+)#i' => 'http://twitgoo.com/show/thumb/%s',
		'#tweetphoto\.com\/(\d+)#' => 'http://api.plixi.com/api/tpapi.svc/imagefromurl?url=http://tweetphoto.com/%s',
		'#img\.ly\/([\w\d]+)#i' => 'http://img.ly/show/thumb/%s',
		'#picplz\.com\/([\d\w\.]+)#' => 'http://picplz.com/%s/thumb',
		'#yfrog\.com\/([\d\w]+)#' => 'http://yfrog.com/%s:small',
		'#instagr\.am\/p\/([_-\d\w]+)#i' => 'http://instagr.am/p/%s/media/?size=t',
		'#instagram\.com\/p\/([_-\d\w]+)#i' => 'http://instagr.am/p/%s/media/?size=t',
	);

	foreach ($feed as &$status) {
		if ($status->entities) {
			if ($status->entities->urls) {
				foreach($status->entities->urls as $urls) {
					if (preg_match($embedly_re, $urls->expanded_url) > 0) { // If it matches an Embedly supported URL
						$matched_urls[urlencode($urls->expanded_url)][] = $status->id;
					} elseif (preg_match("/.*\.(jpg|png|gif)/i", $urls->expanded_url)) {
						$feed[$status->id]->text .= '<br /><a href="{$urls->expanded_url}"><img src="'.img_proxy_url($urls->expanded_url, TRUE).'" style="max-width:150px;" /></a>';
					} else {
						foreach ($services as $pattern => $thumbnail_url) {
							if (preg_match_all($pattern, $urls->expanded_url, $matches, PREG_PATTERN_ORDER) > 0) {
								foreach ($matches[1] as $key => $match) {
									$feed[$status->id]->text .= '<br /><a href="'.$urls->expanded_url.'"><img src="'.img_proxy_url(sprintf($thumbnail_url, $match)).'" style="max-width:150px;" /></a>';
								}
							}
						}
					}
				}
			}

			if ($status->entities->media) {
				$image = substr(BASE_URL, 4, 5) == 's' ? $status->entities->media[0]->media_url_https : $status->entities->media[0]->media_url;

				$feed[$status->id]->text .= '<br /><a href="'.$image.'"><img src="'.img_proxy_url($image, TRUE).'" style="max-width:150px;" /></a>';
			}
		}
	}

	// Make a single API call to Embedly.
	$justUrls = array_keys($matched_urls);
	$count = count($justUrls);

	if ($count == 0) return;
	if ($count > 20) {
		// Embedly has a limit of 20 URLs processed at a time. Not ideal for @dabr, but fair enough to ignore images after that.
		$justUrls = array_chunk($justUrls, 20);
		$justUrls = $justUrls[0];
	}

	$url = 'http://api.embed.ly/1/oembed?key='.EMBEDLY_KEY.'&urls=' . implode(',', $justUrls) . '&format=json';

	$embedly_json = twitter_fetch($url);
	$oembeds = json_decode($embedly_json);

	// Put the thumbnails into the $feed
	foreach ($justUrls as $index => $url) {
		if ($thumb = $oembeds[$index]->thumbnail_url) {
			foreach ($matched_urls[$url] as $statusId) {
				$feed[$statusId]->text .= '<br /><a href="'.$url.'"><img src="'.img_proxy_url($thumb).'" style="max-width:150px;" /></a>';
			}
		}
	}
}
