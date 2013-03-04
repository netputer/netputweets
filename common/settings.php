<?php
$GLOBALS['colour_schemes'] = array(
	0 => 'Colorful|535F74,D1D0B4,000,555,FFEDED,FFD3D3,FFA,DD9,D33D3E,FFF,FFF',
	1 => 'Blue|3B5998,F7F7F7,000,555,D8DFEA,EEE,FFA,DD9,3B5998,FFF,FFF',
	2 => 'Red|d12,ddd,111,555,fff,eee,ffa,dd9,c12,fff,fff',
	3 => 'Orange|b50,ddd,111,555,fff,eee,ffa,dd9,e81,c40,fff',
	4 => 'Pink|c06,fcd,623,c8a,fee,fde,ffa,dd9,C06,fee,fee',
	5 => 'Green|293C03,ccc,000,555,fff,eee,CCE691,ACC671,495C23,919C35,fff',
	6 => 'Purple|BAAECB,1F1530,9C8BB5,6D617E,362D45,4C4459,4A423E,5E5750,191432,6D617E,6D617E',
);

menu_register(array(
	'settings' => array(
		'callback' => 'settings_page',
		'title' => __("Settings"),
	),
	'reset' => array(
		'hidden' => true,
		'callback' => 'cookie_monster',
	),
));

function cookie_monster() {
	$cookies = array(
		'browser',
		'settings',
		'utc_offset',
		'search_favourite',
		'USER_AUTH',
	);
	$duration = time() - 3600;
	foreach ($cookies as $cookie) {
		setcookie($cookie, NULL, $duration, '/');
		setcookie($cookie, NULL, $duration);
	}
	return theme("page", "Cookie Monster", "<p>The cookie monster has logged you out and cleared all settings. Try <a href='".BASE_URL."'>logging in again</a> now.</p>");
}

function setting_fetch($setting, $default = NULL) {
	$settings = (array) unserialize(base64_decode($_COOKIE['settings']));
	if (array_key_exists($setting, $settings)) {
		return $settings[$setting];
	} else {
		return $default;
	}
}

