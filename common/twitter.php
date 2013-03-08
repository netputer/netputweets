<?php
date_default_timezone_set("Asia/Shanghai");

require 'class.autolink.php';
require 'class.extractor.php';
require 'lists.php';
require 'class.embedly.php';

menu_register(array(
	'' => array(
		'callback' => 'twitter_home_page',
		'accesskey' => '0',
	),
	'status' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_status_page',
	),
	'update' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_update',
	),
	'replies' => array(
		'security' => true,
		'callback' => 'twitter_replies_page',
		'accesskey' => '1',
		'title' => __("Replies"),
	),
	'twitter-retweet' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweet',
	),
	'favourite' => array(
		'hidden'=> true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'unfavourite' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'directs' => array(
		'security' => true,
		'callback' => 'twitter_directs_page',
		'accesskey' => '3',
		'title' => __("Directs"),
	),
	'search' => array(
		'security' => true,
		'callback' => 'twitter_search_page',
		'accesskey' => '4',
		'title' => __("Search"),
	),
	'user' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_user_page',
	),
	'follow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'unfollow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'confirm' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_confirmation_page',
	),
	'block' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'unblock' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'favourites' => array(
		'security' => true,
		'callback' =>	'twitter_favourites_page',
		'title' => __("Favourites"),
	),
	'followers' => array(
		'security' => true,
		'callback' => 'twitter_followers_page',
		'title' => __("Followers"),
	),
	'friends' => array(
		'security' => true,
		'callback' => 'twitter_friends_page',
		'title' => __("Friends"),
	),
	'blockings' => array(
		'security' => true,
		'security' => true,
		'callback' => 'twitter_blockings_page',
		'title' => __("Blockings"),
	),
	'delete' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_delete_page',
	),
	'retweet' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweet_page',
	),
	'hash' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_hashtag_page',
	),
	'upload' => array(
		'security' => true,
		'callback' => 'twitter_upload_page',
		'title' => __("Upload Picture"),
	),
	'profile' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_profile_page',
	),
	'lists' => array(
		'security' => true,
		'callback' => 'lists_controller',
		'title' => __("Lists"),
	),
	'spam' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_spam_page',
	),
));

$blackberry_pagination = NULL;

function friendship_exists($user_a) {
	$following = friendship($user_a);

	if ($following->relationship->target->following == 1) {
		return true;
	} else {
		return false;
	}
}

function friendship($user_a, $user_id = 0) {
	if ($user_a != NULL)
		$request = API_ROOT.'friendships/show.json?target_screen_name=' . $user_a;
	else
		$request = API_ROOT.'friendships/show.json?target_id=' . $user_id;
	return twitter_process($request);
}

function twitter_block_exists($query, $user_a = NULL) {
	if ($query > 0)
		$following = friendship(NULL, $query);
	else
		$following = friendship($user_a);

	if ($following->relationship->source->blocking == 1) {
		return true;
	} else {
		return false;
	}
}

function js_counter($name, $length='140') {
	$script = '<script type="text/javascript">
	function updateCount() {
		var remaining = ' . $length . ' - document.getElementById("' . $name . '").value.length;
		document.getElementById("remaining").innerHTML = remaining;
		if(remaining < 0) {
			var colour = "#FF0000";
			var weight = "bold";
		} else {
			var colour = "";
			var weight = "";
		}
		document.getElementById("remaining").style.color = colour;
		document.getElementById("remaining").style.fontWeight = weight;
		setTimeout(updateCount, 400);
	}
	updateCount();
</script>';
	return $script;
}

