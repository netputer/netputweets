<?php
$current_theme = false;

function theme() {
	global $current_theme;
	$args = func_get_args();
	$function = array_shift($args);
	$function = 'theme_'.$function;

	if ($current_theme) {
		$custom_function = $current_theme.'_'.$function;

		if (function_exists($custom_function)) $function = $custom_function;
	} else {
		if (!function_exists($function)) return "<p>Error: theme function <b>$function</b> not found.</p>";
	}

	return call_user_func_array($function, $args);
}

function theme_csv($headers, $rows) {
	$out = implode(',', $headers)."\n";

	foreach ($rows as $row) {
		$out .= implode(',', $row)."\n";
	}

	return $out;
}

function theme_list($items, $attributes) {
	if (!is_array($items) || count($items) == 0) {
		return '';
	}

	$output = '<ul'.theme_attributes($attributes).'>';

	foreach ($items as $item) {
		$output .= "<li>$item</li>\n";
	}

	$output .= "</ul>\n";

	return $output;
}

function theme_options($options, $selected = NULL) {
	if (count($options) == 0) return '';

	$output = '';

	foreach($options as $value => $name) {
		if (is_array($name)) {
			$output .= '<optgroup label="'.$value.'">';
			$output .= theme('options', $name, $selected);
			$output .= '</optgroup>';
		} else {
			$output .= '<option value="'.$value.'"'.($selected == $value ? ' selected="selected"' : '').'>'.$name."</option>\n";
		}
	}

	return $output;
}

function theme_info($info) {
	$rows = array();

	foreach ($info as $name => $value) {
		$rows[] = array($name, $value);
	}

	return theme('table', array(), $rows);
}

function theme_table($headers, $rows, $attributes = NULL) {
	$out = '<div'.theme_attributes($attributes).'>';

	if (count($headers) > 0) {
		$out .= '<thead><tr>';
		foreach ($headers as $cell) {
			$out .= theme_table_cell($cell, TRUE);
		}
		$out .= '</tr></thead>';
	}

	if (count($rows) > 0) {
		$out .= theme('table_rows', $rows);
	}

	$out .= '</div>';

	return $out;
}

function theme_table_rows($rows) {
	$i = 0;

	foreach ($rows as $row) {
		if ($row['data']) {
			$cells = $row['data'];
			unset($row['data']);
			$attributes = $row;
		} else {
			$cells = $row;
			$attributes = FALSE;
		}

		$attributes['class'] .= ($attributes['class'] ? ' ' : '') . ($i++ %2 ? 'even' : 'odd');
		$out .= '<div'.theme_attributes($attributes).'>';

		foreach ($cells as $cell) {
			$out .= theme_table_cell($cell);
		}

		$out .= "</div>\n";
	}

	return $out;
}

function theme_attributes($attributes) {
	if (!$attributes) return;

	foreach ($attributes as $name => $value) {
		$out .= " $name=\"$value\"";
	}

	return $out;
}

function theme_table_cell($contents, $header = FALSE) {
	if (is_array($contents)) {
		$value = $contents['data'];
		unset($contents['data']);
		$attributes = $contents;
	} else {
		$value = $contents;
		$attributes = false;
	}

	return "<span".theme_attributes($attributes).">$value</span>";
}


function theme_error($message) {
	theme_page('Error', $message);
}

function theme_google_analytics() {
	if (!$GA_ACCOUNT) return '';
	$GA_PIXEL = "ga.php";
	$url = "";
	$url .= $GA_PIXEL . "?";
	$url .= "utmac=" . $GA_ACCOUNT;
	$url .= "&utmn=" . rand(0, 0x7fffffff);
	$referer = $_SERVER["HTTP_REFERER"];
	$query = $_SERVER["QUERY_STRING"];
	$path = $_SERVER["REQUEST_URI"];
	if (empty($referer)) {
		$referer = "-";
	}
	$url .= "&utmr=" . urlencode($referer);
	if (!empty($path)) {
		$url .= "&utmp=" . urlencode($path);
	}
	$url .= "&guid=ON";
	$googleanalyticsimg = str_replace("&", "&amp;", $url);
	return "<img src='{$googleanalyticsimg}' />";
}

function theme_page($title, $content) {
	$page = ($_GET['page'] == 0 ? null : " - Page ".$_GET['page'])." - ";

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link href="'.BASE_URL.'favicon.ico" rel="shortcut icon" type="image/x-icon" /><title>'.$title.$page.NPT_TITLE.'</title>'.theme('css').'</head><body id="thepage">'.theme('menu_top').$content.theme('menu_bottom').theme('google_analytics').'</body></html>';

	exit();
}

function theme_colours() {
	$info = $GLOBALS['colour_schemes'][setting_fetch('colours', 0)];
	list($name, $bits) = explode('|', $info);
	$colours = explode(',', $bits);

	return (object) array(
		'links' => $colours[0],
		'bodybg' => $colours[1],
		'bodyt' => $colours[2],
		'small' => $colours[3],
		'odd' => $colours[4],
		'even' => $colours[5],
		'replyodd' => $colours[6],
		'replyeven' => $colours[7],
		'menubg' => $colours[8],
		'menut' => $colours[9],
		'menua' => $colours[10],
	);
}

function theme_css() {
	$c = theme('colours');
	$out = "<style type='text/css'>a{color:#{$c->links};}form{margin:.3em;}img{border:0;}small,small a{color:#{$c->small};font-weight:normal;}body{background:#{$c->bodybg};color:#{$c->bodyt};margin:0;font:90% sans-serif;}.odd{background:#{$c->odd};}.even{background:#{$c->even};}.reply{background:#{$c->replyodd};}.reply.even{background:#{$c->replyeven};}.menu{color:#{$c->menut};background:#{$c->menubg};padding:2px;}.menu a{color:#{$c->menua};text-decoration:none;}.profile,.tweet{padding:5px;}.stext a{font-weight:bold;}.date{padding:5px;font-size:0.75em;color:#{$c->small}}.avatar{display:block;left:0.3em;margin:0;overflow:hidden;position:absolute;}.status{display:block;}.shift{margin-left:30px;min-height:24px;}.shift48{margin-left:60px;min-height:48px;}".setting_fetch('css')."</style>";

	return $out;
}

if(!function_exists('__')) {
	function __($text) {return $text;}
}
