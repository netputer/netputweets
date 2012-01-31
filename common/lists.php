<?php
/*
 API Calls

 Note that some calls are XML and not JSON like the rest of Dabr. This is because 32-bit
 PHP installs cannot handle the 64-bit Lists API cursors used for paging.

 */

function lists_paginated_process($url, $param = FALSE) {
	// Adds cursor/pagination parameters to a query
	$cursor = $_GET['cursor'];
	if (!is_numeric($cursor)) {
		$cursor = -1;
	}
	
	if ($param) {
		$url .= '&cursor='.$cursor;
	} else {
		$url .= '?cursor='.$cursor;
	}
	
	$xml = twitter_process($url);
	return simplexml_load_string($xml);
}

function twitter_lists_tweets($user, $list) {
	// Tweets belonging to a list
	$url = API_URL."lists/statuses.json?slug={$list}&owner_screen_name={$user}&include_entities=true";
	$page = intval($_GET['page']);
	if ($page > 0) $url .= '&page='.$page;
	return twitter_process($url);
}

function twitter_lists_user_lists($user) {
	// Lists a user has created
	return lists_paginated_process(API_URL."{$user}/lists.xml");
}

function twitter_lists_user_memberships($user) {
	// Lists a user belongs to
	return lists_paginated_process(API_URL."{$user}/lists/memberships.xml");
}

function twitter_lists_list_members($user, $list) {
	// Members of a list
	// return lists_paginated_process(API_URL."{$user}/{$list}/members.xml");
	return lists_paginated_process(API_URL."lists/members.xml?slug={$list}&owner_screen_name={$user}", TRUE);
}

function twitter_lists_list_subscribers($user, $list) {
	// Subscribers of a list
	// return lists_paginated_process(API_URL."{$user}/{$list}/subscribers.xml");
	return lists_paginated_process(API_URL."lists/subscribers.xml?slug={$list}&owner_screen_name={$user}", TRUE);
}

/* Front controller for the new pages

List URLS:
lists -- current user's lists
lists/$user -- xhosen user's lists
lists/$user/lists -- alias of the above
lists/$user/memberships -- lists user is in
lists/$user/$list -- tweets
lists/$user/$list/members
lists/$user/$list/subscribers
lists/$user/$list/edit -- rename a list (no member editting)
*/

function lists_controller($query) {
	// Pick off $user from $query or default to the current user
	$user = $query[1];
	if (!$user) $user = user_current_username();

	// Fiddle with the $query to find which part identifies the page they want
	if ($query[3]) {
		// URL in form: lists/$user/$list/$method
		$method = $query[3];
		$list = $query[2];
	} else {
		// URL in form: lists/$user/$method
		$method = $query[2];
	}

	// Attempt to call the correct page based on $method
	switch ($method) {
		case '':
		case 'lists':
			// Show which lists a user has created
			return lists_lists_page($user);
		case 'memberships':
			// Show which lists a user belongs to
			return lists_membership_page($user);
		case 'members':
			// Show members of a list
			return lists_list_members_page($user, $list);
		case 'subscribers':
			// Show subscribers of a list
			return lists_list_subscribers_page($user, $list);
		case 'edit':
			// TODO: List editting page (name and availability)
			break;
		default:
			// Show tweets in a particular list
			$list = $method;
			return lists_list_tweets_page($user, $list);
	}

	// Error to be shown for any incomplete pages (breaks above)
	return theme("error", __("List page not found"));
}



/* Pages */

function lists_lists_page($user) {
	// Show a user's lists
	$lists = twitter_lists_user_lists($user);
	$content = "<p><a href='".BASE_URL."lists/{$user}/memberships'>".__("Following")." {$user} ".__("'s Lists")."</a> | <strong>{$user} ".__("'s Lists")."</strong></p>";
	$content .= theme('lists', $lists);
	theme('page', "{$user} ".__("'s Lists"), $content);
}

function lists_membership_page($user) {
	// Show lists a user belongs to
	$lists = twitter_lists_user_memberships($user);
	$content = "<p><strong>".__("Following")." {$user} ".__("'s Lists")."</strong> | <a href='".BASE_URL."lists/{$user}'>{$user} ".__("'s Lists")."</a></p>";
	$content .= theme('lists', $lists);
	theme('page', __("Following")." {$user} ".__("'s Lists"), $content);
}

function lists_list_tweets_page($user, $list) {
	// Show tweets in a list
	$tweets = twitter_lists_tweets($user, $list);
	$tl = twitter_standard_timeline($tweets, 'user');
	$content = theme('status_form');
	$list_url = "lists/{$user}/{$list}";
	$content .= "<p><a href='".BASE_URL."user/{$user}'>{$user}</a>/<strong>{$list}</strong> ".__("'s Tweets")." | <a href='".BASE_URL."{$list_url}/members'>".__("View Members")."</a> | <a href='".BASE_URL."{$list_url}/subscribers'>".__("View Subscribers")."</a></p>";
	$content .= theme('timeline', $tl);
	theme('page', __("Lists")." {$user}/{$list}", $content);
}

function lists_list_members_page($user, $list) {
	// Show members of a list
	// TODO: add logic to CREATE and REMOVE members
	$p = twitter_lists_list_members($user, $list);

	// TODO: use a different theme() function? Add a "delete member" link for each member
	$content = theme('followers', $p, 1);
	$content .= theme('list_pagination', $p);
	theme('page', __("Members of")." {$user}/{$list}", $content);
}

function lists_list_subscribers_page($user, $list) {
	// Show subscribers of a list
	$p = twitter_lists_list_subscribers($user, $list);
	$content = theme('followers', $p, 1);
	$content .= theme('list_pagination', $p);
	theme('page', __("Subscribers of")." {$user}/{$list}", $content);
}



/* Theme functions */

function theme_lists($json) {
	if (count($json->lists) == 0) {
		return "<p>".__("No lists to display")."</p>";
	}
	$rows = array();
	$headers = array(__("Lists")." ", __("Members")." ", __("Subscribers")." ");
	foreach ($json->lists->list as $list) {
		$url = "lists/{$list->user->screen_name}/{$list->slug}";
		$rows[] = array(
			"<a href='user/{$list->user->screen_name}'>@{$list->user->screen_name}</a>/<a href='{$url}'><strong>{$list->slug}</strong></a> ",
			"<a href='{$url}/members'>{$list->member_count}</a> ",
			"<a href='{$url}/subscribers'>{$list->subscriber_count}</a>",
		);
	}
	$content = theme('table', $headers, $rows);
	$content .= theme('list_pagination', $json);
	return $content;
}

function theme_list_pagination($json) {
	if ($cursor = (string) $json->next_cursor) {
		$links[] = "<a href='{$_GET['q']}?cursor={$cursor}'>".__("Older")."</a>";
	}
	if ($cursor = (string) $json->previous_cursor) {
		$links[] = "<a href='{$_GET['q']}?cursor={$cursor}'>".__("Newer")."</a>";
	}
	if (count($links) > 0) return '<p>'.implode(' | ', $links).'</p>';
}
