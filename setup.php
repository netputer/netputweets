<?php
error_reporting(E_ALL ^ E_WARNING);

$base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
$base_url .= '://'.$_SERVER['HTTP_HOST'];

if ($directory = trim(dirname($_SERVER['SCRIPT_NAME']), '/\,')){
	$base_url .= '/'.$directory;
}

define('BASE_URL', $base_url.'/');
define('ABSPATH', dirname(__FILE__).'/');

function available($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $code < 400;
}

$configFile = file(ABSPATH . 'config-sample.php');
$notice = '';

if (!function_exists("curl_init")) {
	$notice = '<strong>提示：</strong>服务器不支持 cURL 函数，奶瓶腿将无法使用。';
} elseif (!available(BASE_URL.'settings')) {
	$notice = '<strong>提示：</strong>服务器不支持 URL Rewrite ，奶瓶腿将无法使用。';
} elseif (file_exists(ABSPATH.'config.php')) {
	$notice = '<strong>提示：</strong> config.php 文件已存在。如果您想更改 config.php 内已有的设定，请先删除它，本向导会重新创建 config.php 。<a href="setup.php">重试</a>。';
} elseif (!file_exists(ABSPATH.'config-sample.php')) {
	$notice = '<strong>提示：</strong>未能检测到 config-sample.php 文件。请确认该目录存在此文件或重新上传。';
} elseif (!is_writable(ABSPATH)) {
	$notice = '<strong>提示：</strong>目录不可写。请更改目录属性或者手动创建 config.php （参考 config-sample.php）和 invited 文件（内容为空）。';
}

if (isset($_GET['step'])) {
	$step = $_GET['step'];
} else {
	$step = 0;
}

function display_header($n) {
	header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html><meta charset=utf-8 /><title>安装向导 - 奶瓶腿!</title><link rel="stylesheet" href="images/setup.css" type="text/css" /><h1 id="logo"><img alt="奶瓶腿" src="images/setup.jpg" /></h1><?php
	if ($n !== ''){
		echo '<div id="error">'.$n.'</div>';
	}
}