function twitter_upload_page($query) {
	$content = "";

	if ($_POST['message'] && $_FILES['image']['tmp_name']) {
		require 'class.upload.php';

		list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);

		$tmhOAuth = new tmhOAuth(array(
			'consumer_key'    => OAUTH_KEY,
			'consumer_secret' => OAUTH_SECRET,
			'user_token'      => $oauth_token,
			'user_secret'     => $oauth_token_secret,
		));

		$image = "{$_FILES['image']['tmp_name']};type={$_FILES['image']['type']};filename={$_FILES['image']['name']}";
		$status = $_POST['message'];
		
		$geo = 'N';
		if (setting_fetch('buttongeo') == 'yes') {
			list($lat, $long) = explode(',', $_POST['location']);
			if (is_numeric($lat) && is_numeric($long)) 
				$geo = 'Y';
			setcookie('geo', $geo, time()+3600);
		}

		if ($geo == 'Y')
			$code = $tmhOAuth->request('POST', API_ROOT.'statuses/update_with_media.json', array('media[]' => "@{$image}", 'status' => " ". $_POST['message'], 'lat' => $lat, 'long' => $long), true, true);
		else
			$code = $tmhOAuth->request('POST', API_ROOT.'statuses/update_with_media.json', array('media[]' => "@{$image}", 'status' => " ". $_POST['message']), true, true);

		if ($code == 200) {
			$content = "<p>".__("Upload success. Image posted to Twitter.")."</p>";

			return theme('page', __('Upload Picture'), $content);
		} else {
			$content = __("Damn! Something went wrong. Sorry :-(")
				."<br /> code=" . $code
				."<br /> status=" . $status
				."<br /> image=" . $image
				."<br /> response=<pre>"
				. print_r($tmhOAuth->response['response'], TRUE)
				. "</pre><br /> info=<pre>"
				. print_r($tmhOAuth->response['info'], TRUE)
				. "</pre><br /> code=<pre>"
				. print_r($tmhOAuth->response['code'], TRUE) . "</pre>";
		}
	}

	if ($_POST) {
		if (!$_POST['message']) {
			$content .= "<p>".__("Please enter a message to go with your image.")."</p>";
		}

		if (!$_FILES['image']['tmp_name']) {
			$content .= "<p>".__("Please select an image to upload.")."</p>";
		}
	}

	$content .=	"<form method='post' action='".RELATIVE_URL."upload' enctype='multipart/form-data'>
						".__("Image: ")."<input type='file' name='image' /><br />
						".__("Content: ")."<br />
						<textarea name='message' style='width:90%; max-width: 400px;' rows='3' id='message'>" . $_POST['message'] . "</textarea><br>
			";
	if (setting_fetch('buttongeo') == 'yes') {
		$content .= '
<span id="geo" style="display: inline;"><input onclick="goGeo()" type="checkbox" id="geoloc" name="location" /> <label for="geoloc" id="lblGeo"></label></span><br />
<script type="text/javascript">
<!--
started = false;
chkbox = document.getElementById("geoloc");
if (navigator.geolocation) {
	geoStatus("'.__("Tweet my location").'");
	if ("'.$_COOKIE['geo'].'"=="Y") {
		chkbox.checked = true;
		goGeo();
	}
}
function goGeo(node) {
	if (started) return;
	started = true;
	geoStatus("'.__("Locating...").'");
	navigator.geolocation.getCurrentPosition(geoSuccess, geoStatus, {enableHighAccuracy: true});
}
function geoStatus(msg) {
	document.getElementById("geo").style.display = "inline";
	document.getElementById("lblGeo").innerHTML = msg;
}
function geoSuccess(position) {
	if(typeof position.address !== "undefined")
		geoStatus("'.__("Tweet my ").'<a href=\'https://maps.google.com/maps?q=loc:" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>location</a>" + " (" + position.address.country + position.address.region + "省" + position.address.city + "市，'.__("accuracy: ").'" + position.coords.accuracy + "m)");
	else
		geoStatus("'.__("Tweet my ").'<a href=\'https://maps.google.com/maps?q=loc:" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>'.__("location").'</a>" + " ('.__("accuracy: ").'" + position.coords.accuracy + "m)");
	chkbox.value = position.coords.latitude + "," + position.coords.longitude;
}
//-->
</script>
';
        }
	$content .=     "
						<input type='submit' value='".__("Send")."'><span id='remaining'>120</span>
					</form>";
	$content .= js_counter("message", "120");

	return theme('page', __('Upload Picture'), $content);
}

function twitter_profile_page($query) {
	if ($_POST['name']) {
		$post_data = array(
			'name' => stripslashes($_POST['name']),
			'location' => $_POST['location'],
			'url' => $_POST['url'],
			'description' => $_POST['description'],
		);
		twitter_process(API_ROOT.'account/update_profile.json', $post_data);
		twitter_refresh('user/'.user_current_username());
	}

	$p = twitter_process(API_ROOT.'account/verify_credentials.json');
	$content = "<form method=\"post\" action=\"".RELATIVE_URL."profile\" enctype=\"multipart/form-data\">".__("Name: ")."<input type=\"text\" name=\"name\" value=\"{$p->name}\" /> (Max 20) <br />".__("Location: ")."<input type=\"text\" name=\"location\" value=\"{$p->location}\" /> (Max 30) <br />".__("Link: ")."<input type=\"text\" name=\"url\" value=\"{$p->url}\" /> (Max 100) <br />".__("Bio: ")."(Max 160) <br /><textarea name=\"description\" style=\"width:95%\" rows=\"3\" id=\"description\" >{$p->description}</textarea><br /><input type=\"submit\" value=\"".__("Update")."\" /></form>";

	return theme('page', __("Update Profile"), $content);
}

function twitter_process($url, $post_data = false, $original = false) {
	if ($post_data === true) $post_data = array();

	if (user_type() == 'oauth') {
		user_oauth_sign($url, $post_data);
	} elseif (is_array($post_data)) {
		$s = array();

		foreach ($post_data as $name => $value)
			$s[] = $name.'='.urlencode($value);
		$post_data = implode('&', $s);
	}

	$ch = curl_init($url);

	if ($post_data !== false && !$_GET['page']) {
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
	}

	if (user_type() != 'oauth' && user_is_authenticated()) curl_setopt($ch, CURLOPT_USERPWD, user_current_username().':'.$GLOBALS['user']['password']);

	curl_setopt($ch, CURLOPT_USERAGENT, 'dabr');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //15
	curl_setopt($ch, CURLOPT_TIMEOUT, 15); //30
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	$response = curl_exec($ch);
	$response_info=curl_getinfo($ch);
	$erno = curl_errno($ch);
	$er = curl_error($ch);
	curl_close($ch);

	if ($original) return json_decode($response);

	switch (intval( $response_info['http_code'])) {
		case 200:
		case 201:
			$json = json_decode($response);

			if ($json) return $json;

			return $response;

		case 401:
			user_logout();

			theme('error', "<p>".__("Error: Login credentials incorrect.")."</p><p>{$response_info['http_code']}: {$result}</p><hr><p>$url</p>");

		case 0:
			$result = $erno . ":" . $er . "<br />" ;

			theme("error", "<h3>".__("Twitter timed out")."</h3><p>".__("Dabr gave up on waiting for Twitter to respond. They're probably overloaded right now, try again in a minute.")."<br />$result</p>");

		default:
			$origin_result = json_decode($response);
			$result = $origin_result->error ? $origin_result->error : $response;

			if (strlen($result) > 500) {
				$result = __("Something broke on Twitter's end.");
			}

			theme('error', "<h3>".__("An error occured while calling the Twitter API")."</h3><p>{$response_info['http_code']}: {$result}</p><hr /><p>$url</p>");
	}
}

function twitter_fetch($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

function twitter_parse_tags($input, $entities = false, $id = false, $source = NULL) {
	// Filter.
	if ($id && substr($_GET["q"], 0, 6) !== "status" && (setting_fetch('filtero', 'no') == 'yes') && twitter_timeline_filter($input.' '.$source)) {
		return "<a href='".RELATIVE_URL."status/{$id}' style='text-decoration:none;'><small>[".__("Tweet Filtered")."]</small></a>";
	}

	// Linebreaks.  Some clients insert \n for formatting.
	$out = nl2br($input);

	// Use the Entities to replace hyperlink URLs
	if ($entities && $entities->urls) {
		foreach ($entities->urls as $urls) {
			if ($urls->expanded_url != "") {
				$display_url = $urls->expanded_url;
			} else {
				$display_url = $urls->url;
			}

			$url_detect = parse_url($display_url);

			if (isset($url_detect["scheme"])) {
				$link_html = theme('external_link', $display_url);

				$url = $urls->url;
				// Replace all URLs *UNLESS* they have already been linked (for example to an image)
				$pattern = '#((?<!href\=(\'|\"))'.preg_quote($url,'#').')#i';
				$out = preg_replace($pattern,  $link_html, $out);
			}
		}
	} else {  // If Entities haven't been returned, use Autolink
		// Create an array containing all URLs
		$urls = Twitter_Extractor::create($input)->extractURLs();

		// Hyperlink the URLs
		$out = Twitter_Autolink::create($out)->addLinksToURLs();

		// Hyperlink the #
		$out = Twitter_Autolink::create($out)->setTarget('')->addLinksToHashtags();
	}

	// Hyperlink the @ and lists
	$out = Twitter_Autolink::create($out)->setTarget('')->addLinksToUsernamesAndLists();

	// Hyperlink the #
	$out = Twitter_Autolink::create($out)->setTarget('')->addLinksToHashtags();

	//Return the completed string
	return $out;
}

function format_interval($timestamp, $granularity = 1) {
	$output = '';
	$units = array(
		__("years") => 31536000,
		__("days") => 86400,
		__("hours") => 3600,
		__("min") => 60,
		__("sec") => 1
	);

	foreach ($units as $key => $value) {
		if ($timestamp >= $value) {
			$output .= ($output ? ' ' : '').floor($timestamp / $value).' '.$key;
			$timestamp %= $value;
			$granularity--;
		}

		if ($granularity == 0) break;
	}
	return $output ? $output : __("0 sec");
}

function twitter_status_page($query) {
	$id = (string) $query[1];

	if (is_numeric($id)) {
		$thread = array();

		$request = API_ROOT."statuses/show.json?id={$id}&include_entities=true";
		$status = twitter_process($request);

		$content = theme('status', $status);

		for ($i=0;$i<3;$i++) {
			$reply_id = $status->in_reply_to_status_id_str;
			$reply_user = $status->in_reply_to_screen_name;

			if (!$reply_id) break;

			$request = API_ROOT."statuses/show.json?id={$reply_id}&include_entities=true";
			$status = twitter_process($request, false, true);

			if (isset($result->error)) {
				break;
			}

			$thread[] = $status;
		}

		if (!empty($thread)) {
			$tl = twitter_standard_timeline($thread, 'friends');
			$content .= theme('timeline', $tl);

			if ($reply_id) {
				$content .= "<p><a href=\"".RELATIVE_URL."status/$reply_id\">".__("Show previous conversations")." &raquo;</a></p>";
			}
		}

		theme('page', __("Status")." $id", $content);
	}
}

function twitter_retweet_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$request = API_ROOT."statuses/show.json?id={$id}&include_entities=true";
		$tl = twitter_process($request);
		$content = theme('retweet', $tl);
		theme('page', __("Retweet"), $content);
	}
}

