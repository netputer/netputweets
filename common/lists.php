<?php

function twitter_lists_tweets($user, $list) {
	// Tweets belonging to a list
	$count = setting_fetch('tpp', 20);
	$url = API_ROOT."lists/statuses.json?slug={$list}&owner_screen_name={$user}&include_entities=true&include_rts=true&count={$count}";
		if ($_GET['max_id']) $url .= "&max_id=".$_GET['max_id'];
		if ($_GET['since_id']) $url .= "&since_id=".$_GET['since_id'];

	return twitter_process($url);
}

function twitter_lists_user_lists($user) {
	// Lists a user subscribed to
	return twitter_process(API_ROOT."lists/list.json?screen_name={$user}");
}

function twitter_lists_user_memberships($user) {
	// Lists a user belongs to
	$cursor = $_GET['cursor'];
		if (!is_numeric($cursor)) {
			$cursor = -1;
		}
	return twitter_process(API_ROOT."lists/memberships.json?screen_name={$user}&cursor={$cursor}");
}

function twitter_lists_list_members($user, $list) {
	// Members of a list
	$cursor = $_GET['cursor'];
		if (!is_numeric($cursor)) {
			$cursor = -1;
		}
	return twitter_process(API_ROOT."lists/members.json?slug={$list}&owner_screen_name={$user}&cursor={$cursor}");
}

function twitter_lists_list_subscribers($user, $list) {
	// Subscribers of a list
	$cursor = $_GET['cursor'];
		if (!is_numeric($cursor)) {
			$cursor = -1;
		}
	return twitter_process(API_ROOT."lists/subscribers.json?slug={$list}&owner_screen_name={$user}&cursor={$cursor}");
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
			// List editting page (name and availability)
			return lists_list_edit_page($user, $list);
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
	$content = "<p><a href='".BASE_URL."lists/{$user}/memberships'>{$user} ".__("'s Memberships")."</a> | <strong>{$user} ".__("'s Subscriptions")."</strong></p>";
	$content .= theme('lists', $lists);
	theme('page', "{$user} ".__("'s Lists"), $content);
}

function lists_membership_page($user) {
	// Show lists a user belongs to
	$lists = twitter_lists_user_memberships($user);
	$content = "<p><strong>{$user} ".__("'s Memberships")."</strong> | <a href='".BASE_URL."lists/{$user}'>{$user} ".__("'s Subscriptions")."</a></p>";
	$content .= theme('lists', $lists);
	theme('page', __("Following")." {$user} ".__("'s Lists"), $content);
}

function lists_list_tweets_page($user, $list) {
	// Show tweets in a list
	$tweets = twitter_lists_tweets($user, $list);
	$tl = twitter_standard_timeline($tweets, 'user');
	$content = theme('status_form');
	$list_url = "lists/{$user}/{$list}";
	$content .= "<p><a href='".BASE_URL."user/{$user}'>{$user}</a>/<strong>{$list}</strong> ".__("'s Tweets")." | <a href='".BASE_URL."{$list_url}/members'>".__("View Members")."</a> | <a href='".BASE_URL."{$list_url}/subscribers'>".__("View Subscribers");
	if(user_is_current_user($user))
		$content .= "</a> | <a href='".BASE_URL."{$list_url}/edit'>".__("Edit List Details");
	$content .= "</a></p>";
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

function lists_list_edit_page($user, $list) {
	if ($_POST['name']) {
		$post_data = array(
			'owner_screen_name' => $user,
			'slug' => $list,
			'name' => stripslashes($_POST['name']),
			'mode' => $_POST['mode'],
			'description' => $_POST['description'],
		);
		twitter_process(API_ROOT."lists/update.json", $post_data);
		twitter_refresh("lists/{$user}/{$p->slug}");
	}

	$p = twitter_process(API_ROOT."lists/show.json?owner_screen_name={$user}&slug={$list}");
	$content = "<form method=\"post\" action=\"".BASE_URL."lists/{$user}/{$list}/edit\" enctype=\"multipart/form-data\">".__("List Name").": <input type=\"text\" name=\"name\" value=\"{$p->name}\" /> (Max 20) <br />".__("Privacy").": <select name=\"mode\">";
	$current_mode = $p->mode === "public";
	$content .= "<option value=\"public\"".($current_mode ? " selected=\"selected\"" : "").">".__("Public")."</option>";
	$content .= "<option value=\"private\"".($current_mode ? "" : " selected=\"selected\"").">".__("Private")."</option>";
	$content .= "</select><br />".__("Description").": (Max 160) <br /><textarea name=\"description\" style=\"width:95%\" rows=\"3\" id=\"description\" >{$p->description}</textarea><br /><input type=\"submit\" value=\"".__("Update")."\" /></form>";

	return theme('page', __("Edit List Details"), $content);
}

/* Theme functions */

function theme_lists($json) {
	$feed = $json->lists ? $json->lists : $json;

	if (!is_array($feed) || count($feed) == 0) {
		return "<p>".__("No lists to display")."</p>";
	}

	$rows = array();
	$headers = array(__("Lists")." ", __("Members")." ", __("Subscribers")." ");

	foreach ($feed as $list) {
		$url = "lists/{$list->user->screen_name}/{$list->slug}";
		$rows[] = array(
			"<a href='".BASE_URL."user/{$list->user->screen_name}'>@{$list->user->screen_name}</a>/<a href='".BASE_URL."{$url}'><strong>{$list->slug}</strong></a> ",
			"<a href='".BASE_URL."{$url}/members'>{$list->member_count}</a> ",
			"<a href='".BASE_URL."{$url}/subscribers'>{$list->subscriber_count}</a>",
		);
	}

	$content = theme('table', $headers, $rows);
	$content .= theme('list_pagination', $json);

	return $content;
}

function theme_list_pagination($json) {
	if ($cursor = (string) $json->next_cursor) {
		$links[] = "<a href='".BASE_URL."{$_GET['q']}?cursor={$cursor}'>".__("Older")."</a>";
	}
	
	if ($cursor = (string) $json->previous_cursor) {
		$links[] = "<a href='".BASE_URL."{$_GET['q']}?cursor={$cursor}'>".__("Newer")."</a>";
	}
	
	if (count($links) > 0) return '<p>'.implode(' | ', $links).'</p>';
}
