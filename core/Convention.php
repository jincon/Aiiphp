<?php
/**
 * Created by PhpStorm.
 * User: jincon
 * Date: 16/7/9
 * Time: 下午2:48
 */
return  array(

    'HOST'                  =>  'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/',
    'BASE_URL'              =>  str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME'])).'/',


    /***************** 数据库设置 *****************/

    // 如果有slave自动开启主从，否则只有一个库使用。
    'DB_MASTER_SLAVE'=> array(

        //样例
        /*'master'  => array(
            'dsn'      => 'mysql:host=localhost;port=3306;dbname=test',
            'username' => 'root',
            'password' => '123456',
            'persistency'=>'false',              //持久链接
        ),
        'slave'   => array(
            array(
                'dsn'      => 'mysql:host=localhost;port=3306;dbname=test',
                'username' => 'root',
                'password' => '123456',
                'persistency'=>'false',          //持久链接
            ),
            array(
                'dsn'      => 'mysql:host=localhost;port=3306;dbname=test',
                'username' => 'root',
                'password' => '123456',
                'persistency'=>'false',          //持久链接
            ),
            array(
                'dsn'      => 'mysql:host=localhost;port=3306;dbname=test',
                'username' => 'root',
                'password' => '123456',
                'persistency'=>'false',          //持久链接
            )
        ),
        //如果是需要连接其他库。注意，此库不会自动连接，需要手动连接。。
        'bbs'  => array(
            'dsn'      => 'mysql:host=localhost;port=3306;dbname=test',
            'username' => 'root',
            'password' => '123456',
            'persistency'=>'false',              //持久链接
        ),
        'prefix'  => '',
        'charset' => 'utf8',*/
    ),
    /***************** 数据库设置end *****************/


    /* 应用设定 */
    'STATIC_PATH'            =>  'static/',  //相对于根目录而已
    'DATA_PATH'              =>  ROOT.'data/',
    'CACHE_PATH'             =>  ROOT.'data/cache/',
    'LOG_ON'                 =>  true,
    'LOG_PATH'               =>  ROOT.'data/log/',

    //'CACHE_TYPE' =>'',

    'MEMCACHED'             => array(
//          'servers'=> array(
//              array('host'=>'127.0.0.1', 'port'=>11211, 'persistent'=>true, 'weight'=>1, 'timeout'=>60),
//              array('host'=>'192.168.0.101', 'port'=>11211, 'persistent'=>true, 'weight'=>2, 'timeout'=>60),
//          ),
//          'compressed'=>true,
//          'expire' => 3600,
//          'persistent' => true,
    ),
    'MEMCACHE'             => array(
//      'servers'=> array(
//          array('host'=>'127.0.0.1', 'port'=>11211, 'persistent'=>true, 'weight'=>1, 'timeout'=>60),
//          array('host'=>'192.168.0.101', 'port'=>11211, 'persistent'=>true, 'weight'=>2, 'timeout'=>60),
//      ),
//      'compressed'=>true,
//      'expire' => 3600,
//      'persistent' => true,
    ),
    'REDIS'    => array(
//        'host'       => '127.0.0.1',
//        'port'       => '6379',
//        'password'   => null,
//        'database'   => 0,
//        'persistent' => false,
//        'expire'     => 900,
    ),

    'MONGODB'               => array(
//        'dbname'=>'test',
//        'dsn'    => 'mongodb://localhost:27017',
//        'username' => '',
//        'password' => '',
//        'option' => array('connect' => true),
    ),

    'MODULE_ALLOW_LIST'     =>  'home,admin',
    'MODULE_DENY_LIST'      =>  'common',


    /* Cookie设置 */
    'COOKIE_EXPIRE'         =>  0,       // Cookie有效期
    'COOKIE_DOMAIN'         =>  '',      // Cookie有效域名
    'COOKIE_PATH'           =>  '/',     // Cookie路径
    'COOKIE_PREFIX'         =>  '',      // Cookie前缀 避免冲突
    'COOKIE_SECURE'         =>  false,   // Cookie安全传输
    'COOKIE_HTTPONLY'       =>  '',      // Cookie httponly设置

    'COOKIE'                => array(

    ),

    //session
    'SESSION_AUTOSTART'     => true,     //是否自动开启session
    'SESSION_EXPIRE'        => '3600',   //设置session最大存活时间。session有效期
    'SESSION_PATH'          => '',       //存储路径





    /* 默认设定 */
    'DEFAULT_MODULE'        =>  'home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE'      =>  'PRC',	// 默认时区
    'DEFAULT_AJAX_RETURN'   =>  'JSON',  // 默认AJAX 数据返回格式,可选JSON XML ...
    'VAR_JSONP_HANDLER'     =>  'callback',
    'DEFAULT_JSONP_HANDLER' =>  'jsonpReturn', // 默认JSONP格式返回的处理方法
    'DEFAULT_FILTER'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数...

    'FILTER_ON'             =>  true,  //是否开启过滤

    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       =>  '',// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_KEY'        =>  '',	// 缓存文件KEY (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别

    /* 错误设置 */
    'ERROR_MESSAGE'         =>  '页面错误！请稍后再试～',//错误显示信息,非调试模式有效
    'ERROR_PAGE'            =>  '',	// 错误定向页面
    'SHOW_ERROR_MSG'        =>  false,    // 显示错误信息
    'TRACE_MAX_RECORD'      =>  100,    // 每个级别的错误信息 最大记录数

    /* 模板引擎设置 */
    'TMPL_TEMPLATE_SUFFIX'  =>  '.php',     // 默认模板文件后缀


    /* URL设置 0，3没有开发功能。  */
    'URL_MODEL'             =>  1,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式

    'URL_CASE_INSENSITIVE' =>true,   //true 不区分大小写url，false区分，默认true

    'URL_HTML_SUFFIX'       =>  '',  //URL伪静态后缀设置，注意，这个添加后，必须要使用U函数生成。

    'LOAD_EXT_CONFIG' => '',   //如 db,user  等，系统会按照顺序依次加载。请注意变量重复。配合文件格式：*.php

    /* 系统变量名称设置 */
    'VAR_AJAX_SUBMIT'       =>  'ajax',  // 默认的AJAX提交变量

    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => '',

    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => '',


    //主题模板的规则的 模块名称大写+ _THEME配置，为空则不使用主题
    'HOME_THEME' => '',  //前台主题模板
    'ADMIN_THEME' => '',   //后台主题模板


    // 开启路由，注意开启路由，可能略微损耗性能。
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
//        'hello'	=> 'index/test',
//        'stu/(:num)'	=> 'index/stu/id/${1}',
//        'stu/(\d+)'	=> 'index/stu/id/${1}', //普通正则正则模式，注意要进行（）哦
//        'city/(:any)'	=> 'city/${1}',
    ),

);