function twitter_refresh($page = NULL) {
	if (isset($page)) {
		$page = RELATIVE_URL . $page;
	} else {
		$page = $_SERVER['HTTP_REFERER'];
	}
	header('Location: '. $page);
	exit();
}

function twitter_delete_page($query) {
	twitter_ensure_post_action();
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$request = API_ROOT."statuses/destroy/{$id}.json?page=".intval($_GET['page']);
		$tl = twitter_process($request, true);
		twitter_refresh('user/'.user_current_username());
	}
}

function twitter_ensure_post_action() {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Error: Invalid HTTP request method for this action.');
}

function twitter_spam_page($query) {
	twitter_ensure_post_action();
	$user = $query[1];
	$post_data = array("screen_name" => $user);
	$request = API_ROOT."users/report_spam.json";
	twitter_process($request, $post_data);
	twitter_refresh("user/{$user}");
}

function twitter_follow_page($query) {
	$user = $query[1];

	if ($user) {
		$post_data = array("screen_name" => $user);
		if($query[0] == 'follow'){
			$request = API_ROOT."friendships/create.json";
			$content = "<p>".__("Follow Success")."</p>";
		} else {
			$request = API_ROOT."friendships/destroy.json";
			$content = "<p>".__("Unfollow Success")."</p>";
		}

		twitter_process($request, $post_data);
		theme('page', __("User")." $user", $content);
	}
}

function twitter_block_page($query) {
	twitter_ensure_post_action();
	$user = $query[1];
	if ($user) {
		$post_data = array("screen_name" => $user);
		if($query[0] == 'block'){
			$request = API_ROOT."blocks/create.json";
		} else {
			$request = API_ROOT."blocks/destroy.json";
		}
		twitter_process($request, $post_data);
		twitter_refresh("user/{$user}");
	}
}

function twitter_confirmation_page($query) {
 	$action = $query[1];
 	$target = $query[2];
 	$target_id = $query[3];

	$realurl = "$action/$target";

	switch ($action) {
		case 'block':
			if (twitter_block_exists($target_id)) {
				$action = 'unblock';
				$content  = "<p>".__("Are you really sure you want to")." <strong>".__("Unblock")." $target</strong>?</p>";
				$content .= "<ul><li>".__("They will see your updates on their home page if they follow you again.")."</li><li>".__("You <em>can</em> block them again if you want.")."</li></ul>";
			} else {
				$content = "<p>".__("Are you really sure you want to")." <strong>$action $target</strong>?</p>";
				$content .= "<ul><li>".__("You won't show up in their list of friends")."</li><li>".__("They won't see your updates on their home page")."</li><li>".__("They won't be able to follow you")."</li><li>".__("You <em>can</em> unblock them but you will need to follow them again afterwards")."</li></ul>";
			}

			break;

		case 'delete':
			$request = API_ROOT."statuses/show.json?id=$target";
			$status = twitter_process($request);
			$parsed = $status->text;
			$content = "<p>".__("Are you really sure you want to")." ".__("delete your tweet?")."</p>";
			$content .= "<ul><li>".__("Tweet: ")."$parsed</li><li>".__("Note: ").__("There is no way to undo this action.")."</li></ul>";

			break;

		case 'listdelete':
			$listname = $target;
			$membername = $target_id;
			$content = "<p>".__("Are you really sure you want to").$membername.__(" removed from your ").$listname.__(" list?")."</p>";

			$realurl = "lists/".user_current_username()."/$listname/delete/$membername";
			break;

		case 'spam':
			$content  = "<p>".__("Are you really sure you want to")." ".__("report")." <strong>$target</strong> ".__("as a spammer?")."</p>";
			$content .= "<p>".__("They won't be able to follow you.")."</p>";
			break;
	}

	$content .= "<form action='".RELATIVE_URL.$realurl."' method='post'><input type='submit' value='".__("Yes")."' /></form>";

	theme('Page', __("Confirm"), $content);
}

function twitter_friends_page($query) {
	$user = $query[1];

	if (!$user) {
		user_ensure_authenticated();
		$user = user_current_username();
	}

	$cursor = $_GET['cursor'];
        if (!is_numeric($cursor)) {
                $cursor = -1;
        }
	$request = API_ROOT.'friends/list.json?screen_name='.$user.'&cursor='.$cursor;
	$tl = twitter_process($request);

	$content = theme('followers', $tl, 1);
	$content .= theme('list_pagination', $tl);

	theme('page', "{$user} ".__("'s Friends"), $content);
}

function twitter_followers_page($query) {
	$user = $query[1];

	if (!$user) {
		user_ensure_authenticated();
		$user = user_current_username();
	}

	$cursor = $_GET['cursor'];
        if (!is_numeric($cursor)) {
                $cursor = -1;
        }
	$request = API_ROOT.'followers/list.json?screen_name='.$user.'&cursor='.$cursor;
	$tl = twitter_process($request);

	$content = theme('followers', $tl, 1);
	$content .= theme('list_pagination', $tl);

	theme('page', "{$user} ".__("'s Followers"), $content);
}

