#Aiiphp 测试 V1.0.1

Aiiphp框架，不是什么牛逼和高深的框架（复杂，学习成本高），相反他却是一个足够简单的框架.

Aiiphp框架，追求的是简单、快速、安全、高效，甚至都去除了模板解析功能.

Aiiphp框架，集成常用的类库，简单的类库扩展方式，让你可以轻松扩展自己的类库和借鉴拿来主义别人类库.

Aiiphp框架，借鉴了几个国内外优秀，并拿来主义了别人类库，一并先后感谢各位.

#Aiiphp 版本问题

Aiiphp将着手开发高版本Aiiphp 2.x ，高版本最低兼容php5.4+，而Aiiphp 1.x 将同步维护，并兼容php5.2低版本。

Aiiphp 1.x 系列框架将主要向下兼容到php5.2，可能适用一些低版本的服务器环境和快速高效的开发环境。

Aiiphp 2.x 系列框架将基于php5.4+，主要发挥php的命名空间等一些高级特性，充分提升框架的性能。目前正在着手开发中。


#系统目录结构：

Aiiphp框架目录。结构

<pre>
|____app                App目录
| |____admin            后台目录
| | |____controller     后台控制器
| | |____model          后台模型
| | |____view           后台视图
| |____common           通用文件
| | |____class          项目通用类库
| | |____config         项目配置文件
| | |____function       项目通用函数库
| |____home             前台目录
| | |____controller     前台控制器
| | |____model          前台模型
| | |____view           前台视图
|____core               系统框架目录
| |____Aii.php          核心框架文件
| |____class            自动加载的类库
| |____Convention.php       系统配置模板
| |____core             核心框架类库
| | |____Config.class.php   配置获取类库
| | |____Controller.class.php   父控制器类库
| | |____Db.class.php       数据库类库
| | |____Model.class.php    服模型类库
| | |____Newexception.class.php 异常加载类库
| |____function     核心框架函数库
| | |____Function.func.php  核心函数库
| |____lib              需要手动加载类库
| |____view             系统的一些加载模板目录
|____data               数据目录
| |____cache            缓存目录
| |____log              日志目录
|____index.php          首页文件
|____static             静态资源目录。
| |____css              CSS目录
| |____font             字体目录
| |____image            图片目录
| |____js               JS目录。
</pre>


#作品相关

Authon：jincon

Web ：http://www.jincon.com/

手册：https://jincon.gitbooks.io/aiiphp/content/

#测试数据：

等我有空搞。。。。或者谁给我一张图！

#更新日志：
12.24
<pre>
1、添加命令行Aii工具，先搞个简单版本吧，算是为了占位。

</pre>

11.18
<pre>
1、添加一个图片处理类库、Tpimage
2、添加微信处理类库 Wechat
3、修复小问题。

</pre>

10.20
<pre>
1、添加模板主题功能，支持模块设置主题，如前后台.
2、添加URL伪静态后缀设置，配置参数：URL_HTML_SUFFIX.
3、URL自定义路由支持，请使用U函数生成url。会自动识别符合自定义路由的url并生成自定义url.
4、手册会抽空完善。
</pre>

10.17
<pre>
1、主要完善捕获异常报错。
2、添加一个简单的基准测试类
</pre>



