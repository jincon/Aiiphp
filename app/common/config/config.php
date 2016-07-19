<?php
/*
 * 系统配置文件
 *
 * */

return array(

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

);


?>