function twitter_blockings_page($query) {
	user_ensure_authenticated();

	$cursor = $_GET['cursor'];
        if (!is_numeric($cursor)) {
                $cursor = -1;
        }
	$request = API_ROOT.'blocks/list.json?cursor='.$cursor;
	$tl = twitter_process($request);

	$content = theme('followers', $tl, 1);
	$content .= theme('list_pagination', $tl);

	theme('page', __("Blockings"), $content);
}

function twitter_update() {
	twitter_ensure_post_action();
	$status = trim($_POST['status']);

	if ($status) {
		if (function_exists(mb_strlen) && (mb_strlen($status, 'utf-8') > 140)) {
			if (setting_fetch('longtext', 'r') == 'a') {
					$status = mb_substr($status, 0, 140, 'utf-8');
			}
		}

		$request = API_ROOT.'statuses/update.json';
		$post_data = array('status' => $status);
		$in_reply_to_id = (string) $_POST['in_reply_to_id'];
		if (is_numeric($in_reply_to_id)) $post_data['in_reply_to_status_id'] = $in_reply_to_id;
		if (setting_fetch('buttongeo') == 'yes') {
			// Geolocation parameters
			list($lat, $long) = explode(',', $_POST['location']);
			$geo = 'N';
			if (is_numeric($lat) && is_numeric($long)) {
				$geo = 'Y';
				$post_data['lat'] = $lat;
				$post_data['long'] = $long;
			}
			setcookie('geo', $geo, time()+3600);
		}
		$b = twitter_process($request, $post_data);
	}

	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_retweet($query) {
	twitter_ensure_post_action();
	$id = $query[1];

	if (is_numeric($id)) {
		$request = API_ROOT.'statuses/retweet/'.$id.'.json';
		twitter_process($request, true);
	}

	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_replies_page() {
	$count = setting_fetch('tpp', 20);
	$request = API_ROOT."statuses/mentions_timeline.json?include_entities=true&count={$count}";

	if ($_GET['max_id']) $request .= "&max_id=".$_GET['max_id'];
	if ($_GET['since_id']) $request .= "&since_id=".$_GET['since_id'];

	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'replies');

	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', __("Replies"), $content);
}

function twitter_directs_page($query) {
	$action = strtolower(trim($query[1]));
	switch ($action) {

	case 'delete':
		$id = $query[2];
		if (!is_numeric($id)) return;
		$post_data = array("id" => $id);
		$request = API_ROOT."direct_messages/destroy.json";
		twitter_process($request, $post_data);
		twitter_refresh();

	case 'create':
		$to = $query[2];
		$content = theme('directs_form', $to);
		theme('page', __("Create DM")." $to", $content);

	case 'send':
		twitter_ensure_post_action();
		$to = trim(stripslashes($_POST['to']));
		$message = trim(stripslashes($_POST['message']));
		$request = API_ROOT.'direct_messages/new.json';
		twitter_process($request, array('screen_name' => $to, 'text' => $message));
		twitter_refresh('directs/sent');

	case 'sent':
		$count = setting_fetch('tpp', 20);
		$request = API_ROOT."direct_messages/sent.json?include_entities=true&count={$count}";

        	if ($_GET['max_id']) $request .= "&max_id=".$_GET['max_id'];
        	if ($_GET['since_id']) $request .= "&since_id=".$_GET['since_id'];

		$tl = twitter_standard_timeline(twitter_process($request), 'directs_sent');
		$content = theme_directs_menu();
		$content .= theme('timeline', $tl);
		theme('page', __("DM Sent"), $content);

	case 'inbox':
	default:
		$count = setting_fetch('tpp', 20);
		$request = API_ROOT."direct_messages.json?include_entities=true&count={$count}";

        	if ($_GET['max_id']) $request .= "&max_id=".$_GET['max_id'];
        	if ($_GET['since_id']) $request .= "&since_id=".$_GET['since_id'];

		$tl = twitter_standard_timeline(twitter_process($request), 'directs_inbox');
		$content = theme_directs_menu();
		$content .= theme('timeline', $tl);
		theme('page', __("DM Inbox"), $content);
	}
}

function theme_directs_menu() {
	return '<p><a href="'.RELATIVE_URL.'directs/create">'.__("Create DM").'</a> | <a href="'.RELATIVE_URL.'directs/inbox">'.__("DM Inbox").'</a> | <a href="'.RELATIVE_URL.'directs/sent">'.__("DM Sent").'</a></p>';
}

function theme_directs_form($to) {
	if ($to) {
		if (friendship_exists($to) != 1) {
			return "<p>$to ".__("is not following you. You cannot send direct message to that guy.")."</p>";
		} else {
			$html_to = __("Sending direct message to")." <b>$to</b><input name='to' value='$to' type='hidden'>";
		}
	} else {
		$html_to = __("To: ")."<input name='to'><br />".__("Content: ");
	}
	$content = "<form action='".RELATIVE_URL."directs/send' method='post'>$html_to<br /><textarea name='message' style='width:100%;max-width:400px;' rows='3' id='message'></textarea><br /><input type='submit' value='".__("Send")."'><span id='remaining'>140</span></form>";
	$content .= js_counter("message");
	return $content;
}

function twitter_search_page() {
	$search_query = $_GET['query'];
	$content = theme('search_form', $search_query);

	if (isset($_POST['query'])) {
		$duration = time() + (3600 * 24 * 365);
		setcookie('search_favourite', $_POST['query'], $duration, '/');
		twitter_refresh('search');
	}

	if (!isset($search_query) && array_key_exists('search_favourite', $_COOKIE)) $search_query = $_COOKIE['search_favourite'];

	if ($search_query) {
		$tl = twitter_search($search_query);

		if ($search_query !== $_COOKIE['search_favourite']) {
			$content .= '<form action="'.RELATIVE_URL.'search/bookmark" method="post"><input type="hidden" name="query" value="'.htmlspecialchars($search_query).'" /><input type="submit" value="'.__("Save as default search").'" /></form>';
		}

		$content .= theme('timeline', $tl);
	}

	theme('page', __("Search")." $search_query", $content);
}

function twitter_search($search_query) {
	$request = API_ROOT."search/tweets.json?include_entities=true&result_type=recent&q=$search_query";

	$max_id = (int) $_GET['max_id'];
	if ($max_id != 0) $request .= "&max_id=$max_id";

	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'search');
	return $tl;
}

