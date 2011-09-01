<?php
/* MAKING DABR INVITE ONLY - BY davidcarrington & NetPuter */

header('Content-type: text/html; charset=utf-8');
require_once 'config.php';

function config_log_request() {
	if (!user_is_authenticated()) return;

	$allowed_users = file('invite.php');

	if (!in_array(strtolower(user_current_username())."\n", $allowed_users)) {
		user_logout();
		die("对不起，您不是受邀用户，无法登录。");
	}
}

$pwd = INVITE_CODE;

if (isset($_GET['p']) && isset($_GET['u'])) {

	if ($_GET['p'] == $pwd) {
		$user = strtolower($_GET['u'])."\n";
		$handle = fopen('invite.php', 'a');

		if (is_writable('invite.php')) {

			if (!$handle = fopen('invite.php', 'a')) {
				echo "不能打开受邀用户列表。";
				exit;
			}

			if (fwrite($handle, $user) == FALSE) {
				echo "不能写入到受邀用户列表。";
				exit;
			}

			echo "成功地将 $user 加入到受邀用户列表！";
			fclose($handle);

		} else {
			echo "受邀用户列表不可写。";
		}

	} else {
        echo "邀请密码错误！";
    }
}
?>