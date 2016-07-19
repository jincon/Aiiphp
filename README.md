#Aiiphp 测试 V0.9.9

Aiiphp框架，不是什么牛逼和高深的框架（复杂），相反他确定一个足够简单的框架.

Aiiphp框架，追求的目的是简单、快速、高效，甚至都去除了模板解析功能。

Aiiphp框架，借鉴了几个国内外优秀，并拿来主义了别人类库，先后感谢：Doitphp，Thinkphp，Yii


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

Web ：http://www.jincon.com/