function twitter_user_page($query) {
	if ($screen_name = $query[1]) {
		$to_users = array($screen_name);
	} else {
		theme('error', __('No username given'));
	}

	$in_reply_to_id = (string) $query[3];
	$content = '';

	if (is_numeric($in_reply_to_id)) {
		$str = __("Reply");

		$request = API_ROOT."statuses/show.json?id={$in_reply_to_id}&include_entities=true";
		$status = twitter_process($request);

		$user = $status->user;

		$content .= "<p>".__("In reply to")." <strong>$screen_name</strong>: {$status->text}</p>";

		if ($status->entities->user_mentions) {
			foreach ($status->entities->user_mentions as $mentions) {
				$to_users[] = $mentions->screen_name;
			}
		}
	} else {
		$str = __("User");

		$user = twitter_user_info($screen_name);
	}

	$status_content = '';

	if (count($to_users) == 1) {
		$status_content .= '@'.$to_users[0].' ';
	} else {
		foreach ($to_users as $username) {
			if (!user_is_current_user($username)) $status_content .= '@'.$username.' ';
		}
	}

	$content .= theme('status_form', $status_content, $in_reply_to_id, true);
	$content .= theme('user_header', $user);

	if ($in_reply_to_id == 0) {
		if (isset($user->status)) {
			$count = setting_fetch('tpp', 20);
			$request = API_ROOT."statuses/user_timeline.json?include_entities=true&screen_name={$screen_name}&include_rts=true&count={$count}";
        		if ($_GET['max_id']) $request .= "&max_id=".$_GET['max_id'];
        		if ($_GET['since_id']) $request .= "&since_id=".$_GET['since_id'];

			$tl = twitter_process($request);
			$tl = twitter_standard_timeline($tl, 'user');

			$content .= theme('timeline', $tl);
		}
	}

	theme('page', "$str $screen_name", $content);
}

function twitter_favourites_page($query) {
	$screen_name = $query[1];
	if (!$screen_name) {
		user_ensure_authenticated();
		$screen_name = user_current_username();
	}
	$count = setting_fetch('tpp', 20);
	$request = API_ROOT."favorites/list.json?screen_name={$screen_name}&include_entities=true&count={$count}";
        if ($_GET['max_id']) $request .= "&max_id=".$_GET['max_id'];
        if ($_GET['since_id']) $request .= "&since_id=".$_GET['since_id'];

	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'favourites');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', "{$screen_name} ".__("'s Favourites"), $content);
}

function twitter_mark_favourite_page($query) {
	$id = (string) $query[1];
	$post_data = array("id" => $id);

	if (!is_numeric($id)) return;

	if ($query[0] == 'unfavourite') {
		$request = API_ROOT."favorites/destroy.json";
		$content = "<p>".__("Unfavourite Success")."</p>";
	} else {
		$request = API_ROOT."favorites/create.json";
		$content = "<p>".__("Favourite Success")."</p>";
	}

	twitter_process($request, $post_data);
	theme('page', __("Favourites"), $content);
}

function twitter_home_page() {
	user_ensure_authenticated();
	$count = setting_fetch('tpp', 20);
	$request = API_ROOT."statuses/home_timeline.json?include_entities=true&count={$count}";

	if ($_GET['max_id']) $request .= "&max_id=".$_GET['max_id'];
	if ($_GET['since_id']) $request .= "&since_id=".$_GET['since_id'];

	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'friends');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', __("Home").$page, $content);
}

function twitter_hashtag_page($query) {
	if (isset($query[1])) {
		$hashtag = '#'.$query[1];
		$content = theme('status_form', $hashtag.' ');
		$tl = twitter_search($hashtag);
		$content .= theme('timeline', $tl);
		theme('page', $hashtag, $content);
	} else {
		theme('page', __("Hashtag"), 'Hash hash!');
	}
}

function theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		$fixedtags = ((setting_fetch('fixedtago', 'no') == "yes") && ($text == '')) ? " #".setting_fetch('fixedtagc') : null;
		$output = '<form method="post" action="'.RELATIVE_URL.'update"><textarea id="status" name="status" rows="3" style="width:100%; max-width: 400px;">'.$text.$fixedtags.'</textarea>';
		if (setting_fetch('buttongeo') == 'yes') {
			$output .= '
<br /><span id="geo" style="display: inline;"><input onclick="goGeo()" type="checkbox" id="geoloc" name="location" /> <label for="geoloc" id="lblGeo"></label></span>
<script type="text/javascript">
<!--
started = false;
chkbox = document.getElementById("geoloc");
if (navigator.geolocation) {
	geoStatus("'.__("Tweet my location").'");
	if ("'.$_COOKIE['geo'].'"=="Y") {
		chkbox.checked = true;
		goGeo();
	}
}
function goGeo(node) {
	if (started) return;
	started = true;
	geoStatus("'.__("Locating...").'");
	navigator.geolocation.getCurrentPosition(geoSuccess, geoStatus, {enableHighAccuracy: true});
}
function geoStatus(msg) {
	document.getElementById("geo").style.display = "inline";
	document.getElementById("lblGeo").innerHTML = msg;
}
function geoSuccess(position) {
	if(typeof position.address !== "undefined")
		geoStatus("'.__("Tweet my ").'<a href=\'https://maps.google.com/maps?q=loc:" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>location</a>" + " (" + position.address.country + position.address.region + "省" + position.address.city + "市，'.__("accuracy: ").'" + position.coords.accuracy + "m)");
	else
		geoStatus("'.__("Tweet my ").'<a href=\'https://maps.google.com/maps?q=loc:" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>'.__("location").'</a>" + " ('.__("accuracy: ").'" + position.coords.accuracy + "m)");
	chkbox.value = position.coords.latitude + "," + position.coords.longitude;
}
//-->
</script>
';
        	}
		$output .= '<div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="'.__('Update').'" />';

		if (substr($_GET["q"], 0, 4) !== "user") {
			$output .= ' <a href="'.RELATIVE_URL.'upload">'.__('Upload Picture').'</a>';
		}

		$output .= '</div></form>';

		return $output;
	}
}

function theme_status($status) {
	$feed[] = $status;
	$tl = twitter_standard_timeline($feed, 'status');
	$content = theme('timeline', $tl);
	return $content;
}

function theme_retweet($status) {
	$text = "RT @{$status->user->screen_name}: {$status->text}";
	$length = function_exists('mb_strlen') ? mb_strlen($text,'UTF-8') : strlen($text);
	$from = substr($_SERVER['HTTP_REFERER'], strlen(RELATIVE_URL));
	$content = "<p>";
	if($status->user->protected == 0) {
		$content .= "<form action='".RELATIVE_URL."twitter-retweet/{$status->id_str}' method='post'><input type='hidden' name='from' value='$from' /><input type='submit' value='Twitter ".__("Official Retweet")."'> ".__("or Traditional Retweet").":</form>";
	} else {
		$content .= __("Note: ").__("It is not well suited to retweet a protected user 's tweet.");
	}

	$content .= "</p><p><form action='".RELATIVE_URL."update' method='post'><input type='hidden' name='from' value='$from' /><textarea name='status' style='width:100%;max-width:400px;' rows='3' id='status'>$text</textarea><br /><input type='submit' value='".__(Retweet)."'><span id='remaining'>" . (140 - $length) ."</span></form>".js_counter("status")."</p>";

	return $content;
}

