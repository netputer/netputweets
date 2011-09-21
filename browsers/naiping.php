<?php
require 'touch.php';

function naiping_theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		$fixedtags = ((setting_fetch('fixedtago', 'no') == "yes") && ($text == '')) ? " #".setting_fetch('fixedtagc') : null;
		$output = '<div id="statusform"><form method="post" action="'.BASE_URL.'update"><textarea id="status" name="status" rows="3" style="width:100%;font-size:20px;">'.$text.$fixedtags.'</textarea>';
		if (substr($_GET["q"], 0, 4) !== "user") {
			$output .= ' <a href="'.BASE_URL.'upload">'.__('Upload Picture').'</a>';
		}
		$output .= ' <span id="remaining">140</span> <input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="'.__('Update').'" /></div></form>'.js_counter('status');
		return $output;
	}
}

function naiping_theme_avatar($url, $force_large = false) {
	return touch_theme_avatar($url, $force_large);
}

function naiping_theme_menu_top() {
	if (user_is_authenticated()) $user = user_current_username();
	$visible = array("" => __("Home"),"replies" => __("Replies"),"retweets" => __("Retweets"),"directs" => __("Directs"),"search" => __("Search"),"user/$user" => __("Profile"),);
	$html = "<div id='header'><span id='logo'><a href='".BASE_URL."'>奶瓶腿</a></span><ul id='nav'>";

	foreach ($visible as $k => $v) {
		$current = ($_GET['q'] == $k) ? "class='current'" : null;
		$html .= "<li $current><a href='".BASE_URL."$k'>$v</a></li>";
	}

	$html .= "<li id='more'><a href='#'>".__("More")." »</a></li></ul><ul id='sub-nav'>	<li><a href='#'>".__("More")." »</a></li>	<li><a href='".BASE_URL."favourites'>".__("Favourites")."</a></li><li><a href='".BASE_URL."followers'>".__("Followers")."</a></li><li><a href='".BASE_URL."friends'>".__("Friends")."</a></li><li><a href='".BASE_URL."blockings'>".__("Blockings")."</a></li><li><a href='".BASE_URL."lists'>".__("Lists")."</a></li><li><a href='".BASE_URL."settings'>".__("Settings")."</a></li><li><a href='".BASE_URL."about'>".__("About")."</a></li><li><a href='".BASE_URL."logout'>".__("Logout")."</a></li></ul></div><div class='clear'></div>";
	return $html;
}

function naiping_theme_css() {
	$out = theme_css();
	$out .= '<link rel="stylesheet" href="'.BASE_URF.'browsers/naiping.css" />';
	$out .= "<script type='text/javascript' src='http".(FORCE_SSL ? "s" : "")."://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js'></script><script type='text/javascript'>
<!--
$(function()
{
    $('#more')
        .mouseover(function()
        {
            var offset = $('#more').offset();
            $('#sub-nav').show().css({left:offset.left, top:offset.top});
        });

    $('#sub-nav')
        .mouseleave(function()
        {
            $(this).hide();
        });
});
//-->
</script>";
	if (($_GET['q'] == 'directs/inbox') || ($_GET['q'] == 'directs') || ($_GET['q'] == 'directs/sent')) $out .= '<style type="text/css">a.tl-dm{display:block;}</style>';
	return $out;
}