function settings_page($args) {
	if ($args[1] == 'save') {
		$settings['browser'] = $_POST['browser'];
		$settings['tpp'] = $_POST['tpp'];
		$settings['locale'] = $_POST['locale'];
		$settings['colours'] = $_POST['colours'];

		$settings['topuser'] = $_POST['topuser'];
		$settings['tophome'] = $_POST['tophome'];
		$settings['topreplies'] = $_POST['topreplies'];
		$settings['topdirects'] = $_POST['topdirects'];
		$settings['topsearch'] = $_POST['topsearch'];

		$settings['replies'] = $_POST['replies'];
		$settings['directs'] = $_POST['directs'];
		$settings['search'] = $_POST['search'];
		$settings['public'] = $_POST['public'];
		$settings['favourites'] = $_POST['favourites'];
		$settings['lists'] = $_POST['lists'];
		$settings['followers'] = $_POST['followers'];
		$settings['friends'] = $_POST['friends'];
		$settings['blockings'] = $_POST['blockings'];
		$settings['about'] = $_POST['about'];
		$settings['ssettings'] = $_POST['ssettings'];
		$settings['slogout'] = $_POST['slogout'];

		$settings['linktrans'] = $_POST['linktrans'];
		$settings['avataro'] = $_POST['avataro'];
		$settings['buttonintext'] = $_POST['buttonintext'];

		$settings['buttontime'] = $_POST['buttontime'];
		$settings['buttonfrom'] = $_POST['buttonfrom'];
		$settings['buttonend'] = $_POST['buttonend'];

		$settings['buttongeo'] = $_POST['buttongeo'];

		$settings['longtext'] = $_POST['longtext'];
		$settings['showthumbs'] = $_POST['showthumbs'];
		$settings['fixedtago'] = $_POST['fixedtago'];
		$settings['fixedtagc'] = $_POST['fixedtagc'];
		$settings['css'] = $_POST['css'];
		$settings['filtero'] = $_POST['filtero'];
		$settings['filterc'] = $_POST['filterc'];

		$duration = time() + (3600 * 24 * 365);
		setcookie('settings', base64_encode(serialize($settings)), $duration, '/');
		settings_refresh('');
	}

	$modes = array(
		'desktop' => __("PC/Laptop"),
		'mobile' => __("Normal phone"),
		'touch' => __("Touch phone"),
		'blackberry' => __("BlackBerry (Pagination At Bottom)"),
	);

	$locale = array(
		'zh_CN' => '简体中文',
		'en_US' => 'English',
		'zh_TW' => '繁體中文',
	);

	$linktrans = array(
		'o' => __("Full URL"),
		'd' => __("Domain Only"),
		'l' => "[link]",
	);

	$longtext = array(
		'a' => __("Automatic Cut"),
		'r' => __("Return Error"),
	);

	$colour_schemes = array();

	foreach ($GLOBALS['colour_schemes'] as $id => $info) {
		list($name, $colours) = explode('|', $info);
		$colour_schemes[$id] = $name;
	}

	$content .= '<form action="'.BASE_URL.'settings/save" method="post">';
	$content .= '<p><b>'.__("Menu Settings").'</b></p>';
	$content .= '<small>[1] '.__("Choose what you want to display on the Top Bar.").'</small><br />';
	$content .= '<label>　<input type="checkbox" name="topuser" value="yes" '. (setting_fetch('topuser') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("User").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="tophome" value="yes" '. (setting_fetch('tophome', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Home").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="topreplies" value="yes" '. (setting_fetch('topreplies', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Replies").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="topdirects" value="yes" '. (setting_fetch('topdirects', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Directs").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="topsearch" value="yes" '. (setting_fetch('topsearch') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Search").'</label><br />';
	$content .= '<small>[2] '.__("And Choose what you want to display on the Bottom Bar.").'</small><br />';
	$content .= '<label>　<input type="checkbox" name="replies" value="yes" '. (setting_fetch('replies') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Replies").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="directs" value="yes" '. (setting_fetch('directs') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Directs").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="search" value="yes" '. (setting_fetch('search') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Search").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="favourites" value="yes" '. (setting_fetch('favourites') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Favourites").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="lists" value="yes" '. (setting_fetch('lists') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Lists").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="followers" value="yes" '. (setting_fetch('followers') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Followers").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="friends" value="yes" '. (setting_fetch('friends') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Friends").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="blockings" value="yes" '. (setting_fetch('blockings') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Blockings").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="about" value="yes" '. (setting_fetch('about') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("About").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="ssettings" value="yes" '. (setting_fetch('ssettings', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Settings").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="slogout" value="yes" '. (setting_fetch('slogout', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Logout").'</label><br />';

	$content .= '<p><b>'.__("Status Settings").'</b></p>';
	$content .= '<label>　<input type="checkbox" name="buttonintext" value="yes" '. (setting_fetch('buttonintext', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Show @/DM/RT/FAV/DEL As Text instead of images").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="buttonend" value="yes" '. (setting_fetch('buttonend') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Put the Buttons after each Status").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="buttontime" value="yes" '. (setting_fetch('buttontime', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Show Status Time").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="buttonfrom" value="yes" '. (setting_fetch('buttonfrom', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Show Status Source").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="avataro" value="yes" '. (setting_fetch('avataro', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Show Avatar").'</label><br />';
	$content .= '<label>  <input type="checkbox" name="buttongeo" value="yes" '. (setting_fetch('buttongeo') == 'yes' ? ' checked="checked" ' : '') .' /> GEO [Geolocation]</label><br />';
	$content .= '<label>　<input type="checkbox" name="fixedtago" value="yes" '. (setting_fetch('fixedtago', 'no') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Enable Fixed Tag: ").'</label>#<input type="text" id="fixedtagc" name="fixedtagc" value="'.setting_fetch('fixedtagc').'" maxlength="70" style="width:40px;" /><br />';
	if (function_exists('mb_strlen')) $content .= '<label>　'.__("When posting a 140+ chars tweet: ").'<select name="longtext">'.theme('options', $longtext, setting_fetch('longtext', 'r')).'</select></label><hr />';

	$content .= '<p><b>'.__("Global Settings").'</b></p>';
	$content .= '<label>　'.__("Colour scheme: ").'<select name="colours">'.theme('options', $colour_schemes, setting_fetch('colours')).'</select></label><br />';
	$content .= '<label>　'.__("Mode: ").'<select name="browser">'.theme('options', $modes, $GLOBALS['current_theme']).'</select></label><br />';
	$content .= '<label>　'.__("Language: ").'<select name="locale">'.theme('options', $locale, setting_fetch('locale', 'zh_CN')).'</select></label><br />';
	$content .= '<label>　'.__("Showing URL: ").'<select name="linktrans">'.theme('options', $linktrans, setting_fetch('linktrans', 'd')).'</select></label><br /><small>　　'.__("Note: ").'"'.__("Domain Only").'" '.__("means change").' https://twitter.com/netputer '.__("to").' [twitter.com]</small><p />';
	$content .= '<label>　'.__("Tweets Per Page: ").'<input type="text" id="tpp" name="tpp" value="'.setting_fetch('tpp', 20).'" maxlength="3" style="width:20px;" /> (20-200)</label><br />';
	if (EMBEDLY_KEY != '') $content .= '<label>　<input type="checkbox" name="showthumbs" value="yes" '. (setting_fetch('showthumbs', 'yes') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Preview Photos In Timelines").'</label><br />';
	$content .= '<label>　<input type="checkbox" name="filtero" value="yes" '. (setting_fetch('filtero', 'no') == 'yes' ? ' checked="checked" ' : '') .' /> '.__("Enable Keyword Filter: ").'</label><input type="text" id="filterc" name="filterc" value="'.setting_fetch('filterc').'" /><br /><small>　　'.__("Note: ").__("Separate keywords with space").'</small><p />';
	$content .= '<p><label>'.__("Custom CSS: ").'<br /><textarea name="css" cols="50" rows="3" id="css" style="width:95%">'.setting_fetch('css').'</textarea></label></p>';
	$content .= '<hr /><p><input type="submit" name="Submit" value="'.__("Save").'" /> <small>'.__('Visit ').'<a href="'.BASE_URL.'reset">'.__("Reset").'</a>'.__(' if things go horribly wrong - it will log you out and clear all settings.').'</small></p></form>';

	return theme('page', __("Settings"), $content);
}

function settings_refresh($page = NULL) {
	if (isset($page)) {
		$page = BASE_URL . $page;
	} else {
		$page = $_SERVER['HTTP_REFERER'];
	}
	header('Location: '. $page);
	exit();
}