function twitter_tweets_per_day($user, $rounding = 1) {
	$days_on_twitter = (time() - strtotime($user->created_at)) / 86400;
	return round($user->statuses_count / $days_on_twitter, $rounding);
}

function theme_user_header($user) {
	$name = theme('full_name', $user);
	$full_avatar = img_proxy_url(str_replace('_normal.', '.', theme_get_avatar($user)));
	$link = theme('external_link', $user->url);
	$raw_date_joined = strtotime($user->created_at);
	$date_joined = date('Y-m-d H:i', $raw_date_joined);
	$tweets_per_day = twitter_tweets_per_day($user, 1);
	$bio = twitter_parse_tags($user->description);

	$out = "<div class='profile'>";

	if (setting_fetch('avataro', 'yes') == 'yes') {
		$out .= "<span class='avatar'><a href='$full_avatar'>".theme('avatar', img_proxy_url(theme_get_avatar($user)), 1)."</a></span><span class='status shift48'>";
	} else {
		$out .= "<span class='status'>";
	}

	$out .= "<b>{$name}</b> ";

	if ($user->verified == true) $out .= '<small><i>'.__("Verified").'</i></small> ';
	if ($user->protected == true) $out .= '<small><i>'.__("Private/Protected").'</i></small> ';
	$friendship_obj = NULL;
	if (strtolower($user->screen_name) !== strtolower(user_current_username())) {
		$friendship_obj = friendship($user->screen_name);
		if ($friendship_obj->relationship->target->following == 1) $out .= '<small><b>FOLLOWS YOU</b></small>';
	}

	$out .= "<br /><span class='features'>[ ";

	if (setting_fetch('avataro', 'yes') != 'yes') $out .= "<a href='$full_avatar'>".__("View picture")."</a> | ";

	if (strtolower($user->screen_name) !== strtolower(user_current_username())) {
		if ($user->following !== true) {
			$out .= "<a href='".RELATIVE_URL."follow/{$user->screen_name}'>".__("Follow")."</a>";
		} else {
			$out .= "<a href='".RELATIVE_URL."unfollow/{$user->screen_name}'>".__("Unfollow")."</a>";
		}

		$out .= " | <a href='".RELATIVE_URL."directs/create/{$user->screen_name}'>".__("Direct Message")."</a>";
	} else {
		$out .= "<a href='".RELATIVE_URL."profile'>".__("Update Profile")."</a>";
	}

	$out .= " ] [ {$user->statuses_count} ".__("Tweets")." | <a href='".RELATIVE_URL."followers/{$user->screen_name}'>{$user->followers_count} ".__("Followers")."</a> | <a href='".RELATIVE_URL."friends/{$user->screen_name}'>{$user->friends_count} ".__("Friends")."</a> | <a href='".RELATIVE_URL."favourites/{$user->screen_name}'>{$user->favourites_count} ".__("Favourites")."</a> | <a href='".RELATIVE_URL."lists/{$user->screen_name}'>{$user->listed_count} ".__("Lists")."</a> ]";

	if (strtolower($user->screen_name) !== strtolower(user_current_username())) {
		$block_str = $friendship_obj->relationship->source->blocking == 1 ? "Unblock" : "Block";
		$out .= " [ <a href='".RELATIVE_URL."confirm/block/{$user->screen_name}/{$user->id_str}'>".__($block_str)."</a> - <a href='".RELATIVE_URL."confirm/spam/{$user->screen_name}/{$user->id_str}'>".__('Report Spam')."</a> ]";
	}

	$out .= "</span><br /><small class='about'>";

	if ($user->description != "") $out .= __("Bio: ")."{$bio}<br />";
	if ($user->url != "") $out .= __("Link: ")."{$link}<br />";
	if ($user->location != "") $out .= __("Location: ")."<a href='http://maps.google.com/maps?q={$user->location}' target='_blank'>{$user->location}</a><br />";

	$out .= __("Joined: ")."{$date_joined} ($tweets_per_day ".__("Tweets Per Day").")</small></span></div>";
	return $out;
}

function theme_get_avatar($object) {
	if ($_SERVER['HTTPS'] == "on" && $object->profile_image_url_https) {
		return $object->profile_image_url_https;
	} else {
		return $object->profile_image_url;
	}
}

function theme_avatar($url, $force_large = false) {
	$size = $force_large ? 48 : 24;
	if (setting_fetch('avataro', 'yes') == 'yes') {
		return "<img class='shead' src='$url' height='$size' width='$size' />";
	} else {
		return '';
	}
}
function theme_status_time_link($status, $is_link = true) {
	$time = strtotime($status->created_at);

	if ($time > 0) {
		if (twitter_date('dmy') == twitter_date('dmy', $time)) {
			$out = format_interval(time() - $time).__(" ago");
		} else {
			$out = twitter_date('H:i', $time);
		}
	} else {
		$out = $status->created_at;
	}

	if ($is_link) $out = "<a href='".RELATIVE_URL."status/{$status->id_str}'>$out</a>";

	if ((substr($_GET['q'],0,4) == 'user') || (setting_fetch('browser') == 'touch') || (setting_fetch('browser', 'desktop') == 'desktop') || (setting_fetch('browser') == 'naiping')) {
		return $out;
	} else {
		return strip_tags($out);
	}
}
function twitter_date($format, $timestamp = null) {
	static $offset;
	if (!isset($offset)) {
	if (user_is_authenticated()) {
		if (array_key_exists('utc_offset', $_COOKIE)) {
		$offset = $_COOKIE['utc_offset'];
		} else {
		$user = twitter_user_info();
		$offset = $user->utc_offset;
		setcookie('utc_offset', $offset, time() + 3000000, '/');
		}
	} else {
		$offset = 0;
	}
	}
	if (!isset($timestamp)) {
	$timestamp = time();
	}
	return gmdate($format, $timestamp + $offset);
}
function twitter_standard_timeline($feed, $source) {
	$output = array();
	if (!is_array($feed) && !is_array($feed->statuses) && $source != 'thread') return $output;
	if (is_array($feed)) {
		foreach($feed as $key => $status) {
			if($status->id_str) {
				$feed[$key]->id = $status->id_str;
			}
			if($status->in_reply_to_status_id_str) {
				$feed[$key]->in_reply_to_status_id = $status->in_reply_to_status_id_str;
			}
			if($status->retweeted_status->id_str) {
				$feed[$key]->retweeted_status->id = $status->retweeted_status->id_str;
			}
		}
	}

	switch ($source) {
		case 'search':
			$feed = $feed->statuses;

		case 'status':
		case 'favourites':
		case 'friends':
		case 'replies':
		case 'user':
			foreach ($feed as $status) {
				$new = $status;

				if ($new->retweeted_status) {
					$retweet = $new->retweeted_status;
					unset($new->retweeted_status);
					$retweet->retweeted_by = $new;
					$retweet->original_id = $new->id_str;
					$new = $retweet;
				}

				$new->from = $new->user;
				unset($new->user);
				$output[(string) $new->id_str] = $new;
			}
			return $output;

		case 'directs_sent':
		case 'directs_inbox':
			foreach ($feed as $status) {
				$new = $status;
				if ($source == 'directs_inbox') {
					$new->from = $new->sender;
					$new->to = $new->recipient;
				} else {
					$new->from = $new->recipient;
					$new->to = $new->sender;
				}
				unset($new->sender, $new->recipient);
				$new->is_direct = true;
				$output[] = $new;
			}

			return $output;

		case 'thread':
			$html_tweets = explode('</li>', $feed);
			foreach ($html_tweets as $tweet) {
				$id = preg_match_one('#msgtxt(\d*)#', $tweet);
				if (!$id) continue;
				$output[$id] = (object) array(
					'id' => $id,
					'text' => strip_tags(preg_match_one('#</a>: (.*)</span>#', $tweet)),
					'source' => preg_match_one('#>from (.*)</span>#', $tweet),
					'from' => (object) array(
					'id' => preg_match_one('#profile_images/(\d*)#', $tweet),
					'screen_name' => preg_match_one('#twitter.com/([^"]+)#', $tweet),
					'profile_image_url' => preg_match_one('#src="([^"]*)"#' , $tweet),
					),
					'to' => (object) array(
					'screen_name' => preg_match_one('#@([^<]+)#', $tweet),
					),
					'created_at' => str_replace('about', '', preg_match_one('#info">\s(.*)#', $tweet)),
				);
			}

			return $output;

		default:
			echo "<h1>$source</h1><pre>";
			print_r($feed); die();
	}
}

