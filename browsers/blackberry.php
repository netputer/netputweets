<?php

function blackberry_theme_menu_bottom() {
	global $blackberry_pagination;
	return theme_menu_bottom().$blackberry_pagination;
}