switch($step) {
	case 0:
		display_header($notice);
?>
<p><strong>欢迎使用「奶瓶腿」！</strong></p>
<p><a href="https://t.orzdream.com/">奶瓶腿</a>是第三方 Twitter 网页客户端（更适合在移动设备上使用），在 <a href="http://dabr.co.uk">Dabr</a> (By <a href="https://twitter.com/davidcarrington">@davidcarrington</a>) 的基础上进行修改，同时也感谢 <a href="https://twitter.com/iChada">@iChada</a> <a href="https://twitter.com/17th">@17th</a> <a href="https://twitter.com/yegle">@yegle</a> <a href="https://twitter.com/luosheng">@luosheng</a> <a href="https://twitter.com/lonelyswan">@LonelySwan</a> 的贡献和协助。</p>
<p>如果你关注奶瓶 <a href="https://twitter.com/NetPuter">@NetPuter</a> 和他折腾的一些项目，并且希望帮助他，<a href="http://netputer.me/donate/">欢迎以捐助的形式使他更好地折腾</a>。</p>
<p>在正式使用之前，你可能需要填写一些信息，包括：</p>
<ol>
	<li>Twitter OAuth Consumer Key & Secret <a href="https://dev.twitter.com/apps/new" title="申请地址">#</a></li>
	<li>Embedly API Key （用于预览图片，建议申请） <a href="https://app.embed.ly/pricing/free" title="申请地址">#</a></li>
</ol>
<p><strong>如果无法进入下一步，别着急。此向导的目的在于创建「奶瓶腿」的配置文件，所以您还可以直接用文本编辑器打开 <code>config-sample.php</code> ，根据提示填写相应信息，然后保存并将它重命名为 <code>config.php</code> 。同时创建 invited 文件（内容为空）。</strong></p>
<p>建议在安装前仔细阅读<a href="https://github.com/netputer/netputweets/blob/master/README.md">说明文档</a>，如果还有不明白的地方，可到 <a href="https://github.com/netputer/netputweets/issues/new">GitHub Issues</a> 提出。
<p>如果已经准备好了 &hellip; &hellip;</p>
	<?php
		if ($notice !== ''){
			echo '<p class="step"><a href="setup.php" class="button">还不能开始！</a></p>';
		}else{
			echo '<p class="step"><a href="setup.php?step=1" class="button">现在就开始吧！</a></p>';
		}
	break;
	case 1:
		display_header($notice);
?>
<form method="post" action="setup.php?step=2">
	<p>请在下面的表单中填入对应的信息。</p>
	<table class="form-table">
		<tr><th scope="row"><label for="t_title">名称</label></th><td><input name="t_title" id="t_title" type="text" value="奶瓶腿!" size="35" /></td><td>如「用户 NetPuter - 奶瓶腿!」</td></tr>
		<tr><th scope="row"><label for="t_url">网址</label></th><td><input name="t_url" id="t_url" type="text" value="<?php echo BASE_URL; ?>" size="35" /></td><td>一般不需要修改，如需自动适应（如多网址同一网站）请清空</td></tr>
		<tr><th scope="row"><label for="t_tck">Twitter OAuth Consumer Key <a href="https://twitter.com/apps/new" title="申请地址">#</a></label></th><td><input name="t_tck" id="t_tck" type="text" size="35" value="awGBKfiSSqf1B2iKGsmJQ" /></td><td>一般需要修改</td></tr>
		<tr><th scope="row"><label for="t_tcs">Twitter OAuth Consumer Secret <a href="https://dev.twitter.com/apps/new" title="申请地址">#</a></label></th><td><input name="t_tcs" id="t_tcs" type="text" size="35" value="hym4qJF1F6nyjISzRUCFBU4OQSIr5mrk7074vId3K8" /></td><td>一般需要修改</td></tr>
		<tr><th scope="row"><label for="t_eak">Embedly API Key <a href="https://app.embed.ly/pricing/free" title="申请地址">#</a></label></th><td><input name="t_eak" id="t_eak" type="text" value="" size="35" /></td><td>用于预览图片，建议填写</td></tr>
		<tr><th scope="row"><label for="t_ipp">图片预览代理</label></th><td><select name="t_ipp"><option value="1">开启</option><option selected="selected" value="0">停用</option></select></td><td>开启此功能会导致服务器流量增加，请谨慎使用</td></tr>
<?php if (function_exists('gd_info')): ?>
		<tr><th scope="row"><label for="t_ipc">图片压缩</label></th><td><select name="t_ipc"><option value="1">开启</option><option selected="selected" value="0">停用</option></select></td><td>仅当「图片预览代理」开启时生效，开启此功能会消耗服务器资源 请谨慎使用</td></tr>
<?php endif ?>
		<tr><th scope="row"><label for="t_ivt">仅受邀用户可登录</label></th><td><select name="t_ivt"><option value="1">开启</option><option selected="selected" value="0">停用</option></select></td><td>请根据您的需要选择</td></tr>
		<tr><th scope="row"><label for="t_psw">设置邀请码</label></th><td><input name="t_psw" id="t_psw" type="text" value="twitter" size="35" /></td><td>仅当「仅受邀用户可登录」开启时生效，用于「 <a href="invite.php">invite.php</a> 」</td></tr>
		<tr><th scope="row"><label for="t_gac">Google Analytics 跟踪 ID</label></th><td><input name="t_gac" id="t_gac" type="text" value="" size="35" /></td><td>如「UA-19890535-X」，不需要请留空</td></tr></table>
<?php
		if ($notice !== ''){
			echo '<p class="step"><a href="setup.php" class="button">出错了！</a></p>';
		}else{
			echo '<p class="step"><input name="submit" type="submit" value="填好了！" class="button" /></p>';
		}
		echo '</form>';
		break;
	case 2:
		if (!isset($_POST['submit'])) {
			header('location: index.php');
		} else {
			$t_title = !empty($_POST['t_title']) ? trim($_POST['t_title']) : '奶瓶腿!';
			$t_url = trim($_POST['t_url']);
			$t_tck = !empty($_POST['t_tck']) ? trim($_POST['t_tck']) : 'awGBKfiSSqf1B2iKGsmJQ';
			$t_tcs = !empty($_POST['t_tcs']) ? trim($_POST['t_tcs']) : 'hym4qJF1F6nyjISzRUCFBU4OQSIr5mrk7074vId3K8';
			$t_eak = trim($_POST['t_eak']);
			$t_ipp = trim($_POST['t_ipp']);
			$t_ipc = !empty($_POST['t_ipc']) ? trim($_POST['t_ipc']) : '0';
			$t_ivt = trim($_POST['t_ivt']);
			$t_psw = !empty($_POST['t_psw']) ? trim($_POST['t_psw']) : 'twitter';
			$t_gac = trim($_POST['t_gac']);

			if ($notice == '') {
				$handle = fopen(ABSPATH . 'config.php', 'w');
				foreach ($configFile as $line_num => $line) {
					switch (substr($line, 11, 5)) {
						case 'RYPTI':
							fwrite($handle, str_replace('putyourinfohere', md5(time()), $line));
							break;
						case 'TH_KE':
							fwrite($handle, str_replace('putyourinfohere', $t_tck, $line));
							break;
						case 'TH_SE':
							fwrite($handle, str_replace('putyourinfohere', $t_tcs, $line));
							break;
						case 'EDLY_':
							fwrite($handle, str_replace('putyourinfohere', $t_eak, $line));
							break;
						case 'E_URL':
							fwrite($handle, str_replace('putyourinfohere', $t_url, $line));
							break;
						case '_TITL':
							fwrite($handle, str_replace('putyourinfohere', $t_title, $line));
							break;
						case 'PROXY':
							fwrite($handle, str_replace('0', $t_ipp, $line));
							break;
						case 'THUMB':
							fwrite($handle, str_replace('0', $t_ipc, $line));
							break;
						case "ITE',":
							fwrite($handle, str_replace('0', $t_ivt, $line));
							break;
						case 'ITE_C':
							fwrite($handle, str_replace('putyourinfohere', $t_psw, $line));
							break;
						case 'ACCOU':
							fwrite($handle, str_replace('putyourinfohere', $t_gac, $line));
							break;
						default:
							fwrite($handle, $line);
					}
				}

				$handle = fopen(ABSPATH.'invited', 'a');
				fclose($handle);

				chmod(ABSPATH.'config.php', 0666);
				chmod(ABSPATH.'invited', 0666);

				display_header($notice);
?><p>恭喜！奶瓶腿已经安装成功。准备好了？开始 &hellip; &hellip;</p><p class="step"><a href="<?php echo BASE_URL.'" class="button">抱抱奶瓶腿！</a></p>';
			}
		}
	break;
}
