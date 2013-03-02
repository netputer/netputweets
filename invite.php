<?php
require('config.php');

header('Content-type: text/html; charset=utf-8');

if (isset($_POST['p']) && isset($_POST['u'])) {

	if (INVITE == 0) {
		exit('目前为开放模式，无需邀请');
	}

	if ($_POST['p'] == INVITE_CODE) {
		$user = strtolower($_POST['u'])."\n";

		if (is_writable('invited')) {

			if (!$handle = fopen('invited', 'a')) {
				exit('不能打开受邀用户列表');
			}

			if (fwrite($handle, $user) == FALSE) {
				echo '不能写入到受邀用户列表';
			} else {
				echo '已将 '.trim($user).' 加入到受邀用户列表';
			}

			fclose($handle);
		} else {
			echo '受邀用户列表不可写';
		}
		
		exit;
	} else {
        exit('邀请密码错误');
    }
}

?><!doctype html><meta charset="utf-8" /><title>添加受邀用户 - 奶瓶腿!</title><form action="invite.php" method="POST"><label>用户名 <input name="u" /></label> <label>邀请码 <input name="p" /></label> <input type="submit" /></form>