function preg_match_one($pattern, $subject, $flags = NULL) {
	preg_match($pattern, $subject, $matches, $flags);
	return trim($matches[1]);
}

function twitter_user_info($username = null) {
	if (!$username)
	$username = user_current_username();
	$request = API_ROOT."users/show.json?include_entities=true&screen_name=$username";
	$user = twitter_process($request);
	return $user;
}

function twitter_timeline_filter($input) {
	$filter_keywords = explode(" ",setting_fetch('filterc'));
	foreach ($filter_keywords as $filter_keyword) {
		if (stripos($input, $filter_keyword) !== FALSE) {
			return true;
		}
	}
	return false;
}

function theme_timeline($feed) {
	if (count($feed) == 0) return theme('no_tweets');

	$hide_pagination = count($feed) < 5 ? true : false;
	$rows = array();
	$page = menu_current_page();
	$date_heading = false;

	$max_id = 0;
	$since_id = 0;

	$first = TRUE;

	foreach ($feed as &$status) {
		if ($first) {
			$since_id = $status->id_str;
			$first = FALSE;
		} else {
			$max_id = isset($status->retweeted_by) ? $status->retweeted_by->id_str : $status->id_str;
		}

		$status->text = twitter_parse_tags($status->text, $status->entities, $status->id_str, strip_tags($status->source));
	}

	unset($status);

	// Only embed images in suitable browsers
	if (EMBEDLY_KEY !== '' && (setting_fetch('showthumbs', 'yes') == 'yes')) {
		embedly_embed_thumbnails($feed);
	}

	foreach ($feed as $status) {
		$time = strtotime($status->created_at);

		if ($time > 0) {
			$date = twitter_date('l jS F Y', strtotime($status->created_at));

			if ($date_heading !== $date) {
				$date_heading = $date;
				$rows[] = array(
					'data' => array("$date"),
					'class' => 'date'
				);
			}
		} else {
			$date = $status->created_at;
		}

		$link = theme('status_time_link', $status, !$status->is_direct);

		$actions = theme('action_icons', $status);
		$avatar = theme('avatar', img_proxy_url(theme_get_avatar($status->from)));

		if ((substr($_GET['q'], 0, 4) == 'user') || (setting_fetch('browser') == 'touch') || (setting_fetch('browser', 'desktop') == 'desktop')) {
			$source = $status->source ? (" ".__("via")." {$status->source}") : '';
		} else {
			$source = $status->source ? (" ".__("via")." ".strip_tags($status->source) ."") : '';
		}

		if ($status->in_reply_to_status_id) {
			$replyto = "<a href='".RELATIVE_URL."status/{$status->in_reply_to_status_id}'>>></a>";
		} else {
			$replyto = null;
		}

		if (setting_fetch('dispnick', 'yes') == 'yes') {
			$showname = $status->from->name;
		} else {
			$showname = $status->from->screen_name;
		}
		$html = "<b class='suser'><a href='".RELATIVE_URL."user/{$status->from->screen_name}'>{$showname}</a></b> ";

		if (setting_fetch('buttonend') == 'yes') {
			$html .= "<span class='stext'>{$status->text}</span><br /><small class='sbutton'>$actions $link ";
		} else {
			$html .= "<small class='sbutton'>$actions</small><br /><span class='stext'>{$status->text}</span><br /><small class='sbutton'>$link";
		}

		$html .= " $source $replyto</small>";

		if ($status->retweeted_by) {
			if (setting_fetch('dispnick', 'yes') == 'yes') {
				$showrtname = $status->retweeted_by->user->name;
			} else {
				$showrtname = $status->retweeted_by->user->screen_name;
			}
			$retweeted_by = $status->retweeted_by->user->screen_name;
			$retweeted_times = $status->retweet_count;

			$retweeted_times_minus = is_numeric($retweeted_times) ? $retweeted_times - 1 : $retweeted_times;
			$retweeted_times_str = ($retweeted_times && $retweeted_times_minus) ? "+{$retweeted_times_minus}" : "";

			$html .= " <small class='sretweet'>".__("retweeted by")." <a href='".RELATIVE_URL."user/{$retweeted_by}'>{$showrtname}</a>{$retweeted_times_str} ".__("<span style='display:none;'>zhuanfa</span>")."</small>";
		}

		unset($row);
		$class = 'status';

		if ($page != 'user' && $avatar)	{
			$row[] = array('data' => $avatar, 'class' => 'avatar');
			$class .= ' shift';
		}

		$row[] = array('data' => $html, 'class' => $class);
		$class = 'tweet';

		if ($page != 'replies' && twitter_is_reply($status)) {
			$class .= ' reply';
		}
		$row = array('data' => $row, 'class' => $class);
		$rows[] = $row;
	}

	$content = theme('table', array(), $rows, array('class' => 'timeline'));

	if (PHP_INT_SIZE > 4) {
		$max_id = intval($max_id) - 1;
	}

	if (setting_fetch('browser') <> 'blackberry' && !$hide_pagination) {
		$content .= theme('pagination', $max_id);
	} else {
		global $blackberry_pagination;
		$blackberry_pagination = theme('pagination', $max_id);
	}

	return $content;
}