function naiping_theme_timeline($feed) {
	if (count($feed) == 0) return theme('no_tweets');
	
	$hide_pagination = count($feed) < 2 ? true : false;
	$rows = array();
	$page = menu_current_page();
	$date_heading = false;
	$first=0;
	
	foreach ($feed as &$status) {
		$status->text = twitter_parse_tags($status->text, $status->entities);
	}
	unset($status);

	// Only embed images in suitable browsers
	if (EMBEDLY_KEY !== '' && (setting_fetch('showthumbs', 'yes') == 'yes')) {
		embedly_embed_thumbnails($feed);
	}
	
	foreach ($feed as $status) {
		if ($first==0) {
			$since_id = $status->id;
			$first++;
		} else {
			$max_id =  $status->id;
			if ($status->original_id) {
				$max_id =  $status->original_id;
			}
		}
		
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

		if ((setting_fetch('filtero', 'no') == 'yes') && twitter_timeline_filter($status->text)) {
			$text = "<a href='".BASE_URL."status/{$status->id}' style='text-decoration:none;'><small>[".__("Tweet Filtered")."]</small></a>";
		} else {
			$text = $status->text;
		}

		if (setting_fetch('buttontime', 'yes') == 'yes') {
			$link = theme('status_time_link', $status, !$status->is_direct);
		}
		$actions = theme('action_icons', $status);
		$avatar = theme('avatar', $status->from->profile_image_url);
		if (setting_fetch('buttonfrom', 'yes') == 'yes') {
			$source = $status->source ? (" ".__("via")." {$status->source}") : '';
		} else {
			$source = NULL;
		}
		if ($status->in_reply_to_status_id) {
			$replyto = "<a href='".BASE_URL."status/{$status->in_reply_to_status_id}'>".__("in reply to")." {$status->in_reply_to_screen_name}</a>";
		} else {
			$replyto = null;
		}

		if ($status->retweeted_by) {
			$retweeted_by = $status->retweeted_by->user->screen_name;
			$retweeted_times = $status->retweet_count;
			$retweeted_times_str = ($retweeted_times && $retweeted_times-1) ? "+{$retweeted_times}" : "";
			$retweeted = " <small class='sretweet'>".__("retweeted by")." <a href='".BASE_URL."user/{$retweeted_by}'>{$retweeted_by}</a>{$retweeted_times_str} ".__("<span style='display:none;'>zhuanfa</span>")."</small>";
		} else {
			$retweeted = null;
		}

		$html = "<table><tr>";
		if (setting_fetch('avataro', 'yes') == 'yes') {
			$html .= "<td width='50'>$avatar</td>";
		}
		$html .= "<td><b class='suser'><a href='".BASE_URL."user/{$status->from->screen_name}'>{$status->from->screen_name}</a></b> <span class='stext'>{$text}</span><br /><small class='sbutton'>$link $source $replyto $retweeted</small></td><td class='actionlinks'>$actions</td></tr></table>";

		unset($row);
		$class = 'status';

		$row[] = array('data' => $html, 'class' => $class);
		$class = 'tweet';

		if ($page != 'replies' && twitter_is_reply($status)) {
			$class .= ' reply';
		}
		$row = array('data' => $row, 'class' => $class);
		$rows[] = $row;
	}

	$content = theme('table', array(), $rows, array('class' => 'timeline'));
	if (count($feed) >= 15) $content .= theme('pagination');
	return $content;
}

function naiping_theme_action_icons($status) {
	$user = $status->from->screen_name;
	$actions = array();
	if (!$status->is_direct) {
		$actions[] = "<a class='tl-re' href='".BASE_URL."user/{$user}/reply/{$status->id}'>@</a>";
	}
	if ($status->user->screen_name != user_current_username()) {
		$actions[] = "<a class='tl-dm' href='".BASE_URL."directs/create/{$user}'><img src='".BASE_URF."images/dm.png' alt='' /></a>";
		$actions[] = " <a class='tl-dm' href='".BASE_URL."directs/delete/{$status->id}'><img src='".BASE_URF."images/trash.gif' alt='' /></a>";
	}
	if (!$status->is_direct) {
		if ($status->favorited == '1') {
			$actions[] = "<a class='tl-uf' href='".BASE_URL."unfavourite/{$status->id}'>UnFav</a>";
		} else {
			$actions[] = "<a class='tl-fa' href='".BASE_URL."favourite/{$status->id}'>Fav</a>";
		}
	$actions[] = "<a class='tl-rt' href='".BASE_URL."retweet/{$status->id}'>RT</a>";
	}
	return implode(' ', $actions);
}

function naiping_theme_menu_bottom() {
	return null;
}
?>