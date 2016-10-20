<?php
/*
 * 系统配置文件
 *
 * */

return array(

    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => 'public:error',

    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'public:success',

    'URL_MODEL'             =>  1,

    'DB_MASTER_SLAVE'=> array(
        'master'  => array(
            'dsn'      => 'mysql:host=localhost;port=3306;dbname=test',
            'username' => 'root',
            'password' => '123456',
            'persistency'=>'',
        ),
        'bbs'  => array(
            'dsn'      => 'mysql:host=localhost;port=3306;dbname=cms',
            'username' => 'root',
            'password' => '123456',
            'persistency'=>'false',              //持久链接
        ),
//        'slave'   => array(
//            array(
//                'dsn'      => 'mysql:host=localhost;port=3306;dbname=cms',
//                'username' => 'root',
//                'password' => '123456',
//                'persistency'=>'',
//            ),
//            array(
//                'dsn'      => 'mysql:host=localhost;port=3306;dbname=cms',
//                'username' => 'root',
//                'password' => '123456',
//                'persistency'=>'',
//            ),
//            array(
//                'dsn'      => 'mysql:host=localhost;port=3306;dbname=cms',
//                'username' => 'root',
//                'password' => '123456',
//                'persistency'=>'',
//            )
//        ),
        'prefix'  => '',
        'charset' => 'utf8',
    ),


    'MODULE_ALLOW_LIST' => 'home,admin',
    'DEFAULT_ACTION'=>'test',

    'LOAD_EXT_CONFIG'=>'ext',

    //主题模板的规则的 模块名称大写+ _THEME配置，为空则不使用主题
    'HOME_THEME' => 'newtheme',  //前台主题模板
    'ADMIN_THEME' => '',   //后台主题模板

    'URL_HTML_SUFFIX'       =>  'html',  //URL伪静态后缀设置，注意，这个添加后，必须要使用U函数生成。

    // 开启路由，注意开启路由，可能略微损耗性能。
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
        'stu/(:num)_ab/(:num)-cd'	=> 'index/stu/id/${1}/type/${2}',
        'hello'	=> 'index/test',
        //'stu/(\d+)'	=> 'index/stu/id/${1}', //普通正则正则模式，注意要进行（）哦
        'ci/(:any)'	=> 'city/${1}',
    ),

);


?>
