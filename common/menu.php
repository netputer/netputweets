<?php

$menu_registry = array();

function menu_register($items) {
	foreach ($items as $url => $item) {
		$GLOBALS['menu_registry'][$url] = $item;
	}
}

function menu_execute_active_handler() {
	$query = (array) explode('/', $_GET['q']);
	$GLOBALS['page'] = $query[0];
	$page = $GLOBALS['menu_registry'][$GLOBALS['page']];
	if (!$page) {
		header('HTTP/1.0 404 Not Found');
		die('404 - Page not found.');
	}
	
	if ($page['security'])
		user_ensure_authenticated();

	if (function_exists('config_log_request'))
		config_log_request();
	
	if (function_exists($page['callback']))
		return call_user_func($page['callback'], $query);

	return false;
}

function menu_current_page() {
	return $GLOBALS['page'];
}

function menu_visible_items() {
	static $items;
	if (!isset($items)) {
		$items = array();
		foreach ($GLOBALS['menu_registry'] as $url => $page) {
			if ($page['security'] && !user_is_authenticated()) continue;
			if ($page['hidden']) continue;
			$items[$url] = $page;
		}
	}
	return $items;
}

function theme_menu_top() {
	$links = array();
	if (user_is_authenticated()){
		if (setting_fetch('topuser') == 'yes') {
			$user = user_current_username();
			$links[] = "<a href='".BASE_URL."user/$user'>$user</a>";
		}
		if (setting_fetch('tophome', 'yes') == 'yes') {
			$links[] = "<a href='".BASE_URL."'>".__("Home")."</a>";
		}
		if (setting_fetch('topreplies', 'yes') == 'yes') {
			$links[] = "<a href='".BASE_URL."replies'>".__("Replies")."</a>";
		}
		if (setting_fetch('topretweets', 'yes') == 'yes') {
			$links[] = "<a href='".BASE_URL."retweets'>".__("Retweets")."</a>";
		}
		if (setting_fetch('topdirects', 'yes') == 'yes') {
			$links[] = "<a href='".BASE_URL."directs'>".__("Directs")."</a>";
		}
		if (setting_fetch('topsearch') == 'yes') {
			$links[] = "<a href='".BASE_URL."search'>".__("Search")."</a>";
		}
		if (setting_fetch('toptwitpic') == 'yes') {
			$links[] = "<a href='".BASE_URL."twitpic'>".__("Twitpic")."</a>";
		}
	} else {
		$links[] = "<span style=font-weight:bold;color:#FFF;>".__("Enjoy Twitter with NetPutweets!")."</span>";
	}
	return "<div class='menu menu-$menu'>".implode(' | ', $links).'</div>';
}


function theme_menu_bottom() {
	$links = array();
	$links[] = "<a href='".BASE_URL."'>".__("Home")."</a>";
	if (user_is_authenticated()) {
		if (setting_fetch('replies') == 'yes') {
			$links[] = "<a href='".BASE_URL."replies'>".__("Replies")."</a>";
		}
		if (setting_fetch('retweets') == 'yes') {
			$links[] = "<a href='".BASE_URL."retweets'>".__("Retweets")."</a>";
		}
		if (setting_fetch('directs') == 'yes') {
			$links[] = "<a href='".BASE_URL."directs'>".__("Directs")."</a>";
		}
		if (setting_fetch('search') == 'yes') {
			$links[] = "<a href='".BASE_URL."search'>".__("Search")."</a>";
		}
		if (setting_fetch('twitpic') == 'yes') {
			$links[] = "<a href='".BASE_URL."twitpic'>".__("Twitpic")."</a>";
		}
		if (setting_fetch('favourites') == 'yes') {
			$links[] = "<a href='".BASE_URL."favourites'>".__("Favourites")."</a>";
		}
		if (setting_fetch('lists') == 'yes') {
			$links[] = "<a href='".BASE_URL."lists'>".__("Lists")."</a>";
		}
		if (setting_fetch('followers') == 'yes') {
			$links[] = "<a href='".BASE_URL."followers'>".__("Followers")."</a>";
		}
		if (setting_fetch('friends') == 'yes') {
			$links[] = "<a href='".BASE_URL."friends'>".__("Friends")."</a>";
		}
		if (setting_fetch('blockings') == 'yes') {
			$links[] = "<a href='".BASE_URL."blockings'>".__("Blockings")."</a>";
		}
		if (setting_fetch('trends') == 'yes') {
			$links[] = "<a href='".BASE_URL."trends'>".__("Trends")."</a>";
		}
	}

	if (user_is_authenticated()) {
		$user = user_current_username();
		array_unshift($links, "<b><a href='".BASE_URL."user/$user'>$user</a></b>");
		if (setting_fetch('about') == 'yes') {
			$links[] = "<a href='".BASE_URL."about'>".__("About")."</a>";
		}
		if (setting_fetch('ssettings', 'yes') == 'yes') {
			$links[] = "<a href='".BASE_URL."settings'>".__("Settings")."</a>";
		}
		if (setting_fetch('slogout', 'yes') == 'yes') {
			$links[] = "<a href='".BASE_URL."logout'>".__("Logout")."</a>";
		}
	}
	if (setting_fetch('srefresh', 'yes') == 'yes') {
		$links[] = "<a href='".BASE_URL."{$_GET['q']}' accesskey='5'>".__("Refresh")."</a> 5";
	}
	return '<div class="menu menu-$menu">'.implode(' | ', $links).'</div>';
}
?>