function twitter_is_reply($status) {
	if (!user_is_authenticated()) {
		return false;
	}

	return stripos($status->text, "user/".user_current_username());
}

function theme_followers($feed, $hide_pagination = false, $list = false) {
	$rows = array();

	$lists = $feed->users;
	if (count($lists) == 0) return '<p>'.__('No users to display.').'</p>';

	foreach ($lists as $user) {
		$name = theme('full_name', $user);
		if($list)
			$name .= " <a href='".RELATIVE_URL."confirm/listdelete/{$list}/{$user->screen_name}'>".__("Delete From List")."</a>";
		$tweets_per_day = twitter_tweets_per_day($user);
		$last_tweet = strtotime($user->status->created_at);
		$content = "{$name}<br /><span class='about'>";

		if ($user->description != "") $content .= "<strong>".__("Bio: ")."</strong>{$user->description}<br />";
		if ($user->location != "") $content .= "<strong>".__("Location: ")."</strong>{$user->location}<br />";

		$content .= "<strong>".__("Info: ")."</strong>";
		$content .= $user->statuses_count . " ".__("Tweets")." | ";
		$content .= $user->friends_count . " ".__("Friends")." | ";
		$content .= $user->followers_count . " ".__("Followers")." | ";
		$content .= "~" . $tweets_per_day . " ". __("Tweets Per Day")."<br />";
		$content .= "<strong>".__("Last tweet: ")."</strong>";

		if ($user->protected == 'true' && $last_tweet == 0) {
			$content .= __("Private/Protected");
		} elseif ($last_tweet == 0) {
			$content .= __("Never tweeted");
		} else {
			// $content .= twitter_date('Y-m-d H:i', $last_tweet);
			$content .= format_interval(time() - $last_tweet).__(" ago");
		}

		$content .= "</span>";

		$rows[] = array(
			'data' => array(
				array('data' => theme('avatar', img_proxy_url(theme_get_avatar($user))),'class' => 'avatar'),
				array('data' => $content, 'class' => 'status shift')
			),
			'class' => 'tweet'
		);

	}

	$content = theme('table', array(), $rows, array('class' => 'followers'));
	if (!$hide_pagination) $content .= theme('list_pagination', $feed);
	return $content;
}


function theme_full_name($user) {
	$name = "<a href='".RELATIVE_URL."user/{$user->screen_name}'>{$user->screen_name}</a>";
	if ($user->name && $user->name != $user->screen_name) {
	$name .= " ({$user->name})";
	}
	return $name;
}

function theme_no_tweets() {
	return '<p>'.__("No tweets to display.").'</p>';
}

function theme_search_form($query) {
	$query = stripslashes(htmlspecialchars($query));
	return "<form action='".RELATIVE_URL."search' method='GET'><input name='query' value=\"$query\" /><input type='submit' value='".__("Search")."' /></form>";
}

function theme_external_link($url) {
	switch (setting_fetch('linktrans', 'd')) {
		case 'o':
			$text = $url;
			break;
		case 'd':
			$urlpara = parse_url($url);
			$text = "[{$urlpara[host]}]";
			break;
		case 'l':
			$text = "[link]";
			break;
	}

	return "<a href='$url'>$text</a>";
}

function theme_pagination($max_id = false, $max_page = 1000) {
	$page = intval($_GET['page']);
	$links = array();

	if (preg_match('#&q(.*)#', $_SERVER['QUERY_STRING'], $matches)) $query = $matches[0];

	if ($page == 0) $page = 1;

	if ($max_id == false) {
		if ($page < $max_page) {
			$links[] = "<a href='".RELATIVE_URL."{$_GET['q']}?page=".($page+1)."$query'>".__("Older")."</a>";

			if ($page > 1) $links[] = "<a href='".RELATIVE_URL."{$_GET['q']}?page=".($page - 1)."$query'>".__("Newer")."</a>";
		}
	} else {
		$links[] = "<a href='".RELATIVE_URL."{$_GET['q']}?max_id=$max_id"."$query'>".__("More")." &raquo;</a>";
	}

	return '<p class="pagination">'.implode(' | ', $links).'</p>';
}

function theme_action_icons($status) {
	$from = isset($status->from->screen_name) ? $status->from->screen_name : $status->user->screen_name;
	$retweeted_by = $status->retweeted_by->user->screen_name;
	$retweeted_id = $status->retweeted_by->id_str;
	$geo = $status->geo;
	$actions = array();

	if (!$status->is_direct) {
		$actions[] = theme('action_icon', RELATIVE_URL."user/{$from}/reply/{$status->id_str}", 'images/reply.png', __('@'));

		if ($status->favorited == '1') {
			$actions[] = theme('action_icon', RELATIVE_URL."unfavourite/{$status->id_str}", 'images/star.png', __('UNFAV'));
		} else {
			$actions[] = theme('action_icon', RELATIVE_URL."favourite/{$status->id_str}", 'images/star_grey.png', __('FAV'));
		}

		if (user_is_current_user($retweeted_by)) {
			$actions[] = theme('action_icon', RELATIVE_URL."confirm/delete/{$retweeted_id}", 'images/trash.gif', __('UNDO'));
		} else {
			$actions[] = theme('action_icon', RELATIVE_URL."retweet/{$status->id_str}", 'images/retweet.png', __('RT'));
		}

		if (user_is_current_user($from)) {
			$actions[] = theme('action_icon', RELATIVE_URL."confirm/delete/{$status->id_str}", 'images/trash.gif', __('DEL'));
		}

		if ($geo !== null) {
			$latlong = $geo->coordinates;
			$lat = $latlong[0];
			$long = $latlong[1];
			$actions[] = theme('action_icon', "http://maps.google.com/maps?q={$lat},{$long}", 'images/map.png', __('GEO'));
		}
	} else {
		$actions[] = theme('action_icon', RELATIVE_URL."directs/create/{$from}", 'images/dm.png', __('DM'));
		$actions[] = theme('action_icon', RELATIVE_URL."directs/delete/{$status->id_str}", 'images/trash.gif', __('DEL'));
	}

	return implode(' ', $actions);
}

function theme_action_icon($url, $image_url, $text) {
	if (setting_fetch('buttonintext', 'yes') == 'yes') {
		return "<a href='$url'>$text</a>";
	} else {
		return "<a href='$url'><img src='$image_url' /></a>";
	}
}
