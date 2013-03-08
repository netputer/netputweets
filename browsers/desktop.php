<?php
function desktop_theme_status_form($text = '', $in_reply_to_id = NULL, $is_desktop = true) {
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
		$output .= '<div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="'.__('Update').'" /> <span id="remaining">140</span> ';

		if (substr($_GET["q"], 0, 4) !== "user") {
			$output .= ' <a href="'.RELATIVE_URL.'upload">'.__('Upload Picture').'</a>';
		}

		$output .= '</div></form>';

		return $output;
	}
}

function desktop_theme_menu_bottom() {
	return theme_menu_bottom().js_counter('status');
}

function desktop_theme_avatar($url, $force_large = true) {
	return theme_avatar($url, $force_large) ;
}
function desktop_theme_css() {
	$out = theme_css();
	if (setting_fetch('avataro', 'yes') == 'yes') {
		$out .= '<link rel="stylesheet" href="/browsers/desktop.avatar.css" />';
	}
	$out .= '<style type="text/css">'.setting_fetch('css').'</style>';
	return $out;
}
