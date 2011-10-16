<?php
function blackberry_theme_pagination() {
	$page = intval($_GET['page']);
	
	if (preg_match('#&q(.*)#', $_SERVER['QUERY_STRING'], $matches)) $query = $matches[0];
	if ($page == 0) $page = 1;
	if ($page > 1) $links[] = "<a href='".BASE_URL."{$_GET['q']}?page=".($page-1)."$query'>".__("Newer")."</a>";
	
	$links[] = "<a href='".BASE_URL."{$_GET['q']}?page=".($page+1)."$query'>".__("Older")."</a>";
	
	return '<div>'.implode(' | ', $links).'</div>';
}

function blackberry_theme_menu_bottom() {
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
		if (setting_fetch('slogout') == 'yes') {
			$links[] = "<a href='".BASE_URL."logout'>".__("Logout")."</a>";
		}
	}
	if (setting_fetch('srefresh', 'yes') == 'yes') {
		$links[] = "<a href='".BASE_URL."{$_GET['q']}' accesskey='5'>".__("Refresh")."</a> 5";
	}
	return "<div class='menu menu-$menu'>".implode(' | ', $links)."</div>".theme('pagination');
}
?>