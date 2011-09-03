<?php
function twitter_get_media($status) {
	if ($status->entities->media) {
		$image = $status->entities->media[0]->media_url;

		$media_html = "<a href=\"".$image."\"><img src=\"";

		if (IMGPROXY == 1) {
			$media_html .= BASE_URL."img.php?u=".base64_encode(base64_encode($image));
		} else {
			$media_html .= $image;
		}

		$media_html .= "\" width=\"{$status->entities->media[0]->sizes->thumb->w}\" height=\"{$status->entities->media[0]->sizes->thumb->h}\" /></a><br />";

		return $media_html;
	}
}

function embedly_embed_thumbnails(&$feed) {
	// Find URLs throughout the $feed, noting the tweet IDs they occur in
	$matched_urls = array();
	$embedly_re = "/http:\/\/(.*yfrog\..*\/.*|tweetphoto\.com\/.*|www\.flickr\.com\/photos\/.*|flic\.kr\/.*|twitpic\.com\/.*|www\.twitpic\.com\/.*|twitpic\.com\/photos\/.*|www\.twitpic\.com\/photos\/.*|.*imgur\.com\/.*|.*\.posterous\.com\/.*|post\.ly\/.*|twitgoo\.com\/.*|i.*\.photobucket\.com\/albums\/.*|s.*\.photobucket\.com\/albums\/.*|phodroid\.com\/.*\/.*\/.*|www\.mobypicture\.com\/user\/.*\/view\/.*|moby\.to\/.*|xkcd\.com\/.*|www\.xkcd\.com\/.*|imgs\.xkcd\.com\/.*|www\.asofterworld\.com\/index\.php\?id=.*|www\.asofterworld\.com\/.*\.jpg|asofterworld\.com\/.*\.jpg|www\.qwantz\.com\/index\.php\?comic=.*|23hq\.com\/.*\/photo\/.*|www\.23hq\.com\/.*\/photo\/.*|.*dribbble\.com\/shots\/.*|drbl\.in\/.*|.*\.smugmug\.com\/.*|.*\.smugmug\.com\/.*#.*|emberapp\.com\/.*\/images\/.*|emberapp\.com\/.*\/images\/.*\/sizes\/.*|emberapp\.com\/.*\/collections\/.*\/.*|emberapp\.com\/.*\/categories\/.*\/.*\/.*|embr\.it\/.*|picasaweb\.google\.com.*\/.*\/.*#.*|picasaweb\.google\.com.*\/lh\/photo\/.*|picasaweb\.google\.com.*\/.*\/.*|dailybooth\.com\/.*\/.*|brizzly\.com\/pic\/.*|pics\.brizzly\.com\/.*\.jpg|img\.ly\/.*|www\.tinypic\.com\/view\.php.*|tinypic\.com\/view\.php.*|www\.tinypic\.com\/player\.php.*|tinypic\.com\/player\.php.*|www\.tinypic\.com\/r\/.*\/.*|tinypic\.com\/r\/.*\/.*|.*\.tinypic\.com\/.*\.jpg|.*\.tinypic\.com\/.*\.png|meadd\.com\/.*\/.*|meadd\.com\/.*|.*\.deviantart\.com\/art\/.*|.*\.deviantart\.com\/gallery\/.*|.*\.deviantart\.com\/#\/.*|fav\.me\/.*|.*\.deviantart\.com|.*\.deviantart\.com\/gallery|.*\.deviantart\.com\/.*\/.*\.jpg|.*\.deviantart\.com\/.*\/.*\.gif|.*\.deviantart\.net\/.*\/.*\.jpg|.*\.deviantart\.net\/.*\/.*\.gif|plixi\.com\/p\/.*|plixi\.com\/profile\/home\/.*|plixi\.com\/.*|www\.fotopedia\.com\/.*\/.*|fotopedia\.com\/.*\/.*|photozou\.jp\/photo\/show\/.*\/.*|photozou\.jp\/photo\/photo_only\/.*\/.*|instagr\.am\/p\/.*|skitch\.com\/.*\/.*\/.*|img\.skitch\.com\/.*|https:\/\/skitch\.com\/.*\/.*\/.*|https:\/\/img\.skitch\.com\/.*|share\.ovi\.com\/media\/.*\/.*|www\.questionablecontent\.net\/|questionablecontent\.net\/|www\.questionablecontent\.net\/view\.php.*|questionablecontent\.net\/view\.php.*|questionablecontent\.net\/comics\/.*\.png|www\.questionablecontent\.net\/comics\/.*\.png|picplz\.com\/user\/.*\/pic\/.*\/|twitrpix\.com\/.*|.*\.twitrpix\.com\/.*|www\.someecards\.com\/.*\/.*|someecards\.com\/.*\/.*|some\.ly\/.*|www\.some\.ly\/.*|pikchur\.com\/.*|achewood\.com\/.*|www\.achewood\.com\/.*|achewood\.com\/index\.php.*|www\.achewood\.com\/index\.php.*)/i";

	foreach ($feed as &$status) { // Loop through the feed
		if ($status->entities) { // If there are entities
			$entities = $status->entities;

			foreach ($entities->urls as $urls) {	// Loop through the URL entities
				if ($urls->expanded_url != "") { // Use the expanded URL, if it exists, to pass to Embedly
					if (preg_match($embedly_re, $urls->expanded_url) > 0) { // If it matches an Embedly supported URL
						$matched_urls[$urls->expanded_url][] = $status->id;
					}
				}
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
				$feed[$statusId]->text .= "<br /><a href=\"$url\"><img src=\"";

				if (IMGPROXY == 1) {
					$feed[$statusId]->text .= BASE_URL."img.php?u=".base64_encode(base64_encode($thumb));
				} else {
					$feed[$statusId]->text .= $thumb;
				}

				$feed[$statusId]->text .= "\" /></a>";
			}
		}
	}
}
