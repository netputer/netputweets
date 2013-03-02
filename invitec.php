<?php
/* MAKING DABR INVITE ONLY - BY davidcarrington & NetPuter */

require('config.php');

header('Content-type: text/html; charset=utf-8');

function config_log_request() {
	if (!user_is_authenticated()) return;

	$allowed_users = file('invite.php');

	if (!in_array(strtolower(user_current_username())."\n", $allowed_users)) {
		user_logout();
		exit('对不起，您不是受邀用户，无法登录。如果你有邀请码，<a href="'.BASE_URL.'invite.php">请自行添加</a>');
	}
}

if (isset($_GET['p']) && isset($_GET['u'])) {

	if (INVITE == 0) {
		exit('目前为开放模式，无需邀请');
	}

	if ($_GET['p'] == INVITE_CODE) {
		$user = strtolower($_GET['u'])."\n";
		$handle = fopen('invite.php', 'a');

		if (is_writable('invite.php')) {

			if (!$handle = fopen('invite.php', 'a')) {
				echo '不能打开受邀用户列表';
			}

			if (fwrite($handle, $user) == FALSE) {
				echo '不能写入到受邀用户列表';
			}

			echo '已将 '.trim($user).' 加入到受邀用户列表';
			fclose($handle);

		} else {
			exit('受邀用户列表不可写');
		}

	} else {
        exit('邀请密码错误');
    }
}
