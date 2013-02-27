奶瓶腿 - NetPutweets
====================
抱着奶瓶腿 享受推的乐趣

介绍
----

「奶瓶腿」是一个安全的、个性的第三方中文 Twitter 手机客户端，基于 [Dabr](http://code.google.com/p/dabr) 项目，由 [@NetPuter](https://twitter.com/NetPuter) 修改、架设。同时也感谢 [@iChada](https://twitter.com/iChada) [@17th](https://twitter.com/17th) [@yegle](https://twitter.com/yegle) [@luosheng](https://twitter.com/luosheng) [@LonelySwan](https://twitter.com/LonelySwan) 的协助。

如果你关注奶瓶 [@NetPuter](https://twitter.com/NetPuter) 和他折腾的一些项目，并且希望帮助他，欢迎以[捐助的形式](http://netputer.me/donate/)使他更好地折腾。

架设
----

### 系统需求 ###

_TODO_

### 使用 Git ###

在服务器中运行
    git clone git://github.com/netputer/netputweets.git
将得到的 `netputweets` 文件夹中的所有内容复制到目标目录中，并从浏览器中访问。

在升级时，请运行 `git fetch` 。然后删除 `config.php` 并重新安装。

### 下载发布版本 ###

请[点击这里](https://github.com/netputer/netputweets/archives/master)下载并解压缩所有文件到服务器 Web 目录中，并从浏览器中访问。

提示
----

1. 请尊重 Dabr 作者 [@davidcarrington](https://twitter.com/davidcarrington) 和修改者 [@17th](https://twitter.com/17th) [@iChada](https://twitter.com/iChada) [@NetPuter](https://twitter.com/NetPuter) [@yegle](https://twitter.com/yegle) [@luosheng](https://twitter.com/luosheng) 的劳动成果，保留 `about.html` 等相应内容。
2. 如需升级，请删除所有文件，重新下载再安装。
3. 修改文件时请使用不会添加 BOM 的编辑器（Windows 上如 `wordpad` 等）。
4. 可视化邀请页面：`invite.php`
5. 由于图片预览代理非常消耗服务器的资源，请谨慎使用。如需使用图片预览代理，请务必在安装时填写 `Embedly API Key` 字段。如不需图片压缩（节省流量），请将 `config.php` 中 `IMGPROXY_THUMB` 后面的 `1` 修改为 `0` 。
6. 使用 Nginx 的用户请将 `dabr.conf` 包含到站点配置文件中。
7. 更多帮助请访问《[奶瓶腿简明架设教程 + Q&A](http://netputer.me/2009/10/netputweets-guide/)》或者通过下面的方法联系我们。

联系
----

* Groups: [奶瓶腿讨论组](https://groups.google.com/group/netputweets?hl=zh-CN)
* Mail: <netputer@gmail.com>
* Twitter: [@NetPuter](https://twitter.com/NetPuter)
