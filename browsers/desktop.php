<?php
function desktop_theme_status_form($text = '', $in_reply_to_id = NULL, $is_desktop = true) {
	if (user_is_authenticated()) {
		$fixedtags = ((setting_fetch('fixedtago', 'no') == "yes") && ($text == '')) ? " #".setting_fetch('fixedtagc') : null;
		$output = '<form method="post" action="'.BASE_URL.'update"><textarea id="status" name="status" rows="3" style="width:100%; max-width: 400px;">'.$text.$fixedtags.'</textarea><div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="'.__('Update').'" /> <span id="remaining">140</span> ';

		if (substr($_GET["q"], 0, 4) !== "user") {
			$output .= ' <a href="'.BASE_URL.'upload">'.__('Upload Picture').'</a>';
		}

		$output .= '</div></form>';

		return $output;
	}
}

function desktop_theme_search_form($query) {
	$query = stripslashes(htmlspecialchars($query));
	return "<form action='".BASE_URL."search' method='get'><input name='query' value=\"$query\" style='width:100%; max-width: 300px' /><input type='submit' value='".__("Search")."' /></form>";
}

function desktop_theme_menu_bottom() {
	return theme_menu_bottom().js_counter('status');
}
?>